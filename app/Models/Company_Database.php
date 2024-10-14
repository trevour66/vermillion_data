<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_Database extends Model
{
    use HasFactory;

    protected $fillable = [
        "website",
        "description"
    ];
}
