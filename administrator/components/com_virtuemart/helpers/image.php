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

/**
 * Image Helper class
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @author RickG, RolandD
 */
//class VmImage {
class VmImage {

	var $id = 0;
	var $media_filename = '';
	var $media_filename_thumb = '';

	var $media_url = '';
	var $media_url_thumb = '';

	var $media_path ='';
	var $media_path_thumb = '';
//	var $media_path_writeable = 0;

//	var $image_exist = 0;
//	var $image_exist_resized = 0;
	var $theme_url = 0;

	function __construct($media_path_url,$media_filename,$media_thumb_name=0) {
		$this->theme_url = VmConfig::get('vm_themeurl',0);
		if(empty($this->theme_url)){
			$this->theme_url = JURI::root().'components/com_virtuemart/';
		}
		$this->media_url = $media_path_url;
		$this->media_url_thumb = $media_path_url.'resized/';

		$media_path = str_replace('/',DS,$media_path_url);
		$media_path = substr($media_path,0,-1);
		$this->media_path = JPATH_ROOT.DS.$media_path.DS;
		$this->media_path_thumb = JPATH_ROOT.DS.$media_path.DS.'resized'.DS;

		$this->media_filename = $media_filename;
		$this->media_filename_thumb = $media_thumb_name;
	}

	function getShopImage($filename,$thumb_filename=0){
		return new VmImage( VmConfig::get('media_path'),$filename,$thumb_filename);
	}

	/**
	 * For displaying vendor/shop/store images
	 * Sets the variables of the images object, so that the new resized image path can be saved
	 *
	 * @author Max Milbers
	 * @param string $filename name of the full sized image
	 * @param string $filename name of the thumb image
	 * @param integer vendor_id for updating the vendor
	 * @param VmImage an image object with extra product attributes
	 */
	function getVendorImage($filename,$thumb_filename=0,$vendor_id=0){
		$image = new VmImage('components/com_virtuemart/assets/images/vendors/',$filename,$thumb_filename);
		$image->table = 'vendor';
		$image->id = $vendor_id;
		$image->idfield = 'vendor_id';
		$image->mfield = 'vendor_thumb_image ';
		return $image;
	}

	/**
	 * Small proxy function for getVendorImage, which just works with the vendor object
	 *
	 * @author Max Milbers
	 * @param object vendor object given by §vendorModel->getVendor()
	 * @return VmImage an image object with extra vendor attributes
	 */
	function getImageByVendor($vendor){
		if(empty($vendor)) return JText::_('VM_CANT_CREATE_IMAGE_NO_VENDOR_GIVEN');
		return self::getVendorImage($vendor->vendor_full_image,$vendor->vendor_thumb_image,$vendor->vendor_id);
	}

	/**
	 * For displaying product images
	 * Sets the variables of the images object, so that the new resized image path can be saved
	 *
	 * @author Max Milbers
	 * @param string $filename name of the full sized image
	 * @param string $filename name of the thumb image
	 * @param integer product_id for updating the product
	 * @param VmImage an image object with extra product attributes
	 */
	function getProductImage($filename,$thumb_filename=0,$product_id=0){
		$image = new VmImage( VmConfig::get('media_product_path'),$filename,$thumb_filename);
		$image->table = 'product';
		$image->id = $product_id;
		$image->idfield = 'product_id';
		$image->mfield = 'product_thumb_image';
		return $image;
	}

	/**
	 * Small proxy function for getProductImage, which just works with the product object
	 *
	 * @author Max Milbers
	 * @param object product object given by §productModel->getProduct()
	 * @return VmImage an image object with extra product attributes
	 */
	function getImageByProduct($product){
		if(empty($product)) return JText::_('VM_CANT_CREATE_IMAGE_NO_PRODUCT_GIVEN');
		return self::getProductImage($product->product_full_image,$product->product_thumb_image,$product->product_id);
	}

	/**
	 * For displaying category images
	 * Sets the variables of the images object, so that the new resized image path can be saved
	 *
	 * @author Max Milbers
	 * @param string $filename name of the full sized image
	 * @param string $filename name of the thumb image
	 * @param integer category_id for updating the category
	 * @return VmImage an image object with extra category attributes
	 */
	function getCatImage($filename,$thumb_filename=0,$cat_id=0){
		$image =  new VmImage( VmConfig::get('media_category_path'),$filename,$thumb_filename);
		$image->table = 'category';
		$image->id = $cat_id;
		$image->idfield = 'category_id';
		$image->mfield = 'category_thumb_image';
		return $image;
	}

	/**
	 * Small proxy function for getCatImage, which just works with the category object
	 *
	 * @author Max Milbers
	 * @param object category object given by §categoryModel->getCategory()
	 * @return VmImage an image object with extra category attributes
	 */
	function getImageByCat($cat){
		if(empty($cat)) return JText::_('VM_CANT_CREATE_IMAGE_NO_CATEGORY_GIVEN');
		return self::getCatImage($cat->category_full_image,$cat->category_thumb_image,$cat->category_id);
	}

	/**
	 * This function should display the image, when the image is not already a resized one,
	 * it tries to get first the resized one, or create a resized one or fallback in case
	 *
	 * @author Max Milbers
	 *
	 * @param string $imageArgs Attributes to be included in the <img> tag.
	 * @param string $alt alternative Text to display
	 * @param boolean $preferResized Try to get the resided image, when in config allowed, create a thumbnail and update the db
	 */
	function displayImage($imageArgs="",$alt = '', $preferResized=1, $dynCreate=1){


		if (empty($this->media_filename) && empty($this->media_filename_thumb)) {
			$url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_set');
			return JHTML::image($url, JText::_('NO_IMAGE_SET'));
		} else {
			if($preferResized){
				if(empty($this->media_filename_thumb)){
					$this->media_filename_thumb = $this->createThumbName();
				}
				$completeImageUrl = $this->media_url_thumb.$this->media_filename_thumb;
				$completeImagePath = $this->media_path_thumb.$this->media_filename_thumb;
				if(empty($alt)) $alt = $this->media_filename_thumb;
			} else {
				if(empty($this->media_filename)){
					$url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_set');
					return JHTML::image($url, JText::_('NO_IMAGE_SET'));
				}
				$completeImageUrl = $this->media_url.$this->media_filename;
				$completeImagePath = $this->media_path.$this->media_filename;
				if(empty($alt)) $alt = $this->media_filename;
			}
		}

		// Remote image URL
		if( substr( $completeImageUrl, 0, 4) == "http" ) {
			return JHTML::image($completeImageUrl, $alt);
		}

		if (!file_exists($completeImagePath)) {

			if($dynCreate && VmConfig::get('img_resize_enable') == '1' && $preferResized){
				$newFileName = $this->createThumb($dynCreate);
				if($newFileName){
					$completeImagePath = $this->media_path.$newFileName;
					$this->media_filename_thumb = $newFileName;
				}
			} else {
				$url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
				return JHTML::image($url,JText::_('NO_IMAGE_FOUND').' '.$alt);
			}
		}

		//okey the pictures exist, does we want the resized one? if not, just give the normal picture back
		if($preferResized){
			return JHTML::image($this->media_url_thumb.$this->media_filename_thumb, $alt, $imageArgs);
		} else {
			return JHTML::image($this->media_url.$this->media_filename, $alt, $imageArgs);
		}

	}

	/**
	 * a small function that ensures that we always build the thumbnail name with the same method
	 */
	private function createThumbName($width=0,$height=0){

		if(empty($this->media_filename)) return false;
		if(empty($width)) $width = VmConfig::get('img_width', 90);
		if(empty($height)) $height = VmConfig::get('img_height', 90);

		$origFileInfo = pathinfo($this->media_filename);
		$this->media_filename_thumb = $origFileInfo['filename'].'_'.$width.'x'.$height.'.'.$origFileInfo['extension'];
		return $this->media_filename_thumb;
	}

	/**
	 * This function actually creates the thumb
	 * and when it is instanciated with one of the getImage function automatically updates the db
	 *
	 * @author Max Milbers
	 * @param boolean $save Execute update function
	 * @return name of the thumbnail
	 */
	public function createThumb($update=0) {

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

		$fullSizeFilenamePath = $this->media_path.$this->media_filename;

		if(empty($this->media_filename_thumb)) $this->media_filename_thumb = $this->createThumbName();
		$resizedFilenamePath = $this->media_path_thumb.$this->media_filename_thumb;

		if (file_exists($fullSizeFilenamePath)) {

			$createdImage = new Img2Thumb($fullSizeFilenamePath, $width, $height, $resizedFilenamePath, $maxsize, $bgred, $bggreen, $bgblue);
			if($createdImage){
				$this->media_filename_thumb = basename($createdImage->fileout);

				if($update){
					//We just created a new thumbnail, we should save that
					if(empty($this->id)){
						JError::raiseWarning(1,'We just created a thumbnail and not able to store it '.$this->media_filename_thumb);
					} else {
						$query  = 'UPDATE `#__vm_'.$this->table.'` ';
						$query .= 'SET `'.$this->mfield.'` = "'.$this->media_filename_thumb.'" WHERE `'.$this->idfield.'` = "'.$this->id.'" ';
						$db = JFactory::getDBO();
						$db->setQuery($query);
						if(!$db->query()){
							JError::raiseWarning(1,'Couldnt update thumb for $query '.$query);
						}
					}
				}
				return $this->media_filename_thumb;
			} else {
				return 0;
			}
		} else {
			return 0;
		}

	}


	/**
	 * Tests for a given URL, if the path is writeable
	 *
	 */
	public function testFolderWriteAble($media_path_url=0){

		if(!empty($media_path_url)){
			$media_path = str_replace('/',DS,$media_path_url);
			$media_path = JPATH_ROOT.DS.substr($media_path,0,-1);
			$media_path_thumb = $media_path.'/resized/';
		} else {
			$media_path = $this->media_path;
			$media_path_thumb = $this->media_path_thumb;
		}

		$folder = array($media_path, $media_path_thumb);
		$style = 'text-align:left;margin-left:20px;';
		$result = '<div class="vmquote" style="'.$style.'">';
		foreach( $folder as $dir ) {
			$result .= $dir . ' :: ';
			$result .= is_writable( $dir )
				 ? '<span style="font-weight:bold;color:green;">'.JText::_('VM_WRITABLE').'</span>'
				 : '<span style="font-weight:bold;color:red;">'.JText::_('VM_UNWRITABLE').'</span>';
			$result .= '<br/>';
		}
		$result .= '</div>';
		return $result;
	}

	/**
	 * Creates the typicall picture uploader we use everywhere
	 * @author Max Milbers
	 */
	public function createImageUploader($thumb){

		if($thumb){
			$thumbchar = '_thumb';
			$name = $this->media_filename_thumb;
			if(empty($name)) $name = $this->createThumbName();
		} else {
			$thumbchar ='_full';
			$name = $this->media_filename;
		}
		$field_full_image = $this->table.$thumbchar.'_image';
		$field_full_image_url = $this->table.$thumbchar.'_image_url';
		$field_full_image_current = $this->table.$thumbchar.'_image_current';

		$html = '<tr> <td class="key"><label for="title">'.JText::_( 'FILE' ).'</label></td>
				<td>
					<input type="file" name="'.$field_full_image.'" id="'.$field_full_image.'" size="30" class="inputbox" />
					<input type="hidden" name="'.$field_full_image_current.'" id="'.$field_full_image_current.'" value="'.$name.'" />
				</td></tr>';
		 if( function_exists('imagecreatefromjpeg') ){

		 	$html .= '<tr><td class="key">
							<label for="image_action'.$thumbchar.'">'.JText::_( 'VM_IMAGE_ACTION' ).'</label>
							</td>
						<td>';

			$imageActions[] = JHTML::_('select.option',  '0', JText::_( 'NONE' ) );

			if(!$thumb){
				$imageActions[] = JHTML::_('select.option',  '1', JText::_( 'VM_FILES_FORM_AUTO_THUMBNAIL' ) );
			}
			if(!empty($name)){
				$imageActions[] = JHTML::_('select.option',  '2', JText::_( 'VM_FORM_IMAGE_DELETE_LBL' ) );
			}

			$html .= '<fieldset id="image_action'.$thumbchar. '" class="radio">';
			$html .= JHTML::_('select.radiolist', $imageActions, 'image_action'.$thumbchar, '', 'value', 'text', 0);
			$html .= '</fieldset></td>	</tr>';
		}

		$fullImageURL = '';

		if( stripos($name, 'http://') ){
				$fullImageURL = $name;
		}

		$html .= '<tr> <td class="key">
					<label for="image_url">'. JText::_( 'URL' ) .' <em>('.  JText::_( 'CMN_OPTIONAL' ) .')</em></label>
				</td> <td>';
		$html .= '<input type="text" name="'.$field_full_image_url.'" id="'.$field_full_image_url.'" size="45" value="'.$fullImageURL.'" class="inputbox" />
				</td></tr>';

// 		$html .= '<tr><td colspan="2">';
// 		$html .= $this->displayImage('','',$thumb,0);
// 		$html .'</td></tr>';

		return $html;

	}

	/**
	 *  an abstract handler for the pictures
	 *
	 * @author Max Milbers
	 */
	function saveImage($data,$fullImage,$thumb){

		if($thumb){
			 $thumbchar = '_thumb';
			$imagevRootFolder = $this->media_path_thumb;
		} else {
			$thumbchar ='_full';
			$imagevRootFolder = $this->media_path;
		}

		$imageRootFolderExp = explode('/', $imagevRootFolder);
		$imageBaseFolder = implode(DS, $imageRootFolderExp);

		$field_image = $this->table.$thumbchar.'_image';
		$field_image_url = $this->table.$thumbchar.'_image_url';
		$field_image_current = $this->table.$thumbchar.'_image_current';
		$field_thumb_image	= $this->table.'_thumb_image';

		if($fullImage['error'] == UPLOAD_ERR_OK) {
			move_uploaded_file( $fullImage['tmp_name'], $imageBaseFolder.$fullImage['name']);
			$data[$field_image] = $fullImage['name'];
		}
		elseif($data[$field_image_url]){
			$data[$field_image] = $data[$field_image_url];
		}


		if( $data['image_action'.$thumbchar] == 0 ){
			if(empty($data[$field_image])) $data[$field_image] = $data[$field_image_current];
		}
		//creating the thumbnail image
		elseif( $data['image_action'.$thumbchar] == 1 ){
			$data[$field_thumb_image] = $this->createThumb(false);
			if($field_image!=$field_thumb_image){
				$data[$field_image] = $data[$field_image_current];
			}
		}
		//deleting image
		elseif( $data['image_action'.$thumbchar] == 2 ){
			jimport('joomla.filesystem.file');
			JFile::delete( $imageBaseFolder.$data[$field_image_current] );
			$data[$field_image] = '';
		}

		return $data;
	}


	/**
	 * Display an image icon for the given image and create a link to the given link.
	 *
	 * @param string $link Link to use in the href tag
	 * @param string $image Name of the image file to display
	 * @param string $text Text to use for the image alt text and to display under the image.
	 */
	public function displayImageButton($link, $image, $text) {
		$button = '<a title="' . $text . '" href="' . $link . '">';
		$button .= JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_48/'.$image, $text, NULL);
		$button .= '<br />' . $text.'</a>';
		echo $button;

	}

}

/**
*
* @version $Id: image.php 2673 2011-01-02 19:41:35Z zbyszek $
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
	private function NewImgCreate($filename,$newxsize,$newysize,$fileout)
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
	private function NewImgResize($orig_img,$newxsize,$newysize,$filename)
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
	private function NewImgSave($new_img,$fileout,$type)
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
	private function NewImgShow($new_img,$type)
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
	private function GetImgType($filename)
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
