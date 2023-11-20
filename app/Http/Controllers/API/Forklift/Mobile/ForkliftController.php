<?php

namespace App\Http\Controllers\API\Forklift\Mobile;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ForkliftController extends Controller
{
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

        $status = $request->id_status;

        $forklift = "SELECT f.id,f.name, f.assetno, f.licenseno,	f.battery, f.id_status, f.image1, f.image2, f.image3 , (SELECT vl.name FROM tbl_vlookup vl 
        WHERE vl.category = 'FORKLIFT' AND vl.id = f.id_status)as name_status 
        FROM tbl_forklift f
        WHERE f.id_status = '$status'
        ORDER BY
        CAST(SUBSTRING(name FROM '[0-9]+') AS integer),
        name
        ";

        $query = DB::select($forklift);

        if (count($query) > 0) {
            // Mengubah URL gambar di dalam $query
            foreach ($query as &$item) {
                $item->image1 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image1}");
                $item->image2 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image2}");
                $item->image3 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image3}");
                //prod
                // $item->image1 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image1}");
                // $item->image2 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image2}");
                // $item->image3 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image3}");
            }
        }
        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'Data retrieved Successfully',
            'DATA' => $query,
        ]);
    }

    public function searchForklift(Request $request)
    {
        $txSearch = '%' . strtoupper(trim($request->forklift)) . '%';
        $query = "SELECT
        id,
        NAME,
        assetno,
        licenseno,
        brand,
        id_status,
        image1,
        image2,
        image3,
        (SELECT vl.name FROM tbl_vlookup vl WHERE vl.category = 'FORKLIFT' AND vl.id = id_status)as name_status
        FROM tbl_forklift
        WHERE (UPPER(NAME) LIKE '$txSearch' OR UPPER(brand) LIKE '$txSearch' OR UPPER(assetno) LIKE '$txSearch' OR UPPER(licenseno) LIKE '$txSearch')";
        $data = DB::select($query);

        if (count($data) > 0) {
            // Mengubah URL gambar di dalam $query
            foreach ($data as &$item) {
                $item->image1 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image1}");
                $item->image2 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image2}");
                $item->image3 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image3}");
                //prod
                // $item->image1 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image1}");
                // $item->image2 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image2}");
                // $item->image3 = str_replace(' ', '%20', "http://192.168.88.60:7011/upload/{$item->image3}");
            }
        }

        return response()->json([
            'RESPONSE' => 200,
            'MESSAGETYPE' => 'S',
            'MESSAGE' => 'Data retrieved Successfully',
            'DATA' => $data,
        ]);
    }

    public function uploadFile(Request $request)
    {
        // dd($request->all());

        // Pastikan request memiliki file dengan nama 'file'
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');

            // Simpan file di dalam direktori public/RoomMeeting
            $file->move(public_path('upload/'), $file->getClientOriginalName());

            return response()->json(['message' => 'File berhasil diupload']);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);

    }
}

