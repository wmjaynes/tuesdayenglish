@extends('layouts.app')

@section('content')
    <div class="container">


        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-danger lead">
                    This site is very much a work in progress.
                    Currently it only contains dances done since September, 2018,
                    and perhaps more.
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">AACTMAD Tuesday English Country Dance</h5>
                        <h6 class="card-title">Dances Done</h6>
                    </div>
                    <div>
                        The number on the right is the number of times the dance has been called in the time period
                        displayed.
                    </div>
                    <div class="card-body">
                        <dances-by-date></dances-by-date>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
