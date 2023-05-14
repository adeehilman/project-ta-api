<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Address extends BaseModel
{
    use HasFactory;

    protected $table = 'tbl_alamat';

    protected $fillable = ['category', 'name_vlookup', 'image'];
}
