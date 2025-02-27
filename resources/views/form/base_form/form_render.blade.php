@extends('layouts.app')
@section('content')
    <div class="mr-main-div">
        @include('Admin.layouts.nav_bar')
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-dialog mw-100 {{$form_data['#size']}}" role="document">
                    <div class="modal-content">
                        <div class="modal-header shadow mr-bg-light-4" style="height: 45px;">
                            <h5 class="font-weight-bold">{{$form_data['#title']}}</h5>
                            <button type="button" class="fa fa-window-close close" @click="showModal = false"
                                    data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{$form_data['#url']}}" method="post">
                                @method('put')
                                @csrf
                                {!! $form !!}
                                <div class="row justify-content-center">
                                    @if($form_data['#btn_info'] ?? null)
                                        <button type="button"
                                                class="btn btn-danger fa">{{$form_data['#btn_info']}}</button>
                                    @else
                                        <button type="submit" class="btn btn-success fa">Сохранить</button>
                                        <button type="button" class="btn btn-danger fa">Отменить</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
