<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswdController;
use App\Http\Controllers\ProfilUSerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListKaryawanController;
use App\Http\Controllers\ProfilKaryawanController;
use App\Http\Controllers\GrupKaryawanController;
use App\Http\Controllers\PKBKaryawanController;
use App\Http\Controllers\AddPKBKaryawanController;
use App\Http\Controllers\MMSController;
use App\Http\Controllers\LMSController;
use App\Http\Controllers\PemberitahuanController;
use App\Http\Controllers\LokerController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PeranController;
use App\Http\Controllers\KritikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

 Route::get('/', function(){
    echo "My Satnusa Mobile App Service Running ...";
 });


// Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
// Route::get('/resetpasswd', [ResetPasswdController::class, 'index'])->name('resetpasswd');
// Route::get('/profile', [ProfilUserController::class, 'index'])->name('profile');

// Route::group(['middleware' => ['LoginCheck']], function () {

//     Route::get('/', [AuthController::class, 'index'])->name('login');
//     Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//     Route::get('/dashboard/customer_list', [DashboardController::class, 'customer_list'])->name('customerlist');
//     Route::post('/dashboard/model_list', [DashboardController::class, 'model_list'])->name('modellist');
//     Route::post('/dashboard/ng_station', [DashboardController::class, 'ng_station'])->name('ngstation');
//     Route::post('/dashboard/simpan_repair', [DashboardController::class, 'simpan_repair'])->name('simpanrepair');
// });

// Route::get('/list', [ListKaryawanController::class, 'index'])->name('list');
// Route::get('/profil', [ProfilKaryawanController::class, 'index'])->name('profil');

// Route::get('/grup', [GrupKaryawanController::class, 'index'])->name('grup');

// Route::get('/pkb', [PKBKaryawanController::class, 'index'])->name('pkb');
// Route::get('/addpkb', [AddPKBKaryawanController::class, 'index'])->name('addpkb');

// Route::get('/mms', [MMSController::class, 'index'])->name('mms');
// Route::get('/lms', [LMSController::class, 'index'])->name('lms');

// /* karna pake session authentication, maka api harus ditarok di route web, karna tidak dapat akses session */
// Route::get('/pemberitahuan', [PemberitahuanController::class, 'index'])->name('pemberitahuan');
// Route::get('/pemberitahuan/list', [PemberitahuanController::class, 'list'])->name('list_pemberitahuan');
// Route::get('/pemberitahuan/penerima/{pemberitahuanId}', [PemberitahuanController::class, 'getReceiver']);
// Route::get('/pemberitahuan/show/{id}', [PemberitahuanController::class, 'show'])->name('show_pemberitahuan');
// Route::get('/pemberitahuan/{id}', [PemberitahuanController::class, 'detail'])->name('detail_pemberitahuan');
// Route::post('/pemberitahuan', [PemberitahuanController::class, 'store'])->name('simpan_pemberitahuan');
// Route::put('/pemberitahuan/{id}', [PemberitahuanController::class, 'update'])->name('ubah_pemberitahuan');
// Route::delete('/pemberitahuan/{id}', [LokerController::class, 'destroy'])->name('hapus_loker');


// Route::get('/loker', [LokerController::class, 'index'])->name('loker');
// Route::get('/loker/list', [LokerController::class, 'list'])->name('list_loker');
// Route::get('/loker/show/{id}', [LokerController::class, 'show'])->name('show_loker');
// Route::post('/loker', [LokerController::class, 'store'])->name('simpan_loker');
// Route::put('/loker/update/{id}', [LokerController::class, 'update'])->name('ubah_loker');
// Route::delete('/loker/{id}', [LokerController::class, 'destroy'])->name('hapus_loker');

// Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('pengaduan');

// Route::get('/peran', [PeranController::class, 'index'])->name('peran');

// Route::get('/kritik', [KritikController::class, 'index'])->name('kritik');
