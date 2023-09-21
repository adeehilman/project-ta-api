<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
            dd($th);
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
     * untuk get list meeeting      
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

            $startDate = $request->startDate;
            $endDate   = $request->endDate;
            $status    = $request->status;
            $roomId    = $request->ruangan;

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


            $q = "SELECT 
                    a.id, 
                    a.title_meeting,
                    a.roommeeting_id,
                    (SELECT room_name FROM tbl_roommeeting WHERE id = roommeeting_id) AS room_name,
                    a.meeting_date,
                    a.meeting_start,
                    a.meeting_end,
                    a.statusmeeting_id,
                    (SELECT status_name_ina FROM tbl_statusmeeting WHERE id = statusmeeting_id) AS status_meeting_name_ina,
                    (SELECT status_name_eng FROM tbl_statusmeeting WHERE id = statusmeeting_id) AS status_meeting_name_eng,
                    COALESCE((SELECT COUNT(*) FROM tbl_participant WHERE meeting_id = a.id), 0) AS jumlah_partisipan
                FROM tbl_meeting a
                WHERE (meeting_date BETWEEN '$startDate' AND '$endDate')";

            if (request()->has('status')) {
                $q .= "AND statusmeeting_id IN($status)";
            }

            if ($roomId != '%') {
                $q .= "AND a.roommeeting_id IN ($roomId)";
            }

            $list_schedule = DB::select($q);
            $arrData = array();

            if ($list_schedule) {
                foreach ($list_schedule as $r) {

                    $arrParticipant = array();

                    $id = $r->id;

                    $dataParticipant = DB::table('tbl_participant')->where('meeting_id', $id)->get();
                    if ($dataParticipant) {
                        foreach ($dataParticipant as $rp) {
                            $dp = array(
                                'Id' => $rp->id,
                                'Meeting_Id' => $rp->meeting_id,
                                'Participant' => $rp->participant,
                                'Participant_Image' => "http://webapi.satnusa.com/EmplFoto/" . $rp->participant . ".JPG"
                            );
                            array_push($arrParticipant, $dp);
                        }
                    }

                    $d = array(
                        'Id'                        => $id,
                        'Title_Meeting'             => $r->title_meeting,
                        'Room_Meeting_Id'           => $r->roommeeting_id,
                        'Room_Name'                 => $r->room_name,
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
            }

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $arrData
            ]);
        } catch (\Throwable $th) {
            dd($th);
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
    public function getAllRoom(Request $request)
    {

        $img = $request->img == "true" ? true : false;
        $txFilter = "";
        if ($img == true) {
            $txFilter = "roomimage_1 as Room_Image_1, roomimage_2 as Room_Image_2, roomimage_3 as Room_Image_3,";
        }

        $query_allRoom = "SELECT
                            id as Id, 
                            room_name as Room_Name, 
                            floor as Floor, 
                            $txFilter 
                            capacity as Capacity
                          FROM tbl_roommeeting ORDER BY Room_Name ASC";
        $data_allRoom  = DB::select($query_allRoom);

        if (COUNT($data_allRoom) > 0) {

            if ($img == true) {
                foreach ($data_allRoom as $key => $item) {
                    $item->Room_Image_1 = "https://webapi.satnusa.com/RoomMeetingFoto/" . $item->Room_Image_1;
                    $item->Room_Image_2 = "https://webapi.satnusa.com/RoomMeetingFoto/" . $item->Room_Image_2;
                    $item->Room_Image_3 = "https://webapi.satnusa.com/RoomMeetingFoto/" . $item->Room_Image_3;
                }
            }

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"          => $data_allRoom
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
     * untuk search room
     */
    public function searchRoom(Request $request)
    {
        $search = $request->search;
        $search = "%" . $search . "%";
        if ($request->search == '') {
            $search = "%%";
        }

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
    public function detailSchedule(Request $request)
    {

        $idMeeting = $request->id_meeting;
        if ($idMeeting == '') {
            return response()->json([
                "message" => "ID Meeting Tidak Boleh Kosong!"
            ], 400);
        }

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

            $query_user = "SELECT 
                                participant, 
                                optional,
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
                        'Image'    => "http://webapi.satnusa.com/EmplFoto/" . $item->participant . ".JPG"
                    ];
                    array_push($list_user, $arrItem);
                }
            }

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"    => [
                    "Info_Meeting"     => $dataMeeting[0],
                    "List_Participant" => $list_user
                ]
            ]);
        }
    }

    /**
     * search user by badge
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

        $fullname = "%" . $request->fullname . "%";
        $query = "		  SELECT
                            id as Id,
                            fullname as Employee_Name,
                            badge_id as Badge,
                            (SELECT position_name FROM tbl_position WHERE position_code = a.position_code) as Position
                    FROM tbl_karyawan a WHERE fullname LIKE '$fullname' OR badge_id LIKE '$fullname'  LIMIT 30";
        $data = DB::select($query);
        $dataNew = [];
        if (COUNT($data) > 0) {
            foreach ($data as $key => $item) {
                $item->image = "https://webapi.satnusa.com/EmplFoto/" . $item->Badge . ".JPG";
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

            $dataMeeting = [
                'roommeeting_id'    => $meetingId,
                'title_meeting'     => $titleMeeting,
                'meeting_date'      => $meetDate,
                'meeting_start'     => $meetStart,
                'meeting_end'       => $meetEnd,
                'statusmeeting_id'  => 2,
                'description'       => $meetDesc,
                'booking_by'        => $booking_by,
                'booking_date'      => date("Y-m-d")
            ];

            $newIdMeeting = DB::table('tbl_meeting')
                ->insertGetId($dataMeeting);

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

            for ($i = 1; $i <= 2; $i++) {
                DB::table('tbl_riwayatmeeting')
                    ->insert([
                        'meeting_id'            => $newIdMeeting,
                        'statusmeeting_id'      => $i,
                        'createby'              => $booking_by,
                        'createdate'            => date("Y-m-d H:i:s")
                    ]);
            }

            DB::commit();

            // send notif with hardcode 

            $formattedDate = date('d F Y', strtotime($meetDate));
            // $this->sendNotifKeResepsionis("200040", "Rapat Baru : ".$titleMeeting , $meetDate . ", Pukul " .$meetStart);
            // $this->sendNotifKeResepsionis("200195", "Rapat Baru : ".$titleMeeting , $meetDate . ", Pukul " .$meetStart);
            $this->sendNotifKeResepsionis("200400", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);
            $this->sendNotifKeResepsionis("038720", "Rapat Baru : " . $titleMeeting, $formattedDate . ", Pukul " . $meetStart);

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


        $badge_id  = $request->badge_id;
        $is_finish = $request->is_finish;

        if ($badge_id == "") {
            return response()->json([
                "message" => "Badge ID tidak boleh kosong"
            ], 400);
        }

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
                ->orderBy('a.id', 'ASC')
                ->paginate(10);
        }

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
                ->orderBy('a.id', 'ASC')
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
                          (id <> $request->id_meeting) AND (roommeeting_id = $request->roommeeting_id)";
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

                $idMeeting          = $request->id_meeting;
                $roomMeetingId      = $request->roommeeting_id;
                $titleMeeting       = $request->title_meeting;
                $dateMeeting        = $request->meeting_date;
                $startTime          = $request->meeting_start;
                $endTime            = $request->meeting_end;
                $description        = $request->description;
                $meetParticipant    = $request->data_participant ? $request->data_participant : [];


                DB::beginTransaction();
                try {

                    // update meeting
                    DB::table('tbl_meeting')
                        ->where('id', $idMeeting)
                        ->update([
                            'title_meeting'     => $titleMeeting,
                            'description'       => $description
                        ]);

                    // delete tabel participant utk insert ulang
                    DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

                    // insert ke tabel participant
                    if (count($meetParticipant) > 0) {
                        for ($i = 0; $i < count($meetParticipant); $i++) {
                            $dp = array(
                                'meeting_id' => $idMeeting,
                                'participant' => $meetParticipant[$i]['participant'],
                                'optional' => $meetParticipant[$i]['optional']
                            );
                            DB::table('tbl_participant')->insert($dp);
                        }
                    }

                    DB::commit();

                    // send update notif ke resepsionis
                    $this->sendNotifKeResepsionis("200400", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");
                    $this->sendNotifKeResepsionis("038720", "Ada perubahan pada meeting " . $titleMeeting, "Ketuk untuk lihat lebih detail");


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



        // apabila ada perubahan pada date, room, start time, dan end time
        $idMeeting          = $request->id_meeting;
        $roomMeetingId      = $request->roommeeting_id;
        $titleMeeting       = $request->title_meeting;
        $dateMeeting        = $request->meeting_date;
        $startTime          = $request->meeting_start;
        $endTime            = $request->meeting_end;
        $description        = $request->description;
        $meetParticipant    = $request->data_participant ? $request->data_participant : [];


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
                    'statusmeeting_id'  => 3
                ]);

            // delete tabel participant utk insert ulang
            DB::table('tbl_participant')->where('meeting_id', $idMeeting)->delete();

            // insert ke tabel participant
            if (count($meetParticipant) > 0) {
                for ($i = 0; $i < count($meetParticipant); $i++) {
                    $dp = array(
                        'meeting_id' => $idMeeting,
                        'participant' => $meetParticipant[$i]['participant'],
                        'optional' => $meetParticipant[$i]['optional']
                    );
                    DB::table('tbl_participant')->insert($dp);
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
     * function cancel meeting
     */
    public function cancelMeeting(Request $request)
    {

        $idMeeting = $request->id_meeting;
        $reason    = $request->reason;
        $badge_id  = $request->badge_id;

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
                    'createdate'            => date("Y-m-d H:i:s")
                ]);

            $query_meeting_get = "SELECT title_meeting FROM tbl_meeting WHERE id = '$idMeeting'";
            $data_meeting      = DB::select($query_meeting_get);
            $title_meeting         = '';
            if(COUNT($data_meeting) > 0){
                $title_meeting     = $data_meeting[0]->title_meeting;
            }

            DB::commit();

            // send update notif ke resepsionis
            $this->sendNotifKeResepsionis("200400", "Meeting `" . $title_meeting ."` telah dibatalkan", "Ketuk untuk lihat lebih detail");
            $this->sendNotifKeResepsionis("038720", "Meeting `" . $title_meeting ."` telah dibatalkan", "Ketuk untuk lihat lebih detail");

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

            $query_user = "SELECT 
                                participant,  
                                optional,
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
                        'Image'    => "http://webapi.satnusa.com/EmplFoto/" . $item->participant . ".JPG"
                    ];
                    array_push($list_user, $arrItem);
                }
            }

            $query_tanggapan = "SELECT id as Id, tanggapan as Tanggapan, createdate as Create_Date, createby as Create_By FROM tbl_tanggapanmeeting WHERE meeting_id = '$idMeeting' ORDER BY id DESC";
            $data_tanggapan  = DB::select($query_tanggapan);

            return response()->json([
                "RESPONSE"      => 200,
                "MESSAGETYPE"   => "S",
                "MESSAGE"       => "SUCCESS",
                "DATA"    => [
                    "Info_Meeting"     => $dataMeeting[0],
                    "List_Participant" => $list_user,
                    "List_Tanggapan"   => $data_tanggapan
                ]
            ]);
        }
    }

    /**
     * function untuk send notif 
     */
    public function sendNotif(Request $request)
    {

        if (!request()->has('message')) {
            $message = "";
        }

        if (!request()->has('sub_message')) {
            $sub_message = "";
        }


        $badge_id = $request->badge_id;
        $message  = $request->message;
        $sub_message = $request->sub_message;

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
                'Category' => 'MEETING_ROOM'
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
     */
    public function sendNotifKeResepsionis($badgeid, $message, $subMessage)
    {

        $badge_id = $badgeid;
        $message  = $message;
        $sub_message = $subMessage;

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
                'Category' => 'MEETING_ROOM'
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
}
