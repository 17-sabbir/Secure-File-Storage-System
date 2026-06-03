<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\EncryptedFile;
use App\Models\DecryptedFile;

class MigrateToMongoDB extends Command
{
    protected $signature = 'migrate:to-mongodb';
    protected $description = 'Migrate data from SQLite to MongoDB';

    public function handle()
    {
        $this->info('Starting migration from SQLite to MongoDB...');

        try {
            // Migrate Users
            $this->info('Migrating Users...');
            $sqliteUsers = DB::connection('sqlite')->table('users')->get();
            foreach ($sqliteUsers as $user) {
                User::create([
                    '_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
            $this->info("✓ Migrated " . count($sqliteUsers) . " users");

            // Migrate Encrypted Files
            $this->info('Migrating Encrypted Files...');
            $sqliteEncrypted = DB::connection('sqlite')->table('encrypted_files')->get();
            foreach ($sqliteEncrypted as $file) {
                EncryptedFile::create([
                    '_id' => $file->id,
                    'user_id' => $file->user_id,
                    'original_name' => $file->original_name,
                    'stored_name' => $file->stored_name,
                    'algorithm' => $file->algorithm,
                    'type' => $file->type,
                    'file_size' => $file->file_size,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ]);
            }
            $this->info("✓ Migrated " . count($sqliteEncrypted) . " encrypted files");

            // Migrate Decrypted Files
            $this->info('Migrating Decrypted Files...');
            $sqliteDecrypted = DB::connection('sqlite')->table('decrypted_files')->get();
            foreach ($sqliteDecrypted as $file) {
                DecryptedFile::create([
                    '_id' => $file->id,
                    'user_id' => $file->user_id,
                    'original_name' => $file->original_name,
                    'stored_name' => $file->stored_name,
                    'algorithm' => $file->algorithm,
                    'file_size' => $file->file_size,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ]);
            }
            $this->info("✓ Migrated " . count($sqliteDecrypted) . " decrypted files");

            $this->info('✅ Migration completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
