@extends('layouts.app')
@section('title', 'Pemberitahuan')

@section('content')

<div class="wrappers">
    <div class="wrapper_content">

 <!-- modified modal Filter -->
 {{-- <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Pencarian Pemberitahuan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInputRepair" style="font-size: 14px;">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <p>PT</p>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioFunction" value="29">
                                <label class="form-check-label" for="radioFunction">PTSN</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioVisual" value="30">
                                <label class="form-check-label" for="radioVisual">SM Engineering</label>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <p>Department</p>
                            <select class="form-select" id="selectNGStation" name="selectNGStation"
                                style="font-size: 12px;">
                                <option value="">Masukkan atau Pilih Departement</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Line Code</p>
                            <select class="form-select" id="selectCustomer" name="selectCustomer"
                                style="font-size: 12px;">
                                <option value="">Masukkan atau Pilih Line Code</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <p>Status</p>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioFunction" value="29">
                                <label class="form-check-label" for="radioFunction">Sudah Diumumkan</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioVisual" value="30">
                                <label class="form-check-label" for="radioVisual">Belum Diumumkan</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3" style="font-size: 12px; display: flex; flex-direction: row;">
                        <div class="col-sm-12">
                            <p>Rentang Terakhir</p>
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

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal"
                    style="text-decoration: none; font-size: 12px; width: 300px; height: 30px;">Batal</button>
                <button type="button" style="font-size: 12px; width: 330px; height: 30px;"
                    id="btnSubmitRepair" class="btn btn-primary">Tampilkan Hasil</button>
            </div>

        </div>
    </div>
</div>
<!-- end modal Filter -->  --}}

 <!-- modified modal Buat -->
 <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Pemberitahuan Baru</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInputRepair" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Judul</p>
                            <input type="text" class="form-control" style="font-size: 12px;"
                                placeholder="Masukkan Judul" required name="judul" id="judul">
                        </div>
                        <div class="col-sm-6">
                            <p>Deskripsi</p>
                            <input type="text" class="form-control" style="font-size: 12px; height: 80px;"
                                placeholder="Masukkan Deskripsi" required name="deskripsi" id="deskripsi">
                        </div>
                    </div>

                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Penerima</p>
                            <select class="form-select" id="penerima" name="penerima"
                                style="font-size: 12px;">
                                <option value="">Ketik atau Pilih Penerima Grup PKB</option>
                                @foreach ($pemberitahuan->grup_karyawan as $grupKaryawan)
                                    <option value={{ $grupKaryawan->id_grup }}>{{ $grupKaryawan->nama_grup }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <p>Upload Gambar(PNG/JPG)</p>
                            <input type="file" class="form-control-file" style="font-size: 12px;"
                            name="gambar" id="gambar">
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <input type="checkbox" id="is_sent_now" name="is_sent_now" checked>
                            <label for="is_sent_now">Posting Sekarang</label>
                        </div>
                       
                        <div class="col-sm-6">
                            <input type="checkbox" id="is_sent_public" name="is_sent_public" checked>
                            <label for="is_sent_public">Post Pemberitahuan untuk Publik</label>
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6 d-none" id="waktu_pemberitahuan_container">
                          <label for="waktu_pemberitahuan">Waktu Pemberitahuan</label>
                          <input type="date"  class="form-control" id="waktu_pemberitahuan" name="waktu_pemberitahuan">
                        </div>
                        <div class="col-sm-6 d-none" id="jam_pemberitahuan_container">
                          <label for="jam_pemberitahuan">Jam Pemberitahuan</label>
                          <input type="time"  class="form-control" id="jam_pemberitahuan" name="jam_pemberitahuan">
                        </div>
                      </div>
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                </form>

            </div>
            <div class="modal-footer d-flex flex-nowrap justify-content-end">
                <button type="button" class="btn btn-link d-inline-block" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 105px; height: 30px;">Batal</button>
                <button type="button" style="font-size: 12px; width: 190px; height: 30px;" id="btnSubmitRepair" class="btn btn-primary d-inline-block">Simpan Perubahan</button>
              </div>
        </div>
    </div>
</div>
<!-- end modal Buat -->

        <div class="row me-3">
            <div class="col-sm-6">
                <p class="h4 mt-6">
                    Pemberitahuan
                </p>
            </div>
            <div class="col-sm-12 mt-2 d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <input type="text" style="width: 50px; min-width: 180px; font-size: 12px; padding-left: 30px; 
                    background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; 
                    background-position: left center;" class="form-control rounded-3" placeholder="Cari Pemberitahuan">
                    <button id="" style="font-size: 12px;" type="button" class="btn btn-outline-danger rounded-3">
                        <i class='bx bx-slider p-1'></i>
                        Filter
                    </button> 
                </div>       
                <div class="d-flex gap-1">
                    <button id="btnModalRepair" class="btn btn-danger rounded-3" type="button" style="font-size: 12px;">
                        <i class="bi bi-plus-circle-fill me-1"></i>
                        Buat Pemberitahuan Baru
                    </button>
                </div>
            </div>
            
            <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                <span style="font-size: 12px;" id="textJumlahTampilan">
                     Menampilkan {{count($pemberitahuan)}} dari total {{$pemberitahuan->total()}} Pemberitahuan 
                </span>
            </div>

            <div class="col-sm-12 mt-1">
                <table class="table table-responsive table-hover" style="max-width: 1000px;">
                    <thead>
                        <tr style="color: #CD202E; height: -10px;" class="table-danger">
                            <th class="p-3" scope="col">Judul</th>
                            <th class="p-3" scope="col">Penerima</th>
                            <th class="p-3" scope="col">Pengirim</th>
                            <th class="p-3" scope="col">Waktu Pemberitahuan</th>
                            <th class="p-3" scope="col">Gambar</th>
                            <th class="p-3" scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tabelPemberitahuan">
                        @foreach ($pemberitahuan as $data)
                            <tr style="color: gray;" class="table-row" data-id={{ $data->id }}>
                                <td class="p-3">{{ $data->judul }}</td>
                                <td class="p-3">{{ $data->pengirim->fullname }}</td>
                                <td class="p-3">{{ $data->penerima->nama_grup }}</td>
                                <td class="p-3">{{ date('d M Y',strtotime($data->waktu_pemberitahuan)) }}</td>
                                <td class="p-3">{{ $data->penerima->file_upload }}</td>
                                <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                    Sudah Diumumkan
                                </a>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>

                </table>


            </div>

            {{ $pemberitahuan->links() }}
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
  const tabelPemberitahuan = $('#tabelPemberitahuan');
  const textJumlahTampilan = $('#textJumlahTampilan');
  const tableRow = $('.table-row');
  const deskripsi = $('#deskripsi');
  const penerima = $('#penerima');
  const token = $('#_token');
  const judul = $('#judul');
  const isSentPublic   = $('input[name="is_sent_public"]');
  const isSentNow   = $('input[name="is_sent_now"]');
  const waktuPemberitahuan   = $('#waktu_pemberitahuan');
  const jamPemberitahuan   = $('#jam_pemberitahuan');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  function displayError(errors, dom, errorProp) {
    const error = errors[errorProp]?.map(message => `<span class="text-danger p-2 errorText">${message}</span>`).join('');

    if (error) {
        dom.after(`${error}`);
    }
  }

  tableRow.on('click', function() {
    let id = $(this).data('id');
    window.location = `/pemberitahuan/${id}`
  });

  isSentNow.on('click', function() {
    $('#waktu_pemberitahuan_container').toggleClass('d-none');
    $('#jam_pemberitahuan_container').toggleClass('d-none');
  });



    // $(document).ready(function() {
    //     $.ajax({
    //         url: '/api/pemberitahuan/list', // replace with your API endpoint
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             // handle successful response
    //             const {total, data} = response.data;

    //             // update text total tampilan
    //             textJumlahTampilan.text(`Menampilkan ${data.length} dari ${total} Pemberitahuan`);

    //             // update isi tabel
    //             const {}
    //             const newRow = data.map(notification => {
    //             return `
    //             <tr style="color: gray;">
    //                 <td class="p-3">${data.judul}</td>
    //                 <td class="p-3">${data.penerima}</td>
    //                 <td class="p-3">HR General Affair</td>
    //                 <td class="p-3">27 Maret 2023</td>
    //                 <td class="p-3">Gambar.png</td>
    //                 <td class="p-3">
    //                     <img src="{{ asset('img/checklist.png') }}">
    //                     Sudah Diumumkan
    //                     </a>
    //                 </td>
    //             </tr>
    //             `
    //             })   
    //         },
    //         error: function(xhr, status, error) {
    //         // handle error response
    //         console.log(xhr.responseText);
    //         }
    //     });
    // });

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
        if(res.status === 200){
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
    if(value){

      $.ajax({
        url: '{{ route('modellist') }}', 
        method: 'post', 
        data: { 
          _token: '{{ csrf_token() }}',
          values: value
        }, 
        dataType: 'json', 
        success: (res) => {

          if(res.status === 200){

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
    if(value){
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
          
          if(res.status === 200){

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

    const input = $('input[type="file"]')[0];
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const image = e.target.result;
        }
        reader.readAsDataURL(file);
    }


      const formData = new FormData();
      formData.append('image', file);
      formData.append('_token', token.val());
      formData.append('judul', judul.val());
      formData.append('deskripsi', deskripsi.val());
      formData.append('penerima', penerima.val());
      formData.append('isSentPublic', isSentPublic.is(':checked'));
      formData.append('isSentNow', isSentNow.is(':checked'));
      formData.append('waktu_pemberitahuan', waktuPemberitahuan.val() );
      formData.append('jam_pemberitahuan', jamPemberitahuan.val() );

      $.ajax({
      url: '/pemberitahuan', 
      data: formData, 
      method: 'POST', 
      dataType: 'json', 
      contentType: false,
      processData: false,
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        
    },
      beforeSend: () => {

      }, 
      success: res => {
            location.reload();
            // const newNotification = res.data;
            // const newTableRow = `
            // <tr style="color: gray;" class="table-row" data-id="${newNotification.id}">
            //     <td class="p-3">${newNotification.judul }</td>
            //     <td class="p-3">${newNotification.pengirim.fullname}</td>
            //     <td class="p-3">${newNotification.penerima.nama_grup}</td>
            //     <td class="p-3">${newNotification.waktu_pemberitahuan}</td>
            //     <td class="p-3">${newNotification.file_upload }</td>
            //     <td class="p-3">
            //         <img src="{{ asset('img/checklist.png') }}">
            //             Sudah Diumumkan
            //         </a>
            //     </td>
            // </tr>
            // `;

            // tabelPemberitahuan.append(newTableRow);
      },
      error: res => {
        const errorText = $('.errorText').remove();
        if (res.status === 422) {
            displayError(res.responseJSON.messages, judul, 'judul');
            displayError(res.responseJSON.messages, deskripsi, 'deskripsi');
            displayError(res.responseJSON.messages, penerima, 'penerima');

            // console.log(res);
        }
      }
    })


  });


</script>

@endsection