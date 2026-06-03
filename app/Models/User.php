<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DecryptedFile;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];

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
}