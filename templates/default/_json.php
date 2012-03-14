<?php
	global $url;
	header("Content-type: application/json");
	$obj = $url->getRequestedObject();
	$obj->show(array('type' => "show"));
?>