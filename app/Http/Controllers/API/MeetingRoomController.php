<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MeetingRoomController extends Controller
{

    public function __construct()
    {
        $this->middleware('api', ['except' => ['login']]);
    }

    public function login(Request $req)
    {

        try {

            $validator = Validator::make(request()->all(), [
                'badge_id'  => 'required',
                'password'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "RESPONSE_CODE" => 400,
                    "MESSAGETYPE"   => "E",
                    "MESSAGE"       => $validator->messages(),
                ], 400)->header(
                    "Accept",
                    "application/json"
                );
            }

            $credentials = $req->only('badge_id', 'password');

            if (Auth::attempt($credentials)) {
                $token = JWTAuth::fromUser(Auth::user());
                return response()->json([
                    "RESPONSE"      => 200,
                    "MESSAGETYPE"   => "S",
                    "MESSAGE"       => "SUCCESS",
                    "TOKEN"          => $token
                ]);
            } else {
                return response()->json([
                    "RESPONSE_CODE" => 401,
                    "MESSAGETYPE"   => "E",
                    "MESSAGE"       => 'UNAUTHORIZED',
                ], 401)->header(
                    "Accept",
                    "application/json"
                );
            }
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',
            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * untuk mendapatkan list meeting yang ada pada
     * tbl_meeting untuk ditampilkan pada aplikasi
     * mobile atau aplikasi tablet
     */
    public function getAllSchedule(Request $request)
    {


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

            /**
             * dapatkan request dari permintaan browser yang
             * telah disediakan dan simpan pada variable
             */
            $startDate = $request->startDate;
            $endDate   = $request->endDate;
            $status    = $request->status;
            $roomId    = $request->ruangan;
            $badge_id = $request->badge_id;

            $get_deptcode = "SELECT dept_code FROM tbl_deptauthorize WHERE badge_id = '$badge_id'";
            $deptcode = DB::select($get_deptcode);

            // Mengambil nilai dept_code dari setiap baris hasil query
            $deptcodeValues = collect($deptcode)->pluck('dept_code')->toArray();

            // Mengonversi array nilai dept_code menjadi string dengan pemisah koma
            $deptcodeString = "'" . implode("','", $deptcodeValues) . "'";
            // dd($deptcodeString);

            /**
             * apabila startdate dan enddate
             */
            if ($startDate == '' || $endDate == '') {
                return response()->json([
                    "RESPONSE_CODE" => 400,
                    "MESSAGETYPE"   => "E",
                    "MESSAGE"       => "START DATE AND END DATE IS REQUIRED",
                ], 400)->header(
                    "Accept",
                    "application/json"
                );
            }



            /**
             * Lakukan insialisasi query
             */
            $q = "SELECT
                    a.id,
                    a.title_meeting,
                    a.roommeeting_id,
                    rm.room_name,
                    rm.dept,
                    a.meeting_date,
                    a.meeting_start,
                    a.meeting_end,
                    a.statusmeeting_id,
                    a.booking_by,
                    (SELECT status_name_ina FROM tbl_statusmeeting WHERE id = statusmeeting_id) AS status_meeting_name_ina,
                    (SELECT status_name_eng FROM tbl_statusmeeting WHERE id = statusmeeting_id) AS status_meeting_name_eng,
                    COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS jumlah_partisipan
                FROM tbl_meeting a LEFT JOIN tbl_roommeeting rm ON rm.id = roommeeting_id
                WHERE (meeting_date BETWEEN '$startDate' AND '$endDate') AND
                ( EXISTS ( SELECT 1 FROM tbl_participant p WHERE p.meeting_id = a.id AND p.participant = '$badge_id') OR
                    rm.dept IN ($deptcodeString, 'SATNUSA'  )
                )";

            if (request()->has('status')) {
                $q .= "AND statusmeeting_id IN($status)";
            }

            if ($roomId != '%') {
                $q .= "AND a.roommeeting_id IN ($roomId)";
            }

            $q .= ' ORDER BY a.meeting_start ASC';

            $list_schedule = DB::select($q);
            $arrData = array();
            $arrData2 = array();

            // Apabila terdapat schedule yang ada
            if ($list_schedule) {
                foreach ($list_schedule as $r) {

                    $arrParticipant = array();

                    $id = $r->id;

                    // cek partisioan yang ada
                    // $dataParticipant = DB::table('tbl_participant')->where('meeting_id', $id)->get();
                    $query_participant = "SELECT
                                                id,
                                                meeting_id,
                                                participant,
                                                (SELECT fullname FROM tbl_karyawan WHERE badge_id = participant) as participant_name,
                                                (SELECT dept_code FROM tbl_karyawan WHERE badge_id = participant) as dept_code
                                            FROM tbl_participant WHERE meeting_id = '$id' ";
                    $dataParticipant   = DB::select($query_participant);

                    // Maka lakukan proses untuk insert ke array arrParticipant
                    if (COUNT($dataParticipant) > 0) {
                        foreach ($dataParticipant as $rp) {
                            $dp = array(
                                'Id' => $rp->id,
                                'Meeting_Id' => $rp->meeting_id,
                                'Participant' => $rp->participant,
                                'Participan_Name' => $rp->participant_name,
                                'Dept_Code' => $rp->dept_code ? $rp->dept_code : 'N/A',
                                'Participant_Image' => env('BASE_URL') ."/EmplFoto/" . $rp->participant . ".JPG"
                            );
                            array_push($arrParticipant, $dp);
                        }
                    }

                    if ($r->statusmeeting_id != 5) {
                        $d = array(
                            'Id'                        => $id,
                            'Title_Meeting'             => $r->title_meeting,
                            'Room_Meeting_Id'           => $r->roommeeting_id,
                            'Room_Name'                 => $r->room_name,
                            'Booking_By'                => $r->booking_by,
                            'Meeting_Date'              => $r->meeting_date,
                            'Meeting_Start'             => substr($r->meeting_start, 0, 5),
                            'Meeting_End'               => substr($r->meeting_end, 0, 5),
                            'Status_Meeting_Id'         => $r->statusmeeting_id,
                            'Status_Meeting_Name_Ina'   => $r->status_meeting_name_ina,
                            'Status_Meeting_Name_Eng'   => $r->status_meeting_name_eng,
                            'Count_Participant'         => $r->jumlah_partisipan,
                            'Participant'               => $arrParticipant
                        );
                        array_push($arrData, $d);
                    }

                    if ($r->statusmeeting_id == 5) {
                        $d = array(
                            'Id'                        => $id,
                            'Title_Meeting'             => $r->title_meeting,
                            'Room_Meeting_Id'           => $r->roommeeting_id,
                            'Room_Name'                 => $r->room_name,
                            'Booking_By'                => $r->booking_by,
                            'Meeting_Date'              => $r->meeting_date,
                            'Meeting_Start'             => substr($r->meeting_start, 0, 5),
                            'Meeting_End'               => substr($r->meeting_end, 0, 5),
                            'Status_Meeting_Id'         => $r->statusmeeting_id,
                            'Status_Meeting_Name_Ina'   => $r->status_meeting_name_ina,
                            'Status_Meeting_Name_Eng'   => $r->status_meeting_name_eng,
                            'Count_Participant'         => $r->jumlah_partisipan,
                            'Participant'               => $arrParticipant
                        );
                        array_push($arrData2, $d);
                    }
                }
            }

            // gabungin array 1 dan array 2, agar yang complete menjadi paling bawah
            /**
             * proses pemisahan array ini dengan tujuan untuk melakukan proses filtering
             * bahwa yang ongoing, menunggu rapat, akan ditampilkan pad abaris atas
             * dan yang sudah selesai itu ditampilkan pada list yang bawah
             */
            $array_gabungan = array_merge($arrData, $arrData2);

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $array_gabungan
            ]);
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                "MESSAGETYPE"   => "E",
                "MESSAGE" => "Something when wrong",
            ], 400)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * untuk get all room
     */
    /**
     * fungsi ini dipakai untuk mendapatkan
     * get semua list ruangan yang tersedia pada
     * database dan akan mereturn semua ruangan
     * yang ada.
     */
    public function getAllRoom(Request $request)
    {

        $img = $request->img == "true" ? true : false;
$txFilter = "";
if ($img == true) {
    $txFilter = "roomimage_1 as Room_Image_1, roomimage_2 as Room_Image_2, roomimage_3 as Room_Image_3,";
}

$badge_id = $request->badge_id;

$get_deptcode = "SELECT dept_code FROM tbl_deptauthorize WHERE badge_id = '$badge_id'";
$deptcode = DB::select($get_deptcode);

// Mengambil nilai dept_code dari setiap baris hasil query
$deptcodeValues = collect($deptcode)->pluck('dept_code')->toArray();

// Mengonversi array nilai dept_code menjadi string dengan pemisah koma
$deptcodeString = "'" . implode("','", $deptcodeValues) . "'";

// Query untuk ruangan public Satnusa
$queryAllPublicRoom = "SELECT
                        r.id AS Id,
                        r.room_name AS Room_Name,
                        r.floor AS Floor,
                        r.capacity AS Capacity,
                        $txFilter
                        r.dept,
                        CASE
                            WHEN m.roommeeting_id IS NOT NULL THEN 'false'
                            ELSE 'true'
                        END AS disable
                    FROM
                        tbl_roommeeting r
                    LEFT JOIN
                        (SELECT DISTINCT roommeeting_id
                         FROM tbl_meeting
                         WHERE meeting_date = '$request->meetingdate'
                           AND (TIME('$request->meetingstart') + INTERVAL 1 MINUTE) <= meeting_end
                           AND (TIME('$request->meetingend') - INTERVAL 1 MINUTE) >= meeting_start
                           AND statusmeeting_id <> '6') m
                    ON r.id = m.roommeeting_id
                    WHERE r.dept IN ('SATNUSA')
                    ORDER BY
                        CAST(SUBSTRING_INDEX(r.room_name, ' ', -1) AS UNSIGNED),
                        r.room_name";

$dataSatnusaRoom  = DB::select($queryAllPublicRoom);

$arrData = [];

// Menggabungkan data dari dataSatnusaRoom
foreach ($dataSatnusaRoom as $r) {
    $d = [
        'Id'        => $r->Id,
        'Room_Name' => $r->Room_Name,
        'Floor'     => $r->Floor,
        'Capacity'  => $r->Capacity,
        'dept'      => $r->dept,
        'available'   => $r->disable // Pastikan ini ditambahkan
    ];

    // Tambahkan URL gambar jika $img true
    if ($img == true) {
        $d['Room_Image_1'] = env('BASE_URL') . "/RoomMeetingFoto/" . $r->Room_Image_1;
        $d['Room_Image_2'] = env('BASE_URL') . "/RoomMeetingFoto/" . $r->Room_Image_2;
        $d['Room_Image_3'] = env('BASE_URL') . "/RoomMeetingFoto/" . $r->Room_Image_3;
    }

    array_push($arrData, $d);
}

// Menggabungkan data
$array_gabungan = $arrData;

if (count($array_gabungan) > 0) {
    return response()->json([
        "RESPONSE"      => 200,
        "MESSAGETYPE"   => "S",
        "MESSAGE"       => "SUCCESS",
        "DATA"          => $array_gabungan
    ]);
}

return response()->json([
    "MESSAGETYPE"   => "E",
    "MESSAGE" => "Something went wrong",
], 400)->header(
    "Accept",
    "application/json"
);

    }

    /**
     * untuk search room
     */
    /**
     * ini adalah fungsi untuk menampilkan search room
     * dengan seaerch nya dapat dicari dengan params
     * room name
     */
    public function searchRoom(Request $request)
    {
        $search = $request->search;
        $search = "%" . $search . "%";
        if ($request->search == '') {
            $search = "%%";
        }

        // Insialisasi query
        $query = "SELECT
                    id as Id,
                    room_name as Room_Name,
                    floor as Floor,
                    capacity as Capacity
        FROM tbl_roommeeting WHERE room_name LIKE '$search' ";

        $data = DB::select($query);
        if (COUNT($data) > 0) {
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $data
            ]);
        }

        return response()->json([
            "MESSAGETYPE"   => "E",
            "MESSAGE" => "Something when wrong",
        ], 400)->header(
            "Accept",
            "application/json"
        );
    }

    /**
     * Detail meeting room
     */
    /**
     * ini adalah fungsi untuk mendapatkan
     * detail schedule ketika schedule di klik
     */
    public function detailSchedule(Request $request)
    {

        $idMeeting = $request->id_meeting;
        if ($idMeeting == '') {
            return response()->json([
                "message" => "ID Meeting Tidak Boleh Kosong!"
            ], 400);
        }

        // Insialisasi query
        $query = "
            SELECT
                a.id as Id,
                a.roommeeting_id as Room_Meeting_Id,
                a.title_meeting as Title_Meeting,
                a.meeting_date as Meeting_Date,
                a.meeting_start as Meeting_Start,
                a.meeting_end as Meeting_End,
                description as Description,
                (SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id ) AS Room_Name,
                (SELECT FLOOR FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Floor,
                (SELECT capacity FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Capacity,
                a.booking_by as Booking_By,
                a.reason as Reason,
                a.category_meeting as Category_Meeting,
                a.jumlah_tamu as Jumlah_Tamu,
                a.ext as Ext,
                a.project_name as Project_Name,
                a.customer_name as Customer_Name,
                (SELECT fullname FROM tbl_karyawan WHERE badge_id = a.booking_by) AS Employee_Name,
                (SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Name,
                a.statusmeeting_id as Status_Meeting_Id
            FROM tbl_meeting a WHERE id = '$idMeeting'
        ";
        $dataMeeting = DB::select($query);

        if (COUNT($dataMeeting) > 0) {

            if ($dataMeeting[0]->Reason == null) {
                $dataMeeting[0]->Reason = '-';
            }

            // get participant
            $query_user = "SELECT
                                participant,
                                optional,
                                kehadiran,
                                (SELECT fullname FROM tbl_karyawan WHERE badge_id = a.participant ) as fullname,
                                (SELECT position_name FROM tbl_position WHERE position_code = (SELECT position_code FROM tbl_karyawan WHERE badge_id = a.participant)) AS position_name
                            FROM tbl_participant a WHERE meeting_id = '$idMeeting' ";
            $data_user  = DB::select($query_user);

            $list_user  = [];
            if ($data_user == 0) {
                $list_user = [];
            } else {
                foreach ($data_user as $key => $item) {
                    $arrItem = [
                        'Badge_Id' => $item->participant,
                        'Optional' => $item->optional,
                        'Fullname' => $item->fullname,
                        'Position' => $item->position_name,
                        'Kehadiran' => $item->kehadiran,
                        'Image'    => env('BASE_URL'). "/EmplFoto/" . $item->participant . ".JPG",
                    ];
                    array_push($list_user, $arrItem);
                }
            }

            // get fasilitas by detail
            $query_fasilitas = "SELECT
                                    meetingfasilitas_id AS Id,
                                    (SELECT fasilitas FROM tbl_meetingfasilitas WHERE id = meetingfasilitas_id ) AS Nama_Fasilitas
                                FROM tbl_meetingfasilitasdetail WHERE meeting_id = '$idMeeting'";
            $data_fasilitas  = DB::select($query_fasilitas);

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"    => [
                    "Info_Meeting"     => $dataMeeting[0],
                    "List_Participant" => $list_user,
                    "List_Fasilitas"   => $data_fasilitas ? $data_fasilitas : []
                ]
            ]);
        }
    }

    /**
     * search user by badge
     */
    /**
     * ini adalah fungsi untuk melakukan pencarian user berdasarkan
     * badge ataupun fullname dan nantinya akan mengeluarkan
     * response informasi berupa id, fullname, badge, position name,
     * dan image dari karyawan tersebut
     */
    public function searchUser(Request $request)
    {

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


        // Ini adalah insialisasi query dengan fullname
        $fullname = "%" . $request->fullname . "%";

        $badge = $request->badge_id;


        if(!$badge){
            // jika tidak memasukan parameter badge
            $query = "SELECT
                            id as Id,
                            fullname as Employee_Name,
                            badge_id as Badge,
                            (SELECT position_name FROM tbl_position WHERE position_code = a.position_code) as Position
                    FROM tbl_karyawan a WHERE fullname LIKE '$fullname' OR badge_id LIKE '$fullname'  LIMIT 30";

        } else{
            $query_dept = "SELECT LEFT(line_code, 4) AS linecode FROM `tbl_karyawan` WHERE line_code <> '-' AND badge_id = '$badge' GROUP BY linecode ";
            $dept = DB::select($query_dept);
            $dept = $dept[0];

            $checkSpecialBadge = DB::table('tbl_allparticipantauthorize')->where('badge', $badge)->first();

            // ketika badge nya berada di special all participant view
            if($checkSpecialBadge){
                $query = "SELECT
                            id as Id,
                            fullname as Employee_Name,
                            badge_id as Badge,
                            (SELECT position_name FROM tbl_position WHERE position_code = a.position_code) as Position
                    FROM tbl_karyawan a WHERE fullname LIKE '$fullname' OR badge_id LIKE '$fullname'  LIMIT 30";
            }else if($dept->linecode == 'MG11' || $dept->linecode == 'DR11'){
                $query = "SELECT
                            id as Id,
                            fullname as Employee_Name,
                            badge_id as Badge,
                            (SELECT position_name FROM tbl_position WHERE position_code = a.position_code) as Position
                    FROM tbl_karyawan a WHERE fullname LIKE '$fullname' OR badge_id LIKE '$fullname'  LIMIT 30";
            }
            else{
                $query = "SELECT * FROM (
                            (SELECT a.id AS Id, a.fullname AS Employee_Name, a.badge_id AS Badge, b.position_name AS POSITION FROM tbl_karyawan a, tbl_position b
                            WHERE a.position_code = b.position_code AND LEFT(a.line_code,4) = '$dept->linecode')
                            UNION
                            (SELECT  a.id AS Id, a.fullname AS Employee_Name, c.badge_id AS Badge, b.position_name AS POSITION
                            FROM tbl_karyawan a, tbl_position b, tbl_mgworkarea c WHERE a.badge_id = c.badge_id AND a.position_code = b.position_code  AND  c.dept_code = '$dept->linecode')) AS A
                            where employee_name LIKE '$fullname' OR badge LIKE '$fullname' LIMIT 30";
            }
        }

        // dd($query);
        $data = DB::select($query);
        $dataNew = [];
        if (COUNT($data) > 0) {
            foreach ($data as $key => $item) {
                $item->image = env('BASE_URL') . "/EmplFoto/" . $item->Badge . ".JPG";
                array_push($dataNew, $item);
            }
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $dataNew,
            ]);
        }

        if (COUNT($data) == 0) {
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $dataNew,
            ]);
        }

        return response()->json([
            "MESSAGETYPE"   => "E",
            "MESSAGE" => "Something when wrong",
        ], 400)->header(
            "Accept",
            "application/json"
        );
    }

    /**
     * Insert Meeting
     *
     * ini adalah fungsi untuk melakukan insert meeting
     * dari mobile dengan mengirimkan data berupa
     * room, title, dan params yang dibutuhkan lainnya yang
     * dapat dibaca pada penggalan kode saat melakukan
     * insert ke database
     */
    public function insertMeeting(Request $request)
    {

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

        /**
         * Apabila exstention number tidak ada maka munculkan pesan error
         * dengan return message 400.
         * ini untuk mencegah aplikasi lama add meeting berulang-ulang, jadi pengguna akan
         * di berikan pesan error dan harus update
         */
        if (!request()->has('ext')) {
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SUSPECT APLIKASI LAMA, SILAHKAN UPDATE',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }

        try {
            /**
             * Jangan ganti key response message nya
             * karena trigger dari mobile menggunakan value tsb
             */
            $query_cek = "SELECT COUNT(*) AS count_meetings FROM tbl_meeting
                            WHERE
                                meeting_date = '$request->meeting_date' AND
                            (TIME('$request->meeting_start') + INTERVAL 1 MINUTE) <= meeting_end AND (TIME('$request->meeting_end') - INTERVAL 1 MINUTE) >= meeting_start
                            AND (roommeeting_id = '$request->roommeeting_id') AND (statusmeeting_id <> '6')";

            $data      = DB::select($query_cek);

            if ($data[0]->count_meetings > 0) {
                return response()->json([
                    "RESPONSE_CODE" => 400,
                    "MESSAGETYPE"   => "E",
                    "MESSAGE"       => 'Tidak Bisa Booking di jam yang telah kamu inputkan',

                ], 200)->header(
                    "Accept",
                    "application/json"
                );
            }
        } catch (\Throwable $th) {
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }

        try {

            DB::beginTransaction();

            $validator = Validator::make(request()->all(), [
                'roommeeting_id'  => 'required',
                'title_meeting'  => 'required',
                'meeting_date'  => 'required',
                'meeting_start'  => 'required',
                'meeting_end'  => 'required',
                'booking_by'     => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "RESPONSE_CODE" => 400,
                    "MESSAGETYPE"   => "E",
                    "MESSAGE"       => $validator->messages(),
                ], 400)->header(
                    "Accept",
                    "application/json"
                );
            }

            $meetingId = $request->roommeeting_id;
            $titleMeeting = $request->title_meeting;
            $meetDate = $request->meeting_date;
            $meetStart = $request->meeting_start;
            $meetEnd = $request->meeting_end;
            $meetDesc = $request->description;
            $booking_by = $request->booking_by;
            $meetParticipant = $request->data_participant ? $request->data_participant : [];
            $meetFasilitas   = $request->data_fasilitas ? $request->data_fasilitas : [];
            $jumlah_tamu     = $request->jumlah_tamu ? $request->jumlah_tamu : 0;
            $ext_no          = $request->ext;
            $project_name    = $request->project_name;
            $customer_name    = $request->customer_name;

            $category_meeting = 0;
            if ($jumlah_tamu > 0) {
                $category_meeting = 1;
            }


            $dataMeeting = [
                'roommeeting_id'    => $meetingId,
                'title_meeting'     => $titleMeeting,
                'meeting_date'      => $meetDate,
                'meeting_start'     => $meetStart,
                'meeting_end'       => $meetEnd,
                'statusmeeting_id'  => 2,
                'description'       => $meetDesc,
                'booking_by'        => $booking_by,
                'booking_date'      => date("Y-m-d H:i:s"),
                'category_meeting'  => $category_meeting,
                'jumlah_tamu'       => $jumlah_tamu,
                'ext'               => $ext_no,
                'project_name'      => $project_name,
                'customer_name'     => $customer_name,
            ];

            /**
             * query get name by booking by
             */
            $query_nama = "SELECT fullname FROM tbl_karyawan WHERE badge_id = '$booking_by' ";
            $data_nama  = DB::select($query_nama);
            $nama = "";
            if (COUNT($data_nama) > 0) {
                $nama = $data_nama[0]->fullname;
            }

            $newIdMeeting = DB::table('tbl_meeting')
                ->insertGetId($dataMeeting);

            // handle to insert partisipan
            if (count($meetParticipant) > 0) {
                for ($i = 0; $i < count($meetParticipant); $i++) {
                    $dp = array(
                        'meeting_id' => $newIdMeeting,
                        'participant' => $meetParticipant[$i]['participant'],
                        'optional' => $meetParticipant[$i]['optional']
                    );
                    DB::table('tbl_participant')->insert($dp);
                }
            }

            // handle to insert tabel meetingfasilitasdetail
            if (COUNT($meetFasilitas) > 0) {
                foreach ($meetFasilitas as $key => $idFasiltas) {
                    DB::table('tbl_meetingfasilitasdetail')
                        ->insert([
                            "meeting_id" => $newIdMeeting,
                            "meetingfasilitas_id" => $idFasiltas
                        ]);
                }
            }

            for ($i = 1; $i <= 2; $i++) {

                $remark = "";
                if ($i == 1) {
                    $remark = "Meeting room has been booked by " . $nama;
                }


                DB::table('tbl_riwayatmeeting')
                    ->insert([
                        'meeting_id'            => $newIdMeeting,
                        'statusmeeting_id'      => $i,
                        'createby'              => $booking_by,
                        'createdate'            => date("Y-m-d H:i:s"),
                        'remark'                => $remark
                    ]);
            }

            DB::commit();

            // send notif with hardcode

            $formattedDate = date('d F Y', strtotime($meetDate));

            $badgeIds = $this->getBadgeAuthorizeNotification($meetingId);

            foreach ($badgeIds as $badgeId){
                $this->sendNotifKeResepsionis($badgeId, "Rapat Baru  ".$titleMeeting , $formattedDate . ", Pukul " .$meetStart, $newIdMeeting);
            }
            // $this->sendNotifKeResepsionis("PKL84", "Rapat Baru  ".$titleMeeting , $formattedDate . ", Pukul " .$meetStart, $newIdMeeting);
            // prod

            // $this->sendNotifKeResepsionis("200040", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);
            // $this->sendNotifKeResepsionis("200195", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);
            // $this->sendNotifKeResepsionis("036834", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);
            // $this->sendNotifKeResepsionis("039264", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);
            // $this->sendNotifKeResepsionis("033861", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);


            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID' => $newIdMeeting,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * api meeting saya
     * ini merupakan fungsi untuk mendapatkan
     * semua meeting saya
     */
    public function myMeeting(Request $request)
    {

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

        /**
         * badge_id dan is finish sebagai parameter
         */
        $badge_id  = $request->badge_id;
        $is_finish = $request->is_finish;

        if ($badge_id == "") {
            return response()->json([
                "message" => "Badge ID tidak boleh kosong"
            ], 400);
        }

        /**
         * apabila is_finish nya tidak sama dengan 1
         */
        if ($is_finish != 1) {
            // $data = DB::table('tbl_meeting as a')
            //     ->select(
            //         'a.id as Id',
            //         'a.title_meeting as Title Meeting',
            //         'a.roommeeting_id as Room_Meeting_Id',
            //         DB::raw('(SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Room_Name'),
            //         'a.meeting_date as Meeting_Date',
            //         'a.meeting_start as Meeting_Start',
            //         'a.meeting_end as Meeting_End',
            //         'a.statusmeeting_id as Status_Meeting_Id',
            //         DB::raw('(SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Ina'),
            //         DB::raw('(SELECT status_name_eng FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Eng'),
            //         DB::raw('COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS Total_Participant')
            //     )
            //     ->where('a.booking_by', $badge_id)
            //     ->whereIn('a.statusmeeting_id', [2, 3, 4])
            //     ->orderBy('a.id', 'ASC')
            //     ->paginate(10);

            // maka perhatikan kondisi di where in, dengan status where 2, 3, dan 4
            $data = DB::table('tbl_participant as b')
                ->select(
                    'a.id as Id',
                    'a.title_meeting as Title Meeting',
                    'a.roommeeting_id as Room_Meeting_Id',
                    DB::raw('(SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Room_Name'),
                    'a.meeting_date as Meeting_Date',
                    'a.meeting_start as Meeting_Start',
                    'a.meeting_end as Meeting_End',
                    'a.statusmeeting_id as Status_Meeting_Id',
                    DB::raw('(SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Ina'),
                    DB::raw('(SELECT status_name_eng FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Eng'),
                    DB::raw('COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS Total_Participant')
                )
                ->join('tbl_meeting as a', 'b.meeting_id', '=', 'a.id')
                ->where('b.participant', $badge_id)
                ->whereIn('a.statusmeeting_id', [2, 3, 4])
                ->orderBy('a.meeting_date', 'ASC')
                ->orderBy('a.meeting_start', 'ASC')
                ->paginate(10);
        }

        /**
         * apabila is_finish nya sama dengan 1
         */
        if ($is_finish == 1) {
            // $data = DB::table('tbl_meeting as a')
            //     ->select(
            //         'a.id as Id',
            //         'a.title_meeting as Title Meeting',
            //         'a.roommeeting_id as Room_Meeting_Id',
            //         DB::raw('(SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Room_Name'),
            //         'a.meeting_date as Meeting_Date',
            //         'a.meeting_start as Meeting_Start',
            //         'a.meeting_end as Meeting_End',
            //         'a.statusmeeting_id as Status_Meeting_Id',
            //         DB::raw('(SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Ina'),
            //         DB::raw('(SELECT status_name_eng FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Eng'),
            //         DB::raw('COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS Total_Participant')
            //     )
            //     ->where('a.booking_by', $badge_id)
            //     ->whereIn('a.statusmeeting_id', [5, 6])
            //     ->orderBy('a.id', 'ASC')
            //     ->paginate(10);

            // maka perhatikan kondisi di where in, dengan status where 2, 3, dan 4
            $data = DB::table('tbl_participant as b')
                ->select(
                    'a.id as Id',
                    'a.title_meeting as Title Meeting',
                    'a.roommeeting_id as Room_Meeting_Id',
                    DB::raw('(SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Room_Name'),
                    'a.meeting_date as Meeting_Date',
                    'a.meeting_start as Meeting_Start',
                    'a.meeting_end as Meeting_End',
                    'a.statusmeeting_id as Status_Meeting_Id',
                    DB::raw('(SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Ina'),
                    DB::raw('(SELECT status_name_eng FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Meeting_Name_Eng'),
                    DB::raw('COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS Total_Participant')
                )
                ->join('tbl_meeting as a', 'b.meeting_id', '=', 'a.id')
                ->where('b.participant', $badge_id)
                ->whereIn('a.statusmeeting_id', [5, 6])
                ->orderBy('a.meeting_date', 'DESC')
                ->orderBy('a.meeting_start', 'DESC')
                ->paginate(10);
        }


        $total = $data->total();
        $current_page = $data->currentPage();
        $last_page = $data->lastPage();
        $next_page_url = $data->nextPageUrl();
        $prev_page_url = $data->previousPageUrl();

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            "CURRENT_PAGE"  => $current_page,
            "LAST_PAGE"     => $last_page,
            "NEXT_PAGE_URL" => $next_page_url,
            "PREV_PAGE_URL" => $prev_page_url,
            "DATA"          => $data->items()
        ]);
    }

    /**
     * function untuk edit meeting
     */
    /**
     * ini adalah proses melakukan update meeting ke database
     * dengan beberapa request dari sisi client untuk melakukan
     * updtae data
     */
    public function updateMeeting(Request $request)
    {


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

        /**
         * Jangan ganti key response message nya
         * karena trigger dari mobile menggunakan value tsb
         */
        // $isReschedule = false;
        try {

            $query_meeting = "SELECT * FROM tbl_meeting WHERE id = '$request->id_meeting' ";
            $data_meeeting = DB::select($query_meeting)[0];
            $badge_pembuat  = $data_meeeting->booking_by;

            $query_karyawan = "SELECT fullname FROM tbl_karyawan WHERE badge_id = '$badge_pembuat' ";
            $data_karyawan  = DB::select($query_karyawan);
            $nama_pembuat   = $data_karyawan[0]->fullname;

            if (
                ($data_meeeting->roommeeting_id != $request->roommeeting_id) ||
                ($data_meeeting->meeting_date != $request->meeting_date) ||
                ($data_meeeting->meeting_start != $request->meeting_start . ":00") ||
                $data_meeeting->meeting_end != $request->meeting_end . ":00"
            ) {
                // lakukan pengecekan apakah available untuk ruangan tersebut
                $query_cek = "SELECT COUNT(*) AS count_meetings FROM tbl_meeting
                                WHERE
                          meeting_date = '$request->meeting_date' AND
                          ((TIME('$request->meeting_start') + INTERVAL 1 MINUTE) <= meeting_end AND (TIME('$request->meeting_end') - INTERVAL 1 MINUTE) >= meeting_start) AND
                          (id <> $request->id_meeting) AND (roommeeting_id = $request->roommeeting_id) AND statusmeeting_id IN ('1','2','3','4')";
                $data      = DB::select($query_cek);
                if ($data[0]->count_meetings > 0) {
                    return response()->json([
                        "RESPONSE_CODE" => 400,
                        "MESSAGETYPE"   => "E",
                        "MESSAGE"       => 'Tidak Bisa Booking di jam yang telah kamu inputkan',

                    ], 200)->header(
                        "Accept",
                        "application/json"
                    );
                }
                $isReschedule = true;
            } else {
                // Lakukan insialisasi dari params yang telah didapatkan
                $idMeeting          = $request->id_meeting;
                $roomMeetingId      = $request->roommeeting_id;
                $titleMeeting       = $request->title_meeting;
                $dateMeeting        = $request->meeting_date;
                $startTime          = $request->meeting_start;
                $endTime            = $request->meeting_end;
                $description        = $request->description;
                $meetParticipant    = $request->data_participant ? $request->data_participant : [];
                $meetFasilitas      = $request->data_fasilitas ? $request->data_fasilitas : [];
                $jumlah_tamu        = $request->jumlah_tamu ? $request->jumlah_tamu : 0;
                $ext_no             = $request->ext;
                $project_name       = $request->project_name;
                $customer_name      = $request->customer_name;

                $category_meeting = 0;
                if ($jumlah_tamu > 0) {
                    $category_meeting = 1;
                }



                DB::beginTransaction();
                try {

                    // update meeting
                    DB::table('tbl_meeting')
                        ->where('id', $idMeeting)
                        ->update([
                            'title_meeting'     => $titleMeeting,
                            'description'       => $description,
                            'category_meeting'  => $category_meeting,
                            'jumlah_tamu'       => $jumlah_tamu,
                            'update_date'       => date('Y-m-d H:i:s'),
                            'updateby'          => $badge_pembuat,
                            'ext'               => $ext_no,
                            'project_name'      => $project_name,
                            'customer_name'     => $customer_name,
                        ]);

                    // delete tabel participant utk insert ulang
                    // DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

                    // delete tabel
                    DB::table('tbl_meetingfasilitasdetail')->where('meeting_id', $idMeeting)->delete();

                    // handle to insert tabel meetingfasilitasdetail
                    if (COUNT($meetFasilitas) > 0) {
                        foreach ($meetFasilitas as $key => $idFasiltas) {
                            DB::table('tbl_meetingfasilitasdetail')
                                ->insert([
                                    "meeting_id" => $idMeeting,
                                    "meetingfasilitas_id" => $idFasiltas
                                ]);
                        }
                    }

                    DB::commit();

                    $badgeIds = $this->getBadgeAuthorizeNotification($roomMeetingId);

                    // dd($badgeIds);
                    foreach ($badgeIds as $badgeId) {
                        // dd($badgeId);
                        // send update notif ke resepsionis
                        $this->sendNotifKeResepsionis($badgeId, "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail", $idMeeting);
                    }
                    // prod
                    // $this->sendNotifKeResepsionis("200040", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");
                    // $this->sendNotifKeResepsionis("200195", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");
                    // $this->sendNotifKeResepsionis("036834", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");
                    // $this->sendNotifKeResepsionis("039264", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");
                    // $this->sendNotifKeResepsionis("033861", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");



                    return response()->json([
                        "RESPONSE"      => 200,
                        "MESSAGETYPE"   => "S",
                        "MESSAGE"       => "SUCCESS",
                        'MEEETING_ID'   => $idMeeting,
                    ]);
                } catch (\Throwable $th) {
                    // dd($th);
                    DB::rollBack();
                    return response()->json([
                        "RESPONSE_CODE" => 400,
                        "MESSAGETYPE"   => "E",
                        "MESSAGE"       => 'SOMETHING WENT WRONG',

                    ], 401)->header(
                        "Accept",
                        "application/json"
                    );
                }
            }
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }

        // apabila ada perubahan pada date, room, start time, dan end time
        /**
         * maka akan melakukan reschdule
         */
        $idMeeting          = $request->id_meeting;
        $roomMeetingId      = $request->roommeeting_id;
        $titleMeeting       = $request->title_meeting;
        $dateMeeting        = $request->meeting_date;
        $startTime          = $request->meeting_start;
        $endTime            = $request->meeting_end;
        $description        = $request->description;
        $meetParticipant    = $request->data_participant ? $request->data_participant : [];
        $meetFasilitas      = $request->data_fasilitas ? $request->data_fasilitas : [];
        $jumlah_tamu        = $request->jumlah_tamu ? $request->jumlah_tamu : 0;
        $ext_no             = $request->ext;
        $project_name       = $request->project_name;
        $customer_name      = $request->customer_name;

        $category_meeting = 0;
        if ($jumlah_tamu > 0) {
            $category_meeting = 1;
        }


        DB::beginTransaction();
        try {

            // update meeting
            DB::table('tbl_meeting')
                ->where('id', $idMeeting)
                ->update([
                    'roommeeting_id'    => $roomMeetingId,
                    'title_meeting'     => $titleMeeting,
                    'meeting_date'      => $dateMeeting,
                    'meeting_start'     => $startTime,
                    'meeting_end'       => $endTime,
                    'description'       => $description,
                    'statusmeeting_id'  => 3,
                    'category_meeting'  => $category_meeting,
                    'jumlah_tamu'       => $jumlah_tamu,
                    'update_date'       => date('Y-m-d H:i:s'),
                    'ext'               => $ext_no,
                    'project_name'      => $project_name,
                    'customer_name'     => $customer_name,
                ]);

            DB::table('tbl_riwayatmeeting')
                ->insert([
                    'meeting_id'            => $idMeeting,
                    'statusmeeting_id'      => 3,
                    'createby'              => $badge_pembuat,
                    'createdate'            => date("Y-m-d H:i:s"),
                    'remark'                => $nama_pembuat . " has just rescheduled the Meeting Schedule"
                ]);

            // delete tabel participant utk insert ulang
            // DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

            // delete tabel meeting fasulitas detail
            DB::table('tbl_meetingfasilitasdetail')->where('meeting_id', $idMeeting)->delete();

            // handle to insert tabel meetingfasilitasdetail
            if (COUNT($meetFasilitas) > 0) {
                foreach ($meetFasilitas as $key => $idFasiltas) {
                    DB::table('tbl_meetingfasilitasdetail')
                        ->insert([
                            "meeting_id" => $idMeeting,
                            "meetingfasilitas_id" => $idFasiltas
                        ]);
                }
            }


            DB::commit();


            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * function untuk extend meeting
     *
     **/
    public function extendMeeting(Request $request)
    {
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

        $idMeeting          = $request->id_meeting;
        $badge_pembuat      = $request->booking_by;
        $meeting_end       = $request->extended_meeting_end;

        $query_karyawan = "SELECT fullname FROM tbl_karyawan WHERE badge_id = '$badge_pembuat' ";
        $data_karyawan  = DB::select($query_karyawan);
        $nama_pembuat   = $data_karyawan[0]->fullname;

        DB::beginTransaction();
        try {
           // update meeting
           DB::table('tbl_meeting')
                ->where('id', $idMeeting)
                ->update([
                    'updateby'    => $badge_pembuat,
                    'meeting_end'     => $meeting_end,
                ]);

            DB::table('tbl_riwayatmeeting')
                ->insert([
                    'meeting_id'            => $idMeeting,
                    'statusmeeting_id'      => 4,
                    'createby'              => $badge_pembuat,
                    'createdate'            => date("Y-m-d H:i:s"),
                    'remark'                => $nama_pembuat . " has just Extended the Meeting"
                ]);

                DB::commit();
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * function untuk speedup meeting
     * ketika rapat sudah selesai akan mengirim notif
     * ke rapat selanjutnya
     *
     **/
    public function endEarlyMeeting(Request $request)
    {
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

        $idMeeting          = $request->id_meeting;
        $badge_pembuat      = $request->booking_by;
        $meeting_end       = $request->meeting_end_early;

        $data_meeting = DB::table('tbl_meeting')
            ->where('id', $idMeeting)
            ->first();

            // dd($data_meeting);
        $checkDataInterval =
        "SELECT id, title_meeting, roommeeting_id, meeting_date, meeting_start, meeting_end, statusmeeting_id , booking_by
        FROM tbl_meeting
        WHERE meeting_date = '$data_meeting->meeting_date'
            AND roommeeting_id = '$data_meeting->roommeeting_id'
            AND NOT statusmeeting_id IN ('5','6')
            AND TIMEDIFF(meeting_start, '$data_meeting->meeting_end') >= '00:00:00'
            AND TIMEDIFF(meeting_start, '$data_meeting->meeting_end') <= '02:00:00'
        ORDER BY meeting_start ASC;
        ";
        $interval = DB::SELECT($checkDataInterval);
        // dd($interval);

        // dd($interval);
        if($interval){

            $data_room = DB::table('tbl_roommeeting')
            ->where('id', $interval[0]->roommeeting_id)
            ->first();

            $realtime = date('H:i', strtotime($meeting_end));
            // dd($realtime);

            $client = new Client();
                $data   = [
                    'badge_id' => $interval[0]->booking_by,
                    'message'  => "Ruangan $data_room->room_name sudah tersedia lebih awal pada pukul $realtime",
                    'sub_message' => "Ketuk untuk mengubah jadwal rapat",
                    'category'    => "MEETING",
                    'tag'         => 'Meeting',
                    'dynamic_id'  => $interval[0]->id
                ];

                // dd($data);
                $response =  $client->post(env('BASE_URL'). '/api/notifikasi/send', [
                    'json' => $data,
                ]);

                // dev
                // $response =  $client->post('http://192.168.88.60:7005/api/notifikasi/send', [
                //     'json' => $data,
                // ]);
            }

        DB::beginTransaction();
        try {
           // update meeting
           DB::table('tbl_meeting')
                ->where('id', $idMeeting)
                ->update([
                    'updateby'    => $badge_pembuat,
                    'meeting_end'     => $meeting_end,
                    'statusmeeting_id' => '5'
                ]);

            DB::table('tbl_riwayatmeeting')
                ->insert([
                    'meeting_id'            => $idMeeting,
                    'statusmeeting_id'      => 5,
                    'createby'              => $badge_pembuat,
                    'createdate'            => date("Y-m-d H:i:s")
                ]);

                DB::commit();
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * function cancel meeting
     * ini adalah proses untuk melakukan cancel meeting saat
     * pengguna ingin melakukan cancel meeting
     */
    public function cancelMeeting(Request $request)
    {

        /**
         * ini adalah params
         */
        $idMeeting = $request->id_meeting;
        $reason    = $request->reason;
        $badge_id  = $request->badge_id;

        /**
         * disini proses melakukan update tabel
         * meeting dan ke tabel riwayat meeting
         */
        DB::beginTransaction();
        try {
            DB::table('tbl_meeting')
                ->where('id', $idMeeting)
                ->update([
                    'statusmeeting_id' => 6, // cancel
                    'reason'           => $reason
                ]);

            DB::table('tbl_riwayatmeeting')
                ->insert([
                    'meeting_id'            => $idMeeting,
                    'statusmeeting_id'      => 6,
                    'createby'              => $badge_id,
                    'createdate'            => date("Y-m-d H:i:s"),
                    'remark'                => $reason
                ]);

            /**
             * Ini mendapatkan title meeting
             */
            $query_meeting_get = "SELECT title_meeting, roommeeting_id FROM tbl_meeting WHERE id = '$idMeeting'";
            $data_meeting      = DB::select($query_meeting_get);
            $title_meeting         = '';
            if (COUNT($data_meeting) > 0) {
                $title_meeting     = $data_meeting[0]->title_meeting;
                $roommeeting_id     = $data_meeting[0]->roommeeting_id;
            }

            DB::commit();

            $badgeIds = $this->getBadgeAuthorizeNotification($roommeeting_id);
                    foreach ($badgeIds as $badgeId) {
                        // send update notif ke resepsionis
                        $this->sendNotifKeResepsionis($badgeId, "Meeting `" . $title_meeting ."` telah dibatalkan", "Ketuk untuk lihat lebih detail", $idMeeting);

                    }
            // prod
            // $this->sendNotifKeResepsionis("200040", "Meeting `" . $title_meeting ."` telah dibatalkan", "Ketuk untuk lihat lebih detail");
            // $this->sendNotifKeResepsionis("200195", "Meeting `" . $title_meeting ."` telah dibatalkan", "Ketuk untuk lihat lebih detail");
            // $this->sendNotifKeResepsionis("036834", "Meeting `" . $title_meeting . "` telah dibatalkan", "Ketuk untuk lihat lebih detail");
            // $this->sendNotifKeResepsionis("039264", "Meeting `" . $title_meeting . "` telah dibatalkan", "Ketuk untuk lihat lebih detail");
            // $this->sendNotifKeResepsionis("033861", "Meeting `" . $title_meeting . "` telah dibatalkan", "Ketuk untuk lihat lebih detail");


            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * function beri tanggapan
     */
    /**
     * disini adalah proses untuk memberikan tanggapan
     * pada meeting, disini dapat dengan memberikan params
     * idMeeting, createBy, dan Tanggapan
     */
    public function beriTanggapan(Request $request)
    {

        $idMeeting = $request->id_meeting;
        $createBy  = $request->create_by;
        $tanggapan = $request->tanggapan;

        /**
         * ambil dulu nilai status meeting
         */
        $query = "SELECT statusmeeting_id FROM tbl_meeting WHERE id = '$idMeeting' ";
        $data  = DB::select($query);

        try {

            // if ($data[0]->statusmeeting_id != 2 || $data[0]->statusmeeting_id != 3) {
            //     return response()->json([
            //         "RESPONSE"      => 201,
            //         "MESSAGETYPE"   => "S",
            //         "MESSAGE"       => "Tidak Boleh Memberikan Komentar"
            //     ]);
            // }
            /**
             * lalu lakukan proses insert ke tanggapan meeting
             */
            DB::table('tbl_tanggapanmeeting')
                ->insert([
                    'meeting_id' => $idMeeting,
                    'tanggapan'  => $tanggapan,
                    'createby'   => $createBy,
                    'createdate' => date("Y-m-d H:i:s")
                ]);
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "RESPONSE_CODE" => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => 'SOMETHING WENT WRONG',

            ], 401)->header(
                "Accept",
                "application/json"
            );
        }
    }

    /**
     * function detail meeeting saya
     * dimana ini adalah proses untuk mendapatkan detail meeting
     * dimana pengguna akan melempar id meeting dan
     * badge id
     */
    public function detailMeetingSaya(Request $request)
    {

        $idMeeting = $request->id_meeting;
        $badgeId   = $request->badge_id;

        if ($idMeeting == '') {
            return response()->json([
                "message" => "ID Meeting Tidak Boleh Kosong!"
            ], 400);
        }

        // Insialisasi query meeting
        $query = "
            SELECT
                a.id as Id,
                a.roommeeting_id as Room_Meeting_Id,
                a.title_meeting as Title_Meeting,
                a.meeting_date as Meeting_Date,
                a.meeting_start as Meeting_Start,
                a.meeting_end as Meeting_End,
                description as Description,
                (SELECT room_name FROM tbl_roommeeting WHERE id = a.roommeeting_id ) AS Room_Name,
                (SELECT FLOOR FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Floor,
                (SELECT capacity FROM tbl_roommeeting WHERE id = a.roommeeting_id) AS Capacity,
                a.booking_by as Booking_By,
                a.reason as Reason,
                a.category_meeting as Category_Meeting,
                a.jumlah_tamu as Jumlah_Tamu,
                a.ext as Ext,
                a.project_name as Project_Name,
                a.customer_name as Customer_Name,
                (SELECT fullname FROM tbl_karyawan WHERE badge_id = a.booking_by) AS Employee_Name,
                (SELECT status_name_ina FROM tbl_statusmeeting WHERE id = a.statusmeeting_id) AS Status_Name,
                a.statusmeeting_id as Status_Meeting_Id
            FROM tbl_meeting a WHERE id = '$idMeeting'
        ";
            $dataMeeting = DB::select($query);
            $meeting_date =  $dataMeeting[0]->Meeting_Date;
            $room =  $dataMeeting[0]->Room_Meeting_Id;
            $meeting_end =  $dataMeeting[0]->Meeting_End;


        if (COUNT($dataMeeting) > 0) {
            if ($dataMeeting[0]->Reason == null) {
                $dataMeeting[0]->Reason = '-';
            }

            // insialisasi query interval max extend



            $checkDataInterval = "SELECT id, title_meeting, roommeeting_id, meeting_date, meeting_start, meeting_end, statusmeeting_id
            FROM tbl_meeting
            WHERE meeting_date = '$meeting_date'
            AND roommeeting_id = '$room'
            AND NOT statusmeeting_id
            IN ('5','6') AND meeting_start > '$meeting_end' ORDER BY meeting_start ASC LIMIT 1"; //13:00:00 start meeting

            $interval = DB::SELECT($checkDataInterval);
            if($interval){
                // dd($interval[0]->meeting_start);
                $NextStart = abs((strtotime($interval[0]->meeting_start) - strtotime($dataMeeting[0]->Meeting_End)) / 60);
            }else{
                $NextStart = abs((strtotime('21:00:00') - strtotime($dataMeeting[0]->Meeting_End)) / 60);
            }

            $NextStart = intval($NextStart);


            // dd($NextStart);
            // Insialisasi query participan
            $query_user = "SELECT
                                participant,
                                optional,
                                kehadiran,
                                (SELECT fullname FROM tbl_karyawan WHERE badge_id = a.participant ) as fullname,
                                (SELECT position_name FROM tbl_position WHERE position_code = (SELECT position_code FROM tbl_karyawan WHERE badge_id = a.participant)) AS position_name
                            FROM tbl_participant a WHERE meeting_id = '$idMeeting' ";
            $data_user  = DB::select($query_user);

            $list_user  = [];
            if ($data_user == 0) {
                $list_user = [];
            } else {
                foreach ($data_user as $key => $item) {
                    $arrItem = [
                        'Badge_Id' => $item->participant,
                        'Optional' => $item->optional,
                        'Fullname' => $item->fullname,
                        'Kehadiran' => $item->kehadiran,
                        'Position' => $item->position_name,
                        'Image'    => env('BASE_URL') . "/EmplFoto/" . $item->participant . ".JPG"
                    ];
                    array_push($list_user, $arrItem);
                    $index = array_search($dataMeeting[0]->Booking_By, array_column($list_user, 'Badge_Id'));
                    if ($index != false) {
                        $element = array_splice($list_user, $index, 1);
                        array_unshift($list_user, $element[0]);
                    }
                }
            }

            // Lalu insialisasi query tanggapan
            $query_tanggapan = "SELECT
                                    id as Id,
                                    tanggapan as Tanggapan,
                                    createdate as Create_Date,
                                    createby as Create_By,
                                    (SELECT fullname FROM tbl_karyawan WHERE badge_id = Create_By ) as Full_Name,
                                    (SELECT position_code FROM tbl_karyawan WHERE badge_id = Create_By ) as Position_Code
                                FROM tbl_tanggapanmeeting WHERE meeting_id = '$idMeeting' ORDER BY id DESC";
            $data_tanggapan  = DB::select($query_tanggapan);
            foreach ($data_tanggapan as $key => $value) {
                $value->Image =  env('BASE_URL') ."/EmplFoto/" . $value->Create_By . ".JPG";
            }

            // get fasilitas by detail
            $query_fasilitas = "SELECT
                    meetingfasilitas_id AS Id,
                    (SELECT fasilitas FROM tbl_meetingfasilitas WHERE id = meetingfasilitas_id ) AS Nama_Fasilitas
                FROM tbl_meetingfasilitasdetail WHERE meeting_id = '$idMeeting'";
            $data_fasilitas  = DB::select($query_fasilitas);


            $Info_Meeting = $dataMeeting[0];
            $Info_Meeting->max_extend = $NextStart;
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"    => [
                    "Info_Meeting"     => $dataMeeting[0],
                    "List_Participant" => $list_user,
                    "List_Tanggapan"   => $data_tanggapan,
                    "List_Fasilitas"   => $data_fasilitas ? $data_fasilitas : []
                ]
            ]);
        }
    }

    /**
     * function untuk send notif
     */
    /**
     * fungsi untuk mengirim notifikasi
     */
    public function sendNotif(Request $request)
    {
        // dd($badge_id);

        if (!request()->has('message')) {
            $message = "";
        }

        if (!request()->has('sub_message')) {
            $sub_message = "";
        }


        $badge_id = $request->badge_id;
        $message  = $request->message;
        $sub_message = $request->sub_message;
        $meetingId = $request->dynamic_id;

        /**
         * query untuk send notif
         */
        $query_player_id = "SELECT player_id FROM tbl_mms WHERE badge_id = '$badge_id'";
        $data_player_id = DB::select($query_player_id);


        $arr_playerId = [];
        foreach ($data_player_id as $key => $value) {
            if ($value->player_id != null) {
                array_push($arr_playerId, $value->player_id);
            }
        }

        // URL Endpoint API OneSignal
        $url = 'https://onesignal.com/api/v1/notifications';

        // Data untuk dikirim dalam permintaan
        $data = [
            'app_id' => 'ef44a0e1-1de9-48a0-b4c5-9e045d45c0cf',
            'include_player_ids' => $arr_playerId,
            'headings' => [
                'en' => $message,
            ],
            'contents' => [
                'en' => $sub_message
            ],
            'data' => [
                'Category' => 'MEETING_ROOM',
                'Dynamic_id' => $meetingId
            ],
        ];

        // Konversi data ke format JSON
        $dataJson = json_encode($data);

        // Pengaturan opsi cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic NmQ2ODI0YjEtNjZhYy00ZDA3LWJkMDEtY2ViZDJjZWNmMTk5',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Eksekusi permintaan cURL
        $response = curl_exec($ch);

        // Periksa jika ada kesalahan dalam permintaan
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            // Lakukan penanganan kesalahan yang sesuai
            // ...
        }

        // Mendapatkan informasi respons
        $info = curl_getinfo($ch);
        $httpCode = $info['http_code'];

        // Menutup koneksi cURL
        curl_close($ch);


        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS"
        ]);
    }

    /**
     * private function send notif ke resepsionis
     * ini adalah sebuah fungsi untuk melakukan send notifikasi
     * kepada resepsionis
     */
    public function sendNotifKeResepsionis($badgeid, $message, $subMessage,$newIdMeeting)
    {
        // dd('$newIdMeeting');
        // URL API tujuan
        $apiUrl = env('BASE_URL') . '/api/notifikasi/send';
        // $apiUrl = 'http://192.168.88.60:7005/api/notifikasi/send';
        // $apiUrl = 'http://127.0.0.1:8000/api/notifikasi/send';

        // Membuat instance Client Guzzle
        $client = new Client();

        // Mengirim permintaan POST ke API dengan parameter badge_id, message, dan sub_message
        $client->post($apiUrl, [
            'query' => [
                'badge_id' => $badgeid,
                'message' => $message,
                'sub_message' => $subMessage,
                'category'    => "MEETING",
                'tag'         => 'Info Meeting',
                'dynamic_id'  => $newIdMeeting
            ],
        ]);
    }

    /**
     * function get fasilitas
     * ini adalah fungsi untuk melakukan get fasilitas
     * dari tabel meeeting fasilitas dimana akan mengirim response fasilitas
     */
    public function getListFasilitas()
    {
        $query_get_fasilitas = "SELECT
                                    id as Id,
                                    fasilitas as Nama_Fasilitas
                                FROM tbl_meetingfasilitas";
        $data_fasilitas      = DB::select($query_get_fasilitas);

        if (COUNT($data_fasilitas) > 0) {
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $data_fasilitas
            ]);
        }

        return response()->json([
            "MESSAGETYPE"   => "E",
            "MESSAGE" => "Something when wrong",
        ], 400)->header(
            "Accept",
            "application/json"
        );
    }

    /**
     * function untuk presensi kehadiran meeting
     * ini merupakan fungsi saat pengguna mobile melakukan check
     * selaku host untuk membuat presensi kehadiran dari
     * partisipan yang hadir.
     */
    public function aksiKehadiran(Request $request)
    {
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

        $meetParticipant = $request->data_participant ? $request->data_participant : [];
        $idMeeting       = $request->id_meeting;
        $mode            = strtoupper($request->mode);

        // apabila mode yang didapat adalah SINGLE maka,
        // flow yang dilakukan adalah update
        if ($mode == 'SINGLE') {
            if (COUNT($meetParticipant) > 0) {
                $soloParticipant = $meetParticipant[0];

                $badgeId = $soloParticipant['participant'];
                $kehadiran = $soloParticipant['kehadiran'];

                // cek partipant dgn badge diatas, apakah sudah ada record ?
                $query_participant_cek = "SELECT id FROM tbl_participant WHERE participant = '$badgeId' AND meeting_id = '$idMeeting' ";
                $data_partisipan       = DB::select($query_participant_cek);
                if (COUNT($data_partisipan) > 0) {
                    DB::table('tbl_participant')
                        ->where('id', $data_partisipan[0]->id)
                        ->update([
                            'kehadiran' => $kehadiran
                        ]);
                }
            }
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting
            ]);
        }

        // apabila mode yang didapat adalah ALL maka,
        // flow yang dilakukan adalah delete all dan insert new list participant
        if ($mode == 'ALL') {
            if (COUNT($meetParticipant) > 0) {
                // delete all participant
                DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

                for ($i = 0; $i < count($meetParticipant); $i++) {
                    $dp = array(
                        'meeting_id' => $idMeeting,
                        'participant' => $meetParticipant[$i]['participant'],
                        'optional' => $meetParticipant[$i]['optional'],
                        'kehadiran' => $meetParticipant[$i]['kehadiran']
                    );
                    DB::table('tbl_participant')->insert($dp);
                }
            }
            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                'MEEETING_ID'   => $idMeeting
            ]);
        }
    }

    /**
     * function untuk edit participant
     * ini merupakan sebuah fungsi dimana selaku host
     * pengguna dapat melakukan perubahan partisopan yang
     * terlibat didalam meeting yg telah dibuat oleh
     * host sebelumnya.
     */
    public function editPartisipan(Request $request)
    {

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

        /**
         * ini akan melakukan reset partisipan
         */
        $meetParticipant = $request->data_participant ? $request->data_participant : [];
        $idMeeting       = $request->id_meeting;

        // apabila array pada meet participant lebih dari 0
        if (COUNT($meetParticipant) > 0) {
            // delete all participant
            DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

            for ($i = 0; $i < count($meetParticipant); $i++) {
                $dp = array(
                    'meeting_id' => $idMeeting,
                    'participant' => $meetParticipant[$i]['participant'],
                    'optional' => $meetParticipant[$i]['optional'],
                    'kehadiran' => $meetParticipant[$i]['kehadiran']
                );
                DB::table('tbl_participant')->insert($dp);
            }
        }
        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS",
            'MEEETING_ID'   => $idMeeting
        ]);
    }

    /**
     * function untuk agar crobjob pak ali bisa
     * memberikan notifikasi kepada partisipan terkait
     * 15 menit sebelum meeting dimulai
     */
    public function reminderMeeting(Request $request)
    {

        $id_meeting = $request->id_meeting;
        // dd($id_meeting);
        if ($id_meeting == '') {
            return response()->json([
                "RESPONSE"      => 400,
                "MESSAGETYPE"   => "E",
                "MESSAGE"       => "FAILED, ID MEETING STRING KOSONG"
            ], 400);
        }

        $query = "SELECT participant FROM tbl_participant WHERE meeting_id = '$id_meeting' ";
        $data_participant  = DB::select($query);

        $query_meeting = "SELECT * FROM tbl_meeting WHERE id = '$id_meeting'";
        $data_meeting  = DB::select($query_meeting);

        $title_meeting = "";
        if (COUNT($data_meeting) > 0) {
            $title_meeting = $data_meeting[0]->title_meeting;
        }

        // Proses Looping  Participant
        foreach ($data_participant as $key => $item) {

            try {

                 DB::table('tbl_queuenotif')->insert([
                    'badge_id' => $item->participant,
                    'message' => 'Rapat akan mulai dalam 10 menit',
                    'sub_message' => "Ketuk untuk lihat lebih detail",
                    'category' => 'Meeting',
                    'tag' => 'Meeting',
                    'dynamic_id' => "$id_meeting",
                    'is_send'    => 0,
                    'is_log'     => 1,
                ]);
            } catch (\Throwable $th) {
                dd($th);
            }
        }

        return response()->json([
            "RESPONSE"      => 200,
            "MESSAGETYPE"   => "S",
            "MESSAGE"       => "SUCCESS"
        ]);
    }


    public function getBadgeAuthorizeNotification($deptRoom)
    {
        // dd($deptRoom);
        // ambil department dari room
        $Room = DB::table('tbl_roommeeting')
        ->where('id',$deptRoom)
        ->first();

        $badgeIds = DB::table('tbl_deptauthorize')
        ->where('get_notif', '1')
        ->where('dept_code', $Room->dept)
        ->pluck('badge_id')
        ->toArray();

// dd($badgeIds);
       return $badgeIds;
    }
}
