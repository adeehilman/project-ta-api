<?php

namespace App\Http\Controllers;

use App\Models\EmployeeGroup;
use App\Models\Notification;
use App\Models\NotificationReceiver;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;

class PemberitahuanController extends Controller
{
    public function index()
    {

        $loggedInUser = User::loggedIn()->first();
        $data = ['userInfo' => $loggedInUser];

        $grupKaryawan = EmployeeGroup::all();
        $pemberitahuan = $this->list();
        $pemberitahuan->grup_karyawan = $grupKaryawan;

        return view('pemberitahuan.index', $data, compact('pemberitahuan'));
    }

    public function list()
    {
        return Notification::with(['penerima', 'pengirim'])
            ->paginate(3);
    }

    public function show($id)
    {
        return Notification::with(['pengirim', 'penerima', 'pembuat', 'pengubah'])->findOrFail($id);
    }

    public function detail($id)
    {
        $loggedInUser = User::loggedIn()->first();
        $data = ['userInfo' => $loggedInUser];

        $grupKaryawan = EmployeeGroup::all();
        $pemberitahuan = $this->show($id);
        $pemberitahuan->semuaPenerima = $this->getReceiver($id);
        $pemberitahuan->grup_karyawan = $grupKaryawan;

        return view('pemberitahuan.detail', $data, compact('pemberitahuan'));
    }

    public function getReceiver($pemberitahuanId)
    {
        return NotificationReceiver::with(['receiver', 'notification'])->where('pemberitahuan_id', $pemberitahuanId)->paginate();
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

        $loggedInUser = User::loggedIn()->first();

        $path = null;
        if ($request->file('image')) {
            $path = $request->file('image')->storePublicly('public/images');
        }

        $notification = new Notification();
        $notification->judul = $request->judul;
        $notification->deskripsi = $request->deskripsi;
        $notification->sent_by = $loggedInUser->employee_no ?? $request->pembuat;
        $notification->created_by = $loggedInUser->employee_no ?? $request->pembuat;
        $notification->updated_by = $loggedInUser->employee_no ?? $request->pembuat;
        $notification->receive_by = $request->penerima;
        $notification->is_sent_public = $request->is_sent_public;
        $notification->waktu_pemberitahuan = filter_var($request->isSentNow, FILTER_VALIDATE_BOOLEAN) ? date('Y-m-d') : date('Y-m-d', strtotime($request->waktu_pemberitahuan)) . " " . $request->jam_pemberitahuan . ':00';
        $notification->file_upload = $path ? 'images/' . basename($path) : null;
        $notification->save();

        /* kalau is_sent_public
                ambil smua user
                utk stiap user buat notification receiver baru dgn user tersebut dan notification yg ad
        */
        if ($request->isSentPublic) {
            $users = User::all();
            foreach ($users as $user) {
                $notificationReceiver = new NotificationReceiver();
                $notificationReceiver->employee_no = $user->employee_no;
                $notificationReceiver->pemberitahuan_id = $notification->id;
                $notificationReceiver->is_sent = true;
                $notificationReceiver->is_read = false;
                $notificationReceiver->save();
            }
        }

        return response()->json(['message' => 'Data berhasil ditambahkan', 'data' => $notification], 201);
    }

    public function update($pemberitahuanId, Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ], 422);
        }

        $loggedInUser = User::loggedIn()->first();

        $notification = Notification::findOrFail($pemberitahuanId);
        $notification->judul = $request->judul;
        $notification->deskripsi = $request->deskripsi;
        $notification->sent_by = $loggedInUser->employee_no ?? $request->pembuat;
        $notification->updated_by = $loggedInUser->employee_no ?? $request->pembuat;
        $notification->receive_by = $request->penerima;
        $notification->is_sent_public = $request->is_sent_public;
        $notification->waktu_pemberitahuan = filter_var($request->isSentNow, FILTER_VALIDATE_BOOLEAN) ? date('Y-m-d') : date('Y-m-d', strtotime($request->waktu_pemberitahuan)) . " " . $request->jam_pemberitahuan . ':00';

        if ($request->hasFile('image')) {
            // Delete old file if it exists
            if ($notification->file_upload) {
                Storage::delete('public/' . $notification->file_upload);
            }
            $path = $request->file('image')->storePublicly('public/images');
            $notification->file_upload = $path ? 'images/' . basename($path) : null;
        }

        $notification->save();

        return response()->json(['message' => 'Data berhasil diubah', 'data' => $notification], 200);
    }

    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Data berhasil dihapus', 'status' => 204], 204);
    }

    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'judul' => 'required',
            'penerima' => 'required',
            'deskripsi' => 'required',
        ], [
            'penerima.required' => 'Penerima tidak boleh kosong',
            'judul.required' => 'Judul tidak boleh kosong',
            'deskripsi.required' => 'Deskripsi tidak boleh kosong',
        ]);
    }

    // public function create(Request $request)
    // {
    //     $notification = new Notification();
    //     $notification->judul = $request->judul;
    //     $notification->penerima = $request->penerima;
    //     $notification->deskripsi = $request->deskripsi;
    //     $notification->pengirim = $request->pengirim;
    //     $notification->waktu_pemberitahuan = $request->waktu_pemberitahuan;
    //     // $notification->gambar = $request->gambar;
    // }
}
