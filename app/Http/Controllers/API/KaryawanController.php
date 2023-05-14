<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    // function cek badge
    public function cekBadge(Request $request)
    {

        if (!$request->badge) {
            return response()->json([
                "message" => "Params dibutuhkan!"
            ]);
        }

        $check_karyawan = DB::table('tbl_karyawan')
            ->where('badge_id', $request->badge)
            ->first();
        if (!$check_karyawan) {
            return response()->json([
                "message" => "Tidak ada badge yang cocok ditemukan!"
            ], 400);
        }

        return response()->json([
            "message" => "BADGE di TEMUKAN!"
        ]);
    }

    // function cek mms
    public function cekMMS(Request $request)
    {
        if (!$request->badge) {
            return response()->json([
                "message" => "Params dibutuhkan!"
            ]);
        }

        $check_karyawan = DB::table('tbl_mms')
            ->where('badge_id', $request->badge)
            ->first();
        if ($check_karyawan) {
            if($check_karyawan->imei1 == null && $check_karyawan->uuid != null){
                return response()->json([
                    "message" => "BISA MENDAFTAR MMS"
                ]);
            }
            return response()->json([
                "message" => "MMS Sudah di daftarkan"
            ], 400);
        }

        return response()->json([
            "message" => "BISA MENDAFTAR MMS"
        ]);
    }

    // function profile
    public function profile(Request $request){
        if (!$request->badge) {
            return response()->json([
                "message" => "Params dibutuhkan!"
            ]);
        }

        /**
         * Get data dari tabel karyawan
         */
        $data_karyawan = DB::table('tbl_karyawan')
                                ->where('badge_id', $request->badge)
                                ->first();
        
        /**
         * Get data alamat dari tabel alamat
         */
        $data_alamat = DB::table('tbl_alamat')
                            ->join('tbl_kelurahan', 'tbl_kelurahan.id', '=', 'tbl_alamat.kelurahan') 
                            ->join('tbl_kecamatan', 'tbl_kecamatan.id', '=', 'tbl_alamat.kecamatan')   
                            ->where('badge_id', $request->badge)
                            ->first();

        /**
         * Apabila keduanya ada
         */
        if($data_karyawan && $data_alamat){
            $response = [
                "message" => "Response OK",
                "identitas_diri" => [
                    "badge" => $data_karyawan->badge_id,
                    "email" => $data_karyawan->email,
                    "fullname" => $data_karyawan->fullname, 
                    "dept_code" => $data_karyawan->dept_code, 
                    "line_code" => $data_karyawan->line_code,
                    "position_code" => $data_karyawan->position_code, 
                    "tgl_lahir" => $data_karyawan->tgl_lahir,
                    "img_user" => $data_karyawan->img_user ? url(asset("/avatar/" .$data_karyawan->img_user)) : null
                ],
                "kontak_karyawan" => [
                    "no_hp" => $data_karyawan->no_hp,
                    "no_hp2" => $data_karyawan->no_hp2,
                    "home_telp" => $data_karyawan->home_telp
                ],
                "alamat" => [
                    "alamat" => $data_alamat->alamat ? $data_alamat->alamat : null,
                    "kelurahan" => $data_alamat->kelurahan ? $data_alamat->kelurahan : null, 
                    "kecamatan" => $data_alamat->kecamatan ? $data_alamat->kecamatan : null,
                    "latitude"  => $data_alamat->latitude ? $data_alamat->latitude : null,
                    "longitude" => $data_alamat->longitude ? $data_alamat->longitude : null
                ]
            ];

            return response()->json($response);
        }


        /**
         * apabila hanya data karyawan yang ada
         */
        if($data_karyawan && $data_alamat == null){
            $response = [
                "message" => "Response OK",
                "identitas_diri" => [
                    "badge" => $data_karyawan->badge_id,
                    "email" => $data_karyawan->email,
                    "fullname" => $data_karyawan->fullname, 
                    "dept_code" => $data_karyawan->dept_code, 
                    "line_code" => $data_karyawan->line_code,
                    "position_code" => $data_karyawan->position_code, 
                    "tgl_lahir" => $data_karyawan->tgl_lahir,
                    "img_user" => $data_karyawan->img_user ? url(asset("/avatar/" .$data_karyawan->img_user)) : null
                ],
                "kontak_karyawan" => [
                    "no_hp" => $data_karyawan->no_hp,
                    "no_hp2" => $data_karyawan->no_hp2,
                    "home_telp" => $data_karyawan->home_telp
                ],
                "alamat" => [
                    "alamat" => null,
                    "kelurahan" => null, 
                    "kecamatan" => null,
                    "latitude"  => null,
                    "longitude" => null
                ]
            ];

            return response()->json($response);
        }

        /**
         * Apabila karyawan tidak ada dan data alamat tidak ada
         */
        if($data_karyawan == null && $data_alamat == null){
            return response()->json([
                "message" => "Karyawan tidak ditemukan"
            ], 400);
        }
    }
}
