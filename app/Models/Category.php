<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Category extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_kategori';

  protected $fillable = ['nama'];
}
