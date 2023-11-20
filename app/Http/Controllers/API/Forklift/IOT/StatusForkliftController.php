<?php

namespace App\Http\Controllers\API\Forklift\IOT;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class StatusForkliftController extends Controller
{
    public function index(Request $request)
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

        $statusForklift = DB::table('tbl_vlookup')
            ->select('id', 'name as NAME')
            ->where('category', 'FORKLIFT')
            ->orderBy('sequence', 'asc') // Menggunakan 'as' untuk memberi nama kembali kolom 'rfidno' menjadi 'MASTERKEY'
            ->get();

        // Ubah hasil ke dalam format yang diinginkan
        $formattedData = [];
        foreach ($statusForklift as $item) {
            $formattedData[] = [
                'ID' => $item->id,
                'NAME' => $item->NAME,
            ];
        }

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'Data retrieved Successfully',
            'DATA' => $formattedData,
        ]);
    }
}
