<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
mm_showMyFileName(__FILE__);
/** This template is used for the quantity box arrangement of products, within the add-to-cart form */ 

extract( $quantity_options );
if( !empty($quantity_box)) {
	$display_type = $quantity_box; 
}
$html = '';
if(!$child && $display_type != 'hide') {
	$html = '<label for="quantity'.$prod_id.'" class="quantity_box">'.JText::_('VM_CART_QUANTITY').':&nbsp;</label>';
}
switch($display_type) {
	case "radio" : //Radio Box
		$html .= '<input type="hidden" id="quantity'.$prod_id.'" name="quantity[]" value="'.$quantity.'" />';
		$html .= '<input type="radio" class="quantitycheckbox" id="selItem'.$prod_id.'" name="selItem" value="0" ';
		if ($quantity > 0 ) {
			$html .= 'checked="checked" ';
		}
		$html .= 'onclick="alterQuantity(this.form)" />';
		break;
	case "hide" : // Hide box - but set quantity to 1!
		$html .= '<input type="hidden" id="quantity'.$prod_id.'" name="quantity[]" value="1" />';
		break;
	case "check" :
		$html .= '<input type="hidden" id="quantity'.$prod_id.'" name="quantity[]" value="'.$quantity.'" style="vertical-align: middle;"/>
		<input type="checkbox" class="quantitycheckbox" id ="selItem'.$prod_id.'" name="check[]" ';
		if ($quantity > 0 ) {
			$html .= 'checked="checked"';
		}
		$html .= ' value="1" onclick="javascript: if(this.checked==true) document.getElementById(\'quantity'.$prod_id.'\').value = 1; else {document.getElementById(\'quantity'.$prod_id.'\').value=0;} "/> ';
		break;
	case "drop" :
		$code = '<select class="inputboxquantity" id="quantity'.$prod_id.'" name="quantity[]">';
		for($i=$quantity_start;$i<$quantity_end+1;$i += $quantity_step) {
			$code .= '  <option value="'.$i.'"';
			if ($i == $quantity) {
				$code .= ' selected="selected"';
			}
			$code .= '>'.$i."</option>\n";
		}
		$code .= "</select>\n";
		$html .= $code;
		break;
	case "none" :
	default:
		$html .= '<input type="text" class="inputboxquantity" size="4" id="quantity'.$prod_id.'" name="quantity[]" value="'.$quantity.'" />
		<input type="button" class="quantity_box_button quantity_box_button_up" onclick="var qty_el = document.getElementById(\'quantity'.$prod_id.'\'); var qty = qty_el.value; if( !isNaN( qty )) qty_el.value++;return false;" />
		<input type="button" class="quantity_box_button quantity_box_button_down" onclick="var qty_el = document.getElementById(\'quantity'.$prod_id.'\'); var qty = qty_el.value; if( !isNaN( qty ) && qty > 0 ) qty_el.value--;return false;" />
		';
		break;
}
echo $html;
?>