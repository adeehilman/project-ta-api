<?php

namespace App\Http\Controllers\API\Forklift\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class HistoryForkliftController extends Controller
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
        $month = $request->month;
        if ($month) {
            $m = $month;
        } else {
            $m = date('m');
        }

        try {
            /**
             * Query Total pemakaian dalam bentuk jam dalam sebulan
             * **/
            $realtimemonth = date('m');

            $totalhourinmonth = "SELECT SUM(totalhour) AS totaljam FROM (
            SELECT a. id, b.name, b.brand , a.date, a.starttime::time, a.endtime::TIME,
            extract(epoch FROM (a.endtime::TIMESTAMP - a.starttime::timestamp))/3600 AS totalhour,
            CASE WHEN a.endtime IS not null THEN 'Selesai' ELSE 'Digunakan' END AS status  FROM tbl_forklifthistory a,
            tbl_forklift b  WHERE a.id_forklift = b.id AND  a.startby = '$badgeno' AND a.id_status = 1 AND  EXTRACT(MONTH FROM a.STARTtime) = '$realtimemonth'
            ) AS a";
            $querytotalhour = $this->second->select($totalhourinmonth)[0];
            $hasil_bulat_desimal = round($querytotalhour->totaljam, 1);

    // dd($hasil_bulat_desimal);
            /**
             * Query list Summary Pemakaian sesuai badge
             * **/
            $historyforklift = "SELECT
            a. id, b.name as name_vehicle, b.brand, b.assetno ,d.badgeno, d.name AS driver, a.date, a.starttime::time, a.endtime::time,
            extract(epoch FROM (a.endtime::TIMESTAMP - a.starttime::timestamp))/3600 AS totalhour,
            CASE WHEN a.endtime IS not null THEN 'Selesai' ELSE 'Digunakan' END AS status
             FROM tbl_forklifthistory a INNER JOIN tbl_driver d ON startby = d.badgeno, tbl_forklift b
             WHERE a.id_forklift = b.id AND  a.startby = '$badgeno' AND a.id_status = 1 AND  EXTRACT(MONTH FROM a.starttime) =  '$realtimemonth' ORDER BY a.starttime DESC";
            $query = $this->second->select($historyforklift);

            $response = [
                'totaljam' => $hasil_bulat_desimal,
                'list_history' => $query,
            ];

            if ($response) {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => $response,
                ]);
            } else {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'Data retrieved Successfully',
                    'DATA' => $response,
                ]);
            }
        } catch (\Throwable $th) {
            dd($th);
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

    public function getLastDriver(Request $request)
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

        try {
            $historybydid = "SELECT
            f.id,
            f.licenseno,
            f.assetno,
            f.brand,
            f.image1,
            f.image2,
            f.image3,
            (SELECT vl.name FROM tbl_vlookup vl WHERE vl.category = 'FORKLIFT' AND vl.id = f.id_status) AS status_real ,
            f.NAME, d.badgeno, d.name, d.dept, his.starttime, his.endtime
            FROM tbl_forklifthistory his
            INNER JOIN tbl_forklift f ON f.id = his.id_forklift
            LEFT JOIN tbl_driver d ON d.badgeno = his.startby   
            WHERE his.id_status = '1' AND his.id_forklift = '$id' ORDER BY his.id DESC LIMIT 1";

            $query = $this->second->select($historybydid);
            return response()->json([
                'RESPONSE' => 200,
                'MESSAGETYPE' => 'S',
                'MESSAGE' => 'Data retrieved Successfully',
                'DATA' => $query,
            ]);
        } catch (\Throwable $th) {
            dd($th);
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

