<?php
/**
 *
 * Data module for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
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
 * Model class for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 */
class VirtueMartModelOrderstatus extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('orderstates');
	}

	function getVMCoreStatusCode(){
		return array( 'P','S');
	}

	/**
	 * Retrieve a list of order statuses from the database.
	 *
	 * @return object List of order status objects
	 */
	function getOrderStatusList()
	{

		if (JRequest::getWord('view') !== 'orderstatus') $ordering = ' order by `ordering` ';
		else $ordering = $this->_getOrdering();
		$this->_noLimit=true;
		$this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_orderstates`','','',$ordering);
		// 		vmdebug('order data',$this->_data);
		return $this->_data ;
	}

}

//No Closing tag
