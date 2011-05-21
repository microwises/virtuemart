<?php
/**
*
* Shipping Carrier table
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
* @version $Id: shippingcarriesr.php -1   $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Shipping Carrier table class
 * The class is is used to manage the shipping carriers in the shop.
 *
 * @package	VirtueMart
 * @author RickG, Max Milbers
 */
class TableShippingcarriers extends VmTable {

	/** @var int Primary key */
	var $virtuemart_shippingcarrier_id			= 0;
	/** @var string Shipping Carrier name*/
	var $shipping_carrier_name			= '';
	/** @var int Shipping Joomla plugin I */
	var $shipping_carrier_jplugin_id	= 0;
	/** @var int Vendor ID */
	var $virtuemart_vendor_id		= 0;
	/** @var int published boolean */
	var $published						= 1;

    /**
     * @author Max Milbers
     * @param $db A database connector object
     */
    function __construct(&$db) {
		parent::__construct('#__virtuemart_shippingcarriers', 'virtuemart_shippingcarrier_id', $db);

		$this->setUniqueName('shipping_carrier_name','COM_VIRTUEMART_SHIPPING_CARRIER_NAME_ALREADY_EXISTS');
		$this->setObligatoryKeys('shipping_carrier_jplugin_id');

		$this->setLoggable();

    }


//    /**
//     * Validates the shipping carrier record fields.
//     *
//     * @author RickG
//     * @return boolean True if the table buffer is contains valid data, false otherwise.
//     */
//    function check() {
//	if (!$this->shipping_carrier_name) {
//	    $this->setError(JText::_('COM_VIRTUEMART_SHIPPING_CARRIER_RECORDS_MUST_CONTAIN_CARRIER_NAME'));
//	    return false;
//	}
//
//	if (($this->shipping_carrier_name) && ($this->virtuemart_shippingcarrier_id == 0)) {
//	    $db =& JFactory::getDBO();
//
//	    $q = 'SELECT count(*) FROM `#__virtuemart_shippingcarriers` ';
//	    $q .= 'WHERE `shipping_carrier_name`="' .  $this->shipping_carrier_name . '"';
//	    $db->setQuery($q);
//	    $rowCount = $db->loadResult();
//	    if ($rowCount > 0) {
//		$this->setError(JText::_('COM_VIRTUEMART_SHIPPING_CARRIER_NAME_ALREADY_EXISTS'));
//		return false;
//	    }
//	}
//
//	return true;
//    }




}
// pure php no closing tag
