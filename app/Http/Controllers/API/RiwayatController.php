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

        $lms_finish = '3,5,8,10,13,15,16,17,18';
        $mms_finish = '3,5,8,10,13,14,12,15';
        $meeting_finish = '5,6';
        $downtime_finish = "5,6,9";
        $downtime_array = explode(',', $downtime_finish);

        

        
        try {
            if($is_finish == 1){
                $query1 = DB::table(DB::raw("
                    (SELECT a.id, 'Pengajuan Handphone' AS category, '3' AS category_id, a.tipe_hp AS title, c.name_vlookup AS subtitle, a.waktu_pengajuan AS date,
                    a.updatedate as lastupdate,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN 'Ditinjau HRD'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN 'Ditinjau QHSE'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN '0xFFFFF7E6'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN '0xFFFFF3E9'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN '0xFFE8A100'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN '0xFFE6781C'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN '0xFF1DB74E' 
                        ELSE '0xFFCD202E'
                    END AS txt_color 
                    FROM tbl_mms a , tbl_statusmms b, tbl_vlookup c 
                    WHERE a.status_pendaftaran_mms = b.id AND a.merek_hp = c.id_vlookup AND a.badge_id = '$badge_id' AND a.status_pendaftaran_mms IN ($mms_finish)
                    UNION
                    SELECT a.id, 'Pengajuan Laptop' AS category,'4' AS category_id, a.tipe_laptop AS title, c.name_vlookup AS subtitle, a.tanggal_pengajuan AS date, 
                    a.updatedate as lastupdate,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN 'Ditinjau HRD'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN 'Ditinjau QHSE'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN 'Disetujui Manager QHSE'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN '0xFFFFF7E6'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN '0xFFFFF3E9'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN '0xFFE6F2FA'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN '0xFFE8A100'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN '0xFFE6781C'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN '0xFF057DCD'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN '0xFF1DB74E'
                        ELSE '0xFFCD202E'
                    END AS txt_color
                    FROM tbl_lms a , tbl_statuslms b, tbl_vlookup c 
                    WHERE a.brand = c.id_vlookup AND a.status_pendaftaran_lms = b.id AND a.badge_id = '$badge_id' AND a.status_pendaftaran_lms IN ($lms_finish)
                    UNION
                    SELECT a.id, 'Meeting Room' AS category,'1' AS category_id, a.title_meeting AS title, CONCAT(c.room_name, ', ', DATE_FORMAT(a.meeting_date, '%d %b %Y'), ', ', TIME_FORMAT(a.meeting_start, '%H:%i'), '-', TIME_FORMAT(a.meeting_end, '%H:%i')) AS subtitle, 
                    a.booking_date AS date, 
                    a.update_date as lastupdate,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN 'Ruangan dibooking'
                        WHEN a.statusmeeting_id IN (2) THEN 'Menunggu meeting dimulai'
                        WHEN a.statusmeeting_id IN (4) THEN 'Sedang Berlangsung'
                        WHEN a.statusmeeting_id IN (3) THEN 'Reschedule'
                        WHEN a.statusmeeting_id IN (5) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN '0xFFF0F1F3'
                        WHEN a.statusmeeting_id IN (2) THEN '0xFFFFF7E6'
                        WHEN a.statusmeeting_id IN (4) THEN '0xFFE6F2FA'
                        WHEN a.statusmeeting_id IN (3) THEN '0xFFFFEEF6'
                        WHEN a.statusmeeting_id IN (5) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN '0xFF41443D'
                        WHEN a.statusmeeting_id IN (2) THEN '0xFFE8A100'
                        WHEN a.statusmeeting_id IN (4) THEN '0xFF057DCD'
                        WHEN a.statusmeeting_id IN (3) THEN '0xFFE84B93'
                        WHEN a.statusmeeting_id IN (5) THEN '0xFF1DB74E'
                        ELSE '0xFFCD202E'
                    END AS txt_color
                    FROM tbl_meeting a , tbl_participant b, tbl_roommeeting c, tbl_statusmeeting d 
                    WHERE a.id = b.meeting_id AND a.roommeeting_id = c.id AND a.statusmeeting_id = d.id AND b.participant = '$badge_id' AND a.statusmeeting_id IN ($meeting_finish)
                    UNION
                    SELECT  a.id,'Kritik dan Saran' AS category,                                                                                                               
        '1' AS category_id, b.name_vlookup AS title, a.description AS subtitle, a.createdate AS date, a.createdate AS lastupdate,                          
CASE                                                                                                                                                       
            WHEN a.status_kritiksaran IN (1,2) THEN 'Menunggu Tanggapan HRD'                                                                               
            WHEN a.status_kritiksaran IN (3) THEN 'Ditanggapi HRD'                                                                                         
            WHEN a.status_kritiksaran IN (4) THEN 'Selesai'                                                                                                
            ELSE 'Dibatalkan'                                                                                                                              
        END AS stat_title,                                                                                                                                 
        CASE                                                                                                                                               
            WHEN a.status_kritiksaran IN (1,2) THEN '0xFFFFF7E6'                                                                                           
            WHEN a.status_kritiksaran IN (3) THEN '0xFFE6F2FA'                                                                                             
            WHEN a.status_kritiksaran IN (4) THEN '0xFFE8F8ED'                                                                                             
            ELSE '0xFFF9E9EA'                                                                                                                              
        END AS bg_color,                                                                                                                                   
        CASE                                                                                                                                               
            WHEN a.status_kritiksaran IN (1,2) THEN '0xFFE8A100'                                                                                           
            WHEN a.status_kritiksaran IN (3) THEN '0xFFE84B93'                                                                                             
            WHEN a.status_kritiksaran IN (4) THEN '0xFF1DB74E'                                                                                             
            ELSE '0xFFCD202E'                                                                                                                              
        END AS txt_color FROM tbl_kritiksaran a, tbl_vlookup b WHERE a.kategori = b.id_vlookup AND a.badge_id = '200400' AND a.status_kritiksaran IN  (3,4)
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
                    DB::raw("
                    CASE
                            WHEN c.statusdowntime_id IN (1) THEN 'Open Ticket'
                            WHEN c.statusdowntime_id IN (2, 4) THEN 'Maintenance Sedang Berlangsung'
                            WHEN c.statusdowntime_id IN (3,5,6) THEN 'Selesai'
                            ELSE 'Dibatalkan'
                        END AS stat_title ,
                        CASE
                            WHEN c.statusdowntime_id IN (1) THEN '0xFFF0F1F3'
                            WHEN c.statusdowntime_id IN (2, 4) THEN '0xFFE6F2FA'
                            WHEN c.statusdowntime_id IN (5) THEN '0xFFE8F8ED'
                            ELSE '0xFFF9E9EA'
                    END AS bg_color ,
                    CASE
                        WHEN c.statusdowntime_id IN (1) THEN '0xFF41443D'
                            WHEN c.statusdowntime_id IN (2, 4) THEN '0xFF057DCD'
                            WHEN c.statusdowntime_id IN (3,5,6) THEN '0xFF1DB74E'
                            ELSE '0xFFCD202E'
                    END AS txt_color"),
                ])
                ->join('tbl_device as b', 'a.equipment_number', '=', 'b.equipment_number')
                ->join('tbl_downtime as c', 'b.id', '=', 'c.device_id')
                ->where('a.driver', '=', $badge_id)
                ->whereIn('c.statusdowntime_id', $downtime_array);
            }


            $lms_ongoing = '1,2,4,6,7,9';
            $mms_ongoing = '1,2,4,6,7,9,11,12,14';
            $meeting_ongoing = '1,2,3,4';
            $downtime_ongoing = '1,2,3,4,7';
            $downtime_array_ongoing = explode(',', $downtime_ongoing);

            if($is_finish != 1){
                    $query1 = DB::table(DB::raw("
                    (SELECT a.id, 'Pengajuan Handphone' AS category, '3' AS category_id, a.tipe_hp AS title, c.name_vlookup AS subtitle, a.waktu_pengajuan AS date, 
                    a.updatedate as lastupdate,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN 'Ditinjau HRD'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN 'Ditinjau QHSE'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN '0xFFFFF7E6'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN '0xFFFFF3E9'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.status_pendaftaran_mms IN (1,2,4,6) THEN '0xFFE8A100'
                        WHEN a.status_pendaftaran_mms IN (7,9) THEN '0xFFE6781C'
                        WHEN a.status_pendaftaran_mms IN (12,15) THEN '0xFF1DB74E' 
                        ELSE '0xFFCD202E'
                    END AS txt_color 
                    FROM tbl_mms a , tbl_statusmms b, tbl_vlookup c 
                    WHERE a.status_pendaftaran_mms = b.id AND a.merek_hp = c.id_vlookup AND a.badge_id = '$badge_id' AND a.status_pendaftaran_mms IN ($mms_ongoing)
                    UNION
                    SELECT a.id, 'Pengajuan Laptop' AS category,'4' AS category_id, a.tipe_laptop AS title, c.name_vlookup AS subtitle, a.tanggal_pengajuan AS date, 
                    a.updatedate as lastupdate,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN 'Ditinjau HRD'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN 'Ditinjau QHSE'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN 'Disetujui Manager QHSE'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN '0xFFFFF7E6'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN '0xFFFFF3E9'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN '0xFFE6F2FA'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.status_pendaftaran_lms IN (1,2,4,6) THEN '0xFFE8A100'
                        WHEN a.status_pendaftaran_lms IN (7,9) THEN '0xFFE6781C'
                        WHEN a.status_pendaftaran_lms IN (11,12,14) THEN '0xFF057DCD'
                        WHEN a.status_pendaftaran_lms IN (15,18) THEN '0xFF1DB74E'
                        ELSE '0xFFCD202E'
                    END AS txt_color
                    FROM tbl_lms a , tbl_statuslms b, tbl_vlookup c 
                    WHERE a.brand = c.id_vlookup AND a.status_pendaftaran_lms = b.id AND a.badge_id = '$badge_id' AND a.status_pendaftaran_lms IN ($lms_ongoing)
                    UNION
                    SELECT a.id, 'Meeting Room' AS category,'1' AS category_id, a.title_meeting AS title, CONCAT(c.room_name, ', ', DATE_FORMAT(a.meeting_date, '%d %b %Y'), ', ', TIME_FORMAT(a.meeting_start, '%H:%i'), '-', TIME_FORMAT(a.meeting_end, '%H:%i')) AS subtitle, 
                    a.booking_date AS date,
                    a.update_date as lastupdate,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN 'Ruangan dibooking'
                        WHEN a.statusmeeting_id IN (2) THEN 'Menunggu meeting dimulai'
                        WHEN a.statusmeeting_id IN (4) THEN 'Sedang Berlangsung'
                        WHEN a.statusmeeting_id IN (3) THEN 'Reschedule'
                        WHEN a.statusmeeting_id IN (5) THEN 'Selesai'
                        ELSE 'Dibatalkan'
                    END AS stat_title,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN '0xFFF0F1F3'
                        WHEN a.statusmeeting_id IN (2) THEN '0xFFFFF7E6'
                        WHEN a.statusmeeting_id IN (4) THEN '0xFFE6F2FA'
                        WHEN a.statusmeeting_id IN (3) THEN '0xFFFFEEF6'
                        WHEN a.statusmeeting_id IN (5) THEN '0xFFE8F8ED'
                        ELSE '0xFFF9E9EA'
                    END AS bg_color,
                    CASE
                        WHEN a.statusmeeting_id IN (1) THEN '0xFF41443D'
                        WHEN a.statusmeeting_id IN (2) THEN '0xFFE8A100'
                        WHEN a.statusmeeting_id IN (4) THEN '0xFF057DCD'
                        WHEN a.statusmeeting_id IN (3) THEN '0xFFE84B93'
                        WHEN a.statusmeeting_id IN (5) THEN '0xFF1DB74E'
                        ELSE '0xFFCD202E'
                    END AS txt_color
                    FROM tbl_meeting a , tbl_participant b, tbl_roommeeting c, tbl_statusmeeting d 
                    WHERE a.id = b.meeting_id AND a.roommeeting_id = c.id AND a.statusmeeting_id = d.id AND b.participant = '$badge_id' AND a.statusmeeting_id IN ($meeting_ongoing)
                    UNION
                    SELECT  a.id,'Kritik dan Saran' AS category,
        '1' AS category_id, b.name_vlookup AS title, a.description AS subtitle, a.createdate AS date, a.createdate AS lastupdate,
CASE
            WHEN a.status_kritiksaran IN (1,2) THEN 'Menunggu Tanggapan HRD' 
            WHEN a.status_kritiksaran IN (3) THEN 'Ditanggapi HRD'
            WHEN a.status_kritiksaran IN (4) THEN 'Selesai' 
            ELSE 'Dibatalkan'
        END AS stat_title,
        CASE
            WHEN a.status_kritiksaran IN (1,2) THEN '0xFFFFF7E6' 
            WHEN a.status_kritiksaran IN (3) THEN '0xFFE6F2FA' 
            WHEN a.status_kritiksaran IN (4) THEN '0xFFE8F8ED' 
            ELSE '0xFFF9E9EA'
        END AS bg_color,
        CASE
            WHEN a.status_kritiksaran IN (1,2) THEN '0xFFE8A100'
            WHEN a.status_kritiksaran IN (3) THEN '0xFFE84B93' 
            WHEN a.status_kritiksaran IN (4) THEN '0xFF1DB74E' 
            ELSE '0xFFCD202E'
        END AS txt_color FROM tbl_kritiksaran a, tbl_vlookup b WHERE a.kategori = b.id_vlookup AND a.badge_id = '200400' AND a.status_kritiksaran IN  (1,2)
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
                    DB::raw("
                    CASE
                            WHEN c.statusdowntime_id IN (1) THEN 'Open Ticket'
                            WHEN c.statusdowntime_id IN (2, 4) THEN 'Maintenance Sedang Berlangsung'
                            WHEN c.statusdowntime_id IN (3,5,6) THEN 'Selesai'
                            ELSE 'Dibatalkan'
                        END AS stat_title ,
                        CASE
                            WHEN c.statusdowntime_id IN (1) THEN '0xFFF0F1F3'
                            WHEN c.statusdowntime_id IN (2, 4) THEN '0xFFE6F2FA'
                            WHEN c.statusdowntime_id IN (5) THEN '0xFFE8F8ED'
                            ELSE '0xFFF9E9EA'
                    END AS bg_color ,
                    CASE
                        WHEN c.statusdowntime_id IN (1) THEN '0xFF41443D'
                            WHEN c.statusdowntime_id IN (2, 4) THEN '0xFF057DCD'
                            WHEN c.statusdowntime_id IN (3,5,6) THEN '0xFF1DB74E'
                            ELSE '0xFFCD202E'
                    END AS txt_color"),
                ])
                ->join('tbl_device as b', 'a.equipment_number', '=', 'b.equipment_number')
                ->join('tbl_downtime as c', 'b.id', '=', 'c.device_id')
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

    /**
     * MMS Riwayat Pengajuan
     **/
    public function listMMS($badge_id)
    {
        // $badgeId = $request->badge_id;
        // query sql
        $query = "SELECT a.id, merek_hp, jenis_permohonan, tipe_hp, waktu_pengajuan, status_pendaftaran_mms FROM tbl_mms a
                        JOIN tbl_statusmms b ON a.status_pendaftaran_mms = b.id
                        WHERE badge_id = '$badge_id' ";
        $data = DB::select($query);

        // insialisasi tanggal today dan kemarin
        $hari_ini = date('Y-m-d', time());
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        // Cek kategori  permohonan
        foreach ($data as $key => $item) {
            if ($item->merek_hp == null) {
                $item->merek_hp = '-';
            }

            if ($item->merek_hp != null) {
                $item->merek_hp = $this->getBrand($item->merek_hp);
            }

            if ($item->jenis_permohonan == null) {
                $item->jenis_permohonan = 1;
            }

            if ($item->jenis_permohonan == 1) {
                $item->jenis_permohonan = 'Karyawan baru';
            }

            if ($item->jenis_permohonan == 3) {
                $item->jenis_permohonan = 'Penambahan Hp Baru';
            }

            $itemTime = strtotime($item->waktu_pengajuan);
            $itemDate = date('Y-m-d', $itemTime);

            if ($itemDate == $hari_ini) {
                $item->waktu_pengajuan = 'Hari Ini, ' . date('H:i', $itemTime);
            } elseif ($itemDate == $kemarin) {
                $item->waktu_pengajuan = 'Kemarin, ' . date('H:i', $itemTime);
            } else {
                $item->waktu_pengajuan = date('d-m-Y, H:i', $itemTime);
            }

            /**
             * apabila status id nya adalah 4 atau id nya adalah 9
             */
            if ($item->status_pendaftaran_mms == 4) {
                $item->status_pendaftaran_mms = 2;
            }

            if ($item->status_pendaftaran_mms == 9) {
                $item->status_pendaftaran_mms = 7;
            }

            $item->status = $this->getTitle($item->status_pendaftaran_mms);
        }
        
        // Buat array baru untuk menampung hasil query
        $dataarray = [];

        // Proses hasil query dan isi array $data
        foreach ($data as $item) {
            // Lakukan semua proses pengolahan data seperti yang telah Anda lakukan sebelumnya

            $processedItem = [
                'title' => 'Pengajuan HP',
                "id"=> $item->id,
                "merek_hp"=> $item->merek_hp,
                "jenis_permohonan"=> $item->jenis_permohonan,
                "tipe_hp"=> $item->tipe_hp,
                "waktu_pengajuan"=> $item->waktu_pengajuan,
                "status_pendaftaran_mms"=> $item->status_pendaftaran_mms,
                "status"=> $item->status
            ];

            // Tambahkan hasil proses ke dalam array $data
            $dataarray[] = $processedItem;
        }
        return $dataarray;
    }

    public function getBrandSmartphone()
    {
        $query = "SELECT * FROM tbl_vlookup WHERE category = 'BRD'";
        $data = DB::select($query);

        return response()->json([
            'message' => 'Success get all brand for SmartPhone',
            'data' => $data,
        ]);
    }
    private function getBrand($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }
    public function getTitle($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statusmms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }

    /**
     * LMS RIwayat Pengajuan
     */
    public function listLms($badge_id)
    {
        // $request->validate([
        //     "badge_id" => "required"
        // ]);

        // query
        $query = "SELECT a.id, brand, tipe_laptop, tanggal_pengajuan, alasan, durasi, start_date, end_date, status_pendaftaran_lms FROM tbl_lms a
                        JOIN tbl_statuslms b ON a.status_pendaftaran_lms = b.id
                        WHERE badge_id = '$badge_id'";
        $data = DB::select($query);

        // insialisasi tanggal today dan kemarin
        $hari_ini = date('Y-m-d', time());
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        foreach ($data as $key => $item) {
            if ($item->brand == null) {
                $item->brand = '-';
            }

            if ($item->brand != null) {
                $item->brand = $this->getBrandLms($item->brand);
            }

            if ($item->alasan == 61) {
                $item->alasan = 'Untuk Bekerja';
            }

            if ($item->alasan == 62) {
                $item->alasan = 'Alasan Lainnya';
            }

            $itemTime = strtotime($item->tanggal_pengajuan);
            $itemDate = date('Y-m-d', $itemTime);

            if ($itemDate == $hari_ini) {
                $item->tanggal_pengajuan = 'Hari Ini, ' . date('H:i', $itemTime);
            } elseif ($itemDate == $kemarin) {
                $item->tanggal_pengajuan = 'Kemarin, ' . date('H:i', $itemTime);
            } else {
                $item->tanggal_pengajuan = date('d-m-Y, H:i', $itemTime);
            }

            /**
             * durasi pemakaian
             */
            $item->durasi_pemakaian = 'Unlimated Duration';
            if ($item->durasi == 57) {
                $item->durasi_pemakaian = $item->start_date . ' s/d ' . $item->end_date;
            }

            /**
             * apabila status id nya adalah 4 atau id nya adalah 9
             */
            if ($item->status_pendaftaran_lms == 4) {
                $item->status_pendaftaran_lms = 2;
            }

            if ($item->status_pendaftaran_lms == 9) {
                $item->status_pendaftaran_lms = 7;
            }

            $item->status = $this->getTitleLms($item->status_pendaftaran_lms);
        }

        // Buat array baru untuk menampung hasil query
        $dataarray = [];

        // Proses hasil query dan isi array $data
        foreach ($data as $item) {
            // Lakukan semua proses pengolahan data seperti yang telah Anda lakukan sebelumnya

            $processedItem = [
                'title' => 'Pengajuan Laptop',
                'id' => $item->id,
                'brand' => $item->brand,
                'tipe_laptop' => $item->tipe_laptop,
                'tanggal_pengajuan' => $item->tanggal_pengajuan,
                'alasan' => $item->alasan,
                'durasi' => $item->durasi,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status_pendaftaran_lms' => $item->status_pendaftaran_lms,
                'durasi_pemakaian' => $item->durasi_pemakaian,
                'status' => $item->status,
            ];

            // Tambahkan hasil proses ke dalam array $data
            $dataarray[] = $processedItem;
        }

        // dd($data);
        return $dataarray;
    }
    /**
     * function untuk get title status lms,
     * berguna ketika di halaman list lms
     */
    public function getTitleLms($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statuslms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }

    /**
     * get name brand laptop
     */
    private function getBrandLms($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }
}
