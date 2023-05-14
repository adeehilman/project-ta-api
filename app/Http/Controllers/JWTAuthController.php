<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Mms;
use App\Models\Question;
use App\Models\SecurityQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{

  public function userExists($employee_no)
  {
    $user = User::where('employee_no', $employee_no)->first();
    return $user;
  }

  public function register(Request $request)
  {
    $request->validate([
      'employee_no' => 'required',
      'password' => 'required',
      'tgl_lahir' => 'required',
      'security_question' => 'required',
      'security_question_answer' => 'required',
    ]);

    $userExist = $this->userExists($request->employee_no);
    if (!$userExist) {
      throw ValidationException::withMessages(['message' => 'Kontak hrd untuk membuat badge'], 422);
    }

    $userExist->no_hp = $request->no_hp;
    $userExist->no_hp2 = $request->no_hp2;
    $userExist->home_telp = $request->home_telp;
    $userExist->save();

    if (strtolower($userExist->regisStatus->name_vlookup) !== 'terdaftar') throw ValidationException::withMessages(['message' => 'Status registrasi harus diubah ke daftar terlebih dahulu'], 422);

    $addressExist = Address::where('employee_no', $userExist->employee_no)->first();
    if (!$addressExist) {
      $address = new Address();
      $address->alamat = $request->alamat;
      $address->kelurahan = $request->kelurahan;
      $address->kecamatan = $request->kecamatan;
      $address->latitude = $request->latitude ?? null;
      $address->longitude = $request->longitude ?? null;
      $address->employee_no = $userExist->employee_no;
      $address->save();
    }

    $securityQuestion = new SecurityQuestion();
    $securityQuestion->id_question = $request->security_question;
    $securityQuestion->answer = $request->security_question_answer;
    $securityQuestion->employee_no = $userExist->employee_no;
    $securityQuestion->save();

    return response()->json(['message' => 'Register Sukses'], 200);
  }

  public function login(Request $request)
  {
    $request->validate([
      'employee_no' => 'required',
      'password' => 'required'
    ]);

    $credentials = $request->only('employee_no', 'password');

    if (Auth::attempt($credentials)) {
      $token = JWTAuth::fromUser(Auth::user());

      /* cek apakah di mms udh ad uuid yg sma */
      /* kalau engga buat baru */
      /* kalau iya ambil uuidnya */
      $isNewSmartphone = Mms::where('uuid', $request->UUID)->first();
      $uuid = $isNewSmartphone->uuid ?? null;
      if (!$isNewSmartphone) {
        $mms = new Mms();
        $mms->employee_no = $request->employee_no;
        $mms->uuid = $request->uuid;
        $mms->save();

        $uuid = $request->uuid;
      }

      return response()->json(['token' => $token, 'uuid' => $uuid]);
    }

    return response()->json(['error' => 'Invalid credentials'], 401);
  }

  public function forgetPassword(Request $request)
  {
    $request->validate([
      'employee_no' => 'required',
      'security_answer' => 'required',
      'password' => 'required'
    ]);

    $userExist = $this->userExists($request->employee_no);

    if (!$userExist) {
      throw ValidationException::withMessages(['message' => 'Belum ada user dengan badge yang diberikan'], 422);
    }

    $securityQuestion = SecurityQuestion::where('employee_no', $request->employee_no)->first();

    if ($securityQuestion->answer != $request->security_answer)
      throw ValidationException::withMessages(['message' => 'Jawaban pertanyaan keamanan salah'], 422);

    $userExist->password = bcrypt($request->password);
    $userExist->save();

    return response()->json(['message' => 'Password berhasil diubah'], 200);
  }

  public function listQuestion(Request $request)
  {
    return Question::all();
  }
}
