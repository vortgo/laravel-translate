<?php

namespace Vortgo\Translate\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\Translation
 *
 * @property integer $translation_id
 * @property integer $entity_id
 * @property string $entity_name
 * @property string $entity_attribute
 * @property string $locale
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereTranslationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereEntityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereEntityName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereEntityAttribute($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereLocale($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Translation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
