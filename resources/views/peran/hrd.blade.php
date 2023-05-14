@extends('layouts.app')
@section('title', 'Tambah Grup PKB')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">
            <div class="row me-3">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Peran Pengguna
                    </p>
                </div>
                <div class="col-sm-8 mt-2 d-flex justify-content-between">
                    <div class="d-flex gap-1">
                            <div class="container">
                                <div class="box-prn">
                                    <div class="button-prn" id="button"></div>
                                    <button type="button" id="depart" class="toggle-btn-prn" onclick="leftClick()">HRD</button>
                                    <button type="button" id="linecode" class="toggle-btn-prn" onclick="rightClick()">Dept HRD</button>
                                    <button type="button" id="history" class="toggle-btn-prn" onclick="leftClick()">QHSE</button>
                                    <button type="button" id="response" class="toggle-btn-prn" onclick="rightClick()">Dept QHSE</button>
                                    <button type="button" id="response" class="toggle-btn-prn" onclick="rightClick()">MIS</button>
                                </div>
                        </div>
                    </div>
                </div>
                    <div style="width: 80%;" >
                        <div class="modal-content">
                            <div class="modal-body mt-3">
                                <form id="formInputRepair" style="font-size: 14px;">
                                    <div class="row mb-3">
                                        <div class="col-sm-5">
                                            <p>Tambah Peran Pengguna</p>
                                                <select class="form-select" id="selectCustomer" name="selectCustomer"
                                                    style="font-size: 12px;">
                                                    <option value="">Ketik atau Pilih Karyawan</option>
                                                </select>
                                        </div>
                                    </div>
                                </form>
    
                            </div>
                        </div>
                    </div>

                <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                    <span style="font-size: 12px;">Menampilkan 10 dari 10 Karyawan</span>
                </div>

                <div class="col-sm-7 mt-1">
                    <table class="table table-responsive table-hover" style="max-width: 700px;">
                        <thead>
                            <tr style="color: #CD202E; height: 0px; " class="table-danger">
                                <th class="p-3" scope="col">No Karyawan</th>
                                <th class="p-3" scope="col">Nama Anggota Grup</th>
                                <th class="p-3" scope="col">Posisi</th>
                                <th class="p-3" scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="color: gray;">
                                <td class="p-3">123456</td  >
                                <td class="p-3">Oliver Bierhof</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-2">
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <img src="{{ asset('img/edit.png') }}" alt="Edit">
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <img src="{{ asset('img/trash.png') }}" alt="Delete">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">789012</td>
                                <td class="p-3">Muhammad Ridwan</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-2">
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <img src="{{ asset('img/edit.png') }}" alt="Edit">
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <img src="{{ asset('img/trash.png') }}" alt="Delete">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">345678</td>
                                <td class="p-3">Erina Angelica</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-2">
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <img src="{{ asset('img/edit.png') }}" alt="Edit">
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <img src="{{ asset('img/trash.png') }}" alt="Delete">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">901234</td>
                                <td class="p-3">Annie Leonhart</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-2">
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <img src="{{ asset('img/edit.png') }}" alt="Edit">
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <img src="{{ asset('img/trash.png') }}" alt="Delete">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <td class="p-3">567890</td>
                                <td class="p-3">David Zakaria</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-2">
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <img src="{{ asset('img/edit.png') }}" alt="Edit">
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <img src="{{ asset('img/trash.png') }}" alt="Delete">
                                        </a>
                                    </div>
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
            function leftClick(){
                button.style.left = "0"
            }

            function rightClick(){
                button.style.left = "90px"
            }

        </script>
    @endsection
