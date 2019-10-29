@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
    <tr><td>
        <p>Hi {{$name}},</p>
        <p>I hope that you are having a great day. I am happy to inform you that you have been approved to join our platform and be a part of this growing family of remote happy workers.</p>
        <p>We want to invite you to apply to our open positions. We are always working to supply you with great job opportunities.</p>
        <p>Access to our platform with following details:</p>
        <p>Login Portal: <a href="{{$link}}" target="_blank">{{$link}}</a></p>
        <p>User: {{$email}}<p>
        <p>Password:  <strong >{{$password}}</strong></p>
        <p><strong>Note:</strong> You can edit your password at anytime in profile section.</p>
        <p>I wish you the best of luck and look forward to having you as a worker in Castellum Pro network! If you have any questions, feel free to reach out :)</p>
        <p>Best Regards, </p> 
    </td></tr>
@endsection    

@section('footer')
    @parent
@endsection
