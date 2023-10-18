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

        try {
            // lakukan query untuk get list notifikasi
            $query = "SELECT 
                    id as Id,
                    title as Title,
                    description as Description, 
                    category as Category,
                    createdate as Create_Date,
                    badge_id as Badge_Id,
                    isread as Is_Read, 
                    read_date as Read_Date
            FROM tbl_notification WHERE badge_id = '$badgeId' ";
            $data  = DB::select($query);

            if (COUNT($data) > 0) {
                return response()->json([
                    "RESPONSE"      => 200,
                    "MESSAGETYPE"   => "S",
                    "MESSAGE"       => "SUCCESS",
                    "DATA"          => $data
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

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            "DATA"          => []
        ]);
    }

    // send notifikasi 
    /**
     * function untuk send notif 
     */
    public function sendNotif(Request $request)
    {


        if (!request()->has('message')) {
            $message = "";
        }


        $badge_id = $request->badge_id;
        $message  = $request->message;
        $category = $request->category;
        $sub_message = $request->sub_message;
        $tag         = $request->tag;


        /**
         * query untuk send notif
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
        $notifikasi = new Notifikasi($message, $sub_message, $tag, $badge_id);
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
