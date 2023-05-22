<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionsController extends Controller
{
    //function get all questions
    public function getAllQuestions(){
        /**
         * get semua all questions
         */
        $data = DB::table('tbl_listquestion')->get();
        return response()->json([
            "message" => "Response OK",
            "data"    => $data
        ]);
    }

    // function check apakah user telah memiliki security question
    public function checkSecurityQuestion(Request $request) {
        if(!$request->badge){
            return response()->json([
                "message" => "Params dibutuhkan"
            ]);
        }

        $data = DB::table('tbl_securityquestion')
                        ->where('badge_id', $request->badge)
                        ->exists();
        if(!$data){
            return response()->json([
                "message" => "User belum mendaftarkan security question, munculkan pop up",
                "status_security"    => 1
            ]);
        }

        if($data){
            return response()->json([
                "message" => "Jangan munculkan pop up, user sudah memiliki security question",
                "status_security"    => 0
            ]);
        }
    }
}
