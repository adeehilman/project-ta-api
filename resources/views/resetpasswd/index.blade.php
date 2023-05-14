@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">

            <div class="row me-1">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Pengaturan Akun
                    </p>
                

                <div class="col-sm-12 d-flex mt-1">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="text-end col-sm-12 mt-1 rounded-3 me-3" style="display: inline-block;">
                        </div>
                    </div>
                    <div class="card-body p-1">
                        <span style="font-size: 20px;">Informasi Karyawan</span>
                        <form action="#" method="POST" id="formResetPasswd">
                            @csrf
                            <div class="form-group mb-3">
                                <label for=""><span style="font-size: 12px;">Password Sekarang</span></label>
                                <input type="password" name="password" id="password" class="form-control" style="font-size: 12px;" placeholder="Password sekarang">
                                <div class="invalid-feedback"></div>
                            </div>
        
                            <div class="form-group mb-3">
                                <label for=""><span style="font-size: 12px;">Password Baru</span></label>
                                <input type="password" name="password" id="password" class="form-control" style="font-size: 12px;" placeholder="Masukkan password paru">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for=""><span style="font-size: 12px;">Konfirmasi Password</span></label>
                                <input type="password" name="password" id="password" class="form-control" style="font-size: 12px;" placeholder="Masukkan password baru sekali lagi">
                                <div class="invalid-feedback"></div>
                            </div>
    
                            <div class="mb-3 d-grid">
                                <button type="submit" id="btnSubmit" class="btn btn-primary">Simpan</button>
                            </div>
    
                        </form>
                    </div>
                </div>
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
