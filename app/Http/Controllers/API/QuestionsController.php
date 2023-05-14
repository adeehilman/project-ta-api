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
}
