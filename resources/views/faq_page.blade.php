@extends('layouts.app')

@section('content')
    <div class="mr-main-div">
        @include('layouts.mr_nav')
        <div class="container m-t-10">
            {!! MrMessage::GetMessage() !!}
            <div class="row no-gutters align-items-center justify-content-center m-t-10" data-scrollax-parent="true">
                <h1 class="mb-3 mt-2">{{__('mr-t.faq_title')}}</h1>
            </div>

            <div class="row justify-content-start pb-3">
                <div class="col-md-7">
                    <h3>{{__('mr-t.faq')}}</h3>
                </div>
            </div>

            <div id="accordion" class="mr_bg_input mr-border-radius-5">
                @foreach($list as $value)
                    <a class="mr-bold" data-bs-toggle="collapse" href="#menu{{ $value->id() }}"
                       aria-expanded="true" aria-controls="menu{{ $value->id() }}">
                        <span class="row card-header margin-horizontal-0">{{ $value->getTitle() }}</span>
                    </a>

                    <div id="menu{{ $value->id() }}" class="collapse card-body">
                        {!! $value->getText() !!}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="container mt-5 ">
            <a data-bs-toggle="collapse" href="#feedback" aria-expanded="true" class="mr-color-black mr-bold">
                Feedback <h3 class="form-group">{{__('mr-t.feedback_2')}}</h3>
            </a>
            <div id="feedback" class="collapse show in mt-4">
                <form action="{{route('feedback')}}" method="post">
                    @csrf
                    <label for="name"
                           class="form-group col-md-6 padding-horizontal-0 mr-bold">{{__('mr-t.name')}}
                        <input required type="text" name="name" class="form-control mr_bg_input"></label>

                    <label for="email" class="form-group col-md-6 padding-horizontal-0 mr-bold">Email
                        <input required type="email" name="email" class="form-control mr_bg_input"></label>

                    <div class="form-group mt-3">
                        <label for="message" class="mr-bold">{{__('mr-t.message')}}</label>
                        <textarea required name="message" id="message" cols="30" rows="10"
                                  class="form-control mr_bg_input"></textarea>
                    </div>

                    <div class="form-group mt-3">
                        <input type="submit" value="{{__('mr-t.feedback_form_send')}}" class="mr-btn-success">
                    </div>
                    <br>
                </form>
            </div>
        </div>
    </div>
@endsection
