<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception;

class CopySqliteToMongoDB extends Command
{
    protected $signature = 'db:copy-sqlite-to-mongodb {--dry-run : Report source document counts without writing to MongoDB}';

    protected $description = 'Copy student portal data from the SQLite source database to MongoDB';

    public function handle(): int
    {
        if (! extension_loaded('mongodb')) {
            $this->error('The MongoDB PHP extension is not enabled. Install ext-mongodb for this PHP version, then restart PHP and try again.');

            return self::FAILURE;
        }

        $dsn = config('database.connections.mongodb.dsn');

        if (blank($dsn)) {
            $this->error('Set MONGODB_URI or DB_URI in .env before connecting to MongoDB.');

            return self::FAILURE;
        }

        if (str_contains($dsn, '<db_password>')) {
            $this->error('Replace the <db_password> placeholder in your MongoDB URI with the Atlas database user password.');

            return self::FAILURE;
        }

        $source = DB::connection('sqlite_source');
        $mongo = DB::connection('mongodb');

        try {
            $mongo->getDatabase()->command(['ping' => 1]);
        } catch (Exception $exception) {
            $this->error('Could not connect to MongoDB: '.$exception->getMessage());

            return self::FAILURE;
        }

        $collections = ['users', 'activity_logs', 'active_sessions', 'records'];
        $dateFields = [
            'users' => ['email_verified_at', 'archived_at', 'created_at', 'updated_at'],
            'activity_logs' => ['occurred_at'],
            'active_sessions' => ['logged_in_at', 'last_activity_at', 'ended_at'],
            'records' => ['created_at', 'updated_at'],
        ];

        foreach ($collections as $collection) {
            if (! Schema::connection('sqlite_source')->hasTable($collection)) {
                $this->line("Skipping {$collection}: it does not exist in SQLite.");

                continue;
            }

            $rows = $source->table($collection)->get();
            $this->line("{$collection}: {$rows->count()} source documents.");

            if ($this->option('dry-run')) {
                continue;
            }

            $target = $mongo->getCollection($collection);

            foreach ($rows as $row) {
                $document = (array) $row;

                if (array_key_exists('id', $document)) {
                    $document['_id'] = $document['id'];
                    unset($document['id']);
                }

                foreach ($dateFields[$collection] as $field) {
                    if (isset($document[$field]) && ! $document[$field] instanceof UTCDateTime) {
                        $document[$field] = new UTCDateTime(
                            new DateTimeImmutable((string) $document[$field], new DateTimeZone('UTC')),
                        );
                    }
                }

                $target->replaceOne(
                    ['_id' => $document['_id']],
                    $document,
                    ['upsert' => true],
                );
            }

            $this->info("Copied {$rows->count()} documents into {$collection}.");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No MongoDB documents were written.');
        } else {
            $this->info('SQLite data copied. The SQLite source database was not changed.');
        }

        return self::SUCCESS;
    }
}
