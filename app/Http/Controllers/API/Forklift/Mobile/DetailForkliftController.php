<?php

namespace App\Http\Controllers\API\Forklift\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class DetailForkliftController extends Controller
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

        $id = $request->id_forklift;

        $forkliftData = $this->second->table('tbl_forklift')
            ->where('id', $id)
            ->first();
        $forklift = "SELECT f.id,f.name, f.brand, f.assetno, f.licenseno,	f.battery, f.id_status, f.image1, f.image2, f.image3 , f.qrcode, l.name AS location_name, l.uniqueid AS location_unique_id, (SELECT vl.name FROM tbl_vlookup vl WHERE vl.category = 'FORKLIFT' AND vl.id = f.id_status)as name_status FROM tbl_forklift f LEFT JOIN tbl_location l ON l.id = f.id_location WHERE f.id = '$id'";

        $query = $this->second->select($forklift);

        if ($query) {
            $historybydid = "SELECT
        f.id,
         d.name,
         d.badgeno, DIV.division, d.dept, his.starttime, his.endtime
        FROM tbl_forklifthistory his
        INNER JOIN tbl_forklift f ON f.id = his.id_forklift
        LEFT JOIN tbl_driver d ON d.badgeno = his.startby
        LEFT JOIN tbl_division DIV ON DIV.dept = d.dept
        WHERE his.id_status = '1' AND his.id_forklift = '$forkliftData->id' ORDER BY his.id DESC LIMIT 1";

            $lastdrive = $this->second->select($historybydid);
            $lastdrivekosong = [
                'id' => null,
                'name' => null,
                'badge_no' => null,
                'division' => null,
                'dept' => null,
                'starttime' => null,
                'endtime' => null,
            ];

            if (count($query) > 0) {
                // Mengubah URL gambar di   dalam $query
                foreach ($query as &$item) {
                    // $item->image1 = str_replace(' ', '%20', "http://192.168.88.60:7011/uploadForklift/{$item->image1}");
                    // $item->image2 = str_replace(' ', '%20', "http://192.168.88.60:7011/uploadForklift/{$item->image2}");
                    // $item->image3 = str_replace(' ', '%20', "http://192.168.88.60:7011/uploadForklift/{$item->image3}");
                    
                    $item->image1 = str_replace(' ', '%20', "https://webapi.satnusa.com/uploadForklift/{$item->image1}");
                    $item->image2 = str_replace(' ', '%20', "https://webapi.satnusa.com/uploadForklift/{$item->image2}");
                    $item->image3 = str_replace(' ', '%20', "https://webapi.satnusa.com/uploadForklift/{$item->image3}");
                    $item->last_drive = $lastdrive ? $lastdrive[0] : $lastdrivekosong;

                    //prod
                }
            }

            return response()->json([
                'RESPONSE' => 200,
                'MESSAGETYPE' => 'S',
                'MESSAGE' => 'Data retrieved Successfully',
                'DATA' => $query[0],
            ]);
        } else {
            return response()->json([
                'RESPONSE' => 200,
                'MESSAGETYPE' => 'S',
                'MESSAGE' => 'Data retrieved Successfully',
                'DATA' => $query[0],
            ]);
        }
    }
}

