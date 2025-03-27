<?php

namespace App\Models\Sabium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Cidade extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'cidades';

    protected $fillable = [
        'estado_id',
        'sabium_id',
        'nome',
        'codigo_ibge',
        'slug',
        'timezone',
    ];

    public function estado(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nome')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
}
