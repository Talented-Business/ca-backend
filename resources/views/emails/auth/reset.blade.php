@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
    <tr><td>
    <p>Hello {{$data['name']}},</p>

    <p>We received your request to restart your password.</p>

    <p>Use the following passcode to get access to Castellum Pro Platform.</p>

    <p>Password:  <strong>{{$data['token']}}</strong></p>

    <p>After login you can change your password at any moment in user profile section.</p>

    <p>If you need further assistance, please contact us.</p>

    <p>Best regards,</p>

    <p>Castellum Pro</p>
</td></tr>
@endsection    

@section('footer')
    @parent
@endsection
