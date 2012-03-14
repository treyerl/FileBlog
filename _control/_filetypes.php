<?php 	/* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */
		/* groups filetypes in categories of filetypes (filetypegroups) */
	
$filetypes = array(
	'application/x-bzip' => 'archive',
	'application/x-bzip2' => 'archive',
	'application/java' => 'application',
	'application/msword' => 'bintext',
	'application/x-compressed' => 'archive',
	'application/jar' => 'archive',
	'application/pdf' => 'bintext',
	'application/postscript' => 'bintext',
	'application/zip'=> 'archive',
	
	'audio/aac' => 'audio',
	'audio/aacp' => 'audio',
	'audio/aiff' => 'audio',
	'audio/x-aiff' => 'audio',
	'audio/basic' => 'audio',
	'audio/midi' => 'audio',
	'audio/mpeg' => 'audio',
	'audio/mpeg4' => 'audio',
	'audio/matroska' => 'audio',
	
	'image/bmp' => 'image',
	'image/jpg' => 'image',
	'image/jpeg'=> 'image',
	'image/png' => 'image',		
	'image/gif' => 'image',
	'image/tif' => 'image',
	'image/tiff' => 'bintext', //CR2
	'image/x-jg' => 'image',
	'image/x-pict' => 'image',
	'image/svg' => 'bintext',
	
	'text/x-c' => 'sourcecode',
	'text/x-c++' => 'text',
	'text/x-fortran' => 'sourcecode',
	'text/pascal' => 'sourcecode',
	'text/php' => 'sourcecode',
	'text/x-script.perl' => 'sourcecode',
	'text/x-script.phyton' => 'sourcecode',
	'text/php' => 'sourcecode',

	'video/x-flv' => 'video',
	'video/x-motion-jpeg' => 'video',
	'video/mpeg' => 'video',
	'video/quicktime' => 'video',
	'video/avi' => 'video',
	'video/matroska' => 'video',

	'application/octet-stream' => 'file',
	
	'text/plain' => 'text',
	
	'text/javascript' => 'javascript',
	'application/javascript' => 'javascript',
	
	'text/css' => 'css',
	'application/css' => 'css'
);

$extensions = array(
	'zip'	=> 'archive',
	'bzip'	=> 'archive',
	'zip'	=> 'archive',
	'jar'	=> 'archive',
	'rar'	=> 'archive',
	'7z'	=> 'archive',
	'zip'	=> 'archive',
	'iso'	=> 'archive',
	'dmg'	=> 'archive',
	
	'docx'	=> 'archive',
	'pages'	=> 'archive',
	
	'pdf'	=> 'bintext',
	'doc'	=> 'bintext',
	'xls'	=> 'bintext',
	'ppt'	=> 'bintext',
	'cr2'	=> 'bintext',
	'blend' => 'bintext',
	
	'txt'	=> 'text',
	
	'jpg'	=> 'image',
	'jpeg'	=> 'image',
	'png'	=> 'image',
	'gif'	=> 'image',
	'tif'	=> 'image',
	'tiff'	=> 'image',
	'bmp'	=> 'image',
	'thm'	=> 'image',
	
	'mp3'	=> 'audio',
	'mp4'	=> 'audio',
	'm4a'	=> 'audio',
	'wma'	=> 'audio',
	'ogg'	=> 'audio',
	
	'm4v'	=> 'video',
	'mov'	=> 'video',
	'mpeg'	=> 'video',
	'avi'	=> 'video',
	'mkv'	=> 'video',
	'ogv'	=> 'video',
	'flv'	=> 'video',	
);

$extrahandle = array(
	'blend' => 'bintext'
);

?>