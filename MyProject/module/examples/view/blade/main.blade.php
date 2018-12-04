<?php $page_title = 'X-FRAME | examples | INDEX'; ?>
<?php $head_layout = 'sitemin';?>
<?php include _X_LAYOUT . "/{$head_layout}/header.phtml"; ?>
<?php if($head_layout === 'sitemin'):?>
	<?php include _X_LAYOUT . '/sitemin/nav.phtml'; ?>
<?php endif;?>
<div style="margin:10px;padding:6px 10px;border:5px #789 solid">
	<h3>"main.blade.php"</h3>
	this is the demo to show how to link to _vendor lib
	<h4>Main header</h1>
	<div class="container-fluid">
		<div class="row ">
	    @yield('content')
	    	</div>
	</div>
</div>
<?php include _X_LAYOUT . "/{$head_layout}/footer.phtml"; ?>
