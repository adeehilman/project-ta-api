<?php

namespace App\Http\Controllers\API\Forklift\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use App\Models\Forklift;

class TokenGenerateController extends Controller
{
    public function index(Request $request)
    {
        $macaddress = $request->input('macaddress');

        $forklift = Forklift::where('macaddress', $macaddress)->first();

        if (!$forklift) {
            return response()->json(
                [
                    'RESPONSE' => 404,
                    'MESSAGETYPE' => 'E',
                    'MESSAGE' => 'Forklift not found',
                ],
                404,
            );
        }

        $token = JWTAuth::fromUser($forklift);

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'Data retrieved Successfully',
            'DATA' => [
                'id' => $forklift->id,
            ],
            'TOKEN' => $token,
        ]);
    }
}
