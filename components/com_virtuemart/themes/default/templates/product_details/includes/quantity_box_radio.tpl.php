<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/** This template is used for the quantity box of a product, which has a radio-style add to cart form */ 
?>
<input type="text" class="inputboxquantity" size="4" id="quantity_adjust" name="quantity_adjust" value="1" style="vertical-align: middle;" onchange="alterQuantity(this.form)"/>
<input type="button" class="quantity_box_button quantity_box_button_up" onclick="var qty_el = document.getElementById('quantity_adjust'); var qty = qty_el.value; if( !isNaN( qty )) qty_el.value++;alterQuantity(this.form);return false;\" />
<input type="button" class="quantity_box_button quantity_box_button_down" onclick="var qty_el = document.getElementById('quantity_adjust'); var qty = qty_el.value; if( !isNaN( qty ) && qty > 0 ) qty_el.value--;alterQuantity(this.form);return false;" />
