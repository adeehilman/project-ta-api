<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class JobVacancy extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_lowongankerja';

  public function status()
  {
    return $this->belongsTo(Status::class, 'status', 'id');
  }

  public function pembuat()
  {
    return $this->belongsTo(User::class, 'created_by', 'id');
  }

  public function pengubah()
  {
    return $this->belongsTo(User::class, 'updated_by', 'id');
  }

  // protected $fillable = ['category', 'name_vlookup', 'image'];
}
