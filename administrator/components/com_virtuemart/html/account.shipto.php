<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: account.shipto.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

require_once( CLASSPATH . "ps_userfield.php" );

$mainframe->setPageTitle( JText::_('VM_ADD_SHIPTO_1') ." ".JText::_('VM_ADD_SHIPTO_2') );
      
$Itemid = $sess->getShopItemid();
$next_page = JRequest::getVar(  "next_page", "account.shipping" );
$user_info_id = JRequest::getVar(  "user_info_id", "" );

// Set the CMS pathway
$pathway = array();
if( stristr( $next_page, 'checkout' ) !== false ) {
	// We are in the checkout process
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_CHECKOUT_TITLE'), $sess->url( SECUREURL."index.php?page=$next_page") );
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_SHOPPER_FORM_SHIPTO_LBL') );	
} else {
	// We are in account maintenance
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_ACCOUNT_TITLE'), $sess->url( SECUREURL .'index.php?page=account.index' ) );
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_USER_FORM_SHIPTO_LBL'), $sess->url( SECUREURL."index.php?page=$next_page") );
	$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_SHOPPER_FORM_SHIPTO_LBL') );
}
$vm_mainframe->vmAppendPathway( $pathway );

// Set the internal VirtueMart pathway
$tpl = vmTemplate::getInstance();
$tpl->set( 'pathway', $pathway );
$vmPathway = $tpl->fetch( 'common/pathway.tpl.php' );
$tpl->set( 'vmPathway', $vmPathway );

$missing = vmGet( $vars, 'missing' );

if (!empty( $missing )) {
    echo "<script type=\"text/javascript\">alert('". JText::_('CONTACT_FORM_NC',false) ."'); </script>\n";
}
$db = new ps_DB;
if (!empty($user_info_id)) {
  $q =  "SELECT * from #__{vm}_user_info WHERE user_info_id='".$database->getEscaped($user_info_id)."' ";
  $q .=  " AND user_id='".$auth['user_id']."'";
  $q .=  " AND address_type='ST'";
  $db->query($q);
  $db->next_record();
}

if( !$db->num_rows()) {
	$vars['country'] = JRequest::getVar( 'country', $vendor_country);
}

$fields = ps_userfield::getUserFields( 'shipping' );

$tpl->set_vars( array('next_page' => $next_page,
					'fields' => $fields,
					'missing' => $missing,
					'vars' => $vars,
					'db' => $db,
					'user_info_id' => $user_info_id
					));
echo $tpl->fetch('pages/'.$page.'.tpl.php');

?>