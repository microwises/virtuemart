<?php
/**
*
* Manufacturer Model
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author RolandD, Patrick Kohl, Max Milbers
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
 * Model class for VirtueMart Manufacturers
 *
 * @package VirtueMart
 * @subpackage Manufacturer
 * @author RolandD, Max Milbers
 * @todo Replace getOrderUp and getOrderDown with JTable move function. This requires the virtuemart_product_category_xref table to replace the product_list with the ordering column
 */
class VirtueMartModelManufacturer extends JModel {

	var $_total;
	var $_pagination;

	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
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
            if (JRequest::getInt('virtuemart_manufacturer_id', 0) > 0) $filter .= ' WHERE #__virtuemart_manufacturers.`virtuemart_manufacturer_id` = '.JRequest::getInt('virtuemart_manufacturer_id');
			$q = "SELECT COUNT(*)
				FROM `#__virtuemart_manufacturers` ".
				$filter;
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Load a single manufacturer
     */
     public function getManufacturer() {

     	$this->_id = JRequest::getInt('virtuemart_manufacturer_id', 0);
     	$this->_data = $this->getTable();
     	$this->_data->load($this->_id);

     	$xrefTable = $this->getTable('mf_media_xref');
		$this->_data->file_ids = $xrefTable->load($this->_id);

     	return $this->_data;
     }

     /**
	 * Bind the post data to the manufacturer table and save it
     *
     * @author Roland
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
	public function store() {

		/* Setup some place holders */
		$table = $this->getTable();

		/* Load the data */
		$data = JRequest::get('post', 4);
		$data['mf_desc'] = JRequest::getVar('mf_desc', '', 'post', 'string', JREQUEST_ALLOWRAW);

		// Bind the form fields to the country table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the country record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the country record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Process the images //		$fullImage = JRequest::getVar('virtuemart_media_id', null, 'files',array());
		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		$mediaModel = new VirtueMartModelMedia();
		$xrefTable = $this->getTable('mf_media_xref');
		$mediaModel->storeMedia($data,$table,'manufacturer');

		return $table->virtuemart_manufacturer_id;
	}


	/**
	 * Delete all record ids selected
     *
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function delete() {
		$manufacturerIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('manufacturer');

    	foreach($manufacturerIds as $manufacturerId) {
       		if (!$table->delete($manufacturerId)) {
           		$this->setError($table->getError());
           		return false;
       		}
    	}

    	return true;
	}

    /**
     * Select the products to list on the product list page
     */
    public function getManufacturerList() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q = "SELECT
			";
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('virtuemart_product_id');
    }

    /**
     * Returns a dropdown menu with manufacturers
     * @author RolandD
	 * @return object List of manufacturer to build filter select box
	 */
	function getManufacturerDropDown() {
		$db = JFactory::getDBO();
		$query = "SELECT `virtuemart_manufacturer_id` AS `value`, `mf_name` AS text, '' AS disable
				FROM `#__virtuemart_manufacturers`";
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTML::_('select.option',  '0', '- '. JText::_('COM_VIRTUEMART_SELECT_MANUFACTURER') .' -' ));
		return $options;
	}

    /**
    * Set the publish/unpublish state
    * @return bool true if manufacturers are published or false if manufacturers are not published
    */
    public function getPublish() {
     	$cid = JRequest::getVar('cid', false);
     	if (is_array($cid)) {
     		$db = JFactory::getDBO();
     		$cids = implode( ',', $cid );
			if (JRequest::getVar('task') == 'publish') $state =  'Y'; else $state = 'N';
			$q = "UPDATE #__virtuemart_manufacturers
				SET product_publish = ".$db->Quote($state)."
				WHERE virtuemart_product_id IN (".$cids.")";
			$db->setQuery($q);
			if ($db->query()) return true;
			else return false;
		}
    }

    /**
	 * Retireve a list of countries from the database.
	 *
     * @param string $onlyPuiblished True to only retreive the publish countries, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of manufacturer objects
	 */
	public function getManufacturers($onlyPublished=false, $noLimit=false) {
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$option	= 'com_virtuemart';


		$mf_category_id	= $mainframe->getUserStateFromRequest( $option.'mf_category_id', 'mf_category_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );

		$where = array();
		if ($mf_category_id > 0) {
			$where[] .= '`#__virtuemart_manufacturers`.`mf_category_id` = '. $mf_category_id;
		}
		if ( $search ) {
			$where[] .= 'LOWER( `#__virtuemart_manufacturers`.`mf_name` ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}
		if ($onlyPublished) {
			$where[] .= '`#__virtuemart_manufacturers`.`published` = 1';
		}

		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		$query = 'SELECT * FROM `#__virtuemart_manufacturers` '
				. $where;

		$query .= ' ORDER BY `#__virtuemart_manufacturers`.`mf_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	public function addImagesToManufacturer($manus){

		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		if(empty($this->mediaModel))$this->mediaModel = new VirtueMartModelMedia();

		$this->mediaModel->attachImages($manus,'file_ids','vendor','image');

//		if(!empty($manus)){
//			if(!is_array($cats)) $cats = array($cats);
//			foreach($cats as $cat){
//				$this->mediaModel -> setId($manus->file_ids );
//				$manus->images = $this->mediaModel->getFile('vendor','image');
//			}
//		}
}

}
// pure php no closing tag