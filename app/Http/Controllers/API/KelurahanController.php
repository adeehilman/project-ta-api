<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelurahanController extends Controller
{
    // get all kelurahan
    public function getAllKelurahan(Request $request)
    {
        $data = DB::table('tbl_kelurahan')
            ->where('id_kecamatan', $request->id_kecamatan)
            ->get();
        return response()->json([
            "message" => "RESPONSE ALL KELURAHAN OK",
            "data"    => $data
        ]);
    }
}
