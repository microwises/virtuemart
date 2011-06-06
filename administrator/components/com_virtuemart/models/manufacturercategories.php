<?php
/**
*
* Manufacturer category model
*
* @package	VirtueMart
* @subpackage Manufacturer category
* @author Patrick Kohl
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
 * Model class for manufacturer category
 *
 * @package	VirtueMart
 * @subpackage Manufacturer category
 * @author
 */
class VirtuemartModelManufacturercategories extends VmModel {


	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('manufacturercategories');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     */
	function getManufacturerCategory(){

		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('manufacturercategories');
   			$this->_data->load((int)$this->_id);
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}

  		return $this->_data;
	}
	/**
	 * Delete all record ids selected
     *
     * @return boolean True is the remove was successful, false otherwise.
     */
	function remove()
	{
		$categoryIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('manufacturercategories');

    	foreach($categoryIds as $categoryId) {
       		if($table->checkManufacturer($categoryId)) {
	    		if (!$table->delete($categoryId)) {
	            		$this->setError($table->getError());
	            		return false;
	       		}
       		}
       		else {
       			return false;
       		}
    	}
    	return true;
	}


	/**
	 * Delete all state records for a given  id.
     *
     * @return boolean True is the remove was successful, false otherwise.
     */
	function removeManufacturerCategories($categoryId = '')
	{
		if ($categoryId) {
			$db = JFactory::getDBO();

			$query = 'DELETE FROM `#__virtuemart_manufacturercategories`  WHERE `virtuemart_manufacturercategories_id`= "'.$categoryId.'"';
			$db->setQuery($query);
			if ($db->query()) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
    		return false;
    	}
	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the remove was successful, false otherwise.
     */
	function publish($publishId = false){

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','manufacturercategories',$publishId);

	}


	/**
	 * Retireve a list of countries from the database.
	 *
     * @param string $onlyPuiblished True to only retreive the published categories, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of manufacturer categories objects
	 */
	function getManufacturerCategories($onlyPublished=false, $noLimit=false)
	{
		$query = 'SELECT * FROM `#__virtuemart_manufacturercategories` ';
		if ($onlyPublished) {
			$query .= 'WHERE `#__virtuemart_manufacturercategories`.`published` = 1';
		}
		$query .= ' ORDER BY `#__virtuemart_manufacturercategories`.`mf_category_name`';

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

	/**
	 * Build category filter
	 *
	 * @return object List of category to build filter select box
	 */
	function getCategoryFilter(){
		$db = JFactory::getDBO();
		$query = 'SELECT `virtuemart_manufacturercategories_id` as `value`, `mf_category_name` as text'
				.' FROM #__virtuemart_manufacturercategories';
		$db->setQuery($query);

		$categoryFilter[] = JHTML::_('select.option',  '0', '- '. JText::_('COM_VIRTUEMART_SELECT_MANUFACTURER_CATEGORY') .' -' );

		$categoryFilter = array_merge($categoryFilter, $db->loadObjectList());


		return $categoryFilter;

	}
}

// pure php no closing tag