<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.waiting_thanks.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage themes
* @copyright Copyright (C) 2007 Soeren Eberhardt. All rights reserved.
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



if( $ok ) {
	echo '<h3>'.JText::_('VM_WAITING_LIST_THANKS').'</h3>';
}
?>
<br />
<br />
<?php 
  	echo '<a class="previous_page" href="'.$sess->url( $_SERVER['PHP_SELF']."?page=shop.product_details&product_id=$product_id" ). '">'
      . JText::_('VM_BACK_TO_DETAILS').'</a>';
?> 
