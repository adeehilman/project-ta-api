<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MenuAccessController extends Controller
{
    public function getMenuAccess(Request $request)
    {
        try {
            // dd($badgeno);
            $badgeno = $request->badgeno;
            if ($badgeno) {
                try {
                    $query = "SELECT a.accessmenu, b.name FROM tbl_mobilerole a, tbl_privatemenumobile b WHERE  a.accessmenu = b.id AND  a.badge_id = '$badgeno'";
                    $data = DB::SELECT($query);
                    if (!$data) {
                        return response()->json(
                            [
                                'RESPONSE_CODE' => 400,
                                'MESSAGETYPE' => 'E',
                                'MESSAGE' => 'User Not Have Authorized',
                            ],
                            400,
                        );
                    }

                    return response()->json([
                        'RESPONSE' => 200,
                        'MESSAGETYPE' => 'S',
                        'MESSAGE' => 'Data retrieved Successfully',
                        'DATA' => $data,
                    ]);
                } catch (\Throwable $th) {
                    return response()->json(
                        [
                            'RESPONSE_CODE' => 400,
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => 'User Not Have Authorized',
                        ],
                        400,
                    );
                }
            } else {
                return response()->json(
                    [
                        'RESPONSE_CODE' => 400,
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => 'User Not Found',
                    ],
                    400,
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'RESPONSE_CODE' => 500,
                    'MESSAGETYPE' => 'E',
                    'MESSAGE' => 'Server Error',
                ],
                500,
            );
        }
    }
}
