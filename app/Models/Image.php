<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model // Corrected the class name to PascalCase
{
    use HasFactory;


    protected $fillable = [
        'image_data',
        'processed',
    
    ];
}
