<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function index()
    {

        // $password = Hash::make(1);

        // $data = [
        //     'data' => $password
        // ];

        // $badge = 111111;
        // $dataUser = DB::table('tbl_user')->where('badge', $badge)->first();

        // dd($dataUser);

        return view('auth/index');
    }

    public function login(Request $request)
    {
        // $badge = $request->post('badge');
        // $dataUser = DB::table('tbl_user')->where('badge', $badge)->first();

        // return response()->json([
        //     'status' => 200,
        //     'data' => $dataUser
        // ]);

        $validator = Validator::make($request->all(), [
            'employee_no' => 'required|min:6',
            'password' => 'required'
        ], [
            'employee_no.required' => 'Password tidak boleh kosong',
            'employee_no.min' => 'No Karyawan minimal 6 angka',
            'password' => 'Password tidak boleh kosong'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ]);
        } else {

            $employeeNo = $request->post('employee_no');
            $password = $request->password;
            $dataUser = DB::table('tbl_user')->where('employee_no', $employeeNo)->first();

            if ($dataUser) {
                $userPassword = $dataUser->password;
                $validPassword = Hash::check($password, $userPassword);
                if ($validPassword) {
                    $request->session()->put('loggedInUser', $dataUser->employee_no);
                    return response()->json([
                        'status' => 200,
                        'messages' => 'Berhasil login',
                    ]);
                } else {
                    return response()->json([
                        'status' => 401,
                        'messages' => 'Password salah'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'messages' => 'Nomor Karyawan tidak ditemukan'
                ]);
            }
        }
    }

    public function logout()
    {
        if (session()->has('loggedInUser')) {
            session()->pull('loggedInUser');
            return redirect('/');
        }
    }
}
