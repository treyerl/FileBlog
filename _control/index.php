<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/*
please make sure the .htaccess file exists in your root directory.
It should contain something similar to this:

Options +FollowSymLinks

RewriteEngine on
RewriteBase   /yoursite.com/
#RewriteBase   /~username/FileBlog/  (for local testing on a mac)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ _control/index.php/$1 [L]
RewriteRule ^(/)?$ _control/index.php [L]

*/
require_once('config.php');
require_once('_array.php');
require_once('_defaults.php');
require_once('_filetypes.php');
require_once('_FileSystemObject.php');
require_once('_Folder.php');
require_once('_Template.php');
require_once('_URL.php');
require_once('_User.php');
chdir('..');

$empty_gif = URL::getServerURL()."/".$settings['resources']."/empty.gif";
$template = new Template();
FileSystemObject::loadExtensions();
$url = new URL();
$template->load();

//echo preg_replace('/'.$settings['system_folder'].'\/index.php$/','',$_SERVER['SCRIPT_NAME']);

?>