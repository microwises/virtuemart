<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file is the CART handler. It calculates the totals and
* uses the basket templates to show the listing to the user
* 
* This version of the basket allows to change quantities and delete products from the cart
* The ro_basket (=read only) doesn't allow that.
* 
* @version $Id: basket.php 774 2007-03-16 12:09:11 +0000 (Fri, 16 Mar 2007) soeren_nb $
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

global $weight_total, $total, $tax_total, $order_tax_details, $discount_factor;

/* make sure this is the checkout screen */
if ($cart["idx"] == 0) {
	$basket_html = JText::_('VM_EMPTY_CART');
	$checkout = False;
}
else {
	$checkout = True;

	$total = 0;
	// Added for the zone shipping module
	$vars["zone_qty"] = 0;
	$weight_total = 0;
	$weight_subtotal = 0;
	$tax_total = 0;
	$shipping_total = 0;
	$shipping_tax = 0;
	$order_total = 0;
	$discount_before=$discount_after=$show_tax=$shipping=false;
	$product_rows = Array();

	for ($i=0;$i<$cart["idx"];$i++) {
		// Added for the zone shipping module
		$vars["zone_qty"] += $cart[$i]["quantity"];

		if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
		else $product_rows[$i]['row_color'] = "sectiontableentry1";

		// Get product parent id if exists
		$product_parent_id=$ps_product->get_field($cart[$i]["product_id"],"product_parent_id");

		// Get flypage for this product
        $flypage_id = $product_parent_id;
        if($flypage_id == 0) {
            $flypage_id = $cart[$i]["product_id"];
        }
		$flypage = $ps_product->get_flypage($flypage_id);
        $category_id=$cart[$i]["category_id"];
		// Build URL based on whether item or product
		if ($product_parent_id) {
			$url = $sess->url(URL . basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=$product_parent_id&category_id=$category_id");
		}
		else {
			$url = $sess->url(URL . basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=" . $cart[$i]["product_id"]."&category_id=$category_id");
		}

		$product_rows[$i]['product_name'] = "<a href=\"$url\"><strong>"
		. shopMakeHtmlSafe($ps_product->get_field($cart[$i]["product_id"], "product_name"))
		. "</strong></a><br />"
		. $ps_product->getDescriptionWithTax( $cart[$i]["description"], $cart[$i]["product_id"] );

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

		/* WEIGHT CALCULATION */
		$weight_subtotal = vmShippingMethod::get_weight($cart[$i]["product_id"]) * $cart[$i]['quantity'];
		$weight_total += $weight_subtotal;

		/* Product PRICE */
		$my_taxrate = $ps_product->get_product_taxrate($cart[$i]["product_id"], $weight_subtotal);
		$tax = $my_taxrate * 100;

		$price = $ps_product->get_adjusted_attribute_price($cart[$i]["product_id"], $cart[$i]["description"]);
		$price["product_price"] = $GLOBALS['CURRENCY']->convert( $price["product_price"], $price["product_currency"] );
		
		if( $auth["show_price_including_tax"] == 1 ) {
			$product_price = $price["product_price"] * ($my_taxrate+1);
		} else {
			$product_price = $price["product_price"];
		}

		$product_price = round( $product_price, 2 );
		$product_rows[$i]['product_price'] = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($product_price);

		/* SUBTOTAL CALCULATION */
		$subtotal = $product_price * $cart[$i]["quantity"];

		$total += $subtotal;
		$product_rows[$i]['subtotal'] = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($subtotal);
		$product_rows[$i]['subtotal_with_tax'] = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($subtotal * ($my_taxrate+1));

		if (!empty($my_taxrate) && MULTIPLE_TAXRATES_ENABLE=='1') {

			if( $auth["show_price_including_tax"] == 1 ) {
				eval( "\$message = \"".JText::_('VM_INCLUDING_TAX')."\";" );
				$product_rows[$i]['subtotal'] .= "&nbsp;".$message;
			}
			else {
				$product_rows[$i]['subtotal'] .= "&nbsp;(+ $tax% ".JText::_('VM_CART_TAX').")";
			}
		}

		// UPDATE SAVED CART / DELETE FROM SAVED CART
		$action_url = $mm_action_url.basename($_SERVER['PHP_SELF']);
		$product_rows[$i]['update_form'] = '<form action="'. $action_url .'" method="post" style="display: inline;">
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="text" title="'. JText::_('VM_CART_UPDATE') .'" class="inputbox" size="4" maxlength="4" name="quantity" value="'.$cart[$i]["quantity"].'" />
		<input type="hidden" name="page" value="'. $page .'" />
    <input type="hidden" name="func" value="savedCartUpdate" />
    <input type="hidden" name="product_id" value="'. $cart[$i]["product_id"] .'" />
    <input type="hidden" name="prod_id" value="'. $cart[$i]["product_id"] .'" />
    <input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
    <input type="hidden" name="description" value="'. stripslashes($cart[$i]["description"]).'" />
    <input type="image" name="update" title="'. JText::_('VM_CART_UPDATE') .'" src="'. VM_THEMEURL .'images/update_quantity_cart.png" alt="'. JText::_('VM_UPDATE') .'" align="middle" />
  </form>';
		$product_rows[$i]['delete_form'] = '<form action="'.$action_url.'" method="post" name="delete" style="display: inline;">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="'. $page .'" />
    <input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
    <input type="hidden" name="func" value="savedCartDelete" />
    <input type="hidden" name="product_id" value="'. $cart[$i]["product_id"] .'" />
    <input type="hidden" name="description" value="'. $cart[$i]["description"].'" />
  	<input type="image" name="delete" title="'. JText::_('VM_CART_DELETE') .'" src="'. VM_THEMEURL .'images/remove_from_cart.png" alt="'. JText::_('VM_CART_DELETE') .'" align="middle" />
  </form>';
	} // End of for loop through the Cart

	$total = $total_undiscounted = round($total, 5);
	$vars["total"] = $total;
	$subtotal_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($total);

    	/* SHOW TAX */
	if (!empty($_REQUEST['ship_to_info_id']) || ps_checkout::tax_based_on_vendor_address() ) {

		$show_tax = true;

		if ($weight_total != 0 or TAX_VIRTUAL=='1') {
			$order_taxable = $ps_checkout->calc_order_taxable($vars);
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

	
	// Attention: When show_price_including_tax is 1,
	// we already have an order_total including the Tax!
	if( $auth["show_price_including_tax"] == 0 ) {
		$order_total += $tax_total;
		$total_undiscounted += $tax_total;
	}
	$order_total += $shipping_total + $total;
	$total_undiscounted += $shipping_total;


	$net_amount_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($order_total-$tax_total);
	$order_total_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($order_total);
	if( $show_basket ) {
		ob_start();
		if( $auth["show_price_including_tax"] == 1) {
			include (VM_THEMEPATH."templates/basket/basket_b2c.html.php");			
		}
		else {
			include (VM_THEMEPATH."templates/basket/basket_b2b.html.php");
		}
		$basket_html = ob_get_contents();
		ob_end_clean();
	}

}

?>