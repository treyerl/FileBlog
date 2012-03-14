<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

class Gallery extends Folder {
	public function __construct($path){
		global $settings;
		global $template;
		global $url;
		
		parent::__construct($path);
		Folder::setThumbnailSize('normal', array(220, 147));
		Folder::setThumbnailSize('single', array(914, 609));
		Folder::setCurrentThumbnailSize('normal');
		$js = $settings['plugin_folder']."/folder.Gallery/js/";
		array_merge($template->load_queue,array( "$jssome_javascript.js", "$jsanother.js" ));
	}
	
	public function randomPic(){
		$pics = $this->getImgPaths();
			$myFile = $this['path']."/_imageCount.txt";
			$fh = @fopen($myFile, 'w') or $fh = tmpfile() ;
			fwrite($fh, "Image count: ".count($pics));
			fclose($fh);
		if (count($pics) > 0) {
			$img = new Image($pics[array_rand($pics)]);
			$img->show(array('thumbnail' => true));
		}
	}
	
	public function getImgPaths($path = null){
		if ($path === null) $path = $this['path'];
		$pics = array();
		foreach(scandir($path) as $item){
			if (preg_match('/^\.|^_/',$item)) continue;
			$i_path = $path."/".$item;
			if (is_dir($i_path)) $pics = array_merge($pics, $this->getImgPaths($i_path));
			if (is_file($i_path)) {
				if (preg_match('/\.jpg$|\.JPG$/',$item)) array_push($pics, $i_path);
			}
		}
		return $pics;
	}

}

?>