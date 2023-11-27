<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ini merupakan sebuah class yang melakukan handle
 * sharing endpoint kepada aplikasi lainnya.
 * mislakan aplikasi digital sop ingin mendapatkan credentials login
 * maka buat saja fungsi dari hal tersbeut disini.
 *
 */
class PlatformController extends Controller
{
    /**
     * function untuk send notif
     */
    public function sendNotif(Request $request)
    {
        if (!request()->has('message')) {
            $message = '';
        }

        $badge_id = $request->badge_id;
        $message = $request->message;
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
                'en' => 'Tap untuk membaca informasi lebih lanjut',
            ],
            'data' => [
                'Category' => 'DIGITAL_SOP',
            ],
        ];

        // Konversi data ke format JSON
        $dataJson = json_encode($data);

        // Pengaturan opsi cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic NmQ2ODI0YjEtNjZhYy00ZDA3LWJkMDEtY2ViZDJjZWNmMTk5', 'Content-Type: application/json']);
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

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
        ]);
    }

    /**
     * get user
     */
    public function getUserInfo(Request $request)
    {
        $badge_id = $request->badge_id;
        $query = "SELECT
                        fullname as Fullname,
                        position_code as Position_Code
                    FROM tbl_karyawan WHERE badge_id = '$request->badge_id'";
        $data = DB::select($query);

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $data[0],
        ]);
    }

    /**
     * login from multi based web or mobile
     * from table my satnusa to user
     */
    public function checkCredentials(Request $request)
    {
        $employee_no = $request->employee_no;
        $password = $request->password;

        $query = "SELECT id, badge_id, fullname, password, position_code, line_code, dept_code, is_active FROM tbl_karyawan
                  WHERE badge_id = '$employee_no' ";
        $data = DB::select($query);

        if (COUNT($data) > 0) {
            $employee = $data[0];
            $checkPassword = Hash::check($password, $employee->password);

            if ($checkPassword) {
                if ($employee->is_active == 0) {
                    return response()
                        ->json(
                            [
                                'RESPONSE_CODE' => 400,
                                'MESSAGETYPE' => 'E',
                                'MESSAGE' => 'Sorry, your account is not active',
                            ],
                            401,
                        )
                        ->header('Accept', 'application/json');
                }

                $data = [
                    'Id' => $employee->id,
                    'Badge_Id' => $employee->badge_id,
                    'Fullname' => $employee->fullname,
                    'Position_Code' => $employee->position_code,
                    'Line_Code' => $employee->line_code,
                    'Dept_Code' => $employee->dept_code,
                ];

                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'SUCCESS',
                    'DATA' => $data,
                ]);
            } else {
                return response()
                    ->json(
                        [
                            'RESPONSE_CODE' => 400,
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'Badge or Password wrong',
                        ],
                        401,
                    )
                    ->header('Accept', 'application/json');
            }
        }

        if (COUNT($data) == 0) {
            return response()
                ->json(
                    [
                        'RESPONSE_CODE' => 400,
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'Badge Not Found',
                    ],
                    401,
                )
                ->header('Accept', 'application/json');
        }
    }

    /**
     * get user by name or badge
     */
    public function listUserBy(Request $req)
    {
        $query = "SELECT badge_id, fullname, dept_code FROM tbl_karyawan
                        WHERE badge_id LIKE '%$req->user%' OR fullname LIKE '%$req->user%' LIMIT 100";
        $data = DB::select($query);

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $data,
        ]);
    }

    public function uploadFile(Request $request)
    {
        // dd($request->all());

        // Pastikan request memiliki file dengan nama 'file'
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');

            // Simpan file di dalam direktori public/RoomMeeting
            $file->move(public_path('RoomMeetingFoto/'), $file->getClientOriginalName());

            return response()->json(['message' => 'File berhasil diupload']);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    public function EmployeeImg(Request $request)
    {
        $badge = $request->badge_id;
        // dd($badge);
        $query = "SELECT img_user FROM tbl_karyawan WHERE badge_id = '$badge'";
        $data = DB::select($query);

        // Pastikan ada data yang ditemukan sebelum mencoba mengakses properti
        if (!empty($data)) {
            $base64Image = $data[0]->img_user;
        } else {
            $base64Image = null; // Atau nilai default sesuai kebutuhan
        }

        return response($base64Image)->header('Content-Type', 'text/plain');
    }

    public function themeEvent(Request $request)
    {
        $query = DB::table('tbl_mobiletheme')
            ->whereDate('startdate', '>=', now()) 
            ->orderBy('startdate', 'asc')
            ->limit(1)
            ->first();

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $query,
        ]);
    }
}
