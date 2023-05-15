<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KritikSaranController extends Controller
{

    // functio get kategori kritik
    private function getKategori($id_kategori)
    {
        $data = DB::table('tbl_vlookup')
            ->where('id_vlookup', $id_kategori)
            ->first();

        return $data->name_vlookup;
    }

    // function get status kritik
    private function getRiwayatKritikSaran($id_kritiksaran)
    {
        $data = DB::table('tbl_riwayatkritiksaran')
            ->select('stat_title', 'stat_desc', 'createdate')
            ->join('tbl_statuskritiksaran', 'tbl_statuskritiksaran.id', '=', 'tbl_riwayatkritiksaran.status_riwayat')
            ->where('id_kritiksaran', $id_kritiksaran)
            ->get();
        return $data;
    }

    // function get tanggapan 
    private function getTanggapanKritikSaran($id_kritiksaran){
        $data = DB::table('tbl_tanggapankritiksaran')
                        ->select('fullname', 'position_code', 'respon', 'waktu')
                        ->join('tbl_karyawan', 'tbl_karyawan.badge_id', '=', 'tbl_tanggapankritiksaran.badge_id')
                        ->where('id_kritik', $id_kritiksaran)
                        ->get();
        return $data;
    }

    // function untuk get semua kritik dan saran
    public function getAllKritikDanSaran(Request $request)
    {

        $request->validate([
            "badge_id" => "required"
        ]);

        $data = DB::table('tbl_kritiksaran')
            ->where('badge_id', $request->badge_id)
            ->get();

        foreach ($data as $key => $item) {
            $item->kategori = $this->getKategori($item->kategori);
            $item->file_upload = url(asset('kritiksaran/' . $item->file_upload));
            $item->riwayat_status = $this->getRiwayatKritikSaran($item->id);
        }

        return response()->json([
            "message" => "Response OK",
            "data"    => $data
        ]);
    }

    // insert ke database kritik saran
    public function insertKritikSaran(Request $request)
    {

        $request->validate([
            "id_kategori" => "required",
            "description" => "required",
            "file_upload" => "required|file|mimes:jpg,png,pdf|max:2048",
            "badge_id"    => "required",
            "area"        => "required"
        ]);

        DB::beginTransaction();
        try {

            $file_upload = $request->file('file_upload');
            $file_name   = time() . '.' . $file_upload->extension();

            // dd($file_name);

            $upload = $file_upload->move(public_path('kritiksaran'), $file_name);
            if ($upload) {
                // insert ke tabel kritiksaran
                $id_kritiksaran = DB::table('tbl_kritiksaran')
                    ->insertGetId([
                        "kategori"      => $request->id_kategori,
                        "description"   => $request->description,
                        "file_upload"   => $file_name,
                        "badge_id"      => $request->badge_id,
                        "area"          => $request->area
                    ]);
                if ($id_kritiksaran) {
                    DB::table('tbl_riwayatkritiksaran')
                        ->insert([
                            "id_kritiksaran" => $id_kritiksaran,
                            "badge_id"       => $request->badge_id,
                            "status_riwayat" => 1, // defaultnya 1 diambil dari tbl_statuskritksaran
                            "createby"       => $request->badge_id,
                            "createdate"     => date("Y-m-d H:i:s")
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil insert kritik dan saran",
                "data"    => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => $th->getMessage(),
            ], 400);
        }
    }

    // get detail kritik dan saran
    public function detailKritikSaran(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);

        $data = DB::table('tbl_kritiksaran')
            ->where("id", $request->id)
            ->first();

        $data->kategori = $this->getKategori($data->kategori);
        $data->file_upload = url(asset('kritiksaran/' . $data->file_upload));
        $data->riwayat_status = $this->getRiwayatKritikSaran($data->id);
        $data->riwayat_tanggapan = $this->getTanggapanKritikSaran($data->id);

        return response()->json([
            "message" => "Response OK",
            "data"    => $data
        ]);

    }

    // insert ke tabel tanggapan kritik saran
    public function createTanggapan(Request $request){
        $request->validate([
            "id_kritiksaran" => "required",
            "badge_id"       => "required",
            "respon"         => "required"
        ]);

        DB::beginTransaction();
        try {

            DB::table('tbl_tanggapankritiksaran')
                    ->insert([
                        "id_kritik" => $request->id_kritiksaran,
                        "respon"    => $request->respon,
                        "badge_id"  => $request->badge_id,
                        "waktu"     => date("Y-m-d H:i:s")
                    ]);
            
            DB::commit();

            return response()->json([
                "message" => "Response OK, Berhasil insert data tanggapan",
                "data"    => []
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "message" => $th->getMessage(),
            ], 400);
        }
    }
}
