<header>
    <div class="navbars">
        <ul class="navbars-nav {{ Route::current()->getName() == 'login' ? 'ms-3' : '' }}">
            @if (Route::current()->getName() != 'login')
                <li class="navs-item">
                    <a class="navs-link">
                    </a>
                </li>
            @endif
            <li class="navs-item">
                <img src="{{ asset('img/sn.png') }}" alt="sat logo" class="logo" />
            </li>
        </ul>
        <div class="navbars__title">
            MySatnusa Admin
        </div>

        {{-- navbar --}}
        @if (Route::current()->getName() != 'login')
                        <ul class="navbars-nav navs-right"> 
                            <li class="navs-item">
                                <div class="avt dropdowns">
                                    <i class='mb-2 bx bxs-bell' alt="Sat Nusapersada" class="dropdowns-toggle" data-toggle="" style="margin-right: 50px; top: 10px;"></i>
                                  <span class="mt-3">{{ $userInfo->username }}</span>
                                    <img src="{{ asset('img/user.png') }}" alt="Sat Nusapersada"
                                        class="dropdowns-toggle" data-toggle="user-menu" />  
                                    <ul id="user-menu" class="dropdowns-menu">
                                      <li class="dropdowns-menu-item">
                                        <a href="{{ route('profile') }}" class="dropdowns-menu-link">
                                            <div>
                                              <i class='bx bxs-user-circle'></i>
                                            </div>
                                            <span>Profil</span>
                                        </a>
                                    </li>
                                    <li class="dropdowns-menu-item">
                                      <a href="{{ route('resetpasswd') }}" class="dropdowns-menu-link">
                                          <div>
                                            <i class='bx bxs-cog' ></i>
                                          </div>
                                          <span>Pengaturan Akun</span>
                                      </a>
                                  </li>
                                        <li class="dropdowns-menu-item">
                                            <a href="{{ route('auth.logout') }}" class="dropdowns-menu-link">
                                                <div>
                                                    <i class='bx bx-log-out'></i>
                                                </div>
                                                <span>Keluar</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
        @endif
        {{-- end navbar --}}
    </div>
</header>

@if (Route::current()->getName() != 'login')
    <div class="sidebar opens">
        <ul class="nav-links">
            <li>
                <a href="{{ route('login') }}">
                    <i>
                        <img src="{{ asset('img/dashboard.png') }}">
                    </i>
                    <span class="link_name">Dashboard</span>
                </a>
                <ul class="sub_menu blank">
                    <li><a class="link_name" href="{{ route('dashboard') }}">Dashboard</a></li>
                </ul>
            </li>
            <li>
                <div class="icon-link">
                    <a href="">
                        <i>
                            <img src="{{ asset('img/employee.png') }}">
                        </i>
                        <span class="link_name">Karyawan</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub_menu">
                    <li><a href="{{ route('list') }}">List Karyawan</a></li>
                    <li><a href="{{ route('grup') }}">Grup Karyawan</a></li>
                    <li><a href="{{ route('pkb') }}">Grup PKB</a></li>
                </ul>
            </li>
            <li>
                <div class="icon-link">
                    <a href="">
                        <i>
                            <img src="{{ asset('img/device.png') }}">
                        </i>
                        <span class="link_name">Device Manager</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub_menu">
                    <li><a href="{{ route('mms') }}">Mobile Management System</a></li>
                    <li><a href="{{ route('lms') }}">Laptop Management System</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('pemberitahuan') }}">
                    <i>
                        <img src="{{ asset('img/notification.png') }}">
                    </i>
                    <span class="link_name">Pemberitahuan</span>
                </a>
                <ul class="sub_menu blank">
                    <li><a class="link_name" href="#">Pemberitahuan</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('kritik') }}">
                    <i>
                        <img src="{{ asset('img/kritik.png') }}">
                    </i>
                    <span class="link_name">Kritik dan Saran</span>
                </a>
            </li>
            <li>
                <a href="{{ route('loker') }}">
                    <i>
                        <img src="{{ asset('img/job.png') }}">
                    </i>
                    <span class="link_name">Lowongan Kerja</span>
                </a>
                <ul class="sub_menu blank">
                    <li><a class="link_name" href="#">Lowongan Kerja</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('peran') }}">
                    <i>
                        <img src="{{ asset('img/user-role.png') }}">
                    </i>
                    <span class="link_name">Peran Pengguna</span>
                </a>
                <ul class="sub_menu blank">
                    <li><a class="link_name" href="#">Peran Pengguna</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('pengaduan') }}">
                    <i>
                        <img src="{{ asset('img/speaking-head.png') }}">
                    </i>
                    <span class="link_name">Pengaduan Pelanggaran</span>
                </a>
                <ul class="sub_menu blank">
                    <li><a class="link_name" href="#">Pengaduan Pelanggaran</a></li>
                </ul>
            </li>
        </ul>
    </div>
@endif
