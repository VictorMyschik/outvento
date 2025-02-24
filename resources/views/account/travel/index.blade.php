@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav')
    <div class="container col-md-10 mt-3">
        <account_travel_list :travel_id="'{{$travelId}}'"></account_travel_list>
    </div>
@endsection

