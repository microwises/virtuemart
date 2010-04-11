<?php
/**
*
* Template for the checkout
*
* @package	VirtueMart
* @subpackage Checkout
* @author RolandD
* @todo create the totalsales value in the cart
* @todo Come up with a better solution for the zone shipping module
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 2343 2010-03-31 20:03:37Z milbo $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// If you have a coupon code, please enter it here:
echo JText::_('PHPSHOP_COUPON_ENTER_HERE') . '<br />';
?>  
<form action="index.php" method="post">
	<input type="text" name="coupon_code" id="coupon_code" width="10" maxlength="30" class="inputbox" />
	<input type="hidden" name="do_coupon" value="yes" />
	<input type="hidden" name="option" value="<?php echo JRequest::getWord('option'); ?>" />
	<input type="hidden" name="view" value="checkout" />
	<input type="submit" value="<?php echo JText::_('PHPSHOP_COUPON_SUBMIT_BUTTON') ?>" class="button" />
</form>