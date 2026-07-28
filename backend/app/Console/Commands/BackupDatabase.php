<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database';
    protected $description = 'Export a mysqldump of the application database to storage/backups/';

    public function handle(): int
    {
        $db       = config('database.connections.mysql');
        $host     = $db['host'];
        $port     = $db['port'];
        $name     = $db['database'];
        $user     = $db['username'];
        $pass     = $db['password'];

        $dir      = storage_path('backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file     = $dir . DIRECTORY_SEPARATOR . $name . '_' . now()->format('Y-m-d_His') . '.sql';
        $passArg  = $pass ? '-p' . escapeshellarg($pass) : '';

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($user),
            $passArg,
            escapeshellarg($name),
            escapeshellarg($file)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Backup failed (exit {$exitCode}).");
            return self::FAILURE;
        }

        // Prune backups older than 30 days
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.sql') as $old) {
            if (filemtime($old) < strtotime('-30 days')) {
                unlink($old);
            }
        }

        $this->info('Database backed up: ' . basename($file));
        return self::SUCCESS;
    }
}
