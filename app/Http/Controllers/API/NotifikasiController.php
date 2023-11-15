<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotifikasiController extends Controller
{
    // buat dapetin list notifikasi by badge 
    public function getListNotifikasi(Request $request)
    {

        try {
            $token = $request->header('Authorization');
            $validateToken = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                "RESPONSE_CODE" => 401,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'UNAUTHORIZED',
            ], 401)->header(
                "Accept",
                "application/json"
            );
        }

        $badgeId = $request->badge_id;
        // $dynamic_Id = $request->dynamic_id;

        try {
            // lakukan query untuk get list notifikasi
            // Di sini, kita melakukan query ke database untuk mengambil data notifikasi berdasarkan badge_id tertentu.
            // Data notifikasi ini kemudian akan digunakan untuk memberikan respons kepada pengguna melalui API.
            $query = "SELECT 
                    id as Id,
                    title as Title,
                    description as Description, 
                    category as Category,
                    createdate as Create_Date,
                    badge_id as Badge_Id,
                    isread as Is_Read, 
                    read_date as Read_Date,
                    dynamic_id as Dynamic_Id
            FROM tbl_notification WHERE badge_id = '$badgeId' ";
            $data  = DB::select($query);

            if (COUNT($data) > 0) {
                return response()->json([
                    "RESPONSE"      => 200,
                    "MESSAGETYPE"   => "S",
                    "MESSAGE"       => "SUCCESS",
                    "DATA"          => $data
                    // "Info_Meeting"     => $dataMeeting[0],
                ]);
            }
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return response()->json([
                "MESSAGETYPE"   => "E",
                "MESSAGE" => "Something when wrong",
            ], 400)->header(
                "Accept",
                "application/json"
            );
        }

        /**
         * - "RESPONSE": Digunakan untuk menyampaikan status respons HTTP. Dalam kasus ini, nilai 200 digunakan untuk
         *   mengindikasikan bahwa operasi telah berhasil. Status respons adalah bagian penting dari respons HTTP, dan
         *   200 OK adalah salah satu kode yang paling umum digunakan untuk menunjukkan kesuksesan.
         *
         * - "MESSAGETYPE": Jenis pesan digunakan untuk memberikan kategori atau jenis respons. Dalam kode ini, "S" mungkin
         *   mengacu pada "Success" (kesuksesan). Ini membantu klien memahami jenis pesan yang mereka terima tanpa harus
         *   menguraikan pesan dalam teks.
         *
         * - "MESSAGE": Pesan ini memberikan deskripsi lebih lanjut tentang hasil operasi. Dalam hal ini, "SUCCESS" digunakan
         *   untuk memberikan konfirmasi bahwa operasi yang diminta berhasil. Pesan ini dapat disesuaikan dengan kebutuhan
         *   aplikasi dan operasi yang dilakukan.
         *
         * - "DATA": Bagian ini dapat digunakan untuk mengirimkan data tambahan dalam respons. Dalam contoh ini, respons tidak
         *   mengandung data tambahan, sehingga disetel ke array kosong. Namun, ini adalah tempat yang sesuai untuk mengirimkan
         *   hasil operasi, daftar objek, atau informasi tambahan yang relevan.
         *
         */

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            "DATA"          => []
        ]);
    }

    // send notifikasi 
    /**
     * function untuk send notif dimana fungsi ini
     * dibuat secara flexibel yaitu terkait message title
     * dan message detailnya. dimana menggunakan one signal 
     * nantinya akan dikirimkan di perangkat pengguna.
     * 
     * setelah itu juga dimasukkan kedalam sebuah tabel notfikasi 
     * yang nantinya akan ditampilkan kedalam list notifikasi 
     * poda aplikasi mobile 
     */
    public function sendNotif(Request $request)
    {

        // dd($request->all());
        if (!request()->has('message')) {
            $message = "";
        }

        /**
         * - $badge_id: Variabel ini digunakan untuk menyimpan "badge_id" yang dikirim dalam permintaan. Biasanya,
         *   ini adalah ID badge yang digunakan untuk mengidentifikasi jenis atau kategori tertentu yang terkait dengan permintaan.
         *
         * - $message: Variabel ini berisi nilai "message" dari permintaan. Nilai ini dapat berupa pesan yang akan ditampilkan
         *   kepada pengguna atau informasi yang akan disimpan dalam database.
         *
         * - $category: Nilai "category" dari permintaan disimpan dalam variabel ini. Biasanya, ini merujuk pada kategori atau jenis
         *   yang terkait dengan data atau operasi yang akan dilakukan.
         *
         * - $sub_message: Variabel ini berisi data yang diterima dengan nama "sub_message" dalam permintaan. Data ini bisa berupa
         *   pesan tambahan atau informasi spesifik yang relevan dengan operasi yang akan dilakukan.
         *
         * - $tag: Nilai "tag" dari permintaan disimpan dalam variabel ini. Biasanya, ini digunakan untuk memberi penanda atau label
         *   pada data atau entitas tertentu dalam aplikasi.
         *
         * Mengambil data dari permintaan HTTP adalah langkah penting dalam pengembangan web dan aplikasi, karena memungkinkan
         * pengembang untuk berinteraksi dengan klien dan menggunakan informasi yang mereka kirimkan. Data ini akan menjadi dasar
         * untuk proses selanjutnya dalam aplikasi, seperti validasi, pemrosesan, penyimpanan, atau pengiriman respons.
         */

      
        $badge_id = $request->badge_id;
        $message  = $request->message;
        $category = $request->category;
        $sub_message = $request->sub_message;
        $tag         = $request->tag;
        $dynamic_Id = $request->dynamic_id;


        /**
         * query untuk send notif
         * dimana disini kamu akan mendapatkan player id
         * dari masing masing mms dan dikirimkan notifnya
         */
        $query_player_id = "SELECT player_id FROM tbl_mms WHERE badge_id = '$badge_id'";
        $data_player_id = DB::select($query_player_id);

        $arr_playerId = [];
        foreach ($data_player_id as $key => $value) {
            if ($value->player_id != null) {
                array_push($arr_playerId, $value->player_id);
            }
        }

        // URL Endpoint API OneSignal
        $url = 'https://onesignal.com/api/v1/notifications';

        // Data untuk dikirim dalam permintaan
        $data = [
            'app_id' => 'ef44a0e1-1de9-48a0-b4c5-9e045d45c0cf',
            'include_player_ids' => $arr_playerId,
            'headings' => [
                'en' => $message,
            ],
            'contents' => [
                'en' => $sub_message
            ],
            'data' => [
                'Category' => $request->category ? $request->category : ''
            ],
        ];

        // Konversi data ke format JSON
        $dataJson = json_encode($data);

        // Pengaturan opsi cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic NmQ2ODI0YjEtNjZhYy00ZDA3LWJkMDEtY2ViZDJjZWNmMTk5',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Eksekusi permintaan cURL
        $response = curl_exec($ch);

        // Periksa jika ada kesalahan dalam permintaan
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            // Lakukan penanganan kesalahan yang sesuai
            // ...
        }

        // Mendapatkan informasi respons
        $info = curl_getinfo($ch);
        $httpCode = $info['http_code'];

        // Menutup koneksi cURL
        curl_close($ch);

        // insert ke tabel notifikasi
        $notifikasi = new Notifikasi($message, $sub_message, $tag, $badge_id, $dynamic_Id);
        $notifikasi->insertNotifikasi();

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS"
        ]);
    }

    // Baca Notifikasi
    public function bacaNotif(Request $request)
    {
        $idNotifikasi = $request->id_notifikasi;

        DB::table('tbl_notification')
            ->where('id', $idNotifikasi)
            ->update([
                'isread' => 1
            ]);

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS"
        ]);
    }
}
