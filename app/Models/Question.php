<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Question extends BaseModel
{
    use HasFactory;

    protected $table = 'tbl_listquestion';

    protected $fillable = ['question'];
}
