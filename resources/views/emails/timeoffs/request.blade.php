@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <div><label style="display:inline-block;width:100px">Date:</label><span> From {{$startDate}} To{{$endDate}}({{$days}} days)</span></div>
    <div><label style="display:inline-block;width:100px">Employee’s Name:</label><span> {{$employeeName}}</span></div>
    <div><label style="display:inline-block;width:100px">Leave Policy:</label><span>{{$policy}}</span></div>
    <div><label style="display:inline-block;width:100px">Reason for Time Off:</label></div>
    <div style="white-space: pre-wrap; height:150px;overflow-y: auto;">
        {{$reason}}
    </div>
</td></tr>
@endsection    

@section('footer')
    @parent
@endsection
