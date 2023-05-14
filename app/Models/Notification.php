<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_pemberitahuan';

  public function pengirim(): BelongsTo
  {
    return $this->belongsTo(User::class, 'sent_by', 'employee_no');
  }

  public function penerima(): BelongsTo
  {
    return $this->belongsTo(EmployeeGroup::class, 'receive_by', 'id_grup');
  }

  public function pembuat(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by', 'employee_no');
  }

  public function pengubah(): BelongsTo
  {
    return $this->belongsTo(User::class, 'updated_by', 'employee_no');
  }

  public function penerimaPemberitahuan()
  {
    return $this->hasMany(NotificationReceiver::class, 'notification_id');
  }
}
