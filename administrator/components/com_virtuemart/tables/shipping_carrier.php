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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Shipping Carrier table class
 * The class is is used to manage the shipping carriers in the shop.
 *
 * @package	VirtueMart
 * @author RickG
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
