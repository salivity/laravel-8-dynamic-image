<?php 

namespace Salivity\PackageTemplate\Providers;

use Illuminate\Support\ServiceProvider;

// commands
use Courier\Console\Commands\InstallCommand;
use Courier\Console\Commands\NetworkCommand;

class PackageTemplateServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    
    /**
     * __construct
     * 
     * @param {\Illuminate\Foundation\Application} $app
     * 
     */
    public function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        
        // laravel auto-discovery
        
        // console commands
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Salivity\PackageTemplate\Commands\TestCommand::class
            ]);
        }
        
        // facade
        
        $this->app->bind('package_template',function(){
            return new \Salivity\PackageTemplate\TestClass();
        });
        
        // config
        /**
         * notes
         * 
         * Avoid using the merge config function as it can cause problems
         */
        $this->publishes([
            __DIR__.'/../../config/package_template.php' => config_path('package_template.php'),
        ]);

        // migrations
        /**
         * adds the migrations to the php artisan migrate
         */
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // routes
        
        // web
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        
        // api
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        
        // translation 
        /**
         * keep the same structure as the laravel translation files
         */
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'package_template');
        
        // views
        /**
         * views are accessed with the package_name:: prefix
         */
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'package_template');

        // public
        // run
        // php artisan vendor:publish --provider="Salivity\PackageTemplate\Providers\PackageTemplateServiceProvider" --tag="public"
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/package_template'),
        ], 'public');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {


    }
    
    public function display(){
        dd("WORKING");
    }
}
