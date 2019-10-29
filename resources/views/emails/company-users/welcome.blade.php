@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <p>Dear {{$userName}},</p>
    <p>Welcome to Castellum Pro.</p>
    <p>We deeply appreciate your trust and hope to help you grow your business. You will have access to applicants and employee’s information. </p>
    <p>Please follow this link to set your password and access to our platform: <a href="https://app.castellumpro.com/auth/login" target="_blank">https://app.castellumpro.com</a></p>
    <p>Password: <strong>{{$password}}</strong></p>
    <p>Need a hand with something else? Reply to this email and we'll be happy to help.</p>
    <p>Best Regards, </p> 
    <p>Castellum Pro</p>
</td></tr>    
@endsection    

@section('footer')
    @parent
@endsection