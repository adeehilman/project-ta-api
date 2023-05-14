<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Lookup extends BaseModel
{
    use HasFactory;

    protected $table = 'tbl_vlookup';

    protected $fillable = ['category', 'name_vlookup', 'image'];
}
