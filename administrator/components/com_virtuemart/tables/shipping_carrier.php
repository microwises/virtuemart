<?php
/**
 * Shipping Carrier table
 *
 * @package	VirtueMart
 * @subpackage ShippingCarrier
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Shipping Carrier table class
 * The class is is used to manage the shipping carriers in the shop.
 *
 * @author RickG
 * @package	VirtueMart
 */
class TableShipping_Carrier extends JTable {
    /** @var int Primary key */
    var $shipping_carrier_id			= 0;
    /** @var string Shipping Carrier name*/
    var $shipping_carrier_name      	= '';
    /** @var char Shipping Carrier code */
    var $shipping_carrier_list_order    = 0;


    /**
     * @author RickG
     * @param $db A database connector object
     */
    function __construct(&$db) {
	parent::__construct('#__vm_shipping_carrier', 'shipping_carrier_id', $db);
    }


    /**
     * Validates the shipping carrier record fields.
     *
     * @author RickG
     * @return boolean True if the table buffer is contains valid data, false otherwise.
     */
    function check() {
	if (!$this->shipping_carrier_name) {
	    $this->setError(JText::_('Shipping Carrier records must contain a carrier name.'));
	    return false;
	}

	if (($this->shipping_carrier_name) && ($this->shipping_carrier_id == 0)) {
	    $db =& JFactory::getDBO();

	    $q = 'SELECT count(*) FROM `#__vm_shipping_carrier` ';
	    $q .= 'WHERE `shipping_carrier_name`="' .  $this->shipping_carrier_name . '"';
	    $db->setQuery($q);
	    $rowCount = $db->loadResult();
	    if ($rowCount > 0) {
		$this->setError(JText::_('The given shipping carrier name already exists.'));
		return false;
	    }
	}

	return true;
    }




}
?>
