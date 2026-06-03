<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecryptedFile extends Model
{
    protected $fillable = ['user_id', 'original_name', 'stored_name', 'algorithm', 'file_size'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
