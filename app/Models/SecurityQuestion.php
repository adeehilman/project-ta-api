<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class SecurityQuestion extends BaseModel
{
    use HasFactory;

    protected $table = 'tbl_securityquestion';

    protected $fillable = ['employee_no', 'id_question', 'answer'];
}
