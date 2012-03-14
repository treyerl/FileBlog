<div id="main">
	<div id="menu">
		<ul>
			<li><a href="<?php echo URL::getServerURL();?>"><b>Welcome to FileBlog!</b></a></li>
			<li>A file based drag & drop website cms</li>
		</ul>
	</div>
	<div id="content">
		<?php
			global $url;
			$item = $url->getRequestedObject();
			$item->show(array('thumbnail' => true));
		?>
	</div>
</div>