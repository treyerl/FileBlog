<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

$defaults = array(
	'website_title'		=> 'FileBlog Website',
	'template'			=> 'default',
	
	'template_folder'	=> 'templates',
	'plugin_folder'		=> 'extensions',
	'system_folder'		=> '_control',
	'content_folder'	=> 'content',
	'cache_folder'		=> '_cache',
	
	'items_per_page'	=>  9,
	'recursion_depth'	=>  50,
	'default_lang'		=> 'EN',
	'date_en'			=> 'D, M jS Y',
	'date_de'			=> 'D, d.M.Y',
	'max_limit'			=> 20,
	'abr_length'		=> 60,
);

/* !! array_umerge() is a custom function. Find it in _array.php !! */
$settings = array_umerge($defaults, 'updateWith', $settings);

date_default_timezone_set('Europe/Zurich');

$settings['template'] = $settings['template_folder']."/".$settings['template'];
$settings['folderpic'] = $settings['system_folder']."/".$settings['folderpic'];
$settings['resources'] = $settings['system_folder']."/_resources";

$default_dict = array(
	'html' => array ( 
		'javascript' => '<script type="application/javascript" scr="@src_url"></script>',
		'css' => '<style type="text/css">@import "@src_url";</style>'
	)
);

?>