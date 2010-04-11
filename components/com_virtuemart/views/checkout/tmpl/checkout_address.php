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

echo '<h4>'. JText::_('CHECK_OUT_GET_SHIPPING_ADDR') . '</h4>';

?>
<!-- Customer Ship To -->
<div style="width: 40%; align: left; float: left;">
	<?php
	// $ps_checkout->ship_to_addresses_radio($auth["user_id"], "ship_to_info_id", $ship_to_info_id);
	?>
</div>
<br />
<div style="width: 100%; align: left; float:left;">
	<?php echo JText::_('VM_ADD_SHIPTO_1') ?>
	<a href="<?php JRoute::_('index.php?'.basename($_SERVER['PHP_SELF']). "?page=account.shipto&next_page=checkout.index");?>">
        <?php echo JText::_('VM_ADD_SHIPTO_2') ?></a>.
</div>

<!-- END Customer Ship To -->
