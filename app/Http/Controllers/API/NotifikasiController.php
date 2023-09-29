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
    }
}
