<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Sauvegarde la base de données dans storage/backups/';

    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $path = storage_path('backups');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $fullPath = $path . '/' . $filename;

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($fullPath)
        );

        if ($password) {
            $cmd = sprintf(
                'MYSQL_PWD=%s mysqldump -h %s -P %s -u %s %s > %s 2>&1',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );
        }

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($fullPath)) {
            $size = round(filesize($fullPath) / 1024, 1);
            $this->info("✅ Backup créé : {$filename} ({$size} Ko)");
            return Command::SUCCESS;
        }

        $this->error("❌ Échec du backup. Code : {$returnCode}");
        if (!empty($output)) {
            $this->error(implode("\n", $output));
        }
        return Command::FAILURE;
    }
}
