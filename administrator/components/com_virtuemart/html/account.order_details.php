<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: account.order_details.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
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

global $vendor_currency, $user;

require_once(CLASSPATH.'ps_order_status.php');
require_once(CLASSPATH.'ps_userfield.php');
require_once(CLASSPATH.'ps_checkout.php');
require_once(CLASSPATH.'ps_product.php');
$ps_product= new ps_product;

$tpl = vmTemplate::getInstance();

$print = JRequest::getVar( 'pop', 0);
$order_id = JRequest::getVar( 'order_id', 0);
$tpl->set( 'print', $print );
$tpl->set( 'order_id', $order_id );

$db =& new ps_DB;
$q = "SELECT * FROM `#__{vm}_orders` WHERE ";
$q .= "user_id=" . $auth["user_id"] . " AND order_id='$order_id'";
$db->query($q);

if ($db->next_record()) {
	
	$mainframe->setPageTitle( JText::_('VM_ACC_ORDER_INFO').' : '.JText::_('VM_ORDER_LIST_ID').' '.$db->f('order_id'));
	require_once( CLASSPATH.'ps_product_category.php');
	
	// Set the CMS pathway
	$pathway = array();
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_ACCOUNT_TITLE'), $sess->url( SECUREURL .'index.php?page=account.index' ) );
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_ACC_ORDER_INFO') );
	$vm_mainframe->vmAppendPathway( $pathway );
	
	// Set the internal VirtueMart pathway
	$tpl->set( 'pathway', $pathway );
	$vmPathway = $tpl->fetch( 'common/pathway.tpl.php' );
	$tpl->set( 'vmPathway', $vmPathway );

	// Get bill_to information
	$dbbt = new ps_DB;
	$q  = "SELECT * FROM `#__{vm}_order_user_info` WHERE order_id='" . $db->f("order_id") . "' ORDER BY address_type ASC";
	$dbbt->query($q);
	
	$dbbt->next_record();
	$old_user = '';
	if( !empty( $user ) && is_object($user)) {
		$old_user = $user;
	}
	
	$user = $dbbt->get_row();
	/** Retrieve Payment Info **/
	$dbpm = new ps_DB;
	
	$q  = "SELECT * FROM `#__{vm}_payment_method` p, `#__{vm}_order_payment` op, `#__{vm}_orders` o ";
	$q .= "WHERE op.order_id='$order_id' ";
	$q .= "AND p.payment_method_id=op.payment_method_id ";
	$q .= "AND o.user_id='" . $auth["user_id"] . "' ";
	$q .= "AND o.order_id='$order_id' ";
	$dbpm->query($q);
	$dbpm->next_record();
	
	$registrationfields = ps_userfield::getUserFields('registration', false, '', true, true );
	$shippingfields = ps_userfield::getUserFields('shipping', false, '', true, true );
	
	//Vendor is based on order_id by Max Milbers
	require_once(CLASSPATH.'ps_order.php');
	$vendor_id = ps_order::get_vendor_id_by_order_id($order_id);

	$tpl->set( 'db', $db );
	$tpl->set( 'dbbt', $dbbt );
	$tpl->set( 'dbpm', $dbpm );
	$tpl->set( 'user', $user );
	$tpl->set( 'order_id', $order_id );
	$tpl->set( 'vendor_id', $vendor_id );
	$tpl->set( 'registrationfields', $registrationfields );
	$tpl->set( 'shippingfields', $shippingfields );
	$tpl->set( 'time_offset', $mosConfig_offset );

	// Get the template for this page
	echo $tpl->fetch( 'pages/account.order_details.tpl.php' );
	if( !empty($old_user) && is_object($old_user)) {
		$user = $old_user;
	}
} else {
	vmRedirect( $sess->url( SECUREURL .'index.php?page=account.index' ) );
}
?>
