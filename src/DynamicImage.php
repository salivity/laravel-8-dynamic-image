<?php

namespace Salivity\Laravel8DynamicImage;

/**
 * DynamicImage
 * 
 * 
 * 
 */
class DynamicImage  {
    
    /**
     * $this->_destinationImage
     * 
     * holds the final image to return to the client
     * 
     * @var type
     */
    private $_destinationImage = NULL;
    
    /**
     * $this->_sourceImage
     * 
     * holds the image as it is contained on the storage system
     */
    private $_sourceImage = NULL;
    
    private $_mimetype = NULL; // header compatible mimetype
    
    private $_quality = NULL; // the output quality setting of the final output

    private $_cpuCredits = 0; // calculate before convertion the amount of cpu power required
    
  
    
    /**
     * __construct
     * 
     */
    public function __construct(){
        
        $this->checkPublishedFiles();
        
    }
    
    
    
    /**
     * cacheCpuCredits
     * 
     * combines all the data stored in the DynamicImageCpuCredits to entries in
     * DynamicImageCpuCreditsCache
     */
    public function cacheCpuCredits(){
        
        $ipAddresses = \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCredit::groupBy('ip_address_v4')->pluck("ip_address_v4"); 
        
        // use minutes to select data
        
        foreach($ipAddresses as $key => $ipAddress){

            // Hour Range
            $fromHour = \Carbon\Carbon::now()->subMinutes(60);
            $toHour = \Carbon\Carbon::now();
            
            $cpuCreditsHour = 0;
                    
            $cpuCreditsHourValues = \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCredit::
                    where("ip_address_v4", "=", $ipAddress)->
                    whereBetween('created_at', [$fromHour, $toHour])
                    ->pluck("credits");
            
            $cpuCreditsHour = array_sum($cpuCreditsHourValues->toArray());
            
            
            // Day Range
            $fromDay = \Carbon\Carbon::now()->subMinutes(1440);
            $toDay = \Carbon\Carbon::now();
            
            $cpuCreditsDay = 0;
                    
            $cpuCreditsDayValues = \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCredit::
                    where("ip_address_v4", "=", $ipAddress)->
                    whereBetween('created_at', [$fromDay, $toDay])
                    ->pluck("credits");
            
            $cpuCreditsDay = array_sum($cpuCreditsDayValues->toArray());
            
            
            // save to database
            $dynamicImageCpuCreditCacheHour = \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCreditsCache::firstOrCreate(["ip_address_v4" => $ipAddress, "minutes" => "60"]);
            $dynamicImageCpuCreditCacheHour->credits = $cpuCreditsHour;
            $dynamicImageCpuCreditCacheHour->minutes = 60;
            $dynamicImageCpuCreditCacheHour->save();
            
            $dynamicImageCpuCreditCacheDay = \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCreditsCache::firstOrCreate(["ip_address_v4" => $ipAddress, "minutes" => "1440"]);
            $dynamicImageCpuCreditCacheDay->minutes = 1440;
            $dynamicImageCpuCreditCacheDay->credits = $cpuCreditsDay;
            $dynamicImageCpuCreditCacheDay->save();
        }
        
    }
    
    /**
     * clearOldCpuCredits
     * 
     * removes the old cpu credits frmo the database
     */
    public function clearOldCpuCredits(){
        Models\DynamicImageCpuCredit::select("id")->where("created_at", "<", \Carbon\Carbon::now()->subMinutes(10))->delete();
    }
    
    /**
     * clearCache
     * 
     * removes all the files in the images cache folder
     * 
     * @returns {integer}
     */
    public function clearCache(){
        $count = 0;
        foreach (new \DirectoryIterator(storage_path("packages/dynamic_image/cache/")) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
                $count++;
            }
        }
        
        return $count;
    }
    
    
    
    /**
     * calculateCpuCredits
     * 
     * Determine the amount of power required and available free cpu credits
     * 
     * @param {string} $filename
     * @param {string} $options
     * @throws {\Exception}
     */
    public function calculateCpuCredits($filename, $options){
        
        $this->_cpuCredits;
        
        // format
        switch($this->_mimetype){
            case "image/jpeg":
                $this->_cpuCredits += 1;
                break;
            case "image/webp":
                $this->_cpuCredits += 1;
                break;
            case "image/png":
                $this->_cpuCredits += 1;
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
        
        // file size
        $bytes = filesize($filename);
        
        // 1mb = 1 credit or 1 credit if < 1mb
        $this->_cpuCredits += ceil($bytes / 1024 / 1024);
        
        // dimensions
        $originalWidth  =   imagesx($this->_sourceImage);       
        $originalHeight =   imagesy($this->_sourceImage);
        
        $this->_cpuCredits += ceil(($originalWidth * $originalHeight) / 1000000);
        
        // quality
        // quality works best with the width and height
        switch($this->_mimetype){
            case "image/jpeg":
                $this->_cpuCredits += ceil($this->_quality / 10) * ceil(($options["width"] * $options["height"]) / 1000000);
                break;
            case "image/webp":
                $this->_cpuCredits += ceil($this->_quality / 10) * ceil(($options["width"] * $options["height"]) / 1000000);
                break;
            case "image/png":
                $this->_cpuCredits += ceil(($this->_quality * 11.1) / 10) * ceil(($options["width"] * $options["height"]) / 1000000);
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
        
        // filters
        
        // negative effect
        if(isset($options["filter_negate"]) AND $options["filter_negate"] == TRUE){
            $this->_cpuCredits += 10;
        }
        
        // greyscale
        if(isset($options["filter_greyscale"]) AND $options["filter_greyscale"] == TRUE){
            $this->_cpuCredits += 10;
        }
        
        // edge detect
        if(isset($options["filter_edge_detect"]) AND $options["filter_edge_detect"] == TRUE){
            $this->_cpuCredits += 10;
        }
        
        // emboss
        if(isset($options["filter_emboss"]) AND $options["filter_emboss"] == TRUE){
            $this->_cpuCredits += 10;
        }
        
        
        // check available cpu credits for each hour and day
        $dynamicImageCpuCreditsCacheHour =  \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCreditsCache::where("ip_address_v4", "=", request()->ip())->where("minutes", "=", "60")->first();
        $dynamicImageCpuCreditsCacheDay =   \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCreditsCache::where("ip_address_v4", "=", request()->ip())->where("minutes", "=", "1440")->first();
        
        // check first
        if($dynamicImageCpuCreditsCacheHour AND $dynamicImageCpuCreditsCacheDay){
            
            if(
                    ($dynamicImageCpuCreditsCacheHour->credits + $this->_cpuCredits) >= 
                    config("dynamic_image.max_cpu_credits_per_hour")
                    ){
                // Exceeded the maximium cpu credits in an hour
                throw new \Exception(__("dynamic_image.error_3"));
            }



            if(
                    ($dynamicImageCpuCreditsCacheHour->credits + $this->_cpuCredits) >= 
                    config("dynamic_image.max_cpu_credits_per_day")
                    ){
                // Exceeded the maximium cpu credits in an hour
                throw new \Exception(__("dynamic_image.error_4"));
            }
            
        }
        
        // create entry in table on this action
        
        $dynamicImageCpuCredit = new \Salivity\Laravel8DynamicImage\Models\DynamicImageCpuCredit();
        $dynamicImageCpuCredit->ip_address_v4 = request()->ip();
        $dynamicImageCpuCredit->credits = $this->_cpuCredits;
        $dynamicImageCpuCredit->save();
    }
    
    /**
     * checkPublishedFiles
     * 
     * Makes sure the Laravel installation has the required published files from the package
     * 
     * @throws \Exception
     */
    public function checkPublishedFiles(){
        // required published files
        if (file_exists(resource_path('lang/en/dynamic_image.php'))) {
            // everything ok
        } else {
            throw new \Exception("You must publish the language file.");
        }
        
        if (file_exists(config_path('dynamic_image.php'))) {
            // everything ok
        } else {
            throw new \Exception("You must publish the configuration file.");
        }
    }


    /**
     * createPackageFolders
     * 
     * creates the folders for the cache
     */
    public function createPackageFolders(){
        
        // supress errors as common folder for all packages
        @mkdir(storage_path("packages/"));
        @mkdir(storage_path("packages/dynamic_image/"));
        @mkdir(storage_path("packages/dynamic_image/cache"));
        @mkdir(storage_path("packages/dynamic_image/watermark"));

    }
    
    /**
     * applyImageFilters
     * 
     * @param {array} $options
     */
    public function applyImageFilters($options){
        
        // negative effect
        if(isset($options["filter_negate"]) AND $options["filter_negate"] == TRUE){
            imagefilter($this->_destinationImage, IMG_FILTER_NEGATE);
        }
        
        // greyscale
        if(isset($options["filter_greyscale"]) AND $options["filter_greyscale"] == TRUE){
            imagefilter($this->_destinationImage, IMG_FILTER_GRAYSCALE);
        }
        
        // edge detect
        if(isset($options["filter_edge_detect"]) AND $options["filter_edge_detect"] == TRUE){
            imagefilter($this->_destinationImage, IMG_FILTER_EDGEDETECT);
        }
        
        // emboss
        if(isset($options["filter_emboss"]) AND $options["filter_emboss"] == TRUE){
            imagefilter($this->_destinationImage,  IMG_FILTER_EMBOSS);
        }
        
        
        
    }
    
    /**
     * checkMimetype
     * 
     * @param {string} $filename
     */
    public function checkMimetype($filename){

        $mimetype = mime_content_type($filename);

        switch($mimetype){
            case "image/webp":
            case "image/png":
            case "image/jpeg":
                $this->_mimetype =  $mimetype;
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
        
        
        
        
    }
    
    /**
     * hashOptions
     * 
     * creates a filename from the options array, require only the required data to 
     * create the image
     * 
     * @return {string}
     */
    public function hashOptions($options){
        $serializedJson = json_encode($options);
        
        $cacheFilename = hash("sha256", $serializedJson);
        
        return $cacheFilename;
    }
    
    /**
     * checkInCache
     * 
     * check to see if the file already exists in the cache
     * 
     * @param {array} $options
     * 
     */
    public function checkInCache($options){
        
        $cacheFilename = $this->hashOptions($options);
        
        if(file_exists(storage_path("packages/dynamic_image/cache/{$cacheFilename}"))){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * cacheImage
     * 
     * @param {array} $options
     */
    public function cacheImage($options){
        $cacheFilename = $this->hashOptions($options);

        if(
                $this->checkInCache($options) === FALSE AND 
                $this->countCacheSize() < config("dynamic_image.cache_item_size")){
            $this->writeBlob(storage_path("packages/dynamic_image/cache/{$cacheFilename}"));
        }
    }
    
    /**
     * getCachedImage
     */
    public function getCachedImage($options){
        
        $cacheFilename = $this->hashOptions($options);
        
        if($this->checkInCache($options) === TRUE){
            // create image object from file
            header("Content-type: {$this->_mimetype}");
        
            $myfile = fopen(storage_path("packages/dynamic_image/cache/{$cacheFilename}"), "r") or die("Unable to open file!");
            echo fread($myfile,filesize(storage_path("packages/dynamic_image/cache/{$cacheFilename}")));
            fclose($myfile);
            die;
        }
    }

    /**
     * countCacheSize
     * 
     * @return {integer}
     */
    public function countCacheSize(){
        $fi = new \FilesystemIterator(storage_path("packages/dynamic_image/cache/"), \FilesystemIterator::SKIP_DOTS);
        return iterator_count($fi);
    }





    public function checkFilesize(){}
    
    /**
     * checkImageDimensions
     * 
     * @param {integer} $width
     * @param {integer} $height
     * 
     */
    public function checkImageDimensions($width, $height){

        if($width > config("dynamic_image.max_image_width") OR $height > config("dynamic_image.max_image_height") ){
            // Exceeded the maximium image dimensions
            throw new \Exception(__("dynamic_image.error_2"));
        }

    }
    
    /**
     * detectFixedSize
     * 
     * @param {integer} $width
     * @param {integer} $height
     */
    public function detectFixedSize(int $width, int $height){
        
        if(config("dynamic_image.use_fixed_image") === TRUE){
            
            foreach(config("dynamic_image.fixed_image_sizes") as $key => $value){
                if($value[0] === $width AND $value[1] === $height){
                    return TRUE;
                }
            }
            
            throw new \Exception(__("dynamic_image.error_5"));
            
        }
        
    }
    
    public function createImageByUrl(){}
    
    /**
     * createImageByFile
     * 
     * @param {string} $filename The filename of a file located in the local storage disk
     * 
     */
    public function createImageByFile($filename){

        $this->checkMimetype($filename);
        
        $image = NULL;
        
        switch($this->_mimetype){
            case "image/jpeg":
                $this->_sourceImage = imagecreatefromjpeg($filename);
                break;
            case "image/webp":
                $this->_sourceImage = imagecreatefromwebp($filename);
                break;
            case "image/png":
                $this->_sourceImage = imagecreatefrompng($filename);
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
        
        
    }
    
    /**
     * 
     * hexColorAllocate
     * 
     * TODO create helper function package (gd_image_helpers.php)
     * 
     * @param {GDImage} $im
     * @param {string} $hex
     * @return type
     */
    public function hexColorAllocate($im, $hex){
        $hex = ltrim($hex,'#');
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
        return imagecolorallocate($im, $r, $g, $b); 
    }
    
    /**
     * scaleImageBySize TODO rename
     * 
     * @param {array} $options
     */
    public function processImage($options){
        
        
        if(
                isset($options["width"]) AND 
                isset($options["height"]) AND 
                is_numeric($options["width"]) AND 
                is_numeric($options["height"])
            ){
            
            if($options["maintain_aspect_ratio"] == TRUE){
                
                // create a border around image
                $originalWidth  =   imagesx($this->_sourceImage);       
                $originalHeight =   imagesy($this->_sourceImage);
                
                $this->checkImageDimensions($originalWidth, $originalHeight);
                
                $maintainAspectRatio = $this->calculateAspectRatio($originalWidth, $originalHeight, $options["width"], $options["height"]);
                
                $this->checkImageDimensions($maintainAspectRatio["width"], $maintainAspectRatio["height"]);
                
                // imagecreatetruecolor create a black image
                $destinationImage = imagecreatetruecolor($options["width"], $options["height"]);
                
                
                // fill the image with a solid colour
                imagefill($destinationImage, 0,0, $this->hexColorAllocate($destinationImage, $options["fill_colour"] ?? config("dynamic_image.fill_colour")));
                
                $repositionX = floor(($options["width"] - $maintainAspectRatio["width"]) / 2);
                $repositionY = floor(($options["height"] - $maintainAspectRatio["height"]) / 2);
                
                imagecopyresampled(
                        $destinationImage, 
                        $this->_sourceImage, 
                        $repositionX, 
                        $repositionY, 
                        0,
                        0,
                        $maintainAspectRatio["width"], 
                        $maintainAspectRatio["height"], 
                        $originalWidth, 
                        $originalHeight
                );
                
             
                
                $this->_destinationImage = $destinationImage;
                
                // apply image filters here
                $this->applyImageFilters($options);
                
                $this->applyWatermark($options);
        
            }else{

                // stretches the image
                
                $originalWidth  =   imagesx($this->_sourceImage);       
                $originalHeight =   imagesy($this->_sourceImage);
                
                // imagecreatetruecolor create a black image
                $destinationImage = imagecreatetruecolor($options["width"], $options["height"]);
                
                imagecopyresampled(
                        $destinationImage, 
                        $this->_sourceImage, 
                        0, 
                        0, 
                        0,
                        0,
                        $options["width"], 
                        $options["height"], 
                        $originalWidth, 
                        $originalHeight
                );
                
                 
                $this->_destinationImage = $destinationImage;
                
                // apply image filters here
                $this->applyImageFilters($options);
                
                $this->applyWatermark($options);

            }
            
        }
        
        
        $this->cacheImage($options);        
        
        
    }
    
    /**
     * applyWatermark
     * 
     * @param {array} $options
     */
    public function applyWatermark(array $options){
        
        
        if(config("dynamic_image.watermark_enabled") === TRUE){


            $watermarkWidth = $options["width"] / 100 * config("dynamic_image.watermark_percentage");
            $watermarkHeight = $options["height"] / 100 * config("dynamic_image.watermark_percentage");

            $watermarkFilename = config('dynamic_image.watermark_image');
            $watermarkImage = imagecreatefrompng(storage_path("packages/dynamic_image/watermark/{$watermarkFilename}"));

            $targetWatermarkX = 0;
            $targetWatermarkY = 0;

            // calculate the possible 9 positions
            switch(config("dynamic_image.watermark_position")){
                case "left_top":
                    $targetWatermarkX = 0;
                    $targetWatermarkY = 0;
                    break;
                case "left_center":
                    $targetWatermarkX = 0;
                    $targetWatermarkY = (($options["height"] / 2) - ($watermarkHeight / 2));
                    break;
                case "left_bottom":
                    $targetWatermarkX = 0;
                    $targetWatermarkY = $options["height"] - $watermarkHeight;
                    break;
                case "top_center":
                    $targetWatermarkX = (($options["width"] / 2) - ($watermarkWidth / 2));
                    $targetWatermarkY = 0;
                    break;
                case "center_center":
                    $targetWatermarkX = (($options["width"] / 2) - ($watermarkWidth / 2));
                    $targetWatermarkY = (($options["height"] / 2) - ($watermarkHeight / 2));
                    break;
                case "bottom_center":
                    $targetWatermarkX = (($options["width"] / 2) - ($watermarkWidth / 2));
                    $targetWatermarkY = $options["height"] - $watermarkHeight;
                    break;
                case "right_top":
                    $targetWatermarkX = $options["width"] - $watermarkWidth;
                    $targetWatermarkY = 0;
                    break;
                case "right_center":
                    $targetWatermarkX = $options["width"] - $watermarkWidth;
                    $targetWatermarkY = (($options["height"] / 2) - ($watermarkHeight / 2));
                    break;
                case "right_bottom":
                    $targetWatermarkX = $options["width"] - $watermarkWidth;
                    $targetWatermarkY = $options["height"] - $watermarkHeight;
                    break;
            }

            // resample and appply water mark
            imagecopyresampled(
                $this->_destinationImage,
                $watermarkImage, 
                $targetWatermarkX, 
                $targetWatermarkY, 
                0, 
                0, 
                $watermarkWidth, 
                $watermarkHeight, 
                imagesx($watermarkImage), 
                imagesy($watermarkImage));
        }
    }
    
    /**
     * processByFlysystem
     * 
     * @param {array} $options
     */
    public function processByFlysystem($options){
        
        $sourceImage = storage_path("{$options['key']}");
        
        $this->detectFixedSize($options["width"], $options["height"]);
        
        // create a universal php gdimage object
        $this->createImageByFile($sourceImage);
        
        // mimetype is required to function
        $this->validateQualitySetting($options);
        
        
        $this->getCachedImage($options); // don't create a cpu credits entry for direct file access   
        
        $this->calculateCpuCredits($sourceImage, $options);
        
        // scale first for performance
        $this->processImage($options);
        
        
    }
    
    /**
    * calculate_aspect_ratio
    * 
    * Manipulate width and Height while maintaining aspect ratio
    *
    * TODO create helper function package (size_helpers.php)
    * 
    * @param  {integer} $width Original Width
    * @param  {integer} $height Original Height
    * @param  {integer} $maxwidth New Width
    * @param  {integer} $maxheight New Height
    * 
    * @return {array}
    */
   public function calculateAspectRatio($width, $height, $maxwidth, $maxheight)
   {
       if($width != $height)
       {
           if($width > $height)
           {
               $t_width = $maxwidth;
               $t_height = (($t_width * $height)/$width);
               //fix height
               if($t_height > $maxheight)
               {
                   $t_height = $maxheight;
                   $t_width = (($width * $t_height)/$height);
               }
           }
           else
           {
               $t_height = $maxheight;
               $t_width = (($width * $t_height)/$height);
               //fix width
               if($t_width > $maxwidth)
               {
                   $t_width = $maxwidth;
                   $t_height = (($t_width * $height)/$width);
               }
           }
       } else {
           $t_width = $t_height = min($maxheight,$maxwidth);

       }

       return array('height'=> (int)$t_height,'width'=> (int)$t_width);


   }
   
    /**
     * validateQualitySetting
     * 
     * make sure the quality settings is compatible with the GDImage
     * 
     * @param {array} $options
     * @return {integer} The percentage for cpu credits function
     */
    public function validateQualitySetting(array $options){
        
        if(isset($options["quality"]) AND is_numeric($options["quality"])){
            switch($this->_mimetype){
                case "image/webp":
                    if($options["quality"] >= 0 AND $options["quality"] <= 100){
                        $this->_quality = $options["quality"];
                    }else{
                        $this->_quality = config("dynamic_image.default_quality.image/webp"); // default quality for webp
                    }
                    break;
                case "image/jpeg":
                    if($options["quality"] >= 0 AND $options["quality"] <= 100){
                        $this->_quality = $options["quality"];
                    }else{
                        $this->_quality = config("dynamic_image.default_quality.image/jpeg"); // default quality for jpeg
                    }
                    break;
                case "image/png":
                    if($options["quality"] >= 0 AND $options["quality"] <= 9){
                        $this->_quality = $options["quality"];
                    }else{
                        $this->_quality = config("dynamic_image.default_quality.image/png");; // default quality for png
                    }
                    break;
            }
        }else{
            
            // default quality by mimetype
             switch($this->_mimetype){
                case "image/webp":
                    $this->_quality = config("dynamic_image.default_quality.image/webp"); // default quality for webp
                    break;
                case "image/jpeg":
                    $this->_quality = config("dynamic_image.default_quality.image/jpeg"); // default quality for jpeg
                    break;
                case "image/png":
                    $this->_quality = config("dynamic_image.default_quality.image/png");; // default quality for png
                    break;
            }
            
        }
        
        
    }
    
    /**
     * returnBlob
     * 
     * returns the HTTP Request to the client
     */
    public function returnBlob(){
        header("Content-type: {$this->_mimetype}");
        
        switch($this->_mimetype){
            case "image/jpeg":
                imagejpeg($this->_destinationImage ?? $this->_sourceImage, NULL, $this->_quality);
                break;
            case "image/png":
                imagepng($this->_destinationImage ?? $this->_sourceImage, NULL, $this->_quality);
                break;
            case "image/webp":
                imagewebp($this->_destinationImage ?? $this->_sourceImage, NULL, $this->_quality);
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
         
    }
    
    /**
     * writeBlob
     */
    public function writeBlob($filename){
        switch($this->_mimetype){
            case "image/jpeg":
                imagejpeg($this->_destinationImage ?? $this->_sourceImage, $filename, $this->_quality);
                break;
            case "image/png":
                imagepng($this->_destinationImage ?? $this->_sourceImage, $filename, $this->_quality);
                break;
            case "image/webp":
                imagewebp($this->_destinationImage ?? $this->_sourceImage, $filename, $this->_quality);
                break;
            default:
                // Unsupported File Type
                throw new \Exception(__("dynamic_image.error_1"));
        }
    }
}
