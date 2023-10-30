<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KalenderController extends Controller
{
    // function kalender 
    /**
     * Ini adalah fungsi untuk mendapatkan list kalender
     * dimana ini mengambil dari tbl_kalender
     * dan akan melakukan response hari2 terkiat hari libur nasional
     */
    public function getAllList(Request $request)
    {

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $hari  = $request->hari;

        /**
         * apabila tidak ada hari
         */
        // if (request()->has('hari')) {

        //     $tanggal = date("Y-m-d", strtotime("$tahun-$bulan-$hari"));
        //     $query_cek = "SELECT * FROM tbl_kalender WHERE tanggal = '$tanggal'";
        //     $ket_hari_ini = DB::select($query_cek);


        //     $query = "SELECT * FROM tbl_kalender WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal ASC";
        //     $all_data = DB::select($query);

        //     $fill_colored_at = [];
        //     $keterangan      = [];

        //     $current_event = null;
        //     $current_start_date = null;
        //     $current_end_date = null;


        //     foreach ($all_data as $key => $item) {
        //         array_push($fill_colored_at, $item->tanggal);

        //         if ($item->acara === $current_event) {
        //             $current_end_date = $item->tanggal;
        //         } else {
        //             if ($current_event !== null) {
        //                 $date_range = $this->formatDateRange($current_start_date, $current_end_date);
        //                 $keterangan[] = $date_range . ' : ' . $current_event;
        //             }

        //             $current_event = $item->acara;
        //             $current_start_date = $item->tanggal;
        //             $current_end_date = $item->tanggal;
        //         }
        //     }

        //     // Menambahkan entitas terakhir ke array "keterangan"
        //     if ($current_event !== null) {
        //         $date_range = $this->formatDateRange($current_start_date, $current_end_date);
        //         $keterangan[] = $date_range . ' : ' . $current_event;
        //     }

        //     $fill_colored_at = array_map(function ($date) {
        //         return date("Y-m-d 00:00:00.000\Z", strtotime($date));
        //     }, $fill_colored_at);

        //     return response()->json([
        //         "message" => "RESPONSE ALL KALENDER OK",
        //         "data"    => [
        //             "fill_colored_at" => $fill_colored_at,
        //             "keterangan_hari_ini" => $ket_hari_ini ? $ket_hari_ini[0]->acara : "Tidak ada agenda",
        //             "keterangan_bulan_ini" => array_values($keterangan)
        //         ]
        //     ]);
        // }

        // Cache::flush();

        $cache_key = "TAHUN_" .$tahun . "_BULAN_" .$bulan;
        if (Cache::has($cache_key)) {
            $result = Cache::get($cache_key);
            return response()->json([
                "message" => "RESPONSE ALL KALENDER OK",
                "data"    => $result
            ]);
        }
        else {
            // Insialisasi query database 
            $query = "SELECT * FROM tbl_kalender WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal ASC";
            $all_data = DB::select($query);

            $fill_colored_at = [];
            $keterangan      = [];

            $current_event = null;
            $current_start_date = null;
            $current_end_date = null;

            // Lakukan perulangan dari data tang telah didapatkan 
            foreach ($all_data as $key => $item) {
                array_push($fill_colored_at, $item->tanggal);

                if ($item->acara === $current_event) {
                    $current_end_date = $item->tanggal;
                } else {
                    if ($current_event !== null) {
                        $date_range = $this->formatDateRange($current_start_date, $current_end_date);
                        $keterangan[] = $date_range . ' : ' . $current_event;
                    }

                    $current_event = $item->acara;
                    $current_start_date = $item->tanggal;
                    $current_end_date = $item->tanggal;
                }
            }

            // Menambahkan entitas terakhir ke array "keterangan"
            if ($current_event !== null) {
                $date_range = $this->formatDateRange($current_start_date, $current_end_date);
                $keterangan[] = $date_range . ' : ' . $current_event;
            }

            $fill_colored_at = array_map(function ($date) {
                return date("Y-m-d 00:00:00.000\Z", strtotime($date));
            }, $fill_colored_at);

            /**
             * dan ini response code yang dilempar ke movike
             */
            $data = [
                "fill_colored_at" => $fill_colored_at,
                "keterangan_hari_ini" =>"Tidak ada agenda",
                "keterangan_bulan_ini" => array_values($keterangan)
            ];

            $times = 60 * 60 * 24;
            Cache::put($cache_key, $data, $times);

            return response()->json([
                "message" => "RESPONSE ALL KALENDER OK",
                "data"    => $data
            ]);
        }
    }

    private function formatDateRange($start_date, $end_date)
    {
        $start_day = date("d", strtotime($start_date));
        $end_day = date("d", strtotime($end_date));

        if ($start_day === $end_day) {
            return $start_day;
        } else {
            return $start_day . '-' . $end_day;
        }
    }
}
