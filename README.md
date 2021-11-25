# laravel 8 Dynamic Image

This project is for the creation of dynamic images using the internal flysystem in Laravel.
If you would like to use the more advanced storage system for cloud storage you need to 
install another package aswell. This dynamic image system is designed for an api based 
website.

You can not change the filetype with this package.


## JSON Options Schema

```
{
    "key": string, 
    "maintain_aspect_ratio": boolean, 
    "width": numeric, 
    "height": numeric,
    "quality": numeric,
    "filter_negate": boolean,
    "filter_greyscale": boolean,
    "filter_edge_detect": boolean,
    "filter_emboss": boolean
}
```

## Required Published Files

```
php artisan vendor:publish --provider="Salivity\Laravel8DynamicImage\Providers\DynamicImageServiceProvider" --force --tag="config"
php artisan vendor:publish --provider="Salivity\Laravel8DynamicImage\Providers\DynamicImageServiceProvider" --force --tag="lang"
```

## Cache

It is possible to write images to the cache if enable and the cache is large enough to support the files
you must create the directories in your laravel project by running the command

```php artisan dynamic_image:create_local_folders```

## CPU Credits

To control te amount of system resources used in the Laravel Project this package uses a Credit system where each a every
system resource is calculated to produce a throtling effect. For instance 1000 thumbnail images would consume far
less CPU credits than 100 large images. A decent amount of CPU credits should be calculate once the front end has been 
completed.

## Config

Take a look at the config file directly for more information.

