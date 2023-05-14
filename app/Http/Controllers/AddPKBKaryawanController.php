<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AddPKBKaryawanController extends Controller
{
    public function index()
    {

        $data = ['userInfo' => DB::table('tbl_user')->where('badge', session('loggedInUser'))->first()];

        return view('karyawan.addpkb', $data);

    }
}
