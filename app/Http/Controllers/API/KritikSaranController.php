<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KritikSaranController extends Controller
{

    // functio get kategori kritik
    /**
     * ini sebuah fungsi helper untuk mendapatkan kategori 
     * dari tabel vlookup dimana kamu dapat melempar sebuah
     * parameter pada id_kategori 
     */
    private function getKategori($id_kategori)
    {
        $data = DB::table('tbl_vlookup')
            ->where('id_vlookup', $id_kategori)
            ->first();

        return $data->name_vlookup;
    }

    // function get status kritik
    /**
     * ini merupakan fungsi untuk mendapatkan riwatar status 
     * kritik dan saran dari tabel riwayat kritrik saran
     * dan di join dengan tbl_statuskritiksaran 
     */
    private function getRiwayatKritikSaran($id_kritiksaran)
    {
        $data = DB::table('tbl_riwayatkritiksaran')
            ->select('tbl_riwayatkritiksaran.status_riwayat as status_riwayat_id', 'stat_title', 'stat_desc', 'createdate')
            ->join('tbl_statuskritiksaran', 'tbl_statuskritiksaran.id', '=', 'tbl_riwayatkritiksaran.status_riwayat')
            ->where('id_kritiksaran', $id_kritiksaran)
            ->orderBy('tbl_riwayatkritiksaran.status_riwayat', 'asc')
            ->get();

        foreach ($data as $key => $item) {
            $item->createdate = date("Y-m-d H:i", strtotime($item->createdate));
        }

        return $data;
    }

    // function get tanggapan 
    /**
     * Dalam fitur di aplikasi mysatnusa
     * terdapar tanggapan kritik saran dimana
     * pengguna dapat memberikan komentar pada menu tanggapan
     * dimana fungsi ini didapatkan dari tgl_getTanggapanKritikSaran
     * artinya ini adalah sebuah list
     */
    private function getTanggapanKritikSaran($id_kritiksaran)
    {
        $data = DB::table('tbl_tanggapankritiksaran')
            ->select('fullname', 'position_code', 'respon', 'waktu', 'img_user')
            ->join('tbl_karyawan', 'tbl_karyawan.badge_id', '=', 'tbl_tanggapankritiksaran.badge_id')
            ->where('id_kritik', $id_kritiksaran)
            ->get();
        return $data;
    }

    // function untuk get semua kritik dan saran
    /**
     * fungsi ini merupakan get all kritik saran 
     * dari kritik dan saran yang telah dibuat oleh pengguna
     * disini kita mengirimkan badge sebagai parameter
     */
    public function getAllKritikDanSaran(Request $request)
    {
        // ini adalah validasi badge id dibutuhkan
        $request->validate([
            "badge_id" => "required"
        ]);

        /**
         * dan dari sini dapat dilihat tabel 
         * kritiksaran dimana badge id adalah 
         */
        $data = DB::table('tbl_kritiksaran')
            ->where('badge_id', $request->badge_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($data as $key => $item) {
            $item->kategori = $this->getKategori($item->kategori);
            $item->status_riwayat_terakhir = $item->status_kritiksaran;
            $item->status_kritiksaran = $this->statusKritikSaran($item->status_kritiksaran) ?  $this->statusKritikSaran($item->status_kritiksaran)->stat_title : null;
            $item->file_upload = url(asset('kritiksaran/' . $item->file_upload));
            $item->riwayat_status = $this->getRiwayatKritikSaran($item->id);

            $item->createdate = date("Y-m-d H:i", strtotime($item->createdate));
        }

        return response()->json([
            "message" => "Response OK",
            "data"    => $data
        ]);
    }

    // ambil value kritik dan saran
    /**
     * ini adalah fungsi untuk melihat status krtik saran
     * berdasarkan id yang di lempar
     */
    private function statusKritikSaran($id)
    {
        $data = DB::table('tbl_statuskritiksaran')
            ->where('id', $id)
            ->first();

        return $data;
    }

    // insert ke database kritik saran
    /**
     * Ini merupakan sebuah fungsi untuk melakukan sebuah
     * insert kritik dan saran dalam aplikasi dengan memberikan 
     * sebuah request diantarannya adalah
     * kategori, description, badge_id, area, is_anonymous,
     * status_kritiksaran, createdate
     */
    public function insertKritikSaran(Request $request)
    {


        /**
         * apabila $request->file_upload == null
         */
        if ($request->file_upload == null) {
            
            /**
             * insert ke tabel kritik saran 
             * ke tbl_riwayatkritiksaran
             */

            DB::beginTransaction();
            try {
                /**
                 * Apabila anonymous true
                 */
                if ($request->is_anonymous == 1) {
                    // insert ke tabel kritiksaran
                    $id_kritiksaran = DB::table('tbl_kritiksaran')
                        ->insertGetId([
                            "kategori"      => $request->id_kategori,
                            "description"   => $request->description,
                            "badge_id"      => $request->badge_id,
                            "area"          => $request->area ? $request->area  : null,
                            "is_anonymous"  => $request->is_anonymous,
                            "status_kritiksaran" => 2,
                            "createdate" => date("Y-m-d H:i:s")
                        ]);
                    if ($id_kritiksaran) {
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);

                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                    }
                }

                /**
                 * Apabila anonymous false
                 */
                if ($request->is_anonymous == 0) {
                    // insert ke tabel kritiksaran
                    $id_kritiksaran = DB::table('tbl_kritiksaran')
                        ->insertGetId([
                            "kategori"      => $request->id_kategori,
                            "description"   => $request->description,
                            "badge_id"      => $request->badge_id,
                            "area"          => $request->area ? $request->area  : null,
                            "is_anonymous"  => $request->is_anonymous,
                            "status_kritiksaran" => 2,
                            "createdate" => date("Y-m-d H:i:s")
                        ]);
                    if ($id_kritiksaran) {
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                    }
                }

                DB::commit();

                return response()->json([
                    "message" => "Response OK, Berhasil insert kritik dan saran",
                    "id_kritiksaran"    => $id_kritiksaran
                ]);
            } catch (\Throwable $th) {
                // dd($th);
                DB::rollBack();
                return response()->json([
                    "message" => "something went wrong",
                    "jalan_dulu" => $th->getMessage()
                ], 400);
            }
        }

        DB::beginTransaction();
        try {

            $request->validate([
                "id_kategori" => "required",
                "description" => "required",
                "file_upload" => "file|mimes:jpg,png,pdf",
                "badge_id"    => "required",
                // "area"        => "required",
                "is_anonymous" => "required"
            ]);


            /**
             * handle apabila tidak ada file upload yang di insert
             */
            if (!$request->has('file_upload')) {

                /**
                 * Apabila anonymous true
                 */
                if ($request->is_anonymous == 1) {
                    // insert ke tabel kritiksaran
                    $id_kritiksaran = DB::table('tbl_kritiksaran')
                        ->insertGetId([
                            "kategori"      => $request->id_kategori,
                            "description"   => $request->description,
                            "badge_id"      => $request->badge_id,
                            "area"          => $request->area ? $request->area  : null,
                            "is_anonymous"  => $request->is_anonymous,
                            "status_kritiksaran" => 2,
                            "createdate" => date("Y-m-d H:i:s")
                        ]);
                    if ($id_kritiksaran) {
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);

                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                    }
                }

                /**
                 * Apabila anonymous false
                 */
                if ($request->is_anonymous == 0) {
                    // insert ke tabel kritiksaran
                    $id_kritiksaran = DB::table('tbl_kritiksaran')
                        ->insertGetId([
                            "kategori"      => $request->id_kategori,
                            "description"   => $request->description,
                            "badge_id"      => $request->badge_id,
                            "area"          => $request->area ? $request->area  : null,
                            "is_anonymous"  => $request->is_anonymous,
                            "status_kritiksaran" => 2,
                            "createdate" => date("Y-m-d H:i:s")
                        ]);
                    if ($id_kritiksaran) {
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                        DB::table('tbl_riwayatkritiksaran')
                            ->insert([
                                "id_kritiksaran" => $id_kritiksaran,
                                "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                "createby"       => $request->badge_id,
                                "createdate"     => date("Y-m-d H:i:s")
                            ]);
                    }
                }
            }


            /**
             * handle apabila ada file upload yang di insert
             */
            if ($request->has('file_upload')) {

                /**
                 * cek apakah file ekstensi jpg atau png, untuk melakukan compress image
                 */
                $file_upload = $request->file('file_upload');
                $file_name   = time() . '.' . $file_upload->extension();

                if ($file_upload->extension() == 'jpg' || $file_upload->extension() == 'png') {

                    // dd("running");

                    $upload = $file_upload->move(public_path('kritiksaran'), $file_name);

                    // compress ukuran gambar
                    $image = imagecreatefromstring(file_get_contents(public_path('kritiksaran/' . $file_name)));
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $new_width = 800;
                    $new_height = ($height / $width) * $new_width;

                    $compressed_image = imagecreatetruecolor($new_width, $new_height);
                    imagecopyresampled($compressed_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                    imagejpeg($compressed_image, public_path('kritiksaran/' . $file_name), 80);
                    imagedestroy($image);
                    imagedestroy($compressed_image);
                } else {
                    $upload = $file_upload->move(public_path('kritiksaran'), $file_name);
                }

                if ($upload) {

                    /**
                     * Apabila anonymous true
                     */
                    if ($request->is_anonymous == 1) {
                        // insert ke tabel kritiksaran
                        $id_kritiksaran = DB::table('tbl_kritiksaran')
                            ->insertGetId([
                                "kategori"      => $request->id_kategori,
                                "description"   => $request->description,
                                "file_upload"   => $file_name,
                                "badge_id"      => $request->badge_id,
                                "area"          => $request->area ? $request->area : null,
                                "is_anonymous"  => $request->is_anonymous,
                                "status_kritiksaran" => 2,
                                "createdate" => date("Y-m-d H:i:s")
                            ]);
                        if ($id_kritiksaran) {
                            DB::table('tbl_riwayatkritiksaran')
                                ->insert([
                                    "id_kritiksaran" => $id_kritiksaran,
                                    "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                    "createby"       => $request->badge_id,
                                    "createdate"     => date("Y-m-d H:i:s")
                                ]);

                            DB::table('tbl_riwayatkritiksaran')
                                ->insert([
                                    "id_kritiksaran" => $id_kritiksaran,
                                    "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                    "createby"       => $request->badge_id,
                                    "createdate"     => date("Y-m-d H:i:s")
                                ]);
                        }
                    }

                    /**
                     * Apabila anonymous false
                     */
                    if ($request->is_anonymous == 0) {
                        // insert ke tabel kritiksaran
                        $id_kritiksaran = DB::table('tbl_kritiksaran')
                            ->insertGetId([
                                "kategori"      => $request->id_kategori,
                                "description"   => $request->description,
                                "file_upload"   => $file_name,
                                "badge_id"      => $request->badge_id,
                                "area"          => $request->area ? $request->area : null,
                                "is_anonymous"  => $request->is_anonymous,
                                "status_kritiksaran" => 2,
                                "createdate" => date("Y-m-d H:i:s")
                            ]);
                        if ($id_kritiksaran) {
                            DB::table('tbl_riwayatkritiksaran')
                                ->insert([
                                    "id_kritiksaran" => $id_kritiksaran,
                                    "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                                    "createby"       => $request->badge_id,
                                    "createdate"     => date("Y-m-d H:i:s")
                                ]);
                            DB::table('tbl_riwayatkritiksaran')
                                ->insert([
                                    "id_kritiksaran" => $id_kritiksaran,
                                    "status_riwayat" => 2, // defaultnya 1 diambil dari tbl_statuskritksaran
                                    "createby"       => $request->badge_id,
                                    "createdate"     => date("Y-m-d H:i:s")
                                ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil insert kritik dan saran",
                "id_kritiksaran"    => $id_kritiksaran
            ]);
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong",
                "sql_msg" => $th->getMessage()
            ], 400);
        }
    }

    // get detail kritik dan saran
    /**
     * ini adalah endpoint agar mobile ketika klik detail
     * dari kritik saran mendapatkan inforamsi kritik saran
     * dari kritik saran yang telah diberikan.
     */
    public function detailKritikSaran(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);

        /**
         * ambil data dari tabel kritiksaran
         * dimana id yg di lempar disini
         */
        $data = DB::table('tbl_kritiksaran')
            ->where("id", $request->id)
            ->first();

        $data->kategori = $this->getKategori($data->kategori);
        $data->status_riwayat_terakhir = $data->status_kritiksaran;
        $data->status_kritiksaran = $this->statusKritikSaran($data->status_kritiksaran) ?  $this->statusKritikSaran($data->status_kritiksaran)->stat_title : null;
        $data->file_upload = url(asset('kritiksaran/' . $data->file_upload));
        $data->riwayat_status = $this->getRiwayatKritikSaran($data->id);
        $data->riwayat_tanggapan = $this->getTanggapanKritikSaran($data->id);

        return response()->json([
            "message" => "Response OK",
            "data"    => $data
        ]);
    }

    // insert ke tabel tanggapan kritik saran
    /**
     * ini adalah endpoint untuk membuat create tanggapan
     * dimana request tanggapan nya ada 
     * id_kritiksaran, badge_id, respon
     * 
     */
    public function createTanggapan(Request $request)
    {
        $request->validate([
            "id_kritiksaran" => "required",
            "badge_id"       => "required",
            "respon"         => "required"
        ]);

        DB::beginTransaction();
        try {

            /**
             * lakukan pengecekan terlebih dahulu, apakah status kritik saran dengan id_kritiksaran
             * ada dan berapa id_statusnya_riwayat_terakhirnya
             */

            $data_kritiksaran = DB::table('tbl_kritiksaran')
                ->where('id', $request->id_kritiksaran)
                ->first();
            if ($data_kritiksaran) {
                $status_kritik_saran = $data_kritiksaran->status_kritiksaran;
                if ($status_kritik_saran == 4) {
                    DB::rollBack();
                    return response()->json([
                        "message" => "Tidak dapat memberi tanggapan karena Pengajuan Kritik dan Saran Telah Selesai!",
                    ], 400);
                }
            }

            // dd($data_kritiksaran);
            /**
             * lalu masukkan ke tabel tanggapankritiksaran
             */
            DB::table('tbl_tanggapankritiksaran')
                ->insert([
                    "id_kritik" => $request->id_kritiksaran,
                    "respon"    => $request->respon,
                    "badge_id"  => $request->badge_id,
                    "waktu"     => date("Y-m-d H:i:s"),
                    "is_anonymous" => $data_kritiksaran->is_anonymous
                ]);

            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil insert data tanggapan",
                "data"    => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong",
            ], 400);
        }
    }
}
