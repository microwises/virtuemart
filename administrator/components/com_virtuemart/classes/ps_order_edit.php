<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_order_edit.php 1760 2009-05-03 22:58:57Z Aravot $
* @author nfischer
* @copyright Copyright (C) 2006 Ingemar F�llman. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*/


/****************************************************************************
*
* CLASS DESCRIPTION
*
* ps_order_edit
*
* The class  acts as a plugin for the order_print page.
# It adds a new tab for order edit handling.
*
*************************************************************************/
class ps_order_edit {
	var $classname = "ps_order_edit";
	var $error;
	var $order_id;
	var $reload_from_db;
	var $product_added = false;
    
	/**************************************************************************
	* name: ps_order_edit (constructor)
	* created by: ingemar
	* description: constructor, setup initial variables
	* parameters: Order Id
	* returns:
	**************************************************************************/
	function ps_order_edit($order_id) {
		$this->order_id = $order_id;
	}

	/**************************************************************************
	 * name: pane_content
	 * created by: ingemar
	 * description: Show pane content
	 * parameters: Tab Object
	 * returns:
	 **************************************************************************/
	function pane_content($tab) {
	
		

		if( JRequest::getVar( 'order_edit_page' ) == '1') {
			?>
			<script type="text/javascript">
			var current = document.getElementById( "order_edit_page" );
			current.tabPage.select();
			</script>
				<?php
		}
	
		if( JRequest::getVar( 'delete_product' ) != '' )
			$this->delete_product();
		elseif( JRequest::getVar( 'add_product') != '' )
			$this->add_product();
		elseif( JRequest::getVar( 'update_quantity' ) != '' )
			$this->update_quantity();
		elseif( JRequest::getVar( 'update_coupon_discount' ) != '' ) 
			$this->update_coupon_discount();
		elseif( JRequest::getVar( 'update_discount' ) != '' ) 
			$this->update_discount();
		elseif( JRequest::getVar( 'update_standard_shipping' ) != '' ) 
			$this->update_standard_shipping();
		elseif( JRequest::getVar( 'update_shipping' ) != '' ) 
			$this->update_shipping();
		elseif( JRequest::getVar( 'update_bill_to' ) != '' ) 
			$this->update_bill_to();
		elseif( JRequest::getVar( 'update_ship_to' ) != '' ) 
			$this->update_ship_to();
		elseif( JRequest::getVar( 'update_shipping_tax' ) != '' )
			 

		?>
		<form method="post" name="editForm" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		<table class="adminlist">
        <tr>
          <th><?php echo(JText::_('VM_ORDER_EDIT_EDIT_ORDER')); ?></th>
        </tr>
      </table>
      <table class="adminlist">
		    <tr>
      		<th><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
      		<th><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
      		<th width="5%" align="left"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></th>
      		<th width="5%" align="left" colspan="2"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th>
    		</tr>
    		<?php
    		$dbt = new ps_DB;
    		$db = new ps_DB;
    
    		$qt  = "SELECT order_item_id, product_quantity,order_item_name,order_item_sku FROM `#__{vm}_order_item`".
    			"WHERE #__{vm}_order_item.order_id='".$this->order_id."' ";
    		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
    		
    		$dbt->query($qt);
    		$db->query($q);
    
    		$db->next_record();
    		$i = 0;
    
    		$rate_details = explode( "|", $db->f("ship_method_id") );
    
    		while ($dbt->next_record()){
    			if ($i++ % 2) {
    				$bgcolor='row0';
    			} else {
    				$bgcolor='row1';
    			}
    			?>
      		<tr class="<?php echo $bgcolor; ?>" valign="top">
        		<td><?php $dbt->p("order_item_sku") ?>&nbsp;</td>
        		<td><?php $dbt->p("order_item_name") ?></td>
        		<td>
        		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            	  <input type="text" value="<?php $dbt->p("product_quantity") ?>" name="product_quantity" size="5" />
        		</td>
            	<td align="left">
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>" 
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_quantity" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_item_id" value="<?php $dbt->p("order_item_id") ?>" />  
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		 </form>
  		      </td>
            <td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="image" title="<?php echo JText::_('VM_DELETE') ?>" src="<?php echo IMAGEURL ?>ps_image/delete_f2.gif" border="0"  alt="<?php echo JText::_('VM_DELETE') ?>" />
            		<input type="hidden" value="1" name="delete_product" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_item_id" value="<?php $dbt->p("order_item_id") ?>" />  
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
          	</td>	
      		</tr>
  			<?php
    		}
    		?>
      </table>
      
      
      <table class="adminlist">
		  <tr>
      		<th><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') . " &amp; " . JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT') ?></th>
      		<th width="5%" align="left"> &nbsp; </th>
      		<th width="5%" align="left" colspan="1"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th>
    		</tr>
  			<?php
			if($db->f('ship_method_id') == "" OR preg_match('/^standard_shipping/', $db->f('ship_method_id'))) {
    		?>
      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>: &nbsp;</strong></td>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		
              		<?php
              		$dbs = new ps_DB;
              		$q = 'SELECT shipping_rate_id, shipping_rate_name, shipping_rate_weight_start, shipping_rate_weight_end, shipping_rate_value, shipping_rate_package_fee, tax_rate, currency_name 
              				FROM #__{vm}_shipping_rate, #__{vm}_currency, #__{vm}_tax_rate 
              				WHERE currency_id = shipping_rate_currency_id 
              					AND ( tax_rate_id = shipping_rate_vat_id OR shipping_rate_vat_id = 0 )
              					ORDER BY shipping_rate_list_order';
              		$dbs->query($q);
              		while ($dbs->next_record()){
              			$rates[$dbs->f('shipping_rate_id')] = $dbs->f('shipping_rate_name')
              					."; (".$dbs->f('shipping_rate_weight_start')." - ".$dbs->f('shipping_rate_weight_end')."); "
              					. " ".(($dbs->f('shipping_rate_value') * (1+$dbs->f('tax_rate'))) + $dbs->f('shipping_rate_package_fee'))
              					. " ".$dbs->f('currency_name');
              		}
              		ps_html::dropdown_display( 'shipping', $rate_details[4], $rates );
              		
              		?>
              	</select>
      		  </td>
            <td>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_standard_shipping" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
          	</td>
          </tr>
    		<?php 
    		} else {
    		?>

      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>: </strong></td>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("order_shipping") ?>" size="5" name="order_shipping" />
        		</td>
            <td>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_shipping" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
        		</td>     
      		</tr>
      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_TAX') ?>: </strong></td>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("order_shipping_tax") ?>" name="order_shipping_tax" size="5" />
        		</td>
            <td>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_shipping_tax" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
        		  </form>
            </td>    
      		</tr>
    		<?php
    		}
    		?>
      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>: </strong></td>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("coupon_discount") ?>" size="5" name="coupon_discount" />
        		</td>
            <td>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_coupon_discount" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
        		</td>     
      		</tr>

      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT') ?>: </strong></td>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("order_discount") ?>" size="5" name="order_discount" />
        		</td>
            <td>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="update_discount" />
            		<input type="hidden" name="order_edit_page" value="1" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
        		</td>
      		</tr>
      </table>
      
      <table class="adminlist">
		    <tr>
      		<th><?php echo JText::_('VM_USER_FORM_BILLTO_LBL') . " & " . JText::_('VM_USER_FORM_SHIPTO_LBL')  ?></th>
      		<th width="5%" align="left"> &nbsp; </th>
      		<th width="5%" align="left" colspan="1"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th>
    		</tr>
    		<tr>
      		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?>: </strong></td>
      		<td align="right">
        		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          		<select name="bill_to">
            		<?php
            		$dbs = new ps_DB;
                $q  = "SELECT user_id, last_name, first_name FROM #__{vm}_user_info WHERE address_type = 'BT' ORDER BY last_name ASC"; 
            		$dbs->query($q);
            		while ($dbs->next_record()){
            		  if (!is_null( $dbs->f('last_name') )) {
              			print '<option value="'.$dbs->f('user_id').'"';
              			if($dbs->f('user_id') == $db->f("user_id")) print " selected ";
            			  print '>';
              			print $dbs->f('last_name');
              			print ", ".$dbs->f('first_name');
              			print '</option>';
            			}
            		}
            		?>
          		</select>
      		</td>
          <td>
          		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
          		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
          		<input type="hidden" value="1" name="update_bill_to" />
          		<input type="hidden" name="order_edit_page" value="1" />
          		<input type="hidden" name="page" value="order.order_print" />
          		<input type="hidden" name="option" value="com_virtuemart" />
          		<input type="hidden" name="func" value="" />
          		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          	</form>
      		</td>
    		</tr>
    		
		    <?php /* Change ship to form */ ?>
    		<tr>
      		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?>: </strong></td>
      		<td align="right">
        		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          		<select name="ship_to">
            		<?php
            		$dbs = new ps_DB;
                $q  = "SELECT user_info_id, address_type_name FROM #__{vm}_user_info WHERE user_id = '" . $db->f("user_id") . "' ORDER BY address_type_name ASC"; 
            		$dbs->query($q);
            		while ($dbs->next_record()){
            		  if (!is_null( $dbs->f('user_info_id') )) {
              			print '<option value="'.$dbs->f('user_info_id').'">';
              			print $dbs->f('address_type_name');
              			print '</option>';
            			}
            		}
            		?>
          		</select>
      		</td>
          <td>
          		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
          		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
          		<input type="hidden" value="1" name="update_ship_to" />
          		<input type="hidden" name="order_edit_page" value="1" />
          		<input type="hidden" name="page" value="order.order_print" />
          		<input type="hidden" name="option" value="com_virtuemart" />
          		<input type="hidden" name="func" value="" />
          		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          	</form>
      		</td>
    		</tr>


      </table>
		
		<?php $this->display_form_add_product(); ?>
		
		<?php
	}
	
	
	
	/**************************************************************************
	 * name: update_ship_to
	 * created by: Kaltokri
	 * description: Change ship to
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_ship_to() {
		$ship_to = trim(JRequest::getVar( 'ship_to' ));
		$db = new ps_DB;
		
		// Delete ship to
		$q = "DELETE FROM #__{vm}_order_user_info ";
		$q .= "WHERE order_id = '" . $this->order_id . "' AND address_type = 'ST'";
		$db->query($q);
		$db->next_record();
		
    $q = "SELECT * FROM #__{vm}_user_info ";
    $q .= "WHERE user_info_id = '" . $ship_to . "'";
    $db->query($q);
		$db->next_record();

		if($db->f('address_type') == 'ST') {
  		
  		// Ship to Address if applicable (copied from ps_checkout.php and changed)
  		$q = "INSERT INTO `#__{vm}_order_user_info` ";
  		$q .= "SELECT '', '$this->order_id', '".$db->f('user_id')."', address_type, address_type_name, company, title, last_name, first_name, middle_name, phone_1, phone_2, fax, address_1, address_2, city, state, country, zip, email, extra_field_1, extra_field_2, extra_field_3, extra_field_4, extra_field_5,bank_account_nr,bank_name,bank_sort_code,bank_iban,bank_account_holder,bank_account_type FROM #__{vm}_user_info WHERE user_id='".$db->f('user_id')."' AND user_info_id='".$ship_to."' AND address_type='ST'";
  		$db->query($q);
  		$db->next_record();
		}

		$this->reload_from_db = 1;
	}

	/**************************************************************************
	 * name: update_bill_to
	 * created by: Kaltokri
	 * description: Change bill to
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_bill_to() {
		$db = new ps_DB;
		$db2 = new ps_DB;
		$bill_to = trim(JRequest::getVar( 'bill_to' ));

		$q = "SELECT * FROM #__{vm}_user_info WHERE user_id = '" . $bill_to . "'";
		$db->query($q);
		if(!$db->next_record()) {
			print "<h1>Invalid user id: $bill_to</h1>"; 
			return;
		}
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET user_id = '" .$bill_to."',";
	  $q .= " user_info_id = '" .$db->f('user_info_id')."'";
		$q .= " WHERE order_id = '" . $this->order_id . "'";
		$db2->query($q);
		$db2->next_record();
		
		// Update order_user_info
		$q = "UPDATE #__{vm}_order_user_info ";
		$q .= "SET user_id = '" .$db->f('user_id')."', ";
		$q .= "address_type_name = '" .$db->f('address_type_name')."', ";
		$q .= "company = '" .$db->f('company')."', ";
		$q .= "title = '" .$db->f('title')."', ";
		$q .= "last_name = '" .$db->f('last_name')."', ";
		$q .= "first_name = '" .$db->f('first_name')."', ";
		$q .= "middle_name = '" .$db->f('middle_name')."', ";
		$q .= "phone_1 = '" .$db->f('phone_1')."', ";
		$q .= "phone_2 = '" .$db->f('phone_2')."', ";
		$q .= "fax = '" .$db->f('fax')."', ";
		$q .= "address_1 = '" .$db->f('address_1')."', ";
		$q .= "address_2 = '" .$db->f('address_2')."', ";
		$q .= "city = '" .$db->f('city')."', ";
		$q .= "state = '" .$db->f('state')."', ";
		$q .= "country = '" .$db->f('country')."', ";
		$q .= "zip = '" .$db->f('zip')."', ";
		$q .= "email = '" .$db->f('email')."', ";
		$q .= "extra_field_1 = '" .$db->f('extra_field_1')."', ";
		$q .= "extra_field_2 = '" .$db->f('extra_field_2')."', ";
		$q .= "extra_field_3 = '" .$db->f('extra_field_3')."', ";
		$q .= "extra_field_4 = '" .$db->f('extra_field_4')."', ";
		$q .= "extra_field_5 = '" .$db->f('extra_field_5')."', ";
		$q .= "bank_account_nr = '" .$db->f('bank_account_nr')."', ";
		$q .= "bank_name = '" .$db->f('bank_name')."', ";
		$q .= "bank_sort_code = '" .$db->f('bank_sort_code')."', ";
		$q .= "bank_iban = '" .$db->f('bank_iban')."', ";
		$q .= "bank_account_holder = '" .$db->f('bank_account_holder')."', ";
		$q .= "bank_account_type = '" .$db->f('bank_account_type')."' ";
		$q .= " WHERE order_id = '" . $this->order_id . "' AND address_type = 'BT'";
		$db2->query($q);
		$db2->next_record();
		
		// Delete ship to
		$q = "DELETE FROM #__{vm}_order_user_info ";
		$q .= "WHERE order_id = '" . $this->order_id . "' AND address_type = 'ST'";
		$db2->query($q);
		$db2->next_record();

		$this->reload_from_db = 1;
	}

	/**************************************************************************
	 * name: update_shipping
	 * created by: ingemar
	 * description: Change order shipping rate
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_shipping() {
		$db = new ps_DB;
		$shipping = trim(JRequest::getVar( 'order_shipping' ));
		if(!is_numeric($shipping)) {
			$shipping = 0;
		} 

		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_total = order_total - order_shipping +".$shipping.", ";
		$q .= "order_shipping =  ".$shipping;
		$q .= " WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;
	}

	/**************************************************************************
	 * name: update_shipping_tax
	 * created by: ingemar
	 * description: Change order shipping tax
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_shipping_tax() {
		$db = new ps_DB;
		$shipping_tax = trim(JRequest::getVar( 'order_shipping_tax' ));
		if(!is_numeric($shipping_tax)) {
			$shipping_tax = 0;
		} 
		
		// Update orde
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_total = order_total - order_shipping_tax +".$shipping_tax.", ";
		$q .= "order_shipping_tax =  ".$shipping_tax;
		$q .= " WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;
	}

	/**************************************************************************
	 * name: update_standard_shipping
	 * created by: ingemar
	 * description: Change order shipping rate
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_standard_shipping() {
		$db = new ps_DB;
		$shipping = trim(JRequest::getVar( 'shipping' ));
		$q = "SELECT shipping_rate_name, shipping_carrier_name, shipping_rate_value, ((tax_rate + 1) *shipping_rate_value) AS shipping_total FROM #__{vm}_shipping_rate, #__{vm}_tax_rate, #__{vm}_shipping_carrier WHERE shipping_carrier_id = shipping_rate_carrier_id AND tax_rate_id = shipping_rate_vat_id and shipping_rate_id = '".addslashes($shipping)."'";
		$db->query($q);
		if(!$db->next_record()) {
			print "<h1>Invalid shipping id: $shipping</h1>"; 
			return;
		}
		$shipping_carrier = $db->f('shipping_carrier_name');
		$shipping_name = $db->f('shipping_rate_name');
		$shipping_rate = $db->f('shipping_rate_value');
		$shipping_tax = $db->f('shipping_total') - $db->f('shipping_rate_value');
		$shipping_total = $db->f('shipping_total');
		$shipping_method = "standard_shipping|$shipping_carrier|$shipping_name|".round($shipping_total,2)."|$shipping";
		
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_total = order_total - order_shipping - order_shipping_tax + ".$shipping_rate." + ".$shipping_tax.", ";
		$q .= "order_shipping = ".$shipping_rate.", ";
		$q .= "order_shipping_tax =  ".$shipping_tax.", ";
		$q .= "ship_method_id = '".addslashes($shipping_method)."'";
		$q .= " WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;
	}

	/**************************************************************************
	 * name: update_coupon_discount
	 * created by: ingemar
	 * description: Change order coupon discount
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_coupon_discount() {
		$db = new ps_DB;
		$discount = trim(JRequest::getVar( 'coupon_discount' ));
		if(!is_numeric($discount)) {
			print "<h1>Invalid discount: $discount</h1>";
			return;
		}

		$q = "SELECT SUM(product_quantity*product_final_price) - SUM(product_quantity*product_item_price) AS item_tax, ".
		$q .= "SUM(product_quantity*product_final_price) as final_price ";
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_tax = (order_total - order_shipping - order_shipping_tax + coupon_discount - ".$discount." ) * (".$db->f('item_tax')." / ".$db->f('final_price')." ), ";
		$q .= "order_total = order_total + coupon_discount - ".$discount.", ";
		$q .= "coupon_discount =  '".$discount."' ";
		$q .= "WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;
	}


	/**************************************************************************
	 * name: update_discount
	 * created by: ingemar
	 * description: Change order discount
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_discount() {
		$db = new ps_DB;
		$discount = trim(JRequest::getVar( 'order_discount' ));
		if(!is_numeric($discount)) {
			print "<h1>Invalid discount: $discount</h1>";
			return;
		}

		$q = "SELECT 
				SUM(product_quantity*product_final_price) - SUM(product_quantity*product_item_price) AS item_tax, 
				SUM(product_quantity*product_final_price) as final_price 
				FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_tax = (order_total - order_shipping - order_shipping_tax + order_discount - ".$discount." ) * (".$db->f('item_tax')." / ".$db->f('final_price')." ), ";
		$q .= "order_total = order_total + order_discount - ".$discount.", ";
		$q .= "order_discount =  '".$discount."' ";
		$q .= "WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;
	}


	/**************************************************************************
	 * name: delete_product
	 * created by: nfischer
	 * description: Delete an item
	 * parameters:
	 * returns:
	 **************************************************************************/
	function delete_product() {
		global  $vmLogger;

		$order_item_id = JRequest::getVar( 'order_item_id' );
		$quantity = trim(JRequest::getVar( 'product_quantity' ));
		
		$db = new ps_DB;

		$q = "SELECT product_id, product_quantity, product_final_price, product_item_price, product_final_price - product_item_price AS item_tax ";
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "' ";
		$q .= "AND order_item_id = '".addslashes($order_item_id)."'";
		$db->query($q);
		$db->next_record();

		$product_id = $db->f('product_id');
		$diff = $quantity - $db->f('product_quantity');
		$net_price_change = $diff * $db->f('product_item_price');
		$tax_change = $diff * $db->f('item_tax');
		$price_change = $diff * $db->f('product_final_price');	
		$timestamp = time() + ($mosConfig_offset*60*60);
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_tax = (order_tax + ".$tax_change." ), ";
		$q .= "order_total = (order_total + ".$price_change." ), ";
		$q .= "order_subtotal = (order_subtotal + ".$net_price_change.") ";
		$q .= "WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;

		// On supprime le produit de la commande
		$q = "DELETE FROM #__{vm}_order_item ";
		$q .= "WHERE order_item_id = '".addslashes($order_item_id)."'";
		$db->query($q);
		$db->next_record();
		
		/* Update Stock Level and Product Sales */
		$q = "UPDATE #__{vm}_product ";
		$q .= "SET product_in_stock = product_in_stock - ".$diff;
		$q .= " WHERE product_id = '" . $product_id . "'";
		$db->query($q);
		$db->next_record();

		$q = "UPDATE #__{vm}_product ";
		$q .= "SET product_sales= product_sales + " .$diff;
		$q .= " WHERE product_id='". $product_id ."'";
		$db->query($q);
		$db->next_record();
		
		$vmLogger->info( JText::_('VM_ORDER_EDIT_PRODUCT_DELETED',false) );
	}

	/**************************************************************************
	 * name: update_quantity
	 * created by: ingemar
	 * description: Change order_item quantity
	 * parameters:
	 * returns:
	 **************************************************************************/
	function update_quantity() {
		global  $vmLogger, $mosConfig_offset;

		$order_item_id = JRequest::getVar( 'order_item_id' );
		$quantity = trim(JRequest::getVar( 'product_quantity' ));
		if(!is_numeric($quantity) || $quantity < 1) {
			$vmLogger->err( JText::_('VM_ORDER_EDIT_ERROR_QUANTITY_MUST_BE_HIGHER_THAN_0',false) );
			return;
		}
		
		$db = new ps_DB;

		$q = "SELECT product_id, product_quantity, product_final_price, product_item_price, product_final_price - product_item_price AS item_tax ";
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "' ";
		$q .= "AND order_item_id = '".addslashes($order_item_id)."'";
		$db->query($q);
		$db->next_record();

		$product_id = $db->f('product_id');
		$diff = $quantity - $db->f('product_quantity');
		$net_price_change = $diff * $db->f('product_item_price');
		$tax_change = $diff * $db->f('item_tax');
		$price_change = $diff * $db->f('product_final_price');	
		$timestamp = time() + ($mosConfig_offset*60*60);
		
		// Update order
		$q = "UPDATE #__{vm}_orders ";
		$q .= "SET order_tax = (order_tax + ".$tax_change." ), ";
		$q .= "order_total = (order_total + ".$price_change." ), ";
		$q .= "order_subtotal = (order_subtotal + ".$net_price_change.") ";
		$q .= "WHERE order_id = '" . $this->order_id . "'";
		$db->query($q);
		$db->next_record();
		$this->reload_from_db = 1;

		$q = "UPDATE #__{vm}_order_item ";
		$q .= "SET product_quantity = ".$quantity.", ";
		$q .= "mdate = ".$timestamp." ";
		$q .= "WHERE order_item_id = '".addslashes($order_item_id)."'";
		$db->query($q);
		$db->next_record();

		/* Update Stock Level and Product Sales */
		$q = "UPDATE #__{vm}_product ";
		$q .= "SET product_in_stock = product_in_stock - ".$diff;
		$q .= " WHERE product_id = '" . $product_id . "'";
		$db->query($q);
		$db->next_record();

		$q = "UPDATE #__{vm}_product ";
		$q .= "SET product_sales= product_sales + " .$diff;
		$q .= " WHERE product_id='". $product_id ."'";
		$db->query($q);
		$db->next_record();
		
		$vmLogger->info( JText::_('VM_ORDER_EDIT_QUANTITY_UPDATED',false) );
		
	}
	
	/**************************************************************************
	 * name: add_product
	 * created by: nfischer
	 * description: Add a new product to an existing order
	 * parameters:
	 * returns:
	 **************************************************************************/
	function add_product() {
		global  $vmLogger, $mosConfig_offset;
		
		require_once(CLASSPATH . 'ps_product_attribute.php');
		require_once(CLASSPATH . 'ps_product.php');
		
		$ps_product_attribute = new ps_product_attribute;
		$ps_product = new ps_product;
		
		$product_id = JRequest::getVar( 'product_id' );		
		$order_item_id = JRequest::getVar( 'order_item_id' );
		$add_product_validate = JRequest::getVar( 'add_product_validate' );
		$d = $_REQUEST;
		
		// On peux ins�rer le produit � la commande
		if ($add_product_validate == 1) {
			$quantity = trim(JRequest::getVar( 'product_quantity' ));
			if(!is_numeric($quantity) || $quantity < 1) {
				$vmLogger->err( JText::_('VM_ORDER_EDIT_ERROR_QUANTITY_MUST_BE_HIGHER_THAN_0',false) );
				$add_product_validate = 0;
			}
		}
        if(!isset($d['order_subtotal_withtax'] )) {
            $d['order_subtotal_withtax'] = 0;
        }
		if ($add_product_validate == 1) {
			$result_attributes = $ps_product_attribute->cartGetAttributes($d);
         
         $dbp = new ps_DB;
			$q = "SELECT vendor_id, product_in_stock,product_sales,product_parent_id, product_sku, product_name FROM #__{vm}_product WHERE product_id='$product_id'";
			$dbp->query($q);
			$dbp->next_record();
			$vendor_id = $dbp->f("vendor_id");
			$product_sku = $dbp->f("product_sku");
			$product_name = $dbp->f("product_name");
			$product_parent_id = $dbp->f("product_parent_id");

			// On r�cup�re le prix exact du produit
			$product_price_arr = $this->get_adjusted_attribute_price($product_id, $quantity , $d["description"], $result_attributes);
			$product_price = $product_price_arr["product_price"];
			$my_taxrate = $ps_product->get_product_taxrate($product_id);
			
			$description = $d["description"];
			$product_final_price = round( ($product_price *($my_taxrate+1)), 2 );
			$product_currency = $product_price_arr["product_currency"];
					
			$db = new ps_DB;
			
			$q = "SELECT * FROM #__{vm}_order_item ";
			$q .= " WHERE order_id=" . $this->order_id;
			$db->query($q);
			$db->next_record();
			$user_info_id = $db->f("user_info_id");
			$order_status = $db->f("order_status");
			
			$timestamp = time() + ($mosConfig_offset*60*60);
						
			$q = "INSERT INTO #__{vm}_order_item ";
			$q .= "(order_id, user_info_id, vendor_id, product_id, order_item_sku, order_item_name, ";
			$q .= "product_quantity, product_item_price, product_final_price, ";
			$q .= "order_item_currency, order_status, product_attribute, cdate, mdate) ";
			$q .= "VALUES ('";
			$q .= $this->order_id . "', '";
			$q .= $user_info_id . "', '";
			$q .= $vendor_id . "', '";
			$q .= $product_id . "', '";
			$q .= $product_sku . "', '";
			$q .= $product_name . "', '";
			$q .= $quantity . "', '";
			$q .= $product_price . "', '";
			$q .= $product_final_price . "', '";
			$q .= $product_currency . "', '";
			$q .= $order_status . "', '";
			// added for advanced attribute storage
			$q .= addslashes( $description ) . "', '";
			// END advanced attribute modifications
			$q .= $timestamp . "','";
			$q .= $timestamp . "'";
			$q .= ")";

			$db->query($q);
			$db->next_record();

			$q = "SELECT product_id, product_quantity, product_final_price, product_item_price, product_final_price - product_item_price AS item_tax ";
			$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "' ";
			$q .= "AND order_item_id = '".addslashes($order_item_id)."'";
			$db->query($q);
			$db->next_record();

			$net_price_change = $quantity * $product_price;
			$tax_change = $quantity * ($product_final_price - $product_price);
			$price_change = $quantity * $product_final_price;
			$order_subtotal = 0;		
		
			if( $_SESSION["auth"]["show_price_including_tax"] == 1 ) {
				$product_price = round( ($product_price *($my_taxrate+1)), 2 );
            $product_price *= $quantity;
            $d['order_subtotal_withtax'] += $product_price;
            $product_price = $product_price /($my_taxrate+1);
            $order_subtotal += $product_price;
         }
         else {
            $order_subtotal += $product_price * $quantity;

            $product_price = round( ($product_price *($my_taxrate+1)), 2 );
            $product_price *= $quantity;
            $d['order_subtotal_withtax'] += $product_price;
            $product_price = $product_price /($my_taxrate+1);
         }		
		
			// Update order
			$q = "UPDATE #__{vm}_orders ";
			$q .= "SET order_tax = (order_tax + ".$tax_change." ), ";
			$q .= "order_total = (order_total + ".$price_change." ), ";
			$q .= "order_subtotal = (order_subtotal + ".$net_price_change.") ";
			$q .= "WHERE order_id = '" . $this->order_id . "'";
			$db->query($q);
			$db->next_record();
			$this->reload_from_db = 1;

			// Update Stock Level and Product Sales
			$q = "UPDATE #__{vm}_product ";
			$q .= "SET product_in_stock = product_in_stock - ".$quantity;
			$q .= " WHERE product_id = '" . $product_id . "'";
			$db->query($q);
			$db->next_record();

			$q = "UPDATE #__{vm}_product ";
			$q .= "SET product_sales= product_sales + " .$quantity;
			$q .= " WHERE product_id='". $product_id ."'";
			$db->query($q);
			$db->next_record();
			
			$this->product_added = true;
			$vmLogger->info( JText::_('VM_ORDER_EDIT_PRODUCT_ADDED',false) );
		}
		
	}	
	
	/**************************************************************************
	 * name: display_form_add_product
	 * created by: nfischer
	 * description: Display the add_product form
	 * parameters:
	 * returns:
	 **************************************************************************/
	function display_form_add_product() {
		global  $vmLogger;
		
		require_once(CLASSPATH . 'ps_product_attribute.php');
		//require_once(CLASSPATH . 'ps_product.php');
		
		$ps_product_attribute = new ps_product_attribute;
		//$ps_product = new ps_product;
	
		$order_item_id = JRequest::getVar( 'order_item_id' );	

		// Affichage de l'en-t�te
		$html_entete = '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
		$html_entete .= '<br /><br /><table class="adminlist"><tr><th>' . JText::_('VM_ORDER_EDIT_ADD_PRODUCT') . '</th></tr></table>';
		$html_entete .= '<table class="adminlist"><tr>';
		$html_entete .= '<th>' . JText::_('VM_ORDER_PRINT_NAME') . '</th>';
		
		$html_pied = '<input type="hidden" name="add_product" value="1" />
		<input type="hidden" name="order_edit_page" value="1" />
		<input type="hidden" name="page" value="order.order_print" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="func" value="" />
		<input type="hidden" name="order_id" value="' . $this->order_id . '" /></form>';
		
		$html_return_parent = '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
		<input type="submit" value="' . JText::_('VM_ORDER_EDIT_RETURN_PARENTS') . '" />
		<input type="hidden" name="product_id" value="-1" />
		<input type="hidden" name="add_product_validate" value="0" />
		<input type="hidden" name="add_product_item" value="0" />
		<input type="hidden" name="add_product" value="1" />
		<input type="hidden" name="order_edit_page" value="1" />
		<input type="hidden" name="page" value="order.order_print" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="func" value="" />
		<input type="hidden" name="order_id" value="' . $this->order_id . '" /></form>';
		
		$product_id = JRequest::getVar( 'product_id' );
		if ($this->product_added ==true) {
			$product_id = -1;
		}
		$d = $_REQUEST;
		
		$add_product_validate = JRequest::getVar( 'add_product_validate' );

		if ($product_id < 0 || $product_id == "") {
			$html_table = '<tr><td>' . $this->list_products($product_id) . '</td>';
			$html_entete .= '</tr>';
			$html_table .= '</tr></table><input type="hidden" name="add_product_validate" value="0" />';
			echo $html_entete . $html_table . $html_pied;
			return;
		}
		
		$db = new ps_DB;
		$q = "SELECT product_id FROM #__{vm}_product WHERE ";
		$q .= "product_parent_id = '".$product_id."'";
		$db->query($q);
		$item = false;
		// Elements fils s�lectionn�s
		if (JRequest::getVar( 'add_product_item' ) == 1) {
			$item = true;
			$html_table = '<tr><td>' . $this->list_attribute($product_id, false) . '<input type="hidden" name="add_product_item" value="1" /></td>';
		}
		// S'il y a des �l�ments fils      
		else if ( $db->num_rows()) {
			$html_entete .= '</tr>';
			$html_table = '<tr><td>' . $this->list_attribute($product_id) . '<input type="hidden" name="add_product_validate" value="0" /><input type="hidden" name="add_product_item" value="1" /></td></tr></table>';
			echo $html_entete . $html_table . $html_pied . $html_return_parent;
			return;
		}
		else {
			$html_table = '<tr><td>' . $this->list_products($product_id) . '</td>';
		}
		
		$html_entete .= '<th>' . JText::_('VM_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST') . '</th>';
		$html_entete .= '<th>' . JText::_('VM_PRODUCT_FORM_ATTRIBUTE_LIST') . '</th>';
		$html_entete .= '<th align="left">' . JText::_('VM_ORDER_PRINT_QUANTITY') . '</th>';
		$html_entete .= '<th align="left">Action</th></tr>';
		$html_table .= '<td>' . $ps_product_attribute->list_advanced_attribute($product_id) . '</td>';
		$html_table .= '<td>' . $ps_product_attribute->list_custom_attribute($product_id) . '</td>';
		$html_table .= '<td><input type="text" value="1" name="product_quantity" size="5" /><input type="hidden" name="add_product_validate" value="1" /></td>';
		$html_table .= '<td><input type="submit" value="' . JText::_('VM_ORDER_EDIT_ADD') . '" /></td></tr></table>';
		
		if ($item) {
			$html_pied .= $html_return_parent;
		}
		
		echo $html_entete . $html_table . $html_pied;
		return;  
		
	}
	
    /**************************************************************************
	 * name: list_products
	 * created by: nfischer
	 * description: Create a list of products
	 * parameters: product_id
	 * returns: html to display
	 **************************************************************************/
	function list_products($product_id) {
		
		$db = new ps_DB;
		
		$query_list_products = "SELECT DISTINCT `product_name`,`products_per_row`,`category_browsepage`,`category_flypage`";
		$query_list_products .= ",`#__{vm}_product`.`product_id`,`#__{vm}_category`.`category_id`,`product_full_image`,`product_thumb_image`";
		$query_list_products .= ",`product_s_desc`,`product_parent_id`,`published`,`product_in_stock`,`product_sku`";
		$query_list_products .= " FROM (`#__{vm}_product`, `#__{vm}_category`, `#__{vm}_product_category_xref`";
		$query_list_products .= ",`#__{vm}_shopper_group`) LEFT JOIN `#__{vm}_product_price` ON";
		$query_list_products .= " `#__{vm}_product`.`product_id` = `#__{vm}_product_price`.`product_id`";
		$query_list_products .= " WHERE `#__{vm}_product_category_xref`.`category_id`=`#__{vm}_category`.`category_id`";
		$query_list_products .= " AND `#__{vm}_product`.`product_id`=`#__{vm}_product_category_xref`.`product_id`";
		$query_list_products .= " AND `#__{vm}_product`.`product_parent_id`='0'";
		$query_list_products .= " AND `published`='1'";
		$query_list_products .= " AND (( `#__{vm}_shopper_group`.`shopper_group_id`=`#__{vm}_product_price`.`shopper_group_id` )";
		$query_list_products .= " OR (`#__{vm}_product_price`.`product_id` IS NULL))";
		$query_list_products .= " GROUP BY `#__{vm}_product`.`product_sku` ORDER BY `#__{vm}_product`.`product_name`";

		$db->query($query_list_products);
		
		$display = '<select name="product_id" onChange="this.form.add_product_validate.value=0;this.form.submit();">';
		$display .= '<option value="-1">' . JText::_('VM_ORDER_EDIT_CHOOSE_PRODUCT') . '</option>';
		while ($db->next_record()) {
			$display .= '<option value="' . $db->f("product_id") . '"';
			if ($product_id == $db->f("product_id")) {
				$display .= ' selected="yes"';
			}
			$display .= '>' . $db->f("product_name") . '</option>';
		}
		$display .= '</select>';
		
		return $display;
	}
	
	
	/**************************************************************************
	 * name: get_price
	 * created by: nfischer
	 * description: Give the price of a product
	 * parameters: $product_id, $quantity ,$check_multiple_prices=false, $result_attributes
	 * returns: Price of the product
	 **************************************************************************/
	function get_price($product_id, $quantity ,$check_multiple_prices=false, $result_attributes) {
		if($check_multiple_prices) {
			$db = new ps_DB;

			// Get the vendor id for this product.
			$q = "SELECT vendor_id FROM #__{vm}_product WHERE product_id='$product_id'";
			$db->setQuery($q); $db->query();
			$db->next_record();
			$vendor_id = $db->f("vendor_id");

			$q = "SELECT svx.shopper_group_id, sg.shopper_group_discount FROM #__{vm}_shopper_vendor_xref svx, #__{vm}_orders o, #__{vm}_shopper_group sg";
			$q .= " WHERE svx.user_id=o.user_id AND sg.shopper_group_id=svx.shopper_group_id AND o.order_id=" . $this->order_id;
			$db->query($q);
			$db->next_record();
			$shopper_group_id = $db->f("shopper_group_id");
			$shopper_group_discount = $db->f("shopper_group_discount");

			// Get the default shopper group id for this vendor
			$q = "SELECT shopper_group_id,shopper_group_discount FROM #__{vm}_shopper_group WHERE ";
			$q .= "vendor_id='$vendor_id' AND `default`='1'";
			$db->setQuery($q); $db->query();
			$db->next_record();
			$default_shopper_group_id = $db->f("shopper_group_id");
			$default_shopper_group_discount = $db->f("shopper_group_discount");

			// Get the product_parent_id for this product/item
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'";
			$db->setQuery($q); $db->query();
			$db->next_record();
			$product_parent_id = $db->f("product_parent_id");

			$price_info = Array();
			if( !$check_multiple_prices ) {
				/* Added for Volume based prices */
				// This is an important decision: we add up all product quantities with the same product_id,
				// regardless to attributes. This gives "real" volume based discount, because our simple attributes
				// depend on one and the same product_id

				$volume_quantity_sql = " AND (('$quantity' >= price_quantity_start AND '$quantity' <= price_quantity_end)
                                OR (price_quantity_end='0') OR ('$quantity' > price_quantity_end)) ORDER BY price_quantity_end DESC";
				/* End Addition */
			}
			else {
				$volume_quantity_sql = " ORDER BY price_quantity_start";
			}

			// Getting prices
			//
			// If the shopper group has a price then show it, otherwise
			// show the default price.
			if( !empty($shopper_group_id) ) {
				$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_id' AND ";
				$q .= "shopper_group_id='$shopper_group_id' $volume_quantity_sql";

				$db->setQuery($q); $db->query();
				if ($db->next_record()) {
					$price_info["product_price"]= $db->f("product_price");
					if( $check_multiple_prices ) {
						$price_info["product_base_price"]= $db->f("product_price");
						$price_info["product_has_multiple_prices"] = $db->num_rows() > 1;
					}
					$price_info["product_price_id"]=$db->f("product_price_id");
					$price_info["product_currency"]=$db->f("product_currency");
					$price_info["item"]=true;
					$GLOBALS['product_info'][$product_id]['price'] = $price_info;
					return $GLOBALS['product_info'][$product_id]['price'];
				}
			}
			// Get default price
			$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_id' AND ";
			$q .= "shopper_group_id='$default_shopper_group_id' $volume_quantity_sql";

			$db->setQuery($q); $db->query();
			if ($db->next_record()) {
				$price_info["product_price"]=$db->f("product_price") * ((100 - $shopper_group_discount)/100);
				if( $check_multiple_prices ) {
					$price_info["product_base_price"]= $price_info["product_price"];
					$price_info["product_has_multiple_prices"] = $db->num_rows() > 1;
				}
				$price_info["product_price_id"]=$db->f("product_price_id");
				$price_info["product_currency"] = $db->f("product_currency");
				$price_info["item"] = true;
				$GLOBALS['product_info'][$product_id]['price'] = $price_info;
				return $GLOBALS['product_info'][$product_id]['price'];
			}

			// Maybe its an item with no price, check again with product_parent_id
			if( !empty($shopper_group_id) ) {
				$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_parent_id' AND ";
				$q .= "shopper_group_id='$shopper_group_id' $volume_quantity_sql";
				$db->setQuery($q); $db->query();
				if ($db->next_record()) {
					$price_info["product_price"]=$db->f("product_price");
					if( $check_multiple_prices ) {
						$price_info["product_base_price"]= $db->f("product_price");
						$price_info["product_has_multiple_prices"] = $db->num_rows() > 1;
					}
					$price_info["product_price_id"]=$db->f("product_price_id");
					$price_info["product_currency"] = $db->f("product_currency");
					$GLOBALS['product_info'][$product_id]['price'] = $price_info;
					return $GLOBALS['product_info'][$product_id]['price'];
				}
			}
			$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_parent_id' AND ";
			$q .= "shopper_group_id='$default_shopper_group_id' $volume_quantity_sql";
			$db->setQuery($q); $db->query();
			if ($db->next_record()) {
				$price_info["product_price"]=$db->f("product_price") * ((100 - $shopper_group_discount)/100);
				if( $check_multiple_prices ) {
					$price_info["product_base_price"]= $price_info["product_price"];
					$price_info["product_has_multiple_prices"] = $db->num_rows() > 1;
				}
				$price_info["product_price_id"]=$db->f("product_price_id");
				$price_info["product_currency"] = $db->f("product_currency");
				$GLOBALS['product_info'][$product_id]['price'] = $price_info;
				return $GLOBALS['product_info'][$product_id]['price'];
			}
			// No price found
			$GLOBALS['product_info'][$product_id]['price'] = false;
			return $GLOBALS['product_info'][$product_id]['price'];
		}
		else {
			return $GLOBALS['product_info'][$product_id]['price'];
		}
	}
	

	/**************************************************************************
	 * name: get_adjusted_attribute_price
	 * created by: nfischer
	 * description: Give the price of a product according to the attributes
	 * parameters: $product_id, $quantity ,$description='', $result_attributes
	 * returns: Price of the product
	 **************************************************************************/
	function get_adjusted_attribute_price ($product_id, $quantity ,$description='', $result_attributes) {

		global $mosConfig_secret;
		$auth = $_SESSION['auth'];
		$price = $this->get_price($product_id, $quantity, true, $result_attributes);
		$base_price = $price["product_price"];

		$setprice = 0;
		$set_price = false;
		$adjustment = 0;

		// We must care for custom attribute fields! Their value can be freely given
		// by the customer, so we mustn't include them into the price calculation
		// Thanks to AryGroup@ua.fm for the good advice
//***********************
//***********************
//***********************
//***********************
// A VOIR
//***********************
//***********************
//***********************
//***********************

		if( empty( $_REQUEST["custom_attribute_fields"] )) {
			if( !empty( $_SESSION["custom_attribute_fields"] )) {
				$custom_attribute_fields = vmGet( $_SESSION, "custom_attribute_fields", Array() );
				$custom_attribute_fields_check = vmGet( $_SESSION, "custom_attribute_fields_check", Array() );
			}
			else
			$custom_attribute_fields = $custom_attribute_fields_check = Array();
		}
		else {
			$custom_attribute_fields = $_SESSION["custom_attribute_fields"] = JRequest::getVar( "custom_attribute_fields", Array() );
			$custom_attribute_fields_check = $_SESSION["custom_attribute_fields_check"]= JRequest::getVar( "custom_attribute_fields_check", Array() );
		}
		
//***********************
//***********************
//***********************
//***********************
// A VOIR
//***********************
//***********************
//***********************
//***********************

		// if we've been given a description to deal with, get the adjusted price
		if ($description != '') { // description is safe to use at this point cause it's set to ''

		$attribute_keys = explode( ";", $description );

		foreach( $attribute_keys as $temp_desc ) {

			$temp_desc = trim( $temp_desc );
			// Get the key name (e.g. "Color" )
			$this_key = substr( $temp_desc, 0, strpos($temp_desc, ":") );

			if( in_array( $this_key, $custom_attribute_fields )) {
				if( @$custom_attribute_fields_check[$this_key] == md5( $mosConfig_secret.$this_key )) {
					// the passed value is valid, don't use it for calculating prices
					continue;
				}
			}

			$i = 0;

			$start = strpos($temp_desc, "[");
			$finish = strpos($temp_desc,"]", $start);

			$o = substr_count ($temp_desc, "[");
			$c = substr_count ($temp_desc, "]");
			//echo "open: $o<br>close: $c<br>\n";


			// check to see if we have a bracket
			if (True == is_int($finish) ) {
				$length = $finish-$start;

				// We found a pair of brackets (price modifier?)
				if ($length > 1) {
					$my_mod=substr($temp_desc, $start+1, $length-1);
					//echo "before: ".$my_mod."<br>\n";
					if ($o != $c) { // skip the tests if we don't have to process the string
						if ($o < $c ) {
							$char = "]";
							$offset = $start;
						}
						else {
							$char = "[";
							$offset = $finish;
						}
						$s = substr_count($my_mod, $char);
						for ($r=1;$r<$s;$r++) {
							$pos = strrpos($my_mod, $char);
							$my_mod = substr($my_mod, $pos+1);
						}
					}
					$oper=substr($my_mod,0,1);

					$my_mod=substr($my_mod,1);


					// if we have a number, allow the adjustment
					if (true == is_numeric($my_mod) ) {
						// Now add or sub the modifier on
						if ($oper=="+") {
							$adjustment += $my_mod;
						}
						else if ($oper=="-") {
							$adjustment -= $my_mod;
						}
						else if ($oper=='=') {
							// NOTE: the +=, so if we have 2 sets they get added
							// this could be moded to say, if we have a set_price, then
							// calc the diff from the base price and start from there if we encounter
							// another set price... just a thought.

							$setprice += $my_mod;
							$set_price = true;
						}
					}
					$temp_desc = substr($temp_desc, $finish+1);
					$start = strpos($temp_desc, "[");
					$finish = strpos($temp_desc,"]");
				}
			}
			$i++; // not necessary, but perhaps interesting? ;)
		}
		}

		// no set price was set from the attribs
		if ($set_price == false) {
			$price["product_price"] = $base_price + $adjustment;
		}
		else { 
			// otherwise, set the price
			// add the base price to the price set in the attributes
			// then subtract the adjustment amount
			// we could also just add the set_price to the adjustment... not sure on that one.
			// $setprice += $adjustment;
			$setprice *= 1 - ($auth["shopper_group_discount"]/100);
			$price["product_price"] = $setprice;
		}

		// don't let negative prices get by, set to 0
		if ($price["product_price"] < 0) {
			$price["product_price"] = 0;
		}
		// Get the DISCOUNT AMOUNT
		$ps_product = new ps_product;
		$discount_info = $ps_product->get_discount( $product_id );

		$my_taxrate = $ps_product->get_product_taxrate($product_id);

		if( !empty($discount_info["amount"])) {
			if( $auth["show_price_including_tax"] == 1 ) {
				switch( $discount_info["is_percent"] ) {
					case 0: $price["product_price"] = (($price["product_price"]*($my_taxrate+1))-$discount_info["amount"])/($my_taxrate+1); break;
					//case 1: $price["product_price"] = ($price["product_price"]*($my_taxrate+1) - $discount_info["amount"]/100*$price["product_price"])/($my_taxrate+1); break;
					case 1: $price["product_price"] = ($price["product_price"] - $discount_info["amount"]/100*$price["product_price"]); break;
				}
			}
			else {
				switch( $discount_info["is_percent"] ) {
					case 0: $price["product_price"] = (($price["product_price"])-$discount_info["amount"]); break;
					case 1: $price["product_price"] = ($price["product_price"] - ($discount_info["amount"]/100)*$price["product_price"]); break;
				}
			}
		}

		return $price;
	}
	

	 /**************************************************************************
	 * name: list_attribute
	 * created by: nfischer
	 * description: Lists all child/sister products of the given product
	 * parameters: $product_id, $fils
	 * returns: string HTML code with Items, attributes & price
	 **************************************************************************/
	function list_attribute($product_id, $fils=true) {
		global  $CURRENCY_DISPLAY;

		$ps_product = new ps_product;
		$db = new ps_DB;
		$db_sku = new ps_DB;
		$db_item = new ps_DB;

		// Get list of children
		if ($fils) {
			$q = "SELECT product_id,product_name FROM #__{vm}_product WHERE product_parent_id='$product_id' AND published='1'";
		}
		else {
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'";
			$db->setQuery($q);
			$db->query();
			$db->next_record();
			$product_parent_id = $db->f("product_parent_id");
			$q = "SELECT product_id,product_name FROM #__{vm}_product WHERE product_parent_id='$product_parent_id' AND published='1'";
		}
		
		$db->setQuery($q);
		$db->query();
		if( $db->num_rows() > 0 ) {
			$display = '<select name="product_id" onChange="this.form.add_product_validate.value=0;this.form.submit();">';
			$display .= '<option value="-1">Choisissez un produit item</option>';
			while ($db->next_record()) {
				$display .= '<option value="' . $db->f("product_id") . '"';
				if ($product_id == $db->f("product_id")) {
					$display .= ' selected="yes"';
				}
				$display .= '>' . $db->f("product_name");

				// For each child get attribute values by looping through attribute list
				$q = "SELECT product_id, attribute_name FROM #__{vm}_product_attribute_sku ";
				$q .= "WHERE product_id='$product_id' ORDER BY attribute_list ASC";
				$db_sku->setQuery($q);  $db_sku->query();

				while ($db_sku->next_record()) {
					$q = "SELECT attribute_name, attribute_value, product_id ";
					$q .= "FROM #__{vm}_product_attribute WHERE ";
					$q .= "product_id='" . $db->f("product_id") . "' AND ";
					$q .= "attribute_name='" . $db_sku->f("attribute_name") . "'";
					$db_item->setQuery($q);  $db_item->query();
					while ($db_item->next_record()) {
						$display .= ' - ' . $db_item->f("attribute_name") . " ";
						$display .= "(" . $db_item->f("attribute_value") . ")";
						if( !$db_sku->is_last_record() )
						$display .= '; ';
					}
				}
				// Attributes for this item are done.
				// Now get item price
				$price = $ps_product->get_price($db->f("product_id"));
				if( $_SESSION["auth"]["show_price_including_tax"] == 1 ) {
					$tax_rate = 1 + $ps_product->get_product_taxrate($db->f("product_id"));
					$price['product_price'] *= $tax_rate;
				}
				$display .= ' - '.$CURRENCY_DISPLAY->getFullValue($price["product_price"]);
				$display .=  '</option>';
			}
	
			$display .= '</select>';			
		}
		else {
			$display= "<input type=\"hidden\" name=\"product_id\" value=\"$product_id\" />\n";
		}

		return $display;
	}
   
}

if( JRequest::getVar( 'page' ) == 'order.order_print' ) {
	$ps_order_edit = new ps_order_edit( $order_id );
	$ps_order_edit->pane_content( $tab );
	if($ps_order_edit->reload_from_db) {
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='$order_id'";
		$db->query($q);
		$db->next_record();
	}
}
?>
