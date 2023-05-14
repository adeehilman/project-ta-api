<?php

namespace App\Http\Controllers;

use App\Models\SuggestionCriticism;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Completion\Suggestion;

class KritikController extends Controller
{
    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'kategori' => 'required',
            'deskripsi' => 'required',
            'mode' => 'required',
            'area' => 'required',
            'file_upload' => 'mimes:jpeg,png,jpg|max:1024',
        ], [
            'kategori.required' => 'Kategori tidak boleh kosong',
            'mode.required' => 'mode tidak boleh kosong',
            'area.required' => 'area tidak boleh kosong',
            'deskripsi.required' => 'Deskripsi tidak boleh kosong',
            'file_upload.mimes' => 'Format image harus jpeg, png, atau jpg',
            'file_upload.max' => 'Ukuran image tidak boleh lebih dari 1 mb',
        ]);
    }

    public function index()
    {
        $loggedInUser = User::loggedIn()->first();
        $data = ['userInfo' => $loggedInUser];

        $suggestion = $this->list();

        return view('kritik.kritik', $data, compact('suggestion'));
    }

    public function list()
    {
        return SuggestionCriticism::with(['pengirim', 'kategori'])->paginate(3);
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

        $kritik = new SuggestionCriticism();
        $kritik->mode = $request->mode;
        $kritik->kategori = $request->kategori;
        $kritik->area = $request->area;
        $kritik->description = $request->deskripsi;
        $kritik->employee_no = Auth::user()->employee_no;
        $kritik->created_at = now();
        $kritik->file_upload = $path ? 'images/' . basename($path) : null;
        $kritik->save();

        return response()->json(['message' => 'Data berhasil ditambahkan'], 201);
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



        $kritik = SuggestionCriticism::findOrFail($id);
        $kritik->mode = $request->mode;
        $kritik->kategori = $request->kategori;
        $kritik->area = $request->area;
        $kritik->description = $request->deskripsi;
        $kritik->employee_no = Auth::user()->employee_no;
        $kritik->created_at = now();

        if ($request->hasFile('image')) {
            // Delete old file if it exists
            if ($kritik->file_upload) {
                Storage::delete('public/' . $kritik->file_upload);
            }
            $path = $request->file('image')->storePublicly('public/images');
            $kritik->file_upload = $path ? 'images/' . basename($path) : null;
        }
        $kritik->save();

        return response()->json(['message' => 'Data berhasil diubah'], 200);
    }

    public function show($id)
    {
        return SuggestionCriticism::with(['pengirim', 'kategori', 'mode'])->findOrFail($id);
    }

    public function destroy(Request $request, $id)
    {
        $kritik = SuggestionCriticism::findOrFail($id);
        $kritik->delete();

        return response()->json(['message' => 'Data berhasil dihapus', 'status' => 204], 204);
    }
}
