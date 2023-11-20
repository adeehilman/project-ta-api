<?php

namespace App\Http\Controllers\API\Forklift\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ParkingController extends Controller
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

        $id = $request->id;
        $badgeno = $request->badgeno;
        $qrcode_location = $request->qr_location;

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
            $checkLocation = $this->second->table('tbl_location')
                ->where('uniqueid', $qrcode_location)
                ->where('id_status', 1)
                ->first();

            if ($checkLocation) {
                $this->second->beginTransaction();
                try {
                    $checkstatus = $this->second->table('tbl_forklift')
                        ->where('id', $id)
                        ->value('id_status');

                    // cek status forklift
                    if ($checkstatus != 1) {
                        // jika status tidak sama dengan 1
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

                        // dd($checkLocation->id);
                        // update status id di tbl_forklift
                        $this->second->table('tbl_forklift')
                            ->where('id', $id)
                            ->update([
                                'qrcode' => $qrcode,
                                'id_status' => 2,
                                'id_location' => $checkLocation->id,
                            ]);
                        // update endby di tbl_forklift
                        $this->second->table('tbl_forklifthistory')
                            ->where('id_forklift', $id)
                            ->where('id_status', 1)
                            ->where('endtime', null)
                            ->update([
                                'endtime' => now(),
                                'endby' => $badgeno,
                            ]);

                        // insert history forklift status tidak digunakan di tbl_forklift
                        $this->second->table('tbl_forklifthistory')->insert([
                            'id_forklift' => $id,
                            'id_status' => 2,
                            'date' => date('Y-m-d'),
                            'starttime' => date('Y-m-d H:i:s', time() + 1),
                            'startby' => $badgeno,
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
                    dd($th->getMessage());
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
            } else {
                return response()
                    ->json(
                        [
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'Location Not Found',
                        ],
                        400,
                    )
                    ->header('Accept', 'application/json');
            }
        }
    }
}
