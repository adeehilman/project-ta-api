<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LmsController extends Controller
{
    /**
     * untuk handle pengajuan
     */
    public function insertPengajuan(Request $request)
    {

        $request->validate([
            "merek_laptop" => 'required',
            "tipe_laptop"  => 'required',
            "asset_number" => 'required',
            "durasi_pemakaian" => 'required',
            "badge_id" => 'required',
            "alasan_id" => 'required',
            "img_depan" => "file|mimes:jpg,png|required",
            "img_blkng" => "file|mimes:jpg,png|required",
        ]);


        /**
         * lakukan pengecekan laptop dengan asset number dan status is_active nya = 1
         * apabila memenuhi kondisi diatas maka laptop masih dipakai orang
         */
        $query_cek_peminjaman = "SELECT * FROM tbl_lms WHERE asset_number = '$request->asset_number' AND is_active = '1'";
        $data_peminjaman = DB::select($query_cek_peminjaman);

        $jumlah = COUNT($data_peminjaman);
        if ($jumlah > 0) {
            return response()->json([
                "message" => "Laptop yang kamu inputkan masih dalam status peminjaman, silahkan hubungi HRD untuk info lebih lanjut"
            ], 400);
        }


        $img_depan = $request->file('img_depan');
        $img_blkng = $request->file('img_blkng');

        $img_depan = $this->convertImgToBase64($img_depan);
        $img_blkng = $this->convertImgToBase64($img_blkng);

        /**
         * apabila durasi tidak sama dengan untlimated maka wajib memiliki start date dan end date
         * dimana perlu melakukan pengecekan 
         */
        if ($request->durasi_pemakaian == '57') {
            // cek request start date dan end date
            if (!request()->has('start_date') || !request()->has('start_date')) {
                return response()->json([
                    "message" => "Tidak bisa melakukan pengajuan, mohon untuk mengisi field start date dan end date"
                ], 400);
            }
            $startDate =  $request->start_date;
            $endDate   =  $request->end_date;
        }

        /**
         * apabila durasi untlimated
         */
        if($request->durasi_pemakaian == '58'){
            $startDate = null;
            $endDate   = null;
        }

        DB::beginTransaction();
        try {
            $idLms = DB::table('tbl_lms')->insertGetId([
                "badge_id"      => $request->badge_id,
                "tipe_laptop"   => $request->tipe_laptop,
                "asset_number"  => $request->asset_number,
                "durasi"        => $request->durasi_pemakaian,
                "brand"         => $request->merek_laptop,
                "alasan"        => $request->alasan_id,
                "desc_alasan"   => $request->alasan_desc,
                "status_pendaftaran_lms" => 2,
                "is_active" => 1,
                "img_dpn" => $img_depan,
                "img_blk" => $img_blkng,
                "start_date" => $startDate,
                "end_date" => $endDate,
                "tanggal_pengajuan" => date("Y-m-d H:i:s"),
                "createby" => $request->badge,
                "player_id" => $request->player_id
            ]);

            for ($i = 1; $i <= 2; $i++) {
                DB::table('tbl_riwayatstatuslms')
                    ->insert([
                        "id_lms" => $idLms,
                        "status_lms" => $i,
                        "createby" => $request->badge_id,
                        "createdate" => date("Y-m-d H:i:s")
                    ]);
            }

            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil insert LMS",
                "idLms"   => $idLms
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => "something went wrong"
            ], 400);
        }
    }

    /**
     * Untuk handle project brand laptop
     */
    public function getBrandLaptop()
    {
        $data = DB::select("SELECT id_vlookup as id, name_vlookup as nama_brand FROM tbl_vlookup WHERE category = 'BRL'");
        return response()->json([
            "message" => "Success get all brand for PC/Laptop",
            "data" => $data
        ]);
    }

    /**
     * Convert gambar jadi base64
     */
    private function convertImgToBase64($img)
    {
        $maxWidth = 800;
        $maxHeight = 800;

        // Dapatkan informasi gambar asli
        $imageInfo = getimagesize($img->getPathname());
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mime = $imageInfo['mime'];

        // Hitung proporsi gambar yang perlu dikompres
        $aspectRatio = $originalWidth / $originalHeight;
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            if ($maxWidth / $maxHeight > $aspectRatio) {
                $newWidth = $maxHeight * $aspectRatio;
                $newHeight = $maxHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $aspectRatio;
            }
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Buat gambar terkompres dalam sumber daya memori
        $compressedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Cek tipe gambar dan buat gambar sumber dari string biner
        if ($mime === 'image/jpeg') {
            $image = imagecreatefromjpeg($img->getPathname());
        } elseif ($mime === 'image/png') {
            $image = imagecreatefrompng($img->getPathname());
        } else {
            // Tambahkan logika untuk format gambar lain jika diperlukan
            return null;
        }

        imagecopyresampled(
            $compressedImage,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Simpan gambar terkompres dalam sumber daya memori ke variabel
        ob_start();
        imagejpeg($compressedImage, null, 75);
        $compressedData = ob_get_clean();

        // Ubah gambar terkompres menjadi base64
        $base64Image = 'data:' . $mime . ';base64,' . base64_encode($compressedData);

        // Hapus sumber daya memori yang tidak diperlukan
        imagedestroy($compressedImage);
        imagedestroy($image);

        return $base64Image;
    }


    /**
     * get all list brand laptop
     */
    public function listLms(Request $request)
    {
        $request->validate([
            "badge_id" => "required"
        ]);

        // query
        $query = "SELECT a.id, brand, tipe_laptop, tanggal_pengajuan, alasan, durasi, start_date, end_date, status_pendaftaran_lms FROM tbl_lms a
                        JOIN tbl_statuslms b ON a.status_pendaftaran_lms = b.id
                        WHERE badge_id = '$request->badge_id'";
        $data = DB::select($query);

        // insialisasi tanggal today dan kemarin
        $hari_ini = date("Y-m-d", time());
        $kemarin  = date("Y-m-d", strtotime("-1 day"));

        foreach ($data as $key => $item) {

            if ($item->brand == null) {
                $item->brand = '-';
            }

            if ($item->brand != null) {
                $item->brand = $this->getBrand($item->brand);
            }

            if ($item->alasan == 61) {
                $item->alasan = 'Untuk Bekerja';
            }

            if ($item->alasan == 62) {
                $item->alasan = 'Alasan Lainnya';
            }

            $itemTime = strtotime($item->tanggal_pengajuan);
            $itemDate = date("Y-m-d", $itemTime);

            if ($itemDate == $hari_ini) {
                $item->tanggal_pengajuan = "Hari Ini, " . date("H:i", $itemTime);
            } else if ($itemDate == $kemarin) {
                $item->tanggal_pengajuan = "Kemarin, " . date("H:i", $itemTime);
            } else {
                $item->tanggal_pengajuan = date("d-m-Y, H:i", $itemTime);
            }

            /**
             * durasi pemakaian
             */
            $item->durasi_pemakaian = "Unlimated Duration";
            if ($item->durasi == 57) {
                $item->durasi_pemakaian = $item->start_date . ' s/d ' . $item->end_date;
            }

            /**
             * apabila status id nya adalah 4 atau id nya adalah 9
             */
            if ($item->status_pendaftaran_lms == 4) {
                $item->status_pendaftaran_lms = 2;
            }

            if ($item->status_pendaftaran_lms == 9) {
                $item->status_pendaftaran_lms = 7;
            }


            $item->status = $this->getTitle($item->status_pendaftaran_lms);
        }

        return response()->json([
            "message" => "SUCCESS GET MY LIST LMS",
            "data" => $data
        ]);
    }

    /**
     * get detail list lms
     */
    public function detailLMS(Request $request)
    {
        $request->validate([
            "id_lms" => "required"
        ]);

        $query = "SELECT a.id, brand, tipe_laptop, alasan, tanggal_pengajuan, stat_title AS status, status_pendaftaran_lms as status_terakhir, durasi, start_date, end_date, img_dpn, img_blk
                        FROM tbl_lms a 
                        JOIN tbl_statuslms b ON a.status_pendaftaran_lms = b.id	
                        WHERE a.id = '$request->id_lms'";
        $data = DB::select($query);

        $data[0]->brand = $this->getBrand($data[0]->brand);

        if ($data[0]->tanggal_pengajuan  == null) {
            $data[0]->tanggal_pengajuan = "-";
        }

        if ($data[0]->alasan  == null) {
            $data[0]->alasan = "Untuk Bekerja";
        }

        if ($data[0]->alasan  == 61) {
            $data[0]->alasan = "Untuk Bekerja";
        }

        if ($data[0]->alasan  == 62) {
            $data[0]->alasan = "Alasan Lainnya";
        }

        // if ($data[0]->durasi  == 57) {
        //     $data[0]->durasi = "Jangka Pendek (Durasi : > 1 Minggu)";
        // }

        // if ($data[0]->durasi  == 58) {
        //     $data[0]->durasi = "Worker-Working Purpose (Unlimited Duration)";
        // }

        $data[0]->riwayat_status = $this->getRiwayatStatusLMS($request->id_lms);
        $data[0]->riwayat_tanggapan = $this->getRiwayatTanggapanLMS($request->id_lms);

        /**
         * durasi pemakaian
         */
        
        if ($data[0]->durasi == '57') {
            $data[0]->durasi = $data[0]->start_date . ' s/d ' . $data[0]->end_date;
        }
        if ($data[0]->durasi == '58') {
            $data[0]->durasi = "Unlimated Duration";
        }

        if ($data[0]->status_terakhir == 4) {
            $data[0]->status_terakhir = 2;
        }

        if ($data[0]->status_terakhir == 9) {
            $data[0]->status_terakhir = 7;
        }

        $data[0]->status = $this->getTitle($data[0]->status_terakhir);

        return response()->json([
            "message" => "RESPONSE DETAIL OK",
            "data"    => $data ? $data[0] : []
        ]);
    }

    /**
     * get name brand laptop
     */
    private function getBrand($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }

    /**
     * get riwaywat status lms
     */
    private function getRiwayatStatusLMS($id_lms)
    {
        $query = "SELECT b.id as status_riwayat_id, stat_title, stat_desc, createdate FROM tbl_riwayatstatuslms a
        JOIN tbl_statuslms b ON a.status_lms = b.id
        WHERE id_lms = '$id_lms' ORDER BY status_riwayat_id ASC";
        $data = DB::select($query);

        $data_baru = [];

        foreach ($data as $key => $item) {
            if (
                $item->status_riwayat_id != 4 &&
                $item->status_riwayat_id != 9
            ) {

                array_push($data_baru, $item);
            }
        }

        foreach ($data_baru as $key => $item) {
            if(  
                $item->status_riwayat_id == 4 ||
                $item->status_riwayat_id == 9 
           ){
                $item->stat_desc = '';
           }
        }

        return $data ? $data_baru : [];
    }

    /**
     * untuk menambah tanggapan 
     */
    public function beriTanggapan(Request $request)
    {
        /**
         * lakukan pengecekan di tbl_lms
         * apabila id_lms yang dihit ada, cek status lms
         */
        $id_lms = $request->id_lms;
        $respon = $request->respon;
        $badge_id = $request->badge_id;

        $query_check = "SELECT status_pendaftaran_lms FROM tbl_lms WHERE id = '$id_lms'";
        $data = DB::select($query_check);


        if ($data) {
            $status_lms = $data[0]->status_pendaftaran_lms;
            /** 
             * tanggapan tidak bisa diberika apabila status lms >= 15
             */
            if ($status_lms >= 15) {
                return response()->json([
                    "message" => "Tidak boleh memberikan tanggapan"
                ], 400);
            }

            /**
             * apabila tidak maka lakukan insert ke tabel tanggapan lms
             */
            DB::beginTransaction();
            try {
                DB::table('tbl_tanggapanlms')
                    ->insert([
                        "id_lms" => $id_lms,
                        "respon" => $respon,
                        "badge_id" => $badge_id,
                        "waktu" => date("Y-m-d H:i:s")
                    ]);
                DB::commit();

                return response()->json([
                    "message" => "Response OK, Berhasil insert Tanggapan LMS",
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    "message" => "Gagal Menyimpan Tanggapan"
                ], 400);
            }
        }
    }

    /**
     * untuk get riwayat tanggapan lms
     */
    public function getRiwayatTanggapanLMS($id_lms)
    {
        $query = "SELECT a.respon, a.waktu, b.fullname, b.position_code, b.img_user
        FROM tbl_tanggapanlms a
        JOIN tbl_karyawan b ON a.badge_id = b.badge_id
        WHERE a.id_lms = '$id_lms'";

        $data = DB::select($query);

        return $data ? $data : [];
    }

    /**
     * function untuk get title status lms,
     * berguna ketika di halaman list lms
     */
    public function getTitle($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statuslms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }
}
