<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
  public $timestamps = false;

  // put any common functionality that you want to share between models here
}
