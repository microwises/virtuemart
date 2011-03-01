<?php
/**
*
* Layout for the edit coupon
*
* @package	VirtueMart
* @subpackage Cart
* @author Oscar van Eijk
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 2458 2010-06-30 18:23:28Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>


<form method="post" id="userForm" name="enterCouponCode" action="<?php echo JRoute::_( 'index.php' ); ?>">
<div style="text-align: right; width: 100%;">
	<button class="button" type="submit"><?php echo JText::_('SAVE'); ?></button>

	<button class="button" type="reset" onClick="window.location.href='<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=cart' ); ?>'" ><?php echo JText::_('CANCEL'); ?></button>
</div>

<?php
	echo JText::_('VM_COUPON_ENTER_HERE');
?>
	<input type="text" title="'. JText::_('VM_CART_UPDATE') .'" class="inputbox" size="20" maxlength="32" name="coupon_code" value="<?php echo $this->couponCode; ?>" />

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="setcoupon" />
	<input type="hidden" name="controller" value="cart" />
</form>
