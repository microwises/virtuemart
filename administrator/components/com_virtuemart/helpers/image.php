<?php
/**
 * Image helper class
 *
 * This class was derived from the show_image_in_imgtag.php and imageTools.class.php files in VM.  It provides some
 * image functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

defined('_JEXEC') or die();

if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

class VmImage extends VmMediaHandler {

	function addMediaActionByType(){

		parent::addMediaActionByType();
		if( function_exists('imagecreatefromjpeg')){
			$this->addMediaAction('create_thumb','COM_VIRTUEMART_FORM_MEDIA_CREATE_THUMBNAIL');
		}
	}

	function addMediaAttributesByType(){

		parent::addMediaAttributesByType();
		$this->addMediaAttributes('file_is_product_image','COM_VIRTUEMART_FORM_MEDIA_PRODUCT_IMAGE');

	}

	function processAction($data){
		
		if(empty($data['media_action'])) return $data;
		$data = parent::processAction($data);

		if( $data['media_action'] == 'upload_create_thumb' ){
			$oldFileUrl = $this->file_url;
			$file_name = $this->uploadFile($this->file_url_folder);
			if($file_name){
				if($file_name!=$oldFileUrl && !empty($this->filename)){
					$this->deleteFile($oldFileUrl);
				}
				$this->file_url = $this->file_url_folder.$file_name;
				$this->filename = $file_name;

				$oldFileUrlThumb = $this->file_url_thumb;
				$this->file_url_thumb = $this->createThumb();
				if($this->file_url_thumb!=$oldFileUrlThumb){
					$this->deleteFile($oldFileUrlThumb);
				}
			}
		} //creating the thumbnail image
		else if( $data['media_action'] == 'create_thumb' ){
			$this->file_url_thumb = $this->createThumb();
		}

		if(empty($this->file_title) && !empty($file_name)) $this->file_title = $file_name;

		return $data;
	}

	function displayMediaFull($imageArgs='',$lightbox=true){

		// Remote image URL
		if( substr( $this->file_url, 0, 4) == "http" ) {
			$file_url = $this->file_url;
			$file_alt = $this->file_title;
		} else {
			$rel_path = str_replace('/',DS,$this->file_url_folder);
			$fullSizeFilenamePath = JPATH_ROOT.DS.$rel_path.$this->file_name.'.'.$this->file_extension;
			if (!file_exists($fullSizeFilenamePath)) {
				$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
				$file_alt = JText::_('COM_VIRTUEMART_NO_IMAGE_FOUND').' '.$this->file_description;
			} else {
				$file_url = $this->file_url;
				$file_alt = $this->file_description;
			}
		}

		return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox);

	}


	/**
	 * a small function that ensures that we always build the thumbnail name with the same method
	 */
	private function createThumbName($width=0,$height=0){

		if(empty($this->file_name)) return false;
		if(empty($width)) $width = VmConfig::get('img_width', 90);
		if(empty($height)) $height = VmConfig::get('img_height', 90);

		$this->file_name_thumb = $this->file_name.'_'.$width.'x'.$height;
		return $this->file_name_thumb;
	}

	/**
	 * This function actually creates the thumb
	 * and when it is instanciated with one of the getImage function automatically updates the db
	 *
	 * @author Max Milbers
	 * @param boolean $save Execute update function
	 * @return name of the thumbnail
	 */
	public function createThumb() {

		if(!VmConfig::get('img_resize_enable')) return;
		//now lets create the thumbnail, saving is done in this function
		$width = VmConfig::get('img_width', 90);
		$height = VmConfig::get('img_height', 90);

		// Don't allow sizes beyond 2000 pixels //I dont think that this is good, should be config
//		$width = min($width, 2000);
//		$height = min($height, 2000);

		$maxsize = false;
		$bgred = 255;
		$bggreen = 255;
		$bgblue = 255;

		$rel_path = str_replace('/',DS,$this->file_url_folder);
		$fullSizeFilenamePath = JPATH_ROOT.DS.$rel_path.$this->file_name.'.'.$this->file_extension;

		$this->file_name_thumb = $this->createThumbName();
		$file_path_thumb = str_replace('/',DS,$this->file_url_folder_thumb);
		$resizedFilenamePath = JPATH_ROOT.DS.$file_path_thumb.$this->file_name_thumb.'.'.$this->file_extension;

		if (file_exists($fullSizeFilenamePath)) {
			if (!class_exists('Img2Thumb')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'img2thumb.php');
			$createdImage = new Img2Thumb($fullSizeFilenamePath, $width, $height, $resizedFilenamePath, $maxsize, $bgred, $bggreen, $bgblue);
			if($createdImage){
				return $this->file_url_folder_thumb.$this->file_name_thumb.'.'.$this->file_extension;
			} else {
				return 0;
			}
		} else {
			return 0;
		}

	}


	/**
	 * Display an image icon for the given image and create a link to the given link.
	 *
	 * @param string $link Link to use in the href tag
	 * @param string $image Name of the image file to display
	 * @param string $text Text to use for the image alt text and to display under the image.
	 */
	public function displayImageButton($link, $imageclass, $text) {
		$button = '<a title="' . $text . '" href="' . $link . '">';
		$button .= '<span class="vmicon48 '.$imageclass.'"></span>';
		$button .= '<br />' . $text.'</a>';
		echo $button;

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


