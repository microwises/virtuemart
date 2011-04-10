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

/**
 * Model for VirtueMart Product Files
 *
 * @package		VirtueMart
 */
class VirtueMartModelMedia extends JModel {

	/** @var integer Primary key */
    private $file_id = 0;

   /** @var integer Total number of files in the database */
    var $_total;
    /** @var pagination Pagination for file list */
    var $_pagination;


	/**
	 * Constructor for product files
	 */
	function __construct(){
		parent::__construct();

//		$this->file_id = $id;

		/* Get the file ID */
		$this->setId(JRequest::getInt('file_id', null));

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	/**
	 * Sets new Id and resets data ...
	 * @author Max Milbers
	 * @param int $id
	 */
    function setId($id) {
		$this->file_id = $id;
		$this->_data = null;
    }

	/**
	 * Loads the pagination
	 *
	 * @author RickG
	 */
    public function getPagination() {
		if (empty($this->_pagination)) {
	    	jimport('joomla.html.pagination');
	    	$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}

    /**
     * Gets the total number of currencies
     *
     * @author Max Milbers
     * @return int Total number of currencies in the database
     */
    function _getTotal() {
		if (empty($this->_total)) {
		    $query = 'SELECT `file_id` FROM `#__vm_media`';
		    $this->_total = $this->_getListCount($query);
		}
		return $this->_total;
    }

    /**
     * Gets a single media by file_id
     * .
     * @param string $type
     * @param string $mime mime type of file, use for exampel image
     * @return mediaobject
     */
    function getFile($type=0,$mime=0){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();

   		$data = $this->getTable('Media');
   		$data->load($this->file_id);

  		if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

  		$media = VmMediaHandler::createMedia($data,$type,$mime);

  		return $media;

    }

    /**
     * Kind of getFiles, it creates a bunch of image objects by an array of file_ids
     *
     * @author Max Milbers
     * @param unknown_type $file_ids
     * @param unknown_type $type
     * @param unknown_type $mime
     */
	function createMediaByIds($file_ids,$type='',$mime=''){

    	if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

    	$medias = array();
    	if(!empty($file_ids)){
    		if(!is_array($file_ids)) $file_ids = explode(',',$file_ids);

    	    foreach($file_ids as $file_id){
	    		$data = $this->getTable('Media');
	    		$id = is_object($file_id)? $file_id->file_id:$file_id;
	   			$data->load($id);
	   			$media = VmMediaHandler::createMedia($data,$type,$mime);
	   			if(is_object($file_id) && !empty($file_id->product_name)) $media->product_name = $file_id->product_name;
	  			$medias[] = $media;
    		}
    	}

    	if(empty($medias)){
    		$data = $this->getTable('Media');
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
    	$product_id = JRequest::getVar('product_id',0);

    	if(!empty($product_id)){
    		$query = 'SELECT `file_ids` as file_id FROM `#__vm_product` ';
    		$whereItems[] = '`product_id` = "'.$product_id.'"';
    		$oderby = '`mdate`';
    	}

    	$cat_id = JRequest::getVar('category_id',0);
    	if(empty($query) && !empty($cat_id)){
    		$query = 'SELECT `file_ids` as file_id FROM `#__vm_category` ';
    		$whereItems[] = '`category_id` = "'.$cat_id.'"';
    		$oderby = '`mdate`';
    	}

    	if(empty($query)){
    		$query='SELECT `file_id` FROM `#__vm_media` ';
    	    if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
	    	if(!Permissions::getInstance()->check('admin') ){
				$whereItems[] = '(`vendor_id` = "'.$vendorId.'" OR `shared`="1")';
	    	}

	    	if ($onlyPublished) {
				$whereItems[] = '`#__vm_media`.`published` = 1';
			}
//			if(empty($whereItems)) $whereItems[] = ' 1 ';
			$oderby = '`#__vm_media`.`mdate`';
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

		$query .= ' ORDER BY '.$oderby;

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
     * @param unknown_type $data
     * @param unknown_type $table
     * @param unknown_type $type
     */
	function storeMedia($data,$table,$type){

		// Check token, how does this really work?
//		JRequest::checkToken() or jexit( 'Invalid Token, while trying to save media' );

		$oldId = $data['file_id'];
		$this -> setId($oldId);
		$file_id = $this->store($type,$data);

//		if($data['file_id']!=$file_id){
			$file_ids = $data['file_ids'];
			if(is_array($file_ids)){
				$key = array_search($data['file_id'],$file_ids);
				if(!$key){
					$file_ids[] = $file_id;
				}else {
					$file_ids[$key] = $file_id;
				}
			} else {
				$data['file_ids'] = $file_id;
			}

			$data['file_id']=$file_id;

			if(is_array($file_ids)){
				$file_ids = array_unique($file_ids);
				$data['file_ids'] = implode(',',$file_ids);
			} else {
				$data['file_ids'] = $file_ids;
			}

			// Bind the form fields to the country table
			if (!$table->bind($data)) {
				$this->setError($table->getError());
				return false;
			}

			// Make sure the category record is valid
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Save the category record to the database
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
			return true;
//		}
	}

	/**
	 * Store an entry of a mediaItem, this means in end effect every media file in the shop
	 * images, videos, pdf, zips, exe, ...
	 *
	 * @author Max Milbers
	 */
	private function store($type,$data=0) {

		$table = $this->getTable('media');
		if(empty($data))$data = JRequest::get('post');

		$modified = JFactory::getDate();
		$data['mdate']=$modified->toMySQL();
		if(empty($this->file_id)) $data['cdate'] = $modified->toMySQL();

		if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');

		// Bind the form fields to the table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		$data = VmMediaHandler::prepareStoreMedia($table,$data,$type); //this does not store the media, it process the actions and prepares data

		if(empty($data['file_url'])){
//			$this->delete($data['file_id']);
		} else {
			// Bind the form fields to the table again
			if (!$table->bind($data)) {
				$this->setError($table->getError());
				return false;
			}

			// Make sure the record is valid
			if (!$table->check()) {
				if($table->getError()){
					foreach($table->getErrors() as $error){
						$this->setError($error);
					}
				}
				return false;
			}
			// Save the record to the database
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
		}

		return $table->file_id;
	}

	/**
	 * Delete an image file
	 * @author unknow, maybe Roland Dalmulder
	 * @author Max Milbers
	 */
	public function delete($cids) {
		$mainframe = Jfactory::getApplication('site');
//		$deleted = 0;
	 	$row = $this->getTable('media');
//	 	$cids = JRequest::getVar('cid');
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
		$mainframe->enqueueMessage(str_replace('{X}', $deleted, JText::_('COM_VIRTUEMART_DELETED_X_MEDIA_ITEMS')));

		//TODO update table belonging, category, product, venodor
		//delete media from server
		/* Redirect so the user cannot reload the delete action */
//		$url = 'index.php?option=com_virtuemart&view=media';
//		$productid = JRequest::getInt('product_id', false);
//		if ($productid) $url .= '&product_id='.$productid;
//		$mainframe->redirect($url);
	}

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function publish($publishId = false)
	{
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','media',$publishId);

	}

	public function attachImages($objects,$nameId,$type,$mime=''){
		if(!empty($objects)){
			if(!is_array($objects)) $objects = array($objects);
			foreach($objects as $object){
				$object->images = $this->createMediaByIds($object->$nameId,$type,$mime);
			}
		}
	}

}
// pure php no closing tag
