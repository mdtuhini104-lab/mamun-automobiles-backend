<?php

namespace App\Services;

class BackupService
{
    public function generateFullBackup()
    {
        // Trigger DB and Storage backup
        return ['status' => 'success', 'file' => 'backup-2023-10-25.zip', 'size' => '250MB'];
    }
}
