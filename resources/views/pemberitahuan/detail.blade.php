@extends('layouts.app')
@section('title', 'Informasi Pemberitahuan')

@section('content')
<div class="wrappers">
  <div class="wrapper_content">
    <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
          <div class="modal-content">
              <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Pemberitahuan</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <form id="formInputRepair" enctype="multipart/form-data">
                      @csrf
                      @method('PUT')
                      <div class="row mb-3" style="font-size: 12px;">
                          <div class="col-sm-6">
                              <p>Judul</p>
                              <input type="text" value="{{ $pemberitahuan->judul }}" class="form-control" style="font-size: 12px;"
                                  placeholder="Masukkan Judul" required name="judul" id="judul">
                          </div>
                          <div class="col-sm-6">
                              <p>Deskripsi</p>
                              <input type="text" value="{{ $pemberitahuan->deskripsi }}" class="form-control" style="font-size: 12px; height: 80px;"
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
                                      <option value="{{ $grupKaryawan->id_grup }}" {{ $pemberitahuan->receive_by == $grupKaryawan->id_grup ? 'selected' : '' }}>{{ $grupKaryawan->nama_grup }}</option>
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
                              <input type="checkbox" id="isSentNow" name="is_sent_now">
                              <label for="is_sent_now">Posting Sekarang</label>
                          </div>
                          <div class="col-sm-6">
                              <input type="checkbox" id="isSentPublic" name="is_sent_public" 
                              {{ $pemberitahuan->is_sent_public ? 'checked' : '' }}>
                              <label for="is_sent_public">Post Pemberitahuan untuk Publik</label>
                          </div>
                      </div>
                      <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6 " id="waktu_pemberitahuan_container">
                          <label for="waktu_pemberitahuan">Waktu Pemberitahuan</label>
                          <input type="date" value="{{ date('Y-m-d', strtotime($pemberitahuan->waktu_pemberitahuan)) }}" class="form-control" id="waktu_pemberitahuan" name="waktu_pemberitahuan">
                        </div>
                        <div class="col-sm-6 " id="jam_pemberitahuan_container">
                          <label for="jam_pemberitahuan" >Jam Pemberitahuan</label>
                          <input type="time" value="{{ date('H:i', strtotime($pemberitahuan->waktu_pemberitahuan)) }}" class="form-control" id="jam_pemberitahuan" name="jam_pemberitahuan">
                        </div>
                      </div>
                      <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                  </form>
  
              </div>
              <div class="modal-footer d-flex flex-nowrap justify-content-end">
                  <button type="button" class="btn btn-link d-inline-block" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 105px; height: 30px;">Batal</button>
                  <button type="button" style="font-size: 12px; width: 190px; height: 30px;" id="btnSubmitUpdate" class="btn btn-primary d-inline-block">Simpan Perubahan</button>
                </div>
          </div>
      </div>
    </div>

    <h3>Informasi Pemberitahuan</h3>
    <a class="btn" href="/pemberitahuan" role="button">< Kembali ke Pemberitahuan</a>

    {{-- grid with 2 columns --}}
    <div class="container text-center mt-2">
      <div class="row">
        <div class="col p-3 border border-secondary rounded me-3">
          <h5 class="text-start">Informasi Pemberitahuan</h5>
          <img src="{{ asset('img/image 2.png') }}" class="img-fluid">

          <table class="mt-4">
            <tr>
              <td class="text-start text-secondary" >Judul</td>
              <td class="text-start ps-4" width="50%">{{ $pemberitahuan->judul }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Sub Judul</td>
              <td class="text-start ps-4"> {{ $pemberitahuan->sub_judul  }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Penerima</td>
              <td class="text-start ps-4"> {{ $pemberitahuan->penerima->fullname  }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Waktu Pemberitahuan</td>
              <td class="text-start ps-4">{{ date('d M y', strtotime($pemberitahuan->waktu_pemberitahuan)) }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Dibuat Oleh</td>
              <td class="text-start ps-4"> {{ $pemberitahuan->pembuat->fullname }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Diperbaharui Oleh</td>
              <td class="text-start ps-4"> {{ $pemberitahuan->pengubah->fullname }}</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Status</td>
              <td class="text-start ps-4">Sudah Diumumkan</td>
            </tr>
            <tr>
              <td class="text-start text-secondary" >Deskripsi</td>
            </tr>
            <tr>
              <td class="text-start" >{{ $pemberitahuan->deskripsi }}</td>
            </tr>
          </table>

          <div class="container p-0 mt-4 d-flex justify-content-start">
            <button class="btn border me-2 align-items-baseline" id="btnModal">
              <img src={{ asset('img/edit.png') }} style="vertical-align:middle;"></img>
                Edit Pemberitahuan
              </button>
            <button class="btn border">
              <img src={{ asset('img/trash.png') }}></img>
              Hapus</button>
          </div>
        </div>
        <div class="col p-3 border border-secondary rounded">
          <h5 class="text-start">Penerima</h5>
          <table class="table table-responsive table-hover" style="max-width: 1000px;">
            <thead>
                <tr style="color: #CD202E; height: -10px;" class="table-danger">
                    <th class="p-3 text-start" scope="col">Nama Lengkap Karyawan</th>
                    <th class="p-3 text-start" scope="col">Terkirim</th>
                    <th class="p-3 text-start" scope="col">Dibaca</th>
                </tr>
            </thead>
            <tbody>
              @foreach ($pemberitahuan->semuaPenerima as $penerima)
                <tr>
                  <td>{{ $penerima->receiver->fullname }}</td>
                  <td>
                    <img src="{{ $penerima->is_sent ? asset('img/checklist.png') : asset('img/vector.png')}}">
                    </img>
                  </td>
                  <td>
                    <img src="{{ $penerima->is_sent ? asset('img/checklist.png') : asset('img/vector.png')}}">
                  </img>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
  <script>
    const btnModal = $('#btnModal');
    const modalForm = $('#modalRepairData');
    const btnSubmitUpdate = $('#btnSubmitUpdate');
    const deskripsi = $('#deskripsi');
    const penerima = $('#penerima');
    const token = $('#_token');
    const judul = $('#judul');
    const isSentNow = $('#isSentNow');
    const isSentPublic   = $('#isSentPublic');
    const waktuPemberitahuan   = $('#waktu_pemberitahuan');
    const jamPemberitahuan   = $('#jam_pemberitahuan');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const pemberitahuan = {!! json_encode($pemberitahuan) !!};

    btnModal.on('click', function(e) {
      e.preventDefault();
      modalForm.modal('show');

    });

    isSentNow.on('click', function() {
      $('#waktu_pemberitahuan_container').toggleClass('d-none');
      $('#jam_pemberitahuan_container').toggleClass('d-none');
    });

    btnSubmitUpdate.on('click', function(e) {
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
      formData.append('_method', 'PUT');
      formData.append('image', file);
      formData.append('_token', token.val());
      formData.append('judul', judul.val());
      formData.append('deskripsi', deskripsi.val());
      formData.append('penerima', penerima.val());
      formData.append('isSentPublic', isSentPublic.is(':checked'));
      formData.append('isSentNow', isSentNow.is(':checked'));
      formData.append('waktu_pemberitahuan', waktuPemberitahuan.val() );
      formData.append('jam_pemberitahuan', jamPemberitahuan.val() );


      // const datas = {
      //   _token: token.val(),
      //   judul: judul.val(),
      //   deskripsi: deskripsi.val(),
      //   penerima: penerima.val(),
      //   isSentPublic: isSentPublic.is(':checked'),
      //   isSentNow: isSentNow.is(':checked'),
      //   waktu_pemberitahuan: waktuPemberitahuan.val(),
      //   jam_pemberitahuan: jamPemberitahuan.val(),
      // }

      $.ajax({
        url: `/pemberitahuan/${pemberitahuan.id}`, 
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
          console.log(res);
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