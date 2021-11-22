<?php

/**
 * api routes
 */

Route::get('/test_api_route', function () {
    return ["test_key" => "test_value"];
});