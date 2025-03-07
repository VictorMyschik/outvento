@extends('layouts.app')

@section('content')
    @include('layouts.mr_nav', ['class' => 'transparent-nav'])
    {!! MrMessage::getMessage() !!}
    <main_page :lang='@json($lang)'></main_page>
    @include('layouts.footer')
@endsection
