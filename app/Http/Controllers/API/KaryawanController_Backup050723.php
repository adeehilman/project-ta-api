<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                "message" => "Tidak ada badge yang cocok ditemukan!",
                "status_pendaftaran" => 0
            ], 400);
        }

        if ($check_karyawan) {
            if ($check_karyawan->password != null) {
                return response()->json([
                    "message" => "Badge sudah didaftarkan",
                    "status_pendaftaran" => 0
                ]);
            }
        }

        return response()->json([
            "message" => "Badge bisa didaftarkan!",
            "status_pendaftaran" => 1
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
            if ($check_karyawan->imei1 == null && $check_karyawan->uuid != null) {
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
    public function profile(Request $request)
    {
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
            ->select(
                'alamat',
                'tbl_alamat.kecamatan as id_kecamatan',
                'tbl_alamat.kelurahan as id_kelurahan',
                'tbl_kecamatan.kecamatan as nama_kecamatan',
                'tbl_kelurahan.kelurahan as nama_kelurahan',
                'latitude',
                'longitude'
            )
            ->join('tbl_kelurahan', 'tbl_kelurahan.id', '=', 'tbl_alamat.kelurahan')
            ->join('tbl_kecamatan', 'tbl_kecamatan.id', '=', 'tbl_alamat.kecamatan')
            ->where('badge_id', $request->badge)
            ->first();

        //dd($data_alamat);

        /**
         * Apabila keduanya ada
         */
        if ($data_karyawan && $data_alamat) {
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
                    // "img_user" => $data_karyawan->img_user ? url(asset("/avatar/" .$data_karyawan->img_user)) : null
                    "img_user" => $data_karyawan->img_user ? $data_karyawan->img_user : null
                ],
                "kontak_karyawan" => [
                    "no_hp" => $data_karyawan->no_hp,
                    "no_hp2" => $data_karyawan->no_hp2,
                    "home_telp" => $data_karyawan->home_telp
                ],
                "alamat" => [
                    "alamat" => $data_alamat->alamat ? $data_alamat->alamat : null,
                    "id_kecamatan" => $data_alamat->id_kecamatan ? $data_alamat->id_kecamatan : null,
                    "nama_kecamatan" => $data_alamat->nama_kecamatan ? $data_alamat->nama_kecamatan : null,
                    "id_kelurahan" => $data_alamat->id_kelurahan ? $data_alamat->id_kelurahan : null,
                    "nama_kelurahan" => $data_alamat->nama_kelurahan ? $data_alamat->nama_kelurahan : null,
                    "latitude"  => $data_alamat->latitude ? $data_alamat->latitude : null,
                    "longitude" => $data_alamat->longitude ? $data_alamat->longitude : null
                ],
                "sisa_cuti" => $this->getSisaCuti($request->badge),
                "cuti_expired" => $this->getCutiExp($request->badge)
            ];

            return response()->json($response);
        }


        /**
         * apabila hanya data karyawan yang ada
         */
        if ($data_karyawan && $data_alamat == null) {
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
                    //"img_user" => $data_karyawan->img_user ? url(asset("/avatar/" .$data_karyawan->img_user)) : null
                    "img_user" => $data_karyawan->img_user ? $data_karyawan->img_user : null
                ],
                "kontak_karyawan" => [
                    "no_hp" => $data_karyawan->no_hp,
                    "no_hp2" => $data_karyawan->no_hp2,
                    "home_telp" => $data_karyawan->home_telp
                ],
                "alamat" => [
                    "alamat" => null,
                    "id_kelurahan" => null,
                    "kelurahan" => null,
                    "id_kecamatan" => null,
                    "kecamatan" => null,
                    "latitude"  => null,
                    "longitude" => null
                ],
                "sisa_cuti" => $this->getSisaCuti($request->badge),
                "cuti_expired" => $this->getCutiExp($request->badge)
            ];

            return response()->json($response);
        }

        /**
         * Apabila karyawan tidak ada dan data alamat tidak ada
         */
        if ($data_karyawan == null && $data_alamat == null) {
            return response()->json([
                "message" => "Karyawan tidak ditemukan"
            ], 400);
        }
    }

    // function edit profile
    public function editAlamat(Request $request)
    {
        $request->validate([
            "badge_id"      => "required",
            "alamat"        => "required",
            "kecamatan"     => "required",
            "kelurahan"     => "required"
        ]);


        DB::beginTransaction();
        try {

            /**
             * cek alamat terlebih dahulu untuk badge tersebut
             */
            $data = DB::table('tbl_alamat')
                ->where('badge_id', $request->badge_id)
                ->first();

            if(!$data){
                DB::table('tbl_alamat')
                        ->where('badge_id', $request->badge_id)
                        ->insert([
                            "badge_id" => $request->badge_id,
                            "alamat"   => $request->alamat,
                            "kecamatan" => $request->kecamatan,
                            "kelurahan" => $request->kelurahan
                        ]);
            }
            else {
                // lakukan update
                DB::table('tbl_alamat')
                ->where('badge_id', $request->badge_id)
                ->update([
                    "badge_id" => $request->badge_id,
                    "alamat"   => $request->alamat,
                    "kecamatan" => $request->kecamatan,
                    "kelurahan" => $request->kelurahan
                ]);
            }

            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil update data alamat",
                "data"    => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong",
            ], 400);
        }
    }

    // function edit profile
    public function editKontak(Request $request)
    {
        $request->validate([
            "badge_id"      => "required",
            "no_hp"        => "required",
        ]);

        DB::beginTransaction();
        try {
            DB::table('tbl_karyawan')
                ->where('badge_id', $request->badge_id)
                ->update([
                    "badge_id" => $request->badge_id,
                    "no_hp"   => $request->no_hp,
                    "no_hp2" => $request->no_hp2 ? $request->no_hp2 : null,
                    "home_telp" => $request->telp ? $request->telp : null
                ]);
            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil update data kontak",
                "data"    => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong",
            ], 400);
        }
    }

    // function edit security 
    public function editSecurity(Request $request)
    {
        $request->validate([
            "badge_id"           => "required",
            "id_question"        => "required",
            "answer"             => "required"
        ]);

        DB::beginTransaction();
        try {
            DB::table('tbl_securityquestion')
                ->where('badge_id', $request->badge_id)
                ->update([
                    "badge_id" => $request->badge_id,
                    "id_question"   => $request->id_question,
                    "answer" => $request->answer
                ]);
            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil update security question",
                "data"    => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong",
            ], 400);
        }
    }

    // function ekios get profile
    public function getProfileEkios(Request $request)
    {
        $request->validate([
            'uuid' => 'required'
        ]);

        echo $request->uuid;
        /**
         * Proses decrypt data menggunakan Kriptografi AES
         * KEY ada di .env
         * Enksripsi di adopsi dari sistem lama MIS
         */
        $key = env('AES_KEY');
        $iv  = env('AES_IV');

        $decryptedData = openssl_decrypt(
            base64_decode($request->uuid),
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        echo $decryptedData;
        die();
        $msg = $this->checkSecurityPhone($decryptedData);

        // try {
        //     $query = "SELECT b.fullname, a.badge_id, b.position_code, b.dept_code, a.uuid,a.img_dpn, a.img_blk, b.img_user FROM tbl_mms a, tbl_karyawan b WHERE 
        //                 a.badge_id = b.badge_id AND
        //                 a.uuid = '$request->uuid' AND a.status_pendaftaran_mms = 12";
        //     $karyawan = DB::select($query);

        //     if(count($karyawan) > 0){
        //         $karyawan = $karyawan[0];
        //         return response()->json([
        //             "message" => "Response OK",
        //             "data" => $karyawan
        //         ]);
        //     }

        //     return response()->json([
        //         "message" => "Data Tidak di temukan",
        //     ], 400);


        // } catch (\Throwable $th) {
        //     return response()->json([
        //         "message" => "Something went wrong",
        //     ], 400);
        // }
    }

    // function get question 
    public function getMyQuestion(Request $request)
    {
        $request->validate([
            "badge_id" => "required"
        ]);

        /**
         * Cek Karyawan ada badge nya atau engga
         */
        $karyawan = DB::table('tbl_karyawan')
            ->where('badge_id', $request->badge_id)
            ->first();

        if (!$karyawan) {
            return response()->json([
                "message" => "Badge tidak ditemukan!"
            ], 400);
        }

        if ($karyawan) {
            $data = DB::table('tbl_securityquestion')
                ->select(
                    'badge_id',
                    'id_question',
                    'question',
                )
                ->join('tbl_listquestion', 'tbl_listquestion.id', '=', 'tbl_securityquestion.id_question')
                ->where('badge_id', $request->badge_id)
                ->first();

            if ($data) {
                return response()->json([
                    "message" => "Bisa Melakukan Forget Password",
                    "status_forget_pass" => 0,
                    "data"    => $data
                ]);
            }

            if (!$data) {
                return response()->json([
                    "message" => "Maaf, kami tidak menemukan security question anda, dan anda belum bisa menggunakan fitur ini",
                    "status_forget_pass" => 1
                ]);
            }
        }
    }

    /**
     * Return nya adalah string message apakah sebuah phone boleh 
     * lewat keluar gerbang satnusa atau enggak
     */
    private function checkSecurityPhone($decypt_text)
    {
        $message = "Kesalahan pada sistem!";

        $array_decrypt = explode(';', $decypt_text);
        // dd($array_decrypt);
    }

    /**
     * Change password di halaman profile
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            "badge_id" => "required",
            "password_skrg" => "required",
            "password_baru" => "required"
        ]);

        // lakukan pengecekan password
        $data = DB::table('tbl_karyawan')
            ->where('badge_id', $request->badge_id)
            ->first();
        if ($data) {
            $check_hash = Hash::check($request->password_skrg, $data->password);
            if ($check_hash) {
                DB::beginTransaction();
                try {
                    DB::table('tbl_karyawan')
                        ->where('badge_id', $request->badge_id)
                        ->update([
                            "password" => bcrypt($request->password_baru)
                        ]);
                    DB::commit();

                    return response()->json([
                        "message" => "Response OK, Berhasil update password anda",
                        "data"    => []
                    ]);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return response()->json([
                        "message" => "Something went wrong",
                    ], 400);
                }
            } else {
                return response()->json([
                    "message" => "Password saat ini salah, mohon masukan password terakhir anda dengan benar"
                ], 400);
            }
        }

        return response()->json([
            "message" => "Badge ID Tidak ditemukan"
        ], 400);
    }

    /**
     * get sisa cuti dari karyawan
     */
    private function getSisaCuti($badge_id){
        $query = "SELECT sisa_cuti FROM tbl_sisacuti WHERE badge_id = '$badge_id'";
        $data  = DB::select($query);

        if($data){
            $sisa_cuti = $data[0]->sisa_cuti;
            return strval($sisa_cuti);
        }

       return '0';
    }

    /**
     * get notes sisa cuti dari karyawan
     */
    private function getCutiExp($badge_id){
        $query = "SELECT notes FROM tbl_sisacuti WHERE badge_id = '$badge_id'";
        $data  = DB::select($query);

        if($data){

            if($data[0]->notes == ' '){
                return null;
            }

            return $data[0]->notes;

        }
    }
}