<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RiwayatController extends Controller
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

        $request->validate([
            'badge_id' => 'required',
        ]);

        $badge_id = $request->badge_id;

        $array_mms = $this->listMMs($badge_id);
        $array_lms = $this->listLMS($badge_id);
        // Menggabungkan kedua array
        $mergedArray = array_merge($array_mms, $array_lms);

        // Mengurutkan array berdasarkan waktu paling baru
        // usort($mergedArray, function ($a, $b) {
        //     return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        // });
        return response()->json([
            'message' => 'Success get all history',
            'data' => $mergedArray,
        ]);
    }

    /**
     * MMS Riwayat Pengajuan
     **/
    public function listMMS($badge_id)
    {
        // $badgeId = $request->badge_id;
        // query sql
        $query = "SELECT a.id, merek_hp, jenis_permohonan, tipe_hp, waktu_pengajuan, status_pendaftaran_mms FROM tbl_mms a
                        JOIN tbl_statusmms b ON a.status_pendaftaran_mms = b.id
                        WHERE badge_id = '$badge_id' ";
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
        
        // Buat array baru untuk menampung hasil query
        $dataarray = [];

        // Proses hasil query dan isi array $data
        foreach ($data as $item) {
            // Lakukan semua proses pengolahan data seperti yang telah Anda lakukan sebelumnya

            $processedItem = [
                'title' => 'Pengajuan HP',
                "id"=> $item->id,
                "merek_hp"=> $item->merek_hp,
                "jenis_permohonan"=> $item->jenis_permohonan,
                "tipe_hp"=> $item->tipe_hp,
                "waktu_pengajuan"=> $item->waktu_pengajuan,
                "status_pendaftaran_mms"=> $item->status_pendaftaran_mms,
                "status"=> $item->status
            ];

            // Tambahkan hasil proses ke dalam array $data
            $dataarray[] = $processedItem;
        }
        return $dataarray;
    }

    public function getBrandSmartphone()
    {
        $query = "SELECT * FROM tbl_vlookup WHERE category = 'BRD'";
        $data = DB::select($query);

        return response()->json([
            'message' => 'Success get all brand for SmartPhone',
            'data' => $data,
        ]);
    }
    private function getBrand($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }
    public function getTitle($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statusmms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }

    /**
     * LMS RIwayat Pengajuan
     */
    public function listLms($badge_id)
    {
        // $request->validate([
        //     "badge_id" => "required"
        // ]);

        // query
        $query = "SELECT a.id, brand, tipe_laptop, tanggal_pengajuan, alasan, durasi, start_date, end_date, status_pendaftaran_lms FROM tbl_lms a
                        JOIN tbl_statuslms b ON a.status_pendaftaran_lms = b.id
                        WHERE badge_id = '$badge_id'";
        $data = DB::select($query);

        // insialisasi tanggal today dan kemarin
        $hari_ini = date('Y-m-d', time());
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        foreach ($data as $key => $item) {
            if ($item->brand == null) {
                $item->brand = '-';
            }

            if ($item->brand != null) {
                $item->brand = $this->getBrandLms($item->brand);
            }

            if ($item->alasan == 61) {
                $item->alasan = 'Untuk Bekerja';
            }

            if ($item->alasan == 62) {
                $item->alasan = 'Alasan Lainnya';
            }

            $itemTime = strtotime($item->tanggal_pengajuan);
            $itemDate = date('Y-m-d', $itemTime);

            if ($itemDate == $hari_ini) {
                $item->tanggal_pengajuan = 'Hari Ini, ' . date('H:i', $itemTime);
            } elseif ($itemDate == $kemarin) {
                $item->tanggal_pengajuan = 'Kemarin, ' . date('H:i', $itemTime);
            } else {
                $item->tanggal_pengajuan = date('d-m-Y, H:i', $itemTime);
            }

            /**
             * durasi pemakaian
             */
            $item->durasi_pemakaian = 'Unlimated Duration';
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

            $item->status = $this->getTitleLms($item->status_pendaftaran_lms);
        }

        // Buat array baru untuk menampung hasil query
        $dataarray = [];

        // Proses hasil query dan isi array $data
        foreach ($data as $item) {
            // Lakukan semua proses pengolahan data seperti yang telah Anda lakukan sebelumnya

            $processedItem = [
                'title' => 'Pengajuan Laptop',
                'id' => $item->id,
                'brand' => $item->brand,
                'tipe_laptop' => $item->tipe_laptop,
                'tanggal_pengajuan' => $item->tanggal_pengajuan,
                'alasan' => $item->alasan,
                'durasi' => $item->durasi,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status_pendaftaran_lms' => $item->status_pendaftaran_lms,
                'durasi_pemakaian' => $item->durasi_pemakaian,
                'status' => $item->status,
            ];

            // Tambahkan hasil proses ke dalam array $data
            $dataarray[] = $processedItem;
        }

        // dd($data);
        return $dataarray;
    }
    /**
     * function untuk get title status lms,
     * berguna ketika di halaman list lms
     */
    public function getTitleLms($id_status)
    {
        $query = "SELECT stat_title FROM tbl_statuslms WHERE id = '$id_status'";
        $data = DB::select($query);

        return $data[0]->stat_title;
    }

    /**
     * get name brand laptop
     */
    private function getBrandLms($brand)
    {
        $query = "SELECT name_vlookup FROM tbl_vlookup WHERE id_vlookup = '$brand'";
        $data = DB::select($query);

        return $data ? $data[0]->name_vlookup : '-';
    }
}
