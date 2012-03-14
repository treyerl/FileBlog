<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* Watch out for filname_languageAbbreviations.txt and filename_comments.txt, 
*  combine it with filename.txt and store it in a Text-Object. */

class Text extends FileSystemObject{
	public function __construct($args){
		parent::__construct($args);
		$this['content'] = $this->get_content();
	}
	
	public function get_content(){
		return file_get_contents($this['path']);
	}
}

?>