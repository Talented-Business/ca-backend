<div style="width:400px">
@section('header')
    <div>
        <div style="display:inline-block;width:50%">
            <h1 style="font-style:bold">digg</h1>
        </div>
        <div style="display:inline-block;width:50%;float:right;margin-top: 40px;">
            <span>{{date('F d,Y')}}</span>
        </div>
    </div>
@show

<div class="container">
    @yield('content')
</div>
@section('footer')
    <hr>
    <p style="font-style:italic">
    This email cant receive replies. For help or more information, visit our website.
    </p>
    <p>By using our website, you hereby consent to our disclaimer and agree to its terms.</p>
    <p>@2019 castellum.   Panama</p>
@show
</div>