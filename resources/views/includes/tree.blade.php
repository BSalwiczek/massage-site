<div class="container vertical-center" 
style="background-color: #f8fafc;min-height: 40px;min-width: 100%;border-bottom: 1px solid #EEE;color:#888">
	<div class="vertical-center-child">
		<span class="tree-item pr-1"><a href="/">Strona główna</a></span>

		@if(View::hasSection('tree1'))
		>
		<span class="tree-item pl-1">@yield('tree1')</span>
		@endif

		@if(View::hasSection('tree2'))
		>
			<span class="tree-item pl-1">@yield('tree2')</span>
		@endif
	</div>		
</div>