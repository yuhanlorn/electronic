<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\CachedModel;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $key
 * @property string $description
 * @property string $color
 * @property string $icon
 * @property string $model_type
 * @property int $model_id
 * @property bool $is_activated
 * @property string $created_at
 * @property string $updated_at
 * @property Type[] $typables
 */
class Type extends CachedModel implements HasMedia
{
    use Cachable;
    use HasTranslations;
    use InteractsWithMedia;

    protected $cachePrefix = 'tomato_types_';

    public $translatable = [
        'name',
        'description',
    ];

    /**
     * @var array
     */
    protected $fillable = ['order', 'for', 'name', 'key', 'type', 'description', 'color', 'icon', 'parent_id', 'model_type', 'model_id', 'is_activated', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function typables()
    {
        return $this->hasMany(Type::class);
    }

    public function parent()
    {
        return $this->belongsTo(Type::class, 'parent_id');
    }
}
