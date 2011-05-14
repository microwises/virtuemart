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
 * Shipping Rate table class
 * The class is is used to manage the shipping rates in the shop.
 *
 * @package	VirtueMart
 * @author RickG
 */
class TableShippingrates extends JTable {

	/** @var int Primary key */
	var $virtuemart_shippingrate_id			= 0;
	/** @var string Shipping Rate name*/
	var $shipping_rate_name      	= '';
	/** @var string Shipping Rate name*/
	var $shipping_rate_carrier_id   = 0;
		/** @var string Shipping Rate name*/
	var $shipping_rate_country      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_zip_start   	= '';
	/** @var string Shipping Rate name*/
	var $shipping_rate_zip_end      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_weight_start      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_weight_end      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_value      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_package_fee      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_virtuemart_currency_id      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_vat_id      	= '';
		/** @var string Shipping Rate name*/
	var $shipping_rate_list_order      	= '';
              /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;
	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_shippingrates', 'virtuemart_shippingrate_id', $db);
	}


	/**
	 * Validates the shipping rate record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
        if (empty($this->shipping_rate_name)) {
			$this->setError(JText::_('COM_VIRTUEMART_SHIPPING_RATE_RECORDS_MUST_CONTAIN_RATE_NAME'));
			return false;
		}

		if (($this->shipping_rate_name) && ($this->virtuemart_shippingrate_id == 0)) {
		    $db =& JFactory::getDBO();

			$q = 'SELECT count(*) FROM `#__virtuemart_shippingrates` ';
			$q .= 'WHERE `shipping_rate_name`="' .  $this->shipping_rate_name . '"';
            $db->setQuery($q);
		    $rowCount = $db->loadResult();
			if ($rowCount > 0) {
				$this->setError(JText::_('COM_VIRTUEMART_SHIPPING_RATE_NAME_ALREADY_EXISTS'));
				return false;
			}
		}

		if(empty($this->shipping_rate_carrier_id)){
			$this->setError(JText::_('COM_VIRTUEMART_SHIPPING_RATE_HAS_NO_SHIPPER_ID'));
			return false;
		}

		return true;
	}




}
// pure php no closing tag
