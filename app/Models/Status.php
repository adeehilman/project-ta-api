<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\JobVacancy;

class Status extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_status';
  protected $fillable = ['deskripsi'];

  public function loker()
  {
    return $this->hasMany(JobVacancy::class, 'id', 'status');
  }
}
