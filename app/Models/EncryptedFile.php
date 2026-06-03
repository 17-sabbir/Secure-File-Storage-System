<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncryptedFile extends Model
{
    protected $fillable = ['user_id', 'original_name', 'stored_name', 'algorithm', 'type', 'file_size'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}