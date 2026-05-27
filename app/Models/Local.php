<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Local extends Model
{
    protected $table = 'local';
    protected $primaryKey = 'id_local';
    public $timestamps = false;

    protected $fillable = ['nombre', 'zona'];

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_local', 'id_local');
    }
}
