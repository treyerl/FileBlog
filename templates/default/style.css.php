<?php
	$main_width = 914;
	$main_height = $main_width*2/3;
	$item_width = 221;//FileSystemObject::getCurrentThumbnailSize()[0];
	$item_height = 148;//FileSystemObject::getCurrentThumbnailSize()[1];
	$parent_icon_width = 60;
	$parent_icon_height = 40;
	
	if (isset( $_GET['w'])) { $width = $_GET['w']-360;}
	if (isset( $_GET['h'])) { $height = $_GET['h']-80;}
	
	$cyan = '#00FFFF';
	
	$color = "#CCCCDF";
	//$color = "#CB0";
	
	header("Content-type: text/css");
?>

a {text-decoration: none; color: #000}
ul, ul li, ul li ul {display: inline;float:left;}
/*ul {padding-left: 15px;}*/
.root {display none;}
#main {	margin: 0 auto;width: <?php echo $main_width+10;?>px;font-family: Arial, Helvetica, san-serif;	font-size: 12px;}
#menu {position: relative; width:<?php echo $main_width;?>px;margin: 10px 0px 0px 0px; height:50px; border-bottom: 1px solid #eee;}
#menu li a:hover, #menu li a.active{border-bottom: 1px solid #88D;}
#menu ul li {float: left;line-height: 50px;}
#menu ul li a {float: left;line-height: 50px;margin-right: 25px;}
#content{position: relative; height: <?php echo $main_height;?>px;margin-top: 15px;}
#paginationlinks{position: absolute; left: <?php echo $main_width+20;?>px; top: -28px; color: #888; width:60px;vertical-align:middle;font-size:20px;}
#paginationlinks p{font-family: Arial, Helvetica, san-serif;	font-size:10px;}
#paginationlinks a{color: #888;}
#paginationlinks a:hover{color: #f84;}
#pagenumbers{font-size:0.5em; vertical-align:middle;}
#content .folder , #content .folder .meta-image{width: <?php echo $item_width;?>px;	/*padding-right: <?php echo gap_in_mapped_row($main_width, $item_width);?>px;*/	}
.folder > div > a > img, div.image {margin: auto;}

#folderinfo{ position: absolute; right: 10px; top: -70px; text-align: right; width:<?php echo $main_width/2;?>px;}
#panel_info {position: absolute;top: 0; z-index:10;right:0;}
#parent_thumb {float: right;margin-left:20px;}
#folderinfo h1 {line-height: 60px;}

.meta-text,  #panel {position: absolute;top: 0; left:0;}
.meta-text {padding: 10px; padding-top: 40px;line-height: 16px; width: <?php echo $item_width-20;?>px;display:none;}
#panel{z-index:10;}
#sourcefile{position: absolute; left: 0; top: 0; z-index:11; border:1px dashed #ccc; background-color: white; padding: 3px;font-style:italic; display:none}
.single .meta-text{width: <?php echo $main_width-14;?>px; text-align: left; background-color: #fff; opacity:0.9;filter:alpha(opacity=90);}
.thumb:hover img, #sourcefile, .thumb h1 {opacity:0.1;filter:alpha(opacity=10);}
.thumb:hover .meta-text, .show:hover .meta-text, .show:hover #sourcefile{display: block;}
.thumb:hover {background-color: <?php echo $color;?>;}
.thumb:hover h1 {background-color: transparent;}
.single:hover img{opacity:1;filter:alpha(opacity=100);}
li.single {text-align:center;}
.thumb {position: relative;width: <?php echo $item_width;?>px;height: <?php echo $item_height;?>px; margin: 0 10px 10px 0;  border-radius: 15px;  box-shadow:3px 4px 6px 2px #ccc;}

.thumb img {border-radius: 15px; border: 1px solid <?php echo $color;?>;}
.thumb h1 {position: absolute; top: 10px; left: 10px; background-color: <?php echo $color;?>; opacity: 0.6; width: <?php echo $item_width-10;?>px; line-height: 40px; margin: -10px; padding-left: 10px; border-top-left-radius: 15px;  border-top-right-radius: 15px; font-size: 17px; }
#folderfinish{width: 100%;}
#footer{margin: 15px 0 30px 0 ; clear: both;}
<?php
	function gap_in_mapped_row($large, $small){
		$div = floor($large/$small);
		$mod = $large % $small;
		return $mod/($div-1)-10;
	}
?>