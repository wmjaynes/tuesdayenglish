@extends('layouts.app')

@section('content')
    <div class="container">


        <div class="row justify-content-center">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">AACTMAD Tuesday English Country Dance</h5>
                        <h6 class="card-title">Dances programs from the past 12 months</h6>
                    </div>
                    <div class="pl-4">
                        The number on the right is the number of times the dance has been called in the past 12 months.
                        <br>
                        Click on the dance name to see a complete history of when it has been called and by whom.
                    </div>
                    <div class="card-body" style="padding-left: 0.25rem; padding-right: 0.25rem">
                        <dances-by-date></dances-by-date>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
