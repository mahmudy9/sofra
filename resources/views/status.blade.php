@if(Session::has('status'))


<div class="alert alert-info">
<p>{{session('status')}}</p>
</div>


@endif