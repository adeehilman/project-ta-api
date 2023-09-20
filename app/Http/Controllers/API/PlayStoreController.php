<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayStoreController extends Controller
{
    // menjadikan is active = 0, untuk kebutuhan review play store
    public function deletedUser(Request $request)
    {

        if ($request->has('badge_id')) {
            DB::table('tbl_karyawan')
                ->where('badge_id', $request->badge_id)
                ->update([
                    "is_active" => '0'
                ]);
            return response()->json([
                "message" => "Deleted data karyawan berhasil"
            ]);
        }

        return response()->json([
            "message" => "Something went wrong"
        ], 400);
    }
}
