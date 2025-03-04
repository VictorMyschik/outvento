@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav', ['class' => 'transparent-nav'])
    <main_page :lang='@json($lang)'></main_page>
@endsection
