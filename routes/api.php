<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KaryawanController;
use App\Http\Controllers\API\KecamatanController;
use App\Http\Controllers\API\KelurahanController;
use App\Http\Controllers\API\KritikSaranController;
use App\Http\Controllers\API\LowonganController;
use App\Http\Controllers\API\PengumumanController;
use App\Http\Controllers\API\QuestionsController;
use App\Http\Controllers\LokerController;
use App\Http\Controllers\PemberitahuanController;
use App\Http\Controllers\KritikController;
use App\Http\Controllers\JWTAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::get('list-question', [JWTAuthController::class, 'listQuestion']);
// Route::post('login', [JWTAuthController::class, 'login']);
// Route::post('register', [JWTAuthController::class, 'register']);
// Route::post('forget-password', [JWTAuthController::class, 'forgetPassword']);
Route::post('/register', [AuthController::class, 'registrasi']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['api', 'auth:api']], function () {
  /**
   * Pengumuman API Service
   */
  Route::post('/pengumuman/baca', [PengumumanController::class, 'bacaPengumuman']);
  Route::get('/pengumuman', [PengumumanController::class, 'getAllPengumuman']);

  /**
   * Loker API Service
   */
  Route::get('/loker', [LowonganController::class, 'getAllLoker']);

  /**
   * Karyawan API Service
   */
  Route::get('check-mms', [KaryawanController::class, 'cekMMS']);
  Route::post('cek-jawaban', [AuthController::class, 'checkAnswer']);
  Route::post('forget-password', [AuthController::class, 'forgetPassword']);
  Route::get('profile', [KaryawanController::class, 'profile']);
  Route::post('profile/edit-alamat', [KaryawanController::class, 'editAlamat']);
  Route::post('profile/edit-kontak', [KaryawanController::class, 'editKontak']);
  Route::post('profile/change-security-question', [KaryawanController::class, 'editSecurity']);

  /**
   * Kritik dan saran
   */
  Route::get('/kritiksaran/me', [KritikSaranController::class, 'getAllKritikDanSaran']);
  Route::post('/kritiksaran/add', [KritikSaranController::class, 'insertKritikSaran']);
  Route::post('/kritiksaran/tanggapan', [KritikSaranController::class, 'createTanggapan']);
  Route::get('/kritiksaran/detail', [KritikSaranController::class, 'detailKritikSaran']);

  /**
   * Karyawan API Service
   */
  Route::get('cek-badge', [KaryawanController::class, 'cekBadge']);

  /**
   * List Questions
   */
  Route::get('/questions', [QuestionsController::class, 'getAllQuestions']);

  /**
   * List kecamatan
   */
  Route::get('/kecamatan', [KecamatanController::class, 'getAllKecamatan']);

  /**
   * List Kelurahan
   */
  Route::get('/kelurahan', [KelurahanController::class, 'getAllKelurahan']);
});


/**
 * di luar middleware
 */
