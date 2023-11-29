<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class MmsController extends Controller
{
    // get all brand smartphone
    /**
     * pada tampilan mobile ada sebuah tampilan dimana
     * pengguna dilihatkan hasil dari dropwdown
     * dan ini merupakan fungsi untuk mendapatkan merk merk
     * smartphone dari tbl_vlookup
     */
    public function getBrandSmartphone()
    {
        $query = "SELECT * FROM tbl_vlookup WHERE category = 'BRD'";
        $data = DB::select($query);

        return response()->json([
            'message' => 'Success get all brand for SmartPhone',
            'data' => $data,
        ]);
    }

    // list pengajuan mms saya
    /**
     * ini merupakan sebuah fungsi untuk mendapatkan
     * tampilan dari mms atau perangkat karyawan
     * mobile yang telah terdaftar (dapat di scan)
     * dan diambil dari tbl_mms
     */
    public function listMMS(Request $request)
    {
        $request->validate([
            'badge_id' => 'required',
        ]);

        // query sql
        $query = "SELECT a.id, merek_hp, jenis_permohonan, tipe_hp, waktu_pengajuan, status_pendaftaran_mms FROM tbl_mms a
                        JOIN tbl_statusmms b ON a.status_pendaftaran_mms = b.id
                        WHERE badge_id = '$request->badge_id' ";
        $data = DB::select($query);

        // insialisasi tanggal today dan kemarin
        $hari_ini = date('Y-m-d', time());
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        // Cek kategori  permohonan
        foreach ($data as $key => $item) {
            if ($item->merek_hp == null) {
                $item->merek_hp = '-';
            }

            if ($item->merek_hp != null) {
                $item->merek_hp = $this->getBrand($item->merek_hp);
            }

            if ($item->jenis_permohonan == null) {
                $item->jenis_permohonan = 1;
            }

            if ($item->jenis_permohonan == 1) {
                $item->jenis_permohonan = 'Karyawan baru';
            }

            if ($item->jenis_permohonan == 3) {
                $item->jenis_permohonan = 'Penambahan Hp Baru';
            }

            $itemTime = strtotime($item->waktu_pengajuan);
            $itemDate = date('Y-m-d', $itemTime);

            if ($itemDate == $hari_ini) {
                $item->waktu_pengajuan = 'Hari Ini, ' . date('H:i', $itemTime);
            } elseif ($itemDate == $kemarin) {
                $item->waktu_pengajuan = 'Kemarin, ' . date('H:i', $itemTime);
            } else {
                $item->waktu_pengajuan = date('d-m-Y, H:i', $itemTime);
            }

            /**
             * apabila status id nya adalah 4 atau id nya adalah 9
             */
            if ($item->status_pendaftaran_mms == 4) {
                $item->status_pendaftaran_mms = 2;
            }

            if ($item->status_pendaftaran_mms == 9) {
                $item->status_pendaftaran_mms = 7;
            }

            $item->status = $this->getTitle($item->status_pendaftaran_mms);
        }

        return response()->json([
            'message' => 'SUCCESS GET MY LIST MMS',
            'data' => $data,
        ]);
    }

    // insert tambah HP baru
    /**
     * ini adalah fungsi dimana karyawan baru
     * ataupun lama ingin mendaftarkan perangkat
     * smartphone nya di aplikasi mysatnusa
     * agar bisa di scan oleh aplikasi e-kios
     */
    public function pengajuan(Request $request)
    {
        $request->validate([
            'tipe_permohonan' => 'required',
            'merek_hp' => 'required',
            'tipe_hp' => 'required',
            'imei1' => 'required',
            'badge_id' => 'required',
            'uuid' => 'required',
            'os' => 'required',
            'img_depan' => 'file|mimes:jpg,png|required',
            'img_blkng' => 'file|mimes:jpg,png|required',
        ]);

        // cek dahulu imei1 dan imei2, apakah ada di database atau enggak ?
        $query = "SELECT imei1, imei2 FROM tbl_mms WHERE (imei1 = '$request->imei1' OR imei2 ='$request->imei2') AND is_active = 1";
        $check_imei = DB::select($query);
        // apabila ada imei yang di input user
        if (count($check_imei) > 0) {
            return response()->json(
                [
                    'message' => 'IMEI YANG DI INPUT SUDAH TERDAFTAR DI DATABASE KAMI',
                ],
                400,
            );
        }

        // cek lagi UUID yang dikirim user, apakah ada duplikasi atau enggak
        $query = "SELECT UUID FROM tbl_mms WHERE UUID = '$request->uuid' AND is_active = 1";
        $check_uuid = DB::select($query);
        if (count($check_uuid) > 0) {
            return response()->json(
                [
                    'message' => 'UUID YANG DI INPUT SUDAH TERDAFTAR DI DATABASE KAMI',
                ],
                400,
            );
        }

        $player_id = $request->player_id;

        $img_depan = $request->file('img_depan');
        $img_blkng = $request->file('img_blkng');

        $img_depan = $this->convertImgToBase64($img_depan);
        $img_blkng = $this->convertImgToBase64($img_blkng);

        switch ($request->tipe_permohonan) {
            // apabila karyawan baru
            case '1':
                // cek apakah benar dia karyawan baru
                $query = "SELECT COUNT(id) AS jumlah FROM tbl_mms WHERE badge_id = '$request->badge_id'";
                $jumlah = DB::select($query);
                $jumlah = $jumlah[0]->jumlah;

                if ($jumlah > 0) {
                    return response()->json(
                        [
                            'message' => 'Kami Mendeteksi Bahwa Kamu Pernah Mendaftar MMS, Gunakan Fitur Tambah HP Baru Untuk Menambah HP Baru',
                        ],
                        400,
                    );
                }

                DB::beginTransaction();
                try {
                    $idMms = DB::table('tbl_mms')->insertGetId([
                        'badge_id' => $request->badge_id,
                        'uuid' => $request->uuid,
                        'jenis_permohonan' => $request->tipe_permohonan,
                        'merek_hp' => $request->merek_hp,
                        'os' => $request->os,
                        'tipe_hp' => $request->tipe_hp,
                        'img_dpn' => $img_depan,
                        'img_blk' => $img_blkng,
                        'versi_aplikasi' => $request->versi_aplikasi,
                        'imei1' => $request->imei1,
                        'imei2' => $request->imei2,
                        'status_pendaftaran_mms' => 2,
                        'waktu_pengajuan' => date('Y-m-d H:i:s'),
                        'is_new_uuid' => 1,
                        'device_type' => 'Mobile',
                        'createby' => $request->badge_id,
                        'createdate' => date('Y-m-d H:i:s'),
                        'player_id' => $player_id,
                    ]);

                    for ($i = 1; $i <= 2; $i++) {
                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => $i,
                            'createby' => $request->badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    DB::commit();

                    $checkImei = $this->checkImei($request->imei1, $request->imei2, $idMms, $request->badge_id);

                    return response()->json([
                        'message' => 'Response OK, Berhasil insert HP Anda Sebagai Karyawan Baru',
                        'id_mms' => $idMms,
                    ]);
                } catch (\Throwable $th) {
                    dd($th);
                    return response()->json(
                        [
                            'message' => 'Something went wrong',
                        ],
                        400,
                    );
                }

                break;

            // apabila penambahan hp baru
            case '3':
                DB::beginTransaction();
                try {
                    $idMms = DB::table('tbl_mms')->insertGetId([
                        'badge_id' => $request->badge_id,
                        'uuid' => $request->uuid,
                        'jenis_permohonan' => $request->tipe_permohonan,
                        'merek_hp' => $request->merek_hp,
                        'os' => $request->os,
                        'tipe_hp' => $request->tipe_hp,
                        'img_dpn' => $img_depan,
                        'img_blk' => $img_blkng,
                        'versi_aplikasi' => $request->versi_aplikasi,
                        'status_pendaftaran_mms' => 2,
                        'waktu_pengajuan' => date('Y-m-d H:i:s'),
                        'is_new_uuid' => 1,
                        'device_type' => 'Mobile',
                        'imei1' => $request->imei1,
                        'imei2' => $request->imei2,
                        'createby' => $request->badge_id,
                        'createdate' => date('Y-m-d H:i:s'),
                        'player_id' => $player_id,
                    ]);

                    for ($i = 1; $i <= 2; $i++) {
                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => $i,
                            'createby' => $request->badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    DB::commit();

                    $checkImei = $this->checkImei($request->imei1, $request->imei2, $idMms, $request->badge_id);

                    return response()->json([
                        'message' => 'Response OK, Berhasil insert HP Baru Anda',
                        'id_mms' => $idMms,
                    ]);
                } catch (\Throwable $th) {
                    dd($th);
                    return response()->json(
                        [
                            'message' => 'Something went wrong',
                        ],
                        400,
                    );
                }

                break;

            default:
                # code...
                break;
        }
    }

    private function checkImei($imei1, $imei2, $idMms, $badge_id)
    {
        // dd($request->all());
        if (empty($imei1) || empty($imei2)) {
            return false;
        }

        DB::beginTransaction();
        try {
            // CEK IMEI 1 DI MI11 (XIAOMI)
            $client = new Client();
            $response = $client->post('http://snws07:8000/api/MES/Ext/IMEIVerification?plant=MI11&IMEI=' . $imei1);
            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $data = json_decode($response->getBody(), true);

                if ($data['MESSAGETYPE'] == 'S') {
                    $isShipData = json_decode($data['DATA']);

                    $dataLog = [
                        'imei' => $imei1,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                    if ($isShipData->is_shipped == null || $isShipData->is_shipped == '' || $isShipData->is_shipped == false) {
                        DB::table('tbl_mms')
                            ->where('imei1', $imei1)
                            ->update(['status_pendaftaran_mms' => 13, 'status_imei' => 2]);
                            
                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => '13',
                            'createby' => $badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                        
                    }
                } else {
                    DB::table('tbl_mms')
                        ->where('imei1', $imei1)
                        ->update(['status_imei' => 1]);
                    $dataLog = [
                        'imei' => $imei1,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Gagal request ke api - ' . date('d M Y H:i'),
                ]);
            }

            // CEK IMEI 1 DI IS13 (ASUS)
            $client = new Client();
            $response = $client->post('http://snws07:8000/api/MES/Ext/IMEIVerification?plant=IS13&IMEI=' . $imei1);
            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $data = json_decode($response->getBody(), true);

                if ($data['MESSAGETYPE'] == 'S') {
                    $isShipData = json_decode($data['DATA']);
                    $dataLog = [
                        'imei' => $imei1,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);

                    if ($isShipData->is_shipped == null || $isShipData->is_shipped == '' || $isShipData->is_shipped == false) {
                        DB::table('tbl_mms')
                            ->where('imei1', $imei1)
                            ->update(['status_pendaftaran_mms' => 13, 'status_imei' => 2]);
                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => '13',
                            'createby' => $badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                        return response()->json([
                            'status' => 200,
                            'message' => 'Data imei telah diupdate - ' . date('d M Y H:i'),
                        ]);
                    }
                } else {
                    DB::table('tbl_mms')
                        ->where('imei1', $imei1)
                        ->update(['status_imei' => 1]);
                    $dataLog = [
                        'imei' => $imei1,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Gagal request ke api - ' . date('d M Y H:i'),
                ]);
            }

            /**
             * CEK IMEI 2
             */

            // CEK IMEI 2 DI MI11 (XIAOMI)
            $client = new Client();
            $response = $client->post('http://snws07:8000/api/MES/Ext/IMEIVerification?plant=MI11&IMEI=' . $imei2);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $data = json_decode($response->getBody(), true);

                if ($data['MESSAGETYPE'] == 'S') {
                    $isShipData = json_decode($data['DATA']);

                    $dataLog = [
                        'imei' => $imei2,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);

                    if ($isShipData->is_shipped == null || $isShipData->is_shipped == '' || $isShipData->is_shipped == false) {
                        DB::table('tbl_mms')
                            ->where('imei2', $imei2)
                            ->update(['status_pendaftaran_mms' => 13, 'status_imei' => 2]);

                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => '13',
                            'createby' => $badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                        return response()->json([
                            'status' => 200,
                            'message' => 'Data imei telah diupdate - ' . date('d M Y H:i'),
                        ]);
                    }
                } else {
                    DB::table('tbl_mms')
                        ->where('imei2', $imei2)
                        ->update(['status_imei' => 1]);
                    $dataLog = [
                        'imei' => $imei2,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Gagal request ke api - ' . date('d M Y H:i'),
                ]);
            }

            // CEK IMEI 2 DI IS13 (ASUS)
            $client = new Client();
            $response = $client->post('http://snws07:8000/api/MES/Ext/IMEIVerification?plant=IS13&IMEI=' . $imei2);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $data = json_decode($response->getBody(), true);

                if ($data['MESSAGETYPE'] == 'S') {
                    $isShipData = json_decode($data['DATA']);

                    $dataLog = [
                        'imei' => $row->imei,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                    if ($isShipData->is_shipped == null || $isShipData->is_shipped == '' || $isShipData->is_shipped == false) {
                        DB::table('tbl_mms')
                            ->where('imei2', $imei2)
                            ->update(['status_pendaftaran_mms' => 13, 'status_imei' => 2]);
                           
                        DB::table('tbl_riwayatstatusmms')->insert([
                            'id_mms' => $idMms,
                            'status_mms' => '13',
                            'createby' => $badge_id,
                            'createdate' => date('Y-m-d H:i:s'),
                        ]);
                        return response()->json([
                            'status' => 200,
                            'message' => 'Data imei telah diupdate - ' . date('d M Y H:i'),
                        ]);
                    }
                } else {
                    DB::table('tbl_mms')
                        ->where('imei2', $imei2)
                        ->update(['status_imei' => 1]);
                    $dataLog = [
                        'imei' => $imei2,
                        'message_type' => $data['MESSAGETYPE'],
                        'message' => $data['MESSAGE'],
                        'data' => $data['DATA'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    DB::table('tbl_logcheckimei')->insert($dataLog);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Gagal request ke api - ' . date('d M Y H:i'),
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Data imei telah diupdate - ' . date('d M Y H:i'),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 401,
                'message' => 'Gagal untuk mengupdate data ' . $ex->getMessage(),
            ]);
        }
    }

    /**
     * Convert gambar jadi base64
     * merupakan sebuah fungsi yang dapat digunakan
     * untuk melakukan konversi image biasa ke
     * base64 format, untuk disimpan kedalam
     * dataabase
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

        imagecopyresampled($compressedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

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
     * get name brand laptop
     * ini merupakan sebuah fungsi untuk mendapatkan
     * dari sisi mobile
     */
    private function getBrand($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }

    /**
     * get detail mms
     */
    public function detailMMS(Request $request)
    {
        $request->validate([
            'id_mms' => 'required',
        ]);

        $query = "SELECT jenis_permohonan, uuid, merek_hp, tipe_hp, imei1, imei2, waktu_pengajuan,  status_pendaftaran_mms AS status_terakhir, img_dpn, img_blk
                        FROM tbl_mms a
                        JOIN tbl_statusmms b ON a.status_pendaftaran_mms = b.id
                        WHERE a.id = '$request->id_mms'";
        $data = DB::select($query);
        $data[0]->merek_hp = $this->getBrand($data[0]->merek_hp);

        if ($data[0]->jenis_permohonan == '3') {
            $data[0]->jenis_permohonan = 'Penambahan HP Baru';
        }

        if ($data[0]->jenis_permohonan == '1') {
            $data[0]->jenis_permohonan = 'Karyawan Baru';
        }

        $data[0]->riwayat_status = $this->getRiwayatMMS($request->id_mms, $data[0]->status_terakhir);
        $data[0]->riwayat_tanggapan = $this->getRiwayatTanggapan($request->id_mms);

        if ($data[0]->status_terakhir == 4) {
            $data[0]->status_terakhir = 2;
        }

        if ($data[0]->status_terakhir == 9) {
            $data[0]->status_terakhir = 7;
        }

        $data[0]->status = $this->getTitle($data[0]->status_terakhir);

        return response()->json([
            'message' => 'RESPONSE DETAIL OK',
            'data' => $data ? $data[0] : [],
        ]);
    }

    /**
     * get mms riwayat
     */
    public function getRiwayatMMS($id_mms, $id_status_terakhir)
    {
        $query = "SELECT b.id AS status_riwayat_id, stat_title, stat_desc, createdate FROM tbl_riwayatstatusmms a
                    JOIN tbl_statusmms b ON a.status_mms = b.id
                    WHERE id_mms = '$id_mms' ORDER BY status_riwayat_id ASC";
        $data = DB::select($query);

        $data_baru = [];

        foreach ($data as $key => $item) {
            if ($item->status_riwayat_id != 4 && $item->status_riwayat_id != 9) {
                array_push($data_baru, $item);
            }
        }

        foreach ($data_baru as $key => $item) {
            if ($item->status_riwayat_id == 2 || $item->status_riwayat_id == 4 || $item->status_riwayat_id == 7 || $item->status_riwayat_id == 9) {
                $item->stat_desc = '';
            }
        }

        // dd($data_baru);

        if (COUNT($data_baru) > 0) {
            return $data_baru;
        }
        return $data;
    }

    /**
     * get riwayat tanggal
     */
    public function getRiwayatTanggapan($id_mms)
    {
        $query = "SELECT a.respon, a.waktu, b.fullname, b.position_code, b.img_user
        FROM tbl_tanggapanmms a
        JOIN tbl_karyawan b ON a.badge_id = b.badge_id
        WHERE a.id_mms = '$id_mms'";

        $data = DB::select($query);

        return $data ? $data : [];
    }

    /**
     * beri tanggapan mms
     */
    public function beriTanggapan(Request $request)
    {
        /**
         * lakukan pengecekan di tbl_lms
         * apabila id_lms yang dihit ada, cek status lms
         */
        $id_mms = $request->id_mms;
        $respon = $request->respon;
        $badge_id = $request->badge_id;

        $query_check = "SELECT status_pendaftaran_mms FROM tbl_mms WHERE id = '$id_mms'";
        $data = DB::select($query_check);

        if ($data) {
            $status_mms = $data[0]->status_pendaftaran_mms;
            /**
             * tanggapan tidak bisa diberika apabila status lms >= 15
             */
            if ($status_mms >= 12) {
                return response()->json(
                    [
                        'message' => 'Tidak boleh memberikan tanggapan',
                    ],
                    400,
                );
            }

            /**
             * apabila tidak maka lakukan insert ke tabel tanggapan lms
             */
            DB::beginTransaction();
            try {
                DB::table('tbl_tanggapanmms')->insert([
                    'id_mms' => $id_mms,
                    'respon' => $respon,
                    'badge_id' => $badge_id,
                    'waktu' => date('Y-m-d H:i:s'),
                ]);
                DB::commit();

                return response()->json([
                    'message' => 'Response OK, Berhasil insert Tanggapan MMS',
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(
                    [
                        'message' => 'Gagal Menyimpan Tanggapan',
                    ],
                    400,
                );
            }
        }
    }

    /**
     * function untuk get title status mms,
     * berguna ketika di halaman list mmms
     */
    public function getTitle($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statusmms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }
}
