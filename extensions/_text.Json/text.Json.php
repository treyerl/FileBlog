<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* Watch out for filname_languageAbbreviations.txt and filename_comments.txt, 
*  combine it with filename.txt and store it in a Text-Object. */

class Json extends Text{
	public function get_content(){
		$json = json_decode(file_get_contents($this['path']), true);
		if (is_array($json)) $this->iteminfo = array_merge($this->iteminfo, $json);
	}
}

?>