<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LokerController extends Controller
{
    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'posisi' => 'required',
            'durasi' => 'required|date',
            'image' => 'mimes:jpeg,png,jpg|max:1024'
        ], [
            'posisi.required' => 'Posisi tidak boleh kosong',
            'durasi.required' => 'Deskripsi tidak boleh kosong',
            'durasi.date' => 'Durasi / berlaku sampai harus dalam format yang benar',
            'image.mimes' => 'Format gambar harus dalam jpg, jpeg, atau png',
            'image.max' => 'Ukuran gambar tidak boleh melebih 1 mb',
        ]);
    }

    public function index()
    {

        $loggedInUser = User::loggedIn()->first();
        $data = ['userInfo' => $loggedInUser];

        $loker = $this->list();

        return view('loker.loker', $data, compact('loker'));
    }

    public function list()
    {
        return JobVacancy::with('status')->paginate(3);
    }

    public function show($id)
    {
        $jobVacancy = JobVacancy::with(['status', 'pembuat', 'pengubah'])->findOrFail($id);
        if ($jobVacancy->file_upload) {
            $jobVacancy->file_upload = asset('storage/' . $jobVacancy->file_upload);
        }
        return $jobVacancy;
    }

    public function store(Request $request)
    {

        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ], 422);
        }

        $path = null;
        if ($request->file('image')) {
            $path = $request->file('image')->storePublicly('public/images');
        }

        $jobVacancy = new JobVacancy();
        $jobVacancy->posisi = $request->posisi;
        $jobVacancy->desc = $request->deskripsi ?? "";
        $jobVacancy->durasi = $request->durasi;
        $jobVacancy->posting_time = now();
        $jobVacancy->catatan_hrd = $request->catatan_hrd ?? "";
        $jobVacancy->status = Status::where('deskripsi', 'sudah diumumkan')->first()->id ?? null;
        $jobVacancy->file_upload = $path ? 'images/' . basename($path) : null;
        $jobVacancy->save();

        return response()->json(['message' => 'Data berhasil ditambah', 'data' => $jobVacancy], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ], 422);
        }

        $jobVacancy = JobVacancy::with(['status'])->findOrFail($id);
        if ($request->hasFile('image')) {
            // Delete old file if it exists
            if ($jobVacancy->file_upload) {
                Storage::delete('public/' . $jobVacancy->file_upload);
            }
            $path = $request->file('image')->storePublicly('public/images');
            $jobVacancy->file_upload = $path ? 'images/' . basename($path) : null;
        }
        $jobVacancy->posisi = $request->posisi;
        $jobVacancy->desc = $request->deskripsi ?? "";
        $jobVacancy->durasi = $request->durasi;
        $jobVacancy->posting_time = $request->posting_time;
        $jobVacancy->catatan_hrd = $request->catatan_hrd ?? "";
        $jobVacancy->status = Status::where('deskripsi', 'sudah diumumkan')->first()->id ?? null;
        $jobVacancy->save();

        return response()->json(['message' => 'Data berhasil diubah', 'data' => $jobVacancy], 200);
    }

    public function destroy(Request $request, $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        $jobVacancy->delete();

        return response()->json(['message' => 'Data berhasil dihapus', 'status' => 204], 204);
    }
}
