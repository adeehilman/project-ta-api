<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LowonganController extends Controller
{
    // get semua all loker
    public function getAllLoker(Request $request)
    {
        try {
            /**
             * Get semua data dari tabel pemberitahuan
             * untuk ditampilkan di UI Mobile dibagian page tampil pengumuman
             * lakukan pagination bawaan laravel
             */
            $data = DB::table('tbl_lowongankerja')
                ->paginate(10);

            foreach ($data as $key => $item) {
                $item->file_upload = url(asset('/lokerimg/' . $item->file_upload));
            }

            $total = $data->total();
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $next_page_url = $data->nextPageUrl();
            $prev_page_url = $data->previousPageUrl();

            $response = [
                'message' => 'RESPONSE OK, BERHASIL GET DATA LOWONGAN KERJA',
                'data' => $data->items(),
                'jumlah_data_saat_ini' => count($data->items()),
                'jumlah_page' => $last_page,
                'sedang_di_page' => $current_page,
                'link_next_page' => $next_page_url,
                'link_prev_page' => $prev_page_url,
            ];

            return response()->json($response);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json([
                "message" => "Something went wrong when get all pengumuman"
            ], 400);
        }
    }
}
