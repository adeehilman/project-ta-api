<?php

namespace App\Http\Controllers\API\PlatMaintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class MaintenanceMobilController extends Controller
{
    public function __construct()
    {
        $this->third = DB::connection('third');
    }

    public function ActivityMaintenance(Request $request)
    {
        // dd($request->all());

        $ordertype = $request->ordertype;
        if (!$ordertype) {
            $ordertype = 'PM01';
        }
        $query = "SELECT * FROM tbl_activitytype WHERE ordertype = '$ordertype'";
        $data = $this->third->select($query);

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $data,
        ]);
    }

    public function onGoingMaintenance(Request $request)
    {
        $employee_no = $request->employee_no;
        $status = $request->status;

        // dd($status);
        try {
            $query_ongoing = "SELECT
                b.device_name,
                b.license_no,
                (SELECT description FROM  tbl_activitytype WHERE activityype=c.activitytype AND ordertype='PM01') AS activitytypedesc,
                c.activitytype,
                c.priority,
                c.lastupdate,
                c.statusdowntime_id
            FROM
                tbl_carlist a
            INNER JOIN
                tbl_device b ON a.equipment_number = b.equipment_number
            INNER JOIN
                tbl_downtime c ON b.id = c.device_id
            WHERE
                a.driver = '$employee_no'
                AND c.statusdowntime_id IN ($status)";

            $data = $this->third->select($query_ongoing);

            // dd($data);
            if (!$data) {
                return response()->json([
                    'RESPONSE' => 200,
                    'MESSAGETYPE' => 'S',
                    'MESSAGE' => 'SUCCESS',
                    'DATA' => $data,
                ]);
            }

            $dataarray = [];

            if ($data[0]->statusdowntime_id == 1 || ($data[0]->statusdowntime_id == 2 || ($data[0]->statusdowntime_id == 3 || $data[0]->statusdowntime_id == 4))) {
                $statusname = 'Sedang Berlangsung';
            } elseif ($data[0]->statusdowntime_id == 5 || $data[0]->statusdowntime_id == 6) {
                $statusname = 'Close';
            } else {
                $statusname = 'Cancel';
            }

            foreach ($data as $item) {
                $processedItem = [
                    'device_name' => $item->device_name,
                    'license_no' => $item->license_no,
                    'activitytype' => $item->activitytypedesc,
                    'priority' => $item->priority,
                    'lastupdate' => $item->lastupdate,
                    'statusdowntime_id' => $statusname,
                ];

                // Tambahkan hasil proses ke dalam array $data
                $dataarray[] = $processedItem;
            }

            return response()->json([
                'RESPONSE' => 200,
                'MESSAGETYPE' => 'S',
                'MESSAGE' => 'SUCCESS',
                'DATA' => $dataarray,
            ]);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return response()
                ->json(
                    [
                        'RESPONSE_CODE' => 400,
                        'MESSAGETYPE' => 'E',
                        'MESSAGE' => $th->getMessage(),
                    ],
                    400,
                )
                ->header('Accept', 'application/json');
        }
    }

    public function getDetailMobil(Request $request)
    {
        // dd($request->all());

        $employee_no = $request->employee_no;

        $query = "SELECT a.license_no,
        a.fleet_name,
        b.year_construction,
        a.equipment_number,
        b.plant,
        b.planner_group
        FROM tbl_carlist a
        INNER JOIN tbl_device b ON a.equipment_number = b.equipment_number
        WHERE driver = '$employee_no'";
        $data = $this->third->select($query);

        // dd($data);
        if(!$data){
            return response()->json([
                "RESPONSE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'Data vehicle tidak ditemukan',
            ], 400)->header(
                "Accept",
                "application/json"
            );
        }

        $data = $data[0];

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $data,
        ]);
    }

    public function addPengajuanMaintenance(Request $request)
    {
        // dd($request->all());

        try {
            $token = $request->header('Authorization');
            $validateToken = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                "RESPONSE_CODE" => 401,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'UNAUTHORIZED',
            ], 401)->header(
                "Accept",
                "application/json"
            );
        }


        try {
            $this->third->beginTransaction();

            $validator = Validator::make(request()->all(), [
                'employee_no' => 'required',
                'employee_name' => 'required',
                'activitytype' => 'required',
                'permasalahan' => 'required',
                'priority' => 'required',
            ]);

            if ($validator->fails()) {
                return response()
                    ->json(
                        [
                            'RESPONSE_CODE' => 400,
                            'MESSAGETYPE' => 'E',
                            'MESSAGE' => $validator->messages(),
                        ],
                        400,
                    )
                    ->header('Accept', 'application/json');
            }

                    
            $employee_no = $request->employee_no;
            $employee_name = $request->employee_name;
            $activitytype = $request->activitytype;
            $permasalahan = $request->permasalahan;
            $priority = $request->priority;
            $duedate = $request->duedate ?? NULL;

            $getDeviceId = "SELECT b.id as device_id, b.plant, b.planner_group, b.department_id FROM tbl_carlist a INNER JOIN tbl_device b ON a.equipment_number = b.equipment_number  WHERE driver = '$employee_no'";
            $list = $this->third->select($getDeviceId);
            $getDeviceId = $list[0];
            // dd($getDeviceId);
            // createticket
            $currDay = date('Y-m-d') . ' 07:00:00';
            $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($currDay))) . ' 07:00:00';

            $dataDowntime = $this->third->table('tbl_downtime')
            ->whereBetween('createdate', [$currDay, $nextDay])
            ->selectRaw('RIGHT(ticket_number, 6) as ticket_number')
            ->orderBy('ticket_number', 'DESC')
            ->first();
            
            $ticketNo = '';
            if($dataDowntime){
                $number = (int)$dataDowntime->ticket_number+1;
                $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);
                $ticketNo = $paddedNumber;
            }else{
                $number = 1;
                $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);
                $ticketNo = $paddedNumber;
            }

            $year = date('Y');
            $month = (string)date('m');
            $date = (string)date('d');
            if(strlen($month) < 2){
                $month = '0' . $month;
            }
            if(strlen($date) < 2){
                $date = '0' . $date;
            }

            $newTicket = 'DT' . $year . $month . $date . $ticketNo;



            
            $dataDowntime = [
                'ticket_number'         => $newTicket,
                'device_id'             => $getDeviceId->device_id,
                'createby'              => $employee_no,
                'createdate'            => date("Y-m-d H:i:s"),
                'statusdowntime_id'     => 1,
                'updateby'              => $employee_no,
                'lastupdate'            => date("Y-m-d H:i:s"),
                'createby_name'         => $employee_name,
                'updateby_name'         => $employee_name,
                'ordertype'             => 'PM01',
                'activitytype'          => $activitytype,
                'priority'              => $priority,
                'priority_start_date'   => date("Y-m-d"),
                'priority_due_date'     => $duedate,
                'plant'                 => $getDeviceId->plant,
                'planner_group'         => $getDeviceId->planner_group,
                'department_id'         => $getDeviceId->department_id,
                'problem'               => $permasalahan,
            ];

            $newDowntime = $this->third->table('tbl_downtime')->insertGetId($dataDowntime);

            $this->third->table('tbl_downtimehistory')
                    ->insert([
                        'downtime_id'           => $newDowntime,
                        'status_downtime'       =>  1,
                        'createby'              => $employee_no,
                        'createdate'            => date("Y-m-d H:i:s"),
                        'createby_name'         => $employee_name,
                        'remark'                => $permasalahan
                    ]);
            
                    $this->third->commit();
                
                return response()->json([
                    "RESPONSE"      => 200,
                    "MESSAGETYPE"   => "S",
                    "MESSAGE"       => "SUCCESS",
                    'DOWNTIME_ID'   => $newDowntime,
                ]);
        } catch (\Throwable $th) {
            // $this->third->rollback();
            return response()->json([
                'status' => 401,
                'message' => 'Gagal untuk menambah data ' . $th->getMessage(),
            ]);
        }
    }

    public function getDetailMaintenance(Request $request)
    {
        // dd($request->all());

        $downtime_id = $request->id;

        $querydetail = "SELECT 
        c.id,
        a.license_no,
        a.fleet_name,
        b.year_construction,
        a.equipment_number,
        CONCAT(a.driver_name, ' (', a.driver, ')') AS driver_name,
        a.driver,
        c.pr_no,
        c.ticket_number,
        c.maintenance_order,
        c.activitytype,
        c.problem,
        (SELECT bp_name FROM tbl_bp d WHERE d.id = c.bp_id)AS vendor,
        CONCAT(c.updateby_name, ' (', c.updateby, ')') AS updateby_name,
        c.statusdowntime_id,
        b.plant,
        b.planner_group
        FROM tbl_carlist a
        INNER JOIN tbl_device b ON a.equipment_number = b.equipment_number
        INNER JOIN tbl_downtime c ON c.device_id = b.id
        WHERE c.id = '$downtime_id'";
        $result = $this->third->select($querydetail);

        $queryhistory = "SELECT
        id,remark,
        CONCAT(createby_name, ' (',createby, ')') AS createby,
        createdate AT TIME ZONE 'Asia/Jakarta' AS createdate,
            CASE
                WHEN status_downtime IN (1, 2, 3, 4) THEN 'Sedang Berlangsung'
                WHEN status_downtime IN (5, 6) THEN 'Close'
                WHEN status_downtime IN (9) THEN 'Cancel'
                ELSE 'Cancel'
            END AS status
        FROM
            tbl_downtimehistory
        WHERE
            downtime_id = '$downtime_id'";
        $result_history = $this->third->select($queryhistory);

        // dd($result_history);
        $dataarray = [
            'detail' => $result,
            'history_status' => $result_history
        ];
        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'SUCCESS',
            'DATA' => $dataarray,
        ]);
    }
}
