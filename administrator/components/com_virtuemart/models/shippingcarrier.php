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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop shipping carriers
 *
 * @package	VirtueMart
 * @subpackage ShippingCarrier
 * @author RickG
 */
class VirtueMartModelShippingCarrier extends VmModel {

//    /** @var integer Primary key */
//    var $_id;
    /** @var integer Joomla plugin ID */
    var $jplugin_id;
    /** @var integer Vendor ID */
    var $virtuemart_vendor_id;

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('shippingcarriers');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
    function getShippingCarrier() {

		if (empty($this->_data)) {
		    $this->_data = $this->getTable('shippingcarriers');
		    $this->_data->load((int)$this->_id);

		    if(empty($this->_data->virtuemart_vendor_id)){
		    	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
		    	$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();;
		    }

		}

		return $this->_data;
    }

    /**
     * Retireve a list of shipping carriers from the database.
     *
     * @author RickG
     * @return object List of shipping carrier objects
     */
    public function getShippingCarriers() {
        if (VmConfig::isJ15()) {
			$table = '#__plugins';
			$enable = 'published';
			$ext_id = 'id';
		}
		else {
			$table = '#__extensions';
			$enable = 'enabled';
			$ext_id = 'extension_id';
		}
	$query = 'SELECT `#__virtuemart_shippingcarriers`.* ,  `'.$table.'`.`name` as shipping_method_name FROM `#__virtuemart_shippingcarriers` ';
        $query .= 'JOIN `'.$table.'`   ON  `'.$table.'`.`'.$ext_id.'` = `#__virtuemart_shippingcarriers`.`shipping_carrier_jplugin_id` ';
	$query .= 'ORDER BY `#__virtuemart_shippingcarriers`.`virtuemart_shippingcarrier_id`';

	$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

	return $this->_data;
    }



    	/**
	 * Bind the post data to the paymentmethod tables and save it
     *
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    public function store($data)
	{
		//$data = JRequest::get('post');

		if(isset($data['params'])){
			$params = new JParameter('');
			$params->bind($data['params']);
			$data['shipping_carrier_params'] = $params->toString();
		}

	  	if(empty($data['virtuemart_vendor_id'])){
	  	   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
	   		$data['virtuemart_vendor_id'] = VirtueMartModelVendor::getLoggedVendor();
	  	} else {
	  		$data['virtuemart_vendor_id'] = (int) $data['virtuemart_vendor_id'];
	  	}
		// missing string FIX, Bad way ?
		if (VmConfig::isJ15()) {
			$tb = '#__plugins';
			$ext_id = 'id';
		} else {
			$tb = '#__extensions';
			$ext_id = 'extension_id';
		}
		$q = 'SELECT `element` FROM `' . $tb . '` WHERE `' . $ext_id . '` = "'.$data['shipping_carrier_jplugin_id'].'"';
		$this->_db->setQuery($q);
		$data['shipping_carrier_element'] = $this->_db->loadResult();

		$table = $this->getTable('shippingcarriers');
		if (!$table->bindChecknStore($data)) {
			$this->setError($table->getError());
		}

		return $table->virtuemart_shippingcarrier_id;
	}

}

//no closing tag
