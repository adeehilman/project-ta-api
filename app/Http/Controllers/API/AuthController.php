<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // function registrasi
    public function registrasi(Request $request)
    {

        // if (!$request->employee_no) {
        //     return response()->json([
        //         "message" => "Body dibutuhkan!"
        //     ]);
        // }

        // if (!$request->tgl_lahir) {
        //     return response()->json([
        //         "message" => "Body dibutuhkan!"
        //     ]);
        // }

        // if (!$request->password) {
        //     return response()->json([
        //         "message" => "Body dibutuhkan!"
        //     ]);
        // }

        $request->validate([
            "employee_no"   => "required",
            "tgl_lahir"     => "required",
            "password"      => "required",
            "id_question"   => "required",
            "answer"       => "required"
            // "alamat_lengkap"    => "required",
            // "kelurahan"         => "required",
            // "kecamatan"         => "kecamatan",
        ]);

        /**
         * lakukan pengecekan terdahulu apakah ada user dengan badge yang direquest
         */

        $user_check = DB::table('tbl_karyawan')
            ->where('badge_id', $request->employee_no)
            ->first();
        if (!$user_check) {
            return response()->json([
                "message" => "Badge " . $request->employee_no . " tidak terdaftar"
            ], 400);
        }

        if ($user_check) {

            /**
             * Apabila sudah pernah set password maka tidak bisa mendaftar lagi
             */
            if ($user_check->password != null) {
                return response()->json([
                    "message" => "Kamu sudah terdaftar di aplikasi ini"
                ], 400);
            }

            /**
             * Apabila belum pernah set password, maka lakukan insert data
             */
            DB::beginTransaction();
            try {

                // uodate ke tabel karyawan
                DB::table('tbl_karyawan')
                    ->where('badge_id', $request->employee_no)
                    ->update([
                        "tgl_lahir" => $request->tgl_lahir,
                        "password"  => bcrypt($request->password),
                        "no_hp"     => $request->no_hp ? $request->no_hp : null,
                        "no_hp2"    => $request->no_hp2 ? $request->no_hp2 : null,
                        "home_telp" => $request->telp ? $request->telp : null
                    ]);

                // insert ke tabel mms
                DB::table('tbl_mms')
                    ->insert([
                        "badge_id"  => $request->employee_no,
                        "uuid"      => $request->uuid ? $request->uuid : "N/A"
                    ]);

                // insert ke tabel alamat
                DB::table('tbl_alamat')
                        ->insert([
                            "badge_id" => $request->employee_no,
                            "alamat"   => $request->alamat ? $request->alamat : null,
                            "kecamatan"=> $request->kecamatan ? $request->kecamatan : null,
                            "kelurahan"=> $request->kelurahan ? $request->kelurahan : null,
                            "latitude" => $request->latitude ? $request->latitude : null,
                            "longitude"=> $request->longitude ? $request->longitude : null
                        ]);

                // insert ke tabel tbl_securityquestion
                DB::table('tbl_securityquestion')
                        ->insert([
                            "badge_id" => $request->employee_no,
                            "id_question" => $request->id_question,
                            "answer" => $request->answer
                        ]);

                DB::commit();

                return response()->json([
                    "message" => "Registrasi data karyawan berhasil"
                ]);
            } catch (\Throwable $th) {

                DB::rollBack();
                return response()->json([
                    "message" => "Something went wrong when update data karyawan"
                ], 400);
            }
        }
    }

    // function login
    public function login(Request $request)
    {
        if (!$request->badge_id) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        if (!$request->password) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        $credentials = $request->only('badge_id', 'password');
        // dd($credentials);
        if (Auth::attempt($credentials)) {
            $token = JWTAuth::fromUser(Auth::user());
            $data = DB::table('tbl_karyawan')
                ->join('tbl_mms', 'tbl_mms.badge_id', '=', 'tbl_karyawan.badge_id')
                ->join('tbl_vlookup', 'tbl_vlookup.id_vlookup', '=', 'tbl_karyawan.gender')
                ->select(
                    'tbl_karyawan.badge_id',
                    'email',
                    'fullname',
                    'dept_code',
                    'line_code',
                    'position_code',
                    'id_grup',
                    'pt',
                    'tempat_lahir',
                    'tgl_lahir',
                    'name_vlookup as jenis_kelamin',
                    'card_no',
                    'uuid',
                )
                ->where('tbl_karyawan.badge_id', $request->badge_id)->first();
            return response()->json([
                "message" => "Berhasil Login",
                "data"    => $data,
                "token"   => $token
            ]);
        } else {
            return response()->json([
                "message" => "Gagal login, harap periksa badge dan password anda!",
            ], 400);
        }
    }

    // cek security answer
    public function checkAnswer(Request $request)
    {
        if (!$request->badge) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        if (!$request->answer) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        /**
         * lakukan lowercase dan menghapus semua spasi 
         * 
         */
        $jawaban = str_replace(' ', '', $request->answer);
        $jawaban = strtolower($jawaban);

        $data_answer = DB::table('tbl_securityquestion')
            ->where('badge_id', $request->badge)
            ->first();
        if ($data_answer) {

            $data_jawaban_employe = $data_answer->answer;
            $data_jawaban_employe = str_replace(' ', '', $data_jawaban_employe);
            $data_jawaban_employe = strtolower($data_jawaban_employe);

            if ($data_jawaban_employe == $jawaban) {
                return response()->json([
                    "message" => "Jawaban diterima dan sesuai dengan data!"
                ]);
            }

            if ($data_jawaban_employe != $jawaban) {
                return response()->json([
                    "message" => "Jawaban dan data tidak macthing!"
                ], 400);
            }
        }
    }

    // change password
    public function forgetPassword(Request $request)
    {

        
        if (!$request->badge_id) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        if (!$request->new_password) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        if (!$request->security_answer) {
            return response()->json([
                "message" => "Body dibutuhkan!"
            ]);
        }

        $jawaban = str_replace(' ', '', $request->security_answer);
        $jawaban = strtolower($jawaban);

        $data_answer = DB::table('tbl_securityquestion')
            ->where('badge_id', $request->badge_id)
            ->first();
        if ($data_answer) {

            $data_jawaban_employe = $data_answer->answer;
            $data_jawaban_employe = str_replace(' ', '', $data_jawaban_employe);
            $data_jawaban_employe = strtolower($data_jawaban_employe);

            if ($data_jawaban_employe != $jawaban) {
                return response()->json([
                    "message" => "Jawaban tidak sesuai dengan database!"
                ], 400);
            }
        }

        /**
         * lakukan update password ditabel karyawan
         */
        DB::beginTransaction();
        try {
            DB::table('tbl_karyawan')
                ->where('badge_id', $request->badge_id)
                ->update([
                    "password" => bcrypt($request->new_password)
                ]);
            DB::commit();
            return response()->json([
                "message" => "Sukses melakukan update password karyawan!"
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "Something went wrong when change password"
            ], 400);
        }
    }
}
