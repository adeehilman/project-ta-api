@extends('layouts.app')
@section('title', 'Profil User')

@section('content')

    <div class="wrappers">
        <div class="wrapper_content">

            <div class="row me-1">
                <div class="col-sm-6">
                    <p class="h4 mt-6">
                        Profil User
                    </p>
                

                <div class="col-sm-12 d-flex mt-1">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="text-end col-sm-12 mt-1 rounded-3 me-3" style="display: inline-block;">
                            
                        </div>
                    </div>
                    <div class="card-body p-1">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Dropdown
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <span class="dropdown-item-text">Dropdown item text</span>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        </div>
    </div>

@endsection

@section('script')

@endsection
