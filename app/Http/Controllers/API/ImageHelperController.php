<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImageHelperController extends Controller
{
    /**
     * Ini adalah fungsi untuk mendapatkan informasi
     * gambar dari ruangan meeting
     */
    public function getImageRoom(Request $request){

        $jumlahImg = $request->images;
        $idRoom    = $request->id_room;

        if($jumlahImg > 3 || $jumlahImg < 0){
            return response()->json([
                "message" => "Images tidak boleh lebih dari tiga atau lebih kecil dari 0"
            ], 400);
        }

        $query = "SELECT ";

        if($jumlahImg == 1){
            $key    = "roomimage_1";
            $query .= "roomimage_1 FROM tbl_roommeeting WHERE id = '$idRoom' ";
            $data_image = DB::select($query);
        }

        if($jumlahImg == 2){
            $key    = "roomimage_1, roomimage_2";
            $query .= "roomimage_1, roomimage_2 FROM tbl_roommeeting WHERE id = '$idRoom' ";
            $data_image = DB::select($query);
        }

        if($jumlahImg == 3){
            $key    = "roomimage_1, roomimage_2, roomimage_3";
            $query .= "roomimage_1, roomimage_2, roomimage_3 FROM tbl_roommeeting WHERE id = '$idRoom' ";
            $data_image = DB::select($query);
        }
     
        return response()->json([
            "message" => "RESPONSE ROOM IMAGE OK",
            "key"     => $key,
            "data"    => $data_image
        ]);
    }
}
