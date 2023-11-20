<?php

namespace App\Http\Controllers\API\Forklift\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UpdateFirmController extends Controller
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

        // dd($id);
        try {
            $checkversion = $this->second->table('tbl_espversion')
                ->orderBy('createdate', 'desc')
                ->value('version');

            $latestVersion = $this->second->table('tbl_espversion')
                ->orderBy('createdate', 'desc')
                ->value('filelocation');

            // lakukan query untuk get firm version
            $forkliftVersion = $this->second->table('tbl_forklift')
                ->where('id', $id)
                ->value('version');

            if ($checkversion == $forkliftVersion) {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => [
                        'STATUSUPDATE' => 'N',
                        'FILELOCATION' => '',
                        'remark' => 'Your firmware already up-to-date',
                    ],
                ]);
            } elseif (!$forkliftVersion) {
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
                $this->second->table('tbl_forklift')
                    ->where('id', $id)
                    ->update([
                        'version' => $checkversion,
                        'updateby' => 'BOT',
                        'updatedate' => now(),
                    ]);
                $this->second->commit();
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => [
                        'STATUSUPDATE' => 'Y',
                        'FILELOCATION' => $latestVersion,
                        'remark' => 'Please update your firmware',
                    ],
                ]);
            }
            // if (COUNT($data) > 0) {
            //
            // }
        } catch (\Throwable $th) {
            // dd($th->getMessage());
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

    public function updateFirmware(Request $request)
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
        $espversion = $request->version;

        try {
            $checkversion = $this->second->table('tbl_espversion')
                ->orderBy('createdate', 'desc')
                ->value('version'); 

            $latestVersion = $this->second->table('tbl_espversion')
                ->orderBy('createdate', 'desc')
                ->value('filelocation');

          

            if ($checkversion == $espversion) {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => [
                        'STATUSUPDATE' => 'N',
                        'FILELOCATION' => '',
                        'remark' => 'Your firmware already up-to-date',
                    ],
                ]);
            } elseif (!$espversion) {
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
                $this->second->table('tbl_forklift')
                    ->where('id', $id)
                    ->update([
                        'version' => $checkversion,
                        'updateby' => 'BOT',
                        'updatedate' => now(),
                    ]);
                $this->second->commit();
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => [
                        'STATUSUPDATE' => 'Y',
                        'FILELOCATION' => $latestVersion,
                        'remark' => 'Please update your firmware',
                    ],
                ]);
            }
            // if (COUNT($data) > 0) {
            //
            // }
        } catch (\Throwable $th) {
            // dd($th->getMessage());
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
