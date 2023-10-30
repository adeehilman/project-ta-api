<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CronJobController extends Controller
{
    // ambil api dari MIS
    /**
     * ambil dari API MIS, dan insert ke tbl_sisacuti
     * lakukan proses ini pada tengah malam
     * truncate dan insert
     */
    public function getSisaCuti()
    {

        /**
         * ini adalah proses insialisasi
         * tahun dan bulan sekarang
         */
        $tahun_skrg = date("Y");
        $bulan_skrg = date("m");
        $bulan_skrg = (int)$bulan_skrg;

        /**
         * lalu menggunakan guzzle panggil API
         * yang telah disediakan dept MIS
         */
        $client = new Client();
        $response = $client->post("http://snws07:8000/api/DOT/GetEmployeeLeave?dept=%&year=" . $tahun_skrg . "&month=" . $bulan_skrg . "");
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $data = json_decode($response->getBody(), true);
            $employeArray = json_decode($data['DATA']);
            /**
             * setelah kita mendapatkan response dari api
             * yang dimaksud maka bisa di insert ke tabel sisa cuti
             * dengan value yang ditulis dalam kode dibawah ini
             * 
             * sisa cuti kenapa ditambah dari  $item->GetLeaveNextMonth,
             * karena saat pengembangan memang begitu formula nya
             * apabila ingin pas dengan aplikasi yang di HR
             * maka lakukan permintaan data hitungan sisa cuti dari MIS
             * berupa response bilangan bulat 
             */
            try {
                DB::table('tbl_sisacuti')->truncate();
                foreach ($employeArray as $key => $item) {
                    DB::table('tbl_sisacuti')
                        ->insert([
                            "badge_id"      => $item->EmployeeNo,
                            "notes"         => $item->Note,
                            "sisa_cuti"     => $item->BalanceThisMonth + $item->GetLeaveNextMonth,
                            "last_update"   => date("Y-m-d H:i:s")
                        ]);
                }
                return response()->json([
                    "message" => "Pembaruan Sisa Cuti Sukses, pada " . date("Y-m-d H:i:s"),
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    "message" => "Something went wrong"
                ], 400);
            }
        }
    }

    // ambil dari API MIS
    /**
     * ambil dari API MIS, dan insert ke database netraya_canteen
     * lakukan proses ini pada tengah malam
     * lakukan truncate dan lakukan insert
     */
    public function getAccessDoor()
    {

        $today = date("Y-m-d");
        $besok = date("Y-m-d", strtotime("+1 day", strtotime($today)));

        $client = new Client();
        $response = $client->post("http://snws07:8001/api/DOT/GetAccessDoorData?date_from=" . $today . "&date_to=" . $besok . "&plant=%");
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $data = json_decode($response->getBody(), true);
            $employeArray = json_decode($data['DATA']);

            try {
                DB::connection('second')->table('tbl_logaccessdoor')
                        ->where('scandate', $today)
                        ->delete();
                foreach ($employeArray as $key => $item) {
                    DB::connection('second')->table('tbl_logaccessdoor')
                    ->insert([
                        "badgeID" => $item->employeeno,
                        "scandate" => $item->scandate,
                        "firsttimescan" => $item->scandate . ' ' .$item->firsttimescan,
                        "lasttimescan"  => $item->scandate . ' ' .$item->lasttimescan,
                        "lastupdate" => date("Y-m-d H:i:s"),
                        "device" => "ACCESS_DOOR"
                    ]);
                }
                return response()->json([
                    "message" => "Pembaruan Access Door, pada " . date("Y-m-d H:i:s"),
                ]);
            } catch (\Throwable $th) {
                dd($th->getMessage());
                return response()->json([
                    "message" => "Something went wrong"
                ], 400);
            }
        }
    }
}
