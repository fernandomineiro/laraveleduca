@if(Auth::guard('web')->check())
	<p> 1. You are Looged as a <strong> USER </strong>
@else
	<p> 1. You are Looged  Out as a <strong> USER </strong>
@endif


@if(Auth::guard('admin')->check())
	<p> 2. You are Looged as a <strong> ADMIN </strong>
@else
	<p> 2. You are Looged  Out as a <strong>  ADMIN </strong>
@endif