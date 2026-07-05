<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
   
class ChatController extends Controller
{
    private function me(): User
    {
        return User::findOrFail(Session::get('user_id'));
    }

    /**
     * Chat shell. The actual crypto happens entirely in the browser
     * (Web Crypto API) — the server never sees plaintext messages,
     * and the RSA private key never leaves the user's browser storage.
     */
    public function index()
{
    $me = $this->me();

    $contacts = User::where('id', '!=', $me->id)
        ->orderBy('name')
        ->get(['id', 'name', 'email', 'chat_public_key'])
        ->map(function ($contact) {
            // Never expose full email addresses to other users — only a
            // masked hint is needed to help identify who's who.
            $contact->email = $this->maskEmail($contact->email);
            return $contact;
        });

    return view('chat.index', [
        'me'       => $me,
        'contacts' => $contacts,
    ]);
}

/**
 * Mask an email address so only a small hint is shown, e.g.
 * "shakil.ahmed@gmail.com" -> "sh*********@gmail.com"
 */
private function maskEmail(string $email): string
{
    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

    $visible = min(2, strlen($local));
    $masked  = substr($local, 0, $visible) . str_repeat('*', max(strlen($local) - $visible, 3));

    return $domain !== '' ? "{$masked}@{$domain}" : $masked;
}

    /**
     * Store this browser's generated RSA public key against the account.
     * Called automatically the first time a user opens the chat page
     * (or if their local keypair was regenerated).
     */
    public function storePublicKey(Request $request)
    {
        $request->validate(['public_key' => 'required|string']);

        $me = $this->me();
        $me->forceFill(['chat_public_key' => $request->public_key])->save();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Fetch the encrypted conversation history with another user.
     */
    public function messages(Request $request, User $user)
    {
        $me = $this->me();

        $messages = ChatMessage::where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me->id)->where('recipient_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)->where('recipient_id', $me->id);
            })
            ->orderBy('created_at')
            ->get();

        ChatMessage::where('sender_id', $user->id)
            ->where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'id'                => $m->id,
                'sender_id'         => $m->sender_id,
                'recipient_id'      => $m->recipient_id,
                'ciphertext'        => $m->ciphertext,
                'iv'                => $m->iv,
                // Only the key encrypted for "me" is ever handed back to me.
                'encrypted_key'     => $m->sender_id === $me->id ? $m->key_for_sender : $m->key_for_recipient,
                'created_at'        => $m->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Store a new encrypted message. Body already contains:
     * - ciphertext + iv (AES-GCM encrypted message, produced client-side)
     * - key_for_recipient (the AES key, RSA-encrypted with the recipient's public key)
     * - key_for_sender     (the same AES key, RSA-encrypted with the sender's own public key,
     *                        so the sender can also decrypt their own sent messages later)
     */
    public function send(Request $request, User $user)
    {
        $request->validate([
            'ciphertext'        => 'required|string|max:12000',
            'iv'                => 'required|string|max:1000',
            'key_for_recipient' => 'required|string|max:12000',
            'key_for_sender'    => 'required|string|max:12000',
        ]);

        $me = $this->me();

        if ($user->id === $me->id) {
            return response()->json(['error' => 'You cannot send an encrypted message to yourself.'], 422);
        }

        if (!$user->chat_public_key) {
            return response()->json(['error' => 'This user has not set up encrypted chat yet.'], 422);
        }

        $message = ChatMessage::create([
            'sender_id'         => $me->id,
            'recipient_id'      => $user->id,
            'ciphertext'        => $request->ciphertext,
            'iv'                => $request->iv,
            'key_for_recipient' => $request->key_for_recipient,
            'key_for_sender'    => $request->key_for_sender,
        ]);

        return response()->json(['status' => 'ok', 'id' => $message->id, 'created_at' => $message->created_at->toIso8601String()]);
    }

    /**
     * Returns the public keys of all users, keyed by id, so the client can
     * encrypt for any recipient without an extra round trip per message.
     */
    public function publicKeys()
    {
        return response()->json(
            User::whereNotNull('chat_public_key')->pluck('chat_public_key', 'id')
        );
    }
}
