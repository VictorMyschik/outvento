@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav')
    <public_search_page :lang='@json($lang)'></public_search_page>
    @include('layouts.footer')
@endsection
