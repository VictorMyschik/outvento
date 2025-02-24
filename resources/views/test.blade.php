@extends('layouts.app')
@section('content')
    <div class="mr-main-div">
        @include('Admin.layouts.nav_bar')
        <div class="container">
            <table class="table table-sm col-md-8">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Icon</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{!! $row['name'] !!}</td>
                        <td>{!! $row['description'] !!}</td>
                        <td>{!! $row['icon'] !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
