<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @author nfischer & kaltokri
* @copyright Copyright (C) 2006 Ingemar F�llman. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*/

/****************************************************************************
* ps_order_change_html
* The class  acts as a plugin for the order_print page.
*************************************************************************/
class ps_order_change_html {
	
	var $order_id;
	var $reload_from_db;

	/**************************************************************************
	* name: ps_order_change_html (constructor)
	* created by: kaltokri
	* description: constructor, setup initial variables
	* parameters: Order Id
	* returns: none
	**************************************************************************/
	function ps_order_change_html($order_id) {
		$this->order_id = $order_id;
	}

	/**************************************************************************
	* name: html_change_bill_to
	* created by: kaltokri
	* description: Prints formular to change bill to
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_bill_to($user_id) {
    
	?><tr> 
    <td width="35%" align="right"><?php echo JText::_('VM_ORDER_CHANGE_UPD_BILL') ?>:</td>
    <td width="65%" align="left">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  	<select name="bill_to">
  		<?php
  		$dbs = new ps_DB;
      $q  = "SELECT user_id, last_name, first_name FROM #__{vm}_user_info WHERE address_type = 'BT' ORDER BY last_name ASC"; 
  		$dbs->query($q);
  		while ($dbs->next_record()){
  		  if (!is_null( $dbs->f('last_name') )) {
    			print '<option value="'.$dbs->f('user_id').'"';
    			if($dbs->f('user_id') == $user_id) print " selected ";
  			  print '>';
    			print $dbs->f('last_name');
    			print ", ".$dbs->f('first_name');
    			print '</option>';
  			}
  		}
  		?>
  	</select>
  	<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>" src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
  	<input type="hidden" value="1" name="change_bill_to" />
  	<input type="hidden" name="page" value="order.order_print" />
  	<input type="hidden" name="option" value="com_virtuemart" />
  	<input type="hidden" name="func" value="" />
  	<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
  </form>
  </td>
  </tr> <?php		  
	}
	
	/**************************************************************************
	* name: html_change_ship_to
	* created by: kaltokri
	* description: Prints formular to change ship to
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_ship_to($user_id) {
    
	?>
		<tr>
  		<td align="right"><strong><?php echo JText::_('VM_ORDER_CHANGE_UPD_SHIP') ?>:</strong></td>
  		<td align="left">
    		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      		<select name="ship_to">
        		<?php
        		$dbs = new ps_DB;
            $q  = "SELECT user_info_id, address_type_name FROM #__{vm}_user_info WHERE user_id = '" . $user_id . "' ORDER BY address_type_name ASC"; 
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
      		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
      		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
      		<input type="hidden" value="1" name="change_ship_to" />
      		<input type="hidden" name="page" value="order.order_print" />
      		<input type="hidden" name="option" value="com_virtuemart" />
      		<input type="hidden" name="func" value="" />
      		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
      	</form>
  		</td>
		</tr> <?php
	}
	
	/**************************************************************************
	* name: html_change_customer_note
	* created by: kaltokri
	* description: Prints formular to change Customer Note
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_customer_note() {
    
    
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
		$db->query($q);
		$db->next_record();
	?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		<textarea name="customer_note" cols="80" rows="5"><?php $db->p("customer_note") ?></textarea>
  		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"	src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
  		<input type="hidden" value="1" name="change_customer_note" />
  		<input type="hidden" name="page" value="order.order_print" />
  		<input type="hidden" name="option" value="com_virtuemart" />
  		<input type="hidden" name="func" value="" />
  		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
		</form> <?php
  }

	/**************************************************************************
	* name: html_change_shipping
	* created by: ?, modified by kaltokri
	* description: Prints formular to change standard shipping
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_shipping() {
    
    
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
		$db->query($q);
		$db->next_record();

		$rate_details = explode( "|", $db->f("ship_method_id") );

    ?>
  			<?php
    		if($db->f('ship_method_id') == "" OR preg_match('/^standard_shipping/', $db->f('ship_method_id'))) {
    		?>
      		<tr>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<select name="shipping">
              		<?php
              		$dbs = new ps_DB;
              		$q = 'SELECT shipping_rate_id, shipping_rate_name, shipping_rate_weight_start, shipping_rate_weight_end, shipping_rate_value, shipping_rate_package_fee, tax_rate, currency_name 
              				FROM #__{vm}_shipping_rate, #__{vm}_currency, #__{vm}_tax_rate 
              				WHERE currency_id = shipping_rate_currency_id 
              					AND tax_rate_id = shipping_rate_vat_id 
              				ORDER BY shipping_rate_list_order';
              		$dbs->query($q);
              		while ($dbs->next_record()){
              			print '<option value="'.$dbs->f('shipping_rate_id').'"';
              			if($dbs->f('shipping_rate_id') == $rate_details[4]) 
              				print " selected ";
              			print '>';
              			print $dbs->f('shipping_rate_name');
              			//print "; (".$dbs->f('shipping_rate_weight_start')." - ".$dbs->f('shipping_rate_weight_end')."); ";
              			print " ---&gt; ";
              			print " ".(($dbs->f('shipping_rate_value') * (1+$dbs->f('tax_rate'))) + $dbs->f('shipping_rate_package_fee'));
              			print " ".$dbs->f('currency_name');
              			print '</option>';
              		}
              		?>
              	</select>
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="change_standard_shipping" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
          		</form>
          	</td>
          </tr><?php
         } else {
    		?>
      		<tr>
        		<td align="right"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>: </strong></td>
        	</tr>
        	<tr>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("order_shipping") ?>" size="5" name="order_shipping" />
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="change_shipping" />
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
      		</tr>
      		<tr>
        		<td>
          		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            		<input type="text" value="<?php $db->p("order_shipping_tax") ?>" name="order_shipping_tax" size="5" />
            		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
            		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
            		<input type="hidden" value="1" name="change_shipping_tax" />
            		<input type="hidden" name="page" value="order.order_print" />
            		<input type="hidden" name="option" value="com_virtuemart" />
            		<input type="hidden" name="func" value="" />
            		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
        		  </form>
            </td>    
      		</tr>
    		<?php
    		}   
  }
  
	/**************************************************************************
	* name: html_change_discount
	* created by: ?, modified by kaltokri
	* description: Prints formular to change discount
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_discount() {
    
    
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
		$db->query($q);
		$db->next_record();

		?>
  		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		<?php echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT') ?>:
    		<input type="text" value="<?php $db->p("order_discount") ?>" size="5" name="order_discount" />
    		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
    		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
    		<input type="hidden" value="1" name="change_discount" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
  		</form>
    <?php
  }
  
	/**************************************************************************
	* name: html_change_coupon_discount
	* created by: ?, modified by kaltokri
	* description: Prints formular to change coupon discount
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_coupon_discount() {
    
    
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
		$db->query($q);
		$db->next_record();
    ?>
  		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		  <?php echo JText::_('VM_COUPON_DISCOUNT') ?>:
    		<input type="text" value="<?php $db->p("coupon_discount") ?>" size="5" name="coupon_discount" />
    		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
    		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
    		<input type="hidden" value="1" name="change_coupon_discount" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
  		</form>
    <?php
  }
  
	/**************************************************************************
	* name: html_change_delete_item
	* created by: ?, modified by kaltokri
	* description: Prints formular to delete products
	* parameters: $order_item_id
	* returns: none
	**************************************************************************/
	function html_change_delete_item($order_item_id) {
    
    
		$db = new ps_DB;
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='".$this->order_id."'";
		$db->query($q);
		$db->next_record();
    ?>
    <td width="5%">
  		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" >		
    		<input type="image" title="<?php echo JText::_('VM_DELETE') ?>"  
    		src="<?php echo IMAGEURL ?>ps_image/delete_f2.gif" border="0"  alt="<?php echo JText::_('VM_DELETE') ?>" />
        <input type="hidden" value="1" name="change_delete_item" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_item_id" value="<?php echo $order_item_id ?>" />  
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />
  		</form>
		</td>
    <?php
  }
  
  
	/**************************************************************************
	* name: html_change_item_quantity
	* created by: ?, modified by kaltokri
	* description: Prints formular to change quantity of an item
	* parameters: $order_item_id, $product_quantity
	* returns: none
	**************************************************************************/
	function html_change_item_quantity($order_item_id, $product_quantity) {
    
    ?>
		<td>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    	  <input type="text" value="<?php echo $product_quantity ?>" name="product_quantity" size="5" />
    		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>" 
    		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
    		<input type="hidden" value="1" name="change_item_quantity" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_item_id" value="<?php echo $order_item_id ?>" />  
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
		 </form>
    </td>
    <?php
  }

	/**************************************************************************
	* name: html_change_add_item
	* created by: ?, modified by kaltokri
	* description: Prints formular to change quantity of an item
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_add_item() {
		global  $vmLogger;
		
		require_once(CLASSPATH . 'ps_product_attribute.php');
		$ps_product_attribute = new ps_product_attribute;
		
		// Get product_id
    $product_id = JRequest::getVar(  'product_id' );
    $product_id_bysku = JRequest::getVar(  'product_id_bysku' );
    
    // If sku was selected it overwrites the product_id
    if ($product_id_bysku > 0) {
			$product_id = $product_id_bysku;
		}
    
    // Output to generate a "return to parant"-button
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

    // Page reseted = -1 or called first time = ""
    if ($product_id < 0 || $product_id == "") { 
    	// Generate product list
      ?>
  		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		  <br /><br />
        <table class="adminlist">
          <tr>
            <th> <?php echo JText::_('VM_ORDER_EDIT_ADD_PRODUCT') ?></th>
          </tr>
          <tr>
            <td align="left"><?php echo $this->list_products($product_id, true) ?><?php echo $this->list_products($product_id) ?></td>
          </tr>
        </table>
    		<input type="hidden" name="add_product_validate" value="0" />
    		<input type="hidden" name="add_product_item" value="0" />
		    <input type="hidden" name="add_product" value="1" />
		    
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />
      </form>
      <?php
    }
  	else {
      // Query child products
  		$db = new ps_DB;
  		$q = "SELECT product_id FROM #__{vm}_product WHERE ";
  		$q .= "product_parent_id = '".$product_id."'";
  		$db->query($q);

      // Are there childs?
  		if ( $db->num_rows()) {
         // Yes! Drop down list to select the child
         ?>
    		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    		  <br /><br />
          <table class="adminlist">
            <tr>
              <th><?php echo JText::_('VM_ORDER_EDIT_ADD_PRODUCT') ?></th>
            </tr>
          </table>
          <table class="adminlist">
            <tr>
              <th><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th> 
            </tr>
            <tr>
              <td>
                <input type="hidden" name="add_product" value="1" />
            		<input type="hidden" name="add_product_validate" value="0" />
                <input type="hidden" name="add_product_item" value="1" />
                <?php echo $this->list_attribute($product_id) ?>
              </td>
            </tr>
          </table>
      		<input type="hidden" name="page" value="order.order_print" />
      		<input type="hidden" name="option" value="com_virtuemart" />
      		<input type="hidden" name="func" value="" />
      		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />
        </form>
      <?php echo $html_return_parent;
  		}
  		else {
         // No Childs or selected child product! Form to add a product that has no childs
         ?>
    		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    		  <br /><br />
          <table class="adminlist">
            <tr>
              <th><?php echo JText::_('VM_ORDER_EDIT_ADD_PRODUCT') ?></th>
            </tr>
          </table>
          <table class="adminlist">
            <tr>
              <th><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th> 
              <th><?php echo JText::_('VM_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST') ?></th>
              <th><?php echo JText::_('VM_PRODUCT_FORM_ATTRIBUTE_LIST') ?></th>
              <th align="left"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></th>
              <th align="left">Action</th>
            </tr>
            <tr>
            <?php
		        if (JRequest::getVar( 'add_product_item' ) == 1) {
              echo '<td>' . $this->list_attribute($product_id,false) . '</td>';
              echo '<input type="hidden" name="add_product_item" value="1" />';
            }
		        else {
              echo '<td>' . $this->list_products($product_id, true) . $this->list_products($product_id) . '</td>';
            }?>
              <td><?php echo $ps_product_attribute->list_advanced_attribute($product_id) ?></td>
              <td><?php echo $ps_product_attribute->list_custom_attribute($product_id) ?></td>
              <td>
                <input type="text" value="1" name="product_quantity" size="5" />
            		<input type="hidden" name="add_product_validate" value="1" />
        		    <input type="hidden" name="add_product" value="1" />
              </td>
              <td><input type="submit" value="<?php echo JText::_('VM_ORDER_EDIT_ADD') ?>" /></td>
            </tr>
          </table>
      		<input type="hidden" name="page" value="order.order_print" />
      		<input type="hidden" name="option" value="com_virtuemart" />
      		<input type="hidden" name="func" value="" />
      		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />
        </form>
      <?php
		    if (JRequest::getVar( 'add_product_item' ) == 1) {
          echo $html_return_parent;
        }
      }
  	}
		return;  
	}


    /**************************************************************************
	 * name: list_products
	 * created by: nfischer
	 * description: Create a list of products
	 * parameters: product_id
	 * returns: html to display
	 **************************************************************************/
	function list_products($product_id, $skumode=false) {
		
		$db = new ps_DB;
		
		// List all products by sku
		if ($skumode) {
		  $sortby = 'product_sku';
		  $select_name = 'product_id_bysku';
		  $reset_other_list = 'this.form.product_id.value=-1';
		  $first_item = JText::_('VM_ORDER_EDIT_CHOOSE_PRODUCT_BY_SKU');
		}
		// List all products by name
    else {
		  $sortby = 'product_name';
		  $select_name = 'product_id';
		  $reset_other_list = 'this.form.product_id_bysku.value=-1';
		  $first_item = JText::_('VM_ORDER_EDIT_CHOOSE_PRODUCT');
    }

		$query_list_products = "SELECT DISTINCT `product_name`,`products_per_row`,`category_browsepage`,`category_flypage`";
		$query_list_products .= ",`#__{vm}_product`.`product_id`,`#__{vm}_category`.`category_id`,`product_full_image`,`product_thumb_image`";
		$query_list_products .= ",`product_s_desc`,`product_parent_id`,`product_publish`,`product_in_stock`,`product_sku`";
		$query_list_products .= " FROM (`#__{vm}_product`, `#__{vm}_category`, `#__{vm}_product_category_xref`";
		$query_list_products .= ",`#__{vm}_shopper_group`) LEFT JOIN `#__{vm}_product_price` ON";
		$query_list_products .= " `#__{vm}_product`.`product_id` = `#__{vm}_product_price`.`product_id`";
		$query_list_products .= " WHERE `#__{vm}_product_category_xref`.`category_id`=`#__{vm}_category`.`category_id`";
		$query_list_products .= " AND `#__{vm}_product`.`product_id`=`#__{vm}_product_category_xref`.`product_id`";
		$query_list_products .= " AND `#__{vm}_product`.`product_parent_id`='0'";
		$query_list_products .= " AND (( `#__{vm}_shopper_group`.`shopper_group_id`=`#__{vm}_product_price`.`shopper_group_id` )";
		$query_list_products .= " OR (`#__{vm}_product_price`.`product_id` IS NULL))";
		$query_list_products .= " GROUP BY `#__{vm}_product`.`product_sku` ORDER BY `#__{vm}_product`.`" . $sortby . "`";
		$db->query($query_list_products);

		$display = '<select name="' . $select_name . '" onChange="this.form.add_product_validate.value=0;' . $reset_other_list . ';this.form.submit();">';
		$display .= '<option value="-1">' . $first_item . '</option>';
		while ($db->next_record()) {
			$display .= '<option value="' . $db->f("product_id") . '"';
			if ($product_id == $db->f("product_id")) {
				$display .= ' selected="yes"';
			}
			if ($skumode) {
        $display .= '>' . $db->f("product_sku") . '</option>';
      }
      else {
        $display .= '>' . $db->f("product_name") . '</option>';      
      }
		}
		$display .= '</select>';
		
		return $display;
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

		if ($fils) {
		  // Generate childlist
			$q = "SELECT product_id,product_name FROM #__{vm}_product WHERE product_parent_id='$product_id'";
		}
		else {
		  // Child is selected, list siblings
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'";
			$db->setQuery($q);
			$db->query();
			$db->next_record();
			$product_parent_id = $db->f("product_parent_id");
			$q = "SELECT product_id,product_name FROM #__{vm}_product WHERE product_parent_id='$product_parent_id'";
		}
		
		$db->setQuery($q);
		$db->query();
		if( $db->num_rows() > 0 ) {
			$display = '<select name="product_id" onChange="this.form.add_product_validate.value=0;this.form.submit();">';
			$display .= '<option value="-1">' . JText::_('VM_SELECT') . '</option>';
			while ($db->next_record()) {
				$display .= '<option value="' . $db->f("product_id") . '"';
				if ($product_id == $db->f("product_id")) {
					$display .= ' selected="yes"';
				}
				$display .= '>' . $db->f("product_name");

				if ($fils) {
          $searched_id = $product_id;
        }
        else {
          $searched_id = $product_parent_id;
        }
        
        // For each child get attribute values by looping through attribute list
				$q = "SELECT product_id, attribute_name FROM #__{vm}_product_attribute_sku ";
				$q .= "WHERE product_id='$searched_id' ORDER BY attribute_list ASC";
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
	
	/**************************************************************************
	* name: html_change_product_item_price
	* created by: kaltokri
	* description: change product item price
	* parameters: $order_item_id, $product_item_price
	* returns: none
	**************************************************************************/
	function html_change_product_item_price($order_item_id, $product_item_price) {
    
    ?>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    	  <input type="text" value="<?php echo $product_item_price ?>" name="product_item_price" size="5" />
    		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>" 
    		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
    		<input type="hidden" value="1" name="change_product_item_price" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_item_id" value="<?php echo $order_item_id ?>" />  
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
		 </form>
    <?php
	}

	/**************************************************************************
	* name: html_change_product_final_price
	* created by: kaltokri
	* description: change product item price
	* parameters: $order_item_id, $product_final_price
	* returns: none
	**************************************************************************/
	function html_change_product_final_price($order_item_id, $product_final_price) {
    
    ?>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    	  <input type="text" value="<?php echo $product_final_price ?>" name="product_final_price" size="5" />
    		<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>" 
    		src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
    		<input type="hidden" value="1" name="change_product_final_price" />
    		<input type="hidden" name="page" value="order.order_print" />
    		<input type="hidden" name="option" value="com_virtuemart" />
    		<input type="hidden" name="func" value="" />
    		<input type="hidden" name="order_item_id" value="<?php echo $order_item_id ?>" />  
    		<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
		 </form>
    <?php
	}

	/**************************************************************************
	* name: html_change_bill_to
	* created by: kaltokri
	* description: Prints formular to change bill to
	* parameters: none
	* returns: none
	**************************************************************************/
	function html_change_payment($payment_id) {
    global  $CURRENCY_DISPLAY;
	?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  	<select name="new_payment_id">
  		<?php
  		$dbs = new ps_DB;
      $q  = "SELECT payment_method_id, name, discount FROM #__{vm}_payment_method WHERE published = 'Y' ORDER BY name ASC"; 
  		$dbs->query($q);
  		while ($dbs->next_record()){
  		  if (!is_null( $dbs->f('payment_method_id') )) {
    			print '<option value="'.$dbs->f('payment_method_id').'"';
    			if($dbs->f('payment_method_id') == $payment_id) print " selected ";
  			  print '>';
    			print $dbs->f('name');
    			print "-> ". $CURRENCY_DISPLAY->getFullValue(($dbs->f('discount') * -1));
    			print '</option>';
  			}
  		}
  		?>
  	</select>
  	<input type="image" title="<?php echo JText::_('VM_UPDATE') ?>"
  	src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo JText::_('VM_UPDATE') ?>" />
  	<input type="hidden" value="1" name="change_payment" />
  	<input type="hidden" name="page" value="order.order_print" />
  	<input type="hidden" name="option" value="com_virtuemart" />
  	<input type="hidden" name="func" value="" />
  	<input type="hidden" name="order_id" value="<?php echo $this->order_id ?>" />  
  </form>
  <?php		  
	}

}
?>
