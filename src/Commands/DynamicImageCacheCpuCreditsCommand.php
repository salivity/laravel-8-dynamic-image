<?php

namespace Salivity\Laravel8DynamicImage\Commands;

use Illuminate\Console\Command;


class DynamicImageCacheCpuCreditsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic_image:cache_cpu_credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges CPU Credit Entries for an optimal experience in middleware';

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
        $this->info("caching cpu credits");
        
        $dynamicImage->cacheCpuCredits();
    }
}