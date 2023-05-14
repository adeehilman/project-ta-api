<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NotificationReceiver extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_penerima_pemberitahuan';

  public function receiver()
  {
    return $this->belongsTo(User::class, 'employee_no', 'employee_no');
  }

  public function notification()
  {
    return $this->belongsTo(Notification::class, 'notification_id', 'id');
  }
}
