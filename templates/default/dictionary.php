<?php
/*
*   All the handling of your file types is specified here.
*/

global $default_dict;
global $empty_gif;

$parenticon = FileSystemObject::getThumbnailSize('parenticon');
$width = 914;

$dict = array(
	'html'	=> array(
		'image' => array(
			'list'	=> "<li class='list @css_class'><a href='@nice_url'>@name</a></li>",
			'thumb' => "<li class='thumb'><div class='image' ><img id='panel' src='".$empty_gif."' width='@width' height='@height'><img src='@thumb_url' title='@name'></div><h1>@name</h1>@metadata</li>",
			'show'	=> "<li class='show single image' style='width:".$width."px; height:@heightpx'><a href='@next'><div class='image' ><img id='panel' src='".$empty_gif."' width='".$width."' height='@height'></a><img src='@thumb_url' title='@name' style='width:@widthpx; height:@heightpx'/></div>@metadata</li>",
			'metadata' => array(
				'text' => "<div class='meta-text'><a href='@parent_url'><h1>@name</h1></a><p><b>@content</b></p></div>"
			)
		),
		'text' => array(
			'list'	=> "<li class='list @css_class'><a href='@nice_url'>@name</a></li>",
			'thumb'	=> "<li class='thumb'><h1>@name</h1><p>@content</p>$metadata</li>",
			'show'	=> "<h1>@name</h1><p>@content</p>"
		),
		'default' => array(
			'list'	=> "<li class='list @css_class @filetype'><a href='@src_url'>@name</a></li>",
			'thumb'	=> "<li class='thumb @filetype'><a href='@nice_url'><img src='@thumb_url'>@metadata</a></li>",
			'show'	=> "<li class='show single @filetype' style='width:".$width."px; height:@heightpx'><a href='@next'><img id='panel' src='".$empty_gif."' width='".$width."' height='@height'></a><img src='@thumb_url' title='@name'>@metadata<a id='sourcefile' href='@src_url'>download as @suffix</a></li>",
			'metadata' => array(
				'text' => "<div class='meta-text'><a href='@parent_url'><h1>@name</h1></a><p>@content</p></div>"
			)
		),
		'audio' => array(
			'list'	=> "<li class='list_item @css_class'><a href='@url' @metadata>@name</a>@children</li>",
			'thumb'	=> "<li class='thumb html5audio'><audio src='@src' controls></audio>@metadata</li>",
			'show'	=> ''
		),
		'video' => array(
			'list'	=> "<li class='list_item @css_class'><a href='@url' @metadata>@name</a>@children</li>",
			'thumb'	=> "<li class='thumb html5video'><div class='image' ><a href='@nice_url' ><img id='panel' src='".$empty_gif."' width='@width' height='@height'></a><img src='@thumb_url' title='@name'></div>@metadata</li>",
			'show'	=> "<li class='show html5video'><video audio='muted' src='@src_url' width='@width' height='@height' controls>Your browser does not support the video tag.</video>@metadata</li>"
		),
		'folder' 	=> array(
			'list'	=> "<li class='list folder @css_class' ><a href='@nice_url'>@name</a>@children</li>",
			'show'	=> "<ul>@children</ul>",
			'thumb'	=> "<li class='thumb folder @css_class' ><a href='@nice_url' ><img id='panel' src='".$empty_gif."' width='@width' height='@height'></a><img src='@thumb_url' ><h1>@name</h1>@metadata</li>",
			'metadata'  => array(
				'text'  => "<div class='meta-text'><h1>@name</h1><p><b>@content</b></p></div>"
			),
		),
		'css' => array(
			'head'	=> '<link rel="Stylesheet" href="@src_url" type="text/css">'
		),
		'javascript' => array(
			'head'	 => '<script type="javascript" scr="@src_url"></script>'
		),
		'info' => array(
			'pages'  => "<div id='paginationlinks'><a href='@prev' >&lsaquo; </a><span id='pagenumbers'>@current_page/@number_of_pages</span><a href='@next' > &rsaquo;</a></div>",
			'grandparent' => "<a href='@nice_url' >@name</a>  &rsaquo; ",
			'parent' => "<div id='folderinfo'><h1>@grandparent<a href='@parent_url' >@parent_name</a></h1></div>"
		)
	)
);

$GLOBALS['dict']  = array_umerge($default_dict, 'recursiveUpdateWith', $dict);

// xml
// js
// text
//    suffixes
//    list
//    show
// image
//    suffixes
//    list
//    show
// audio
//    suffixes
//    list
//    show
//    player
// video
//    suffixes
//    list
//    show
//    player
?>