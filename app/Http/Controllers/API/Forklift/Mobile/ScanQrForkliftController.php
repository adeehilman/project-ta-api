<?php

namespace App\Http\Controllers\API\Forklift\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ScanQrForkliftController extends Controller
{
    public function __construct(){
        $this->second = DB::connection('second');
    }
    public function index(Request $request)
    {
        // cek authorization token pada header
        try {
            $token = $request->header('Authorization');
            $validateToken = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()
                ->json(
                    [
                        'RESPONSE_CODE' => 401,
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'UNAUTHORIZED',
                    ],
                    401,
                )
                ->header('Accept', 'application/json');
        }

        //Check QRCODE apakah ada pada database

        $qrcode = $request->qrcode;
        $forklift = "SELECT f.id,f.name,f.id_status , f.iscoverage,
        (SELECT vl.name FROM tbl_vlookup vl WHERE vl.category = 'FORKLIFT'
        AND vl.id = f.id_status)as name_status
        from tbl_forklift f
        where f.qrcode = '$qrcode'";
        $query = $this->second->select($forklift);

        if ($query) {
            if($query[0]->iscoverage != 1){
                return response()
                ->json(
                    [
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'Gagal Scan, Pastikan Forklift online!',
                    ],
                    400,
                )
                ->header('Accept', 'application/json');
            }
            return response()->json([
                'RESPONSE' => 200,
                'MESSAGETYPE' => 'S',
                'MESSAGE' => 'Data retrieved Successfully',
                'DATA' => $query[0],
            ]);
        } else {
            return response()
                ->json(
                    [
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'Forklift tidak ditemukan',
                    ],
                    400,
                )
                ->header('Accept', 'application/json');
        }
    }

    public function scanQrLocation(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            $validateToken = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()
                ->json(
                    [
                        'RESPONSE_CODE' => 401,
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'UNAUTHORIZED',
                    ],
                    401,
                )
                ->header('Accept', 'application/json');
        }

        //Check QRCODE apakah ada pada database

        $qrcode_location = $request->qr_location;
        $checkLocation = "SELECT * FROM tbl_location WHERE uniqueid = '$qrcode_location'";
        $q = $this->second->select($checkLocation);

        // dd($q);

        if (!empty($q)) {
            if ($q[0]->id_status == 1) {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => $q[0],
                ]);
            } else {
                return response()
                    ->json(
                        [
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'Lokasi sedang dalam Maintenance',
                        ],
                        400,
                    )
                    ->header('Accept', 'application/json');
            }
        } else {
            return response()
                ->json(
                    [
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'Lokasi tidak ditemukan',
                    ],
                    400,
                )
                ->header('Accept', 'application/json');
        }

        // if ($q) {
        //     if ($q->id_status == 1) {
        //         return response()->json([
        //             'RESPONSE' => 200,
        //             'MESSAGETYPE' => 'S',
        //             'MESSAGE' => 'Data retrieved Successfully',
        //             'DATA' => $q,
        //         ]);
        //     } else {
        //         return response()
        //             ->json(
        //                 [
        //                     'MESSAGETYPE' => 'E',
        //                     'MESSAGE' => 'Location Under Maintenance',
        //                 ],
        //                 400,
        //             )
        //             ->header('Accept', 'application/json');
        //     }
        // } else {
        //     return response()
        //         ->json(
        //             [
        //                 'MESSAGETYPE' => 'E',
        //                 'MESSAGE' => 'Location not found',
        //             ],
        //             400,
        //         )
        //         ->header('Accept', 'application/json');
        // }
    }
}
