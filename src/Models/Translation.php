<?php

namespace Vortgo\Translate\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $primaryKey = 'translation_id';

    protected $fillable = [
        'entity_id',
        'entity_name',
        'entity_attribute',
        'locale',
        'value'
    ];
}
