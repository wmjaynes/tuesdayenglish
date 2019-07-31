@extends('layouts.app')

@section('content')
    <div class="container">


        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-danger lead">
                    This site is very much a work in progress and may not always be available and working properly. <br>
                    I haven't decided on exact formating of this page. Suggestions are welcome.
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">AACTMAD Tuesday English Country Dance</h5>
                        <h6 class="card-title">Dances Done</h6>
                    </div>
                    <div class="card-body">
                        <dances-history></dances-history>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
