<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Users;

class SuggestionCriticismResponse extends BaseModel
{
  use HasFactory;

  protected $table = 'tbl_responkritiksaran';

  public function kritik()
  {
    return $this->hasMany(SuggestionCriticism::class, 'id_kritik');
  }

  // protected $fillable = ['category', 'name_vlookup', 'image'];
}
