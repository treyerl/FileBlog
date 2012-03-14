<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

class Image extends FileSystemObject {
	
	public function thumbnail(&$size = null, $portrait = false){
		global $settings;

		if ($size == null) $size = FileSystemObject::getCurrentThumbnailSize();
    	elseif (!is_array($size)) {
    		if (array_search($size,FileSystemObject::getThumbnailSizes()) !== false)
    			$size = FileSytemItem::getThumbnailSize($size);
    		else return false;
    	}
    	
		$cache = $settings['cache_folder'];
		$c = $settings['content_folder'];
		$type_thumbs = $settings['resources'];
		$tt = preg_replace('/\//','\/',$settings['resources']);
		$this['height'] = $size[1];
		
		if (preg_match('/'.$c.'\/(.*)(\..+)$/',$this['path'], $path_suffix)) 
			$thumbnail = $cache."/".$path_suffix[1]."_".$size[0]."_".$size[1].$path_suffix[2];
		elseif (preg_match('/'.$tt.'\/(.+)_\d{1,4}_\d{1,4}(\..+)$/',$this['path'], $path_suffix) )
			$thumbnail = $type_thumbs."/".$path_suffix[1]."_".$size[0]."_".$size[1].$path_suffix[2];
		else return false;

		if (file_exists($thumbnail)) {
			$this['thumb_url'] =  URL::getServerURL().$thumbnail; // return thumb_url
			list($old_width, $old_height) = getimagesize($thumbnail);
			$this['width'] = ($old_width < $old_height)? ceil($size[1]*$old_width/$old_height) : $size[0];
		} else {
			$chain = $this['path_chain'];
			array_shift($chain);
			$cache .= "/";
			for ($i = 0; $i<count($chain); $i++) {
				$cache .= $chain[$i]."/";
				
				if (!file_exists($cache)){
					//echo "mkdir($path)";
					if (!mkdir($cache)){
						echo "Could not create the thumbnail ".$cache.". You may check permissions. Failed upon mkdir('$cache').";
					}
				}
			}
			
			// read image
			$image = @imagecreatefromjpeg($this['path']);
			if (!$image) $image = @imagecreatefrompng($this['path']);
			if (!$image) $image = @imagecreatefromgif($this['path']);
			if (!$image) {
				echo "Could not read the image file '".$this['path']."'!";
				return;
			}
			
			// width + height
			$y_off = $x_off = 0;
			list($new_width, $new_height) = $size;
			list($old_width, $old_height) = getimagesize($this['path']);
			$zoom_old_height = $old_height;
			if (false !== $exif = @exif_read_data($this['path'])){
				$o = $exif['Orientation'];
				if ($o == 6 || $o == 8) {
					// prepare image for rotation (needs less memory once scaled already)
					$tmp_image = imageCreateTrueColor(floor($old_width/$old_height*$new_width), $new_width);
					imageantialias($tmp_image, true);
					$success = imagecopyresampled($tmp_image, $image, 0, 0, 0, 0, floor($old_width/$old_height*$new_width), $new_width, $old_width, $old_height);
					//echo floor($old_width/$old_height*$new_width)." x ".$new_width;echo "<br>";
					//echo $old_width ." x ". $old_height;
					//imagejpeg($tmp_image, '_cache/tmp_test.jpg',86);
					$zoom_old_height = $old_height = floor($old_width/$old_height*$new_width);
					$old_width = $new_width;
					
					if ($o == 6) $image = imagerotate($tmp_image, -90, 100);
					if ($o == 8) $image = imagerotate($tmp_image,  90, 100);
					//imagejpeg($image, '_cache/test.jpg',86);
				}
			}
			//echo "<br>".$old_height.$old_width;
			if ($old_height > $old_width) { //portrait
				//echo "portrait: ";var_dump($portrait);
				if ($portrait){
					// make it portrait
					//echo "portrait";
					$new_width = floor($new_height/$old_height*$old_width);
				} else {
					// zoom in for landscape thumbnail
					$zoom_old_height = ($old_width/$new_width)*$new_height;
					$y_off = $zoom_old_height/2;
				}
			}elseif (round($new_height/$new_width, 2) < round($old_height/$old_width, 2)){
				$zoom_old_height = $old_width*$new_height/$new_width;
				$y_off = ($old_height-$zoom_old_height)/2;
			}elseif (round($new_height/$new_width, 2) > round($old_height/$old_width, 2)){
				$old_old_width = $old_width;
				$old_width = $new_width*$old_height/$new_height;
				$x_off = round(($old_old_width-$old_width)/2);
			}
			
			// new canvas
			$new = imageCreateTrueColor($new_width, $new_height);
			imagealphablending($new, false);
			imagesavealpha($new, true);
			imageantialias($new, true);
			
			// resize
			imagecopyresampled($new, $image, 0, 0, $x_off, $y_off, $new_width, $new_height, $old_width, $zoom_old_height);
			//echo round($new_height/$new_width, 2);
			//echo round($old_height/$old_width, 2);
			$this['width'] = $new_width;
			
			// save
			if (imagejpeg($new, $thumbnail, 85)) $this['thumb_url'] = URL::getServerURL().$thumbnail;
			elseif (imagepng($new, $thumbnail, 3)) $this['thumb_url'] = URL::getServerURL().$thumbnail;
			elseif (imagegif($new, $thumbnail)) $this['thumb_url'] = URL::getServerURL().$thumbnail;
			else echo "Could not safe meta thumbnail image. You may check permissions.";
		}
	}
	
	public function show($args){
		extract($args);
		if (!isset($thumbnail))$thumbnail = false; 
		if (!isset($no_ul_tags)) $no_ul_tags = false;
		
		if (($size = FileSystemObject::getThumbnailSize("single")) === false) $size = array(900,600);
		if ($thumbnail) {
			$this->thumbnail($size, $portrait = true);
		} else {
			list($old_width, $old_height) = getimagesize($this['path']);
			$this['width'] = ($old_width < $old_height)? floor($size[1]*$old_width/$old_height) : $size[0];
			$this['height'] = $size[1];
			$this['thumb_url'] = $this['src_url'];
		}
		$this->parentIcon();
		echo $this->encode("show", $no_ul_tags);
	}
}

?>