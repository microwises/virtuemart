<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: account.order_details.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
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
global $vm_mainframe;
 require_once(CLASSPATH.'paymentMethod.class.php');
 $vmPaymentMethod = new vmPaymentMethod();
 
if( $db->f('order_number')) {
?>	

	<?php if (empty( $print )) : ?>
	<div class="pathway"><?php echo $vmPathway; ?></div>
	<div class="buttons_heading">
	<?php echo vmCommonHTML::PrintIcon(); ?>
	</div>
	<br /><br />
	 <?php endif; ?>

	<table width="100%" align="center" border="0" cellspacing="0" cellpadding="2">
	  <tr>
	    <td valign="top">
	     <h2><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></h2>
	     <p><?php echo ps_vendor::formatted_store_address(true,$vendor_id) ?></p>
	    </td>
	    <td valign="top" width="10%" align="right"><?php echo $vendor_image; ?></td>
	  </tr>
	</table>
	<?php
	if ( $db->f("order_status") == "P" ) {
		// Copy the db object to prevent it gets altered
		$db_temp = ps_DB::_clone( $db );
	 	// Start printing out HTML Form code (Payment Extra Info
	 	?>
	<table width="100%">
	  <tr>
	    <td width="100%" align="center">
	    <?php 
	    
	    vmPaymentMethod::importPaymentPluginById($dbpm->f('id'));
	    
		$vm_mainframe->triggerEvent('showPaymentForm', array($db, $user, $dbbt) );
	    	
	      ?>
	    </td>
	  </tr>
	</table>
	<?php
		$db = $db_temp;
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
	    <td><?php printf("%08d", $db->f("order_id")); ?></td>
	  </tr>
	
	  <tr> 
		<td><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</td>
	    <td><?php echo vmFormatDate($db->f("cdate")+$time_offset); ?></td>
	  </tr>
	  <tr> 
	    <td><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</td>
	    <td><?php echo ps_order_status::getOrderStatusName( $db->f("order_status") ); ?></td>
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
	    $dbbt->next_record();
	    $dbst =& $dbbt;
	
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
	  <?php if ( $PSHOP_SHIPPING_MODULES[0] != "no_shipping" && $db->f("ship_method_id")) { ?> 
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
	                $details = explode( "|", $db->f("ship_method_id"));
	                echo $details[1];
	                    ?>&nbsp;
	                </td>
	                <td><?php 
	                echo $details[2];
	                    ?>
	                </td>
	                <td><?php 
	                     echo $CURRENCY_DISPLAY->getFullValue($details[3], '', $db->f('order_currency'));
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
	$dbdl = new ps_DB;
	/* Check if the order has been paid for */
	if ($db->f("order_status") == ENABLE_DOWNLOAD_STATUS && ENABLE_DOWNLOADS) {
	
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
	        $dbcart = new ps_DB;
	        $q  = "SELECT * FROM #__{vm}_order_item ";
	        $q .= "WHERE #__{vm}_order_item.order_id='$order_id' ";
	        $dbcart->query($q);
	        $subtotal = 0;
	        $dbi = new ps_DB;
	        while ($dbcart->next_record()) {
	
	        	/* BEGIN HACK EUGENE */
	        	/*HACK SCOTT had to retest order status else unpaid were able to download*/
	        	if ($db->f("order_status") == ENABLE_DOWNLOAD_STATUS && ENABLE_DOWNLOADS) {
	        		/* search for download record that corresponds to this order item */
	        		$q = "SELECT `download_id` FROM #__{vm}_product_download WHERE";
	        		$q .= " `order_id`=" . intval($vars["order_id"]);
	        		$q .= " AND `product_id`=". intval($dbcart->f("product_id"));
	        		$dbdl->query($q);
	
	        	}
	        	/* END HACK EUGENE */
	
	        	$product_id = null;
	        	$dbi->query( "SELECT product_id FROM #__{vm}_product WHERE product_sku='".$dbcart->f("order_item_sku")."'");
	        	$dbi->next_record();
	        	$product_id = $dbi->f("product_id" );
	?> 
	        <tr align="left"> 
	          <td><?php $dbcart->p("product_quantity"); ?></td>
	          <td><?php 
	              if ($dbdl->next_record()) {
	        			// hyperlink the downloadable order item	
	        			$url = $mosConfig_live_site."/index.php?option=com_virtuemart&page=shop.downloads";
	        			echo '<a href="'."$url&download_id=".$dbdl->f("download_id").'">'
	        					. '<img src="'.VM_THEMEURL.'images/download.png" alt="'.JText::_('VM_DOWNLOADS_CLICK').'" align="left" border="0" />&nbsp;'
	        					. $dbcart->f("order_item_name")
	        					. '</a>';
					}
	        		else {
			        	if( !empty( $product_id )) {
			          		echo '<a href="'.$sess->url( $mm_action_url."index.php?page=shop.product_details&product_id=$product_id") .'" title="'.$dbcart->f("order_item_name").'">';
			          	}
			          	$dbcart->p("order_item_name");
			          	echo " <div style=\"font-size:smaller;\">" . $dbcart->f("product_attribute") . "</div>";
			          	if( !empty( $product_id )) {
			          		echo "</a>";
			          	}
	        		}
			?>
	          </td>
	          <td><?php $dbcart->p("order_item_sku"); ?></td>
	          <td><?php /*
			$price = $ps_product->get_price($dbcart->f("product_id"));
			$item_price = $price["product_price"]; */
			if( $auth["show_price_including_tax"] ){
				$item_price = $dbcart->f("product_final_price");
			}
			else {
				$item_price = $dbcart->f("product_item_price");
			}
			echo $CURRENCY_DISPLAY->getFullValue($item_price, '', $db->f('order_currency'));
	
	           ?></td>
	          <td align="right"><?php $total = $dbcart->f("product_quantity") * $item_price; 
	          $subtotal += $total;
	          echo $CURRENCY_DISPLAY->getFullValue($total, '', $db->f('order_currency'));
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
	          <td align="right"><?php echo $CURRENCY_DISPLAY->getFullValue($subtotal, '', $db->f('order_currency')) ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	<?php 
	/* COUPON DISCOUNT */
	$coupon_discount = $db->f("coupon_discount");
	$order_discount = $db->f("order_discount");
	
	if( PAYMENT_DISCOUNT_BEFORE == '1') {
		if (($db->f("order_discount") != 0)) {
	?>
	          <tr>
	              <td colspan="4" align="right"><?php 
	              if( $db->f("order_discount") > 0)
	              echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
	              else
	              echo JText::_('VM_FEE');
	                ?>:
	              </td> 
	              <td align="right"><?php
	              if ($db->f("order_discount") > 0 ) {
	              	echo "- ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
	              }
	              elseif ($db->f("order_discount") < 0 )  {
	              	echo "+ ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
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
	            echo "- ".$CURRENCY_DISPLAY->getFullValue( $coupon_discount, '', $db->f('order_currency') ); ?>&nbsp;&nbsp;&nbsp;
	          </td>
	        </tr>
	<?php
		}
	}
	?>        
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?> :</td>
	          <td align="right"><?php 
	          $shipping_total = $db->f("order_shipping");
	          if ($auth["show_price_including_tax"] == 1)
	          $shipping_total += $db->f("order_shipping_tax");
	          echo $CURRENCY_DISPLAY->getFullValue($shipping_total, '', $db->f('order_currency'));
	
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	  <?php
	  $tax_total = $db->f("order_tax") + $db->f("order_shipping_tax");
	  if ($auth["show_price_including_tax"] == 0) {
	  ?>
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?> :</td>
	          <td align="right"><?php 
	
	          echo $CURRENCY_DISPLAY->getFullValue($tax_total, '', $db->f('order_currency'));
	            ?>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	<?php
	  }
	  if( PAYMENT_DISCOUNT_BEFORE != '1') {
	  	if (($db->f("order_discount") != 0)) {
	?>
	          <tr>
	              <td colspan="4" align="right"><?php 
	              if( $db->f("order_discount") > 0)
	              echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
	              else
	              echo JText::_('VM_FEE');
	                ?>:
	              </td> 
	              <td align="right"><?php
	              if ($db->f("order_discount") > 0 )
	              echo "- ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
	              elseif ($db->f("order_discount") < 0 )
	                 echo "+ ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency')); ?>
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
	            echo "- ".$CURRENCY_DISPLAY->getFullValue( $coupon_discount, '', $db->f('order_currency') ); ?>&nbsp;&nbsp;&nbsp;
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
	          $total = $db->f("order_total");
	          echo $CURRENCY_DISPLAY->getFullValue($total, '', $db->f('order_currency'));
	          ?></strong>&nbsp;&nbsp;&nbsp;</td>
	        </tr>
	  <?php
	  if ($auth["show_price_including_tax"] == 1) {
	  ?>
	        
	        <tr> 
	          <td colspan="3" align="right">&nbsp;</td>
	          <td colspan="2" align="right"><hr/></td>
	        </tr>
	        <tr> 
	          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?> :</td>
	          <td align="right"><?php 
	
	          echo $CURRENCY_DISPLAY->getFullValue($tax_total, '', $db->f('order_currency'));
			  
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
					echo ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
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
	        <td><?php $dbpm->p("name"); ?> </td>
	      </tr>
		  <?php
		  $payment = $dbpm->f("id");
	
		  if ($vmPaymentMethod->is_creditcard($payment)) {
	
		  	// DECODE Account Number
		  	$dbaccount = new ps_DB;
		  	$q = 'SELECT '.VM_DECRYPT_FUNCTION.'(order_payment_number,\''.ENCODE_KEY.'\') as account_number 
		  				FROM #__{vm}_order_payment WHERE order_id=\''.$order_id.'\'';
		  	$dbaccount->query($q);
	        $dbaccount->next_record();
	         ?>
	      <tr> 
	        <td width="10%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NAME') ?> :</td>
	        <td><?php $dbpm->p("order_payment_name"); ?> </td>
	      </tr>
	      <tr> 
	        <td><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER') ?> :</td>
	        <td> <?php echo ps_checkout::asterisk_pad($dbaccount->f("account_number"),4);
	    ?> </td>
	      </tr>
	      <tr> 
	        <td><?php echo JText::_('VM_ORDER_PRINT_EXPIRE_DATE') ?> :</td>
	        <td><?php echo strftime("%m - %Y", $dbpm->f("order_payment_expire")); ?> </td>
	      </tr>
		  <?php } ?>
	      <!-- end payment information --> 
	      </table>
	
	<?php // }
	
	    /** Print out the customer note **/
	    if ( $db->f("customer_note") ) {
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
	         <?php echo nl2br($db->f("customer_note"))."<br />"; ?>
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