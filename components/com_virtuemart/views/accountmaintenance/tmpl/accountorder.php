<?php
/**
*
* Account shipping template
*
* @package	VirtueMart
* @subpackage AccountMaintenance
* @author RolandD
* @todo Create HTTPS links
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: accountbilling.php 2243 2010-01-23 02:52:23Z SimonHodgkiss $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<div class="buttons_heading">
	<?php echo shopFunctionsF::PrintIcon(); ?>
	</div>
	<br /><br />
<?php if ($this->perm->isRegisteredCustomer()) { ?>
	<table width="100%" align="center" border="0" cellspacing="0" cellpadding="2">
	  <tr>
	    <td valign="top">
	     <h2><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></h2>
	     <p><?php echo Vendor::formatted_store_address(1) ?></p>
	    </td>
	    <td valign="top" width="10%" align="right"><?php echo JHTML::_('image', $this->vendor->vendor_full_image, $this->vendor->vendor_name); ?></td>
	  </tr>
	</table>
	<?php
	if ($this->order->order_status == "P" ) {
		// Copy the db object to prevent it gets altered
	 	// Start printing out HTML Form code (Payment Extra Info
	 	/** @todo Show payment plugin */
	 	?>
	<table width="100%">
	  <tr>
	    <td width="100%" align="center">
	    <?php 
	    
	    // vmPaymentMethod::importPaymentPluginById($dbpm->f('id'));
	    
		// $vm_mainframe->triggerEvent('showPaymentForm', array($db, $user, $dbbt) );
	    	
	      ?>
	    </td>
	  </tr>
	</table>
	<?php
	}
	// END printing out HTML Form code (Payment Extra Info)
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="100%">
	  <!-- begin customer information --> 
	  <tr class="sectiontableheader"> 
	    <th align="left" colspan="2"><?php echo JText::_('VM_ACC_ORDER_INFO') ?></th>
	  </tr>
	  <tr> 
	    <td><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER')?>:</td>
	    <td><?php printf("%08d", $this->order->order_id); ?></td>
	  </tr>
	
	  <tr> 
		<td><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</td>
		<?php /** @todo do we need a time_offset here? */ ?> 
	    <td><?php echo shopFunctions::formatDate($this->order->cdate); ?></td>
	  </tr>
	  <tr> 
	    <td><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</td>
	    <td><?php echo $this->order->order_status_name; ?></td>
	  </tr>
	  <!-- End Customer Information --> 
	  <!-- Begin 2 column bill-ship to --> 
	  <tr class="sectiontableheader"> 
	    <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_CUST_INFO_LBL') ?></th>
	  </tr>
	  <tr valign="top"> 
	    <td width="50%"> <!-- Begin BillTo -->
	      <table width="100%" cellspacing="0" cellpadding="2" border="0">
	        <tr> 
	          <td colspan="2"><strong><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></strong></td>
	        </tr>
	        <?php 
	        foreach( $registrationfields as $field ) {
				if( $field->type == 'captcha') continue;
				if( $field->name == 'email') $field->name = 'email';
			?>
			<tr> 
			<td align="right"><?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbbt->f($field->name));
	          			if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
	          		case 'state':
	          			$state = $dbbt->f($field->name);
	          			if(isset($state) && $state!='-'){
	          				require_once(CLASSPATH.'ps_state.php');
							$state = new ps_state();
							$dbc = $state->get_state_by_code($dbbt->f($field->name));
							if( $dbc !== false ) echo $dbc->f('state_name');
	          			}
    	  				break;
		          	default: 
		          		echo $dbbt->f($field->name);
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
	      </table>
	      <!-- End BillTo --> </td>
	    <td width="50%"> <!-- Begin ShipTo --> <?php
	    // Get ship_to information
	    //$dbbt->next_record();
	
	  ?> 
	 <table width="100%" cellspacing="0" cellpadding="2" border="0">
	        <tr> 
	          <td colspan="2"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></strong></td>
	        </tr>
	        <?php 
		foreach( $shippingfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			?>
		  <tr> 
			<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td width="65%"><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbst->f($field->name));
		          		if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
	          		case 'state':
						$state = $dbbt->f($field->name);
	          			if(isset($state) && $state!='-'){	
	          				require_once(CLASSPATH.'ps_state.php');
							$state = new ps_state();
							$dbc = $state->get_state_by_code($dbbt->f($field->name));
							if( $dbc !== false ) echo $dbc->f('state_name');
	          			}
            	 		break;
		          	default: 
		          		echo $dbst->f($field->name);
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
	      </table>
	      <!-- End ShipTo --> 
	      <!-- End Customer Information --> 
	    </td>
	  </tr>
	  <tr> 
	    <td colspan="2">&nbsp;</td>
	  </tr>
	  <?php if ( $PSHOP_SHIPPING_MODULES[0] != "no_shipping" && $this->order->ship_method_id) { ?> 
	  <tr> 
	    <td colspan="2"> 
	      <table width="100%" border="0" cellspacing="0" cellpadding="1">
	        
	        <tr class="sectiontableheader"> 
	          <th align="left"><?php echo JText::_('VM_ORDER_PRINT_CUST_SHIPPING_LBL') ?></th>
	        </tr>
	        <tr> 
	          <td> 
	            <table width="100%" border="0" cellspacing="0" cellpadding="0">
	              <tr> 
	                <td><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?></strong></td>
	                <td><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_MODE_LBL') ?></strong></td>
	                <td><strong><?php echo JText::_('VM_ORDER_PRINT_PRICE') ?>&nbsp;</strong></td>
	              </tr>
	              <tr> 
	                <td><?php 
	                $details = explode( "|", $this->order->ship_method_id);
	                echo $details[1];
	                    ?>&nbsp;
	                </td>
	                <td><?php 
	                echo $details[2];
	                    ?>
	                </td>
	                <td><?php 
	                     echo $this->currencyDisplay->getFullValue($details[3], '', $this->order->order_currency);
	                    ?>
	                </td>
	              </tr>
	            </table>
	          </td>
	        </tr>
	        
	      </table>
	    </td>
	  </tr><?php
	  }
	
	  ?> 
	  <tr>
	    <td colspan="2">&nbsp;</td>
	  </tr>
	  <!-- Begin Order Items Information --> 
	  <tr class="sectiontableheader"> 
	    <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_ITEM') ?></th>
	  </tr>
	  <tr>
	    <td colspan="4">
	<?php
	/* Check if the order has been paid for */
	if ($this->order->order_status == VmConfig::get('enable_download_status') && VmConfig::get('enable_downloads')) {
	
		$q = "SELECT `download_id` FROM #__{vm}_product_download WHERE";
		$q .= " order_id =" .(int)$vars["order_id"];
		$dbdl->query($q);
	
		// $q = "SELECT * FROM #__{vm}_product_download WHERE order_id ='" . $db->f("order_id")
		// $dbbt->query($q);
	
	
		// check if download records exist for this purchase order
		if ($dbdl->next_record()) {
			echo "<b>" . JText::_('VM_DOWNLOADS_CLICK') . "</b><br /><br />";
	
			echo(JText::_('VM_DOWNLOADS_SEND_MSG_3').DOWNLOAD_MAX.". <br />");
	
			$expire = ((DOWNLOAD_EXPIRE / 60) / 60) / 24;
			echo(str_replace("{expire}", $expire, JText::_('VM_DOWNLOADS_SEND_MSG_4')));
			
			echo "<br /><br />";
		}
		//else {
			//echo "<b>" . JText::_('VM_DOWNLOADS_EXPIRED') . "</b><br /><br />";
		//}
	}
	?>
	    </td>
	  </tr>
	  <!-- END HACK EUGENE -->
	  <tr> 
	    <td colspan="2"> 
	      <table width="100%" cellspacing="0" cellpadding="2" border="0">
	        <tr align="left"> 
	          <th><?php echo JText::_('VM_ORDER_PRINT_QTY') ?></th>
	          <th><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
	          <th><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
	          <th><?php echo JText::_('VM_ORDER_PRINT_PRICE') ?></th>
	          <th align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?>&nbsp;&nbsp;&nbsp;</th>
	        </tr>
	        <?php 
	        $subtotal = 0;
	        foreach ($this->order_items as $order_item) {
	        	/* BEGIN HACK EUGENE */
	        	/*HACK SCOTT had to retest order status else unpaid were able to download*/
	        	if ($this->order->order_status == VmConfig::get('enable_download_status') && VmConfig::get('enable_downloads')) {
	        		/* search for download record that corresponds to this order item */
	        		$q = "SELECT `download_id` FROM #__{vm}_product_download WHERE";
	        		$q .= " `order_id`=" . intval($vars["order_id"]);
	        		$q .= " AND `product_id`=". intval($dbcart->f("product_id"));
	        		$dbdl->query($q);
	
	        	}
	        	/* END HACK EUGENE */
	
	        	// $product_id = null;
	        	// $dbi->query( "SELECT product_id FROM #__{vm}_product WHERE product_sku='".$dbcart->f("order_item_sku")."'");
	        	// $dbi->next_record();
	        	// $product_id = $dbi->f("product_id" );
	?> 
	        <tr align="left"> 
	          <td><?php $order_item->product_quantity; ?></td>
	          <td><?php 
	              if (0) {
	        			// hyperlink the downloadable order item	
	        			$url = $mosConfig_live_site."/index.php?option=com_virtuemart&page=shop.downloads";
	        			echo '<a href="'."$url&download_id=".$dbdl->f("download_id").'">'
	        					. '<img src="'.VM_THEMEURL.'images/download.png" alt="'.JText::_('VM_DOWNLOADS_CLICK').'" align="left" border="0" />&nbsp;'
	        					. $dbcart->f("order_item_name")
	        					. '</a>';
					}
	        		else {
			        	if( !empty( $product_id )) {
			          		echo '<a href="'.$sess->url( $mm_action_url."index.php?page=shop.product_details&product_id=$product_id") .'" title="'.$order_item->order_item_name.'">';
			          	}
			          	echo $order_item->order_item_name;
			          	echo " <div style=\"font-size:smaller;\">" . $order_item->product_attribute . "</div>";
			          	if( !empty( $product_id )) {
			          		echo "</a>";
			          	}
	        		}
			?>
	          </td>
	          <td><?php $order_item->order_item_sku; ?></td>
	          <td><?php /*
			$price = $ps_product->get_price($dbcart->f("product_id"));
			$item_price = $price["product_price"]; */
			if ($this->perm->showPriceIncludingTax()) $item_price = $order_item->product_final_price;
			else $item_price = $order_item->product_item_price;
			
			echo $this->currencyDisplay->getFullValue($item_price, '', $this->order->order_currency);
	
	           ?></td>
	          <td align="right"><?php $total = $order_item->product_quantity * $item_price; 
	          $subtotal += $total;
	          echo $this->currencyDisplay->getFullValue($total, '', $this->order->order_currency);
	           ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr><?php
	        }
	?> 
	        <tr> 
	          <td colspan="4" align="right">&nbsp;&nbsp;</td>
	          <td>&nbsp;</td>
	        </tr>
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?> :</td>
	          <td align="right"><?php echo $this->currencyDisplay->getFullValue($subtotal, '', $this->order->order_currency) ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	<?php 
	/* COUPON DISCOUNT */
	$coupon_discount = $this->order->coupon_discount;
	$order_discount = $this->order->order_discount;
	
	if (VmConfig::get('payment_discount_before') == '1') {
		if (($this->order->order_discount != 0)) {
	?>
	          <tr>
	              <td colspan="4" align="right"><?php 
	              if ($this->order->order_discount > 0)
	              echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
	              else
	              echo JText::_('VM_FEE');
	                ?>:
	              </td> 
	              <td align="right"><?php
	              if ($this->order->order_discount > 0 ) {
	              	echo "- ".$this->currencyDisplay->getFullValue(abs($db->f("order_discount")), '', $this->order->order_currency);
	              }
	              elseif ($this->order->order_discount < 0 )  {
	              	echo "+ ".$this->currencyDisplay->getFullValue(abs($db->f("order_discount")), '', $this->order->order_currency);
	              } 
	              ?>
	              &nbsp;&nbsp;&nbsp;</td>
	          </tr>
	        
	        <?php 
		}
		if( $coupon_discount > 0 ) {
	?>
	        <tr>
	          <td colspan="4" align="right"><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:
	          </td> 
	          <td align="right"><?php
	            echo "- ".$this->currencyDisplay->getFullValue( $coupon_discount, '', $this->order->order_currency ); ?>&nbsp;&nbsp;&nbsp;
	          </td>
	        </tr>
	<?php
		}
	}
	?>        
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?> :</td>
	          <td align="right"><?php 
	          $shipping_total = $this->order->order_shipping;
	          if ($this->perm->showPriceIncludingTax() == 1)
	          $shipping_total += $this->order->order_shipping_tax;
	          echo $this->currencyDisplay->getFullValue($shipping_total, '', $this->order->order_currency);
	
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	  <?php
	  $tax_total = $this->order->order_tax + $this->order->order_shipping_tax;
	  if (!$this->perm->showPriceIncludingTax()) { ?>
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?> :</td>
	          <td align="right"><?php 
	
	          echo $this->currencyDisplay->getFullValue($tax_total, '', $this->order->order_currency);
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	<?php
	  }
	  if (VmConfig::get('payment_discount_before') != '1') {
	  	if (($this->order->order_discount != 0)) {
	?>
	          <tr>
	              <td colspan="4" align="right"><?php 
	              if ($this->order->order_discount > 0)
	              echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
	              else
	              echo JText::_('VM_FEE');
	                ?>:
	              </td> 
	              <td align="right"><?php
	              if ($this->order->order_discount > 0 )
	              echo "- ".$this->currencyDisplay->getFullValue(abs($this->order->order_discount), '', $this->order->order_currency);
	              elseif ($this->order->order_discount < 0 )
	                 echo "+ ".$this->currencyDisplay->getFullValue(abs($this->order->order_discount), '', $this->order->order_currency); ?>
	              &nbsp;&nbsp;&nbsp;</td>
	          </tr>
	        
	        <?php 
	  	}
	  	if( $coupon_discount > 0 ) {
	?>
	        <tr>
	          <td colspan="4" align="right"><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:
	          </td> 
	          <td align="right"><?php
	            echo "- ".$this->currencyDisplay->getFullValue( $coupon_discount, '', $this->order->order_currency); ?>&nbsp;&nbsp;&nbsp;
	          </td>
	        </tr>
	<?php
	  	}
	  }
	?>      <tr> 
	          <td colspan="3" align="right">&nbsp;</td>
	          <td colspan="2" align="right"><hr/></td>
	        </tr>
	        <tr> 
	          <td colspan="4" align="right">
	          <strong><?php echo JText::_('VM_CART_TOTAL') .": "; ?></strong>
	          </td>
	          
	          <td align="right"><strong><?php  
	          $total = $this->order->order_total;
	          echo $this->currencyDisplay->getFullValue($total, '', $this->order->order_currency);
	          ?></strong>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	  <?php
	  if ($this->perm->showPriceIncludingTax()) {
	  ?>
	        
	        <tr> 
	          <td colspan="3" align="right">&nbsp;</td>
	          <td colspan="2" align="right"><hr/></td>
	        </tr>
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?> :</td>
	          <td align="right"><?php 
	
	          echo $this->currencyDisplay->getFullValue($tax_total, '', $this->order->order_currency);
			  
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	<?php
	  }
	  ?>    <tr> 
	          <td colspan="3" align="right">&nbsp;</td>
	          <td colspan="2" align="right"><hr/></td>
	        </tr>
	        <tr> 
	          <td colspan="3" align="right">&nbsp;</td>
	          <td colspan="2" align="right"><?php 
					//echo ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	      </table>
	    </td>
	  </tr>
	 </table>
	  <!-- End Order Items Information --> 
	
	<br />
	
	  <!-- Begin Payment Information --> 
	
	      <table width="100%">
	      <tr class="sectiontableheader"> 
	        <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PAYINFO_LBL') ?></th>
	      </tr>
	      <tr> 
	        <td width="20%"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LBL') ?> :</td>
	        <td><?php echo $this->order_payment->name; ?> </td>
	      </tr>
		  <?php if (!empty($this->order_payment->account_number)) { ?>
	      <tr> 
	        <td width="10%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NAME') ?> :</td>
	        <td><?php $this->order_payment->order_payment_name; ?> </td>
	      </tr>
	      <tr> 
	        <td><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER') ?> :</td>
	        <td> <?php echo shopFunctions::asteriskPad($order_payment->account_number,4);
	    ?> </td>
	      </tr>
	      <tr> 
	        <td><?php echo JText::_('VM_ORDER_PRINT_EXPIRE_DATE') ?> :</td>
	        <td><?php echo strftime("%m - %Y", $this->order_payment->order_payment_expire); ?> </td>
	      </tr>
		  <?php } ?>
	      <!-- end payment information --> 
	      </table>
	
	<?php // }
	
	    /** Print out the customer note **/
	    if ($this->order->customer_note) {
	    ?>
	    <table width="100%">
	      <tr>
	        <td colspan="2">&nbsp;</td>
	      </tr>
	      <tr class="sectiontableheader">
	        <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
	      </tr>
	      <tr>
	        <td colspan="2">
	         <?php echo nl2br($this->order->customer_note)."<br />"; ?>
	       </td>
	      </tr>
	    </table>
	    <?php
	    }
}
else {
	echo '<h4>'._LOGIN_TEXT .'</h4><br/>';
	include(PAGEPATH.'checkout.login_form.php');
	echo '<br/><br/>';
}
?>