<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* This file lists all shipping modules. It's in a file that's not called shipping_module_list
* because we currently can't add or remove shipping modules automatically!
*
* @version $Id: store.shipping_module_list.php 1553 2008-10-23 13:44:05Z soeren_nb $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
$_REQUEST['folder'] = 'shipping';
include(PAGEPATH.'admin.plugin_list.php');
?>