<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{

    // get all pengumuman
    public function getAllPengumuman(Request $request)
    {
        try {
            /**
             * Get semua data dari tabel pemberitahuan
             * untuk ditampilkan di UI Mobile dibagian page tampil pengumuman
             * lakukan pagination bawaan laravel
             */
            $data = DB::table('tbl_pemberitahuan')
                ->whereIn('receive_by', [4])
                ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
                ->select(
                    'tbl_pemberitahuan.id as id',
                    'judul',
                    'waktu_pemberitahuan',
                    'deskripsi',
                    'image',
                    'nama_grup as penerima'
                )
                ->orderBy('id', 'desc')
                ->paginate(10);

            if ($request->badge_id) {

                // get grup id
                $data_user = DB::table('tbl_karyawan')
                    ->select('id_grup')
                    ->where('badge_id', $request->badge_id)
                    ->first();

                /**
                 * apabila user tidak ada id grup
                 */
                $data = DB::table('tbl_pemberitahuan')
                    ->whereIn('receive_by', [4])
                    ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
                    ->select(
                        'tbl_pemberitahuan.id as id',
                        'judul',
                        'waktu_pemberitahuan',
                        'deskripsi',
                        'image',
                        'nama_grup as penerima'
                    )
                    ->orderBy('id', 'desc')
                    ->paginate(10);

                /**
                 * Apabila karyawan adalah PKB, maka get kategori pkb, all, ptsn
                 */
                if ($data_user->id_grup == 3) {
                    $data = DB::table('tbl_pemberitahuan')
                        ->whereIn('receive_by', [4, 1, $data_user->id_grup])
                        ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
                        ->select(
                            'tbl_pemberitahuan.id as id',
                            'judul',
                            'waktu_pemberitahuan',
                            'deskripsi',
                            'image',
                            'nama_grup as penerima'
                        )
                        ->orderBy('id', 'desc')
                        ->paginate(10);
                }

                /**
                 * Apabila karyawan adalah PTSN, maka get kategori all dan ptsn
                 */
                if ($data_user->id_grup == 1) {
                    $data = DB::table('tbl_pemberitahuan')
                        ->whereIn('receive_by', [4, $data_user->id_grup])
                        ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
                        ->select(
                            'tbl_pemberitahuan.id as id',
                            'judul',
                            'waktu_pemberitahuan',
                            'deskripsi',
                            'image',
                            'nama_grup as penerima'
                        )
                        ->orderBy('id', 'desc')
                        ->paginate(10);
                }

                /**
                 * Apabila karyawan adalah PTSN, maka get kategori all dan ptsn
                 */
                if ($data_user->id_grup == 2) {
                    $data = DB::table('tbl_pemberitahuan')
                        ->whereIn('receive_by', [4, $data_user->id_grup])
                        ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
                        ->select(
                            'tbl_pemberitahuan.id as id',
                            'judul',
                            'waktu_pemberitahuan',
                            'deskripsi',
                            'image',
                            'nama_grup as penerima'
                        )
                        ->orderBy('id', 'desc')
                        ->paginate(10);
                }
            }

            foreach ($data as $key => $item) {
                // $item->image = env('URL_PENGUMUMAN') . $item->image;
                // $file_image = file_get_contents($item->image);
                // $item->image = 'data:image/jpg;base64,' .base64_encode($file_image);
                $plaintext = strip_tags($item->deskripsi);
                $item->deskripsi = substr($plaintext, 0, 15) . ".....";
            }

            $total = $data->total();
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $next_page_url = $data->nextPageUrl();
            $prev_page_url = $data->previousPageUrl();

            $response = [
                'message' => 'RESPONSE OK, BERHASIL GET DATA PENGUMUMANS',
                'data' => $data->items(),
                'jumlah_data_saat_ini' => count($data->items()),
                'last_page' => $last_page,
                'sedang_di_page' => $current_page,
                'link_next_page' => $next_page_url,
                'link_prev_page' => $prev_page_url,
            ];

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "something went wrong"
            ], 400);
        }
    }

    // get detail pengumuman
    public function detailPengumuman(Request $request)
    {

        if (!$request->id) {
            return response()->json([
                "message" => "Params dibutuhkan"
            ]);
        }

        if (!$request->badge) {
            return response()->json([
                "message" => "Params dibutuhkan"
            ]);
        }

        DB::beginTransaction();
        try {

            $cek_pengumuman = DB::table('tbl_dibaca')
                ->where('badge_id', $request->badge)
                ->where('id_pemberitahuan', $request->id)
                ->exists();
            if (!$cek_pengumuman) {
                DB::table('tbl_dibaca')->insert([
                    "badge_id" => $request->badge,
                    "id_pemberitahuan" => $request->id,
                    "waktu_dibaca" => date('Y-m-d H:i:s')
                ]);
                DB::commit();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
        }

        $data = DB::table('tbl_pemberitahuan')
            ->select(
                'tbl_pemberitahuan.id',
                'judul',
                'waktu_pemberitahuan',
                'deskripsi',
                'nama_grup as penerima',
                'image'
            )
            ->join('tbl_grup', 'tbl_grup.id', '=', 'tbl_pemberitahuan.receive_by')
            ->where('tbl_pemberitahuan.id', $request->id)
            ->first();

        if ($data) {

            if($data->image != null){
                $data->image = env('URL_PENGUMUMAN') . $data->image;
                $file_image = file_get_contents($data->image);
                $data->image = 'data:image/jpg;base64,' .base64_encode($file_image);
            }

            return response()->json([
                "message" => "Response OK",
                "data"    => $data
            ]);
        }

        if(!$data){
            return response()->json([
                "message" => "Tidak ada data",
                "data"    => []
            ]);
        }
    }
}
