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
* @copyright (C) 2006-2007 soeren - All rights reserved.
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
// Session variable shopper_currency
 $session =& JFactory::getSession();
$session->set( 'myvar', 'helloworld' );
dump ($session, 'my session'); ?>
	
	Choisir sa monnaie