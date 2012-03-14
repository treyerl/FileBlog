<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* Watch out for filname_languageAbbreviations.txt and filename_comments.txt, 
*  combine it with filename.txt and store it in a Text-Object. */

class Captcha extends User{
	public function __construct(){
		parent::__construct();
	}
	
	public function login(){
		// check for email adress in form
		// check for cookie
	}
	
	public static function generate() {
	
	}
}

?>