@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav')
    <slider :lang='@json($lang)'></slider>
    <activities :lang='@json($lang)' :activities='@json($activities)'></activities>

    {{--  @include('layouts.example')--}}
    @include('layouts.footer')
@endsection
