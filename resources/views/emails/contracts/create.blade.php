@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <p>Dear {{$name}},</p>
    <p>Congratulations! You have worked hard to get your first Castellum Pro job. We wish you tremendous success and are here to help you at any point.</p>
    <p>One of our members will contact you and help you with the onboarding process. </p>
    <p>I wish you the best of luck and look forward to having you as a family member for years to come. If you have any questions, feel free to reach out :)</p>
    <p>Best Regards, </p> 
    <p>Castellum Pro</p>
</td></tr>    
@endsection    

@section('footer')
    @parent
@endsection