<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class SubscribeTableCommand extends Command
{
    protected $signature = 'weapp:subscribe:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the wechat app subscribe notifications table';

    /**
     * The filesystem instance.
     *
     * @var Filesystem $files
     */
    protected $files;

    /**
     * @var Composer $composer
     */
    protected $composer;

    /**
     * Create a new notifications table command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $fullPath = $this->createBaseMigration();

        $this->files->put($fullPath, $this->files->get(__DIR__ . '/stubs/weapp_subscribe_notifications_table.stub'));

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the notifications.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_weapp_subscribe_notifications_table';

        $path = $this->laravel->databasePath() . '/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }
}