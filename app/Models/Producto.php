<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'url_imagen',
        'id_franquicia',
        'precio_actual',
    ];

    public function franquicia(): BelongsTo
    {
        return $this->belongsTo(Franquicia::class, 'id_franquicia', 'id_franquicia');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(
            Categoria::class,
            'producto_categoria',
            'id_producto',
            'id_categoria'
        );
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_producto', 'id_producto');
    }
}
