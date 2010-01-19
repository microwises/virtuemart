<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

?>
<form action="<?php echo SECUREURL ?>index.php" method="post">
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="page" value="account.order_details" />
<input type="hidden" name="print" value="1" />
<table border="0" cellspacing="0" cellpadding="10" width="100%" align="center">
<tr>
   <td>
   <b><?php echo JText::_("Order Information") ?></b>
   <br />
	<?php $ps_order->list_order("A","1"); ?>
   <br />
   <input type="submit" class="button" name="submit" value="<?php echo JText::_('BACK'); ?>" />
   </td>
</tr>
</table>
</form>
<!-- Body ends here -->
