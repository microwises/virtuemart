<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: shop.recommend.php
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2006 Alatis GmbH & Co. KG. All rights reserved.
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
global $ok;
$product_id = JRequest::getVar(  'product_id', null);

include_once(CLASSPATH.'ps_communication.php');

$vm_mainframe->addStyleSheet( 'templates/'. $mainframe->getTemplate() );

if( empty( $_POST['submit'] ) || !$ok ) {
	$mainframe->setPageTitle( JText::_('VM_RECOMMEND_FORM_LBL') );
	echo '<h3>'.JText::_('VM_RECOMMEND_FORM_LBL').'</h3>';
	
	ps_communication::showRecommendForm($product_id);
}
else {
	$mainframe->setPageTitle( JText::_('VM_RECOMMEND_FORM_LBL') );
	echo '<span class="contentheading">'. JText::_('VM_RECOMMEND_DONE').' '. vmGet($_POST,'recipient_mail').'</span> <br />
		<br />
		<br />
		<a href="javascript:window.close();">
		<span class="small">'. JText::_('PROMPT_CLOSE') .'</span>
		</a>';
	
}

?>