<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuestionsController extends Controller
{
    //function get all questions
    /**
     * Ini merupakan sebuah fungsi dimana kita mendapatkan
     * get all question dari tabel question yang nantinya 
     * akan ditampilkan pada tampilan mobile.
     */
    public function getAllQuestions(){
        /**
         * get semua all questions
         */

        // Cache::forget('all_questions');

        // cek apakah ada cache atau enggak
        if(Cache::has('all_questions')){
            // ambil data dari cache
            $data = Cache::get("all_questions");
        }
        else {

            // second * minute * hour * day  
            $times = 60 * 60 * 24 * 1;

            $data = DB::table('tbl_listquestion')->get();

            // letakkan di cache
            Cache::put('all_questions', $data, $times); // 60 second
        }

        // $data = DB::table('tbl_listquestion')->get();
       
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
