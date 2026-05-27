<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $table = 'compra';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_empleado',
        'id_metodo',
        'id_local',
        'fecha_compra',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraProducto::class, 'id_compra', 'id_compra');
    }
}
