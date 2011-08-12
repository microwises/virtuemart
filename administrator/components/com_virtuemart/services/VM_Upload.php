<?php
define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart Product SOA Connector
 *
 * Virtuemart Product SOA Connector : File for upload file into components/com_virtuemart/shop_image/product,
 * components/com_virtuemart/shop_image/category, components/com_virtuemart/shop_image/vendor
 *
 * @package    com_vm_soa
 * @subpackage component
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

exit404();//dont use it now, not ready


global $mosConfig_absolute_path, $sess;
global $jscook_type, $jscookMenu_style, $jscookTree_style;

$mosConfig_absolute_path= realpath( dirname(__FILE__).'/../../../..' );

// Load the joomla main cfg
if( file_exists(dirname(__FILE__).'/configuration.php' )) {
	require_once( $mosConfig_absolute_path.'/configuration.php' );
	
} else {
	require_once( $mosConfig_absolute_path.'/configuration.php');
}

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../../../components/com_virtuemart/virtuemart_parser.php' );
	$mosConfig_absolute_path = realpath( dirname(__FILE__).'/../..' );
} else {
	require_once( dirname(__FILE__).'/../../../../components/com_virtuemart/virtuemart_parser.php');
}
 
include('../vm_soa_conf.php');

/* Authenticate*/
$result = onAdminAuthenticate($_REQUEST['login'], $_REQUEST['pass']);
if ($conf['auth_all_upload']=="on"){
	//$result = "true";
}else {
	$result = "false";
}

//Auth OK
if ($result == "true"){
		
		$thumbW = $_REQUEST['thumbW'];
		$thumbH = $_REQUEST['thumbH'];
		
		
		//  15MB maximum file size
		$MAXIMUM_FILESIZE = 15 * 1024 * 1024; 
		$rEFileTypes = "/^\.(jpg|jpeg|gif|png|doc|docx|txt|rtf|pdf|xls|xlsx|ppt|pptx|zip|rar){1}$/i"; 

		if ($_REQUEST['dir'] == "product") {
			$dir = realpath( dirname(__FILE__).'/../../../../components/com_virtuemart/shop_image/product' );
		} else if ($_REQUEST['dir'] == "category") {
			$dir = realpath( dirname(__FILE__).'/../../../../components/com_virtuemart/shop_image/category' );
		}else if ($_REQUEST['dir'] == "vendor") {
			$dir = realpath( dirname(__FILE__).'/../../../../components/com_virtuemart/shop_image/vendor' );
		}else if ($_REQUEST['dir'] == "media") {
			$dir = realpath( dirname(__FILE__).'/../../../../media' );
		}
		$tempFile = $_FILES['Filedata']['tmp_name'];
		$fileName = $_FILES['Filedata']['name'];
		$fileSize = $_FILES['Filedata']['size'];
		$extension= strrchr($fileName, '.');
		
		if ($_FILES['Filedata']['size'] <= $MAXIMUM_FILESIZE && preg_match($rEFileTypes, $extension)) {

			//
			if ($_REQUEST['dir'] == "product" || $_REQUEST['dir'] == "category"){
				$ret = uploadAndCreateThumb($dir,$dir.'/resized',$thumbW,$thumbH);
			}else {
				$ret = move_uploaded_file($tempFile, $dir."/" . $fileName);
			}
		}else {
			$ret = false;
			echo "<result> upload KO </result> ";
		}
		if ($ret){
			echo "<result> upload OK </result> ";
		} else {
			echo "<result> upload KO 404 </result> ";
			//echo "<error> upload KO </error>";
			exit404();
		}
		
		
} else {
	//echo "<error> AUTH KO </error>";
	exit404();
}

/**
* this function upload image to VM dir and create thumb into "resized" vm dir
* work with jpg, png, gif
*/
function uploadAndCreateThumb($dirFull,$dirThumb,$thumb_widht,$thumb_height){

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

	if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
		$file_name=$_FILES['Filedata']['name'];
		move_uploaded_file($_FILES['Filedata']['tmp_name'], $save_to."/".basename($file_name));

		//echo $file_name." was uplaoded successfully";

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
		} else {
			echo "No thumbnail was generated - only JPG|PNG|GIF files are supported. your extention : ".$ex;
			return false;
		}
	} else {
		echo "Error was occured while uploading!";
		return false;
	}    
	
	return true;

}

	/**
    *  function onAuthenticate
	* (not expose as WS)
    * @param login/pass
    * @return true/false
   */
	function onAdminAuthenticate($login,$passwd){
	
		jimport('joomla.user.helper');
		$response="false";
		$db = new ps_DB;

		$list  = "SELECT id, username, password, usertype FROM `#__users` ";
		$list .= "WHERE username='".$login."' ";
		
		$response=$list;
		//$list .= $q . " LIMIT 0,100 "; 
		
		$db = new ps_DB;
		$db->query($list);

		/* function inspired by onAuthenticate (joomla.php) | Verify password is good*/
		if($db->next_record())
		{
			$parts	= explode( ':', $db->f("password") );
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($passwd, $salt);

			if ($crypt == $testcrypt ) {
				$response= "no_admin";
				if ( $db->f("usertype") == "Super Administrator" ){
					$response= "true";
				}
			} else {
				$response= "false";
			}
		}
		else
		{
			$response="no_user";
		}
		/////////////////////////////
		return $response;
	}
	
function exit404() {
	global $_SERVER;
	header ("HTTP/1.1 404 Not Found");
	exit();
}


?>