@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav')
    <slider :lang='@json($lang)'></slider>
    <activities :lang='@json($lang)' :travel_type_list='@json($travelTypeList)' :travel_examples='@json($travelExamples)'></activities>

    {{--  @include('layouts.example')--}}
    @include('layouts.footer')
@endsection
