<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeGroup extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_grupkaryawan';

  public function pemberitahuan(): HasMany
  {
    return $this->hasMany(Notification::class, 'id_grup', 'receive_by');
  }
}
