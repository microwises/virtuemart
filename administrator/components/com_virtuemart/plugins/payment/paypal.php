<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
 * @version $Id: paypal.php 1760 2009-05-03 22:58:57Z Aravot $
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

/**
 */
class plgPaymentPaypal extends vmPaymentPlugin {
	
	var $payment_code = "PAYPAL" ;
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
	function plgPaymentPaypal( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	/**
	 * Shows the HTML Form Code to redirect the customer to PayPal
	 *
	 * @param ps_DB $db
	 * @param stdCls $user
	 * @param ps_DB $dbbt
	 */
	function showPaymentForm( &$db, $user, $dbbt ) {
		global  $page, $vendor_image_url;
		
		$db1 = new ps_DB( ) ;
		$q = "SELECT country_2_code FROM #__vm_country WHERE country_3_code='" . $user->country . "' ORDER BY country_2_code ASC" ;
		$db1->query( $q ) ;
		
		$url = "https://www.paypal.com/cgi-bin/webscr" ;
		$tax_total = $db->f( "order_tax" ) + $db->f( "order_shipping_tax" ) ;
		$discount_total = $db->f( "coupon_discount" ) + $db->f( "order_discount" ) ;
		$post_variables = Array( 
			"cmd" => "_ext-enter" , 
			"redirect_cmd" => "_xclick" , 
			"upload" => "1" , 
			"business" => $this->params->get('PAYPAL_EMAIL') , 
			"receiver_email" => $this->params->get('PAYPAL_EMAIL') , 
			"item_name" => JText::_( 'VM_ORDER_PRINT_PO_NUMBER' ) . ": " . $db->f( "order_id" ) , 
			"order_id" => $db->f( "order_id" ) , 
			"invoice" => $db->f( "order_number" ) , 
			"amount" => round( $db->f( "order_subtotal" ) + $tax_total - $discount_total, 2 ) , 
			"shipping" => sprintf( "%.2f", $db->f( "order_shipping" ) ) , 
			"currency_code" => $_SESSION['vendor_currency'] , 
			"address_override" => "1" , 
			"first_name" => $dbbt->f( 'first_name' ) , 
			"last_name" => $dbbt->f( 'last_name' ) , 
			"address1" => $dbbt->f( 'address_1' ) , 
			"address2" => $dbbt->f( 'address_2' ) , 
			"zip" => $dbbt->f( 'zip' ) , 
			"city" => $dbbt->f( 'city' ) , 
			"state" => $dbbt->f( 'state' ) , 
			"country" => $db1->f( 'country_2_code' ) , 
			"email" => $dbbt->f( 'email' ) , 
			"night_phone_b" => $dbbt->f( 'phone_1' ) , 
			"cpp_header_image" => $vendor_image_url , 

			"return" => SECUREURL . "index.php?option=com_virtuemart&page=checkout.result&order_id=" . $db->f( "order_id" ) , 
			"notify_url" => SECUREURL . "administrator/components/com_virtuemart/notify.php" , 
			"cancel_return" => SECUREURL . "index.php" , 
			"undefined_quantity" => "0" , 

			"test_ipn" => $this->params->get('DEBUG') , 
			"pal" => "NRUBJXESJTY24" , 
			"no_shipping" => "1" , 
			"no_note" => "1" ) ;
		if( $page == "checkout.thankyou" ) {
			$query_string = "?" ;
			foreach( $post_variables as $name => $value ) {
				$query_string .= $name . "=" . urlencode( $value ) . "&" ;
			}
			vmRedirect( $url . $query_string ) ;
		} else {
			echo '<form action="' . $url . '" method="post" target="_blank">' ;
			echo '<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/x-click-but6.gif" border="0" alt="Click to pay with PayPal - it is fast, free and secure!" />' ;
			
			foreach( $post_variables as $name => $value ) {
				echo '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars( $value ) . '" />' ;
			}
			echo '</form>' ;
		
		}
	
	}

}
?>