<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__); ?>

<?php
if( sizeof($VM_BROWSE_ORDERBY_FIELDS) < 2 ) {
	return;
}
?>
<?php echo JText::_('VM_ORDERBY') ?>: 
<select class="inputbox" name="orderby" onchange="order.submit()">
<option value="product_list" ><?php echo JText::_('VM_SELECT') ?></option>
<?php
// SORT BY PRODUCT LIST
if( in_array( 'product_list', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
        <option value="product_list" <?php echo $orderby=="product_list" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_DEFAULT') ?></option>
        <?php
}
// SORT BY PRODUCT NAME
if( in_array( 'product_name', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
        <option value="product_name" <?php echo $orderby=="product_name" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_PRODUCT_NAME_TITLE') ?></option>
        <?php
}
// SORT BY PRODUCT PRICE
  if (_SHOW_PRICES == '1' && $auth['show_prices'] && in_array( 'product_price', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
                <option value="product_price" <?php echo $orderby=="product_price" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_PRODUCT_PRICE_TITLE') ?></option>
        <?php
}
// SORT BY PRODUCT SKU
if( in_array( 'product_sku', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
        <option value="product_sku" <?php echo $orderby=="product_sku" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_CART_SKU') ?></option>
        <?php
}
// SORT BY PRODUCT CREATION DATE
if( in_array( 'product_cdate', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
        <option value="product_cdate" <?php echo $orderby=="product_cdate" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_LATEST') ?></option>
        <?php
}
// SORT BY BEST SELLING PRODUCT
if( in_array( 'product_sales', $VM_BROWSE_ORDERBY_FIELDS)) { ?>
        <option value="product_sales" <?php echo $orderby=="product_sales" ? "selected=\"selected\"" : "";?>>
        <?php echo JText::_('VM_SALES') ?></option>
        <?php
}
?>
</select>
