<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LowonganController extends Controller
{
    // get semua all loker, ini belum ada cache
    public function getAllLoker(Request $request)
    {
        try {
            /**
             * Get semua data dari tabel pemberitahuan
             * untuk ditampilkan di UI Mobile dibagian page tampil pengumuman
             * lakukan pagination bawaan laravel
             */

            // Cache::flush();

            $result = [];

            $today = date('Y-m-d');

            $data = DB::table('tbl_lowongankerja')
                ->where(function ($query) use ($today) {
                    $query->where('mulai_berlaku', '<=', $today)
                        ->where('berlaku_sampai', '>=', $today);
                })
                ->orderBy('id', 'desc')
                ->paginate(10);


            foreach ($data as $key => $item) {
                // if ($today >= $item->mulai_berlaku && $today <= $item->berlaku_sampai) {

                // }

                $item->id = $item->id;
                $item->posisi = $item->posisi;
                $plaintext = strip_tags($item->desc);
                $item->desc = substr($plaintext, 0, 15) . ".....";
                $item->mulai_berlaku = $item->mulai_berlaku;
                $item->berlaku_sampai = $item->berlaku_sampai;
                // $item->file_upload = env("URL_LOKER") . $item->file_upload;
                // $file_image = file_get_contents($item->file_upload);
                // $item->file_upload = 'data:image/jpg;base64,' . base64_encode($file_image);
                array_push($result, $item);
            }

            $total = $data->total();
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $next_page_url = $data->nextPageUrl();
            $prev_page_url = $data->previousPageUrl();

            $response = [
                'message' => 'RESPONSE OK, BERHASIL GET DATA LOWONGAN KERJA',
                // 'data' => $data->items(),
                'data' => $result,
                'jumlah_data_saat_ini' => count($result),
                'jumlah_page' => $last_page,
                'sedang_di_page' => $current_page,
                'link_next_page' => $next_page_url,
                'link_prev_page' => $prev_page_url,
            ];

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Something went wrong when get all lowongan kerja"
            ], 400);
        }
    }

    // get semua all loker, yang ini pakai cache
    public function getAllLokerBaru(Request $request)
    {
        try {
            /**
             * Get semua data dari tabel pemberitahuan
             * untuk ditampilkan di UI Mobile dibagian page tampil pengumuman
             * lakukan pagination bawaan laravel
             */

            $result = [];

            $today = date('Y-m-d');
            $page = $request->input('page', 1);
            $cacheKey = 'all_lowongan_kerja_page_' . $page;
            $pageInfoCacheKey = 'all_lowongan_kerja_page_info_' . $page;

            // cek apakah ada di cache
            if (Cache::has($cacheKey)) {
                $result = Cache::get($cacheKey);

                $response = [
                    'message' => 'RESPONSE OK, BERHASIL GET DATA LOWONGAN KERJA',
                    'data' => $result,
                    'jumlah_data_saat_ini' => count($result),
                ];

                // Cek apakah ada cache untuk informasi halaman
                if (Cache::has($pageInfoCacheKey)) {
                    $pageInfo = Cache::get($pageInfoCacheKey);

                    $response['jumlah_page'] = $pageInfo['jumlah_page'];
                    $response['sedang_di_page'] = $pageInfo['sedang_di_page'];
                    $response['link_next_page'] = $pageInfo['link_next_page'];
                    $response['link_prev_page'] = $pageInfo['link_prev_page'];
                }
            } else {
                $data = DB::table('tbl_lowongankerja')
                    ->orderBy('id', 'desc')
                    ->paginate(10);


                foreach ($data as $key => $item) {
                    if ($today >= $item->mulai_berlaku && $today <= $item->berlaku_sampai) {
                        $item->id = $item->id;
                        $item->posisi = $item->posisi;
                        $plaintext = strip_tags($item->desc);
                        $item->desc = substr($plaintext, 0, 15) . ".....";
                        $item->mulai_berlaku = $item->mulai_berlaku;
                        $item->berlaku_sampai = $item->berlaku_sampai;
                        $item->file_upload = env("URL_LOKER") . $item->file_upload;
                        $file_image = file_get_contents($item->file_upload);
                        $item->file_upload = 'data:image/jpg;base64,' . base64_encode($file_image);
                        array_push($result, $item);
                    }
                }

                $total = $data->total();
                $current_page = $data->currentPage();
                $last_page = $data->lastPage();
                $next_page_url = $data->nextPageUrl();
                $prev_page_url = $data->previousPageUrl();

                // Menyimpan informasi halaman dalam cache
                $pageInfo = [
                    'jumlah_page' => $last_page,
                    'sedang_di_page' => $current_page,
                    'link_next_page' => $next_page_url,
                    'link_prev_page' => $prev_page_url,
                ];

                $times = 60 * 60 * 2;

                Cache::put($cacheKey, $result, $times);
                Cache::put($pageInfoCacheKey, $pageInfo, $times);

                $response = [
                    'message' => 'RESPONSE OK, BERHASIL GET DATA LOWONGAN KERJA',
                    // 'data' => $data->items(),
                    'data' => $result,
                    'jumlah_data_saat_ini' => count($result),
                    'jumlah_page' => $last_page,
                    'sedang_di_page' => $current_page,
                    'link_next_page' => $next_page_url,
                    'link_prev_page' => $prev_page_url,
                ];
            }

            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return response()->json([
                "message" => "Something went wrong when get all lowongan kerja"
            ], 400);
        }
    }

    // get detail lowongan
    public function getDetailLowongan(Request $request)
    {

        if (!$request->id) {
            return response()->json([
                "message" => "params dibutuhkan"
            ]);
        }

        $data = DB::table('tbl_lowongankerja')
            ->select(
                'id',
                'posisi',
                'mulai_berlaku',
                'berlaku_sampai',
                'url',
                'file_upload',
                'desc'
            )
            ->where('id', $request->id)
            ->first();

        if ($data) {

            $data->file_upload =  env("URL_LOKER") . $data->file_upload;
            $file_image = file_get_contents($data->file_upload);
            $data->file_upload = 'data:image/jpg;base64,' . base64_encode($file_image);

            return response()->json([
                "message" => "Response OK",
                "data"    => $data
            ]);
        }

        if (!$data) {

            return response()->json([
                "message" => "Data Tidak Ada",
                "data"    => []
            ]);
        }
    }
}
