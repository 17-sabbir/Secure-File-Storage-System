<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();

            // The server only ever stores ciphertext. Messages are encrypted client-side
            // with a random AES-GCM key; that key is itself encrypted with the
            // recipient's RSA public key (and, so the sender can read their own
            // sent history back, with the sender's RSA public key too).
            $table->text('ciphertext');          // AES-GCM encrypted message body (base64)
            $table->string('iv');                // AES-GCM initialization vector (base64)
            $table->text('key_for_recipient');    // AES key encrypted with recipient's RSA public key (base64)
            $table->text('key_for_sender');       // AES key encrypted with sender's RSA public key (base64)

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'sender_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
