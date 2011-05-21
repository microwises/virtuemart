<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved by the author.
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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Product Files
 *
 * @package		VirtueMart
 */
class VirtueMartModelMedia extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_media_id');
		$this->setMainTable('medias');

	}

    /**
     * Gets a single media by virtuemart_media_id
     * .
     * @param string $type
     * @param string $mime mime type of file, use for exampel image
     * @return mediaobject
     */
    function getFile($type=0,$mime=0){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();

   		$data = $this->getTable('medias');
   		$data->load($this->_id);

  		if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

  		$media = VmMediaHandler::createMedia($data,$type,$mime);

  		return $media;

    }

    /**
     * Kind of getFiles, it creates a bunch of image objects by an array of virtuemart_media_id
     *
     * @author Max Milbers
     * @param unknown_type $virtuemart_media_id
     * @param unknown_type $type
     * @param unknown_type $mime
     */
	function createMediaByIds($virtuemart_media_id,$type='',$mime=''){

    	if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

    	$medias = array();
    	if(!empty($virtuemart_media_id)){
    		if(!is_array($virtuemart_media_id)) $virtuemart_media_id = explode(',',$virtuemart_media_id);

    		$data = $this->getTable('medias');
    	    foreach($virtuemart_media_id as $virtuemart_media_id){
	    		$id = is_object($virtuemart_media_id)? $virtuemart_media_id->virtuemart_media_id:$virtuemart_media_id;
	   			$data->load($id);
	   			$media = VmMediaHandler::createMedia($data,$type,$mime);
	   			if(is_object($virtuemart_media_id) && !empty($virtuemart_media_id->product_name)) $media->product_name = $virtuemart_media_id->product_name;
	  			$medias[] = $media;
    		}
    	}

    	if(empty($medias)){
    		$data = $this->getTable('medias');
    		$data->load(0);
    		$medias[] = VmMediaHandler::createMedia($data,$type,$mime);
    	}

    	return $medias;
	}

    /**
	 * Retireve a list of files from the database. This is meant only for backend use
	 *
     * @author Max Milbers
     * @param string $onlyPuiblished True to only retreive the published files, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of media objects
	 */
    function getFiles($onlyPublished=false, $noLimit=false,  $count=false, $where=array()){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();
    	$vendorId = 1; //TODO set to logged user or requested vendorId, not easy later
    	$query = '';
    	$whereItems = array();
    	$virtuemart_product_id = JRequest::getVar('virtuemart_product_id',0);

    	if(!empty($virtuemart_product_id)){
    		$query = 'SELECT `virtuemart_media_id` as virtuemart_media_id FROM `#__virtuemart_product_medias` ';
    		$whereItems[] = '`virtuemart_product_id` = "'.$virtuemart_product_id.'"';
    		$oderby = '`#__virtuemart_medias`.`modified_on`';
    	}

    	$cat_id = JRequest::getVar('virtuemart_category_id',0);
    	if(empty($query) && !empty($cat_id)){
    		$query = 'SELECT `virtuemart_media_id` as virtuemart_media_id FROM `#__virtuemart_category_medias` ';
    		$whereItems[] = '`virtuemart_category_id` = "'.$cat_id.'"';
    		$oderby = '`#__virtuemart_medias`.`modified_on`';
    	}

    	if(empty($query)){
    		$query='SELECT `virtuemart_media_id` FROM `#__virtuemart_medias` ';
    	    if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
	    	if(!Permissions::getInstance()->check('admin') ){
				$whereItems[] = '(`virtuemart_vendor_id` = "'.$vendorId.'" OR `shared`="1")';
	    	}

	    	if ($onlyPublished) {
				$whereItems[] = '`#__virtuemart_medias`.`published` = 1';
			}
//			if(empty($whereItems)) $whereItems[] = ' 1 ';
			$oderby = '`#__virtuemart_medias`.`modified_on`';
    	}


		if (!empty($where)) $whereItems = array_merge($whereItems,$where);

		if(!empty($whereItems)){
			$where = 'WHERE (';
			foreach($whereItems as $item){
				$where .= $item.' AND ';
			}
			$where = substr($where,0,strlen($where)-5);
			$where .= ')';
			$query .= $where;
		}

		//Todo sorting for modified_on does not work
//		$query .= ' ORDER BY '.$oderby;

		$app =& JFactory::getApplication();

		if ( $count) {
			$this->_data = $this->_getListCount($query);
			return $this->_data;
		} else if($noLimit){
			$this->_data = $this->_getList($query);
		} else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
//			$this->_data = $this->_getList($query);
		}

		$errMsg = $this->_db->getErrorMsg();
		$errs = $this->_db->getErrors();

		if(!empty($errMsg)){
			$errNum = $this->_db->getErrorNum();
			$this->setError('SQL-Error: '.$errNum.' '.$errMsg.' <br /> used query '.$query);
		}

		if(!empty($errs)){
			foreach($errs as $err){
				if(!empty($err)) $this->setError($err);
			}
		}

		if($errs = $this->getErrors()){
			foreach($errs as $err){
				$app->enqueueMessage($err);
			}
		}

		if(!is_array($this->_data)){
			$this->_data = explode(',',$this->_data);
		}

		$this->_data = $this->createMediaByIds($this->_data);

		return $this->_data;
    }


    /**
     * This function stores a media and updates then the refered table
     *
     * @author Max Milbers
     * @author Patrick Kohl
     * @param unknown_type $data
     * @param unknown_type $table
     * @param unknown_type $type
     */
	function storeMedia($data,$type){

		// Check token, how does this really work?
//		JRequest::checkToken() or jexit( 'Invalid Token, while trying to save media' );

//		$oldId = $data['active_media_id'];
//		$this -> setId($oldId);
		$virtuemart_media_id = $this->store($data,$type);
		$this -> setId($virtuemart_media_id);

		/* add the virtuemart_media_id & remove 0 and '' from $data */
		$virtuemart_media_ids = array_merge( (array)$virtuemart_media_id,$data['virtuemart_media_id']);
		$virtuemart_media_ids = array_diff($virtuemart_media_ids,array('0',''));
		$data['virtuemart_media_id'] = array_unique($virtuemart_media_ids);

//		$data['virtuemart_media_id'] = array_reverse ($virtuemart_media_id,true);

		$table = $this->getTable($type.'_medias');
		// Bind the form fields to the country table
		$data = $table->bindChecknStore($data);
	    $errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		return $data->virtuemart_media_id;

	}

	/**
	 * Store an entry of a mediaItem, this means in end effect every media file in the shop
	 * images, videos, pdf, zips, exe, ...
	 *
	 * @author Max Milbers
	 */
	public function store($data,$type) {


		if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

		$table = $this->getTable('medias');
		// Bind the form fields to the table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		$data = VmMediaHandler::prepareStoreMedia($table,$data,$type); //this does not store the media, it process the actions and prepares data
		// workarround for media published and product published two fields in one form.
		if ($data['media_published'])
			$data['published'] = $data['media_published'];
		else
			$data['published'] = 0;


		$table->bindChecknStore();
	    $errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		return $table->virtuemart_media_id;
	}

	/**
	 * Delete an image file
	 * @author unknow, maybe Roland Dalmulder
	 * @author Max Milbers
	 */
	public function remove($cids) {
		$mainframe = Jfactory::getApplication('site');
//		$removed = 0;
	 	$row = $this->getTable('medias');
//	 	$cids = JRequest::getVar('cid');
	 	if (is_array($cids)) {
			foreach ($cids as $key => $cid) {
				$row->load($cid);
				if ($row->delete()) $removed++;
			}
		}
		else {
			$row->load($cids);
			if ($row->delete()) $removed++;
		}
		$mainframe->enqueueMessage(JText::sprintf('COM_VIRTUEMART_DELETED_X_MEDIA_ITEMS',$removed));

		//TODO update table belonging, category, product, venodor
		//remove media from server
		/* Redirect so the user cannot reload the remove action */
//		$url = 'index.php?option=com_virtuemart&view=media';
//		$productid = JRequest::getInt('virtuemart_product_id', false);
//		if ($productid) $url .= '&virtuemart_product_id='.$productid;
//		$mainframe->redirect($url);
	}

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the remove was successful, false otherwise.
     */
	public function publish($publishId = false)
	{
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','medias',$publishId);

	}

	public function attachImages($objects,$type,$mime=''){
		if(!empty($objects)){
			if(!is_array($objects)) $objects = array($objects);
			foreach($objects as $object){

				if(empty($object->virtuemart_media_id)) $virtuemart_media_id = null; else $virtuemart_media_id = $object->virtuemart_media_id;

				$object->images = $this->createMediaByIds($virtuemart_media_id,$type,$mime);

			}
		}
	}

}
// pure php no closing tag
