<?php

namespace App\Http\Controllers;

use App\Models\DecryptedFile;
use App\Models\EncryptedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CryptoController extends Controller
{
    /**
     * Dashboard - show user's files
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        $encryptedFiles = EncryptedFile::where('user_id', $userId)->get();
        $encryptedFiles->each(function ($file) {
            $file->setAttribute('type', 'encrypted');
            $file->setAttribute('download_route', route('download.encrypted', $file));
            $file->setAttribute('delete_route', route('file.delete.encrypted', $file));
        });

        $decryptedFiles = DecryptedFile::where('user_id', $userId)->get();
        $decryptedFiles->each(function ($file) {
            $file->setAttribute('type', 'decrypted');
            $file->setAttribute('download_route', route('download.decrypted', $file));
            $file->setAttribute('delete_route', route('file.delete.decrypted', $file));
        });

        $files = $encryptedFiles
            ->concat($decryptedFiles)
            ->sortByDesc('created_at')
            ->values();
        return view('dashboard.index', compact('files'));
    }

    /**
     * Encrypt a file using AES-128 or DES (16-char key required)
     */
    public function encrypt(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|max:51200', // max 50MB
            'key'       => 'required',
            'algorithm' => 'required|in:AES,DES',
        ]);

        $key  = $request->input('key');
        $algo = $request->input('algorithm');
        $expectedLength = $algo === 'DES' ? 8 : 16;
        if (strlen($key) !== $expectedLength) {
            return back()
                ->withErrors(['key' => "Key must be {$expectedLength} characters for {$algo}."])
                ->withInput();
        }

        try {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $plainData    = file_get_contents($uploadedFile->getRealPath());

            // Encrypt
            $encryptedData = $this->cryptoProcess($plainData, $key, $algo, 'encrypt');

            // Save to storage/encrypted/
            $storedName = time() . '_' . $originalName . '.enc';
            $savePath   = storage_path('encrypted/' . $storedName);
            file_put_contents($savePath, $encryptedData);

            // Save record in DB
            EncryptedFile::create([
                'user_id'       => Session::get('user_id'),
                'original_name' => $originalName,
                'stored_name'   => $storedName,
                'algorithm'     => $algo,
                'file_size'     => strlen($encryptedData),
            ]);

            return back()->with('success', 'encryption-success');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Encryption failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Decrypt a file (must provide same 16-char key & algorithm)
     */
    public function decrypt(Request $request)
    {
        $request->validate([
            'file'      => 'required|file',
            'key'       => 'required',
            'algorithm' => 'required|in:AES,DES',
        ]);

        $key  = $request->input('key');
        $algo = $request->input('algorithm');
        $expectedLength = $algo === 'DES' ? 8 : 16;
        if (strlen($key) !== $expectedLength) {
            return back()
                ->withErrors(['key' => "Key must be {$expectedLength} characters for {$algo}."])
                ->withInput();
        }

        try {
            $uploadedFile  = $request->file('file');
            $originalName  = $uploadedFile->getClientOriginalName();
            $encryptedData = file_get_contents($uploadedFile->getRealPath());

            // Decrypt
            $decryptedData = $this->cryptoProcess($encryptedData, $key, $algo, 'decrypt');

            // Remove .enc extension if present
            $decName    = preg_replace('/\.enc$/', '', $originalName);
            $storedName = time() . '_' . $decName . '.dec';
            $savePath   = storage_path('decrypted/' . $storedName);
            file_put_contents($savePath, $decryptedData);

            // Save record in DB
            DecryptedFile::create([
                'user_id'       => Session::get('user_id'),
                'original_name' => $decName,
                'stored_name'   => $storedName,
                'algorithm'     => $algo,
                'file_size'     => strlen($decryptedData),
            ]);

            return back()->with('success', 'decryption-success');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Decryption failed. The key or algorithm may be incorrect.']);
        }
    }

    /**
     * Download an encrypted or decrypted file
     */
    public function downloadEncrypted(EncryptedFile $file)
    {
        return $this->downloadStoredFile(
            $file,
            'encrypted',
            $file->original_name . '.enc'
        );
    }

    public function downloadDecrypted(DecryptedFile $file)
    {
        return $this->downloadStoredFile(
            $file,
            'decrypted',
            $file->original_name
        );
    }

    /**
     * Delete a file record and its stored file
     */
    public function deleteEncrypted(EncryptedFile $file)
    {
        return $this->deleteStoredFile($file, 'encrypted');
    }

    public function deleteDecrypted(DecryptedFile $file)
    {
        return $this->deleteStoredFile($file, 'decrypted');
    }

    private function downloadStoredFile(Model $file, string $folder, string $downloadName)
    {
        if ($file->user_id !== Session::get('user_id')) {
            abort(403);
        }

        $path = storage_path($folder . '/' . $file->stored_name);
        if (!file_exists($path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return response()->download($path, $downloadName);
    }

    private function deleteStoredFile(Model $file, string $folder)
    {
        if ($file->user_id !== Session::get('user_id')) {
            abort(403);
        }

        $path = storage_path($folder . '/' . $file->stored_name);
        if (file_exists($path)) {
            unlink($path);
        }

        $file->delete();
        return back()->with('success', 'File deleted successfully.');
    }

    // ─── Core crypto logic ───────────────────────────────────────────────────

    /**
     * AES-128-CBC or DES-CBC encrypt/decrypt via OpenSSL
     *
     * The Java project uses a custom AES with CBC and PKCS7 padding,
     * and the 16-character key directly (AES-128).
     * DES requires 8-byte key — we use the first 8 chars of the 16-char key.
     */
    private function cryptoProcess(string $data, string $key, string $algo, string $mode): string
    {
        if ($algo === 'AES') {
            $cipher = 'AES-128-CBC';
            $keyBytes = $key; // 16 bytes = AES-128
        } else {
            // DES uses 8-byte key; take first 8 chars of the 16-char key
            $cipher   = 'DES-CBC';
            $keyBytes = substr($key, 0, 8);
        }

        // Fixed IV (16 zero bytes for AES, 8 for DES) — matches Java's behaviour
        // where IV is all-zero unless explicitly provided
        $ivLen = openssl_cipher_iv_length($cipher);
        $iv    = str_repeat("\x00", $ivLen);

        if ($mode === 'encrypt') {
            $result = openssl_encrypt($data, $cipher, $keyBytes, OPENSSL_RAW_DATA, $iv);
            if ($result === false) {
                throw new \Exception('OpenSSL encrypt failed: ' . openssl_error_string());
            }
            return $result;
        } else {
            $result = openssl_decrypt($data, $cipher, $keyBytes, OPENSSL_RAW_DATA, $iv);
            if ($result === false) {
                throw new \Exception('OpenSSL decrypt failed: ' . openssl_error_string());
            }
            return $result;
        }
    }
}