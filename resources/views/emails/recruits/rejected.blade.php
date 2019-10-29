@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <p>Hi {{$name}},</p>
    <p>I hope you are having a great day.</p>
    <p>We have carefully checked and assessed your application. 
    Unfortunately,we do not consider you a good fit for our organization at this time. 
    Pleasenote, therefore, that this rejection does not depreciate in any way your qualifications.</p>
    <p>We wish you the best in all your endeavours. </p>
    <p>Best regards,</p>
    <p>Castellum Pro</p>
</td></tr>
@endsection    

@section('footer')
    @parent
@endsection
