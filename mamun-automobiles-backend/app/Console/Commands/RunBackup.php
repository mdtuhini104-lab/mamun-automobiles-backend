<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupRecoveryEngine;

class RunBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create an encrypted database backup and verify restore viability';

    /**
     * Execute the console command.
     */
    public function handle(BackupRecoveryEngine $engine)
    {
        $this->info('Starting database backup compression...');
        
        try {
            $archivePath = $engine->createArchive();
            $this->info("Archive created: {$archivePath}");

            $this->info('Encrypting archive using AES-256...');
            $encryptedPath = $engine->gpgEncrypt($archivePath);
            $this->info("Encrypted backup saved to: {$encryptedPath}");

            $this->info('Verifying backup restore integrity...');
            $isViable = $engine->verifyRestore($encryptedPath);

            if ($isViable) {
                $this->info('✓ Backup Restore Verification Passed successfully.');
                return Command::SUCCESS;
            } else {
                $this->error('CRITICAL ERROR: Backup file decrypted, but did not pass verification.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Backup job failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
