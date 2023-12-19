<?php

namespace App\Http\Controllers\API\PlatMaintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
}
