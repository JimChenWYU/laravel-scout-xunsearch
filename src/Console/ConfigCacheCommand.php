<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use JimChen\LaravelScout\XunSearch\IniConfiguration;
use JimChen\LaravelScout\XunSearch\XunSearchClient;

class ConfigCacheCommand extends Command
{
    use HasConfiguration;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'xunsearch:config:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config cache command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(XunSearchClient $client, IniConfiguration $iniConfiguration)
    {
        $this->call('xunsearch:config:clear');

        $schemaConfig = $this->getFreshConfiguration();

        foreach ($schemaConfig as $schema => $config) {
            $this->files->put(
                $iniConfiguration->getCachedConfigPath($schema . '.ini'),
                $client->generateIniConfig($schema)
            );
        }

        $this->info('Configuration cached successfully!');
    }
}
