<?php

namespace App\Http\Controllers\API\Forklift\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MasterKeyController extends Controller
{
    public function __construct(){
        $this->second = DB::connection('second');
    }
    public function index(Request $request)
    {
        //validasi token
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

        $rfidNos = $this->second->table('tbl_masterkey')
            ->select('id', 'rfidno as MASTERKEY')
            ->where('status', '1') // Menggunakan 'as' untuk memberi nama kembali kolom 'rfidno' menjadi 'MASTERKEY'
            ->get();

        // Ubah hasil ke dalam format yang diinginkan
        $formattedData = [];
        foreach ($rfidNos as $rfidNo) {
            $formattedData[] = [
                'ID' => $rfidNo->id,
                'MASTERKEY' => $rfidNo->MASTERKEY,
            ];
        }

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $formattedData,
        ]);
    }
}
