@extends('layouts.app')
@section('title', 'Kritik dan Saran')


@section('content')

 <!-- modified modal Show Loker-->
 <div class="modal fade" data-bs-backdrop="static" id="modalShow" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Informasi Kritik dan Saran</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalShowBody">              
                <ul class="nav nav-tabs py-1 border     rounded bg-danger" id="myTab" role="tablist" style="--bs-bg-opacity: .2;">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link text-dark rounded active" id="detail-kritik-dan-saran-tab" data-bs-toggle="tab" data-bs-target="#detail-kritik-dan-saran-tab-pane" type="button" role="tab" aria-controls="detail-kritik-dan-saran-tab-pane" aria-selected="true">Detail Kritik dan Saran</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link rounded text-dark" id="informasi-pengirim-tab" data-bs-toggle="tab" data-bs-target="#informasi-pengirim-tab-pane" type="button" role="tab" aria-controls="informasi-pengirim-tab-pane" aria-selected="false">Informasi Pengirim</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link rounded text-dark" id="riwayat-status-tab" data-bs-toggle="tab" data-bs-target="#riwayat-status-tab-pane" type="button" role="tab" aria-controls="riwayat-status-tab-pane" aria-selected="false">Riwayat Status</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded text-dark" id="tanggapan-tab" data-bs-toggle="tab" data-bs-target="#tanggapan-tab-pane" type="button" role="tab" aria-controls="tanggapan-tab-pane" aria-selected="false">Tanggapan</button>
                      </li>
                  </ul>
                 
            </div>
            {{-- <div class="modal-footer d-flex justify-content-start">
                <button type="button" id="btnModalEdit" class="btn border" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 214px; height: 41px;">
                  <img src={{ asset('img/edit.png') }} style="vertical-align:middle;"></img>
                  <span>Edit Lowongan Kerja</span>
                </button>
                <button type="button" style="font-size: 12px; width: 205px; height: 41px;" id="btnSubmitRepair" class="btn border">
                  <img src={{ asset('img/share.png') }} style="vertical-align:middle;"></img>
                  <span>Bagikan Lowongan</span>
                </button>
                <button type="button" style="font-size: 12px; width: 124px; height: 41px;" id="btnOpenModalHapus" class="btn border">
                  <img src={{ asset('img/trash.png') }} style="vertical-align:middle;"></img>
                  <span>Hapus</span>
                </button>
            </div> --}}
        </div>
    </div>
  </div>

  
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
                                        <p>Kategori</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction" style="font-size: 12px;">Aplikasi</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">Perusahaan</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" style="font-size: 12px;">
                                    <div class="col-sm-12">
                                        <p>Rentang Waktu</p>
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
                                        <p>Status</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioFunction" value="29">
                                            <label class="form-check-label" for="radioFunction" style="font-size: 12px;">Belum ditanggapi</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="radioRejectCategory"
                                                id="radioVisual" value="30">
                                            <label class="form-check-label" for="radioVisual" style="font-size: 12px;">Sudah ditanggapi</label>
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
                        Kritik dan Saran
                    </p>
                </div>

                <div class="col-sm-12 mt-2 d-flex justify-content-between">
                    <div class="d-flex gap-1">
                        <input type="text"
                            style="width: 50px; min-width: 220px; font-size: 12px; padding-left: 30px; 
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
                    <span style="font-size: 12px;" id="textJumlahTampilan">
                        Menampilkan {{count($suggestion)}} dari total {{$suggestion->total()}} Pengirim 
                   </span>
                </div>

                <div class="col-sm-12 mt-1">
                    <table class="table table-responsive table-hover">
                        <thead>
                            <tr style="color: #CD202E; height: 10px;" class="table-danger">
                                <th class="p-3" scope="col">Pengirim</th>
                                <th class="p-3" scope="col">Kategori</th>
                                <th class="p-3" scope="col">Kritik dan Saran</th>
                                <th class="p-3" scope="col">Waktu Submit</th>
                                <th class="p-3" scope="col">Jam</th>
                                <th class="p-3" scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suggestion as $item)
                                @php 
                                    /* decode utk bug g bs akses status */
                                    $data = json_decode($item);
                                    $kategori = $data->kategori;
                                @endphp
                                <tr style="color: gray;" data-id={{ $item->id }} data-href="{{ route('profil') }}" class="table-row">
                                    <td class="p-3">{{ $item->pengirim->fullname ?? '' }}</td>
                                    <td class="p-3">{{ $kategori->nama ?? '' }}</td>
                                    <td class="p-3">{{ $item->description }}</td>
                                    <td class="p-3">{{ $item->created_at ? date('d M Y', strtotime($item->created_at)) : '' }}</td>
                                    <td class="p-3">{{ $item->created_at ? date('H:i', strtotime($item->created_at)) : '' }}</td>
                                    <td class="p-3">
                                        <img src="{{ asset('img/checklist.png') }}">
                                        {!! ($status->deskripsi ?? null) === 'sudah ditanggapi' ? 
                                        '<img src="img/checklist.png"> <span>Sudah Ditanggapi</span>' 
                                        : '<img src="img/vector.png"> <span>Belum Ditanggapi</span>' 
                                        !!}
                                    </td>
                                </tr>
                            @endforeach
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
        const modalShow = $('#modalShow');
        const selectCustomer = $('#selectCustomer');
        const selectModelCustomer = $('#selectModelCustomer');
        const btnSubmitRepair = $('#btnSubmitRepair');
        const tableRow = $('.table-row');

        const kritik = {!! json_encode($suggestion) !!};

        tableRow.on('click', function(e) {
            let id = $(this).data('id');
            $('#myTab').after("");

            const selectedKritik = kritik?.data?.find(item => item.id === id);
            modalShow.modal('show');

            const tabContent = $(`
                <div class="tab-content mt-2 p-2" id="myTabContent">
                    <div class="tab-pane fade show active mt-2" id="detail-kritik-dan-saran-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                        <h5>Informasi Kritik dan Saran</h5>
                        <table width="100%">
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Kategori
                                </td>
                                <td width="50%">
                                    ${selectedKritik.id}
                                </td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Waktu Submit
                                </td>
                                <td width="50%">
                                    ${new Date(selectedKritik.created_at).toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Jam
                                </td>
                                <td width="50%">
                                    ${new Date(selectedKritik.created_at).toLocaleString('en-US', {
                                        hour: 'numeric',
                                        minute: 'numeric',
                                    })}
                                </td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Status
                                </td>
                                <td width="50%">
                                </td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Kritik dan Saran
                                </td>
                                <td width="50%">
                                    ${selectedKritik.description}
                                </td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">
                                    Dokumen Pendukung
                                </td>
                                <td width="50%">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="informasi-pengirim-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                        <h5>Informasi Pengirim</h5>
                        <table>
                            <tr class="text-start">
                                <td class="text-secondary">Nama</td>
                                <td width="50%">${selectedKritik.pengirim.fullname}</td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">Badge</td>
                                <td width="50%">${selectedKritik.pengirim.employee_no}</td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">Departemen</td>
                                <td width="50%"></td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">Posisi</td>
                                <td width="50%"></td>
                            </tr>
                            <tr class="text-start">
                                <td class="text-secondary">Mulai Masuk</td>
                                <td width="50%"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="riwayat-status-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">2</div>
                    <div class="tab-pane fade" id="tanggapan-tab-pane" role="tabpanel" aria-labelledby="disabled-tab" tabindex="0">3</div>
                    </div>
            `);

            $('#myTab').after(tabContent)
        });

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

        // btnSubmitRepair.click(function(e) {
        //     e.preventDefault();

        //     // const repairCat = $('input[name="radioRepairCategory"]:checked').val();

        //     const repairCat = $('input[name="radioRepairCategory"]:checked').val();
        //     const serialNum = $('input[name="txSerial_number"]').val();
        //     const selectCustomer = $('#selectCustomer').val();
        //     const selectModelCustomer = $('#selectModelCustomer').val();
        //     const rejectCategory = $('input[name="radioRejectCategory"]:checked').val();
        //     const selectNGStation = $('#selectNGStation').val();
        //     const ngSymptom = $('#ng_symptom').val();


        //     const datas = {
        //         _token: '{{ csrf_token() }}',
        //         repairCat,
        //         serialNum,
        //         selectCustomer,
        //         selectModelCustomer,
        //         rejectCategory,
        //         selectNGStation,
        //         ngSymptom
        //     }

        //     $.ajax({
        //         url: '{{ route('simpanrepair') }}',
        //         data: datas,
        //         method: 'POST',
        //         dataType: 'json',
        //         beforeSend: () => {

        //         },
        //         success: res => {
        //             console.log(res);
        //         }
        //     })


        // });
    </script>

@endsection
