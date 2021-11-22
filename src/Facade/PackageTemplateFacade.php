<?php

namespace Salivity\PackageTemplate\Facade;

use Illuminate\Support\Facades\Facade;


class PackageTemplateFacade extends Facade{

    protected static function getFacadeAccessor() { 
        return 'package_template'; 
        
    }

}