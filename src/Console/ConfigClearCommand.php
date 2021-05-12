<?php

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use JimChen\LaravelScout\XunSearch\IniConfiguration;

class ConfigClearCommand extends Command
{
    use HasConfiguration;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'xunsearch:config:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the configuration cache file';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config clear command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(IniConfiguration $iniConfiguration)
    {
        $schemaConfig = $this->getFreshConfiguration();

        foreach ($schemaConfig as $schema => $config) {
            $this->files->delete(
                $iniConfiguration->getCachedConfigPath($schema.'.ini')
            );
        }

        $this->info('Configuration cache cleared!');
    }
}
