@extends('layouts.app')
@section('title', 'Anggota Departemen')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">
            <div class="row me-3">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Anggota Grup PKB1
                    </p>
                </div>
                <a href="{{ route('pkb') }}" style="color: #101010; text-decoration: none;">
                    <i class='bx bx-chevron-left' style='color:#101010; font-size: 1.2rem; vertical-align: middle;' ></i>
                    <span style="vertical-align: middle;">Kembali</span>
                  </a>
                  <div class="col-sm-12 mt-2 d-flex justify-content-between">
                    <div class="d-flex gap-1">
                        <input type="text"
                            style="width: 50px; min-width: 230px; font-size: 12px; padding-left: 30px; 
                            background-image: url('{{ asset('img/search.png') }}'); background-repeat: no-repeat; 
                            background-position: left center;"
                            class="form-control rounded-3" placeholder="Cari Group Karyawan">
                        <button id="btnModalRepair" style="font-size: 12px;" type="button"
                            class="btn btn-outline-danger rounded-3">
                            <i class='bx bx-slider p-1'></i>
                            Filter
                        </button>
                    </div>
                </div>   

                <div class="text-end col-sm-9 d-flex mt-2 mb-2 rounded-3">
                    <span style="font-size: 12px;">Menampilkan 7 dari 50 Anggota Grup PKB1 </span>
                </div>

                <div class="col-sm-12 mt-1">
                    <table class="table table-responsive table-hover" style="max-width: 670px;">
                        <thead>
                            <tr style="color: #CD202E; height: 10px;" class="table-danger">
                                <th class="p-3" scope="col">Nama Lengkap Karyawan</th>
                                <th class="p-3" scope="col">No Karyawan</th>
                                <th class="p-3" scope="col">PT</th>
                                <th class="p-3" scope="col">Department</th>
                                <th class="p-3" scope="col">Line Code</th>
                                <th class="p-3" scope="col">Mulai Kerja</th>
                                <th class="p-3" scope="col">Regis MySatnusa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Anderson</th>
                                <td class="p-3">000001</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">GAD</td>
                                <td class="p-3">DR11-2A</td>
                                <td class="p-3">1 Juni 1990</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/vector.png') }}">
                                    Tidak Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Anne Forger</th>
                                <td class="p-3">000002</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">Shipping & Store Loading</td>
                                <td class="p-3">DR14-2A</td>
                                <td class="p-3">9 Juni 2020</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Budi Irawan</th>
                                <td class="p-3">000009</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">HRD-Security & Auditor Kebersihan</td>
                                <td class="p-3">DR15-3A</td>
                                <td class="p-3">1 Agustus 2018</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Alex Silva</th>
                                <td class="p-3">000005</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">Manager</td>
                                <td class="p-3">MG11-1A</td>
                                <td class="p-3">12 Juni 2007</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Makise Kurissu</th>
                                <td class="p-3">000006</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">Manager</td>
                                <td class="p-3">MG11-2A</td>
                                <td class="p-3">1 Februari 2000</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Muhammad Rahmat</th>
                                <td class="p-3">000010</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">SMT</td>
                                <td class="p-3">SM11-1C</td>
                                <td class="p-3">1 April 2006</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/checklist.png') }}">
                                    Terdaftar
                                </td>
                            </tr>
                            <tr style="color: gray;" data-href="{{ route('profil') }}">
                                <th class="p-3">Nanda</th>
                                <td class="p-3">000009</td>
                                <td class="p-3">PTSN</td>
                                <td class="p-3">DOT</td>
                                <td class="p-3">DR10-9A</td>
                                <td class="p-3">9 Oktober 2000</td>
                                <td class="p-3">
                                    <img src="{{ asset('img/vector.png') }}">
                                    Tidak Terdaftar
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endsection

    @section('script')

    @endsection
