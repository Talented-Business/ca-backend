@extends('layouts.email')


@section('header')
    @parent
@endsection

@section('content')
<tr><td>
<p>Hi {{$companyUserName}},</p>

<p>Hope you are doing great. Thank you for using Castellum Pro to grow your business. </p>

<p>Your Castellum Pro weekly invoice is available. Please find the PDF document attached at the bottom of this email.</p>

<div><strong>Invoice Number</strong>: {{$invoiceId}}</div>
<div><strong>Amount due</strong>: U${{$total}}</div>
<div><strong>Period Invoiced</strong>: {{$period}}.</div>

<p>If you want to view your payment history or update your payment info, visit your account at www.castellumpro.com.</p>

<p>Need a hand with something else? Reply to this email and we'll be happy to help.</p>

<p>Best Regards,</p>

<p>Castellum Pro Team</p>

</td></tr>
@endsection    

@section('footer')
    @parent
@endsection
