<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Franquicia extends Model
{
    protected $table = 'franquicia';
    protected $primaryKey = 'id_franquicia';
    public $timestamps = false;

    protected $fillable = ['nombre_franquicia', 'desarrollador'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_franquicia', 'id_franquicia');
    }
}
