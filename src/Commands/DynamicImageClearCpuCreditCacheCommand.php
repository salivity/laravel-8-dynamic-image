<?php

namespace Salivity\Laravel8DynamicImage\Commands;

use Illuminate\Console\Command;


class DynamicImageClearCpuCreditCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic_image:clear_cpu_credit_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes old cpu credit data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Support\DripEmailer  $drip
     * @return mixed
     */
    public function handle(\Salivity\Laravel8DynamicImage\DynamicImage $dynamicImage)
    {
        $this->info("clearing old cpu credits");
        
        $dynamicImage->clearOldCpuCredits();
    }
}