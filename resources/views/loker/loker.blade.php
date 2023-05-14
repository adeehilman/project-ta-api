@extends('layouts.app')
@section('title', 'Lowongan Kerja')

@section('content')

<div class="wrappers">
    <div class="wrapper_content">

 <!-- modified modal Filter -->
 <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Pencarian Lowongan Kerja</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInputRepair">
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-8">
                            <p>Posisi</p>
                            <select class="form-select" id="selectCustomer" name="selectCustomer"
                                style="font-size: 12px;">
                                <option value="">Masukkan atau Pilih Posisi</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-8">
                            <p>Berlaku Sampai</p>
                            <input type="date" class="form-control me-2 rounded-3" style="width: 335px; font-size: 12px;">
                        </div>

                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 240px; height: 30px;">Batal</button>
                <button type="button" style="font-size: 12px; width: 260px; height: 30px;" id="btnSubmitAdd" class="btn btn-primary">Tambahkan Lowongan</button>
              </div>
        </div>
    </div>
</div>
<!-- end modal Filter --> 

 <!-- modified modal Tambah Loker-->
 <div class="modal fade" data-bs-backdrop="static" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Lowongan Kerja</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInputRepair" enctype="multipart/form-data">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Posisi</p>
                            <input type="text" class="form-control" style="font-size: 12px;"
                                placeholder="Ketikkan Posisi Lowongan Kerja" name="posisi" id="posisi">
                        </div>
                        <div class="col-sm-6">
                            <p>Berlaku Sampai</p>
                            <input type="date" class="form-control me-2 rounded-3" style="width: 240px; font-size: 12px;" id="durasi">
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-12">
                            <p>Deskripsi</p>
                            <textarea class="form-control" style="font-size: 12px; height: 80px;" name="deskripsi" id="deskripsi">
                            </textarea>
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                      <div class="col-sm-12">
                          <p>Catatan HRD</p>
                          <textarea class="form-control" style="font-size: 12px; height: 80px;" name="catatan_hrd" id="catatan_hrd">
                          </textarea>
                      </div>
                  </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Upload Gambar(PNG/JPG)</p>
                            <input type="file" class="form-control-file" style="font-size: 12px;"
                            name="gambar" id="gambar">
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px;  height: 30px;">Batal</button>
                <button type="button" style="font-size: 12px;  height: 30px;" id="btnSubmitLoker" class="btn btn-primary">Tambahkan Lowongan</button>
              </div>
        </div>
    </div>
</div> 
<!-- end modal Buat Tambah Loker --> 

 <!-- modified modal Show Loker-->
 <div class="modal fade" data-bs-backdrop="static" id="modalShow" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 50%;">
      <div class="modal-content">
          <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Informasi Lowongan Kerja</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="modalShowBody">              
          </div>
          <div class="modal-footer d-flex justify-content-start">
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
          </div>
      </div>
  </div>
</div>


 <!-- modified modal Tambah Loker-->
 <div class="modal fade" data-bs-backdrop="static" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
      <div class="modal-content">
          <div class="modal-header">
              <h1 class="modal-title fs-5" >Edit Lowongan Kerja</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="formInputRepair" enctype="multipart/form-data">
                  <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                  <div class="row mb-3" style="font-size: 12px;">
                      <div class="col-sm-6">
                          <p>Posisi</p>
                          <input type="text" class="form-control" style="font-size: 12px;"
                              placeholder="Ketikkan Posisi Lowongan Kerja" name="posisi_edit" id="posisi_edit">
                      </div>
                      <div class="col-sm-6">
                          <p>Berlaku Sampai</p>
                          <input type="date" class="form-control me-2 rounded-3" style="width: 240px; font-size: 12px;" id="durasi_edit">
                      </div>
                  </div>
                  <div class="row mb-3" style="font-size: 12px;">
                      <div class="col-sm-12">
                          <p>Deskripsi</p>
                          <textarea class="form-control" style="font-size: 12px; height: 80px;" name="deskripsi_edit" id="deskripsi_edit">
                          </textarea>
                      </div>
                  </div>
                  <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-sm-12">
                        <p>Catatan HRD</p>
                        <textarea class="form-control" style="font-size: 12px; height: 80px;" name="catatan_hrd_edit" id="catatan_hrd_edit">
                        </textarea>
                    </div>
                </div>
                  <div class="row mb-3" style="font-size: 12px;">
                      <div class="col-sm-6">
                          <p>Upload Gambar(PNG/JPG)</p>
                          <input type="file" class="form-control-file" style="font-size: 12px;"
                          name="gambar_edit" id="gambar_edit">
                      </div>
                  </div>
              </form>

          </div>
          <div class="modal-footer d-flex justify-content-end">
              <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px;  height: 30px;">Batal</button>
              <button type="button" style="font-size: 12px;  height: 30px;" id="btnSubmitEditLoker" class="btn btn-primary">Tambahkan Lowongan</button>
            </div>
      </div>
  </div>
</div> 
<!-- end modal Buat Show Loker --> 


 <!-- modified modal hapus Loker-->
 <div class="modal fade" data-bs-backdrop="static" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
      <div class="modal-content">
          <div class="modal-header">
              <h1 class="modal-title fs-5" >Hapus Lowongan Kerja</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body d-flex flex-column justify-content-center">
              <img src={{ asset('img/warning.png') }} alt="warning" class="mx-auto"/>
              <p class="text-center my-2">Apakah kamu yakin ingin menghapus lowongan kerja kamu</p>
              <p class="text-center text-secondary my-2">Data yang dihapus tidak bisa dipulihkan kembali</p>
          </div>
          <div class="modal-footer d-flex justify-content-end">
              <button type="button" id="btnSubmitHapus" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px;  height: 30px;">Ya, Hapus</button>
              <button type="button" data-bs-dismiss="modal" style="font-size: 12px;  height: 30px;" id="btnCancelHapus" class="btn btn-primary">Batalkan</button>
            </div>
      </div>
  </div>
</div> 
<!-- end modal Buat Show Loker --> 


        <div class="row me-3">
            <div class="col-sm-6">
                <p class="h4 mt-6">
                    Lowongan Kerja
                </p>
            </div>
    
            <div class="col-sm-12 mt-2 d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <input type="text" style="width: 50px; min-width: 230px; font-size: 12px; padding-left: 30px; 
                    background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; 
                    background-position: left center;" class="form-control rounded-3" placeholder="Cari Lowongan Kerja">
                    <button id="btnModalRepair" style="font-size: 12px;" type="button" class="btn btn-outline-danger rounded-3">
                        <i class='bx bx-slider p-1'></i>
                        Filter
                    </button> 
                </div>       
                <div class="d-flex gap-1">
                    <button id="btnAdd" class="btn btn-danger rounded-3" type="button" style="font-size: 12px;">
                        <i class="bi bi-plus-circle-fill me-1"></i>
                        Tambah Lowongan Kerja
                    </button>
                </div>
            </div>
            
            <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                <span style="font-size: 12px;">
                  Menampilkan {{count($loker)}} dari total {{$loker->total()}} Lowongan 
                
                </span>
            </div>

            <div class="col-sm-12 mt-1">
                <table class="table table-responsive table-hover" style="max-width: 1000px;">
                    <thead>
                        <tr style="color: #CD202E; height: -10px;" class="table-danger">
                            <th class="p-3" scope="col">Posisi</th>
                            <th class="p-3" scope="col">Deskripsi</th>
                            <th class="p-3" scope="col">Berlaku Sampai</th>
                            <th class="p-3" scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @foreach ($loker as $item)
                        @php 
                          /* decode utk bug g bs akses status */
                          $data = json_decode($item);
                          $status = $data->status;
                        @endphp
                        <tr style="color: gray;" class="table-row" data-id={{ $item->id }}>
                              <td class="p-3">{{ $item->posisi }}</td>
                              <td class="p-3">{{ $item->desc }}</td>
                              <td class="p-3">{{ $item->durasi ? date('d M y', strtotime($item->durasi)) : '' }}</td>
                              <td class="p-3">
                                {!! ($status->deskripsi ?? null) === 'sudah diumumkan' ? 
                                      '<img src="img/checklist.png"> <span>Sudah Diumumkan</span>' 
                                      : '<img src="img/vector.png"> <span>belum diumumkan</span>' 
                                !!}
                              </td>
                              {{-- <td class="p-3">{{ $item->status->deskripsi === 'sudah diumumkan' ? '<img>' :'' }}</td> --}}
                          </tr>
                        @endforeach
                    </tbody>

                </table>

                {{ $loker->links() }}

            </div>
        </div>
    </div>

@endsection

@section('script')

<script>
  const btnModal = $('#btnModalRepair');
  const btnModalEdit = $('#btnModalEdit');
  const btnAdd = $('#btnAdd');
  const modalForm = $('#modalRepairData');
  const btnOpenModalHapus = $('#btnOpenModalHapus');
  const btnSubmitHapus = $('#btnSubmitHapus');
  const modalAdd = $('#modalAdd');
  const modalEdit = $('#modalEdit');
  const modalHapus = $('#modalHapus');
  const modalShow = $('#modalShow');
  const selectCustomer = $('#selectCustomer');
  const selectModelCustomer = $('#selectModelCustomer');
  const btnSubmitLoker = $('#btnSubmitLoker');
  const btnSubmitEditLoker = $('#btnSubmitEditLoker');
  const tableRow = $('.table-row');
  const tableBody = $('#table-body');
  const modalShowBody = $('#modalShowBody');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const token = $('#_token');
  const posisi_edit = $('#posisi_edit');
  const durasi_edit = $('#durasi_edit');
  const deskripsi_edit = $('#deskripsi_edit');
  const catatan_hrd_edit = $('#catatan_hrd_edit');
  let currentLoker = {};


  const loker = {!! json_encode($loker) !!};

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

  const getDataLoker = (id) => {
    let html = ``;

    $.ajax({
      url: `/loker/show/${id}`, 
      method: 'GET', 
      dataType: 'json',
      beforeSend: () => {

      }, 
      success: res => {
        $('#modalShowBody .child').remove();

        const data = res;
        const html = `
          <div class="child">
            <img src="${data.file_upload}" alt="image" width="100%" style="object-fit: contain;"/>

            <table width="100%" class="mt-2">
              <tr class="text-start">
                <td class="text-secondary" width="30%">Posisi</td>
                <td >${data.posisi}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" width="30%">Waktu Posting</td>
                <td >${data.posting_time}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" width="30%">Berlaku Sampai</td>
                <td >${data.durasi}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" width="30%">Dibuat Oleh</td>
                <td >${data.pembuat?.fullname ?? ''}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" width="30%">Diperbaharui Oleh</td>
                <td >${data.pengubah?.fullname ?? ''}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" width="30%">Status</td>
                <td >${data.status?.deskripsi ?? ''}</td>
              </tr>
              <tr class="text-start">
                <td class="text-secondary" colspan="2">Deskripsi</td>
              </tr>
              <tr class="text-start">
                <td  colspan="2">
                  ${data.desc}
                </td>
              </tr>
            </table>
          </div>
        `;
        modalShowBody.append(html);
        currentLoker = res;
      }

    })
  }

  tableRow.on('click', function() {
    let id = $(this).data('id');
    getDataLoker(id);
    modalShow.modal('show');

    // window.location = `/pemberitahuan/${id}`
  });

  btnAdd.click(e => {
    e.preventDefault();
    modalAdd.modal('show');
  });

  btnModal.click(e => {
    e.preventDefault();
    modalForm.modal('show');

  });

  btnModalEdit.click(e => {
    e.preventDefault();
    modalEdit.modal('show');
    posisi_edit.val(currentLoker.posisi);
    durasi_edit.val(currentLoker.durasi);
    deskripsi_edit.val(currentLoker.desc);
    catatan_hrd_edit.val(currentLoker.catatan_hrd);
  });

  btnOpenModalHapus.click(e => {
    modalHapus.modal('show');
  });

  btnSubmitHapus.click(e => {
    console.log(e);
    $.ajax({
      url: `/loker/${currentLoker.id}`, 
      method: 'DELETE', 
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      beforeSend: () => {

      }, 
      success: res => {
        console.log(res);
        location.reload();
      }
    })
  })



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

  btnSubmitLoker.click(function(e) {
    e.preventDefault();

      const posisi = $('#posisi');
      const durasi = $('#durasi');
      const deskripsi = $('#deskripsi');
      const catatan_hrd = $('#catatan_hrd');

      var input = $('input[type="file"]')[0];
      var file = input.files[0];

      if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          const image = e.target.result;
        }
        reader.readAsDataURL(file);
      }

      const formData = new FormData();
      formData.append('image', file);
      formData.append('_token', token.val());
      formData.append('posisi', posisi.val());
      formData.append('durasi', durasi.val());
      formData.append('deskripsi', deskripsi.val());
      formData.append('catatan_hrd', catatan_hrd.val());

    $.ajax({
      url: '/loker', 
      data: formData, 
      method: 'POST', 
      dataType: 'json', 
      contentType: false,
      processData: false,
      beforeSend: () => {

      }, 
      success: res => {
        console.log(res);
        location.reload();
      }
    })


  });

  btnSubmitEditLoker.click(function(e) {
    var input = $('input[type="file"]')[0];
    var file = input.files[0];

    if (file) {
      var reader = new FileReader();
      reader.onload = function(e) {
        const image = e.target.result;
      }
      reader.readAsDataURL(file);
    }
    console.log(e);

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('image', file);
    formData.append('_token', token.val());
    formData.append('posisi', posisi_edit.val());
    formData.append('durasi', durasi_edit.val());
    formData.append('deskripsi', deskripsi_edit.val());
    formData.append('catatan_hrd', catatan_hrd_edit.val());

    $.ajax({
      url: `/loker/update/${currentLoker.id}`, 
      data: formData, 
      method: 'POST', 
      dataType: 'json', 
      contentType: false,
      processData: false,
      beforeSend: () => {

      }, 
      success: res => {
        console.log(res);
        location.reload();
      }
    })
  })


</script>

@endsection