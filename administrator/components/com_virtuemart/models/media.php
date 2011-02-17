<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

/**
 * Model for VirtueMart Product Files
 *
 * @package		VirtueMart
 */
class VirtueMartModelMedia extends JModel {

	/* Private variables */
	private $_total;
	private $_pagination;
	/** @var object Contains all image related information */
	private $_productfile;

	/**
	 * Constructor for product files
	 */
	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		/* Get the file ID */
		$this->_productfile->file_id = JRequest::getInt('file_id', null);
		$this->_productfile->product_id = JRequest::getInt('product_id', null);
	}

	/**
	 * Loads the pagination
	 */
    public function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
    		$db = JFactory::getDBO();
    		$filter = '';
            if (JRequest::getInt('product_id', 0) > 0) $filter .= ' WHERE `#__vm_product_files`.`file_product_id` = '.JRequest::getInt('product_id');
			$q = "SELECT COUNT(*) FROM `#__vm_product_files` ".$filter;
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

     /**
     * Select the products to list on the product list page
     */
    public function getProductFilesList() {
    	$db = JFactory::getDBO();

    	/* Pagination */
     	$this->getPagination();

     	/* Get the files from the product files table */
    	$db->setQuery($this->getImageQuery('product_files'));
    	$productfileslist = $db->loadObjectList();

    	/* Get the files from the product table */
    	$db->setQuery($this->getImageQuery('product'));
    	$productlist = $db->loadObjectList();

    	return array_merge($productfileslist, $productlist);
    }

    /**
	 * Returns the number of files AND images which are assigned to $pid
	 *
	 * @param int $pid
	 * @param string $type Filter the query by file_is_image: [files|images|(empty)]
	 * @return int
	 */
	public function countFilesForProduct($pid, $type = '') {
		$db = JFactory::getDBO();
		switch ($type) {
			case 'files': $type_sql = 'AND file_is_image=0'; break;
			case 'images': $type_sql = 'AND file_is_image=1'; break;
			default: $type_sql = ''; break;
		}
		$q = "SELECT COUNT(file_id) AS files
			FROM #__vm_product_files
			WHERE file_product_id=".intval($pid).' '.$type_sql;
		$db->setQuery($q);
		$files = $db->loadResult();

		$q = "SELECT IF (LENGTH(`product_full_image`) = 0,
					IF (LENGTH(`product_thumb_image`) = 0, '0', '1'),
					IF (LENGTH(`product_thumb_image`) = 0, '1', '2'))
					AS cnt
				FROM `#__vm_product`
				WHERE product_id=".intval($pid);
		$db->setQuery($q);
		$files += $db->loadResult();

		return $files;
	}

	/**
	 * Set the different roles available for a file
	 */
	public function getProductFilesRoles() {
	 	return array(
	 			'isDownloadable' => VmConfig::get('assets_general_path').'images/vmgeneral/downloadable.gif',
				'isImage' => VmConfig::get('assets_general_path').'images/vmgeneral/image.gif',
				'isProductImage' => VmConfig::get('assets_general_path').'images/vmgeneral/image.png',
				'isFile' => VmConfig::get('assets_general_path').'images/vmgeneral/attachment.gif',
				'isRemoteFile' => VmConfig::get('assets_general_path').'images/vmgeneral/url.gif'
		);
	}

	/**
	 * Get the image details to edit  them
	 */
	public function getImageDetails() {
		/* Check if the item is being edited or created new */
		if (JRequest::getCmd('task', 'add') == 'add') {
			$this->_productfile = new StdClass();
			$this->_productfile->file_id = null;
			$this->_productfile->file_is_image = null;
			$this->_productfile->file_extension = null;
			$this->_productfile->file_url = null;
			$this->_productfile->published = null;
			$this->_productfile->file_name = null;
			$this->_productfile->file_title = null;
			$this->_productfile->product_thumb_image = null;
			$this->_productfile->isdownloadable = null;

			/* Get some product details */
			$row = $this->getTable('product');
			$row->load(JRequest::getInt('product_id'));
			$this->_productfile->product_name = $row->product_name;
			$this->_productfile->file_product_id = $row->product_id;
		}
		else {
			$db = JFactory::getDBO();
			/* Get the files from the product table */
			if (JRequest::getCmd('file_role', 'isProductImage') == 'isProductImage') {
				$q = $this->getImageQuery('product');
			}
			else $q = $this->getImageQuery('product_files');
			$db->setQuery($q);
			$this->_productfile = $db->loadObject();
		}
    	return $this->_productfile;
	}

	/**
	 * Query for retrieving images
	 */
	 private function getImageQuery($table = 'product') {
	 	 switch ($table) {
	 	 	case 'product_files':
	 	 		if (JRequest::getInt('product_id', false)) $filter = ' WHERE file_product_id = '.JRequest::getInt('product_id');
	 	 		else $filter = '';
	 	 		if (JRequest::getInt('file_id', false)) $filter .= ' AND file_id = '.JRequest::getInt('file_id');
	 	 		$q = "SELECT file_id,
							file_is_image,
							file_product_id,
							file_extension,
							file_url,
							file_published AS published,
							file_name,
							file_title,
							NULL AS product_thumb_image,
							IF (LOWER(attribute_name) = 'download', 1, 0) AS isdownloadable,
							IF (file_is_image = 1, 'isImage', 'isFile') AS file_role,
							product_name
					FROM #__vm_product_files
					LEFT JOIN #__vm_product_attribute
					ON #__vm_product_files.file_title = #__vm_product_attribute.attribute_value
					LEFT JOIN #__vm_product
					ON #__vm_product_files.file_product_id = #__vm_product.product_id ".$filter;
				$q .= " ORDER BY file_is_image DESC";
				break;
			case 'product':
			default:
				if (JRequest::getInt('product_id', false)) $filter = ' AND product_id = '.JRequest::getInt('product_id');
				else $filter = '';
				//TODO replace IMAGEURL with VmConfig::get('media_product_path')
				$q = "SELECT 'isProductImage' AS file_role,
							NULL AS file_id,
							'1' AS file_is_image,
							product_id AS file_product_id,
							'1' AS published,
							product_full_image AS file_name,
							'' AS file_title,
							product_thumb_image,
							'0' AS isdownloadable,
							product_name,
							CONCAT('". VmConfig::get('media_product_path')."', product_full_image) AS file_url,			
							SUBSTRING(product_full_image, -3, 3) AS file_extension
					FROM #__vm_product
					WHERE LENGTH(product_full_image) > 0 ".$filter;
				break;
		 }
		 return $q;
	 }

	/**
	 * This function finds out what kind of media file it is
	 * product_images -> new media
	 * product_full_image -> full image
	 * product_thumb_image -> thumbnail image
	 * downloadable_file -> downloadable file
	 * image -> additional image
	 * file -> a non-image file
	 */
	public function getSelectedFileType() {
		if (is_null($this->_productfile->file_id)) return 'product_images';
		else {
			$db = JFactory::getDBO();
			$this->_productfile->file_id = JRequest::getInt('file_id', null);
			$this->_productfile->product_id = JRequest::getInt('product_id', null);
			$isProductDownload = $this->isProductDownloadFile($this->_productfile->file_id, $this->_productfile->product_id);
			$q = "SELECT file_name,file_url,file_is_image,file_published,file_title
				  FROM #__vm_product_files
				  WHERE file_id='".$this->_productfile->file_id."'";
			$db->setQuery($q);
			$pfile = $db->loadObject();
			if ($db->getAffectedRows() > 0) {
				if( $isProductDownload ) {
					$db->setQuery('SELECT attribute_id FROM `#__vm_product_attribute` WHERE attribute_name=\'download\' AND attribute_value=\''.$pfile->file_title.'\' AND product_id=\''.$this->_productfile->product_id.'\'');
					$db->query();
					$this->_productfile->attribute_id = $db->loadResult();
					return 'downloadable_file';
				}
				else {
					$index = $pfile->file_is_image == 1 ? 'image' : 'file';
					return $index;
				}
			}
		}

	}

	/**
	 * Checks if a file is a restricted downloadable product file
	 * a user must pay for
	 *
	 * @param int $file_id
	 * @param int $product_id
	 * @return boolean
	 */
	function isProductDownloadFile( $file_id, $product_id ) {
		$db = JFactory::getDBO();
		$q = "SELECT attribute_value, attribute_name,file_id FROM #__vm_product_attribute,#__vm_product_files WHERE ";
		$q .= "product_id=".intval($product_id)." AND attribute_name='download' ";
		$q .= "AND file_id=".intval($file_id)." AND attribute_value=file_title";
		$db->setQuery($q);
		$db->query();
		if($db->getAffectedRows() > 0) return true;
		else return false;
	}

	/**
	 * Get the list of files from the DOWNLOADROOT
	 */
	public function getFilesSelect() {
		$downloadroot = VmConfig::get('downloadroot',JPATH_BASE.DS.'images');  //Todo add config data
		if (JFolder::exists($downloadroot)) {
			$files = JFolder::files($downloadroot);
			if (count($files) > 1) {
				$filesselect = array(JHTML::_('select.option',  '', '- '. JText::_( 'SELECT_FILE' ) .' -' ));
				foreach ( $files as $file) {
					$filesselect[] = JHTML::_('select.option',  $file );
				}
			}
			else {
				$filesselect = array(JHTML::_('select.option',  '', '- '. JText::_('NO_FILES_FOUND') .' -' ));
			}
			return JHTML::_('select.genericlist', $filesselect, 'downloadable_file', 'class="inputbox"');
		}
		else return JText::_('NO_VALID_DOWNLOADROOT_SET');
	}

	/**
	 * Save a media item
	 */
	public function getSaveMedia() {
	 	$db = JFactory::getDBO();
	 	$mainframe = JFactory::getApplication('site');
		$timestamp = time();
		$row = $this->getTable();
		$files = JRequest::get('files');
		$file_type = JRequest::getVar('file_type');
		$this->_productfile->file_url = JRequest::getVar('file_url');
		$this->_productfile->file_title = JRequest::getVar('file_title');

		/* Validate the file */
		if (!$this->validateAdd()) return false;

		/* Set if the file is to be published */
		$row->file_published = JRequest::getInt("file_published", 0);

		// Do we have an uploaded file?
		if (!empty($files['file_upload']['name'])) {
			if(!$this->handleFileUpload()) {
				return false;
			}
		}
		else {
			// No file uploaded, but specified by URL
			$this->_productfile->file_is_image = stristr( $file_type, "image" ) ? '1' : '0';

			if (!empty($this->_productfile->file_url)) {
				$this->_productfile->file_name = '';
			} else {
				$this->_productfile->file_name = DOWNLOADROOT.JRequest::getVar('downloadable_file');
				$this->_productfile->file_title = basename(JRequest::getVar('downloadable_file'));
			}
			$this->_productfile->file_extension = "";
			$this->_productfile->file_image_height = "";
			$this->_productfile->file_image_width = "";
			$this->_productfile->file_image_thumb_height = "";
			$this->_productfile->file_image_thumb_width = "";
		}

		/* Store image data*/
		if( $file_type == 'product_images' ||  $file_type == 'product_full_image' ||  $file_type == 'product_thumb_image') {
			/* Get the table data */
			$product_row = $this->getTable('product');
			$product_row->load(JRequest::getInt("product_id"));

			$filename = str_replace( IMAGEPATH.'product'.DS, '', $this->_productfile->file_name);
			$fullimage = str_replace( IMAGEPATH.'product'.DS, '', $this->_productfile->file_name);

			if ($file_type == 'product_images' || $file_type == 'product_full_image' ) {
				$this->_productfile->product_full_image = $fullimage;
			}
			if ($file_type == 'product_images' ) {
				$this->_productfile->product_thumb_image = str_replace( IMAGEPATH.'product'.DS, '', $this->_productfile->file_name);
			}
			if ($file_type == 'product_thumb_image' ) {
				$this->_productfile->product_thumb_image = $db->getEscaped($filename);
			}
			$product_row->bind($this->_productfile);
			if ($product_row->store()) {
				$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_IMAGES_SET'));
				return true;
			}
			else return false;
		}
		else {
			// erase JPATH_SITE to have a relative path
			$this->_productfile->file_name = str_replace( JPATH_SITE, '', $this->_productfile->file_name );
			if (empty($this->_productfile->file_name) && !empty($this->_productfile->file_url)) {
				$this->_productfile->file_name = $this->_productfile->file_url;
			}
			if ($file_type == 'downloadable_file') {
				if ($this->_productfile->file_name == $this->_productfile->file_url) {
					$attribute_value = $this->_productfile->file_name;
				} else {
					$attribute_value= basename($this->_productfile->file_name);
				}
				$this->_productfile->file_title = $attribute_value;
				// Insert an attribute called "download", attribute_value: filename
				$fields = array( 'product_id' => JRequest::getInt("product_id"),
											'attribute_name' => 'download',
											'attribute_value' => $attribute_value
										);
				$attribute_row = $this->getTable('vm_product_attribute');
				$attribute_row->bind($fields);
				$attribute_row->store();
			}
			else echo $file_type;
			$this->_productfile->file_mimetype = $files['file_upload']['type'];
			$this->_productfile->file_product_id = JRequest::getInt("product_id");
			$row->bind($this->_productfile);
			if( $row->store() !== false ) {
				$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ADDED'));
				JRequest::setVar('file_id', $row->file_id);
			}
			else {
				return false;
			}
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=media&product_id='.JRequest::getInt("product_id"));

	}

	/**
	 * Checks if a file can be added or not
	 *
	 * @return boolean
	 */
	function validateAdd() {
		$mainframe = JFactory::getApplication('site');
		$files = JRequest::get('files');

		/* Check if there is any file specified */
		if (empty($files["file_upload"]["name"]) && is_null(JRequest::getVar('file_url')) && is_null(JRequest::getVar('downloadable_file'))) {
			$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ERR_PROVIDE'), 'error');
			return False;
		}

		/* Check if we have a product ID */
		if (!JRequest::getInt("product_id", false)) {
			$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ERR_ID'), 'error');
			return false;
		}

		/* Handling uploaded file */
		if (!empty($files["file_upload"]["name"])) {
			$db = JFactory::getDBO();
			$q = "SELECT count(*) AS rowcnt FROM #__vm_product_files WHERE";
			$q .= " file_name LIKE '%" . $db->getEscaped($files["file_upload"]["name"]) . "%'";
			$db->setQuery($q);
			$rowcnt = $db->loadResult();
			if ($rowcnt > 0) $this->_productfile->fileexists = true;
			else $this->_productfile->fileexists = false;
		}
		return true;

	}

	/**
	 * This function handles the file upload
	 * and image resizing when necessary
	 *
	 * @return boolean
	 */
	function handleFileUpload() {
		$mainframe = JFactory::getApplication('site');
		$files = JRequest::get('files');
//		require_once(CLASSPATH.'imageTools.class.php' );

		/* Get the filename */
		if ($this->_productfile->fileexists) {
			$mainframe->enqueueMessage(JText::_('VM_UPLOADED_FILE_NAME_EXISTS').' '.basename($files['file_upload']['name']), 'error');
			return false;
		}
		/* Get file details */
		$this->_productfile->file_name = $files['file_upload']['name'];
		$fileinfo = pathinfo($files['file_upload']['name']);
		$this->_productfile->file_extension = $fileinfo["extension"];


		// This plays a role when a file is added from the ps_product class
		// on adding and updating a downloadable product
		if (JRequest::getVar('file_type') == 'downloadable_file' ) {
			$this->_productfile->file_title = $this->_productfile->file_name;
		}

		switch( JRequest::getVar("upload_dir")) {
			case "IMAGEPATH":
				$uploaddir = VmConfig::get('media_product_path');
				break;
			case "FILEPATH":
				$uploaddir = JPATH_SITE.trim(JRequest::getVar("file_path"));
				if( !file_exists($uploaddir) ) {
					@mkdir( $uploaddir );
				}
				if( !file_exists( $uploaddir ) ) {
					$mainframe->enqueueMessage(JText::_('VM_FILES_PATH_ERROR'), 'error');
					return false;
				}

				if( substr( $uploaddir, strlen($uploaddir)-1, 1) != '/') {
					$uploaddir .= DS;
				}
				break;
			case "DOWNLOADPATH":
				$uploaddir = VmConfig::get('download_root');	//Max, I think this path should always been set as absolute path, 
				break;
		}
		if ($this->checkUploadedFile('file_upload')) {
			$this->_productfile->upload_success = $this->moveUploadedFile( 'file_upload', $uploaddir.$this->_productfile->file_name);
		}
		else {
			$mainframe->enqueueMessage(JText::_('VM_FILES_UPLOAD_FAILURE'), 'error');
			return false;
		}

		switch (JRequest::getVar('file_type')) {
			case 'image':
			case 'product_images':
			case 'product_full_image':
			case 'product_thumb_image':
				$this->_productfile->file_is_image = "1";
//				$this->_productfile->file_url = IMAGEURL."product/".$this->_productfile->file_name;
				$this->_productfile->file_url = JPATH_SITE.DS.VmConfig::get('media_product_path').$this->_productfile->file_name;
				$file_create_thumbnail = JRequest::getVar("file_create_thumbnail", false);
				if ($file_create_thumbnail) {
				    $tmp_filename = $uploaddir.$this->_productfile->file_name;
					/* Resize the image */
					$height = JRequest::getInt('thumbimage_height');
					$width = JRequest::getInt('thumbimage_width');
					$this->_productfile->fileout = $this->createThumbImage($tmp_filename, 'product', $height, $width );
					if (is_file($this->_productfile->fileout)) {
						$mainframe->enqueueMessage(JText::_('VM_FILES_IMAGE_RESIZE_SUCCESS'));
						$thumbimg = getimagesize($this->_productfile->fileout);
						$this->_productfile->file_image_thumb_width = $thumbimg[0];
						$this->_productfile->file_image_thumb_height = $thumbimg[1];
						/**
						$ss_str_wh_in = '_'.$height.'x'.$width.'.';
						$ss_str_wh_out = '_'.$this->_productfile->file_image_thumb_height.'x'.$this->_productfile->file_image_thumb_width.'.';
						$ss_new_fileout = str_replace($ss_str_wh_in, $ss_str_wh_out, $this->_productfile->fileout);

						if (!file_exists($ss_new_fileout)) {
							rename($this->_productfile->fileout, $ss_new_fileout);
						}
						else {
							$mainframe->enqueueMessage(JText::_('VM_FILES_UPLOAD_EXISTS').' '.$ss_new_fileout, 'notice');
						}

						$this->_productfile->fileout = str_replace($ss_str_wh_in, $ss_str_wh_out, $this->_productfile->fileout);
						*/
					}
					else {
						$mainframe->enqueueMessage(JText::_('VM_FILES_IMAGE_RESIZE_FAILURE'), 'error');
						$this->_productfile->file_image_thumb_height = "";
						$this->_productfile->file_image_thumb_width = "";
					}
					$fullimg = getimagesize( $tmp_filename );
					$this->_productfile->file_image_width = $fullimg[0];
					$this->_productfile->file_image_height = $fullimg[1];

				}
				if( !empty($d["file_resize_fullimage"])) {
					// Resize the full image!
					$height = JRequest::getInt('fullimage_height');
					$width = JRequest::getInt('fullimage_width');

					vmImageTools::resizeImage( $uploaddir.$this->_productfile->file_name, $uploaddir.$this->_productfile->file_name, $width, $height );

					$fullimg = getimagesize($uploaddir.$this->_productfile->file_name);
					$this->_productfile->file_image_width = $fullimg[0];
					$this->_productfile->file_image_height = $fullimg[1];
				}
				break;

			default:
				### File Upload ###
				$this->_productfile->file_is_image = "0";
				$this->_producfile->file_image_height = "";
				$this->_producfile->file_image_width = "";
				$this->_producfile->file_image_thumb_height = "";
				$this->_producfile->file_image_thumb_width = "";
				break;
		}
		return true;
	}

	/**
	 * Checks if a file was correctly uploaded.
	 *
	 * @param string $fieldname The name of the index in $_FILES to check
	 * @return boolean True when the file upload is correct, false when not.
	 */
	function checkUploadedFile($fieldname) {
		$mainframe = JFactory::getApplication('site');
		$files = JRequest::get('files');
		if( (!is_uploaded_file($files[$fieldname]['tmp_name']) && strstr( $fieldname, 'thumb')
			|| substr( JRequest::getVar($fieldname.'_url'), 0, 4 ) == 'http' )) {
			return true;
		}
		elseif( is_uploaded_file($files[$fieldname]['tmp_name'])) {
			return true;
		}
		else {
			switch ($files[$fieldname]['error']) {
				case 0: //no error; possible file attack!
					//$vmLogger->warning( "There was a problem with your upload." );
					break;
				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ERR_TOOBIG'), 'warning');
					break;
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ERR_TOOBIG'), 'warning');
					break;
				case 3: //uploaded file was only partially uploaded
					$mainframe->enqueueMessage(JText::_('VM_PRODUCT_FILES_ERR_PARTIALLY'), 'warning');
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
	}

	/**
	 * Moves an uploaded file $_FILES[$fieldname] to $storefilename
	 *
	 * @param string $fieldname The array index of the _FILES array
	 * @param string $storefilename The full path including filename to the store path
	 */
	function moveUploadedFile( $fieldname, $storefilename ) {
		if( !is_uploaded_file( $_FILES[$fieldname]['tmp_name'] )) {
			return true;
		}
		if( move_uploaded_file( $_FILES[$fieldname]['tmp_name'], $storefilename )) {
			chmod( $storefilename, 0644 );
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Resizes an image
	 *
	 * @param string $fileName
	 * @param string $section
	 * @param int $height
	 * @param int $width
	 * @return string
	 */
	function createThumbImage( $fileName, $section='product', $height=90, $width=90) {
		
//		require_once(CLASSPATH . 'imageTools.class.php' );
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
		
		$media_path_url = VmConfig::get('media_product_path');
		$media_filename = $fileName;
		$media_thumb_name = $fileName.'_thumb';
		$image = new VmImage($media_path_url,$media_filename,$media_thumb_name);
		
		/* Generate Image Destination File Name */
//		$pathinfo = pathinfo( $fileName );
//		$to_file_thumb = basename( $fileName, '.'.$pathinfo['extension']).".".$pathinfo['extension'];
//		$fileout = IMAGEPATH.$section.DS.'resized'.DS.$to_file_thumb;
//		vmImageTools::ResizeImage( $fileName, $fileout, $height, $width );
		$fileout = $image->createThumb();
		
		return $fileout;

	}

	/**
	 * Delete an image file
	 */
	public function getDeleteMedia() {
		$mainframe = Jfactory::getApplication('site');
		$deleted = 0;
	 	$row = $this->getTable('media');
	 	$cids = JRequest::getVar('cid');
	 	if (is_array($cids)) {
			foreach ($cids as $key => $cid) {
				$row->load($cid);
				if ($row->delete()) $deleted++;
			}
		}
		else {
			$row->load($cids);
			if ($row->delete()) $deleted++;
		}
		$mainframe->enqueueMessage(str_replace('{X}', $deleted, JText::_('DELETED_{X}_MEDIA_ITEMS')));
		/* Redirect so the user cannot reload the delete action */
		$url = 'index.php?option=com_virtuemart&view=media';
		$productid = JRequest::getInt('product_id', false);
		if ($productid) $url .= '&product_id='.$productid;
		$mainframe->redirect($url);
	}

	/**
	 * Publish/unpublish a media item
	 */
	public function getPublishMedia() {
		$row = $this->getTable('media');
		$cids = JRequest::getVar('cid');
		$row->load($cids[0]);
		$row->file_published = (JRequest::getCmd('task') == 'publish') ? 1 : 0;
		$row->store();
	}
}
// pure php no closing tag
