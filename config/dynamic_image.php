<?php

/**
 * dynamic image config
 * 
 */
return [
    "default_quality" => [
        "image/jpeg" => 75,
        "image/png" => 9,
        "image/webp" => 75
    ],
    
    "fill_colour" => "#000000", // default black
    
    // filter defaults
    "filter_smooth_amount" => 50,
    
    // effects both input and output
    "max_image_width" => 8000,
    "max_image_height" => 8000,
    
    // throttling
    /**
     * a good limit for cpu credits for a day would be about 4 times the hour
     * rate
     */
    "max_cpu_credits_per_hour" => 10000,
    "max_cpu_credits_per_day" => 100000,
    
    /**
     * depending of the size of the site and the server power ajust the cpu 
     * credits cache duration
     */
    "cron_cache_clear" => '0 * * * *',
    
    
    /**
     * fixed images
     * 
     * only allow dynamic images size we specify here
     */
    "use_fixed_image" => TRUE,
    
    /**
     * supported sizes for the fixed images width x height
     */
    "fixed_image_sizes" => [
        [100, 100],
        [200, 200],
        [300, 300]
        
    ],
    
    /**
     * cache
     * 
     */
    "cache_item_size" => 100,
    
    /**
     * watermark
     */
    "watermark_enabled" => TRUE,
    
    "watermark_image" => "logo.png", // must be a png file located in the watermark folder    
    
    /**
     * x and y order
     * 
     * 9 possible positions
     * 
     * 
     * left_top
     * left_center
     * left_bottom
     * 
     * top_center
     * center_center
     * bottom_center
     * 
     * right_top
     * right_center
     * right_bottom
     */
    "watermark_position" => "right_bottom",
    "watermark_percentage" => 50, // the amount of scale relative to the destination width and height
    
    
];
