<?php

namespace Vortgo\Translate\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Vortgo\Translate\Traits\Translate;

class Category extends Model
{
    use Translate;

    protected $defaultLocale = 'en';

    protected  $fillable = [
        'name'
    ];

}
