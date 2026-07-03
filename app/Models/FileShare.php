<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    protected $fillable = ['encrypted_file_id', 'shared_by_user_id', 'shared_with_user_id'];

    public function encryptedFile()
    {
        return $this->belongsTo(EncryptedFile::class);
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}
