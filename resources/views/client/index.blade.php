@extends('layouts.app')
@section('no_search')
@endsection

@section('content')
<div id="app">
	<client-profile s_user="{{ Auth::user() }}"></client-profile>
</div>
@endsection