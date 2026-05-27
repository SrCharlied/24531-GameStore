<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodo_pago';
    protected $primaryKey = 'id_metodo';
    public $timestamps = false;

    protected $fillable = ['nombre'];
}
