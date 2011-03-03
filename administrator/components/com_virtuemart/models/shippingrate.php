<?php
/**
*
* Data module for shipping rates
*
* @package	VirtueMart
* @subpackage ShippingRate
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
 * @subpackage ShippingRate
 * @author RickG
 */
class VirtueMartModelShippingRate extends JModel {

	/** @var integer Primary key */
    var $_id;
	/** @var objectlist shipping rate data */
    var $_data;
	/** @var integer Total number of shipping rates in the database */
	var $_total;
	/** @var pagination Pagination for shipping rate list */
	var $_pagination;


    /**
     * Constructor for the shipping rate model.
     *
     * The shipping rate id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
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

        // Get the shipping rate id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);
    }


    /**
     * Resets the shipping rate id and data
     *
     * @author RickG
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the shipping rate table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of shipping rates
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
	 * Gets the total number of shipping rates
	 *
     * @author RickG
	 * @return int Total number of shipping rates in the database
	 */
	function _getTotal()
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `shipping_rate_id` FROM `#__vm_shipping_rate`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getShippingRate()
	{
		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('shipping_rate');
   			$this->_data->load((int)$this->_id);

   			// Convert ; separated string into array
   			$this->_data->shipping_rate_country = explode(';', $this->_data->shipping_rate_country);
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}

  		return $this->_data;
	}

	/**
	 * Get the prices for a given shipping rate
	 * @param integer $_id Shipping rate ID
	 * @return Indexed array with the keys shipping_rate_value and shipping_rate_package_fee
	 * @author Oscar van Eijk
	 */
	public function getShippingRatePrices ($_id)
	{
		$_q = 'SELECT `shipping_rate_value` '
			. ',      `shipping_rate_package_fee` '
			. 'FROM  `#__vm_shipping_rate` '
			. 'WHERE `shipping_rate_id` = ' . $_id . ' '
		;
		$_rates = $this->_getList($_q);
		return array('shipping_rate_value' => $_rates[0]->shipping_rate_value
					,'shipping_rate_package_fee' => $_rates[0]->shipping_rate_package_fee)
		;
	}

	/**
	 * Bind the post data to the shipping rate table and save it
     *
     * @author RickG, Max Milbers, Oscar van Eijk
     * @return mixed False if the save was unsuccessful, the shiping rate ID otherwise.
	 */
    function store()
	{
		$table = $this->getTable('shipping_rate');

		$data = JRequest::get( 'post' );

		// Store multiple selectlist entries as a ; separated string
		if (key_exists('shipping_rate_country', $data) && is_array($data['shipping_rate_country'])) {
		    $data['shipping_rate_country'] = implode(';', $data['shipping_rate_country']);
		}

		// Bind the form fields to the shipping rate table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the shipping rate record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the shipping rate record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->shipping_rate_id;
	}


	/**
	 * Delete all record ids selected
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
	function delete()
	{
		$shippingCarrierIds = JRequest::getVar('cid',  0, '', 'array');
    	$table =& $this->getTable('shipping_rate');

    	foreach($shippingCarrierIds as $shippingCarrierId) {
    		if (!$table->delete($shippingCarrierId)) {
            	$this->setError($table->getError());
            	return false;
        	}
    	}

    	return true;
	}


	/**
	 * Retireve a list of shipping rates from the database.
	 *
     * @author RickG
	 * @return object List of shipping rate objects
	 */
	function getShippingRates()
	{
		$query = 'SELECT sr.*, sc.shipping_carrier_name FROM `#__vm_shipping_rate` AS sr ';
		$query .= 'JOIN `#__vm_shipping_carrier` AS sc ON `sc`.`shipping_carrier_id` = `sr`.`shipping_rate_carrier_id`';
		$query .= 'ORDER BY `sr`.`shipping_rate_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}
}
// pure php no closing tag