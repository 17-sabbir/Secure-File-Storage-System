<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('file_shares')) {
            return;
        }

        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encrypted_file_id')->constrained('encrypted_files')->onDelete('cascade');
            $table->foreignId('shared_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['encrypted_file_id', 'shared_with_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};