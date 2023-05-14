@extends('layouts.app')
@section('title', 'Group PKB')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">
            <div class="row me-3">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Profil Karyawan
                    </p>
                </div>
                <a href="{{ route('pkb') }}" style="color: #101010; text-decoration: none;">
                    <i class='bx bx-chevron-left' style='color:#101010; font-size: 1.2rem; vertical-align: middle;' ></i>
                    <span style="vertical-align: middle;">Kembali</span>
                  </a>
                    <div style="width: 80%;" >
                        <div class="modal-content">
                            <div class="modal-body mt-3">
                                <form id="formInputRepair" style="font-size: 14px;">
                                    <div class="row mb-3">
                                        <div class="col-sm-5">
                                            <p>Nama Grup PKB</p>
                                                <select class="form-select" id="selectCustomer" name="selectCustomer"
                                                    style="font-size: 12px;">
                                                    <option value="">Ketik Nama Grup PKB</option>
                                                </select>
                                        </div>
    
                                        <div class="col-sm-5">
                                            <p>Tambah Anggota Grup PKB</p>
                                                <select class="form-select" id="selectNGStation" name="selectNGStation"
                                                    style="font-size: 12px;">
                                                    <option value="">ketik No Karyawan</option>
                                                </select>
                                        </div>
                                    </div>
    
                                    <div class="row mb-3" style="font-size: 12px;">
                                        <div class="col-sm-10">
                                            <p>Deskripsi Grup</p>
                                            <input type="text" class="form-control" style="font-size: 12px; height: 80px;"
                                                placeholder="Ketikkan Deskripsi Grup" name="ng_symptom" id="ng_symptom">
                                        </div>
                                    </div>
                                    <div class="row mb-3" style="font-size: 12px; display: flex; flex-direction: row;">
                                        <div class="col-sm-10">
                                            <p>Unggah Berkas</p>
                                            <input type="file" class="form-control-file" style="font-size: 12px;"
                                                name="ng_symptom" id="ng_symptom">
                                        </div>
                                    </div>
                                    
    
                                </form>
    
                            </div>
                        </div>
                    </div>

                <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                    <span style="font-size: 12px;">Menampilkan 10 dari 10 Karyawan</span>
                </div>

                <div class="col-sm-12 mt-1">
                    <table class="table table-responsive table-hover" style="max-width: 670px;">
                        <thead>
                            <tr style="color: #CD202E; height: -10px;" class="table-danger">
                                <th class="p-3" scope="col">No Karyawan</th>
                                <th class="p-3" scope="col">Nama Anggota Grup</th>
                                <th class="p-3" scope="col">Posisi</th>
                                <th class="p-3" scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="color: gray;">
                                <th class="p-3">123456</th>
                                <td class="p-3">Oliver Bierhof</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-3" style="color: red; cursor: pointer;">
                                    <a href="#">
                                        <img src="{{ asset('img/trash.png') }}">
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <th class="p-3">789012</th>
                                <td class="p-3">Muhammad Ridwan</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-3" style="color: red; cursor: pointer;">
                                    <a href="#">
                                        <img src="{{ asset('img/trash.png') }}">
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <th class="p-3">345678</th>
                                <td class="p-3">Erina Angelica</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-3" style="color: red; cursor: pointer;">
                                    <a href="#">
                                        <img src="{{ asset('img/trash.png') }}">
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <th class="p-3">901234</th>
                                <td class="p-3">Annie Leonhart</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-3" style="color: red; cursor: pointer;">
                                    <a href="#">
                                        <img src="{{ asset('img/trash.png') }}">
                                    </a>
                                </td>
                            </tr>
                            <tr style="color: gray;">
                                <th class="p-3">567890</th>
                                <td class="p-3">David Zakaria</td>
                                <td class="p-3">HRD Staff</td>
                                <td class="p-3" style="color: red; cursor: pointer;">
                                    <a href="#">
                                        <img src="{{ asset('img/trash.png') }}">
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: center; align-items: center; padding-left: 170px">
                    <button type="button" class="btn btn-link" style="text-decoration: none; font-size: 12px; width: 80px; height: 30px; margin-right: 10px;">Batal</button>
                    <button type="button" style="font-size: 12px; width: 80px; height: 30px;" id="btnSubmitRepair" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>

    @endsection

    @section('script')

    @endsection
