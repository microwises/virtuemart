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

/**
 * Model class for manufacturer category
 *
 * @package	VirtueMart
 * @subpackage Manufacturer category
 * @author
 */
class VirtueMartModelManufacturerCategory extends JModel {

	/** @var integer Primary key */
    var $_id;
	/** @var objectlist manufacturer category data */
    var $_data;
	/** @var integer Total number of manufacturer categories in the database */
	var $_total;
	/** @var pagination Pagination for manufacturer category list */
	var $_pagination;


    /**
     * Constructor for the manufacturer category model.
     *
     * The man id is read and detmimined if it is an array of ids or just one single id.
     *
     */
    function __construct()
    {
        parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the country id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);
    }


    /**
     * Resets the manufacturer category id and data
     *
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the manufacturer category table
	 *
     * @return JPagination Pagination for the current list of manufacturers
	 */
    function getPagination()
    {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}


	/**
	 * Gets the total number of manufacturers categories
	 *
	 * @return int Total number of manufacturer categories in the database
	 */
	function _getTotal()
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `mf_category_id` FROM `#__vm_manufacturer_category`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     */
	function getManufacturerCategory()
	{
		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('manufacturer_category');
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
	 * Bind the post data to the manufacturer category table and save it
     *
     * @return boolean True is the save was successful, false otherwise.
	 */
    function store()
	{
		$table = $this->getTable('manufacturer_category');

		$data = JRequest::get('post');

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

		return $table->mf_category_id;
	}


	/**
	 * Delete all record ids selected
     *
     * @return boolean True is the delete was successful, false otherwise.
     */
	function delete()
	{
		$categoryIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('manufacturer_category');

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
     * @return boolean True is the delete was successful, false otherwise.
     */
	function deleteManufacturerCategories($categoryId = '')
	{
		if ($categoryId) {
			$db = JFactory::getDBO();

			$query = 'DELETE FROM `#__vm_manufacturer_category`  WHERE `mf_category_id`= "'.$categoryId.'"';
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
     * @return boolean True is the delete was successful, false otherwise.
     */
	function publish($publishId = false)
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','manufacturer_category',$publishId);

//		$table = $this->getTable('manufacturer_category');
//		$categoryIds = JRequest::getVar( 'cid', array(0), 'post', 'array' );
//
//        if (!$table->publish($categoryIds, $publishId)) {
//			$this->setError($table->getError());
//			return false;
//        }
//
//		return true;
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
		$query = 'SELECT * FROM `#__vm_manufacturer_category` ';
		if ($onlyPublished) {
			$query .= 'WHERE `#__vm_manufacturer_category`.`published` = 1';
		}
		$query .= ' ORDER BY `#__vm_manufacturer_category`.`mf_category_name`';

		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}
	/**
	 * Build category filter
	 *
	 * @return object List of category to build filter select box
	 */
	function getCategoryFilter()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT mf_category_id as value, mf_category_name as text'
				.' FROM #__vm_manufacturer_category';
		$db->setQuery($query);

		$categoryFilter[] = JHTML::_('select.option',  '0', '- '. JText::_( 'VM_SELECT_MANUFACTURER_CATEGORY' ) .' -' );

		$categoryFilter = array_merge($categoryFilter, $db->loadObjectList());


		return $categoryFilter;



	}
}
// pure php no closing tag