<?php

namespace App\Http\Controllers;

use App\Models\DecryptedFile;
use App\Models\EncryptedFile;
use App\Models\FileShare;
use App\Models\User;
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
        $encryptedFiles = EncryptedFile::where('user_id', $userId)->with('shares.sharedWith')->get();
        $encryptedFiles->each(function ($file) {
            $file->setAttribute('type', 'encrypted');
            $file->setAttribute('download_route', route('download.encrypted', $file));
            $file->setAttribute('delete_route', route('file.delete.encrypted', $file));
            $file->setAttribute('share_route', route('file.share', $file));
        });

        $decryptedFiles = DecryptedFile::where('user_id', $userId)->get();
        $decryptedFiles->each(function ($file) {
            $file->setAttribute('type', 'decrypted');
            $file->setAttribute('download_route', route('download.decrypted', $file));
            $file->setAttribute('delete_route', route('file.delete.decrypted', $file));
            $file->setAttribute('open_route', route('open.decrypted', $file));
        });

        $files = $encryptedFiles
            ->concat($decryptedFiles)
            ->sortByDesc('created_at')
            ->values();

        // Encrypted files that other registered users have shared with the current user
        $sharedWithMe = FileShare::where('shared_with_user_id', $userId)
            ->whereHas('encryptedFile')
            ->with(['encryptedFile', 'sharedBy'])
            ->get()
            ->sortByDesc('created_at')
            ->values();

        return view('dashboard.index', compact('files', 'sharedWithMe'));
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
        $minLength = $algo === 'DES' ? 3 : 5;
        $maxLength = 20;
        if (strlen($key) < $minLength || strlen($key) > $maxLength) {
            return back()
                ->withErrors(['key' => "Key must be between {$minLength} and {$maxLength} characters for {$algo}."])
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
        $minLength = $algo === 'DES' ? 3 : 5;
        $maxLength = 20;
        if (strlen($key) < $minLength || strlen($key) > $maxLength) {
            return back()
                ->withErrors(['key' => "Key must be between {$minLength} and {$maxLength} characters for {$algo}."])
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
        $userId = Session::get('user_id');
        $isOwner = $file->user_id === $userId;
        $isSharedWithMe = $isOwner ? false : FileShare::where('encrypted_file_id', $file->id)
            ->where('shared_with_user_id', $userId)
            ->exists();

        if (!$isOwner && !$isSharedWithMe) {
            abort(403);
        }

        $path = storage_path('encrypted/' . $file->stored_name);
        if (!file_exists($path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return response()->download($path, $file->original_name . '.enc');
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
     * Open a decrypted file directly in the browser (inline) instead of
     * forcing a download, so it can be previewed when the file type
     * supports it (images, PDFs, text, etc.)
     */
    public function openDecrypted(DecryptedFile $file)
    {
        if ($file->user_id !== Session::get('user_id')) {
            abort(403);
        }

        $path = storage_path('decrypted/' . $file->stored_name);
        if (!file_exists($path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }

    /**
     * Share an encrypted file with another registered user (by email).
     * The encryption key itself is never stored or shared here — the
     * recipient must still receive the key through a separate channel,
     * exactly as the sender needed one to encrypt the file in the first place.
     */
    public function share(Request $request, EncryptedFile $file)
    {
        if ($file->user_id !== Session::get('user_id')) {
            abort(403);
        }

        $request->validateWithBag('share', [
            'email' => 'required|email',
        ]);

        $recipient = User::where('email', $request->input('email'))->first();

        if (!$recipient) {
            return back()->withErrors(['email' => 'No account found with that email.'], 'share')->withInput();
        }

        if ($recipient->id === $file->user_id) {
            return back()->withErrors(['email' => 'You cannot share a file with yourself.'], 'share')->withInput();
        }

        $alreadyShared = FileShare::where('encrypted_file_id', $file->id)
            ->where('shared_with_user_id', $recipient->id)
            ->exists();

        if ($alreadyShared) {
            return back()->withErrors(['email' => 'This file is already shared with that user.'], 'share')->withInput();
        }

        FileShare::create([
            'encrypted_file_id'   => $file->id,
            'shared_by_user_id'   => Session::get('user_id'),
            'shared_with_user_id' => $recipient->id,
        ]);

        return back()->with('success', 'share-success');
    }

    /**
     * Revoke a previously granted share.
     */
    public function unshare(FileShare $share)
    {
        if ($share->shared_by_user_id !== Session::get('user_id')) {
            abort(403);
        }

        $share->delete();

        return back()->with('success', 'Access revoked successfully.');
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
     * The Java project uses a custom AES with CBC and PKCS7 padding.
     * Since the user-supplied password/key can now be any length (5-20
     * chars for AES, 3-20 for DES), we derive a fixed-size cipher key by
     * SHA-256 hashing the raw password and taking the required number of
     * leading bytes: 16 bytes for AES-128, 8 bytes for DES.
     */
    private function cryptoProcess(string $data, string $key, string $algo, string $mode): string
    {
        $hash = hash('sha256', $key, true); // 32 raw bytes

        if ($algo === 'AES') {
            $cipher   = 'AES-128-CBC';
            $keyBytes = substr($hash, 0, 16); // 16 bytes = AES-128
        } else {
            $cipher   = 'DES-CBC';
            $keyBytes = substr($hash, 0, 8); // 8 bytes = DES
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