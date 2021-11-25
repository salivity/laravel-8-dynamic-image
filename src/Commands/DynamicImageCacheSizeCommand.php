<?php

namespace Salivity\Laravel8DynamicImage\Commands;

use Illuminate\Console\Command;


class DynamicImageCacheSizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic_image:cache_size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $this->info("There are {$dynamicImage->countCacheSize()} image(s) in the cache.");
    }
}