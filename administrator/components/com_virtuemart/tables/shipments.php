<?php
/**
*
* Shipment Carrier table
*
* @package	VirtueMart
* @subpackage ShipmentCarrier
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: shipmentcarriesr.php -1   $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Shipment Carrier table class
 * The class is is used to manage the shipment carriers in the shop.
 *
 * @package	VirtueMart
 * @author RickG, Max Milbers
 */
class TableShipments extends VmTable {

	/** @var int Primary key */
	var $virtuemart_shipment_id			= 0;
        /** @var int Vendor ID */
	var $virtuemart_vendor_id		= 0;
        /** @var int Shipment Joomla plugin I */
	var $shipment_carrier_jplugin_id	= 0;
	/** @var string Shipment Carrier name*/
	var $shipment_carrier_name	= '';
        	/** @var string Shipment Carrier name*/
	var $shipment_carrier_desc	= '';
        /** @var string Element of shipmentmethod */
        var $shipment_carrier_element = '';
        /** @var string parameter of the shipmentmethod*/
	var $shipment_carrier_params				= 0;
        /** var float rate value */
        var $shipment_carrier_value				= 0;
        var $shipment_carrier_package_fee                       = 0;
        var $shipment_carrier_vat_id				= 0;

        var $ordering						= 0;
        var $shared						= 0;
	/** @var int published boolean */
	var $published						= 1;


    /**
     * @author Max Milbers
     * @param $db A database connector object
     */
    function __construct(&$db) {
		parent::__construct('#__virtuemart_shipments', 'virtuemart_shipment_id', $db);
		// we can have several time the same shipment name. It is the vendor problem to set up correctly his shipment rate.
		//$this->setUniqueName('shipment_carrier_name');
		$this->setObligatoryKeys('shipment_carrier_jplugin_id');

		$this->setLoggable();

    }

}
// pure php no closing tag
