@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav', ['class' => 'transparent-nav'])
    <public_search_page :lang='@json($lang)'></public_search_page>
@endsection
