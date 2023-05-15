<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KecamatanController extends Controller
{
    // get all kecamatan 
    public function getAllKecamatan(){
        $data = DB::table('tbl_kecamatan')
                        ->get();
        return response()->json([
            "message" => "RESPONSE ALL KECAMATAN OK",
            "data"    => $data
        ]);
    }
}
