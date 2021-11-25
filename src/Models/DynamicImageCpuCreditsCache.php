<?php

namespace Salivity\Laravel8DynamicImage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicImageCpuCreditsCache extends Model
{
    use HasFactory;
    
    protected $fillable = ["ip_address_v4"];
}
