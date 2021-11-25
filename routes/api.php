<?php

/**
 * api routes
 * 
 * default unauthenticated routes
 */


Route::prefix('api/v1/dynamic_image')->group(function () {
    Route::post('/get_image_by_flysystem', [Salivity\Laravel8DynamicImage\Http\Controllers\Api\DynamicImageApiController::class, 'getImageByFlysystem']);
});
