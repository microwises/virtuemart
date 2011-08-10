<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Virtuemart Commons method SOA Connector
 *
 * Virtuemart Product SOA Connector : File for upload file into components/com_virtuemart/shop_image/product,
 * components/com_virtuemart/shop_image/category, components/com_virtuemart/shop_image/vendor
 *
 * @package    com_vm_soa
 * @subpackage component
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2011 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

define('DS', DIRECTORY_SEPARATOR);

$soa_dir 	= dirname(__FILE__);
$jpath 		= realpath( dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.'' );
$jadminpath = realpath( dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'' );

define('JPATH_BASE',$jadminpath );
define('JPATH',$jpath );

if (file_exists(JPATH_BASE . '/includes/defines.php')) {
	include_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE.'/includes/framework.php';
require_once JPATH_BASE.'/includes/helper.php';
require_once JPATH_BASE.'/includes/toolbar.php';

// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('site');

// Initialise the application.
$app->initialise();

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
$vmConfig = VmConfig::loadConfig();

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'tables');

include('../vm_soa_conf.php');

define ("OK" , "0");
define ("KO" , "1");
define ("ADD" , 2);
define ("UP" , 3);
define ("DEL" , 4);
define ("NOTALLOK" , 5);
define ("ADDKO" , 6);
define ("UPKO" , 7);
define ("DELKO" , 8);
define ("ALLOK" , 9);

	/**
    * This function return string message for WS
	* (NOT expose as WS)
    * @param string
    * @return result
    */

	function getWSMsg($nameObj,$type) {
	
		$msg = "Undefined";
		
		if ($type == ADD){
			$msg = $nameObj.' successfully added ';
		}else if ($type == UP) {
			$msg = $nameObj.' successfully Updated ';
		}else if ($type == DEL) {
			$msg = $nameObj.' successfully deleted ';
		}else if ($type == NOTALLOK) {
			$msg = 'Not all '.$nameObj.' processed successfully ';
		}else if ($type == ADDKO) {
			$msg = "Cannot add ".$nameObj.' ';
		}else if ($type == UPKO) {
			$msg = "Cannot update ".$nameObj.' ';
		}else if ($type == DELKO) {
			$msg = "Cannot delete ".$nameObj.' ';
		}else if ($type == ALLOK) {
			$msg = "All ".$nameObj.' processed successfully ';
		}
		return $msg;
	
	}
	
	/**
    * This function Set Post var with token
	* (NOT expose as WS)
    * @param 
    * @return 
    */
	function setToken() {
	
		$token  = JUtility::getToken();
		$_REQUEST[$token] = $token;
		$_POST[$token] = $token;
	
	}


	/**
    *  function write a file ($data must be enocoded in base64Binary )
	* (not expose as WS)
    * @param login/pass
    * @return False/fileuurl 
	*/
	function writeMedia($data,$filename,$type,$isImg=false){
	
		if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
		$vmConfig = VmConfig::loadConfig();
		
		$img_width = $vmConfig->get('img_width');
		$img_height = $vmConfig->get('img_height');
		
		if ($type == 'category'){
			$media_path = $vmConfig->get('media_category_path');
		} else if ($type == 'product'){
			$media_path = $vmConfig->get('media_product_path');
		}else if ($type == 'manufacturer'){
			$media_path = $vmConfig->get('media_manufacturer_path');
		} else {
			return false;
		}
		
		$dir = JPATH.DS.$media_path.'';	
		
		$fileServerPath = $dir.$filename; // eg : /httpdocs/www/joomla/images/stories/virtuemart/category/toto.jpg
		$fileURL = $media_path.$filename; //eg : images/stories/virtuemart/category/toto.jpg
		
		//SAVE FILE 
		$ifp = fopen( $fileServerPath, "wb" );
		//$data must be enocoded in base64Binary 
		fwrite( $ifp,  $data  );
		fclose( $ifp ); 
		
		if (!file_exists($fileServerPath)) {
			return false;
		}
		
		
		if ($isImg){
			//if is image then create thumb image
			$ret = createThumb($fileServerPath,$dir,$dir.DS.'resized',$img_width,$img_height);
			if (!$ret){
				return false;
			}
			$filesurls[0] = $fileURL; // full
			$filesurls[1] = $media_path.'resized/'.$ret; //thumb
			return $filesurls;
			
		} else {
			$filesurls[0] = $fileURL; // full
			return $filesurls; 
		}
		
	}
	
	/**
    *  function create Thumb image 
	* (not expose as WS)
    * @param file /dir/dir thumb /w /h
    * @return False/true
   */
	function createThumb($file,$dirFull,$dirThumb,$thumb_widht,$thumb_height){

		// set folder for saving uploaded images
		//$save_to="/httpdocs/images";
		$save_to=$dirFull;

		// set folder for saving thumbnails of uploaded images
		//$thumb_save_to="/httpdocs/images/thumbs";
		$thumb_save_to=$dirThumb;
		// default width of thumbnails (in pixels)
		if (empty($thumb_widht)){
			$thumb_w=90;
		} else {
			$thumb_w=$thumb_widht;
		}

		if (empty($thumb_height)){
			
		} else {
			$h=$thumb_height;
		}

		if (true) {
			$file_name=$file;
			// get file extension   
			$ex= strrchr($file_name, '.');
			
			//$ex=strtolower(substr($file['name'], strrpos($file['name'], ".")+1, strlen($file['name'])));
			if ($ex==".jpg" || $ex==".JPG") {
				// read the uploaded JPG file
				$img=imagecreatefromjpeg($save_to."/".basename($file_name));

				// get dimension of the image
				$ow=imagesx($img);
				$oh=imagesy($img);

				// keep aspect ratio
				$scale=$thumb_w/$ow;
				if (empty($h))
				$h=round($oh*$scale);

				$newimg=imagecreatetruecolor($thumb_w,$h);
				imagecopyresampled($newimg,$img,0,0,0,0,$thumb_w,$h,$ow,$oh);

				// saving the JPG thumbnail
				$FileNameTosave = pathinfo($file_name, PATHINFO_FILENAME); 
				$FileNameTosave .= "_".$h."x".$thumb_w.$ex;
				//imagejpeg($newimg, $thumb_save_to."/".$file_name, 90);
				imagejpeg($newimg, $thumb_save_to."/".$FileNameTosave, 90);
				
				return $FileNameTosave;
				
			} else if ($ex==".png" || $ex==".PNG") {
				// read the uploaded JPG file
				$img=imagecreatefrompng($save_to."/".basename($file_name));

				// get dimension of the image
				$ow=imagesx($img);
				$oh=imagesy($img);

				// keep aspect ratio
				$scale=$thumb_w/$ow;
				if (empty($h))
				$h=round($oh*$scale);

				$newimg=imagecreatetruecolor($thumb_w,$h);
				imagecopyresampled($newimg,$img,0,0,0,0,$thumb_w,$h,$ow,$oh);

				// saving the JPG thumbnail
				$FileNameTosave = pathinfo($file_name, PATHINFO_FILENAME); 
				$FileNameTosave .= "_".$h."x".$thumb_w.$ex;
				//imagejpeg($newimg, $thumb_save_to."/".$file_name, 90);
				imagepng($newimg, $thumb_save_to."/".$FileNameTosave, 9);
				
				return $FileNameTosave;
				
			} else if ($ex==".gif" || $ex==".GIF") {
				// read the uploaded JPG file
				$img=imagecreatefromgif($save_to."/".basename($file_name));

				// get dimension of the image
				$ow=imagesx($img);
				$oh=imagesy($img);

				// keep aspect ratio
				$scale=$thumb_w/$ow;
				if (empty($h))
				$h=round($oh*$scale);

				$newimg=imagecreatetruecolor($thumb_w,$h);
				imagecopyresampled($newimg,$img,0,0,0,0,$thumb_w,$h,$ow,$oh);

				// saving the JPG thumbnail
				$FileNameTosave = pathinfo($file_name, PATHINFO_FILENAME); 
				$FileNameTosave .= "_".$h."x".$thumb_w.$ex;
				//imagejpeg($newimg, $thumb_save_to."/".$file_name, 90);
				imagegif($newimg, $thumb_save_to."/".$FileNameTosave, 90);
				
				return $FileNameTosave;
				
			} else {
				//echo "No thumbnail was generated - only JPG|PNG|GIF files are supported. your extention : ".$ex;
				return false;
			}
		} else {
			//echo "Error was occured while uploading!";
			return false;
		}    
		
		return true;

	}
	
	/**
	*
	* Mime type is Image ?
	*/
	function isMimeTypeImg($mimeType){
	
		$tab = explode('/',$mimeType);
		$mt = $tab[1];
		
		if ($mt=='jpg' || $mt=='jpeg' || $mt=='pjpeg' || $mt=='pjpeg' || $mt=='pjpeg' || $mt=='png' || $mt=='gif' ){
			return true;
		}else {
			return false;
		}
	
	}
	
	/**
	* mimetpe -> image/jpeg to .jpg
	*
	*/
	function mimeTypeToExtention($mimeType){
	
		$tab = explode('/',$mimeType);
		$mt = $tab[1];
		
		if ($mt=='jpg' || $mt=='jpeg' || $mt=='pjpeg' ){
			return '.jpg';
		}
		if ($mt=='png'){
			return '.png';
		}
		if ($mt=='gif'){
			return '.gif';
		}
		if ($mt=='x-gzip' || $mt=='x-gzip'){
			return '.gzip';
		}
		if ($mt=='x-zip' || $mt=='zip' || $mt=='x-zip-compressed' || $mt=='x-compressed'){
			return '.zip';
		}
		if ($mt=='xml'){
			return '.xml';
		}
		if ($mt=='x-excel'){
			return '.xls';
		}
		if ($mt=='msword'){
			return '.doc';
		}
		if ($mt=='pdf'){
			return '.pdf';
		}
		
		
		return '';
	
	}
	
	/**
    * This function binb object to data
	* (NOT expose as WS)
    * @param string The Object
    * @return array of key and value
   */
   
	function bindObject($obj,&$data) {
		
		foreach ($obj as $key => $value){
			$data[$key]= $value;
		}
	}
	
	/**
    * This function binb object to data
	* (NOT expose as WS)
    * @param string The Object
    * @return array of key and value
   */
   
	function bindArray($obj,&$data) {
		
		foreach ($obj as $key => $value){
			$data[$key]= $value;
		}
	}
	
	/**
	*
	* DEBUG
	*
	**/
	function debugInfile($data) {
		$on = true;
		if ($on){
			ob_start();
			print_r(PHP_EOL.'------------------'.date('l jS \of F Y h:i:s A').'-----------------'.PHP_EOL);
			print_r( $data );
			$output = ob_get_clean();
			file_put_contents( 'debug.txt', file_get_contents( 'debug.txt' ) . $output );
		}

	}
	

	/**
    *  function onAuthenticate
	* (not expose as WS)
    * @param login/pass
    * @return true/false
   */
	function onAdminAuthenticate($login,$passwd,$isEncrypted=false){
	
		jimport('joomla.user.helper');
		
		$credentials['password']=$passwd;
		$credentials['username']=$login;
		
		if ($isEncrypted == "true" || $isEncrypted == "Y" || $isEncrypted == "1"  ){
			$isEncrypted = true;
		}else {
			$isEncrypted = false;
		}

		// Joomla does not like blank passwords
		if (empty($credentials['password'])) {
			return false;
		}

		// Initialise variables.
		$conditions = '';

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id, password');
		$query->from('#__users');
		$query->where('username=' . $db->Quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result) {
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			
			if ($isEncrypted){
				$testcrypt = $credentials['password'];
			}else {
				$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt);
			}
			

			if ($crypt == $testcrypt) {
				$user = JUser::getInstance($result->id); // Bring this in line with the rest of the system
				$autorGroups =$user->getAuthorisedGroups();
				
				if ($autorGroups['1'] == '8'){ // /  8 	is 	Super Users //to ameliorate in future
					return "true";
				} else {
					return "no_admin";
				}
				
			} else {
				$ret= "false";
				
			}
		} else {
			$ret= "false";
		}
		return $ret;
	}
	
	/**
	 * 
	 * 404
	 */
	function exit404() {
		global $_SERVER;
		header ("HTTP/1.1 404 Not Found");
		exit();
	}


?>