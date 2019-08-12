@extends('layouts.app')

@section('content')
    <div class="container-fluid">


        <div class="row justify-content-center">
            <div class="col-lg-12">
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
                        <li>
                            8/8/2019: On the "History" page, can specify number of past months to include,
                            both for the dances and their history.
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">AACTMAD Tuesday English Country Dance</h5>
                        <h6 class="card-title">Dances programs from the past 12 months</h6>
                    </div>
                    <div>
                        The number on the right is the number of times the dance has been called in the time period
                        displayed.
                        <br>
                        Click on the dance name to see a history of when it has been called and by whom.
                    </div>
                    <div class="card-body">
                        <dances-by-date></dances-by-date>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
