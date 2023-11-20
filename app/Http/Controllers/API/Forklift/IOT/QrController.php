<?php

namespace App\Http\Controllers\API\Forklift\IOT;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class QrController extends Controller
{
    public function __construct(){
        $this->second = DB::connection('second');
    }
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

        $id = $request->id;

        // cek id apakah valid
        $user = $this->second->table('tbl_forklift')
            ->where('id', $id)
            ->first();
        if (!$user) {
            return response()->json(
                [
                    'RESPONSE_CODE' => 404,
                    'MESSAGETYPE' => 'E',
                    'MESSAGE' => 'Not Found',
                ],
                404,
            );
        } else {
            $this->second->beginTransaction();
            try {
                $checkstatus = $this->second->table('tbl_forklift')
                    ->where('id', $id)
                    ->value('id_status');

                // cek status forklift
                if ($checkstatus == 1) {
                    // jika status sama dengan 1
                    $qrCodeCheck = $this->second->table('tbl_forklift')
                        ->where('id', $id)
                        ->value('qrcode');

                    return response()->json([
                        'MESSAGETYPE' => 'S',
                        'MESSAGE' => 'Data retrieved Successfully',
                        'DATA' => [
                            'QRCODE' => $qrCodeCheck,
                        ],
                    ]);
                } else {
                    $uniqueId = $this->second->table('tbl_forklift')
                        ->where('id', $id)
                        ->value('uniqueid');

                    $timestamp = now()->timestamp;

                    $qrcode = md5($uniqueId . $timestamp);
                    $this->second->table('tbl_forklift')
                        ->where('id', $id)
                        ->update([
                            'qrcode' => $qrcode,
                        ]);

                    $this->second->commit();
                    // jika status sama dengan 1
                    $qrCodeCheck = $this->second->table('tbl_forklift')
                        ->where('id', $id)
                        ->value('qrcode');

                    return response()->json([
                        'MESSAGETYPE' => 'S',
                        'MESSAGE' => 'Data retrieved Successfully',
                        'DATA' => [
                            'QRCODE' => $qrCodeCheck,
                        ],
                    ]);
                }
            } catch (\Throwable $th) {
                return response()
                    ->json(
                        [
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'Something when wrong',
                        ],
                        400,
                    )
                    ->header('Accept', 'application/json');
            }
        }
    }
}
