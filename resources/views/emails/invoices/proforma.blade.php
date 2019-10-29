@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
    <p>Dear {{$companyUserName}}, </p>
    <p>Attached you can find the invoice regarding the operations department, 
        please review and give your approval to proceed with the payment for the dates 
        {{$startDate}} ~ {{$endDate}}.</p>
    <p>Thank you very much,</p>
    <p>Best regards,</p>
    <p>Castellum Pro</p>
</td></tr>
@endsection    

@section('footer')
    @parent
@endsection
