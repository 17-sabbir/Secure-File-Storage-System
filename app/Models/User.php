<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DecryptedFile;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'chat_public_key',
    ];

    protected $hidden = ['password', 'two_factor_code'];

    protected function casts(): array
    {
        return [
            'two_factor_enabled'     => 'boolean',
            'two_factor_expires_at'  => 'datetime',
        ];
    }

    // SHA-512 password hashing (same as original Java project)
    public static function sha512(string $password): string
    {
        return hash('sha512', $password);
    }

    public function files()
    {
        return $this->hasMany(EncryptedFile::class);
    }

    public function decryptedFiles()
    {
        return $this->hasMany(DecryptedFile::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'recipient_id');
    }
}