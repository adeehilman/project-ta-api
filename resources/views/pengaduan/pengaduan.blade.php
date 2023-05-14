@extends('layouts.app')
@section('title', 'Pengaduan Pelanggaran')


@section('content')

    <div class="wrappers">
        <div class="wrapper_content">

            <!-- modal -->
            <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 30%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Pengaduan Pelanggaran</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formInputRepair" style="font-size: 14px;">
                                <div class="row mb-3">
                                    <div class="col-sm-10">
                                        <p>Jenis Pelanggaran</p>
                                        <select class="form-select" id="selectNGStation" name="selectNGStation"
                                            style="font-size: 12px;">
                                            <option value="">Masukkan atau Pilih Jenis Pelanggaran</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3" style="font-size: 12px;">
                                    <div class="col-sm-12">
                                        <p>Waktu Pengaduan</p>
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

                                <div class="row mb-3" style="font-size: 12px; display: flex; flex-direction: row;">
                                    <div class="col-sm-10">
                                        <p>Status Pengaduan</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction" style="font-size: 12px;">Belum ditanggapi</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">Sedang ditinjau</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1
                                                Selesai</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">1
                                                Ditolak</label>
                                        </div>
                                    </div>
                                </div>

                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal"
                                style="text-decoration: none; font-size: 12px; width: 170px; height: 30px;">Batal</button>
                            <button type="button" style="font-size: 12px; width: 180px; height: 30px;"
                                id="btnSubmitRepair" class="btn btn-primary">Tampilkan Hasil</button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end modal -->
            <div class="row me-1">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Pengaduan Pelanggaran
                    </p>
                </div>

                <div class="col-sm-12 mt-2 d-flex justify-content-between">
                    <div class="d-flex gap-1">
                        <input type="text"
                            style="width: 50px; min-width: 150px; font-size: 12px; padding-left: 30px; 
                    background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; 
                    background-position: left center;"
                            class="form-control rounded-3" placeholder="Cari Pengirim">
                        <button id="btnModalRepair" style="font-size: 12px;" type="button"
                            class="btn btn-outline-danger rounded-3">
                            <i class='bx bx-slider p-1'></i>
                            Filter
                        </button>
                    </div>
                </div>

                <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                    <span style="font-size: 12px;">Menampilkan 7 dari 100 Pengirim</span>
                </div>

                <div class="col-sm-12 mt-1">
                    <table class="table table-responsive table-hover">
                        <thead>
                            <tr style="color: #CD202E; height: 10px;" class="table-danger">
                                <th class="p-3" scope="col">Pengirim</th>
                                <th class="p-3" scope="col">Jenis Pelanggaran</th>
                                <th class="p-3" scope="col">Deskripsi Pelanggaran</th>
                                <th class="p-3" scope="col">Nama Pelanggar</th>
                                <th class="p-3" scope="col">Waktu Kejadian</th>
                                <th class="p-3" scope="col">Statusa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Anonymous</td>
                                <td class="p-3">Pencurian</td>
                                <td class="p-3">Saya, ingin melaporkan tindakan pencurian yang saya ...</td>
                                <td class="p-3">Lili</td>
                                <td class="p-3">10.00</td>
                                <td class="p-3">Belum ditanggapi</td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Anne Forger</td>
                                <td class="p-3">Aplikasi</td>
                                <td class="p-3">Aplikasi ini memiliki beberapa kelemahan dalam penggunaannya.</td>
                                <td class="p-3">Oscar</td>
                                <td class="p-3">15.00</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Sudah Ditanggapi
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Anonymous</td>
                                <td class="p-3">Perusahaan</td>
                                <td class="p-3">Perusahaan ini memiliki beberapa kelemahan dalam cara ...</td>
                                <td class="p-3">Oscar</td>
                                <td class="p-3">11.00</td>
                                <td class="p-3">Ditinjau</td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Muhammad Ridwan</td>
                                <td class="p-3">Aplikasi</td>
                                <td class="p-3">Aplikasi ini memiliki beberapa kelemahan dalam penggunaannya.</td>
                                <td class="p-3">Dede</td>
                                <td class="p-3">16.00</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Sudah Ditanggapi
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Muhammad Rahmat</td>
                                <td class="p-3">Perusahaan</td>
                                <td class="p-3">Perusahaan ini memiliki beberapa kelemahan dalam cara ...</td>
                                <td class="p-3">Kiki</td>
                                <td class="p-3">12.00</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Selesai
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Siti Fadhilah</td>
                                <td class="p-3">Aplikasi</td>
                                <td class="p-3">Aplikasi ini memiliki beberapa kelemahan dalam penggunaannya.</td>
                                <td class="p-3">Nana</td>
                                <td class="p-3">16.00</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Selesai
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <td class="p-3">Budi Irawan</td>
                                <td class="p-3">Aplikasi</td>
                                <td class="p-3">Aplikasi ini memiliki beberapa kelemahan dalam penggunaannya.</td>
                                <td class="p-3">Jeje</td>
                                <td class="p-3">16.30</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Selesai
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>
        const btnModal = $('#btnModalRepair');
        const modalForm = $('#modalRepairData');
        const selectCustomer = $('#selectCustomer');
        const selectModelCustomer = $('#selectModelCustomer');
        const btnSubmitRepair = $('#btnSubmitRepair');

        btnModal.click(e => {
            e.preventDefault();
            modalForm.modal('show');

            getDataCustomer()

        });


        const getDataCustomer = () => {

            let html = '';

            $.ajax({
                url: '{{ route('customerlist') }}',
                method: 'GET',
                dataType: 'json',
                beforeSend: () => {

                },
                success: res => {
                    if (res.status === 200) {
                        $.each(res.data, (i, v) => {
                            html +=
                                `
            <option value="${v.id}">${v.customer_name}</option>
            `;
                        });
                        $('#selectCustomer').append(html);

                    }

                }

            })
        }

        selectCustomer.change(e => {
            e.preventDefault();

            const value = $('#selectCustomer').val()
            let html = '';
            if (value) {

                $.ajax({
                    url: '{{ route('modellist') }}',
                    method: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        values: value
                    },
                    dataType: 'json',
                    success: (res) => {

                        if (res.status === 200) {

                            html += `<option value="">Pilih model</option>`;

                            $.each(res.data, (i, v) => {
                                html +=
                                    `
              <option value="${v.id}">${v.model}</option>
              `;
                            });
                        }

                        $('#selectModelCustomer').children().remove();
                        $('#selectModelCustomer').append(html)
                    }
                })

            }


        });


        // select model
        selectModelCustomer.change(e => {
            e.preventDefault();

            const value = $('#selectModelCustomer').val()
            let html = ''
            if (value) {
                $.ajax({
                    url: '{{ route('ngstation') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        values: value
                    },
                    dataType: 'json',
                    success: res => {

                        html += `<option value="">Pilih Lokasi NG</option>`;

                        if (res.status === 200) {

                            console.log(res);


                            $.each(res.data, (i, v) => {
                                html +=
                                    `
              <option value="${v.id}">${v.name_vlookup}</option>
              `;
                            });

                        }

                        $('#selectNGStation').children().remove();
                        $('#selectNGStation').append(html)


                    }

                })
            }
        });

        btnSubmitRepair.click(function(e) {
            e.preventDefault();

            // const repairCat = $('input[name="radioRepairCategory"]:checked').val();

            const repairCat = $('input[name="radioRepairCategory"]:checked').val();
            const serialNum = $('input[name="txSerial_number"]').val();
            const selectCustomer = $('#selectCustomer').val();
            const selectModelCustomer = $('#selectModelCustomer').val();
            const rejectCategory = $('input[name="radioRejectCategory"]:checked').val();
            const selectNGStation = $('#selectNGStation').val();
            const ngSymptom = $('#ng_symptom').val();


            const datas = {
                _token: '{{ csrf_token() }}',
                repairCat,
                serialNum,
                selectCustomer,
                selectModelCustomer,
                rejectCategory,
                selectNGStation,
                ngSymptom
            }

            $.ajax({
                url: '{{ route('simpanrepair') }}',
                data: datas,
                method: 'POST',
                dataType: 'json',
                beforeSend: () => {

                },
                success: res => {
                    console.log(res);
                }
            })


        });
    </script>

@endsection
