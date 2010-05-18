<?php
/**
*
* Data module for shipping carriers
*
* @package	VirtueMart
* @subpackage ShippingCarrier
* @author RickG
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
 * Model class for shop shipping carriers
 *
 * @package	VirtueMart
 * @subpackage ShippingCarrier
 * @author RickG
 */
class VirtueMartModelShippingCarrier extends JModel {

    /** @var integer Primary key */
    var $_id;
    /** @var objectlist shipping carrier data */
    var $_data;
    /** @var integer Total number of shipping carriers in the database */
    var $_total;
    /** @var pagination Pagination for shipping carrier list */
    var $_pagination;


    /**
     * Constructor for the shipping carrier model.
     *
     * The shipping carrier id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    function __construct() {
	parent::__construct();

	// Get the pagination request variables
	$mainframe = JFactory::getApplication() ;
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

	// Set the state pagination variables
	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);

	// Get the shipping carrier id or array of ids.
	$idArray = JRequest::getVar('cid',  0, '', 'array');
	$this->setId((int)$idArray[0]);
    }


    /**
     * Resets the shipping carrier id and data
     *
     * @author RickG
     */
    function setId($id) {
	$this->_id = $id;
	$this->_data = null;
    }


    /**
     * Loads the pagination for the shipping carrier table
     *
     * @author RickG
     * @return JPagination Pagination for the current list of shipping carriers
     */
    function getPagination() {
	if (empty($this->_pagination)) {
	    jimport('joomla.html.pagination');
	    $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
	}
	return $this->_pagination;
    }


    /**
     * Gets the total number of shipping carriers
     *
     * @author RickG
     * @return int Total number of shipping carriers in the database
     */
    function _getTotal() {
	if (empty($this->_total)) {
	    $query = 'SELECT `shipping_carrier_id` FROM `#__vm_shipping_carrier`';
	    $this->_total = $this->_getListCount($query);
	}
	return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
    function getShippingCarrier() {
	$db = JFactory::getDBO();

	if (empty($this->_data)) {
	    $this->_data = $this->getTable('shipping_carrier');
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
     * Bind the post data to the shipping carrier table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
     */
    function store() {
	$table = $this->getTable('shipping_carrier');

	$data = JRequest::get( 'post' );
	// Bind the form fields to the shipping carrier table
	if (!$table->bind($data)) {
	    $this->setError($table->getError());
	    return false;
	}

	// Make sure the shipping carrier record is valid
	if (!$table->check()) {
	    $this->setError($table->getError());
	    return false;
	}

	// Save the shipping carrier record to the database
	if (!$table->store()) {
	    $this->setError($table->getError());
	    return false;
	}

	return true;
    }


    /**
     * Delete all record ids selected
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
    function delete() {
	$shippingCarrierIds = JRequest::getVar('cid',  0, '', 'array');
	$table =& $this->getTable('shipping_carrier');

	foreach($shippingCarrierIds as $shippingCarrierId) {
	    if ($this->deleteShippingCarrierRates($shippingCarrierId)) {
		if (!$table->delete($shippingCarrierId)) {
		    $this->setError($table->getError());
		    return false;
		}
	    }
	    else {
		$this->setError('Could not remove shipping carrier rates!');
		return false;
	    }
	}

	return true;
    }


    /**
     * Delete all rate records for a given shipping carrier id.
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
    function deleteShippingCarrierRates($carrierId = '') {
	if ($carrierId) {
	    $db =& JFactory::getDBO();

	    $query = 'DELETE FROM `#__vm_shipping_rate`  WHERE `shipping_rate_carrier_id` = "' . $carrierId . '"';
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
     * Retireve a list of shipping carriers from the database.
     *
     * @author RickG
     * @return object List of shipping carrier objects
     */
    public function getShippingCarriers() {
	$query = 'SELECT * FROM `#__vm_shipping_carrier` ';
	$query .= 'ORDER BY `#__vm_shipping_carrier`.`shipping_carrier_id`';
	$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	return $this->_data;
    }
    
    
    /**
     * 
     * @author Max Milbers
     * @return a associative List
     */
	public function getShippingCarrierRates($weight=0, $country=0, $zip=0) {
		
		$query = 'SELECT * FROM `#__vm_shipping_carrier` ';
		$query .= 'ORDER BY `#__vm_shipping_carrier`.`shipping_carrier_list_order`';
		$carrierList = $this->_getList($query);

		$db =& JFactory::getDBO();
		$list;
		$i=(int)0;

		foreach ($carrierList as $key=>$value) {
			$query = 'SELECT * FROM `#__vm_shipping_rate` WHERE `shipping_rate_carrier_id`="'.$value->shipping_carrier_id.'" ';
			if(!empty($weight)){
				$query .= 'AND `shipping_rate_weight_start` <="'.$weight.'" AND `shipping_rate_weight_end` > "'.$weight.'"';
			}		 	 	
			if(!empty($zip)){
				$query .= 'AND `shipping_rate_zip_start` <="'.$zip.'" AND `shipping_rate_zip_end` > "'.$zip.'"';
			}
			
			//todo country and dimension
//			if(!empty($country)){
//				$countries = explode(';', $this->_data->shipping_rate_country);
//				$query .= 'AND (`shipping_rate_country` ="'.$country.'" OR `shipping_rate_country` ="" )';
//			}
//			echo '<br />$query '.$query;
			$db->setQuery($query);
			if ($db->query()) {
				echo '<br /><br />';
//				echo 'WIE <pre>'. print_r($db->loadAssocList()) .'</pre>';
				$list[$value->shipping_carrier_name]=$db->loadAssocList();
				echo '<br />';
//				return true;
	    	}
		}
//		echo '<br />';echo '<br />';echo '<br />';
//		echo '<pre>'. print_r($list) .'</pre>';
//		
		return $list;
    }
    
}

//no closing tag
