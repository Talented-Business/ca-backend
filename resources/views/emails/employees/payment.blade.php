@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <p>Hello,</p>
    <p>Hope you are doing great. </p>
    <p>This coming DATE within business hours you will receive the following payment:</p>

    <div><strong>Week</strong>: {{$period}}</div>
    @if ( $sales)
        <div><strong>Sales Commissions</strong>: U${{$sales}}</div>
    @endif 
    @if ( $hours)
        <div><strong>Hours</strong>: U${{$hours}}</div>
    @endif 

    <p>If you want to see more details about your next or previous payments, please visit our portal.</p>
    <p>Regards, </p> 
    <p>Castellum Pro</p>
</td></tr>    
@endsection    

@section('footer')
    @parent
@endsection