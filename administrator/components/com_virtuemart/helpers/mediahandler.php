<?php
/**
 * Media file handler class
 *
 * This class provides some file handling functions that are used throughout the VirtueMart shop.
 *  Uploading, moving, deleting
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved by the author.
 */

defined('_JEXEC') or die();

class VmMediaHandler {

	var $media_attributes = 0;
	var $setRole = false;

	function __construct($id=0){

		$this->virtuemart_media_id = $id;

		$this->theme_url = VmConfig::get('vm_themeurl',0);
		if(empty($this->theme_url)){
			$this->theme_url = JURI::root().'components/com_virtuemart/';
		}
	}

	/**
	 * The type of the media determines the used path for storing them
	 *
	 * @author Max Milbers
	 * @param string $type type of the media, allowed values product, category, shop, vendor, manufacturer, forSale
	 */
	public function getMediaUrlByView($type){

		//the problem is here, that we use for autocreatoin the name of the model, here products
		//But for storing we use the product to build automatically the table out of it (product_medias)
		$choosed = false;
		if($type == 'product' || $type == 'products'){
			$relUrl = VmConfig::get('media_product_path');
			$choosed = true;
		}
		else if($type == 'category' || $type == 'categories'){
			$relUrl = VmConfig::get('media_category_path');
			$choosed = true;
		}
		else if($type == 'shop'){
			$relUrl = VmConfig::get('media_path');
			$choosed = true;
		}
		else if($type == 'vendor' || $type == 'vendors'){
			$relUrl = VmConfig::get('media_vendor_path');
		//	$relUrl = 'components/com_virtuemart/assets/images/vendors/';
			$choosed = true;
		}
		else if($type == 'manufacturer' || $type == 'manufacturers'){
			$relUrl = VmConfig::get('media_manufacturer_path');
			$choosed = true;
		}
		else if($type == 'forSale' || $type== 'file_is_forSale'){
			//todo add this path to config
			$relUrl = VmConfig::get('forSale_path');
			$choosed = true;
		}

// 		$this->type = $type;
// 		$this->setRole=false;
		if($choosed && empty($relUrl)){
			$uri = JFactory::getURI();
			$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
			vmInfo('COM_VIRTUEMART_MEDIA_NO_PATH_TYPE',$type,$link );
			//Todo add general media_path to config
			//$relUrl = VmConfig::get('media_path');
			$relUrl = 'images/stories/virtuemart/';
			$this->setRole=true;
		} else if(!$choosed && empty($relUrl)){
			vmError('Ignore this message, when it appears while the media synchronisation process, else report to http://forum.virtuemart.net/index.php?board=127.0 : cant create media of unknown type, a programmers error, used type ',$type);

			//$relUrl = VmConfig::get('media_path');
			$relUrl = 'images/stories/virtuemart/';
			$this->setRole=true;

		}

		return $relUrl;
	}

	/**
	 * This function determines the type of a media and creates it.
	 * When you want to write a child class of the mediahandler, you need to manipulate this function.
	 * We may use later here a hook for plugins or simular
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param string  $type vendor,product,category,...
	 * @param string $file_mimetype such as image/jpeg
	 */
	public function createMedia($table,$type='',$file_mimetype=''){

		//		if(!empty($file_mimetypee)){
		//			$isImage = self::isImage($file_mimetypee);
		//		}
		//		else if(!empty($table)){
		if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');
		//			$extension = $this->file_extension = strtolower(JFile::getExt($table->file_url));
		$extension = strtolower(JFile::getExt($table->file_url));
		$isImage = self::isImage($extension);
		//		} else {
		//			$isImage = true;
		//			$app = JFactory::getApplication();
		//			$app->enqueueMessage('create media of unknown mimetype, a programmers error');
		//		}

		if($isImage){
			if (!class_exists('VmImage')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'image.php');
			$media = new VmImage();
		} else {
			$media = new VmMediaHandler();
		}

		$attribsImage = get_object_vars($table);

		foreach($attribsImage as $k=>$v){
			$media->$k = $v;
		}

		if(empty($type)){
			$type = $media->file_type;
		} else {
			$media->file_type = $type;
		}
		$media->setFileInfo($type);

		return $media;
	}

	/**
	 * This prepares the object for storing the data. This means it does the action
	 * and returns the data for storing in the table
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param array $data
	 * @param string $type
	 */
	public function prepareStoreMedia($table,$data,$type){

		$media = VmMediaHandler::createMedia($table,$type);

		$data = $media->processAttributes($data);
		$data = $media->processAction($data);

		$attribsImage = get_object_vars($media);
		foreach($attribsImage as $k=>$v){
			$data[$k] = $v;
		}

		return $data;
	}

	/**
	 * Sets the file information and paths/urls and so on.
	 *
	 * @author Max Milbers
	 * @param unknown_type $filename
	 * @param unknown_type $url
	 * @param unknown_type $path
	 */
	function setFileInfo($type=0){

		$this->file_url_folder = $this->getMediaUrlByView($type);
		$this->file_path_folder = str_replace('/',DS,$this->file_url_folder);
		$this->file_url_folder_thumb = $this->file_url_folder.'resized/';

		//Clean from possible injection
		while(strpos($this->file_path_folder,'..')!==false){
			$this->file_path_folder  = str_replace('..', '', $this->file_path_folder);
		};
		$this->file_path_folder  = preg_replace('#[/\\\\]+#', DS, $this->file_path_folder);

		if(empty($this->file_url)){
			$this->file_url = $this->file_url_folder;
			$this->file_name = '';
			$this->file_extension = '';
		} else {
			if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');

			// 				$lastIndexOfSlash= strrpos($this->file_url,'/');
			// 				$name = substr($this->file_url,$lastIndexOfSlash+1);
			$name = str_replace($this->file_url_folder,'',$this->file_url);

			if(!empty($name) && $name !=='/'){
				$this->file_name = JFile::stripExt($name);

				//Ensure using right directory
				$file_url = $this->getMediaUrlByView($type).$this->file_name;
				if(JFile::exists($file_url)){
					$this->file_url = $file_url;
				}

				$this->file_extension = strtolower(JFile::getExt($name));
			}


		}

		if($this->file_is_product_image) $this->media_attributes = 'file_is_product_image';
		if($this->file_is_downloadable) $this->media_attributes = 'file_is_downloadable';
		if($this->file_is_forSale) $this->media_attributes = 'file_is_forSale';

		$this->determineFoldersToTest();

		if(!empty($this->file_url) && empty($this->file_url_thumb)){
			$this->displayMediaThumb('',true,'',false);
		}
// 		$this->file_name_thumb = $this->createThumbName();
// 		$this->file_url_thumb = $this->file_url_folder_thumb.$this->file_name_thumb.'.'.$this->file_extension;


	}

	public function getUrl(){
		return $this->file_url_folder.$this->file_name.$this->file_extension;
	}

	public function getThumbUrl(){
		return $this->file_url_folder_thumb.$this->file_name.'.'.$this->file_extension;
	}

	public function getFullPath(){

		$rel_path = str_replace('/',DS,$this->file_url_folder);
		return JPATH_ROOT.DS.$rel_path.$this->file_name.'.'.$this->file_extension;
	}

	public function getThumbPath(){

		$rel_path = str_replace('/',DS,$this->file_url_folder);
		return JPATH_ROOT.DS.$rel_path.$this->file_name_thumb.'.'.$this->file_extension;
	}

	/**
	 * Tests if a function is an image by mime or extension
	 *
	 * @author Max Milbers
	 * @param string $file_mimetype
	 * @param string $file_extension
	 */
	private function isImage($file_extension=0){

		//		if(!empty($file_mimetype)){
		//			if(strpos($file_mimetype,'image')===FALSE){
		//				$isImage = FALSE;
			//			}else{
			//				$isImage = TRUE;
			//			}
			//		} else {
			if($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif'){
				$isImage = TRUE;

			} else {
				$isImage = FALSE;
			}
			//		}

			return $isImage;
		}

		private $_foldersToTest = array();

		/**
		 * This functions adds the folders to test for each media, you can add more folders to test with
		 * addFoldersToTest
		 * @author Max Milbers
		 */
		public function determineFoldersToTest(){

			$file_path = str_replace('/',DS,$this->file_url_folder);
			$this->addFoldersToTest(JPATH_ROOT.DS.$file_path);

			$file_path_thumb = str_replace('/',DS,$this->file_url_folder_thumb);
			$this->addFoldersToTest(JPATH_ROOT.DS.$file_path_thumb);

		}


		/**
		 * Add complete paths here to test/display if their are writable
		 *
		 * @author Max Milbers
		 * @param absolutepPath $folders
		 */
		public function addFoldersToTest($folders){
			if(!is_array($folders)) $folders = (array) $folders;
			$this->_foldersToTest = array_merge($this->_foldersToTest, $folders);
		}

		/**
		 * Displays for paths if they are writeable
		 * You set the folders to test with the function addFoldersToTest
		 * @author Max Milbers
		 */
		public function displayFoldersWriteAble(){

			$style = 'text-align:left;margin-left:20px;';
			$result = '<div class="vmquote" style="'.$style.'">';
			foreach( $this->_foldersToTest as $dir ) {
				$result .= $dir . ' :: ';
				$result .= is_writable( $dir )
				? '<span style="font-weight:bold;color:green;">'.JText::_('COM_VIRTUEMART_WRITABLE').'</span>'
				: '<span style="font-weight:bold;color:red;">'.JText::_('COM_VIRTUEMART_UNWRITABLE').'</span>';
				$result .= '<br/>';
			}
			$result .= '</div>';
			return $result;
		}

		/**
		 * Shows the supported file types for the server
		 *
		 * @author enyo 06-Nov-2003 03:32 http://www.php.net/manual/en/function.imagetypes.php
		 * @author Max Milbers
		 * @return multitype:string
		 */
		function displaySupportedImageTypes() {
			$aSupportedTypes = array();

			$aPossibleImageTypeBits = array(
			IMG_GIF=>'GIF',
			IMG_JPG=>'JPG',
			IMG_PNG=>'PNG',
			IMG_WBMP=>'WBMP'
			);

			foreach ($aPossibleImageTypeBits as $iImageTypeBits => $sImageTypeString) {

				if(function_exists('imagetypes')){
					if (imagetypes() & $iImageTypeBits) {
						$aSupportedTypes[] = $sImageTypeString;
					}
				}

			}

			$supportedTypes = '';
			if(function_exists('mime_content_type')){
				$supportedTypes .= JText::_('COM_VIRTUEMART_FILES_FORM_MIME_CONTENT_TYPE_SUPPORTED').'<br />';
			} else {
				$supportedTypes .= JText::_('COM_VIRTUEMART_FILES_FORM_MIME_CONTENT_TYPE_NOT_SUPPORTED').'<br />';
			}

			$supportedTypes .= JText::_('COM_VIRTUEMART_FILES_FORM_IMAGETYPES_SUPPORTED'). implode($aSupportedTypes,', ');

			return $supportedTypes;
		}

		/**
		 * Just for overwriting purpose for childs. Take a look on VmImage to see an exampel
		 *
		 * @author Max Milbers
		 */
		function displayMediaFull(){

			return '';
		}

		/**
		 * This function displays the image, when the image is not already a resized one,
		 * it tries to get first the resized one, or create a resized one or fallback in case
		 *
		 * @author Max Milbers
		 *
		 * @param string $imageArgs Attributes to be included in the <img> tag.
		 * @param boolean $lightbox alternative display method
		 * @param string $effect alternative lightbox display
		 * @param boolean $withDesc display the image media description
		 */
		function displayMediaThumb($imageArgs='',$lightbox=true,$effect="class='modal' rel='group'",$return = true,$withDescr = false){

			if(empty($this->file_name)){
				$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_set');
				$file_alt = JText::_('COM_VIRTUEMART_NO_IMAGE_SET').' '.$this->file_description;
				if($return) return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox);
			}

			if(!empty($this->file_url_thumb)){
				$file_url = $this->file_url_thumb;
			}

			$media_path = JPATH_ROOT.DS.str_replace('/',DS,$this->file_url_thumb);
			$file_alt = $this->file_description ? $this->file_description : $this->file_name;


			if ((empty($this->file_url_thumb) || !file_exists($media_path)) && is_a($this,'VmImage')) {

				$this->file_url_thumb = $this->createThumb();
// 				vmdebug('displayMediaThumb',$this->file_url_thumb);
				$media_path = JPATH_ROOT.DS.str_replace('/',DS,$this->file_url_thumb);
				$file_url = $this->file_url_thumb;

				//Here we need now to update the database field of $this->file_url_thumb to prevent dynamic thumbnailing in future
				if(empty($this->_db)) $this->_db = JFactory::getDBO();
				$query = 'UPDATE `#__virtuemart_medias` SET `file_url_thumb` = "'.$this->_db->getEscaped($this->file_url_thumb).'" WHERE `#__virtuemart_medias`.`virtuemart_media_id` = "'.(int)$this->virtuemart_media_id.'" ';
				$this->_db->setQuery($query);
				$this->_db->query();
			}

			if (empty($this->file_url_thumb) || !file_exists($media_path)) {
				return $this->getIcon($imageArgs,$lightbox,$return);
			}


			if($withDescr) $withDescr = $this->file_description;
			if($return) return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox,$effect,$withDescr);

		}

		/**
		 * This function should return later also an icon, if there isnt any automatic thumbnail creation possible
		 * like pdf, zip, ...
		 *
		 * @author Max Milbers
		 * @param string $imageArgs
		 * @param boolean $lightbox
		 */
		function getIcon($imageArgs,$lightbox,$return=false){
			//we can later add here icons for different types
			$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
			$file_alt = JText::_('COM_VIRTUEMART_NO_IMAGE_FOUND').' '.$this->file_description;
			if($return)return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox);
		}

		/**
		 * This function is just for options how to display an image...
		 * we may add here plugins for displaying images
		 *
		 * @author Max Milbers
		 * @param string $file_url relative Url
		 * @param string $file_alt media description
		 * @param string $imageArgs attributes for displaying the images
		 * @param boolean $lightbox use lightbox
		 */
		function displayIt($file_url, $file_alt, $imageArgs,$lightbox, $effect ="class='modal'",$withDesc=false){

			if ($withDesc) $desc='<span class="vm-img-desc">'.$withDesc.'</span>';
			else $desc='';
			if($lightbox){
				$image = JHTML::image($file_url, $file_alt, $imageArgs);
				if ($file_alt ) $file_alt = 'title="'.$file_alt.'"';
				if ($this->file_url) $href = JURI::root() .$this->file_url ;
				else $href = $image ;
				$lightboxImage = '<a '.$file_alt.' '.$effect.' href="'.$href.'">'.$image.'</a>';
				return $lightboxImage.$desc;
			} else {
				return JHTML::image($file_url, $file_alt, $imageArgs).$desc;
			}
		}

		/**
		 * Handles the upload process of a media, sets the mime_type, when success
		 *
		 * @author Max Milbers
		 * @param string $urlfolder relative url of the folder where to store the media
		 * @return name of the uploaded file
		 */
		function uploadFile($urlfolder,$overwrite = false){

			$media = JRequest::getVar('upload', array(), 'files');

			$app = JFactory::getApplication();
			switch ($media['error']) {
				case 0:
					$path_folder = str_replace('/',DS,$urlfolder);

					//Sanitize name of media
					jimport('joomla.filesystem.file');
					$media['name'] = JFile::makeSafe( $media['name'] );

					$mediaPure = JFile::stripExt($media['name']);
					$mediaExtension = '.'.strtolower(JFile::getExt($media['name']));

					if(!$overwrite){
						while (file_exists(JPATH_ROOT.DS.$path_folder.$mediaPure.$mediaExtension)) {
							$mediaPure = $mediaPure.rand(1,9);
						}
					}

					$media['name'] = $this->file_name =$mediaPure.$mediaExtension;
					JFile::upload($media['tmp_name'],JPATH_ROOT.DS.$path_folder.$media['name']);

					$this->file_mimetype = $media['type'];
					$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_FILE_UPLOAD_OK',JPATH_ROOT.DS.$path_folder.$media['name']));
					return $media['name'];

				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_UPLOAD_MAX_FILESIZE',$media['name'],$media['tmp_name']), 'warning');
					break;
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_MAX_FILE_SIZE',$media['name'],$media['tmp_name']), 'warning');
					break;
				case 3: //uploaded file was only partially uploaded
					$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_PARTIALLY',$media['name'],$media['tmp_name']), 'warning');
					break;
				case 4: //no file was uploaded
					//$vmLogger->warning( "You have not selected a file/image for upload." );
					break;
				default: //a default error, just in case!  :)
					//$vmLogger->warning( "There was a problem with your upload." );
					break;
			}
			return false;
		}

		/**
		 * Deletes a file
		 *
		 * @param string $url relative Url, gets adjusted to path
		 */
		function deleteFile($url){

			jimport('joomla.filesystem.file');
			$file_path = str_replace('/',DS,$url);
			$app = JFactory::getApplication();
			if($res = JFile::delete( JPATH_ROOT.DS.$file_path )){
				$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_FILE_DELETE_OK',$file_path));
			} else {
				$app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_FILE_DELETE_ERR',$res));
			}
			return ;
		}

		/**
		 * Processes the choosed Action while storing the data, gets extend by the used child, use for the action clear commands.
		 * Useable commands in all medias upload, upload_delete, delete, and all of them with _thumb on it also.
		 *
		 * @author Max Milbers
		 * @param arraybyform $data
		 */
		function processAction($data){

			if(empty($data['media_action'])) return $data;
			$data['published'] = 1;
			if( $data['media_action'] == 'upload' ){

				$this->virtuemart_media_id=0;
				$this->file_url='';
				$this->file_url_thumb='';
				$file_name = $this->uploadFile($this->file_url_folder);
				$this->file_name = $file_name;
				$this->file_url = $this->file_url_folder.$this->file_name;
			}
			else if( $data['media_action'] == 'replace' ){
// 				$oldFileUrl = $data['file_url'];
// 				vmdebug('replace media',$this);
				$oldFileUrl = $this->file_url;
				$file_name = $this->uploadFile($this->file_url_folder,true);
				$this->file_name = $file_name;
				$this->file_url = $this->file_url_folder.$this->file_name;
				if($this->file_url!=$oldFileUrl && !empty($this->file_name)){
					$this->deleteFile($oldFileUrl);
				}
			}
			else if( $data['media_action'] == 'replace_thumb' ){
// 				$oldFileUrl = $data['file_url_thumb'];

				$file_name = $this->uploadFile($this->file_url_folder_thumb);
				$this->file_url_thumb = $this->file_url_folder_thumb.$file_name;
				if($this->file_url_thumb!=$oldFileUrl&& !empty($file_name)){
					$this->deleteFile($oldFileUrl);
				}

			}
			else if( $data['media_action'] == 'delete' ){
				//TODO this is complex, we must assure that the media entry gets also deleted.
				//$this->deleteFile($this->file_url);
				unset($data['active_media_id']);

			}
			//		else{
			//
			//		}

			if(empty($this->file_title) && !empty($file_name)) $this->file_title = $file_name;
			//		if(empty($this->file_title) && !empty($file_name)) $data['file_title'] = $file_name;

			return $data;
		}


		/**
		 * For processing the Attributes of the media while the storing process
		 *
		 * @author Max Milbers
		 * @param unknown_type $data
		 */
		function processAttributes($data){

			if(empty($data['media_attributes'])) return $data;
			if($data['media_attributes'] == 'file_is_product_image'){

				$this->file_is_product_image = 1;
				$this->file_is_downloadable = 0;
				$this->file_is_forSale = 0;
			}
			else if($data['media_attributes'] == 'file_is_downloadable'){
				$this->file_is_downloadable = 1;
				$this->file_is_forSale = 0;
			}
			else if($data['media_attributes'] == 'file_is_forSale'){
				$this->file_is_product_image = 0;
				$this->file_is_downloadable = 0;
				$this->file_is_forSale = 1;
			}

			if($this->setRole){
				$this->file_url_folder = $this->getMediaUrlByView($data['media_attributes']);
				$this->file_url_folder_thumb = $this->file_url_folder.'resized/';
			}

			return $data;
		}

		private $_actions = array();
		/**
		 * This method can be used to add extra actions to the media
		 *
		 * @author Max Milbers
		 * @param string $optionName this is the value in the form
		 * @param string $langkey the langkey used
		 */
		function addMediaAction($optionName,$langkey){
			$this->_actions[$optionName] = $langkey ;
		}

		/**
		 * Adds the media action which are needed in the form for all media,
		 * you can use this function in your child calling parent. Look in VmImage for an exampel
		 * @author Max Milbers
		 */
		function addMediaActionByType(){

			$this->addMediaAction(0,'COM_VIRTUEMART_NONE');

			$this->addMediaAction('upload','COM_VIRTUEMART_FORM_MEDIA_UPLOAD');
			if(empty($this->file_name)){

			} else {
				//			$this->addMediaAction('upload_delete','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_DELETE');
				$this->addMediaAction('replace','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_REPLACE');
				//			$this->addMediaAction('delete','COM_VIRTUEMART_FORM_MEDIA_DELETE');
			}

			$this->addMediaAction('replace_thumb','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_REPLACE_THUMB');

			//		$this->addMediaAction('replace_thumb','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_DELETE_THUMB');

			//		if(empty($this->file_url_thumb)){
			//			$this->addMediaAction('upload_thumb','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_THUMB');
			//		} else {
			//			$this->addMediaAction('upload_delete_thumb','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_DELETE_THUMB');
			//			$this->addMediaAction('delete_thumb','COM_VIRTUEMART_FORM_MEDIA_DELETE_THUMB');
			//		}

		}


		private $_attributes = array();

		/**
		 * This method can be used to add extra attributes to the media
		 *
		 * @author Max Milbers
		 * @param string $optionName this is the value in the form
		 * @param string $langkey the langkey used
		 */
		public function addMediaAttributes($optionName,$langkey=''){
			$this->_attributes[$optionName] = $langkey ;
		}

		/**
		 * Adds the attributes which are needed in the form for all media,
		 * you can use this function in your child calling parent. Look in VmImage for an exampel
		 * @author Max Milbers
		 */
		public function addMediaAttributesByType(){


			if($this->setRole){
// 				$this->addMediaAttributes('file_is_product_image','COM_VIRTUEMART_FORM_MEDIA_SET_PRODUCT');
				$this->addMediaAttributes('product','COM_VIRTUEMART_FORM_MEDIA_SET_PRODUCT');
				$this->addMediaAttributes('category','COM_VIRTUEMART_FORM_MEDIA_SET_CATEGORY');
				$this->addMediaAttributes('manufacturer','COM_VIRTUEMART_FORM_MEDIA_SET_MANUFACTURER');
				$this->addMediaAttributes('vendor','COM_VIRTUEMART_FORM_MEDIA_SET_VENDOR');
				$this->addMediaAttributes('file_is_forSale','COM_VIRTUEMART_FORM_MEDIA_SET_FOR_SALE');
			} else {
				$this->addMediaAttributes(0,'COM_VIRTUEMART_FORM_MEDIA_NO_ATTRIB');
				//Every media can be free for download. This attribute indicate if there should be a link to be created
				$this->addMediaAttributes('file_is_downloadable','COM_VIRTUEMART_FORM_MEDIA_DOWNLOADABLE');

			}

		}


		private $_hidden = array();

		/**
		 * Use this to adjust the hidden fields of the displayFileHandler to your form
		 *
		 * @author Max Milbers
		 * @param string $name for exampel view
		 * @param string $value for exampel media
		 */
		public function addHidden($name, $value=''){
			$this->_hidden[$name] = $value;
		}

		/**
		 * Adds the hidden fields which are needed for the form in every case
		 * @author Max Milbers
		 */
		private function addHiddenByType(){

			$this->addHidden('active_media_id',$this->virtuemart_media_id);
			$this->addHidden('option','com_virtuemart');
			//		$this->addHidden('file_mimetype',$this->file_mimetype);

		}

		/**
		 * Displays file handler and file selector
		 *
		 * @author Max Milbers
		 * @param array $fileIds
		 */
		public function displayFilesHandler($fileIds,$type){
			$this->lists= $this->displayImages($type);
			$html = $this->displayFileSelection($fileIds,$type);
			$html .= $this->displayFileHandler('id="vm_display_image" ');
			$html .= '<div style="display:none"><div id="media-dialog" >'.$this->lists['htmlImages'].'</div></div>';//$type);
			$this->_db->setQuery('SELECT FOUND_ROWS()');
			$imagetotal = $this->_db->loadResult();
			//vmJsApi::jQuery(array('easing-1.3.pack','mousewheel-3.0.4.pack','fancybox-1.3.4.pack'),'','fancybox');
			$isJ15 = VmConfig::isJ15();
			if ($isJ15) {
				$j = "
			jQuery(document).ready(function(){ jQuery('#ImagesContainer').vm2admin('media','".$type."','".$this->lists['total']."') }); " ;
			}
			else $j = "
			jQuery(document).ready(function(){ jQuery('#ImagesContainer').vm2admin('media','".$type."','".$this->lists['total']."') }); " ;
			$document = JFactory::getDocument ();
			$document->addScriptDeclaration ( $j);
			return $html;
		}

		/**
		 * Displays a possibility to select already uploaded media
		 * the getImagesList must be adjusted to have more search functions
		 * @author Max Milbers
		 * @param array $fileIds
		 */
		public function displayFileSelection($fileIds,$type = 0){

			$html='';
                        // this one break the tabs. Don't know why.
                          $html .= '<fieldset class="checkboxes">' ;
                         $html .= '<legend>'.JText::_('COM_VIRTUEMART_IMAGES').'</legend>';

			$result = $this->getImagesList($type);
			$html .= '<div id="ImagesContainer">';

// 			$html .= ShopFunctions::displayDefaultViewSearch('COM_VIRTUEMART_NAME','','searchMedia') ;
			$name = 'searchMedia';
			$html .=  JText::_('COM_VIRTUEMART_FILTER') . ' ' . JText::_('COM_VIRTUEMART_IMAGES') . ':
					<input type="text" name="' . $name . '" id="' . $name . '" value="' .JRequest::getString('searchMedia') . '" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="document.getElementById(\'' . $name . '\').value=\'\';this.form.submit();">' . JText::_('COM_VIRTUEMART_RESET') . '</button>';



			// if(empty($fileIds)) {
			// return  $html;
			// }
			// $text = 'COM_VIRTUEMART_FILES_FORM_ALREADY_ATTACHED_FILE_PRIMARY';
			if(!empty($fileIds)) {
				foreach($fileIds as $k=>$id){
					$html .= $this->displayImage($id,$k );
				}
			}
                        $html .= '<a id="addnewselectimage2" href="#media-dialog">'.JText::_('COM_VIRTUEMART_IMAGE_ATTACH_NEW').'</a>';
                         $html .= '</div></fieldset>' ;


			return $html.'<div class="clear"></div>';
		}

		function displayImage($virtuemart_media_id ,$key) {

			$db = JFactory::getDBO();
			$query='SELECT * FROM `#__virtuemart_medias` where `virtuemart_media_id`='.(int)$virtuemart_media_id;
			$db->setQuery( $query );
			$image = $db->loadObject();
			if (isset($image->file_url)) {
				$image->file_root = JURI::root(true).'/';
				$image->msg =  'OK';
				return  '<div  class="vm_thumb_image"><input type="hidden" value="'.$image->virtuemart_media_id.'" name="virtuemart_media_id[]">
				<input class="ordering" type="hidden" name="mediaordering['.$image->virtuemart_media_id.']" value="'.$key.'">
			<a class="vm_thumb" rel="group1" title ="'.$image->file_title.'"href="'.JURI::root(true).'/'.$image->file_url.'" >
			'.JHTML::image($image->file_url_thumb, $image->file_title, '').'
			</a><div class="trash" title="remove image"></div><div class="edit-24-grey" title="edit image information"></div></div>';
			} else {
				$fileTitle = empty($image->file_title)? 'no  title':$image->file_title;
				return  '<div  class="vm_thumb_image"><b>'.JText::_('COM_VIRTUEMART_NO_IMAGE_SET').'</b><br />'.$fileTitle.'</div>';
			}

		}
		function displayImages($types ='',$page=0 ) {

			$htmlImages ='';
			$list = VmMediaHandler::getImagesList($types,$page);
			if (empty($list['images'])) return 'ERROR';

			foreach ($list['images'] as $image) {
				if ($image->file_url_thumb > "0" ) {
					// $imagesList->file_root = JURI::root(true).'/';
					// $imagesList->msg =  'OK';
					$htmlImages .= '<div class="vm_thumb_image">
					<a class="vm_thumb" rel="group1" title ="'.$image->file_title.'"href="'.JURI::root(true).'/'.$image->file_url.'" >'
					.JHTML::image($image->file_url_thumb,$image->file_title, 'class="vm_thumb" ').'</a>';
				} else {
					$htmlImages .=  '<div class="vm_thumb_image">'.JText::_('COM_VIRTUEMART_NO_IMAGE_SET').'<br />'.$image->file_title ;
				}
				$htmlImages .= '<input type="hidden" value="'.$image->virtuemart_media_id.'" name="virtuemart_media_id['.$image->virtuemart_media_id.']"><input class="ordering" type="hidden" name="mediaordering['.$image->virtuemart_media_id.']" value=""><div class="add-image"></div></div>';
			}
			$list['htmlImages'] = $htmlImages;
			return $list;
		}
		/**
		 * Retrieve a list of layouts from the default and choosen templates directory.
		 *
		 * We may use here the getFiles function of the media model or write something simular
		 * @author Max Milbers
		 * @param name of the view
		 * @return object List of flypage objects
		 */
		function getImagesList($type = '',$page=0,$max=24) {
			$list = array();
			$vendorId=1;//TODO control the vendor
			$q='SELECT SQL_CALC_FOUND_ROWS `virtuemart_media_id` FROM `#__virtuemart_medias` WHERE `published`=1
    	AND (`virtuemart_vendor_id`= "'.(int)$vendorId.'" OR `shared` = "1")';
			if(!empty($type)){
				$q .= ' AND `file_type` = "'.$type.'" ';
			}
			if ($search = JRequest::getString('searchMedia', false)){
				$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;
				$q .=  ' AND (`file_title` LIKE '.$search.' OR `file_description` LIKE '.$search.' OR `file_meta` LIKE '.$search.') ';
			}
			$q .= ' LIMIT '.(int)$page*$max.', '.(int)$max;

			if(empty($this->_db)) $this->_db = JFactory::getDBO();

			$this->_db->setQuery($q);
			//		$result = $this->_db->loadAssocList();
			$virtuemart_media_ids = $this->_db->loadResultArray();
			$errMsg = $this->_db->getErrorMsg();
			$errs = $this->_db->getErrors();

			if(!class_exists('VirtueMartModelMedia'))require(JPATH_VM_ADMINISTRATOR.DS.'model'.DS.'media.php');
			$model = new VirtueMartModelMedia ;
			$this->_db->setQuery('SELECT FOUND_ROWS()');
			$list['total'] = $this->_db->loadResult();

			$list['images'] = $model->createMediaByIds($virtuemart_media_ids, $type);

			if(!empty($errMsg)){
				$app = JFactory::getApplication();
				$errNum = $this->_db->getErrorNum();
				$app->enqueueMessage('SQL-Error: '.$errNum.' '.$errMsg);
			}

			if($errs){
				$app = JFactory::getApplication();
				foreach($errs as $err){
					$app->enqueueMessage($err);
				}
			}

			return $list;
		}
		/**
		 * This displays a media handler. It displays the full and the thumb (icon) of the media.
		 * It also gives a possibility to upload/change/thumbnail media
		 *
		 * @param string $imageArgs html atttributes, Just for displaying the fullsized image
		 */
		public function displayFileHandler($imageArgs=''){

			$identify = ''; // ':'.$this->virtuemart_media_id;

			$this->addHiddenByType();

                        $html = '<fieldset class="checkboxes">' ;
                        $html .= '<legend>'.JText::_('COM_VIRTUEMART_IMAGE_INFORMATION').'</legend>';
			$html .= '<div class="vm__img_autocrop"><div id="file_title">'.$this->file_title.'</div>';
			$html .=  $this->displayMediaFull($imageArgs,false).'</div>';

			//This makes problems, when there is already a form, and there would be form in a form. breaks js in some browsers
			//		$html .= '<form name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">';

			$html .= ' <table class="adminform"> ';

			if ($this->published || $this->virtuemart_media_id === 0){

				//if($this->_id==0){
				//	$media->media_published = 1;
				//}
				$checked =  "checked=\"checked\"";
			} else {
				$checked ='';
			}

			$html .= '<tr>
	<td class="labelcell">
		<label for="published">'. JText::_('COM_VIRTUEMART_FILES_FORM_FILE_PUBLISHED') .'</label>
	</td>
	<td>
		<input type="checkbox" class="inputbox" id="published" name="media_published'.$identify.'" '.$checked.' size="16" value="1" />
	</td>';
			$html .= '<td rowspan = 5>';
					$html .= JHTML::image($this->file_url_thumb, 'thumbnail', 'id="vm_thumb_image" style="overflow: auto; float: right;"');
			// $html .= $this->displayMediaThumb('',false,'id="vm_thumb_image" style="overflow: auto; float: right;"');
			$html .= '</td>';

			$html .= '</tr>';

			$html .= '<tr>
	<td class="labelcell">'. JText::_('COM_VIRTUEMART_FILES_FORM_CURRENT_FILE') .'</td>
	<td>'.$this->file_name.'.'.$this->file_extension .'</td>
</tr>';

			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if(!Permissions::getInstance()->check('admin') ) $readonly='readonly'; else $readonly ='';
			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_TITLE','file_title');
			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_DESCRIPTION','file_description');
			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_META','file_meta');

			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_URL','file_url',$readonly);
			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_URL_THUMB','file_url_thumb',$readonly);

			$this->addMediaAttributesByType();
			$html .= '<tr>
		<td class="labelcell">'.JText::_('COM_VIRTUEMART_FILES_FORM_ROLE').'</td>
		<td><fieldset class="checkboxes">'.JHTML::_('select.radiolist', $this->getOptions($this->_attributes), 'media_attributes'.$identify, '', 'value', 'text', $this->media_attributes).'</fieldset></td></tr>';

			$html .= '</table>';
                        $html .='<br /></fieldset>';
			$this->addMediaActionByType();

			$html .= '<fieldset class="checkboxes">' ;
                        $html .= '<legend>'.JText::_('COM_VIRTUEMART_FILE_UPLOAD').'</legend>';
                        $html .= JText::_('COM_VIRTUEMART_IMAGE_ACTION'). JHTML::_('select.radiolist', $this->getOptions($this->_actions), 'media_action'.$identify, '', 'value', 'text', 0).'<br /><br style="clear:both" />';


			$html .= JText::_('COM_VIRTUEMART_FILE_UPLOAD').' <input type="file" name="upload" id="upload" size="50" class="inputbox" /><br />';

			$html .= '<br />'.$this->displaySupportedImageTypes();
                        $html .='<br /></fieldset>';
			$html .= $this->displayFoldersWriteAble();

			$html .= $this->displayHidden();

			//		$html .= '</form>';

			return $html;
		}

		/**
		 * child classes can add their own options and you can get them with this function
		 *
		 * @param array $optionsarray Allowed values are $this->_actions and $this->_attributes
		 */
		private function getOptions($optionsarray){

			$options=array();
			foreach($optionsarray as $optionName=>$langkey){
				$options[] = JHTML::_('select.option',  $optionName, JText::_( $langkey ) );
			}
			return $options;
		}

		/**
		 * Just for creating simpel rows
		 *
		 * @author Max Milbers
		 * @param string $descr
		 * @param string $name
		 */
		private function displayRow($descr, $name,$readonly=''){
			$html = '<tr>
		<td class="labelcell">'.JText::_($descr).'</td>
		<td> <input type="text" '.$readonly.'class="inputbox" name="'.$name.'" size="70" value="'.$this->$name.'" /></td>
	</tr>';
			return $html;
		}

		/**
		 * renders the hiddenfields added in the layout before (used to make the displayFileHandle reusable)
		 * @author Max Milbers
		 */
		private function displayHidden(){
			$html='';
			foreach($this->_hidden as $k=>$v){
				$html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
			}
			return $html;
		}

	}
