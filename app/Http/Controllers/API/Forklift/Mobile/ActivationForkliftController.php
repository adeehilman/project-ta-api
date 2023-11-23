<?php

namespace App\Http\Controllers\API\Forklift\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ActivationForkliftController extends Controller
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

        $badgeno = $request->badgeno;
        $id = $request->id_forklift;

        //  cek apakah
        $user = $this->second->table('tbl_driver')
            ->where('badgeno', $badgeno)
            ->first();
        if (!$user) {
            return response()->json(
                [
                    'RESPONSE_CODE' => 400,
                    'MESSAGETYPE' => 'E',
                    'MESSAGE' => 'User not have authorized',
                ],
                400,
            );
        } else {
            $this->second->beginTransaction();
            try {
                $drivercheck = "SELECT * FROM tbl_driver WHERE badgeno = '$badgeno'";
                $isAccess = $this->second->select($drivercheck)[0];

                /**
                 * saat melakukan scan, mobile akan mengirim request badgeno session dan id_forklift
                 * Cek apakah driver masih aktif dan memiliki lisensi yang tidak expire
                 * **/
                if ($isAccess) {
                    if ($isAccess->isactive == 1) {
                        if ($isAccess->isexpired == 0) {
                            $forkliftData = $this->second->table('tbl_forklift')
                                ->where('id', $id)
                                ->first();

                            if ($forkliftData->id_status == 2 && $forkliftData->isactive == 1) {
                                /**
                                 * cek apakah driver tersebut belum memakai forklift lain?
                                 *
                                 * **/
                                $check = "SELECT * FROM tbl_forklifthistory WHERE id_status = '1' and startby = '$badgeno' AND endtime is NULL";
                                $cekhistory = $this->second->select($check);

                                if (count($cekhistory) > 0) {
                                    return response()->json(
                                        [
                                            'MESSAGETYPE' => 'E',
                                            'MESSAGE' => 'Anda sedang menggunakan forklift lain saat ini!',
                                        ],
                                        400,
                                    );
                                } else {
                                    $this->second->table('tbl_forklift')
                                        ->where('id', $id)
                                        ->update([
                                            'id_status' => 1,
                                            'updateby' => 'BOT',
                                            'updatedate' => now(),
                                        ]);
                                        $this->second->table('tbl_forklifthistory')->insert([
                                        'id_forklift' => $forkliftData->id,
                                        'id_status' => 1,
                                        'date' => date('Y-m-d'),
                                        'starttime' => now(),
                                        'startby' => $badgeno,
                                        'dept' => $isAccess->dept,
                                    ]);

                                    // update endby di tbl_forklift pada status tidak digunakan
                                    $this->second->table('tbl_forklifthistory')
                                        ->where('id_forklift', $forkliftData->id)
                                        ->where('id_status', 2)
                                        ->where('endtime', null)
                                        ->update([
                                            'endtime' => date('Y-m-d H:i:s', time() + 1),
                                            'endby' => $badgeno,
                                        ]);

                                    $this->second->commit();

                                    return response()->json([
                                        'RESPONSE' => 200,
                                        'MESSAGETYPE' => 'S',
                                        'MESSAGE' => 'Data retrieved Successfully',
                                    ]);
                                }
                            } elseif ($forkliftData->id_status == 1) {
                                return response()->json(
                                    [
                                        'MESSAGETYPE' => 'E',
                                        'MESSAGE' => 'Forklift sedang digunakan',
                                    ],
                                    400,
                                );
                            } else {
                                return response()->json(
                                    [
                                        'MESSAGETYPE' => 'E',
                                        'MESSAGE' => 'Forklift tidak aktif',
                                    ],
                                    400,
                                );
                            }
                        } else {
                            return response()->json(
                                [
                                    'MESSAGETYPE' => 'E',
                                    'MESSAGE' => 'SIO Driver telah kedaluwarsa.',
                                ],
                                400,
                            );
                        }
                    } else {
                        return response()->json(
                            [
                                'MESSAGETYPE' => 'E',
                                'MESSAGE' => 'Driver tidak aktif',
                            ],
                            400,
                        );
                    }
                } else {
                    return response()->json(
                        [
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'Driver not found',
                        ],
                        400,
                    );
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
        }

        // // cek id apakah valid
        // $user = $this->second->table('tbl_forklift')
        //     ->where('id', $id)
        //     ->first();
        // if (!$user) {
        //     return response()->json(
        //         [
        //             'RESPONSE_CODE' => 404,
        //             'MESSAGETYPE' => 'E',
        //             'MESSAGE' => 'Not Found',
        //         ],
        //         404,
        //     );
        // } else {
        //     $this->second->beginTransaction();
        //     try {
        //         $checkstatus = $this->second->table('tbl_forklift')
        //             ->where('id', $id)
        //             ->value('id_status');

        //         // cek status forklift
        //         if ($checkstatus != 1) {
        //             // jika status sama dengan 1
        //             $qrCodeCheck = $this->second->table('tbl_forklift')
        //                 ->where('id', $id)
        //                 ->value('qrcode');

        //             return response()->json([
        //                 'MESSAGETYPE' => 'S',
        //                 'MESSAGE' => 'Data retrieved Successfully',
        //                 'DATA' => [
        //                     'QRCODE' => $qrCodeCheck,
        //                 ],
        //             ]);
        //         } else {
        //             $uniqueId = $this->second->table('tbl_forklift')
        //                 ->where('id', $id)
        //                 ->value('uniqueid');

        //             $timestamp = now()->timestamp;

        //             $qrcode = md5($uniqueId . $timestamp);
        //             $this->second->table('tbl_forklift')
        //                 ->where('id', $id)
        //                 ->update([
        //                     'qrcode' => $qrcode,
        //                 ]);

        //             $this->second->commit();
        //             // jika status sama dengan 1
        //             $qrCodeCheck = $this->second->table('tbl_forklift')
        //                 ->where('id', $id)
        //                 ->value('qrcode');

        //             return response()->json([
        //                 'MESSAGETYPE' => 'S',
        //                 'MESSAGE' => 'Data retrieved Successfully',
        //                 'DATA' => [
        //                     'QRCODE' => $qrCodeCheck,
        //                 ],
        //             ]);
        //         }
        //     } catch (\Throwable $th) {
        //         dd($th->getMessage());
        //         return response()
        //             ->json(
        //                 [
        //                     'MESSAGETYPE' => 'E',
        //                     'MESSAGE' => 'Something when wrong',
        //                 ],
        //                 400,
        //             )
        //             ->header('Accept', 'application/json');
        //     }
        // }
    }
}

