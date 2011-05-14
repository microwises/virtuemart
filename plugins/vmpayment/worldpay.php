<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage payment
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

class plgPaymentWorldpay extends vmPaymentPlugin {
	
	var $payment_code = "WORLDPAY";
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.2.0
	 */
	function plgPaymentWorldpay( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	/**
	 * Shows the HTML Form Code to redirect the customer to WorldPay
	 *
	 * @param object $db
	 * @param object $user
	 * @param object $dbbt
	 */
	function showPaymentForm( &$db, $user, $dbbt ) {
		?>
	
		<form action="https://select.worldpay.com/wcc/purchase" method="post">
	        <input type=hidden name="testMode" value="100"> 
	       	<input type="hidden" name="instId" value="<?php echo $this->params->get('WORLDPAY_INST_ID') ?>" />
	        <input type="hidden" name="cartId" value="<?php echo $db->f("virtuemart_order_id") ?>" />
	        <input type="hidden" name="amount" value="<?php echo $db->f("order_total") ?>" />
	      	<input type="hidden" name="currency" value="<?php echo $_SESSION['vendor_currency'] ?>" />
	        <input type="hidden" name="desc" value="Products" />
	        <input type="hidden" name="email" value="<?php echo $user->email?>" />
	         <input type="hidden" name="address" value="<?php echo $user->address_1?>&#10<?php echo $user->address_2?>&#10<?php echo
	        $user->city?>&#10<?php echo $user->state?>" />
	        <input type="hidden" name="name" value="<?php echo $user->title?><?php echo $user->first_name?>. <?php echo $user->middle_name?><?php echo $user->last_name?>" />
	      	<input type="hidden" name="country" value="<?php echo $user->country?>"/>
	      	<input type="hidden" name="postcode" value="<?php echo $user->zip?>" />
	        <input type="hidden" name="tel"  value="<?php echo $user->phone_1?>">
	        <input type="hidden" name="withDelivery"  value="true">
	        <br />
			<?php // shouldn't this be a "JText..."? Franz 20100410 ?>
			<input type="submit" value ="PROCEED TO PAYMENT PAGE" />
	    </form>
	    <?php
	}
}
?>
