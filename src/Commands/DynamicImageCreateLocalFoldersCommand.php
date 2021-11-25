<?php

namespace Salivity\Laravel8DynamicImage\Commands;

use Illuminate\Console\Command;


class DynamicImageCreateLocalFoldersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic_image:create_local_folders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the folders for the local cache and watermark images.';

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
        $this->info("creating folders for the dynamic image package");
        
        $dynamicImage->createPackageFolders();
    }
}