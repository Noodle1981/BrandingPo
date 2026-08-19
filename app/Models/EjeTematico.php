<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EjeTematico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'color_badge',
        'descripcion',
    ];
}
