@extends('layouts.app')

@section('content')
    <div class="container-fluid">


        <div class="row justify-content-center">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">AACTMAD Tuesday English Country Dance</h5>
                        <h6 class="card-title">Program for
                            {{(new \Carbon\Carbon($dances[0]->callers[0]->pivot->date_of))->toDateString()}}</h6>
                    </div>
                    <div class="card-body">

                        @foreach($dances as $dance)
                            <div class="row border-left">
                                <div class="col col-lg-3 dance-name">
                                    {{$dance->name}}
                                </div>
                                <div class="col col-lg-3">
                                    {{$dance->callers[0]->name}}
                                </div>
                            </div>
                        @endforeach


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
