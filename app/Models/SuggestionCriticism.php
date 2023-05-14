<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Users;
use App\Models\Lookup;

class SuggestionCriticism extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_kritiksaran';

  public function pengirim()
  {
    return $this->belongsTo(User::class, 'employee_no', 'employee_no');
  }

  public function kategori()
  {
    return $this->belongsTo(Lookup::class, 'kategori');
  }

  public function mode()
  {
    return $this->belongsTo(Lookup::class, 'mode');
  }

  // protected $fillable = ['category', 'name_vlookup', 'image'];
}
