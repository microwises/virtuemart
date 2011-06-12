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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for VirtueMart Manufacturers
 *
 * @package VirtueMart
 * @subpackage Manufacturer
 * @author RolandD, Max Milbers
 * @todo Replace getOrderUp and getOrderDown with JTable move function. This requires the virtuemart_product_category_xref table to replace the ordering with the ordering column
 */
class VirtueMartModelManufacturer extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_manufacturer_id');
		$this->setMainTable('manufacturers');

	}


    /**
     * Load a single manufacturer
     */
     public function getManufacturer() {

     	$this->_data = $this->getTable('manufacturers');
     	$this->_data->load($this->_id);

     	$xrefTable = $this->getTable('manufacturer_medias');
		$this->_data->virtuemart_media_id = $xrefTable->load($this->_id);

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
		$table = $this->getTable('manufacturers');

		/* Load the data */
		$data = JRequest::get('post');
		/* add the mf desc as html code */
		$data['mf_desc'] = JRequest::getVar('mf_desc', '', 'post', 'string', JREQUEST_ALLOWHTML );

		$table->bindChecknStore($data);
		$errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		// Process the images //		$fullImage = JRequest::getVar('virtuemart_media_id', null, 'files',array());
		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		$mediaModel = new VirtueMartModelMedia();
		$mediaModel->storeMedia($data,'manufacturer');
	    $errors = $mediaModel->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}
		return $table->virtuemart_manufacturer_id;
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


		$virtuemart_manufacturercategories_id	= $mainframe->getUserStateFromRequest( $option.'virtuemart_manufacturercategories_id', 'virtuemart_manufacturercategories_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );

		$where = array();
		if ($virtuemart_manufacturercategories_id > 0) {
			$where[] .= 'M.`virtuemart_manufacturercategories_id` = '. $virtuemart_manufacturercategories_id;
		}
		if ( $search ) {
			$where[] .= 'LOWER( M.`mf_name` ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}
		if ($onlyPublished) {
			$where[] .= '`#__virtuemart_manufacturers`.`published` = 1';
		}

		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		$query = 'SELECT M.*,MC.`mf_category_name`   FROM `#__virtuemart_manufacturers` AS M LEFT JOIN `#__virtuemart_manufacturercategories` AS MC on M.`virtuemart_manufacturercategories_id`= MC.`virtuemart_manufacturercategories_id`'
				. $where;
		$query .= ' ORDER BY M.`mf_name`';

		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		// set total for pagination
		$this->_total = $this->_getListCount($query);

		return $this->_data;
	}

}
// pure php no closing tag