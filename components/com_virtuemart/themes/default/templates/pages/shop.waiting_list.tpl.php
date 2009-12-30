<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.waiting_list.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
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



?>
<form action="<?php echo $mm_action_url ?>index.php" method="post" name="waiting">
<input type="hidden" name="option" value="<?php echo $option ?>" />
<input type="hidden" name="func" value="waitinglistadd" />
<?php echo JText::_('VM_WAITING_LIST_MESSAGE') ?>
<br />
<br />

<input type="text" class="inputbox" name="notify_email" value="<?php echo $my->email ?>" />
&nbsp;&nbsp;

<input type="submit" class="button" name="waitinglistadd" value="<?php echo JText::_('VM_WAITING_LIST_NOTIFY_ME') ?>" />

<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
<input type="hidden" name="page" value="shop.waiting_thanks" />

</form> 
