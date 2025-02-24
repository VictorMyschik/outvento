@extends('layouts.app')

@section('content')
    <div class="mr-main-div">
        @include('layouts.mr_nav')
        <div class="container m-t-10">
            <div class="row no-gutters align-items-center justify-content-center"
                 data-scrollax-parent="true">
                <div class="col-md-9 ftco-animate text-center">
                    <h3>
                        Такой страницы не существует.
                    </h3>
                    <h3>
                        Если Вы уверены, что она должна быть, то <a href="{{route('faq.page')}}">сообщите</a> нам.
                    </h3>
                </div>
            </div>
        </div>
    </div>
@endsection

