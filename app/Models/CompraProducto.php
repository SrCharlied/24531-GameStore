<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraProducto extends Model
{
    protected $table = 'compra_productos';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_compra', 'id_producto', 'cantidad', 'precio_venta'];

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('id_compra', $this->getAttribute('id_compra'))
            ->where('id_producto', $this->getAttribute('id_producto'));
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
