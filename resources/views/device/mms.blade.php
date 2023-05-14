@extends('layouts.app')
@section('title', 'MMS')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">

            <!-- modified modal filter-->
            <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Pencarian Departemen</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modalfil" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formInputRepair">
                                <div class="row mb-3" style="font-size: 12px;">
                                    <div class="col-sm-12">
                                        <p>Merek HP</p>
                                        <select class="form-select" id="selectCustomer" name="selectCustomer"
                                            style="font-size: 12px;">
                                            <option value="">Masukkan atau Pilih Merek HP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12" style="font-size: 12px;">
                                        <p>Jenis Permohonan</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction"
                                                style="font-size: 12px;">Karyawan Baru</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual"
                                                style="font-size: 12px;">Error, Kerusakan Barcode, atau lainnya</label>
                                        </div>
                                        <br> <!-- Menambahkan baris baru -->
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction"
                                                style="font-size: 12px;">Penambahan HP Baru</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual"
                                                style="font-size: 12px;">Perubahan data HP</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" style="font-size: 12px; display: flex; flex-direction: row;">
                                    <div class="col-sm-12">
                                        <p>Waktu Pengajuan</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction" style="font-size: 12px;">24
                                                Jam Terakhir</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1
                                                Minggu Terakhir</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1
                                                Bulan Terakhir</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-10" style="font-size: 12px;">
                                        <p>Status Permohonan</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction"
                                                style="font-size: 12px;">Menunggu di approve HRD</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">
                                                Meunggu di approve QHSE/TC</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction"
                                                style="font-size: 12px;">Selesai</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">
                                                Ditolak</label>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modalfil"
                                style="text-decoration: none; font-size: 12px; width: 240px; height: 30px;">Batal</button>
                            <button type="button" style="font-size: 12px; width: 260px; height: 30px;"
                                id="btnSubmitFilter" class="btn btn-primary">Tampilkan Hasil</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End modified modal Filter-->

            {{-- <!-- modified modal Daftar-->
                <div class="modal fade" data-bs-backdrop="static" id="modalDaftar" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Daftar Mobile Management System</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formInputRepair">
                                <div class="row mb-3" style="font-size: 12px;">
                                    <div class="col-sm-6">
                                    <p>Nomor Badge</p>
                                    <input type="text" class="form-control" style="font-size: 12px;"
                                        placeholder="Masukkan Nomor Badge" name="ng_symptom" id="ng_symptom">
                                </div>
                                <div class="col-sm-6">
                                    <p>Nama Karyawan</p>
                                    <select class="form-select" id="selectCustomer" name="selectCustomer"
                                        style="font-size: 12px;">
                                        <option value="">Masukkan Nama atau Pilih Karyawan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                            <div class="col-sm-6" style="font-size: 12px;">
                                <p>Departemen</p>
                                <input type="text" class="form-control" style="font-size: 12px;" placeholder="Masukkan Nama atau Pilih Departemen" name="" id="">
                                </div>
                            <div class="col-sm-6" style="font-size: 12px;">
                                <p>Posisi</p>
                                    <select class="form-select" id="" name="" style="font-size: 12px;"><option value="">Masukkan atau Pilih Posisi</option>
                            </select>
                        </div>
                    </div>
                
                    <div class="row mb-3" style="font-size: 12px; display: flex; flex-direction: row;">
                      <div class="col-sm-12">
                          <p>Mulai Masuk</p>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="" id="radioVisual" value="29">
                              <label class="form-check-label" for="radioVisual" style="font-size: 12px;">24 Jam Terakhir</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="" id="radioVisual" value="30">
                              <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1 Minggu Terakhir</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="" id="radioVisual" value="30">
                              <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1 Bulan Terakhir</label>
                          </div>
                      </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-sm-12" style="font-size: 12px;">
                      <p>Jenis Permohonan</p>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="" id="radioFunction" value="29">
                        <label class="form-check-label" for="radioFunction" style="font-size: 12px;">Karyawan Baru</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="" id="radioVisual" value="30">
                        <label class="form-check-label" for="radioVisual" style="font-size: 12px;">Error, Kerusakan Barcode, atau lainnya</label>
                      </div>
                      <br> <!-- Menambahkan baris baru -->
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="" id="radioFunction" value="29">
                        <label class="form-check-label" for="radioFunction" style="font-size: 12px;">Penambahan HP Baru</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="" id="radioVisual" value="30">
                        <label class="form-check-label" for="radioVisual" style="font-size: 12px;">Perubahan data HP</label>
                      </div>
                    </div>
                </div>
                </form>

            </div>
            <div class="modal-footer">
              <span style="font-size: 12px;">1 / 3 Informasi Karyawan dan Jenis Permohonan</span>
              <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 60px; height: 30px;">Batal</button>
              <button type="button" style="font-size: 12px; width: 100px; height: 30px;" id="btnSelanjut" class="btn btn-primary">Selanjutnya</button>
            </div>
            
        </div>
    </div>
</div>
<!-- End modified modal Daftar--> --}}

            <!-- modified modal klik data table-->
            <div class="modal fade" data-bs-backdrop="static" id="modalDaftar" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Informasi Mobile</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formInputRepair">
                                <div class="container">
                                    <div class="box">
                                        <div class="button" id="button"></div>
                                        <button type="button" id="depart" class="toggle-btn"
                                            onclick="leftClick()">Informasi Perangkat</button>
                                        <button type="button" id="linecode" class="toggle-btn"
                                            onclick="rightClick()">Informasi Pengguna</button>
                                        <button type="button" id="history" class="toggle-btn"
                                            onclick="leftClick()">Riwayat Status</button>
                                        <button type="button" id="response" class="toggle-btn"
                                            onclick="rightClick()">Tanggapan</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal"
                                style="text-decoration: none; font-size: 12px; width: 150px; height: 30px;">Tolak
                                Pengajuan</button>
                            <button type="button" style="font-size: 12px; width: 100px; height: 30px;"
                                id="btnTerima" class="btn btn-primary">Terima Pengajuan</button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- End modified modal klik data table-->

            <div class="row me-3">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Mobile Management System
                    </p>
                </div>

                <div class="col-sm-12 mt-2 d-flex justify-content-between">
                    <div class="d-flex gap-1">
                        <input type="text"
                            style="width: 50px; min-width: 150px; font-size: 12px; padding-left: 30px; background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; background-position: left center;"
                            class="form-control rounded-3" placeholder="Cari HP">
                        <button id="" style="font-size: 12px;" type="button"
                            class="btn btn-outline-danger rounded-3">
                            <i class='bx bx-slider p-1'></i>
                            Filter
                        </button>
                    </div>
                    <div class="d-flex gap-1">
                        <button id="btnDaftar" style="font-size: 12px;" type="button"
                            class="btn btn-outline-danger rounded-3">
                            Daftar MMS
                        </button>
                    </div>
                </div>

                <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                    <span style="font-size: 12px;">Menampilkan 7 dari 8.138 Perangkat</span>
                </div>

                <div class="col-sm-12 mt-1">
                    <table class="table table-responsive table-hover" style="max-width: 1000px;">
                        <thead>
                            <tr style="color: #CD202E; height: -10px;" class="table-danger">
                                <th class="p-3" scope="col">Merek HP</th>
                                <th class="p-3" scope="col">Type HP</th>
                                <th class="p-3" scope="col">Nomor IMEI 1</th>
                                <th class="p-3" scope="col">Nomor IMEI 2</th>
                                <th class="p-3" scope="col">Jenis Permohonan</th>
                                <th class="p-3" scope="col">Waktu Pengajuan</th>
                                <th class="p-3" scope="col">Status Pengajuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="color: gray;">
                                <td class="p-3">Samsung</td>
                                <td class="p-3">Galaxy S12 FE</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">7 April 2023</td>
                                <td class="p-3">
                                    Menunggu di approve HRD
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">ASUS</td>
                                <td class="p-3">Zenfone 8</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">7 April 2023</td>
                                <td class="p-3">
                                    Menunggu di approve HRD
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">Samsung</td>
                                <td class="p-3">Galaxy S7 FE</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">6 April 2023</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Selesai
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">Samsung</td>
                                <td class="p-3">Galaxy S20</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">10 Maret 2023</td>
                                <td class="p-3">
                                    Menunggu di approve oleh QHSE/TC
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">Realme</td>
                                <td class="p-3">Realme 6</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">9 Maret 2023</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/vector.png') }}">
                                    Ditolak
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">Samsung</td>
                                <td class="p-3">Galaxy S12 FE</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">7 Maret 2023</td>
                                <td class="p-3">
                                    Menunggu di approve oleh QHSE/TC
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">Samsung</td>
                                <td class="p-3">Galaxy Note 10</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">123456789012</td>
                                <td class="p-3">Karyawan Baru</td>
                                <td class="p-3">7 Maret 2023</td>
                                <td class="p-3">
                                    Menunggu di approve oleh QHSE/TC
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endsection

    @section('script')

        <script>
            let button = document.getElementById('button');

            function leftClick() {
                button.style.left = "0"
            }

            function rightClick() {
                button.style.left = "185px"
            }

            const btnModal = $('#btnModalRepair');
            const modalForm = $('#modalRepairData');
            const btndaftar = $('#btnDaftar');
            const modaldaftar = $('#modalDaftar');
            const btnSubmitRepair = $('#btnSubmitRepair');

            btndaftar.click(e => {
                e.preventDefault();
                modaldaftar.modal('show');

                getDataCustomer()

            });

            btnModal.click(e => {
                e.preventDefault();
                modalForm.modalfil('show');

                getDataCustomer()

            });
        </script>

    @endsection
