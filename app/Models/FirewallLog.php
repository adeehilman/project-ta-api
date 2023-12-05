<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirewallLog extends Model
{
    use HasFactory;
    
    protected $table = 'tbl_alamat';

protected $fillable = ['category', 'name_vlookup', 'image'];
}
