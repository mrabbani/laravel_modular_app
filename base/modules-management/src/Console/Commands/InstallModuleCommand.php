<?php namespace Mrabbani\ModuleManager\Console\Commands;

use Illuminate\Console\Command;

class InstallModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:install {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install module';

    /**
     * @var array
     */
    protected $container = [];

    /**
     * @var array
     */
    protected $dbInfo = [];

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $app;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->app = app();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * Migrate tables
         */
        \ModulesManagement::enableModule($this->argument('alias'));
        \ModulesManagement::modifyModuleAutoload($this->argument('alias'));

        $this->line('Migrate database...');
        \Artisan::call('module:migrate', ['alias' => $this->argument('alias')]);
        $this->line('Install module dependencies...');
        $this->registerInstallModuleService();

        $this->info("\nModule " . $this->argument('alias') . " installed.");
    }

    protected function registerInstallModuleService()
    {

        $module = get_module_information($this->argument('alias'));
        $namespace = str_replace('\\\\', '\\', array_get($module, 'namespace', '') . '\Providers\InstallModuleServiceProvider');
        if(class_exists($namespace)) {
            $this->app->register($namespace);
            save_module_information($module, [
                'installed' => true
            ]);
        }

    }
}
