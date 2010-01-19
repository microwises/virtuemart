<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This is the read-only version of the BASKET handler.
* It displays all products and all totals. Product quantities can't be
* changed on this page.
* 
* @version $Id$
* @package VirtueMart
* @subpackage html
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
mm_showMyFileName( __FILE__ );

require_once(CLASSPATH. 'ps_product.php' );
$ps_product = new ps_product;
require_once(CLASSPATH. 'ps_checkout.php' );
$ps_checkout = new ps_checkout;
require_once(CLASSPATH . 'shippingMethod.class.php' );

global $weight_total, $total, $tax_total, $order_tax_details, $discount_factor, $order_total;

/* make sure this is the checkout screen */
if ($cart["idx"] == 0) {
	echo JText::_('VM_EMPTY_CART');
	$checkout = False;
}
else {
	$checkout = True;
	$payment_method_id = JRequest::getVar(  "payment_method_id" );
	$total = 0;
	// Added for the zone shipping module
	$vars["zone_qty"] = 0;
	$weight_total = 0;
	$weight_subtotal = 0;
	$tax_total = 0;
	$shipping_total = $shipping_tax = 0;
	$order_total = 0;
	$coupon_discount = vmGet( $_SESSION, 'coupon_discount', 0 );
	$coupon_discount_before=$coupon_discount_after=$payment_discount_before=$payment_discount_after=$tax=$shipping=false;
	$product_rows = Array();

	for ($i=0;$i<$cart["idx"];$i++) {
		// Added for the zone shipping module
		$vars["zone_qty"] += $cart[$i]["quantity"];

		if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
		else $product_rows[$i]['row_color'] = "sectiontableentry1";

		// Get product parent id if exists
		$product_parent_id=$ps_product->get_field($cart[$i]["product_id"],"product_parent_id");

		// Get flypage for this product
		$flypage = $ps_product->get_flypage($cart[$i]["product_id"]);

		// Build URL based on whether item or product
		if ($product_parent_id) {
			$url = $sess->url(URL .basename($_SERVER['PHP_SELF']). "?page=shop.product_details&flypage=$flypage&product_id=$product_parent_id");
		}
		else {
			$url = $sess->url(URL . basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=" . $_SESSION['cart'][$i]["product_id"]);
		}

		$product_rows[$i]['product_name'] = "<a href=\"$url\"><strong>"
		. shopMakeHtmlSafe($ps_product->get_field($_SESSION['cart'][$i]["product_id"], "product_name"))
		. "</strong></a><br />"
		. $ps_product->getDescriptionWithTax( $_SESSION['cart'][$i]["description"], $_SESSION['cart'][$i]["product_id"] );

		// Display attribute values if this an item
		$product_rows[$i]['product_attributes'] = "";
		if ($product_parent_id) {
			$db_detail=$ps_product->attribute_sql($cart[$i]["product_id"],$product_parent_id);
			while ($db_detail->next_record()) {
				$product_rows[$i]['product_attributes'] .= "<br />" . $db_detail->f("attribute_name") . "&nbsp;";
				$product_rows[$i]['product_attributes'] .= "(" . $db_detail->f("attribute_value") . ")";
			}
		}
		$product_rows[$i]['product_sku'] = $ps_product->get_field($cart[$i]["product_id"], "product_sku");

		/* Product PRICE */
		$my_taxrate = $ps_product->get_product_taxrate($cart[$i]["product_id"], $weight_subtotal);
		$tax = $my_taxrate * 100;

		$price = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
		$price['product_price'] = $GLOBALS['CURRENCY']->convert( $price['product_price'], $price['product_currency'] );
		if( $auth["show_price_including_tax"] == 1 ) {
			$product_price = $price["product_price"] * ($my_taxrate+1);
		} else {
			$product_price = $price["product_price"];
		}
		$product_price = round( $product_price, 5 );
		$product_rows[$i]['product_price'] = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($product_price);

		/* Quantity Box */
		$product_rows[$i]['quantity'] = $cart[$i]["quantity"];

		/* WEIGHT CALCULATION */
		$weight_subtotal = vmShippingMethod::get_weight($cart[$i]["product_id"]) * $cart[$i]['quantity'];
		$weight_total += $weight_subtotal;

		/* SUBTOTAL CALCULATION */
		$subtotal = $product_price * $cart[$i]["quantity"];

		$total += $subtotal;
		$product_rows[$i]['subtotal'] = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($subtotal);
		if (!empty($my_taxrate) && MULTIPLE_TAXRATES_ENABLE=='1') {
			if( $auth["show_price_including_tax"] == 1 ) {
				eval( "\$message = \"".JText::_('VM_INCLUDING_TAX')."\";" );
				$product_rows[$i]['subtotal'] .= "&nbsp;".$message;
			}
			else {
				$product_rows[$i]['subtotal'] .= "&nbsp;(+ $tax% ".JText::_('VM_CART_TAX').")";
			}
		}

		// UPDATE CART / DELETE FROM CART 
		$action_url = $_SERVER['SERVER_PORT']=="443" ? SECUREURL : URL . basename($_SERVER['PHP_SELF']);
		$product_rows[$i]['update_form'] = "<input type=\"hidden\" name=\"page\" value=\"". $page ."\" />
        <input type=\"hidden\" name=\"func\" value=\"cartUpdate\" />
        <input type=\"hidden\" name=\"product_id\" value=\"". $_SESSION['cart'][$i]["product_id"] ."\" />
        <input type=\"hidden\" name=\"Itemid\" value=\"". $sess->getShopItemid() ."\" />
        <input type=\"hidden\" name=\"description\" value=\"". $cart[$i]["description"]."\" />
        <input type=\"image\" name=\"update\" title=\"". JText::_('VM_CART_UPDATE') ."\" src=\"". IMAGEURL ."ps_image/edit_f2.gif\" value=\"". JText::_('VM_UPDATE') ."\" />
      </form>";
		$product_rows[$i]['delete_form'] = "<form action=\"$action_url\" method=\"post\" name=\"delete\" />
        <input type=\"hidden\" name=\"option\" value=\"com_virtuemart\" />
        <input type=\"hidden\" name=\"page\" value=\"". $page ."\" />
        <input type=\"hidden\" name=\"Itemid\" value=\"". $sess->getShopItemid() ."\" />
        <input type=\"hidden\" name=\"func\" value=\"cartDelete\" />
        <input type=\"hidden\" name=\"product_id\" value=\"". $_SESSION['cart'][$i]["product_id"] ."\" />
        <input type=\"hidden\" name=\"description\" value=\"". $cart[$i]["description"]."\" />
      <input type=\"image\" name=\"delete\" title=\"". JText::_('VM_CART_DELETE') ."\" src=\"". IMAGEURL ."ps_image/delete_f2.gif\" value=\"". JText::_('VM_CART_DELETE') ."\" />
      </form>";
	} // End of for loop through the Cart

	$total = $total_undiscounted = round($total, 5);
	$subtotal_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($total);


	if ($_REQUEST["page"] == "checkout.index" && !empty($_POST["do_coupon"])) {
		/* process the coupon */

		/* make sure they arent trying to run it twice */
		if (@$_SESSION['coupon_redeemed'] == true) {
			$vmLogger->warning( JText::_('VM_COUPON_ALREADY_REDEEMED',false) );
		}
		else {
			require_once( CLASSPATH . "ps_coupon.php" );
			$vars["total"] = $total;
			ps_coupon::process_coupon_code( $vars );
		}
	}

	/* DISCOUNT */
	$discount_word = '';
	$payment_discount = $ps_checkout->get_payment_discount($payment_method_id, $total);
	if ( PAYMENT_DISCOUNT_BEFORE == '1') {
		if( $payment_discount != 0.00 ) {
			$payment_discount_before = true;
			if( $payment_discount > 0.00 ) {
				$discount_word = JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
			}
			else {
				$discount_word = JText::_('VM_FEE');
			}

			$total -= $payment_discount;
			$payment_discount_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($payment_discount-($payment_discount*2));
		}
		/* COUPON DISCOUNT */
		if( PSHOP_COUPONS_ENABLE=='1' && @$_SESSION['coupon_redeemed']==true ) {
			$total -= $_SESSION['coupon_discount'];
			$coupon_discount_before = true;
			$coupon_display = "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $_SESSION['coupon_discount'] );
		}
	}
	/* SHOW SHIPPING COSTS */
	if( !empty($shipping_rate_id) && !ps_checkout::noShippingMethodNecessary() ) {
		$shipping = true;
		$vars["weight"] = $weight_total;
		$result = $vm_mainframe->triggerEvent('get_shipping_rate', array( $vars ));		
		$shipping_total = is_array($result) ? round($result[0],5) : 0.00;
		
		$result = $vm_mainframe->triggerEvent('get_shippingtax_rate');
		$shipping_taxrate = is_array($result) ? $result[0] : 0.00;

		// When the Shipping rate is shown including Tax
		// we have to extract the Tax from the Shipping Total
		if( $auth["show_price_including_tax"] == 1 ) {
			$shipping_tax = round( $shipping_total- ($shipping_total / (1+$shipping_taxrate)), 5);
		}
		else {
			$shipping_tax = round( $shipping_total * $shipping_taxrate, 5);
		}
		$shipping_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($shipping_total);
	}
	else {
		$shipping_total = 0;
		$shipping_display = "";
	}
	/* SHOW TAX */
	if (!empty($_REQUEST['ship_to_info_id']) || $auth["show_price_including_tax"] == 1) {
		$tax = true;

		if( ($weight_total != 0 || TAX_VIRTUAL=='1') && $total > 0 ) {
			$order_taxable = $ps_checkout->calc_order_taxable($vars);
			$vars['payment_discount'] = $payment_discount;
			$tax_total = $ps_checkout->calc_order_tax($order_taxable, $vars);
		} else {
			$tax_total = 0;
		}
		if( $auth['show_price_including_tax']) {
			$tax_total *= $discount_factor;
		}
		$tax_total += $shipping_tax;
		$tax_total = round( $tax_total, 5 );
		$tax_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($tax_total);

		$tax_display .= ps_checkout::show_tax_details( $order_tax_details );
	}

	if ( PAYMENT_DISCOUNT_BEFORE != '1') {
		if( $payment_discount != 0.00 ) {
			$payment_discount_after = true;
			if( $payment_discount > 0.00 ) {
				$discount_word = JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
			}
			else {
				$discount_word = JText::_('VM_FEE');
			}
			$total -= $payment_discount;
			$payment_discount_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($payment_discount-($payment_discount*2));
		}
		// COUPON DISCOUNT
		$coupon_display = '';
		if( PSHOP_COUPONS_ENABLE=='1' && @$_SESSION['coupon_redeemed']==true ) {
			$total -= $_SESSION['coupon_discount'];
			$coupon_discount_after = true;
			$coupon_display = "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $_SESSION['coupon_discount'] );
		}
	}

	// Attention: When show_price_including_tax is 1,
	// we already have an order_total including the Tax!
	if( $auth["show_price_including_tax"] == 0 ) {
		$order_total += $tax_total;
		$total_undiscounted += $tax_total;
	}
	$order_total += $shipping_total + $total;
	$total_undiscounted += $shipping_total;

	/* check if the minimum purchase order value has already been reached */
	if (round($_SESSION['minimum_pov'], 2) > 0.00) {
		if ($total_undiscounted >= $GLOBALS['CURRENCY']->convert( $_SESSION['minimum_pov'] ) ) {
			// OKAY!
			define ('_MIN_POV_REACHED', '1');
		}
	} else
	define ('_MIN_POV_REACHED', '1');

	$order_total_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($order_total);
	
	$tpl = new $GLOBALS['VM_THEMECLASS']();
	$tpl->set_vars( Array(
								'product_rows' => $product_rows,
								'subtotal_display' => $subtotal_display,
								'discount_word' => $discount_word,
								'payment_discount_before' => $payment_discount_before,
								'payment_discount_after' => $payment_discount_after,
								'payment_discount_display' => $payment_discount_display,
								'coupon_discount_before' => $coupon_discount_before,
								'coupon_discount_after' => $coupon_discount_after,
								'coupon_display' => $coupon_display,
								'shipping' => $shipping,
								'shipping_display' => $shipping_display,
								'tax' => $tax,
								'tax_display' => $tax_display,
								'order_total_display' => $order_total_display,
				));
				
	if( $auth["show_price_including_tax"] == 1) {
		$basket_html = $tpl->fetch( 'basket/ro_basket_b2c.html.php');
	}
	else {
		$basket_html = $tpl->fetch( 'basket/ro_basket_b2b.html.php');
	}
	
}
?>