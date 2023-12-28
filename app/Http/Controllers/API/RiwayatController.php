<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RiwayatController extends Controller
{
    public function __construct()
    {
        $this->third = DB::connection('third');
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

        
        $badge_id = $request->badge_id;
        $is_finish = $request->is_finish;

        if ($badge_id == "") {
            return response()->json([
                "message" => "Badge ID tidak boleh kosong"
            ], 400);
        }

        $FilterValue = $request->filterMenu;
        $StartDate = $request->startDate;

        // dd($StartDate);
        


        $lms_finish = '3,5,8,10,13,15,16,17,18';
        $mms_finish = '3,5,8,10,13,14,12,15';
        $meeting_finish = '5,6';
        $downtime_finish = '5,6,9';
        $kritiksaran_finish = "3,4";
        $downtime_array = explode(',', $downtime_finish);

        

        
        try {
            if($is_finish == 1){
                $query1 = DB::table(DB::raw("
                    (SELECT a.id, 'Pengajuan Handphone' AS category, '3' AS category_id, a.tipe_hp AS title, c.name_vlookup AS subtitle, a.waktu_pengajuan AS date,
                    a.updatedate as lastupdate, b.short_title AS stat_title, b.bg_color, b.txt_color
                    FROM tbl_mms a , tbl_statusmms b, tbl_vlookup c 
                    WHERE a.status_pendaftaran_mms = b.id AND a.merek_hp = c.id_vlookup AND a.badge_id = '$badge_id' AND a.status_pendaftaran_mms IN ($mms_finish)
                    UNION
                    SELECT a.id, 'Pengajuan Laptop' AS category,'4' AS category_id, a.tipe_laptop AS title, c.name_vlookup AS subtitle, a.tanggal_pengajuan AS date, 
                    a.updatedate as lastupdate, b.short_title AS stat_title, b.bg_color, b.txt_color
                    FROM tbl_lms a , tbl_statuslms b, tbl_vlookup c 
                    WHERE a.brand = c.id_vlookup AND a.status_pendaftaran_lms = b.id AND a.badge_id = '$badge_id' AND a.status_pendaftaran_lms IN ($lms_finish)
                    UNION
                    SELECT a.id, 'Meeting Room' AS category,'1' AS category_id, a.title_meeting AS title, CONCAT(c.room_name, ', ', DATE_FORMAT(a.meeting_date, '%d %b %Y'), ', ', TIME_FORMAT(a.meeting_start, '%H:%i'), '-', TIME_FORMAT(a.meeting_end, '%H:%i')) AS subtitle, 
                    a.booking_date AS date, 
                    a.update_date as lastupdate,  d.short_title AS stat_title, d.bg_color, d.txt_color
                    FROM tbl_meeting a , tbl_participant b, tbl_roommeeting c, tbl_statusmeeting d 
                    WHERE a.id = b.meeting_id AND a.roommeeting_id = c.id AND a.statusmeeting_id = d.id AND b.participant = '$badge_id' AND a.statusmeeting_id IN ($meeting_finish)
                    UNION
                    SELECT  a.id,'Kritik dan Saran' AS category,                                                                                                               
                    '7' AS category_id, b.name_vlookup AS title, a.description AS subtitle, a.createdate AS date, a.createdate AS lastupdate,                          
                    c.short_title AS stat_title, c.bg_color, c.txt_color
                    FROM tbl_kritiksaran a, tbl_vlookup b, tbl_statuskritiksaran c WHERE a.kategori = b.id_vlookup AND a.badge_id = '200400' AND a.status_kritiksaran IN  ($kritiksaran_finish)
                    ) AS A
                "));

                // Query kedua
                $query2 = DB::connection('third')->table('tbl_carlist as a')
                ->select([
                    'c.id',
                    DB::raw("'Maintenance Mobil' AS category"),
                    DB::raw("'10' AS category_id"),
                    'b.license_no AS title',
                    DB::raw("CONCAT((SELECT description FROM tbl_activitytype WHERE activitytype = c.activitytype AND ordertype = 'PM01' LIMIT 1), ', ', c.priority) AS subtitle"),
                    DB::raw("c.lastupdate AS date"),
                    DB::raw("c.lastupdate AS lastupdate"),
                    DB::raw("d.short_title AS stat_title"),
                        'd.bg_color',
                        'd.txt_color',
                    ])
                ->join('tbl_device as b', 'a.equipment_number', '=', 'b.equipment_number')
                ->join('tbl_downtime as c', 'b.id', '=', 'c.device_id')
                ->join('tbl_statusdowntime as d', 'c.statusdowntime_id', '=', 'd.id')
                ->where('a.driver', '=', $badge_id)
                ->whereIn('c.statusdowntime_id', $downtime_array);
            }


            $lms_ongoing = '1,2,4,6,7,9';
            $mms_ongoing = '1,2,4,6,7,9,11,12,14';
            $meeting_ongoing = '1,2,3,4';
            $downtime_ongoing = '1,2,3,4,7';
            $kritiksaran_ongoing = '1,2';
            $downtime_array_ongoing = explode(',', $downtime_ongoing);

            if($is_finish != 1){
                    $query1 = DB::table(DB::raw("
                    (SELECT a.id, 'Pengajuan Handphone' AS category, '3' AS category_id, a.tipe_hp AS title, c.name_vlookup AS subtitle, a.waktu_pengajuan AS date, 
                    a.updatedate as lastupdate, b.short_title AS stat_title, b.bg_color, b.txt_color
                    FROM tbl_mms a , tbl_statusmms b, tbl_vlookup c 
                    WHERE a.status_pendaftaran_mms = b.id AND a.merek_hp = c.id_vlookup AND a.badge_id = '$badge_id' AND a.status_pendaftaran_mms IN ($mms_ongoing)
                    UNION
                    SELECT a.id, 'Pengajuan Laptop' AS category,'4' AS category_id, a.tipe_laptop AS title, c.name_vlookup AS subtitle, a.tanggal_pengajuan AS date, 
                    a.updatedate as lastupdate, b.short_title AS stat_title, b.bg_color, b.txt_color
                    FROM tbl_lms a , tbl_statuslms b, tbl_vlookup c 
                    WHERE a.brand = c.id_vlookup AND a.status_pendaftaran_lms = b.id AND a.badge_id = '$badge_id' AND a.status_pendaftaran_lms IN ($lms_ongoing)
                    UNION
                    SELECT a.id, 'Meeting Room' AS category,'1' AS category_id, a.title_meeting AS title, CONCAT(c.room_name, ', ', DATE_FORMAT(a.meeting_date, '%d %b %Y'), ', ', TIME_FORMAT(a.meeting_start, '%H:%i'), '-', TIME_FORMAT(a.meeting_end, '%H:%i')) AS subtitle, 
                    a.booking_date AS date,
                    a.update_date as lastupdate, d.short_title AS stat_title, d.bg_color, d.txt_color
                    FROM tbl_meeting a , tbl_participant b, tbl_roommeeting c, tbl_statusmeeting d 
                    WHERE a.id = b.meeting_id AND a.roommeeting_id = c.id AND a.statusmeeting_id = d.id AND b.participant = '$badge_id' AND a.statusmeeting_id IN ($meeting_ongoing)
                    UNION
                    SELECT  a.id,'Kritik dan Saran' AS category,
                    '7' AS category_id, b.name_vlookup AS title, a.description AS subtitle, a.createdate AS date, a.createdate AS lastupdate,
                    c.short_title AS stat_title, c.bg_color, c.txt_color
                    FROM tbl_kritiksaran a, tbl_vlookup b,tbl_statuskritiksaran c WHERE a.kategori = b.id_vlookup AND a.badge_id = '200400' AND a.status_kritiksaran IN  ($kritiksaran_ongoing)
                    ) AS A
                "));

                // Query kedua
                $query2 = DB::connection('third')->table('tbl_carlist as a')
                ->select([
                    'c.id',
                    DB::raw("'Maintenance Mobil' AS category"),
                    DB::raw("'10' AS category_id"),
                    'b.license_no AS title',
                    DB::raw("CONCAT((SELECT description FROM tbl_activitytype WHERE activitytype = c.activitytype AND ordertype = 'PM01' LIMIT 1), ', ', c.priority) AS subtitle"),
                    DB::raw("c.lastupdate AS date"),
                    DB::raw("c.lastupdate AS lastupdate"),
                    DB::raw("d.short_title AS stat_title"),
                        'd.bg_color',
                        'd.txt_color',
                    ])
                ->join('tbl_device as b', 'a.equipment_number', '=', 'b.equipment_number')
                ->join('tbl_downtime as c', 'b.id', '=', 'c.device_id')
                ->join('tbl_statusdowntime as d', 'c.statusdowntime_id', '=', 'd.id')
                ->where('a.driver', '=', $badge_id)
                ->whereIn('c.statusdowntime_id', $downtime_array_ongoing);

            }
        } catch (\Throwable $th) {
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
       

        // Gabungkan hasil kedua query
        $result = $query1->get()->merge($query2->get());

        // Filter Condition
        if($FilterValue){
            $FilterMenu = explode(',', $FilterValue);
            $result = $result->whereIn('category_id', $FilterMenu);
        }

        // Date Time Condition
        if($StartDate){
            $StartDatetime = $StartDate;
            $endDatetime = date("Y-m-d H:i:s");
            
            // dd($endDatetime);
            // Tambahkan klausa WHERE BETWEEN pada hasil query
            $result = $result->filter(function ($item) use ($StartDatetime, $endDatetime) {
                // Cek apakah lastupdate berada dalam rentang waktu atau null
                $lastupdateInRange = $item->lastupdate && ($item->lastupdate >= $StartDatetime && $item->lastupdate <= $endDatetime);

                // Cek apakah date berada dalam rentang waktu atau null jika lastupdate kosong
                $dateInRange = (!$item->lastupdate && $item->date && ($item->date >= $StartDatetime && $item->date <= $endDatetime));

                return $lastupdateInRange || $dateInRange;
            });
        }
    
        // Sort hasil berdasarkan kolom date
        $data = $result->sort(function ($a, $b) {
        

        // Bandingkan lastupdate
        $lastupdateComparison = $b->lastupdate <=> $a->lastupdate;

        // Jika lastupdate sama atau NULL, bandingkan date
        return $lastupdateComparison === 0 ? ($b->date <=> $a->date) : $lastupdateComparison;
        })->values();


        // dd($data);
        // Tentukan jumlah item per halaman
        $perPage = 10;

        // Gunakan metode paginate
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $paginatedData = $data->slice(($page - 1) * $perPage, $perPage)->values()->all();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($paginatedData, count($data), $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
        ]);       

        // Dapatkan informasi paginasi
        $total = $paginator->total();
        $current_page = $paginator->currentPage();
        $last_page = $paginator->lastPage();
        $next_page_url = $paginator->nextPageUrl();
        $prev_page_url = $paginator->previousPageUrl();

        // dd($paginator->items());
        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            "CURRENT_PAGE"  => $current_page,
            "LAST_PAGE"     => $last_page,
            "NEXT_PAGE_URL" => $next_page_url,
            "PREV_PAGE_URL" => $prev_page_url,
            "DATA"          => $paginator->items()
        ]);
    }

    public function filterRiwayat(Request $request)
    {
        $badge_id = $request->badge_id;
        // dd($request->all());
        
        $queryMenu = "SELECT
            b.id,
            b.name,
            CASE WHEN a.accessmenu IS NOT NULL THEN 'Private' ELSE 'Publik' END AS description
        FROM tbl_mobilemenu b
        LEFT JOIN tbl_mobilerole a ON a.accessmenu = b.id AND a.badge_id = '$badge_id'
        WHERE b.description = 'Publik' OR a.accessmenu IS NOT NULL
        ";
        $data = DB::select($queryMenu);

        // dd($data);
        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            "DATA"          => $data
        ]);
    }
}
