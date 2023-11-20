<?php

namespace App\Http\Controllers\API\Forklift\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class HeartbeatController extends Controller
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

        $user = $this->second->table('tbl_forklift')
            ->where('id', $id)
            ->first();
        if (!$user) {
            return response()->json(
                [
                    'RESPONSE_CODE' => 401,
                    'MESSAGETYPE' => 'E',
                    'MESSAGE' => 'Not Found',
                ],
                401,
            );
        } else {
            $this->second->beginTransaction();
            try {
                $this->second->table('tbl_heartbeat')->insert([
                    'id_forklift' => $id,
                    'timestamp' => now(),
                ]);

                $this->second->commit();

                return response()->json([
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                ]);
            } catch (\Throwable $th) {
                return response()
                    ->json(
                        [
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => $th,
                        ],
                        400,
                    )
                    ->header('Accept', 'application/json');
            }
        }
    }
}

