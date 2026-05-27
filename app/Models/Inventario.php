<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_producto', 'id_local', 'cantidad_actual'];

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('id_producto', $this->getAttribute('id_producto'))
            ->where('id_local', $this->getAttribute('id_local'));
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local', 'id_local');
    }
}
