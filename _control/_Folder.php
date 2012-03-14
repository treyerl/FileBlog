<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* All folder_types should inherit from this class. */

class Folder extends FileSystemObject{
	private static $plugins = array();
	private static $find_suffix = '/^([^\/]+\/)?([^\.^\/].+\.([^\.^\/]+))$/';
	
	public function getFiles(){ array_filter($this->iteminfo['children'], function ($child) {return $child['filetype'] != 'folder';});}
	public function getSubFolders() {array_filter($this->iteminfo['children'], function ($child) {return $child['filetype'] == 'folder';});}
	public function filter($type) {array_filter($this->iteminfo['children'], function ($child, $type) {return $child['filetype'] == $type;});}
	public function get_content() {return $this->iteminfo['children'];}
	//public function getFolderMetas() {return $this->inteminfo['meta_data'];}
	public static function defaultSameFilenameOrder() {return $this->sameFilenameOrdner;}

	public static function getSuffixes(){return $this->suffixes;} // like folder.gallery or folder.album
	
	public function __construct($path = null){
		global $settings, $url;
		if ($path == null) $path = $settings['content_folder'];
		if (!is_dir($path)) {
			throw new Exception('Folder: '.$path.' is no directory!');
			return false;
		}
		parent::__construct($path);
		$this->icon = $settings['resources']."/Folder";
		
		$this->iteminfo['children'] = "";
		$this->iteminfo['items_per_page'] = $settings['items_per_page'];
		$this->iteminfo['number_of_pages'] = "";
		
		$this->findFolderMetas();

	}
	
	// fills the array $iteminfo['children']
	public function scan($args = array()){
		global $settings;
		global $template;
		if ($level == null) $level = $settings['recursion_depth'];

		$path = $this->iteminfo['path'];
		$items = array();
		$index = 0;
		
		foreach($this->scandir($path) as $item){
			$i_path = $path."/".$item;
			if (preg_match('/^_|^\./',$item)) continue;
			if (preg_match('/'.$this['name'].'/',$item)) continue;

			if (is_file($i_path)) $item = FileSystemObject::assign($i_path);
			if (is_dir($i_path)) {
				$item = Folder::assign($i_path);
				if (--$args['level'] > 0) $item->scan($args);
				if (--$args['level'] == 0) $item['metadata'] = $item->findFolderMetas();
			}
			$item->parent = $this;
			$items[$item['path']] = $item; // path is the unique identifier of a filesystem object
		}
		
		if (empty($items)) error_message($this['name'].' is empty!');
		else {
			$this->iteminfo['children'] = $this->aggregate_all($items);
			$this->sort();
			//$this->paginate($args['page']);
		}

	}
	
	public function aggregate_all($items){
		$allDuplicates = $this->getDuplicates($items);
		//foreach($allDuplicates as $sameName) foreach($sameName as $same) echo $same['name'].".".$same['suffix']."<br>";
		foreach($allDuplicates as $duplicates){
			$this->aggregate($duplicates, $items);
		}		
		return $items;
	}
    
	public function getDuplicates($items = null){
		if ($items == null) $items = $this->iteminfo['children'];
		$sameName = array();
		$sameNames = array();

		while(count($items) > 0){
			reset($items);
			$firstkey = key($items);
			$first = array_shift($items);
			foreach ($items as $key => $item) {
				if ($first['name'] == $item['name']) {
					$sameName[$key] = $item;
					unset($items[$key]);
				} 
			}
			if (!empty($sameName)) {
				$sameName[$firstkey] = $first;
				$this->sort_by_type($sameName);
				array_push($sameNames, $sameName);
				$sameName = array();
			}
		}
		return $sameNames;
	}
	
	public function show($args){
		global $url;
		extract($args);
		if (!isset($type))$type = "show"; 
		if (!isset($no_ul_tags)) $no_ul_tags = false;
		if (!isset($level)) $level = 1;
		
		$sizes = array_keys(FileSystemObject::getThumbnailSizes());
		$size = array_search('parenticon',$sizes) !== false ? 'parenticon' : 'icon';
		$this->thumbnail(FileSystemObject::getThumbnailSize($size));
		$this['parent_thumb'] = $this['thumb_url'];
		$this['parent_name'] = $this['name'];
		$this['parent_url'] = $this['nice_url'];
		
		if ($this->iteminfo['children'] == '') $this->scan(array('level' => $level));
		
		if ($this->iteminfo['children'] != '') {

			if ($type == "show" && $url->getType() == "html") $this->paginate();
			foreach($this->iteminfo['children'] as $item) $item->thumbnail(); // needed for $this->show()
		}
		echo $this->encode($type, $no_ul_tags);
		
	}
	
	/* look at URL class. one of the fileblog's features are nice url's. file path = $prefix + name + $suffix */
	public function find($niceURL){
		$niceURL = preg_replace('/\//','\/',$niceURL);
		$this->scan(array('level' => 1, 'page' => $page));
		
		if ($kids = $this->iteminfo['children'] != '') {
			//echo $this['name']."-kids ";
			return $this->paginate($niceURL);
		}
		return false;
	}
	
	/* imagine a two dimensional array as a table where arrays are rows; */
	/* then this function would be similar to getColumn */
	public function reduce_to($key, &$items = null){
		if ($items === null) $items = &$this->iteminfo['children'];
		$result = array();
		if (!is_array($items)) throw new Exception('no array given!');
		foreach ($items as $a_key => $a) if (is_array_accessible($a)) $result[$a_key] = $a[$key];
		return $result;
	}
	
	public function sort(){
		// for overloading: after a directory has been scanned it could be sorted here
		$this->sort_by('fullname');
	}
	
	public function sort_by($key, $order = 'ASC'){
		$GLOBALS['key'] = $key;
		uasort($this->iteminfo['children'], function($a,$b){global $key;return strcasecmp($a[$key],$b[$key]);});
		if ($order == "DESC") $this->iteminfo['children'] = array_reverse($this->iteminfo['children']);
	}
	
	public function sort_by_type(&$items = null){
		if ($items === null) $items = &$this->iteminfo['children'];
		$sort = $this->reduce_to('filetypegroup', $items);

		uasort($sort, array($this, "compare_sameFilenameOrder"));
		foreach($items as $key => $item) $sort[$key] = $item;
		$items = $sort;
	}
	
	private function compare_sameFilenameOrder($a,$b){
		$a = array_search($a, $this->sameFilenameOrder);
		$b = array_search($b, $this->sameFilenameOrder);
		if ($a == $b) {return 0;}
		return ($a < $b) ? -1 : 1;
	}
	
	private function paginate($child = null){
		global $settings;
		global $url;
		
		$ipp = $settings['items_per_page'];
		$count = count($this['children']);
		if ($child == null){
			$this['current_page'] = $page = $p = $url->getPage();
			$this['number_of_pages'] = ceil($count/$ipp);
			$this['next'] = ($this['current_page'] < $this['number_of_pages']) ? preg_replace("/^(.+)(_.+(_.+)*)?$/","$1_".++$p."$2", $this['nice_url']) : $this->parent['nice_url'];
			if ($this['current_page'] > 1)  $this['prev'] = $this['nice_url']."_".($this['current_page'] - 1);
			$start = ($page-1)*$ipp;
			$end = $page*$ipp;
		}
		
		$index = -1;
		$lastkey = false;
		foreach ($this['children'] as $key => &$item){
			++$index;
			$page = ceil(($index+1)/$ipp);
			if ($child != null){
				if (is_object($child)){
					$child['next'] = $item['nice_url'];
					return $child;
				}
				if ($item['name'] == $child){
					$item['current_page'] = $index;
					$item['number_of_pages'] = $count;
					$child = &$item;

				}
			} elseif ($index < $start || $index >= $end ) {
				unset($this->iteminfo['children'][$key]);
				continue;
			} else {
				$item['current_page'] = $page;
			}
			// set prev & next (--> has to be moved to linkChildren())
			$item['next'] = preg_replace("/^(.+)(_.+(_.+)*)?$/","$1_".$page."$2", $item['parent_url']);
			if ($lastkey !== false){
				$item['prev'] = $this['children'][$lastkey]['nice_url'];
				$this['children'][$lastkey]['next'] = $item['nice_url'];
			}
			
			
			$item['parent_url'] = preg_replace("/^(.+)(_.+(_.+)*)?$/","$1_".$page."$2", $item['parent_url']);
			$lastkey = $key;
			if ($index == $count-1){
				if ($child != null) return $child;
			}
		}
	}
	
	private function findFolderMetas(){
		$name = $this['name'];
		$path = $this['path'];
		$matches = array();
		foreach($this->scandir($path) as $subs){
			if (preg_match('/^_|^\./',$subs)) continue;
			//echo " . preg_match: ".preg_match('/'.$this->prefix_regexp.$niceURL.$this->suffix_regexp.'/',$subs);
			if (preg_match('/'.$name.'/',$subs)) {
				$item = FileSystemObject::assign($path."/".$subs);
				//echo "<br>subs: ".$subs." name= ".$item['path']." filetype= ".$item['filetype'];
				$matches[$item['filetype']] = $item;
			}
		}
		
		if (count($matches) > 0) $this['metadata'] = $matches;
	}
	
	private function scandir($path){return scandir($path);	}
	
	public static function find_suffix() {return self::$find_suffix;}
	
	public static function assign($path){
		if (preg_match(self::$find_suffix,$path, $match)){
			$type = $match[3];
			$type = ucwords($type);
			
			if ($type == "Archive") { return new Folder($path);}
			elseif (array_search($type, FileSystemObject::getFolderExtensions()) !== false) {
				try {
					return new $type($path);
				} catch(Exception $e) {
					echo $e;
					return false;
				}
			} else {
				//throw new Exception('Folder::assign(): Folder type indicated by '.$type.' in '.$path.' is not defined as a plugin.');
				return new Folder($path);
			}
		} else {
			return new Folder($path);
		}
	}
}


?>