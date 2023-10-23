<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KecamatanController extends Controller
{
    // get all kecamatan 
    /**
     * ini adlaah sebuah fungsi untuk
     * melakukan get list all kecamatamn
     * dari tabel kecamaran
     */
    public function getAllKecamatan()
    {

        /**
         * apabila memiliki cache, maka ambil datanya
         * dari cache apabila tidak ambil dari tabel kecamatan
         */
        if (Cache::has('all_kecamatan')) {
            $data = Cache::get('all_kecamatan');
        } else {
            // second * minute * hour * day  
            $times = 60 * 60 * 24 * 1;
            //$times = 60;

            $data = DB::table('tbl_kecamatan')->get();

            // letakkan di cache
            Cache::put('all_kecamatan', $data, $times); // 60 second
        }


        return response()->json([
            "message" => "RESPONSE ALL KECAMATAN OK",
            "data"    => $data
        ]);
    }
}
