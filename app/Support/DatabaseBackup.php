<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseBackup
{
    /**
     * @var list<string>
     */
    private const SKIP_TABLES = [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'password_reset_tokens',
        'sessions',
    ];

    public function directory(): string
    {
        $path = storage_path(app()->environment('testing') ? 'app/private/testing-backups' : 'app/private/backups');

        File::ensureDirectoryExists($path);

        return $path;
    }

    /**
     * @return list<array{name: string, path: string, size: int, modified_at: Carbon}>
     */
    public function list(): array
    {
        return Collection::make(File::files($this->directory()))
            ->filter(fn ($file): bool => (bool) preg_match('/^ssml-backup-.+\.(sql|sqlite)$/', $file->getFilename()))
            ->sortByDesc(fn ($file): int => $file->getMTime())
            ->values()
            ->map(fn ($file): array => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified_at' => Carbon::createFromTimestamp($file->getMTime()),
            ])
            ->all();
    }

    public function create(): string
    {
        $stamp = now()->format('Y-m-d-His');

        if ($this->sqliteFilePath() !== null) {
            $name = 'ssml-backup-'.$stamp.'.sqlite';
            $destination = $this->directory().DIRECTORY_SEPARATOR.$name;

            if (! copy($this->sqliteFilePath(), $destination)) {
                throw new RuntimeException('Unable to copy the SQLite database file.');
            }

            return $name;
        }

        $name = 'ssml-backup-'.$stamp.'.sql';
        $destination = $this->directory().DIRECTORY_SEPARATOR.$name;

        if (File::put($destination, $this->exportSql()) === false) {
            throw new RuntimeException('Unable to write the database backup file.');
        }

        return $name;
    }

    public function restore(string $filename): void
    {
        $path = $this->resolvedPath($filename);

        if (str_ends_with(strtolower($filename), '.sqlite')) {
            $this->restoreSqliteFile($path);

            return;
        }

        $sql = File::get($path);

        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('The backup file is empty.');
        }

        DB::transaction(function () use ($sql): void {
            $this->disableForeignKeys();

            try {
                foreach ($this->statements($sql) as $statement) {
                    DB::unprepared($statement);
                }
            } finally {
                $this->enableForeignKeys();
            }
        });
    }

    public function resolvedPath(string $filename): string
    {
        if (! preg_match('/^ssml-backup-[A-Za-z0-9._-]+\.(sql|sqlite)$/', $filename)) {
            throw new RuntimeException('Invalid backup filename.');
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$filename;

        if (! File::isFile($path)) {
            throw new RuntimeException('Backup file not found.');
        }

        return $path;
    }

    private function sqliteFilePath(): ?string
    {
        if (config('database.default') !== 'sqlite') {
            return null;
        }

        $path = config('database.connections.sqlite.database');

        if (! is_string($path) || $path === '' || $path === ':memory:' || ! is_file($path)) {
            return null;
        }

        return $path;
    }

    private function restoreSqliteFile(string $path): void
    {
        $database = $this->sqliteFilePath();

        if ($database === null) {
            throw new RuntimeException('SQLite file restore is only available when the application uses a file-based SQLite database.');
        }

        DB::disconnect();

        if (! copy($path, $database)) {
            throw new RuntimeException('Unable to restore the SQLite database file.');
        }

        DB::reconnect();
    }

    private function exportSql(): string
    {
        $driver = DB::connection()->getDriverName();
        $lines = [
            '-- SSML laboratory backup',
            '-- Created at '.now()->toDateTimeString(),
            '',
        ];

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
        } else {
            $lines[] = 'PRAGMA foreign_keys=OFF;';
        }

        foreach ($this->dataTables() as $table) {
            $lines[] = $this->truncateStatement($table, $driver);

            foreach (DB::table($table)->get() as $row) {
                $lines[] = $this->insertStatement($table, (array) $row, $driver);
            }
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        } else {
            $lines[] = 'PRAGMA foreign_keys=ON;';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @return list<string>
     */
    private function dataTables(): array
    {
        $tables = Schema::getTableListing();
        $connection = Schema::getConnection()->getTablePrefix();

        $names = [];

        foreach ($tables as $table) {
            $name = str_starts_with($table, $connection) ? substr($table, strlen($connection)) : $table;

            if (str_contains($name, '.')) {
                $name = substr($name, strrpos($name, '.') + 1);
            }

            if (in_array($name, self::SKIP_TABLES, true) || str_starts_with($name, 'sqlite_')) {
                continue;
            }

            $names[] = $name;
        }

        return array_values(array_unique($names));
    }

    private function truncateStatement(string $table, string $driver): string
    {
        $quoted = $this->quoteIdentifier($table, $driver);

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return 'DELETE FROM '.$quoted.';';
        }

        return 'DELETE FROM '.$quoted.';';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function insertStatement(string $table, array $row, string $driver): string
    {
        $columns = [];
        $values = [];

        foreach ($row as $column => $value) {
            $columns[] = $this->quoteIdentifier((string) $column, $driver);
            $values[] = $this->quoteValue($value);
        }

        return 'INSERT INTO '.$this->quoteIdentifier($table, $driver)
            .' ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).');';
    }

    private function quoteIdentifier(string $name, string $driver): string
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return '`'.str_replace('`', '``', $name).'`';
        }

        return '"'.str_replace('"', '""', $name).'"';
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }

    /**
     * @return list<string>
     */
    private function statements(string $sql): array
    {
        $statements = [];

        foreach (preg_split('/;\s*\R/', $sql) ?: [] as $statement) {
            $statement = trim($statement);

            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            $statements[] = $statement.';';
        }

        return $statements;
    }

    private function disableForeignKeys(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

            return;
        }

        DB::unprepared('PRAGMA foreign_keys=OFF');
    }

    private function enableForeignKeys(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');

            return;
        }

        DB::unprepared('PRAGMA foreign_keys=ON');
    }
}
