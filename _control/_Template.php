<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */
class Template extends Folder {
	public $load_queue = array();
	public $meta_tags = array();
	
	public function __construct(){
		global $settings;
		parent::__construct($settings['template']);
	}
	
	public function load($encoding = "html"){
		global $settings;
		global $url;
		$load_items = array();
		$index = false;
		$path = $this->iteminfo['path'];
		if (is_file($path."/functions.php")) require_once($path."/functions.php");
		foreach(scandir($path) as $file){
			if (substr($file,0,1)==".") continue;
			$filepath = $path . "/" . $file;
			if(is_file($filepath) && substr($file,0,1)!="." && substr($file,0,1)!="_" ){
				if (strpos($file,".php")!==false && strpos($file,".css.php")===false) {
					if ($file == "functions.php") continue;
					if ($file != "index.php") require_once($filepath); else $index = true;
				}
				else {
					$item = FileSystemObject::assign($filepath);
					if (strpos($file,".css.php")!==false || strpos($file,".css")!==false) $item['filetype'] = "css";
					array_push($load_items, $item);
				}
			} elseif (is_dir($filepath) && $file == '_js'){
				foreach(scandir($filepath) as $subfile){
					if (substr($file,0,1)==".") continue;
					$subfilepath = $filepath . "/" . $subfile;
					if(is_file($filepath) && substr($file,0,1)!="."){
						array_push($load_items, FileSystemObject::assign($filepath));
					}
				}
			}
		}
		
		foreach($this->load_queue as $load) {
			array_push($load_items, FileSystemObject::assign($load));
		}
		if ($url->getType() == "html") {
			header( 'content-type: text/html; charset=utf-8' );
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<title>'.$settings['website_title'].'</title>';
			foreach($this->meta_tags as $tags) echo $tags;
			foreach($load_items as $loads) echo $loads->encode('head', $no_ul_tags = true);
			echo '</head>';
			echo '<body>';
			if ($index) require_once($path."/index.php");
			echo '</body>';
			echo '</html>';
		} else {
			require_once($path."/_".$url->getType().".php");
		}
	}
	
	public function error_pages($error){
		die($error);
	}
}
?>