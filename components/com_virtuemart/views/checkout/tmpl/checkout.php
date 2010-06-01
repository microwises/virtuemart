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
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Load the coupon */
echo $this->loadTemplate('coupon');

/* Load the address */
echo $this->loadTemplate('address');

/* Load the shipping */
echo $this->loadTemplate('shipping');

/* Load the payment */
echo $this->loadTemplate('payment');
?>