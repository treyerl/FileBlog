<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

	/* website only works with apaches rewrite module:
	 * www.example.com/index.php/PATH_INFO 
	 */

class URL {
	// $path = 'content_folder/' + $url
	private $item_chain = array();
	private $path;
	private $requestedObject;
	
	//original URL = $serverURL + $requestType + $niceUrl + $page
	private static $serverURL;
	private $type;
	private $page;
	private $lang;
	private $args;
	private $niceURL;
	private $niceURL_chain;
	
	private $isHome;
	private $isError;
	
	public function __construct($niceURL = null){
		global $settings;
		$pathinfo = ($niceURL != null)? $niceURL : preg_replace('/^\//','', $_SERVER['PATH_INFO']);

		// Security I
		if (preg_match('/\.[\.]?\//',$pathinfo)) die('No relative paths containing ".." allowed.');

		// analyse original URL
		$this->setType($pathinfo); // $pathinfo referenced: type will be cut off at the beginning of $pathinfo, if set
		$this->setArgs($pathinfo); // $pathinfo referenced: args will be cut off at the end of $pathinfo, if set
		$this->niceURL = $pathinfo;
		$this->isHome = empty($pathinfo);

		if (!is_array($this->niceURL_chain = preg_split("/\//", $pathinfo, -1, PREG_SPLIT_NO_EMPTY))) $this->niceURL_chain = array();
		$this->findPath();  //translate NiceURLs to file system path
		$this->secure(); //if any folder in the path contains securing information in a .htaccess force authentification
	}
	
	private function setType(&$pathinfo){
		global $settings;
		$prefix = preg_split("/\//", $pathinfo, 2, PREG_SPLIT_NO_EMPTY);
		preg_match('/^__(.+)$/',$prefix[0], $match);
		if (!$match[1]) {
			$this->type = "html";
			return;
		}
		
		$pathinfo = $prefix[1];
		$prefix = $match[1];
		switch($prefix){
			case "json":
				$this->type = 'json';
				break;
			case "":
				$this->type = 'html';
			default:
				$this->type = $prefix;
		}
	}
	
	private function setArgs(&$pathinfo){
		global $settings;
		if (preg_match('/(^.*\/)_(.+(_.+)+)/', $pathinfo, $match)) {
			//echo $match[2];
			$pathinfo = $match[1];
			$match = preg_split('/_/', $match[2], -1);
		} elseif (preg_match('/(^.*\/)_(\d+|[A-Z]{2}|.+)$/',$pathinfo, $match)) {
			array_shift($match);
			$pathinfo = array_shift($match);
		}
		if(preg_match('/^\d+$/',$match[0])) $this->page = array_shift($match);
		else $this->page = 1;
		if(preg_match('/^[A-Z]{2}$/',$match[0])) $this->lang = array_shift($match);
		else $this->lang = $settings['default_lang'];
		$this->args = $match;
	}
	
	private function secure(){
		$auth_path;
		for ($i = 0; $i<count($this->path_chain)-1; $i++) {
			$auth_path .= $this->path_chain[$i]."/";
			if ($this->is_private($auth_path)) {
				$this->auth($auth_path);
				break;
			}
		}
	}
	
	private function auth($path){
		global $settings;
		if($_SERVER["HTTPS"] != "on") {
		   header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
		   die();
		}
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="'.$settings['auth_realm'].'"');
			header('HTTP/1.0 401 Unauthorized');
			die('You are not allowed to access this content.');
		}
	}
		
	private function is_private($path){
		if (is_dir($path)) {
			if (file_exists($path."/.htaccess")) {
				if($fp=fopen($path."/.htaccess",'r')){
					while($line=fgets($fp)){
						$line=preg_replace('`[\r\n]$`','',$line);
						if (preg_match('/AuthType/',$line)) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
	public function findPath() {
		// if there is the same filename with more than one suffix, folder_types must provide an order. default is provided by filesystem
		global $settings;
		$path = $settings['content_folder'];
		$path_chain = array();
		$root = $item = Folder::assign($path);
		foreach ($this->niceURL_chain as $URLPart){
			$item = $item->find($URLPart);
			if (($item) === false) die($item['path'].': '.$URLPart.' not found.');		
			$path .= "/".$item['fullname'];
			array_push($this->item_chain,$item);
			$item['css_class'] .= " current-parent";
		}
		
		if (preg_match('/current-parent/',$item['css_class'])){
			$item['css_class'] = preg_replace("/current-parent/","current-item", $item['css_class']);
		} else $item['css_class'] .= "current-item";
		
		$this->path = $path;
		$this->requestedObject = $item;
		return true;
	}
	
	public function getType() { return $this->type;}
	
	public function getPage() { 
		if ($this->type == "html") return $this->page;
		else return false;
	}
	
	public function getLang() { return $this->lang;}
	
	public function getArgs() { return $this->args;}
	
	public function is_home() { return $this->isHome; }
	
	public function getNiceURL() { return $this->niceURL;}

	public function getNiceURL_chain() { return $this->niceURL_chain;}
	
	public function getPath() { return $this->path;}
	
	public function getPathChain() {return $this->$path_chain;}
	
	public function getRequestedObject() {return $this->requestedObject;}
	
	private static function setServerURL(){
		global $settings;
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://".$_SERVER["SERVER_NAME"];
		$pageURL .=  preg_replace('/'.$settings['system_folder'].'\/index.php$/','',$_SERVER['SCRIPT_NAME']);
		if ($_SERVER["SERVER_PORT"] != "80") $pageURL .= ":".$_SERVER["SERVER_PORT"];
		self::$serverURL =  $pageURL;
	}
	
	public static function getServerURL(){
		if (!isset(self::$serverURL)) self::setServerURL();
		return self::$serverURL;
	}
}
?>