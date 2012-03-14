<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* Stores all information dedicated to html/json/... encoding in an array called $iteminfo */

class FileSystemObject implements arrayaccess, Iterator, Countable{
	private $position = 0;
	protected $icon;
	public $parent = false;
	private static $fileplugins = array();
	private static $folderplugins = array();
	private static $thumbnail_sizes = array( 'icon'	 => array( 64, 64));
	private static $current_thumbnail_size = "icon";
	protected $sameFilenameOrder = array('folder','flash','video','audio','bintext','archive','image','sourcecode','text');
	protected $sameFilenameSameFiletypeOrder = array(
		'image' 	=> array('jpg','png','tif','psd'),
		'archive'	=> array('zip','tar','','','')		
	);
	protected $prefix_regexp = "^(\d{0,8})-?";	// 20110101-filename or 081-filename, extendable by chilrden-folder_types
	protected $suffix_regexp = "[^\.]+\.?([A-Z]{2})?\.([^\.]+)\/?$";
	
	
	
	// arrayaccess
	protected $iteminfo = array('name','fullname','nice_url','src_url','path','prefix', 'suffix', 'parent_name', 'parent_thumb','parent_url',
		'css_class','time','user_time','filetype', 'filetypegroup', 'mimetype', 'prev', 'next', 'number_of_pages', 'current_page', 'lang', 'metadata', 'thumbsize');
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->iteminfo[] = $value;
        } else {
            $this->iteminfo[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->iteminfo[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->iteminfo[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->iteminfo[$offset]) ? $this->iteminfo[$offset] : null;
    }
    
    // Iterator
    function rewind() {$this->position = 0;}
    function current() {return $this->array[$this->position];}
    function key() {return $this->position;}
    function next() {++$this->position;}
    function valid() {return isset($this->array[$this->position]);}
    
    // Countable
    public function count(){return count($this-iteminfo);}
    
    // FileSystemObject
    public function __construct($args) {
    	global $settings;
    	global $filetypes;
    	global $extensions;
    	$c = $settings['content_folder'];
    	$this->icon = $settings['resources']."/GenericDocumentIcon";
    
    	// internal
    	$this->position = 0;
		$this->iteminfo = array_flip($this->iteminfo);
		foreach ($this->iteminfo as &$item) $item = "";
		
		// path
		$this->iteminfo['path'] = $path = is_array($args)? $args['path'] : $args;
		
		// filetype
		$types = (isset($args['types'])) ? $args['types'] : "";
		if (!is_array($types)) FileSystemObject::filetype($path, $types);
		$this->iteminfo['filetype'] = $types[0];
		$this->iteminfo['filetypegroup'] = ($filetypes[$types[1]])? $filetypes[$types[1]] : $types[1];
		$this->iteminfo['mimetype'] = $types[1];
		
		// name + parent
		if ($path == $c) {
			$fullname = $name = $c;
		} else { 
			preg_match('/('.$c.')?\/?(.*)\/([^\/]+)\/?$/',$path, $matches);
			$fullname = $matches[3];
			$pathbase = preg_split("/\//", $matches[2], -1,PREG_SPLIT_NO_EMPTY);
			$this->iteminfo['path_chain'] = array_merge(array($c),$pathbase);
			
			if (preg_match('/'.$this->suffix_regexp.'/',$fullname, $match)) {
				
				$this->iteminfo['lang'] = $match[1];
				$suffix = strtolower($match[2]);

				if (isset($extensions[$suffix]) || array_search($suffix, FileSystemObject::getFolderExtensions()) 
						|| array_search($suffix, FileSystemObject::getFileExtensions()) ){
					$this->iteminfo['suffix'] = $suffix;
					$name = preg_replace('/\.[^\.]+$/','',$fullname);
				} else $name = $fullname;
			} else $name = $fullname;
			
			//parent
			$this->iteminfo['parent_path'] = $matches[1]."/".$matches[2];
			foreach($pathbase as &$pathpart) $pathpart = preg_replace('/^\d{0,8}-([^\.]+)(\.[^\.]+)?/','$1',$pathpart);
			$this->iteminfo['parent_name'] = $pathbase[count($pathbase)-1];
			$pathbase = implode($pathbase, "/");

			
		}
		$this->iteminfo['fullname'] = $fullname;
		
		// time
		$this->iteminfo['time'] = $utime = filemtime($path);
		if (preg_match('/^(\d{8})-\D+/',$name, $tmp)) {
			$name = preg_replace('/^\d{8}-/','',$name);
			$utime = strtotime($tmp[1]);
			//echo date($settings['date_de'], $utime);
		}
		$this->iteminfo['user_time'] = $utime;
		if (preg_match('/^\d{0,7}-\D+/',$name, $prefix)) {
			$name = preg_replace('/^\d{0,7}-/','',$name);
			$this->iteminfo['prefix'] = $prefix[0];
		} else { $this->iteminfo['prefix'] = '';}
		
		$this->iteminfo['name'] = $name;
		
		// URL
		if ($pathbase != null || $pathbase != "") $pathbase .= "/";
		$this->iteminfo['parent_url'] = URL::getServerURL().$pathbase;
		$this->iteminfo['nice_url'] = URL::getServerURL().$pathbase.$name."/";
		$this->iteminfo['src_url'] = URL::getServerURL().preg_replace('/\s/','%20',$path);
		
		
    }
    
    public function symbols(){
    	// returns $this->iteminfo as an array like $iteminfo('key' => '/@key/') 
    	// --> needed for encoding
    	$return = $this->iteminfo;
    	foreach ($return as $key => &$var) $var = "/@".$key."/";
		return $return;
    }
    
    protected function parentIcon(){
    	$sizes = array_keys(FileSystemObject::getThumbnailSizes());
		$size = array_search('parenticon',$sizes) !== false ? 'parenticon' : 'icon';
		if ($this->parent) $this->parent->thumbnail(FileSystemObject::getThumbnailSize($size));
		$this['parent_thumb'] = $this->parent['thumb_url'];
    }
    
    public function show($args){
		extract($args);
		if (!isset($type))$type = "show"; 
		if (!isset($no_ul_tags)) $no_ul_tags = false;
		
    	if ($type == "show"){
    		if (($size = FileSystemObject::getThumbnailSize("single")) === false) $size = array(900,600);
    		$this->thumbnail($size, $portrait = true);
    	} else { 
    		$size = FileSystemObject::getCurrentThumbnailSize();
    		$this->thumbnail($size);
    	}
    	$this->parentIcon();
		echo $this->encode($type, $no_ul_tags);
    }
    
    public function encode($show_style = "show", $no_ul_tags = false){
    	return encode($this,$show_style,$no_ul_tags);
    }
    
    public function thumbnail($size = null, $portrait = false){
    	//echo "*";
    	if ($size == null) $size = FileSystemObject::getCurrentThumbnailSize();
    	$large = $this->icon."_512_512.png";
    	$meta = $this['metadata'];
    	
    	if ($meta !== false && $meta != '' && isset($meta['image'])) {
			"meta";
			$icon = $meta['image'];
			$icon->thumbnail($size, $portrait);
			$icon = $icon['thumb_url'];
		} else {
			$icon = new Image($large);
			$icon->thumbnail($size);
			$icon = $icon['thumb_url'];
		} 
		$this['width']  = $size[0];
		$this['height'] = $size[1];
		$this['thumb_url'] = $icon;
    }
    
	public function aggregate($duplicates, &$in_an_array = null){
		// $duplicates and $in_an_array need to have the same keys!
		//add metadata to item
		$first = false;
		$firstkey = false;
		$metadata = array();
		$sameFiletype = array();
		
		foreach($duplicates as $key => $item){
			if (!$first) { $first = $item; $firstkey = $key; continue;}
			if ($item['filetype'] != $first['filetype'] && $item['mimetype'] != $first['mimetype']) {
				$metadata[$item['filetype']] = $item;
				if ($in_an_array != null) unset($in_an_array[$key]);
			} else {
				$sameFiletype[$key] = $item;
			}
		}

		if (!empty($metadata)) {
			$first['metadata'] = $metadata;
			//$first->thumbnail();
		}
		if ($in_an_array != null) {
			$in_an_array[$firstkey] = $first;
			if (!empty($sameFiletype)) {
				foreach($sameFiletype as $key => $more) {
					$more['metadata'] = $metadata;
					$in_an_array[$key] = $more;
				}
			}
		}
		
		return $first;
	}
    
    public function get_as_array(){return $this->iteminfo;}
    
    public function get_as_array_rcsv(){
    	$array = $this->iteminfo;
    	foreach($array as $key => $item){
    		if (is_array($item)){
    			foreach($item as $subkey => $subitem){
					if (is_object($subitem)){
						if (array_search('ArrayAccess',class_implements(get_class($subitem))) !== false){
							$array[$key][$subkey] = $subitem->get_as_array();
						}
					}
				}
    		}
    		if (is_object($item)){
				if (array_search('ArrayAccess',class_implements(get_class($item))) !== false)
					$array[$key] = $item->get_as_array();
			}
    	}
    	return $array;
    }
    
    public static function getCurrentThumbnailSize(){ return self::$thumbnail_sizes[self::$current_thumbnail_size];}
    
    public static function getThumbnailSize($key){
		if (isset(self::$thumbnail_sizes[$key])) return self::$thumbnail_sizes[$key];
		else return false;
    }
    
    public static function getThumbnailSizes() { return self::$thumbnail_sizes; }
    
    public static function setCurrentThumbnailSize($size){
    	if (isset(self::$thumbnail_sizes[$size])) {
    		self::$current_thumbnail_size = $size;
    		return true;
    	} else return false;
    }
    
    public static function setThumbnailSize($key,$array){
    	if (is_array($array)) self::$thumbnail_sizes[$key] = $array;
    }
    
    public static function filetype($path, &$types = null){
    	global $filetypes;
    	global $extrahandle;
    	if (!file_exists($path)) throw new Exception(var_export($path, true).' is no path.');
    	preg_match('/.+\/[^\.]+\.([^\.]+$)/',$path, $suffix);
    	if (is_dir($path)) $return = $mimetype = "folder";
    	elseif (isset($extrahandle[$suffix[1]])) {
    		$return = $extrahandle[$suffix[1]];
    		$mimetype = $suffix[1];
		} else {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $path);
			finfo_close($finfo);
			$return = ($filetypes[$mimetype])? $filetypes[$mimetype] : $mimetype;
		}
		$types = array($return, $mimetype);
		return $return;
    }
    
    public static function assign($path){
    	global $settings;
    	global $filetypes;
    	
		if (is_dir($path)){
			return Folder::assign($path);
		} elseif(is_file($path)){
			$types = array();
			$type = FileSystemObject::filetype($path, $types);
			if ($type == "text") { 
				$type = preg_match('/[^\/]?\/([^\/]+\.([^\/]+))$/',$path, $match) ? ($match[2] != "txt") ? $match[2] : $type : $type;
				$types[0] = $type;
			}
			$type = ucwords($type);
			if ($type == "Archive") {
				if (preg_match(Folder::find_suffix(), $path, $match)){
					$types[0] = $match[3];
					$type = ucwords($types[0]);
					if (array_search($type, FileSystemObject::getFolderExtensions()) !== false) {
						$content = $settings['content_folder'];
						$cache = $settings['cache_folder'];
						
						$zip = new ZipArchive();
						if ($zip->open($path) === TRUE) {
							$cachepath = preg_replace('/'.$content.'/',$cache, $path);
							if (!file_exists($cachepath)) mkdir($cachepath);
							if ($zip->extractTo($cachepath)) {$zip->close(); return Folder::assign($cachepath);}
							else {error_log('Zip extraction failed'); $zip->close(); return new FileSystemObject($path);}
						} else {
							error_log('open Zip failed');
						}
					} 
				}
			}
			if ($type == "File"){
				preg_match('/^(.+\/)*\d{0,8}-?([^_\/]+)__([^_\/]+)$/',$path,$match);
				//echo preg_replace("/:/","/",$match[3]);
				$link = new URL(preg_replace("/:/","/",$match[3]));
				//print_r($link->getRequestedObject());
				$item = $link->getRequestedObject();
				$item['name'] = $match[2];
				return $item;
			}
			if (array_search($type, self::$fileplugins) !== false) {
				try {
					return new $type(array('path'=>$path, 'types'=>$types));
				} catch(Exception $e) {
					error_log($e);
					return new FileSystemObject(array('path'=>$path, 'types'=>$types));
				}
			} else {
				error_log($path.": ".$type." not found in ".print_r(self::$fileplugins, true));
				return new FileSystemObject(array('path'=>$path, 'types'=>$types));
			}
			
		} else {
			throw new Exception('FileSystemObject::assign() was given no regular path: $path');
			return false;
		}
	}
	
	public static function loadExtensions(){
		global $settings;
		$fiplugins = $foplugins = array();
		
		$base = $settings['plugin_folder'];
		foreach(scandir($base) as $folder){
			if (substr($folder,0,1)==".") continue;
			//echo $folder;
			foreach(scandir($base."/".$folder) as $file){
				if (substr($file,0,1)==".") continue;
				//echo $file;
				if(is_file($base."/".$folder . "/" . $file) && substr($file,0,1)!="."  && strpos($file,".php")!==false){
					//echo "load ".$base."/".$folder."/".$file;
					require_once($base."/".$folder."/".$file);
					$classname = preg_replace("/.+\.(.+).php$/","$1",$file);
					$class = new ReflectionClass($classname);
					if ($class->isSubclassOf( new ReflectionClass("Folder"))) array_push($foplugins, $classname);
					elseif ($class->isSubclassOf( new ReflectionClass("FileSystemObject"))) array_push($fiplugins, $classname);
				}
			}
		}
		self::$folderplugins = $foplugins;
		self::$fileplugins = $fiplugins;
	}
	
	public static function printExtensions(){
		echo "<br>folder plugins: "; print_r(self::$folderplugins);
		echo "<br>file plugins: "; print_r(self::$fileplugins);
	}
	
	public static function getFolderExtensions() { return self::$folderplugins; }
	public static function getFileExtensions() { return self::$fileplugins; }
}

?>