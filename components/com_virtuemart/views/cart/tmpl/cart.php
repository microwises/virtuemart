<?php
/**
*
* Template for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @todo create the totalsales value in the cart
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

echo '<h2>'. JText::_('VM_CART_TITLE') .'</h2>';

/* Show Continue Shopping link when the cart is empty */ 
if ($this->cart["idx"] == 0) {
	echo JText::_('VM_EMPTY_CART');
	echo '<br />';
	echo JHTML::link($this->continue_link, JText::_('VM_CONTINUE_SHOPPING'), array('class' => 'continue_link'));
}
else {
	
	?><pre><?php
	print_r($this->cart);
	?></pre><?php
	
	if (0) {
		
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
	
		for ($i=0;$i<$this->cart["idx"];$i++) {
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
			$category_id = vmGet( $cart[$i], 'category_id', 0 );
			// Build URL based on whether item or product
			if ($product_parent_id) {
				$url = $sess->url(URL . basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=$product_parent_id&category_id=$category_id");
			}
			else {
				$url = $sess->url(URL . basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=" . $_SESSION['cart'][$i]["product_id"]."&category_id=$category_id");
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
	
			// UPDATE CART / DELETE FROM CART
			$action_url = $mm_action_url.basename($_SERVER['PHP_SELF']);
			$product_rows[$i]['update_form'] = '<form action="'. $action_url .'" method="post" style="display: inline;">
			<input type="hidden" name="option" value="com_virtuemart" />
			<input type="text" title="'. JText::_('VM_CART_UPDATE') .'" class="inputbox" size="4" maxlength="4" name="quantity" value="'.$cart[$i]["quantity"].'" />
			<input type="hidden" name="page" value="'. $page .'" />
		<input type="hidden" name="func" value="cartUpdate" />
		<input type="hidden" name="product_id" value="'. $_SESSION['cart'][$i]["product_id"] .'" />
		<input type="hidden" name="prod_id" value="'. $_SESSION['cart'][$i]["product_id"] .'" />
		<input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
		<input type="hidden" name="description" value="'. stripslashes($cart[$i]["description"]).'" />
		<input type="image" name="update" title="'. JText::_('VM_CART_UPDATE') .'" src="'. VM_THEMEURL .'images/update_quantity_cart.png" alt="'. JText::_('VM_UPDATE') .'" align="middle" />
	  </form>';
			$product_rows[$i]['delete_form'] = '<form action="'.$action_url.'" method="post" name="delete" style="display: inline;">
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="page" value="'. $page .'" />
		<input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
		<input type="hidden" name="func" value="cartDelete" />
		<input type="hidden" name="product_id" value="'. $_SESSION['cart'][$i]["product_id"] .'" />
		<input type="hidden" name="description" value="'. $cart[$i]["description"].'" />
		<input type="image" name="delete" title="'. JText::_('VM_CART_DELETE') .'" src="'. VM_THEMEURL .'images/remove_from_cart.png" alt="'. JText::_('VM_CART_DELETE') .'" align="middle" />
	  </form>';
		} // End of for loop through the Cart
	
		vmRequest::setVar( 'zone_qty', $vars['zone_qty'] );
	
		$total = $total_undiscounted = round($total, 5);
		$vars["total"] = $total;
		$subtotal_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($total);
	
		if (!empty($_POST["do_coupon"]) || (in_array( strtolower($func), array( 'cartadd', 'cartupdate', 'cartdelete' )) && !empty($_SESSION['coupon_redeemed'])) ) {
			/* process the coupon */
			require_once( CLASSPATH . "ps_coupon.php" );
			$vars["total"] = $total;
			ps_coupon::process_coupon_code( $vars );
	
		}
	
		/* HANDLE SHIPPING COSTS */
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
				$shipping_tax = round($shipping_total- ($shipping_total / (1+$shipping_taxrate)), 5);
			}
			else {
				$shipping_tax = round($shipping_total * $shipping_taxrate, 5);
			}
	
			$shipping_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($shipping_total);
		}
		else {
			$shipping_total = $shipping_taxrate = 0;
			$shipping_display = "";
		}
	
		//CT.COUPON VALIDITY CHECK ON ORDER
		if (
			(isset($_SESSION['coupon_redeemed'])) &&
			($_SESSION['coupon_redeemed'] == 1) &&
			($total+$shipping_total < @$_SESSION['coupon_value_valid'])){
	
			echo JText::_('VM_COUPON_REMOVED').$_SESSION['coupon_value_valid'];
	
			@$_SESSION['coupon_redeemed'] = 0;
			@$_SESSION['coupon_id'] = 0;
			@$_SESSION['coupon_code'] = "";
			@$_SESSION['coupon_type'] = "";
			@$_SESSION['coupon_value_valid'] = 0;
			@$_SESSION['coupon_discount'] = 0;
	
		}
	
		// COUPON DISCOUNT
		$coupon_display = '';
		if( PSHOP_COUPONS_ENABLE=='1' && @$_SESSION['coupon_redeemed']=="1" && PAYMENT_DISCOUNT_BEFORE=='1') {
	
			$total -= $_SESSION['coupon_discount'];
			$coupon_display = "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $_SESSION['coupon_discount'] );
			$discount_before=true;
		}
	
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
	
		/* COUPON DISCOUNT */
		if( PSHOP_COUPONS_ENABLE=='1' && @$_SESSION['coupon_redeemed']=="1" && PAYMENT_DISCOUNT_BEFORE != '1') {
			$discount_after=true;
			$total -= $_SESSION['coupon_discount'];
			$coupon_display = "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $_SESSION['coupon_discount'] );
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
		if( !defined( '_MIN_POV_REACHED' )) {
			if(!empty($_SESSION['minimum_pov'])){
				$minPov = $_SESSION['minimum_pov'];
			}else{
				$minPov = 1;
			}
			if (round($minPov, 2) > 0.00) {
				if ($total_undiscounted >= $GLOBALS['CURRENCY']->convert( $minPov )) {
					// OKAY!
					define ('_MIN_POV_REACHED', '1');
				}
			} else {
				define ('_MIN_POV_REACHED', '1');
			}
		}
	
		$order_total_display = $GLOBALS['CURRENCY_DISPLAY']->getFullValue($order_total);
	
		$tpl = new $GLOBALS['VM_THEMECLASS']();
		$tpl->set_vars( Array(
									'product_rows' => $product_rows,
									'subtotal_display' => $subtotal_display,
									'discount_before' => $discount_before,
									'discount_after' => $discount_after,
									'coupon_display' => $coupon_display,
									'shipping' => $shipping,
									'shipping_display' => $shipping_display,
									'show_tax' => $show_tax,
									'tax_display' => $tax_display,
									'order_total_display' => $order_total_display,
					));
		$basket_html = '';
		if( $show_basket ) {
	
			if( $auth["show_price_including_tax"] == 1) {
				$basket_html = $tpl->fetch( 'basket/basket_b2c.html.php');
			}
			else {
				$basket_html = $tpl->fetch( 'basket/basket_b2b.html.php');
			}
		}
		/* Input Field for the Coupon Code */
		if( PSHOP_COUPONS_ENABLE=='1'
			&& !@$_SESSION['coupon_redeemed']
			//&& ($page == "shop.cart" )
		) {
			$basket_html .= $tpl->fetch( 'common/couponField.tpl.php' );
		}
	}
		
		?>
		<div align="center">
			<?php
			if ($this->continue_link != '') echo JHTML::link($this->continue_link, JText::_('VM_CONTINUE_SHOPPING'), array('class' => 'continue_link'));
			
			if (VmStore::get('vendor_min_pov', 0) < $this->cart->totalsales) {
				/** @todo currency format totalsales */
				?>
				<span style="font-weight:bold;"><?php echo JText::_('VM_CHECKOUT_ERR_MIN_POV2'). " ".$this->cart->totalsales; ?></span>
				<?php
			}
			else {
				$href = JRoute::_('index.php?option=com_virtuemart&view=checkout');
				$href2 = JRoute::_('index2.php?option=com_virtuemart&view=checkout');
				$class_att = array('class' => 'checkout_link');
				$text = JText::_('VM_CHECKOUT_TITLE');
				
				/** @todo build the greybox checkout */
				//if ($this->get_cfg('useGreyBoxOnCheckout', 1)) echo vmCommonHTML::getGreyBoxPopupLink( $href2, $text, '', $text, $class_att, 500, 600, $href );
				echo JHTML::link($href, $text, $class_att);
			} ?>
		</div>
<?php } ?>