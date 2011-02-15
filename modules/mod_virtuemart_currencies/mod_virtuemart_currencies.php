<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Currency Selector Module
*
* NOTE: THIS MODULE REQUIRES THE VIRTUEMART COMPONENT!
/*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2010 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/


/***********
 * TODO When this module is converted to 1.5, the following fields must be set
 * in the cart:
 * 	currency_id: ID of the user selected currenct
 * 	currency_rate: Actual rate compared to the shop currency
 * 	
 * Prices in the orders are saved in the shop currency; these fields are required
 * to show the prices to the user in a later stadium.
  */
$currency_id = $mainframe->getUserStateFromRequest( "currency_id", 'currency_id',JRequest::getInt('currency_id', 1) );

$vendorId = JRequest::getInt('vendorid', 1);
$text_before = $params->get( 'text_before', '');

/* table vm_vendor */
$db = JFactory::getDBO();
$q = 'SELECT `vendor_accepted_currencies` FROM `#__vm_vendor` WHERE `vendor_id`='.$vendorId;
$db->setQuery($q);
$currency_codes = explode(',',$db->loadResult());

/* table vm_currency */
$q = 'SELECT `currency_id`,`currency_code`,`currency_name` FROM `#__vm_currency` WHERE `currency_code` IN ("'.implode('","',$currency_codes).'") and published =1 ORDER BY `currency_name`';
$db->setQuery($q);
$currencies = $db->loadObjectList();
/* load the template */
require(JModuleHelper::getLayoutPath('mod_virtuemart_currencies'));
    ?>
