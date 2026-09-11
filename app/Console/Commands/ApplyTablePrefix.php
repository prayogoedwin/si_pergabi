<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyTablePrefix extends Command
{
    protected $signature = 'pergabi:apply-table-prefix';

    protected $description = 'Rename existing unprefixed tables to use the configured DB prefix (prgb_)';

    public function handle(): int
    {
        $connection = DB::connection();
        $prefix = $connection->getTablePrefix();
        $driver = $connection->getDriverName();

        if ($prefix === '') {
            $this->warn('No table prefix is configured.');

            return self::SUCCESS;
        }

        $tables = $this->tableNames($driver);

        if ($tables === []) {
            $this->info('No tables found.');

            return self::SUCCESS;
        }

        $renamed = 0;

        foreach ($tables as $table) {
            if (str_starts_with($table, $prefix)) {
                continue;
            }

            $target = $prefix.$table;

            if (in_array($target, $tables, true)) {
                $this->warn("Skipped {$table}: {$target} already exists.");

                continue;
            }

            $this->renameTable($driver, $table, $target);
            $this->line("Renamed {$table} → {$target}");
            $renamed++;
        }

        $this->info($renamed === 0 ? 'All tables already use the prefix.' : "Renamed {$renamed} table(s).");

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function tableNames(string $driver): array
    {
        if ($driver === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
                ->pluck('name')
                ->all();
        }

        $column = 'Tables_in_'.DB::connection()->getDatabaseName();

        return collect(DB::select('SHOW TABLES'))
            ->map(fn (object $row) => $row->{$column} ?? array_values((array) $row)[0])
            ->all();
    }

    private function renameTable(string $driver, string $from, string $to): void
    {
        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE "'.$from.'" RENAME TO "'.$to.'"');

            return;
        }

        DB::statement('RENAME TABLE `'.$from.'` TO `'.$to.'`');
    }
}
