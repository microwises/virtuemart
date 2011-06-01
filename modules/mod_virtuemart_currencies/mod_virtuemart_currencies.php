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
* @copyright (C) 2011 virtuemart team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/


/***********
 * 
 * Prices in the orders are saved in the shop currency; these fields are required
 * to show the prices to the user in a later stadium.
  */
$virtuemart_currency_id = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', 1) );

$vendorId = JRequest::getInt('vendorid', 1);
$text_before = $params->get( 'text_before', '');

/* table vm_vendor */
$db = JFactory::getDBO();
$q  = 'SELECT `vendor_accepted_currencies` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`='.$vendorId;
$db->setQuery($q);
$currency_ids = $db->loadResult();
if (!$currency_ids) return;
//$currency_codes = explode(',' , $currencies->vendor_accepted_currencies );
 

/* table vm_currency */
//$q = 'SELECT `virtuemart_currency_id`,CONCAT_WS(" ",`currency_name`,`currency_exchange_rate`,`currency_symbol`) as currency_txt FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` IN ('.$currency_codes.') and enabled =1 ORDER BY `currency_name`';
$q = 'SELECT `virtuemart_currency_id`,CONCAT_WS(" ",`currency_name`,`currency_symbol`) as currency_txt
FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` IN ('.$currency_ids.') and (`virtuemart_vendor_id` = "'.$vendorId.'" OR `shared`="1") AND published = "1" ORDER BY `ordering`,`currency_name`';
$db->setQuery($q);
$currencies = $db->loadObjectList();
/* load the template */
require(JModuleHelper::getLayoutPath('mod_virtuemart_currencies'));
    ?>
