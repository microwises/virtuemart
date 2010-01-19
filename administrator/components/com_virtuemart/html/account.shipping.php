<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
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

$mainframe->setPageTitle( JText::_('VM_USER_FORM_SHIPTO_LBL') );

// Set the CMS pathway
$pathway = array();
$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_ACCOUNT_TITLE'), $sess->url( SECUREURL .'index.php?page=account.index' ) );
$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_USER_FORM_SHIPTO_LBL') );
$vm_mainframe->vmAppendPathway( $pathway );

// Set the internal VirtueMart pathway
$tpl = vmTemplate::getInstance();
$tpl->set( 'pathway', $pathway );
$vmPathway = $tpl->fetch( 'common/pathway.tpl.php' );
$tpl->set( 'vmPathway', $vmPathway );

$q  = "SELECT * FROM #__{vm}_user_info WHERE ";
$q .= "(address_type='ST' OR address_type='st') ";
$q .= "AND user_id='" . $auth["user_id"] . "'";
$db->query($q);

$tpl->set('db', $db);
echo $tpl->fetch('pages/'.$page.'.tpl.php');
?>
