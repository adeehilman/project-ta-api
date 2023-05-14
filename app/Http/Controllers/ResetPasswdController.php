<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ResetPasswdController extends Controller
{
    public function index()
    {

        $data = ['userInfo' => DB::table('tbl_user')->where('employee_no', session('loggedInUser'))->first()];

        return view('resetpasswd.index', $data);
    }
}
