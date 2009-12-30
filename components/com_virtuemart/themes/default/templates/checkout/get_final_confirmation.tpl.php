<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: get_final_confirmation.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
ps_checkout::show_checkout_bar();

echo $basket_html;

echo '<br />';

$varname = 'VM_CHECKOUT_MSG_' . CHECK_OUT_GET_FINAL_CONFIRMATION;
echo '<h5>'. JText::_($varname) . '</h5>';
$db = new ps_DB();

echo '<table>';
// Begin with Shipping Address
if(!ps_checkout::noShipToNecessary()) {

	$db->query("SELECT * FROM #__{vm}_user_info WHERE user_info_id='".strip_tags($_REQUEST['ship_to_info_id'])."'");
	$db->next_record();

	echo '<tr><td valign="top"><strong>'.JText::_('VM_ADD_SHIPTO_2') . ":</strong></td>";
	echo '<td>';
	echo vmFormatAddress( array('name' => $db->f("first_name")." ".$db->f("last_name"),
        								'address_1' => $db->f("address_1"),
        								'address_2' => $db->f("address_2"),
        								'state' => $db->f("state"),
        								'zip' => $db->f("zip"),
        								'city' => $db->f("city"),
        								'country' => $db->f('country')
        							), true );
	
	echo "</td></tr>";
}

// Print out the Selected Shipping Method
if(!ps_checkout::noShippingMethodNecessary()) {

	echo '<tr><td valign="top"><strong>'.JText::_('VM_INFO_MSG_SHIPPING_METHOD') . ":</strong></td>";
	$rate_details = explode( "|", urldecode(urldecode(JRequest::getVar('shipping_rate_id'))) );
	echo '<td>';
	foreach( $rate_details as $k => $v ) {
		if( $k == 3 ) {
			echo $CURRENCY_DISPLAY->getFullValue( $v )."; ";
		} elseif( $k > 0 && $k < 4) {
			echo "$v; ";
		}
	}
	echo "</td></tr>";
}

unset( $row );
if( !isset($order_total) || $order_total > 0.00 ) {
	$payment_method_id = vmRequest::getInt( 'payment_method_id' );
	
	$db->query('SELECT payment_method_id, name FROM #__{vm}_payment_method WHERE payment_method_id='.$payment_method_id);
	$db->next_record();
	echo '<tr><td valign="top"><strong>'.JText::_('VM_ORDER_PRINT_PAYMENT_LBL') . ":</strong></td>";
	echo '<td>';
	echo $db->f("name");
	echo "</td></tr>";
}
echo '</table>';
?>
<br />
<div align="center">
    <?php echo JText::_('VM_CHECKOUT_CUSTOMER_NOTE') ?>:<br />
    <textarea title="<?php echo JText::_('VM_CHECKOUT_CUSTOMER_NOTE') ?>" cols="50" rows="5" name="customer_note"></textarea>
    <br />
    <?php
    if (PSHOP_AGREE_TO_TOS_ONORDER == '1') { ?>
        <br />
      	<input type="checkbox" name="agreed" value="1" class="inputbox" />&nbsp;&nbsp;
      	<?php 
      	$link = $mosConfig_live_site .'/index2.php?option=com_virtuemart&amp;page=shop.tos&amp;pop=1&amp;Itemid='. $Itemid;
		$text = JText::_('VM_I_AGREE_TO_TOS');
		echo vmPopupLink( $link, $text );
        echo '<br />';
    }
    ?>
</div>
<?php
if( VM_ONCHECKOUT_SHOW_LEGALINFO == '1' ) {
	$link =  sefRelToAbs('index2.php?option=com_content&amp;task=view&amp;id='.VM_ONCHECKOUT_LEGALINFO_LINK );
	$jslink = "window.open('$link', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;";
		if( @VM_ONCHECKOUT_LEGALINFO_SHORTTEXT=='' || !defined('VM_ONCHECKOUT_LEGALINFO_SHORTTEXT')) {
		$text = JText::_('VM_LEGALINFO_SHORTTEXT');
	} else {
		$text = VM_ONCHECKOUT_LEGALINFO_SHORTTEXT;
	}
	?>
    <div class="legalinfo"><?php
    	echo sprintf( $text, $link, $jslink );
    	?>
    </div><br />
    <?php
	}
    ?>
<div align="center">
<input type="submit" onclick="return( submit_order( this.form ) );" class="button" name="formSubmit" value="<?php echo JText::_('VM_ORDER_CONFIRM_MNU') ?>" />
</div>
<?php
if(  PSHOP_AGREE_TO_TOS_ONORDER == '1' ) {
	echo vmCommonHTML::scriptTag('', "function submit_order( form ) {
    if (!form.agreed.checked) {
        alert( \"". JText::_('VM_AGREE_TO_TOS',false) ."\" );
        return false;
    }
    else {
        return true;
    }
}" );
} else {
	echo vmCommonHTML::scriptTag('', "function submit_order( form ) { return true;  }" );
}
?>