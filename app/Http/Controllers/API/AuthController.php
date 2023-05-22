<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // function registrasi
    public function registrasi(Request $request)
    {

        $request->validate([
            "employee_no"   => "required",
            "tgl_lahir"     => "required",
            "password"      => "required",
            "id_question"   => "required",
            "answer"       => "required"
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
                        "home_telp" => $request->telp ? $request->telp : null,
                        "id_grup"   => 1
                    ]);

                // insert ke tabel mms
                // DB::table('tbl_mms')
                //     ->insert([
                //         "badge_id"  => $request->employee_no,
                //         "uuid"      => $request->uuid ? $request->uuid : "N/A"
                //     ]);

                // insert ke tabel alamat
                DB::table('tbl_alamat')
                    ->insert([
                        "badge_id" => $request->employee_no,
                        "alamat"   => $request->alamat ? $request->alamat : null,
                        "kecamatan" => $request->kecamatan ? $request->kecamatan : null,
                        "kelurahan" => $request->kelurahan ? $request->kelurahan : null,
                        "latitude" => $request->latitude ? $request->latitude : null,
                        "longitude" => $request->longitude ? $request->longitude : null
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
                    "message" => $th->getMessage()
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
                    'img_user'
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

        if (!$data_answer) {
            return response()->json([
                "message" => "Security answer tidak ditemukan untuk badge " . $request->badge
            ], 400);
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

    // check decrypt password
    public function decryptQr(Request $request)
    {
        $request->validate([
            "encrypt_code" => "required"
        ]);

        /**
         * Proses decrypt data menggunakan Kriptografi AES
         * KEY ada di .env
         * Enksripsi di adopsi dari sistem lama MIS
         */
        $key = env('AES_KEY');
        $iv  = env('AES_IV');

        $decryptedData = openssl_decrypt(
            base64_decode($request->encrypt_code),
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        $data = $this->checkSecurityPhone($decryptedData);

        return response()->json($data, $data['status_code']);
    }

    // private function check security uuid
    /**
     * Return nya adalah objek
     * lewat keluar gerbang satnusa atau enggak
     */
    private function checkSecurityPhone($decypt_text)
    {

        $status_check = 0;
        $message = "Kesalahan pada sistem!";

        /**
         * Apabila decrypt text tidak return false dan tidak string kosong
         */
        if ($decypt_text != false || $decypt_text != null) {

            // dipishain dulu pertitik koma
            $array_decrypt = explode(';', $decypt_text);
            $uuid = $array_decrypt[0];
            $date = $array_decrypt[1];
            $hours = (int)$array_decrypt[2];

            // dipisahin dulu per strip utk tanggalnya
            $array_date = explode('-', $date);
            $hari  = $array_date[0];
            $bulan = $array_date[1];
            $tahun = $array_date[2];

            // lakukan pengeceka uuid apakah ada di tbl_mms
            // $query = "SELECT b.fullname, a.badge_id, b.position_code, b.dept_code, a.uuid,a.img_dpn, a.img_blk, b.img_user FROM tbl_mms a, tbl_karyawan b WHERE 
            //             a.badge_id = b.badge_id AND
            //             a.uuid = '$uuid' AND a.status_pendaftaran_mms = 12";

            $query = "SELECT b.fullname, a.badge_id, b.position_code, b.dept_code, a.uuid,a.img_dpn, a.img_blk, a.status_pendaftaran_mms, c.stat_title, c.stat_desc, b.img_user FROM tbl_mms a, tbl_karyawan b, tbl_statusmms c WHERE 
                            a.badge_id = b.badge_id AND a.status_pendaftaran_mms = c.id AND a.uuid = '$uuid' ";

            $karyawan = DB::select($query);

            // apabila ditemukan uuid yang sama dan status nya 12 maka lakukan kode dibawah ini
            if (count($karyawan) > 0) {
                $karyawan = $karyawan[0];

                // lakukan pengecekan expired qr code yang telah dilempar oleh kodenya
                /**
                 * Apabila jam >= 7 dan jam <= 23
                 */
                if ($hours >= 7 && $hours <= 23) {
                    $minHour = $hours - 2;
                    $maxHour = $hours + 1;

                    // lakukan pengecekan hari dan jam
                    if (
                        date('d') == $hari &&
                        date('m') == $bulan &&
                        date('Y') == $tahun &&
                        date('H') >= $minHour &&
                        date('H') <= $maxHour

                    ) {

                        $status_check = 1;
                        $message = "DEVICE FOUND";

                        $data = [
                            "message" => $message,
                            "status_code" => 200,
                            "status_check" => $status_check,
                            "data" => $karyawan
                        ];
                        return $data;
                    } else {
                        $status_check = 0;
                        $message = "QR CODE EXPIRED";

                        $data = [
                            "status_check" => $status_check,
                            "status_code" => 400,
                            "message" => $message,
                            "data"    => []
                        ];

                        return $data;
                    }
                }

                /**
                 * Apabila jam jam krusial saat ganti hari dan waktu
                 */
                if (($hours >= 0 && $hours <= 2) || ($hours >= 23 && $hours <= 24)) {
                    $hours = $hours == 0 ? 24 : $hours;
                    $span2 = new DateInterval('PT' . $hours . 'H');
                    $combineDateNIHour = (new DateTime())->setDate($tahun, $bulan, $hari)->setTime($span2->h, $span2->i, $span2->s);
                    $minHour = (new DateTime())->sub(new DateInterval('PT2H'));
                    $maxHour = (new DateTime())->add(new DateInterval('PT1H'));

                    if ($minHour < $combineDateNIHour && $combineDateNIHour < $maxHour) {
                        $status_check = 1;
                        $message = "DEVICE FOUND";

                        $data = [
                            "status_check" => $status_check,
                            "status_code" => 200,
                            "message" => $message,
                            "data"    => $karyawan
                        ];

                        return $data;
                    } else {
                        $status_check = 0;
                        $message = "QR CODE EXPIRED";

                        $data = [
                            "status_check" => $status_check,
                            "status_code" => 400,
                            "message" => $message,
                            "data"    => $karyawan
                        ];

                        return $data;
                    }
                }
            } else {
                $status_check = 0;
                $message = "DEVICE NOT REGISTERED";

                $data = [
                    "message" => $message,
                    "status_code" => 400,
                    "status_check" => $message,
                    "data" => $karyawan
                ];

                return $data;
            }
        }

        $data = [
            "message" => "TIDAK DAPAT MENGURAI DATA QR YANG DIBERIKAN",
            "status_code" => 400,
            "status_check" => 0,
            "data" => []
        ];

        return $data;
    }

    /**
     * check apakah user baru pertama kali melakukan login 
     * untuk pengguna yang lama, atau menghadapi masa transisi dari aplikasi mysatnusa lama
     * ke mysatnusa baru
     */
    public function isFirstLogin(Request $request)
    {

        $request->validate([
            "badge_id" => "required"
        ]);

        $data = DB::table('tbl_karyawan')
            ->select('is_reset')
            ->where('badge_id', $request->badge_id)
            ->first();
        if ($data) {
            // apakah user pertama kali login ?
            if ($data->is_reset == 0) {
                return response()->json([
                    "message" => "munculkan dialog, karena return 1",
                    "isFirstLogin" => 1
                ]);
            }

            // apakah user pertama kali login ?
            if ($data->is_reset == 1) {
                return response()->json([
                    "message" => "jangan munculkan dialog, karena return 0",
                    "isFirstLogin" => 0
                ]);
            }
        }

        if (!$data) {
            return response()->json([
                "message" => "badge tidak ditemukan"
            ], 400);
        }
    }

    /**
     * lakukan insert ke database, apabila user baru portama kali login
     * user set password, dan user set security question
     */
    public function setFirstLogin(Request $request)
    {

        $request->validate([
            "badge_id" => "required",
            "new_password" => "required",
            "id_question"  => "required",
            "answer"       => "required"
        ]);

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

            // insert ke tabel tbl_securityquestion
            DB::table('tbl_securityquestion')
                ->insert([
                    "badge_id" => $request->badge_id,
                    "id_question" => $request->id_question,
                    "answer" => $request->answer
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
