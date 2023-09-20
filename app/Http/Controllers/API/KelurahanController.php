<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KelurahanController extends Controller
{
    // get all kelurahan
    public function getAllKelurahan(Request $request)
    {
        // $data = DB::table('tbl_kelurahan')
        //     ->where('id_kecamatan', $request->id_kecamatan)
        //     ->get();

        $idKecamatan = $request->id_kecamatan;
        $times = 60 * 60 * 24 * 1;

        // Membuat cache dengan label yang unik berdasarkan nilai parameter $idKecamatan
        $data = Cache::remember('kelurahan_' . $idKecamatan, $times, function () use ($idKecamatan) {
            return DB::table('tbl_kelurahan')
                ->where('id_kecamatan', $idKecamatan)
                ->get();
        });

        return response()->json([
            "message" => "RESPONSE ALL KELURAHAN OK",
            "data"    => $data
        ]);
    }
}
