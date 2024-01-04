<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Akaunting\Firewall\Facade\Firewall;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    /**
     * Dalam registrasi ada beberapa request yang dibutuhkan pada API
     * diaman registrasi ini adalah 
     * employee, tgl lahir, password, questions id, dan answer
     */
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
        // Apabila gak ada maka karyawan artinya tidak terdaftar
        if (!$user_check) {
            return response()->json([
                "message" => "Badge " . $request->employee_no . " tidak terdaftar"
            ], 400);
        }

        // Apabila ada user dengan badge tersebut
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

                // update ke tabel karyawan
                /**
                 * dan set is_reset nya menjadi 1, hal ini berguna
                 * guna ketika user membuka pertama kali aplikasi mobile
                 * maka akan muncul dialog must change password
                 * dan field is reset inilah sebagai parameter 
                 */
                DB::table('tbl_karyawan')
                    ->where('badge_id', $request->employee_no)
                    ->update([
                        "tgl_lahir" => $request->tgl_lahir,
                        "password"  => bcrypt($request->password),
                        "no_hp"     => $request->no_hp ? $request->no_hp : null,
                        "no_hp2"    => $request->no_hp2 ? $request->no_hp2 : null,
                        "home_telp" => $request->telp ? $request->telp : null,
                        "id_grup"   => 1,
                        "is_reset"  => 1
                    ]);

                /**
                 * lalu setelah update tabel karyawan, proses selanjutnya adalah
                 * dengan melakukan insert ke tabel alamat dengan informasi
                 * seperti badge, alamat, kecamatan, dan kelurahan
                 */
                DB::table('tbl_alamat')
                    ->insert([
                        "badge_id" => $request->employee_no,
                        "alamat"   => $request->alamat ? $request->alamat : null,
                        "kecamatan" => $request->kecamatan ? $request->kecamatan : null,
                        "kelurahan" => $request->kelurahan ? $request->kelurahan : null,
                        // "latitude" => $request->latitude ? $request->latitude : null,
                        // "longitude" => $request->longitude ? $request->longitude : null
                    ]);

                /**
                 * Lalu setelah insert ke tabel alamat, kemudian selanjutnya
                 * lakukan insert ke tabel security question
                 * berdasarkan pertanyaan yang telah dipilh user dari sisi mobile
                 * dan jawaban yang telah di set oleh user.
                 */
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

                dd($th);
                DB::rollBack();
                return response()->json([
                    "message" => "Something went wrong"
                ], 400);
            }
        }
    }

    // function login
    /**
     * ini adalah fungsi melakukan proses login
     * dimana user melempar beberapa request yang 
     * diperlukan seperti badge, password, uuid, tipe hp,
     * merek hp, os, dan versi aplikasi
     */
    public function login(Request $request)
    {
 
        $request->validate([
            "badge_id" => "required",
            "password" => "required",
            "uuid_new" => "required",
            "tipe_hp"  => "required",
            "merek_hp" => "required",
            "os"       => "required",
            "versi_aplikasi" => "required"
        ]);

        // if (Firewall::isBlocked($request)) {
        //     // Tanggapi blokir dengan HTTP 403 dan pesan kesalahan
        //     return response()->json(['error' => 'Percobaan login terlalu banyak. Akses diblokir.'], 403);
        // }

        // Selanjutnya buatlah sebuah credentials
        $credentials = $request->only('badge_id', 'password');


     
        /**
         * lakukan proses pengecekan credentials dengan
         * JWT. dimana pengecekan JWT ini menggunakan library 
         * dari tymon/jwtauth
         */
        try {
                
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
                        'img_user',
                        'is_active'
                    )
                    ->where('tbl_karyawan.badge_id', $request->badge_id)->first();
    

                $ip = $request->ip();
                $IpCount = "SELECT * FROM firewall_logs WHERE ip = '$ip' AND deleted_at IS NULL";
                $blockedIpCount = DB::select($IpCount);

                if (COUNT($blockedIpCount) == 4) {
                    return response()->json([
                        "message" => "Percobaan login anda tersisa 2 kali lagi",
                    ], 400);
                } else if(COUNT($blockedIpCount) == 5){
                    return response()->json([
                        "message" => "Percobaan login anda tersisa 1 kali lagi",
                    ], 400);
                }

                /**
                 * apabila is_actice nya adalah 0 maka tidak boleh login
                 */
                if ($data->is_active == '0') {
                    return response()->json([
                        "message" => "Akun anda sudah di non-aktifkan, anda tidak bisa login ke aplikasi ini!",
                    ], 400);
                }
    
    
                /**
                 * logic kak fara start
                 * Pengecekan untuk uuid lama, di app mysatnusa baru
                 * apabila sama bolehkan pengguna login
                 */
                $isUUIDMatching = DB::select("SELECT * FROM tbl_mms WHERE badge_id = '$request->badge_id' AND UUID = '$request->uuid_new' AND is_active = '1' LIMIT 1");
    
                if (count($isUUIDMatching) > 0) {
    
                    // lakukan update is_new_uuid agar tidak di timpa device lain. apabila is_new_uuid nya adalah 0
                    if ($isUUIDMatching[0]->is_new_uuid == '0') {
                        // atau org yg iseng
                        DB::beginTransaction();
                        try {
                            // update is new uuid di tabel mms
                            DB::table('tbl_mms')
                                ->where('badge_id', $request->badge_id)
                                ->where('uuid', $request->uuid_new)
                                ->update([
                                    "is_new_uuid" => '1',
                                    "versi_aplikasi" => $request->versi_aplikasi,
                                    "player_id" => $request->player_id
                                ]);
    
                            DB::commit();
                        } catch (\Throwable $th) {
                            DB::rollBack();
                        }
                    }
    
                    // update is new uuid di tabel mms
                    DB::table('tbl_mms')
                        ->where('badge_id', $request->badge_id)
                        ->where('uuid', $request->uuid_new)
                        ->update([
                            "versi_aplikasi" => $request->versi_aplikasi,
                            "player_id" => $request->player_id
                        ]);
    
                    DB::commit();
    
                    return response()->json([
                        "message" => "Berhasil Login",
                        "data"    => $data,
                        "token"   => $token
                    ]);
                }
                /**
                 * logic kak fara end
                 */
    
    
                /**
                 * Lakukan pengecekan ke tabel mms
                 * untuk mendapatkan data device user yang terdaftar.
                 * 
                 * apabila device yang ditemukan hanya 1 phone
                 * maka replace uuid yang lama dengan uuid yang baru dari aplokasi mysatnusa DOT
                 * 
                 * apabila device yang ditemukan lebih dari 1
                 * maka return can't not login, butuh patching oleh TIM DOT
                 */
                // $list_device_karyawan = DB::select("SELECT COUNT(*) AS jlh FROM tbl_mms WHERE badge_id = '$request->badge_id' ");
                $list_device_karyawan = DB::select("SELECT COUNT(*) AS jlh FROM tbl_mms WHERE badge_id = '$request->badge_id' AND is_new_uuid = '0' ");
    
    
                // apabila list device karyawan lebih dari 1, return can't not login, butuh patching oleh TIM DOT
                if ($list_device_karyawan[0]->jlh > 0) {
                    /**
                     * dan masukkan ke tabel logs login apabila user gagal
                     * login karena device nya lebih dari 1 perangkat
                     */
    
                    DB::beginTransaction();
                    try {
    
                        DB::table('tbl_device_temp')
                            ->insert([
                                "badge_id" => $request->badge_id,
                                "uuid_new" => $request->uuid_new,
                                "tipe_hp"  => $request->tipe_hp,
                                "merek_hp" => $request->merek_hp,
                                "os"       => $request->os,
                                "versi_aplikasi" => $request->versi_aplikasi,
                                "createdate" => date("Y-m-d H:i:s")
                            ]);
    
                        DB::commit();
    
                        return response()->json([
                            "message" => "Kamu belum bisa login, ada hp lama kamu yang belum di pairing, kami akan bantu kamu agar bisa login, silahkan login kembali selama 2 X 24 Jam",
                        ], 400);
                    } catch (\Throwable $th) {
    
    
                        DB::rollBack();
                        return response()->json([
                            "message" => "Something went wrong, when insert logs login",
                        ], 400);
                    }
                }
    
               



                /**
                 * Apabila enggak dan hanya 1 device maka lakukan update uuid,
                 * atau timpa uuid lama dengan yang baru, yang diperoleh dari aplikasi mysatnusa baru
                 * kondisi ini sudah di make sure bahwa uuid itu adalah value yg tidak berubah
                 * meskipun app satnusa baru di uninstall, dan di clear data/cache
                 */
    
                DB::beginTransaction();
                try {
    
                    /**
                     * cek terlebih dahulu apakah pengguna membawa uuid yang lama dan itu mathcing dengan database nya
                     * kalau iya boleh login
                     */
                    $isUUIDMatching = DB::select("SELECT * FROM tbl_mms WHERE badge_id = '$request->badge_id' AND UUID = '$request->uuid_new' LIMIT 1");
                    if ($isUUIDMatching) {
                        // DB::rollBack();
    
                        DB::table('tbl_mms')
                            ->where('badge_id', $request->badge_id)
                            ->where('uuid', $request->uuid_new)
                            ->update([
                                "versi_aplikasi" => $request->versi_aplikasi,
                                "player_id" => $request->player_id
                            ]);
    
                        DB::commit();
    
    
                        return response()->json([
                            "message" => "Berhasil Login",
                            "data"    => $data,
                            "token"   => $token
                        ]);
                    }
    
                    /**
                     * cek apakah uuid yang dilempar dari login
                     * sudah pernah digunakan di device lain atau enggak
                     */
                    $existsUUID = DB::table('tbl_mms')
                        ->where('uuid', $request->uuid_new)
                        ->where('is_active', 1)
                        ->exists();
                    if ($existsUUID) {
                        DB::rollBack();
                        return response()->json([
                            "message" => "Gagal login, UUID " . $request->uuid_new . " telah didaftarkan sebelumnya, silahkan ke HRD untuk validasi",
                        ], 400);
                    }
    
                    // insert ke tabel temp uuid, dengan nilai badge_id, uuid, dan versi aplikasi harusnya
                    DB::table("tbl_temp_uuid")
                        ->insert([
                            "badge_id" => $request->badge_id,
                            "uuid"     => $request->uuid_new
                        ]);
    
                    DB::commit();
    
                    return response()->json([
                        "message" => "Berhasil Login",
                        "data"    => $data,
                        "token"   => $token
                    ]);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return response()->json([
                        "message" => "Terjadi kesalahan saat login",
                    ], 400);
                }
            }
             else {
                return response()->json([
                    "message" => "Gagal login, harap periksa badge dan password anda!",
                ], 400);
            }
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {

            $response = $e->getResponse();
            $blockedIp = $response->getData()->blocked_ip;

            if($blockedIp){
                try {
                    $clientOnesignal = new Client();
                

                    $getBadge = DB::table('tbl_deptauthorize')->where('dept_code', 'WAF')->where('get_notif', 1)->get();
                    $client = new Client();
                    foreach($getBadge as $badge){
                       
                        $dataOS   = [
                            'badge_id' => $badge->badge_id,
                            'message'  => "🔥 Possible attack on webapi.satnusa.com",
                            'sub_message' => "A possible login attack on webapi.satnusa.com has been detected from $blockedIp"
                        ];

                        // API yang hanya mengirim One Signal
                        $responseOS =  $clientOnesignal->get('https://webapi.satnusa.com/api/meeting/send-notif', [
                            'json' => $dataOS,
                        ]);

                      

                    }
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return response()
                        ->json(
                            [
                                'MESSAGETYPE' => 'E',
                                'MESSAGE' => $th->getMessage(),
                            ],
                            400,
                        )
                        ->header('Accept', 'application/json');
                }
                
                return response()->json([
                "message" => "Maaf, Anda telah melebihi batas percobaan login. Silakan coba lagi dalam beberapa saat",
            ], 400);
            }
            // Lakukan sesuatu dengan informasi IP yang diblokir


            return response()->json([
                "message" => "Something went wrong! $blockedIp",
            ], 400);
        }
    }

    // cek security answer
    /**
     * ini adalah proses melakukan pengecekan jawaban
     * ketika user melaukan reset password, dimana dalam tampilan 
     * UI nanti jawabannya akan di cek pada endpoint ini.
     */
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
         * dan kemudian lakukan pengecekan data ke tabel 
         * securityquestion 
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
    /**
     * disini adalah proses pergantian password
     * dimana flow awalnya pengguna mendapatkan
     * jawaban dan mencocokkan security questionsnya
     * 
     */
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
         * dan proses flow change password selesai 
         * sampai disini
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
            "encrypt_code" => "required",
            "status_toggle" => "required"
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

        $data = $this->checkSecurityPhone($decryptedData, $request->status_toggle);
        // apabila AES kriptografi berhasil
        if ($data) {
            return response()->json($data, $data['status_code']);
        }

        // apabila tidak berhasil, maka baca barcode label
        if (!$data) {
            $data = $this->checkBarcodeLabelNew($request->encrypt_code, $request->status_toggle);
            return response()->json($data, $data['status_code']);
        }
    }

    // private function check security uuid
    /**
     * Return nya adalah objek
     * lewat keluar gerbang satnusa atau enggak
     */
    private function checkSecurityPhone($decypt_text, $status_toggle)
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
            // $query = "SELECT a.badge_id,  b.fullname, uuid, status_pendaftaran_mms, dept_code, position_code, img_dpn, img_blk, img_user FROM tbl_mms  AS a JOIN tbl_karyawan AS b ON b.badge_id = a.badge_id WHERE (UUID = '$uuid' OR imei1 = '$uuid' OR imei2 = '$uuid' ) AND a.is_active = '1' LIMIT 1";
            $query = "SELECT a.badge_id,  b.fullname, UUID, status_pendaftaran_mms, dept_code, position_code, img_dpn, img_blk, img_user, c.stat_title AS STATUS
            FROM tbl_mms  AS a 
            JOIN tbl_karyawan AS b ON b.badge_id = a.badge_id 
            JOIN tbl_statusmms AS c ON c.id = a.status_pendaftaran_mms
            WHERE (UUID = '$uuid' OR imei1 = '$uuid' OR imei2 = '$uuid' ) 
            AND a.is_active = '1' LIMIT 1";

            $karyawan = DB::select($query);

            // apabila ditemukan uuid yang sama dan status nya 12 maka lakukan kode dibawah ini
            if (count($karyawan) > 0) {
                $karyawan = $karyawan[0];

                $karyawan->position_name = $this->printPositionName($karyawan->position_code) ? $this->printPositionName($karyawan->position_code) : '';
                $karyawan->dept_name = $this->printDeptnName($karyawan->dept_code) ? $this->printDeptnName($karyawan->dept_code) : '';

                // lakukan pengecekan expired qr code yang telah dilempar oleh kodenya
                /**
                 * Apabila jam >= 7 dan jam <= 23
                 */
                if ($hours > 2 && $hours < 23) {
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

                        // apabila belum ada masukkan ke tabel 
                        DB::beginTransaction();
                        try {
                            DB::table("tbl_scanlog")
                                ->insert([
                                    "unique_key_device" => $uuid,
                                    "createdate" => date("Y-m-d H:i:s"),
                                    "indicator" => $status_toggle
                                ]);
                            DB::commit();
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            $status_check = 0;
                            $message = "SERVER ERROR";
                            $data = [
                                "message" => $message,
                                "status_code" => 400,
                                "status_check" => $status_check,
                                "data" => []
                            ];
                            return $data;
                        }

                        $status_check = 1;
                        $message = "DEVICE FOUND";

                        $karyawan->img_dpn =  $karyawan->img_dpn;
                        $karyawan->img_blk =  $karyawan->img_blk;

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
                    if ($hours == 24) {
                        $combineDateNIHour = (new DateTime())->setDate($tahun, $bulan, $hari - 1)->setTime($span2->h, $span2->i, $span2->s);
                    }
                    $minHour = (new DateTime())->sub(new DateInterval('PT2H'));
                    $maxHour = (new DateTime())->add(new DateInterval('PT1H'));

                    if ($minHour < $combineDateNIHour && $combineDateNIHour < $maxHour) {

                        // masukkan ke data tabel scan log
                        DB::beginTransaction();
                        try {
                            DB::table("tbl_scanlog")
                                ->insert([
                                    "unique_key_device" => $uuid,
                                    "createdate" => date("Y-m-d H:i:s"),
                                    "indicator" => $status_toggle
                                ]);
                            DB::commit();
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            $status_check = 0;
                            $message = "SERVER ERROR";
                            $data = [
                                "message" => $message,
                                "status_code" => 400,
                                "status_check" => $status_check,
                                "data" => []
                            ];
                            return $data;
                        }

                        $status_check = 1;
                        $message = "DEVICE FOUND";

                        $karyawan->img_dpn = $karyawan->img_dpn;
                        $karyawan->img_blk = $karyawan->img_blk;

                        $data = [
                            "status_check" => $status_check,
                            "status_code" => 200,
                            "message" => $message,
                            "data"    => $karyawan
                        ];

                        return $data;
                    } else {
                        $status_check = 0;
                        $message =  "QR CODE EXPIRED";

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

                // apabila belum ada masukkan ke tabel 
                DB::beginTransaction();
                try {
                    DB::table("tbl_scanlog")
                        ->insert([
                            "unique_key_device" => $uuid,
                            "createdate" => date("Y-m-d H:i:s"),
                            "indicator" => 'NOT FOUND'
                        ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    // dd($th->getMessage());
                    $status_check = 0;
                    $message = "SERVER ERROR";
                    $data = [
                        "message" => $message,
                        "status_code" => 400,
                        "status_check" => $status_check,
                        "data" => []
                    ];
                    return $data;
                }

                $status_check = 0;
                $message = "DEVICE NOT REGISTERED OR NOT ACTIVE";

                $data = [
                    "message" => $message,
                    "status_code" => 400,
                    "status_check" => $message,
                    "data" => $karyawan
                ];

                return $data;
            }
        }

        return false;
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
                    "password" => bcrypt($request->new_password),
                    "is_reset" => 1
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
                "message" => "Sukses melakukan update password dan set security questoon"
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "Kami menemukan user sudah pernah melakukan login pertama kali"
            ], 400);
        }
    }

    /**
     * function untuk cek barcode label dari tbl_mms dan tbl_lms
     */
    private function checkBarcodeLabelNew($barcode, $status_toggle)
    {

        $check_mms = DB::select("SELECT COUNT(*) as jlh FROM tbl_mms WHERE barcode_label = '$barcode'");
        $jlh = $check_mms[0]->jlh;

        // apabila ada di mms
        if ($jlh > 0) {

            // eksekusi mms
            $query_mms = "SELECT a.badge_id, fullname, dept_code, position_code, img_dpn, img_blk, img_user, stat_title AS STATUS FROM tbl_mms AS a
                                        JOIN tbl_karyawan AS b ON a.badge_id = b.badge_id
                                        JOIN tbl_statusmms AS c ON a.status_pendaftaran_mms = c.id
                                        WHERE barcode_label = '$barcode'  AND a.is_active = '1' LIMIT 1";

            $data_mms = DB::select($query_mms);

            if ($data_mms) {
                // apabila belum ada masukkan ke tabel 
                DB::beginTransaction();
                try {
                    DB::table("tbl_scanlog")
                        ->insert([
                            "unique_key_device" => $barcode,
                            "createdate" => date("Y-m-d H:i:s"),
                            "indicator" => $status_toggle
                        ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    $status_check = 0;
                    $message = "SERVER ERROR";
                    $data = [
                        "message" => $message,
                        "status_code" => 400,
                        "status_check" => $status_check,
                        "data" => []
                    ];
                    return $data;
                }

                // rebuild response
                $data_mms[0]->dept_code =  $data_mms[0]->dept_code ?  $data_mms[0]->dept_code : '';
                $data_mms[0]->dept_name = $this->printDeptnName($data_mms[0]->dept_code) ? $this->printDeptnName($data_mms[0]->dept_code) : '';
                $data_mms[0]->position_code =  $data_mms[0]->position_code ?  $data_mms[0]->position_code : '';
                $data_mms[0]->position_name = $this->printPositionName($data_mms[0]->position_code) ? $this->printPositionName($data_mms[0]->position_code) : '';

                $status_check = 1;
                $message = "DEVICE FOUND";
                $data = [
                    "message" => $message,
                    "status_code" => 200,
                    "status_check" => $status_check,
                    "data" => $data_mms[0] ? $data_mms[0] : []
                ];
                return $data;
            }

            // masukin ke scan log apabila ada error
            DB::beginTransaction();
            try {
                DB::table("tbl_scanlog")
                    ->insert([
                        "unique_key_device" => $barcode,
                        "createdate" => date("Y-m-d H:i:s"),
                        "indicator" => 'NOT FOUND'
                    ]);
                DB::commit();
            } catch (\Throwable $th) {
                // dd($th->getMessage());  
                DB::rollBack();
                $status_check = 0;
                $message = "SERVER ERROR";
                $data = [
                    "message" => $message,
                    "status_code" => 400,
                    "status_check" => $status_check,
                    "data" => []
                ];
                return $data;
            }

            // apabila tidak ada korelasi 
            $status_check = 1;
            $message = "DEVICE FOUND, BUT KARYAWAN NOT FOUND";
            $data = [
                "message" => $message,
                "status_code" => 400,
                "status_check" => $status_check,
                "data" => []
            ];
            return $data;
        }

        // apabila ada di lms
        if ($jlh ==  0) {

            // eksekusi ke tabel lms

            $today = date('Y-m-d');

            // $query_lms = "SELECT a.badge_id, fullname, dept_code, position_code, img_dpn, img_blk, img_user, end_date, stat_title AS STATUS FROM tbl_lms AS a
            // JOIN tbl_karyawan AS b ON a.badge_id = b.badge_id
            // JOIN tbl_statuslms AS c ON a.status_pendaftaran_lms = c.id
            // WHERE barcode_label = '$barcode' AND a.is_active = 1 LIMIT 1";

            $query_lms = "SELECT a.badge_id, fullname, dept_code, position_code, img_dpn, img_blk, img_user, end_date, stat_title AS STATUS FROM tbl_lms AS a
            JOIN tbl_karyawan AS b ON a.badge_id = b.badge_id
            JOIN tbl_statuslms AS c ON a.status_pendaftaran_lms = c.id
            WHERE barcode_label = '$barcode' LIMIT 1";

            $data_lms = DB::select($query_lms);

            if ($data_lms) {

                // cek apakah enddate nya sudah expired 
                $end_date = $data_lms[0]->end_date ? $data_lms[0]->end_date : null;
                if ($end_date != null) {
                    if ($end_date < $today) {
                        $status_check = 0;
                        $message = "DEVICE PERMISSION EXPIRED";
                        $data = [
                            "message" => $message,
                            "status_code" => 400,
                            "status_check" => $status_check,
                            "data" => []
                        ];
                        return $data;
                    }
                }

                // insialisasi 
                $data_lms[0]->dept_code =  $data_lms[0]->dept_code ?  $data_lms[0]->dept_code : '';
                $data_lms[0]->dept_name = $this->printDeptnName($data_lms[0]->dept_code) ? $this->printDeptnName($data_lms[0]->dept_code) : '';
                $data_lms[0]->position_code =  $data_lms[0]->position_code ?  $data_lms[0]->position_code : '';
                $data_lms[0]->position_name = $this->printPositionName($data_lms[0]->position_code) ? $this->printPositionName($data_lms[0]->position_code) : '';

                $data_scanlogs = DB::table("tbl_scanlog")
                    ->where("unique_key_device", $barcode)
                    ->orderBy('id', 'desc')
                    ->first();

                // apabila data scan logs ada
                if ($data_scanlogs) {
                    // apabila data scanlogs ada
                    /**
                     * seandainya karyawan terakhir scan keluar
                     * dan ketika satpam mau scan, tapi terpilih toggle out
                     */
                    if ($data_scanlogs->indicator == "OUT" && $status_toggle == "OUT") {
                        $status_check = 0;
                        $message = "SCAN IN HARUS DI LAKUKAN TERLEBIH DAHULU";
                        $data = [
                            "message" => $message,
                            "status_code" => 400,
                            "status_check" => $status_check,
                            "data" => []
                        ];
                        return $data;
                    }

                    if ($data_scanlogs->indicator == "IN" && $status_toggle == "IN") {
                        $status_check = 0;
                        $message = "SCAN OUT HARUS DI LAKUKAN TERLEBIH DAHULU";
                        $data = [
                            "message" => $message,
                            "status_code" => 400,
                            "status_check" => $status_check,
                            "data" => []
                        ];
                        return $data;
                    }
                }

                /**
                 * apabila laptop belum ada di insert sama sekali, harusnya 
                 * laptop melakukan login terlebih dahulu
                 */
                if (!$data_scanlogs && $status_toggle == "OUT") {
                    $status_check = 0;
                    $message = "SCAN IN HARUS DI LAKUKAN TERLEBIH DAHULU";
                    $data = [
                        "message" => $message,
                        "status_code" => 400,
                        "status_check" => $status_check,
                        "data" => []
                    ];
                    return $data;
                }

                // apabila belum ada masukkan ke tabel 
                DB::beginTransaction();
                try {
                    DB::table("tbl_scanlog")
                        ->insert([
                            "unique_key_device" => $barcode,
                            "createdate" => date("Y-m-d H:i:s"),
                            "indicator" => $status_toggle
                        ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    $status_check = 0;
                    $message = "SERVER ERROR";
                    $data = [
                        "message" => $message,
                        "status_code" => 400,
                        "status_check" => $status_check,
                        "data" => []
                    ];
                    return $data;
                }

                // lakukan cek ke tabel log
                $status_check = 1;
                $message = "DEVICE FOUND";
                $data = [
                    "message" => $message,
                    "status_code" => 200,
                    "status_check" => $status_check,
                    "data" => $data_lms[0]
                ];
                return $data;
            }

            // input ke tabel logs
            DB::beginTransaction();
            try {
                DB::table("tbl_scanlog")
                    ->insert([
                        "unique_key_device" => $barcode,
                        "createdate" => date("Y-m-d H:i:s"),
                        "indicator" => 'NOT FOUND'
                    ]);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                // dd($th->getMessage());
                $status_check = 0;
                $message = "SERVER ERROR";
                $data = [
                    "message" => $message,
                    "status_code" => 400,
                    "status_check" => $status_check,
                    "data" => []
                ];
                return $data;
            }

            $status_check = 0;
            $message = "DEVICE NOT FOUND";
            $data = [
                "message" => $message,
                "status_code" => 400,
                "status_check" => $status_check,
                "data" => []
            ];
            return $data;
        }
    }

    /**
     * function print position name
     */
    private function printPositionName($posistion_code)
    {
        $data = DB::table('tbl_position')
            ->where('position_code', $posistion_code)
            ->first();

        if ($data) {
            return $data->position_name;
        }
    }

    /**
     * function print departement name
     */
    private function printDeptnName($dept_code)
    {
        $data = DB::table('tbl_deptcode')
            ->where('dept_code', $dept_code)
            ->first();

        if ($data) {
            return $data->dept_name;
        }
    }
}
