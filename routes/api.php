<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CronJobController;
use App\Http\Controllers\API\DurationBreakController;
use App\Http\Controllers\API\ImageHelperController;
use App\Http\Controllers\API\KalenderController;
use App\Http\Controllers\API\KaryawanController;
use App\Http\Controllers\API\KecamatanController;
use App\Http\Controllers\API\KelurahanController;
use App\Http\Controllers\API\KritikSaranController;
use App\Http\Controllers\API\LmsController;
use App\Http\Controllers\API\LowonganController;
use App\Http\Controllers\API\MeetingRoomController;
use App\Http\Controllers\API\MmsController;
use App\Http\Controllers\API\PengumumanController;
use App\Http\Controllers\API\PlatformController;
use App\Http\Controllers\API\PlayStoreController;
use App\Http\Controllers\API\QuestionsController;
use App\Http\Controllers\API\UserRoleController;
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
  Route::get('/pengumuman/detail', [PengumumanController::class, 'detailPengumuman']);

  /**
   * Loker API Service
   */
  Route::get('/loker', [LowonganController::class, 'getAllLoker']);
  Route::get('/loker/detail', [LowonganController::class, 'getDetailLowongan']);

  /**
   * Karyawan API Service
   */
  Route::get('check-mms', [KaryawanController::class, 'cekMMS']);
  Route::get('profile', [KaryawanController::class, 'profile']);
  Route::post('profile/edit-alamat', [KaryawanController::class, 'editAlamat']);
  Route::post('profile/edit-kontak', [KaryawanController::class, 'editKontak']);
  Route::post('profile/change-security-question', [KaryawanController::class, 'editSecurity']);
  Route::post('profile/change-password', [KaryawanController::class, 'changePassword']);

  /**
   * Kritik dan saran
   */
  Route::get('/kritiksaran/me', [KritikSaranController::class, 'getAllKritikDanSaran']);
  Route::post('/kritiksaran/add', [KritikSaranController::class, 'insertKritikSaran']);
  Route::post('/kritiksaran/tanggapan', [KritikSaranController::class, 'createTanggapan']);
  Route::get('/kritiksaran/detail', [KritikSaranController::class, 'detailKritikSaran']);

  /**
   * Question
   */
  Route::get('/questions/check', [QuestionsController::class, 'checkSecurityQuestion']);

  /**
   * First login
   */
  Route::get('/first-login', [AuthController::class, 'isFirstLogin']);
  Route::post('/first-login', [AuthController::class, 'setFirstLogin']);

  /**
   * durarion break time
   */
  Route::get('/breaktime/getdurationbreak', [DurationBreakController::class, 'getDurationBreak']);

  /**
   * lms API 
   */
  Route::post('/lms/pengajuan', [LmsController::class, 'insertPengajuan']);
  Route::post('/lms/tanggapan', [LmsController::class, 'beriTanggapan']);
  Route::get('/lms/list', [LmsController::class, 'listLms']);
  Route::get('/lms/detail', [LmsController::class, 'detailLMS']);

  /**
   * MMS API
   */
  Route::get('/mms/list', [MmsController::class, 'listmms']);
  Route::post('/mms/pengajuan', [MmsController::class, 'pengajuan']);
  Route::get('/mms/detail', [MmsController::class, 'detailMMS']);
  Route::post('/mms/tanggapan', [MmsController::class, 'beriTanggapan']);

  /**
   * User Role
   */
  Route::get('/user-role', [UserRoleController::class, 'getMyRole']);

  
});

/**
 * Karyawan API Service
 */
Route::get('cek-badge', [KaryawanController::class, 'cekBadge']);

/**
 * List Questions
 */
Route::get('/questions', [QuestionsController::class, 'getAllQuestions']);
Route::get('questions/my-question', [KaryawanController::class, 'getMyQuestion']);


/**
 * List kecamatan
 */
Route::get('/kecamatan', [KecamatanController::class, 'getAllKecamatan']);

/**
 * List Kelurahan
 */
Route::get('/kelurahan', [KelurahanController::class, 'getAllKelurahan']);

/**
 * Forget Password
 */
Route::post('forget-password', [AuthController::class, 'forgetPassword']);

/**
 * Cek Jawaban
 */
Route::post('cek-jawaban', [AuthController::class, 'checkAnswer']);

/**
 * Ekios check uuid
 */
Route::get('ekios/profile', [KaryawanController::class, 'getProfileEkios']);

/**
 * Decrtypt code
 */
Route::post('/decrypt_qr_code', [AuthController::class, 'decryptQr']);

/**
 * Play store kebutuhan
 */
Route::get('karyawan/delete', [PlayStoreController::class, 'deletedUser']);

/**
 * get all brand laptop
 */
Route::get('/lms/brandlaptop', [LmsController::class, 'getBrandLaptop']);

/**
 * get all hari libur kalender
 */
Route::get('/kalender', [KalenderController::class, 'getAllList']);

/**
 * get all brand handphone
 */
Route::get('/mms/brandsmartphone', [MmsController::class, 'getBrandSmartphone']);

/**
 * Task Schedule -- START
 */
Route::get('/taskschedule/getsisacuti', [CronJobController::class, 'getSisaCuti']);
Route::get('/taskschedule/getaccessdoor', [CronJobController::class, 'getAccessDoor']);


/**
* Meeting Room
*/

Route::group(['middleware' => 'api','prefix' => 'meeting'], function ($router) {
  Route::post('login', [MeetingRoomController::class, 'login']);
  Route::post('logout', [MeetingRoomController::class, 'logout']);
  Route::post('test', [MeetingRoomController::class, 'test']);
  Route::get('all-schedule', [MeetingRoomController::class, 'getAllSchedule']);
  Route::get('image/room', [ImageHelperController::class, 'getImageRoom']);
  Route::get('search-room', [MeetingRoomController::class, 'searchRoom']);
  Route::get('schedule/detail', [MeetingRoomController::class, 'detailSchedule']);
  // Route::get('/meeting/all-schedule', [MeetingRoomController::class, 'getAllSchedule']);
  // Route::get('/meeting/all-room', [MeetingRoomController::class, 'getAllRoom']);
  // Route::get('meeting/image/room', [ImageHelperController::class, 'getImageRoom']);
  // Route::get('/meeting/search-room', [MeetingRoomController::class, 'searchRoom']);
  // Route::get('/meeting/schedule/detail', [MeetingRoomController::class, 'detailSchedule']);
  Route::get('search-user', [MeetingRoomController::class, 'searchUser']);
  Route::post('insert_meeting', [MeetingRoomController::class, 'insertMeeting']);
  Route::post('update-meeting', [MeetingRoomController::class, 'updateMeeting']);
  Route::post('cancel-meeting', [MeetingRoomController::class, 'cancelMeeting']);
  Route::post('beri-tanggapan', [MeetingRoomController::class, 'beriTanggapan']);
  Route::get('my-meeting', [MeetingRoomController::class, 'myMeeting']);
  Route::get('detail-meeting-saya', [MeetingRoomController::class, 'detailMeetingSaya']);
  Route::post('aksi-kehadiran', [MeetingRoomController::class, 'aksiKehadiran']);
  Route::post('edit-partisipan', [MeetingRoomController::class, 'editPartisipan']);
});

Route::group(['prefix' => 'digitalsop'], function ($router){
  Route::post('kirim-notif', [PlatformController::class, 'sendNotif']);
  Route::get('get-user', [PlatformController::class, 'getUserInfo']);
});

Route::group(['prefix' => 'platform'], function($router){
  Route::post('check-credentials', [PlatformController::class, 'checkCredentials']);
});

Route::get('/meeting/all-room', [MeetingRoomController::class, 'getAllRoom']);
Route::get('/meeting/send-notif', [MeetingRoomController::class, 'sendNotif']);
Route::get('/meeting/fasilitas', [MeetingRoomController::class, 'getListFasilitas']);