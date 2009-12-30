<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: shop.waiting_thanks.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );

$tpl = vmTemplate::getInstance();
$tpl->set_vars(array('product_id'=>$product_id,
								'ok' => $ok )
						);
echo $tpl->fetch('pages/shop.waiting_thanks.tpl.php');
?>