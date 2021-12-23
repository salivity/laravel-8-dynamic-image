<?php

namespace Salivity\Laravel8DynamicImage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DynamicImageApiController extends Controller{
    
    /**
     * Storage (TODO) System functions
     */
    
    
    /**
     * /////////////////////////////////////////////////////////////////////////
     * 
     * Flysystem functions
     * 
     * /////////////////////////////////////////////////////////////////////////
     */
    
    /**
     * getImageByFlysystem
     * 
     * Uses the built in Laravel Flysystem directly
     * 
     * @param Request $request
     * 
     * @return {Blob}
     */
    public function getImageByFlysystem(
            Request $request, 
            \Salivity\Laravel8DynamicImage\DynamicImage $dynamicImage){
        
        $customErrorMessages = [];
        
        /* validate the form data */
        $validatedData = $request->validate([
            
        ], $customErrorMessages);
        
        
        
        
        
        $dynamicImage->processByFlysystem($request->all());
        
        
        
    }
    
}