<?php
/*
*   This is where all your functions and definitions go
*/
Folder::setThumbnailSize('parenticon', array(60, 40));

// YOU MUST PROVIDE THESE FUNCTIONS IN YOUR TEMPLATE!!! JUST COPY, IF YOU DONT NEED TO ALTER THEM!!!
function encode($item,$show_style,$no_ul_tags){
	global $dict;
	global $url;
	global $settings;
	
	$is_root = $item['name'] == $settings['content_folder'];
	$enc_type = $url->getType();
	$filetype = $item['filetype'];
	$type_dict = (isset($dict[$enc_type][$filetype]))? $dict[$enc_type][$filetype] : $dict[$enc_type]['default'];
	
	//json ?
	if ($enc_type == "json") {

		return json_encode($item->get_as_array_rcsv());
	}
	
	// children
	if (is_array($item['children'])) {
		$child_style = ($show_style == 'show') ? 'thumb' : $show_style;
		$children = $item['children'];
		foreach($children as &$child) {
			unset($child['parent']);
			$child = $child->encode($child_style, true);
		}
		$item['children'] = implode($children, "\n");
	} 
	if ($is_root) $item['css_class'] .= " root";
	
	// metadata
	if (is_array($item['metadata'])) {
		$metas = $item['metadata'];
		foreach($metas as &$meta) {
			//$meta->specialChars();
			$meta = preg_replace($meta->symbols(),$meta->get_as_array(),$type_dict['metadata'][$meta['filetype']]);
		}
		$item['metadata'] = implode($metas);
	} elseif ($item['metadata'] != "") echo $item['path']."[metadata] is no array but ";
	
	//html ?
	if ($enc_type == "html" && $show_style == "show") {
		//navigation
		$info = $dict['html']['info'];
		if ($item['number_of_pages'] > 1 )echo $pagelinks = preg_replace($item->symbols(),$item->get_as_array(), $info['pages']);
		if ($item->parent) {
			$parent = $item->parent; 
			if ($parent['name'] != $settings['content_folder'] && $parent['name'] != $item['parent_name']) $item['grandparent'] = preg_replace($parent->symbols(), $parent->get_as_array(), $info['grandparent']);
			else $item['grandparent'] = "";
			echo $folderinfo = preg_replace($item->symbols(),$item->get_as_array(), $info['parent']);
		}
	}
	
	// encode
	if ($is_root) $item = $item['children'];
	else $item = preg_replace($item->symbols(),$item->get_as_array(),$type_dict[$show_style]);

	if (!$no_ul_tags) {
		$ul_1 = "<ul>";
		$ul_2 = "</ul>";
		
	}
	
	return $ul_1.$item.$ul_2;
}

function error_message($message){
	echo $message;
}
?>