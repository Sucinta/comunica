<?php

namespace App\Models\Sabium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Estado extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'estados';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uf',
        'nome',
        'codigo_ibge',
        'slug',
        'timezone',
    ];

    /**
     * Configuração do Slug: gera a partir de 'nome' e salva em 'slug'
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nome')
            ->saveSlugsTo('slug');
    }
}
