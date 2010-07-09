<?php
/**
 * Image helper class
 *
 * This class was derived from the show_image_in_imgtag.php and imageTools.class.php files in VM.  It provides some
 * image functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG, RolandD
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */
defined('_JEXEC') or die();

//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart' . DS . 'classes' . DS. "class.img2thumb.php");


/**
 * Image Helper class
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG, RolandD
 */
class ImageHelper {
	/**
	 * Display an image icon for the given image and create a link to the given link.
	 *
	 * @param string $link Link to use in the href tag
	 * @param string $image Name of the image file to display
	 * @param string $text Text to use for the image alt text and to display under the image.
	 */
	public function displayImageButton($link, $image, $text) {
		$button = '<a title="' . $text . '" href="' . $link . '">';
		$button .= JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_48/'.$image, $text, NULL, $text);
		$button .= '<br />' . $text.'</a>';
		echo $button;
				
	}	
	
	
	/**
	 * Display a given image witin IMG tags.
	 *
	 * @param string $image Name of the image file to display
	 * @param string $text Text to use for the image alt text.
	 */
	public function displayImage($image, $text)  {
		$imageHtml  = '<div style="float:left;"><div class="icon">';
		$imageHtml .= JHTML::_('image.administrator',  $image, '/components/com_virtuemart/assets/images/icon_48/', NULL, NULL, $text);
		$imageHtml .= '</div></div>';
		echo $imageHtml;
	}	

	
	/** 
	 * Display a given image in <img> tags
	 *
	 * @author RickG
	 * @param string $image Filename of the image.  No path.
	 * @param string $imgRootFolder The whole URI from joomla path up to the picture, use the variables defined in the config.	 
	 * @param string $imageArgs Attributes to be included in the <img> tag.
	 * @param integer $resize Should this image be auto resized.
	 * @param integer $thumbWidth Width the returned image should be.
	 * @param integer $thumbHeight Height the returned image should be. 
	 * @param boolean $overrideSize If true, $thumbWidth and $thumbHeight will overried image sizes set in the shop configuration.
	 */
	public function displayShopImage($image, $imgRootURI, $imageArgs="", $resize=1, $thumbWidth=0, $thumbHeight=0, $overrideSize=false) {
		echo ImageHelper::generateImageHtml($image, $imgRootURI, $imageArgs, $resize, $thumbWidth, $thumbHeight, $overrideSize);		
	}
	
	/** Return the HTML <img> code for a given image.
	 *
	 * @author RickG
	 * @param string $image Filename of the image.  No path.
	 * @param string $imgRootFolder The whole URI from joomla path up to the picture, use the variables defined in the config.
	 * @param string $imageArgs Attributes to be included in the <img> tag.
	 * @param integer $resize Should this image be auto resized.
	 * @param integer $thumbWidth Width the returned image should be.
	 * @param integer $thumbHeight Height the returned image should be. 
	 * @param boolean $overrideSize If true, $thumbWidth and $thumbHeight will overried image sizes set in the shop configuration.
	 * @return string <img> tag containing the image as the src attribute.  Needs only to be echo'd.
	 */	
	function getShopImageHtml($image, $imgRootURI, $imageArgs="", $resize=1, $thumbWidth=0, $thumbHeight=0, $overrideSize=false) {
		return ImageHelper::generateImageHtml($image, $imgRootURI, $imageArgs, $resize, $thumbWidth, $thumbHeight, $overrideSize);		
	}
	
	/**
	 * Generate the <img> html code for a given image and a given image size.
	 *
	 * @author RickG, RolandD
	 * @param string $image Filename of the image.  No path.
	 * @param string $imgRootURI The whole URI from joomla path up to the picture, use the variables defined in the config.	 
	 * @param string $imageArgs Attributes to be included in the <img> tag.
	 * @param integer $resize Should this image be auto resized.
	 * @param integer $thumbWidth Width the returned image should be.
	 * @param integer $thumbHeight Height the returned image should be. 
	 * @param boolean $overrideSize If true, $thumbWidth and $thumbHeight will overried image sizes set in the shop configuration.
	 * @return string <img> tage containing the image as the src attribute.  Needs only to be echo'd.
	 */
	function generateImageHtml($image, $imgRootURI, $imageArgs="", $resize=1, $thumbWidth=0, $thumbHeight=0, $overrideSize=false) {
		// Process image arguments
		$border="";
		if( strpos( $imageArgs, "border=" )===false ) {
			$border = 'border="0"';
		}
		$newImageHeight = $newImageWidth = '';
		
		if ($image != "") {
			// Remote image URL
			if( substr( $image, 0, 4) == "http" ) {
				$url = $image;
			}
			// Local image file
			else {
				if ($overrideSize) {
					$newImageWidth = $thumbWidth;
					$newImageHeight = $thumbHeight;
				}
				else {
					$newImageWidth = VmConfig::get('pshop_img_width', 90);
					$newImageHeight = VmConfig::get('pshop_img_height', 90);
				}
				
				// Dynamic image resizing will happen
				if (VmConfig::get('pshop_img_resize_enable') == '1' || $resize==1) {
					$url = ImageHelper::createResizedImage(urlencode($image), $imgRootURI, $newImageWidth, $newImageHeight);
					if (!strpos($imageArgs, "height=")) {
						$arr = @getimagesize(ImageHelper::getresizedfilename($image, $imgRootURI, '', $newImageWidth, $newImageHeight));
						$width = $arr[0]; 
						$height = $arr[1];
					}
				}			
				else {
//					if ($imgRootFolder <> '') {
////						$url = JURI::root().'components/com_virtuemart/shop_image/'.$imgRootFolder.'/'.$image;	
//						$url = JURI::root().'/'.$imgRootFolder.$image;	
//					}
//					else {
////					$url = JURI::root().'components/com_virtuemart/shop_image/'.$image;	
						$url = JURI::root().$imgRootURI.$image;	
//					}
					if ($resize) {
						if ($height < $width) {
							$newImageWidth = round($width / ($height / VmConfig::get('pshop_img_height', 90)));
							$newImageHeight = VmConfig::get('pshop_img_height', 90);
						} 
						else {
							$newImageHeight = round($height / ($width / VmConfig::get('pshop_img_width', 90)));
							$newImageWidth = VmConfig::get('pshop_img_width', 90);
						}
						$url = ImageHelper::createResizedImage(urlencode($image), $imgRootURI, $newImageWidth, $newImageHeight);
					}
				}
			}
		}
		else {
			$url = VmConfig::get('vm_themeurl').'images/'.VmConfig::get('no_image');
		}

		return JHTML::image($url, '');
			
	}
	
	
	/**
	 * Create a resized image for a given image if one does not already exit.
	 *
	 * Resized images are currently held in the shop image directory in a resized folder found under the $imageRootFolder.
	 *
	 * @author RickG, RolandD
	 * @param string $imageFilename Filename of the image.  No path included.
	 * @param string $imageRootFolder Folder under the shop imgae location that contains this image.  For example, 'products'.
	 * @param integer $width Width the resized image should be 
	 * @param integer $height Height the resized image should be
	 * @return string URL to the resized image or 'No Imgae'
	 */
	function createResizedImage($imageFilename, $imgRootURI, $width, $height) {
		$maxsize = false;
		$bgred = 255;
		$bggreen = 255;
		$bgblue = 255;

		$origFileInfo = pathinfo($imageFilename);
		$resizedFilename = $origFileInfo['filename'].'_'.$width.'x'.$height.'.'.$origFileInfo['extension'];
		
//		if ($imageRootFolder) {
//			$fullSizeFilenamePath = JPATH_COMPONENT_SITE.DS.'shop_image'.DS.$imageRootFolder.DS.$imageFilename;
//			$resizedFilenamePath = JPATH_COMPONENT_SITE.DS.'shop_image'.DS.$imageRootFolder.DS.'resized'.DS.$resizedFilename;
//		}
//		else {
//			$fullSizeFilenamePath = JPATH_COMPONENT_SITE.DS.'shop_image'.DS.$imageFilename;
//			$resizedFilenamePath = JPATH_COMPONENT_SITE.DS.'shop_image'.DS.'resized'.DS.$resizedFilename;
//		}

		$imageRootFolderExp = explode('/', VmConfig::get('media_product_path'));
		$imageRootFolder = implode(DS, $imageRootFolderExp);

//		$imageRootFolderExp = explode('/', $imgRootURI);
//		$imageRootFolder = implode(DS, $imageRootFolderExp);

		$fullSizeFilenamePath = JPATH_SITE.DS.$imageRootFolder.$imageFilename;
		$resizedFilenamePath = JPATH_SITE.DS.$imageRootFolder.'resized'.DS.$resizedFilename;
//		echo'$fullSizeFilenamePath '.$fullSizeFilenamePath;
		// Don't allow sizes beyond 2000 pixels
		$width = min($width, 2000);
		$height = min($height, 2000);

		if (!file_exists($resizedFilenamePath) && file_exists($fullSizeFilenamePath)) {
			$newFile = new Img2Thumb($fullSizeFilenamePath, $width, $height, $resizedFilenamePath, $maxsize, $bgred, $bggreen, $bgblue);
		}	
		if (file_exists($resizedFilenamePath)) {
//			if ($imageRootFolder <> '') {
////				return JURI::root().'components/com_virtuemart/shop_image/'.$imageRootFolder.'/resized/'.$resizedFilename;
//				return JURI::root().$imageRootFolder.'/resized/'.$resizedFilename;
//			}
//			else {
//				return JURI::root().'components/com_virtuemart/shop_image/resized/'.$resizedFilename;
				return JURI::root().VmConfig::get('media_product_path').'resized/'.$resizedFilename;
//			}				
		}
		else {
			return VmConfig::get('vm_themeurl').'images/'.VmConfig::get('no_image');
		}
	}
	
	
	/**
	 * Returns the filename of an image's resized copy in the /resized folder
	 *
	 * @author RickG
	 * @todo Do we really want to add the width and height to the filename?
	 * @param string $filename Filename of the image we are looking for.
	 * @param string $imageRootFolder Folder under the shop image location that contains this image.  For example, 'products'.
	 * @param string $ext Image extension we ar elooking for,  If none we will use what is on the $filename.
	 * @param integer $height Height of the image we are looking for.  
	 * @param integer $width Width of the image we are looking for.  
	 * @return string Full path to the resized image file.
	 */
	function getResizedFilename($filename, $imgRootURI, $ext='', $height=0, $width=0)
	{
		$fileinfo = pathinfo($filename);
		if ($ext == '') {
			$ext = $fileinfo['extension'];
		}
		
		if ($width == 0) {
			$width = VmConfig::get('pshop_img_width', 90);
		}
		if ($height == 0) {
			$height = VmConfig::get('pshop_img_height', 90);
		}
		
		
		$resizedFilename = $fileinfo['filename'].'_'.$width.'x'.$height.'.'.$fileinfo['extension'];
		$imageRootFolderExp = explode('/', $imgRootURI);
		$imageRootFolder = implode(DS, $imageRootFolderExp);
//		if ($imageRootFolder) {
			return JPATH_SITE.DS.$imageRootFolder.$resizedFilename;
//		}
//		else {
//			return JPATH_COMPONENT.DS.'shop_image'.DS.'resized'.DS.$resizedFilename;
//		}
	}
	
}

/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
* class Image2Thumbnail
* Thumbnail creation with PHP4 and GDLib (recommended, but not mandatory: 2.0.1 !)
*
*
* @author     Andreas Martens <heyn@plautdietsch.de>
* @author     Patrick Teague <webdude@veslach.com>
* @author     Soeren Eberhardt <soeren|at|virtuemart.net>
*@version	1.0b
*@date       modified 11/22/2004
*@modifications 
*   - added support for GDLib < 2.0.1
*	- added support for reading gif images 
*	- makes jpg thumbnails
*	- changed several groups of 'if' statements to single 'switch' statements
*   - commented out original code so modification could be identified.
*/

class Img2Thumb	{
// New modification
/**
*	private variables - do not use
*	
*	@var int $bg_red				0-255 - red color variable for background filler
*	@var int $bg_green				0-255 - green color variable for background filler
*	@var int $bg_blue				0-255 - blue color variable for background filler
*	@var int $maxSize				0-1 - true/false - should thumbnail be filled to max pixels
*/
	var $bg_red;
	var $bg_green;
	var $bg_blue;
	var $maxSize;
	/**
	 * @var string Filename for the thumbnail
	 */
	var $fileout;

/**
*   Constructor - requires following vars:
*	
*	@param string $filename			image path
*	
*	These are additional vars:
*	
*	@param int $newxsize			new maximum image width
*	@param int $newysize			new maximum image height
*	@param string $fileout			output image path
*	@param int $thumbMaxSize		whether thumbnail should have background fill to make it exactly $newxsize x $newysize
*	@param int $bgred				0-255 - red color variable for background filler
*	@param int $bggreen				0-255 - green color variable for background filler
*	@param int $bgblue				0-255 - blue color variable for background filler
*	
*/
	function Img2Thumb($filename, $newxsize=60, $newysize=60, $fileout='',
		$thumbMaxSize=0, $bgred=0, $bggreen=0, $bgblue=0)
	{		
		
		//	New modification - checks color int to be sure within range
		if($thumbMaxSize)
		{
			$this->maxSize = true;
		}
		else
		{
			$this->maxSize = false;
		}
		if($bgred>=0 || $bgred<=255)
		{
			$this->bg_red = $bgred;
		}
		else
		{
			$this->bg_red = 0;
		}
		if($bggreen>=0 || $bggreen<=255)
		{
			$this->bg_green = $bggreen;
		}
		else
		{
			$this->bg_green = 0;
		}
		if($bgblue>=0 || $bgblue<=255)
		{
			$this->bg_blue = $bgblue;
		}
		else
		{
			$this->bg_blue = 0;
		}
		
		$this->NewImgCreate($filename,$newxsize,$newysize,$fileout);
	}
	
/**
*  
*	private function - do not call
*
*/
	function NewImgCreate($filename,$newxsize,$newysize,$fileout)
	{

		$type = $this->GetImgType($filename);
		
		$pathinfo = pathinfo( $fileout );
		if( empty( $pathinfo['extension'])) {
			$fileout .= '.'.$type;
		}
		$this->fileout = $fileout;
		
		switch($type)
		{
			case "gif":
				// unfortunately this function does not work on windows
				// via the precompiled php installation :(
				// it should work on all other systems however.
				if( function_exists("imagecreatefromgif") )
				{
					$orig_img = imagecreatefromgif($filename);
					break;
				}
				else
				{
					echo 'Sorry, this server doesn\'t support <b>imagecreatefromgif()</b>';
					exit;
					break;
				}
			case "jpg":
				$orig_img = imagecreatefromjpeg($filename);
				break;
			case "png":
				$orig_img = imagecreatefrompng($filename);
				break;
		}
		
		$new_img =$this->NewImgResize($orig_img,$newxsize,$newysize,$filename);

		if (!empty($fileout))
		{
			 $this-> NewImgSave($new_img,$fileout,$type);
		}
		else
		{
			 $this->NewImgShow($new_img,$type);
		}
		
		ImageDestroy($new_img);
		ImageDestroy($orig_img);
	}
	
	/**
*  
*	private function - do not call
*	includes function ImageCreateTrueColor and ImageCopyResampled which are available only under GD 2.0.1 or higher !
*/
	function NewImgResize($orig_img,$newxsize,$newysize,$filename)
	{
		//getimagesize returns array
		// [0] = width in pixels
		// [1] = height in pixels
		// [2] = type
		// [3] = img tag "width=xx height=xx" values
		
		$orig_size = getimagesize($filename);

		$maxX = $newxsize;
		$maxY = $newysize;
		
		if ($orig_size[0]<$orig_size[1])
		{
			$newxsize = $newysize * ($orig_size[0]/$orig_size[1]);
			$adjustX = ($maxX - $newxsize)/2;
			$adjustY = 0;
		}
		else
		{
			$newysize = $newxsize / ($orig_size[0]/$orig_size[1]);
			$adjustX = 0;
			$adjustY = ($maxY - $newysize)/2;
		}
		
		/* Original code removed to allow for maxSize thumbnails
		$im_out = ImageCreateTrueColor($newxsize,$newysize);
		ImageCopyResampled($im_out, $orig_img, 0, 0, 0, 0,
			$newxsize, $newysize,$orig_size[0], $orig_size[1]);
		*/

		//	New modification - creates new image at maxSize
		if( $this->maxSize )
		{
			if( function_exists("imagecreatetruecolor") )
			  $im_out = imagecreatetruecolor($maxX,$maxY);
			else
			  $im_out = imagecreate($maxX,$maxY);
			  
			// Need to image fill just in case image is transparent, don't always want black background
			$bgfill = imagecolorallocate( $im_out, $this->bg_red, $this->bg_green, $this->bg_blue );
 		    
			if( function_exists( "imageAntiAlias" )) {
				imageAntiAlias($im_out,true);
			}
 		    imagealphablending($im_out, false);
		    if( function_exists( "imagesavealpha")) {
		    	imagesavealpha($im_out,true);
		    }
		    if( function_exists( "imagecolorallocatealpha")) {
		    	$transparent = imagecolorallocatealpha($im_out, 255, 255, 255, 127);
		    }
			
			//imagefill( $im_out, 0,0, $bgfill );
			if( function_exists("imagecopyresampled") ){
				ImageCopyResampled($im_out, $orig_img, $adjustX, $adjustY, 0, 0, $newxsize, $newysize,$orig_size[0], $orig_size[1]);
			}
			else {
				ImageCopyResized($im_out, $orig_img, $adjustX, $adjustY, 0, 0, $newxsize, $newysize,$orig_size[0], $orig_size[1]);
			}
			
		}
		else
		{
		
			if( function_exists("imagecreatetruecolor") )
			  $im_out = ImageCreateTrueColor($newxsize,$newysize);
			else
			  $im_out = imagecreate($newxsize,$newysize);
			  
			if( function_exists( "imageAntiAlias" ))
			  imageAntiAlias($im_out,true);
 		    imagealphablending($im_out, false);
		    if( function_exists( "imagesavealpha"))
			  imagesavealpha($im_out,true);
		    if( function_exists( "imagecolorallocatealpha"))
			  $transparent = imagecolorallocatealpha($im_out, 255, 255, 255, 127);
			  
			if( function_exists("imagecopyresampled") )
			  ImageCopyResampled($im_out, $orig_img, 0, 0, 0, 0, $newxsize, $newysize,$orig_size[0], $orig_size[1]);
			else
			  ImageCopyResized($im_out, $orig_img, 0, 0, 0, 0, $newxsize, $newysize,$orig_size[0], $orig_size[1]);
		}
		

		return $im_out;
	}
	
	/**
*  
*	private function - do not call
*
*/
	function NewImgSave($new_img,$fileout,$type)
	{
		if( !@is_dir( dirname($fileout))) {
			@mkdir( dirname($fileout) );
		}
		switch($type)
		{
			case "gif":
				if( !function_exists("imagegif") )
				{
					if (strtolower(substr($fileout,strlen($fileout)-4,4))!=".gif") {
						$fileout .= ".png";
					}
					return imagepng($new_img,$fileout);
					
				}
				else {
					if (strtolower(substr($fileout,strlen($fileout)-4,4))!=".gif") {
						$fileout .= '.gif';
					}
					return imagegif( $new_img, $fileout );
					
				}
				break;
			case "jpg":
				if (strtolower(substr($fileout,strlen($fileout)-4,4))!=".jpg")
					$fileout .= ".jpg";
				return imagejpeg($new_img, $fileout, 100);
				break;
			case "png":
				if (strtolower(substr($fileout,strlen($fileout)-4,4))!=".png")
					$fileout .= ".png";
				return imagepng($new_img,$fileout);
				break;
		}
	}
	
	/**
*  
*	private function - do not call
*
*/
	function NewImgShow($new_img,$type)
	{
		/* Original code removed in favor of 'switch' statement
		if ($type=="png")
		{
			header ("Content-type: image/png");
			 return imagepng($new_img);
		}
		if ($type=="jpg")
		{
			header ("Content-type: image/jpeg");
			 return imagejpeg($new_img);
		}
		*/
		switch($type)
		{
			case "gif":
				if( function_exists("imagegif") )
				{
					header ("Content-type: image/gif");
					return imagegif($new_img);
					break;
				}
				else
					$this->NewImgShow( $new_img, "jpg" );
			case "jpg":
				header ("Content-type: image/jpeg");
				return imagejpeg($new_img);
				break;
			case "png":
				header ("Content-type: image/png");
				return imagepng($new_img);
				break;
		}
	}
	
	/**
*  
*	private function - do not call
*
*   1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF,
*   5 = PSD, 6 = BMP,
*   7 = TIFF(intel byte order),
*   8 = TIFF(motorola byte order),
*   9 = JPC, 10 = JP2, 11 = JPX,
*   12 = JB2, 13 = SWC, 14 = IFF
*/
	function GetImgType($filename)
	{
		$info = getimagesize($filename);
		/* Original code removed in favor of 'switch' statement
		if($size[2]==2)
			return "jpg";
		elseif($size[2]==3)
			return "png";
		*/
		switch($info[2]) {
			case 1:
				return "gif";
				break;
			case 2:
				return "jpg";
				break;
			case 3:
				return "png";
				break;
			default:
				return false;
		}
	}
	
}