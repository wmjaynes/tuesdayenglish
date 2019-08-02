@extends('layouts.app')

@section('content')
    <div class="container">


        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-danger lead">
                    This site is very much a work in progress and may not always be available and working properly.
                    Currently it only displays a twelve month window, from today.
                    <ul>
                        <li>7/30/2019: Added dance "History" page</li>
                        <li>8/1/2019: Added dance history dropdown toggle. Click on a dance name below. It shows all
                            called dates in the database, not just in the last 12 months.
                        </li>
                        <li>
                            8/2/2019: On the "History" page, added columns for meter, key,
                            dance formation, and Barnes location, if in Barnes. Still working on formatting.
                        </li>
                    </ul>
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
