<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
  use HasFactory;

  protected $table = 'customizations';

  protected $casts = [
    'slides' => 'array',
    'collections' => 'array',
  ];

  protected $fillable = [
    'slides',
    'collections',
    'banner',
  ];
}
