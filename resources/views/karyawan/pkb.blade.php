@extends('layouts.app')
@section('title', 'Grup PKB')

@section('content')

<div class="wrappers">
    <div class="wrapper_content">

 <!-- modified modal -->
 <div class="modal fade" data-bs-backdrop="static" id="modalRepairData" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Pencarian Departemen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInputRepair">
                    <div class="row mb-3">
                        <div class="col-sm-6" style="font-size: 12px;">
                            <p>PT</p>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioFunction" value="29">
                                <label class="form-check-label" for="radioFunction"
                                    style="font-size: 12px;">PTSN</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radioRejectCategory"
                                    id="radioVisual" value="30">
                                <label class="form-check-label" for="radioVisual" style="font-size: 12px;">SM
                                    Engineering</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-12">
                          <p>Line Code</p>
                          <select class="form-select" id="selectCustomer" name="selectCustomer" style="font-size: 12px;">
                            <option value="">Masukkan atau Pilih Line Code</option>
                          </select>
                        </div>
                    </div>
                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-sm-6">
                            <p>Jumlah Anggota</p>
                            <input type="text" class="form-control" style="font-size: 12px;"
                                placeholder="Min" name="ng_symptom" id="ng_symptom">
                        </div>
                        <div class="col-sm-6">
                            <p>&nbsp;</p>
                            <input type="text" class="form-control" style="font-size: 12px;"
                                placeholder="Max" name="ng_symptom" id="ng_symptom">
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; font-size: 12px; width: 240px; height: 30px;">Batal</button>
                <button type="button" style="font-size: 12px; width: 260px; height: 30px;" id="btnSubmitRepair" class="btn btn-primary">Tampilkan Hasil</button>
              </div>
        </div>
    </div>
</div>
<!-- end modal -->  
        <div class="row me-3">
            <div class="col-sm-6">
                <p class="h4 mt-6">
                    Grup Perjanjian Kerja Bersama
                </p>
            </div>
    
            <div class="col-sm-12 mt-2 d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <input type="text" style="width: 50px; min-width: 150px; font-size: 12px; padding-left: 30px; 
                    background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; 
                    background-position: left center;" class="form-control rounded-3" placeholder="Cari Grup PKB">
                    <button id="btnModalRepair" style="font-size: 12px;" type="button" class="btn btn-outline-danger rounded-3">
                        <i class='bx bx-slider p-1'></i>
                        Filter
                    </button> 
                </div>       
                <div class="d-flex gap-1">
                    <a href="{{ route('addpkb') }}" class="btn btn-danger rounded-3 align-self-center" style="font-size: 12px;">
                        <i class="bi bi-plus-circle-fill me-1"></i>
                        Tambah Grup PKB
                      </a>
                      
                    
                </div>
            </div>
            
            <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                <span style="font-size: 12px;">Menampilkan 10 dari 300 Grup PKB</span>
            </div>

            <div class="col-sm-12 mt-1">
                <table class="table table-responsive table-hover" style="max-width: 605px;">
                    <thead>
                        <tr style="color: #CD202E; height: -10px;" class="table-danger">
                            <th class="p-3" scope="col">Nama Grup PKB</th>
                            <th class="p-3" scope="col">Jumlah Anggota</th>
                            <th class="p-3" scope="col">Department</th>
                            <th class="p-3" scope="col">Dibuat Pada Tanggal</th>
                            <th class="p-3" scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="color: gray;">
                            <th class="p-3">PKB1</th>
                            <td class="p-3">10</td>
                            <td class="p-3">GA Digital</td>
                            <td class="p-3">7 April 2023</td>
                            <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </a>
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB2</th>
                            <td class="p-3">20</td>
                            <td class="p-3">GA Digital</td>
                            <td class="p-3">18 April 2023</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB3</th>
                            <td class="p-3">15</td>
                            <td class="p-3">GA Digital</td>
                            <td class="p-3">2 Maret 2023</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB4</th>
                            <td class="p-3">12</td>
                            <td class="p-3">DR</td>
                            <td class="p-3">4 Februari 2023</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB5</th>
                            <td class="p-3">17</td>
                            <td class="p-3">DR</td>
                            <td class="p-3">19 Mei 2006</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB6</th>
                            <td class="p-3">17</td>
                            <td class="p-3">DR</td>
                            <td class="p-3">12 Maret 2020</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                        <tr style="color: gray;">
                            <th class="p-3">PKB7</th>
                            <td class="p-3">16</td>
                            <td class="p-3">Molding</td>
                            <td class="p-3">21 Mei 2019</td>
                            <td class="p-3">
                                <img src="{{ asset('img/checklist.png') }}">
                                Terdaftar
                            </td>
                        </tr>
                    </tbody>

                </table>


            </div>
        </div>
    </div>
    <div class="col-sm-3 text-center align-self-center position-relative">
        <div class="d-flex flex-column align-items-center position-absolute"
            style="top: 100%; left: 380%; transform: translate(-110%, -300%);">
            <img src="{{ asset('img/hand-click.png') }}" alt="Click Icon" style="width: 100px; height: 100px;">
            <span class="h5 mt-2" style="white-space: nowrap; font-size: 14px;">Klik salah satu grup untuk melihat
                informasi user</span>
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

    // const repairCat = $('input[name="radioRepairCategory"]:checked').val();

      const repairCat   = $('input[name="radioRepairCategory"]:checked').val();
      const serialNum   = $('input[name="txSerial_number"]').val();
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