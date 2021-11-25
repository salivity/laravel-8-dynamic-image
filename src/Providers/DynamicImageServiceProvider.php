<?php 

namespace Salivity\Laravel8DynamicImage\Providers;

use Illuminate\Support\ServiceProvider;

// commands
use Courier\Console\Commands\InstallCommand;
use Courier\Console\Commands\NetworkCommand;
use Salivity\Laravel8DynamicImage\DynamicImage;
use Illuminate\Console\Scheduling\Schedule;

class DynamicImageServiceProvider extends ServiceProvider {

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
        
        $this->app->bind(DynamicImage::class, function ($app) {
            return new DynamicImage();
        });
        
        /**
         * Avoid using the merge config function as it can cause problems
         */
        $this->publishes([
            __DIR__.'/../../config/dynamic_image.php' => config_path('dynamic_image.php'),
        ], 'config');
        
        /**
         * entire language translation files
         */
        $this->publishes([
            __DIR__.'/../../resources/lang/' => resource_path('lang/'),
        ], 'lang');
        

        
        // api
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        
        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Salivity\Laravel8DynamicImage\Commands\DynamicImageCacheCpuCreditsCommand::class,
                \Salivity\Laravel8DynamicImage\Commands\DynamicImageClearCpuCreditCacheCommand::class,
                \Salivity\Laravel8DynamicImage\Commands\DynamicImageCreateLocalFoldersCommand::class,
                \Salivity\Laravel8DynamicImage\Commands\DynamicImageCacheSizeCommand::class,
                \Salivity\Laravel8DynamicImage\Commands\DynamicImageCacheClearCommand::class
            ]);
        }
        
        // automated tasks
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            
            // list all the automated tasks here
            
            // for the best acuracy use every minute for the cpu credits calculation
            $schedule->command(\Salivity\Laravel8DynamicImage\Commands\DynamicImageCacheCpuCreditsCommand::class, [])->everyMinute();
            
            // dont' really need to run this command every minute
            $schedule->command(\Salivity\Laravel8DynamicImage\Commands\DynamicImageClearCpuCreditCacheCommand::class, [])->cron(config("dynamic_image.cron_cache_clear"));

        });
        
        
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
