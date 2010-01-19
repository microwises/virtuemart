<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
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

require_once( CLASSPATH . 'ps_creditcard.php' );

include_class( 'shopper');
global $ps_shopper_group;

$payment_method_id = vmRequest::getint('id');
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

$vars['published'] = "Y";
$default['element'] = 'payment';

if (!empty($payment_method_id)) {
	//TODO vendorthing by Max Milbers
    $q = "SELECT * FROM #__{vm}_payment_method WHERE vendor_id='$hVendor_id' AND ";
    $q .= "id='$payment_method_id'"; 
    $db->query($q);  
    $db->next_record();
}

//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_PAYMENT_METHOD_FORM_LBL') );
//Then Start the form
$formObj->startForm();

?>
<br />
<?php
$tabs = new vmTabPanel(0, 1, "paymentform");
$tabs->startPane("content-pane");
$tabs->startTab( JText::_('VM_PAYMENT_METHOD_FORM_LBL'), "global-page");
?>
<table class="adminform">
    <tr class="row0">
      <td class="labelcell"><?php echo JText::_('VM_ISSHIP_LIST_PUBLISH_LBL') ?>?:</td>
      <td><input type="checkbox" name="published" class="inputbox" value="Y" <?php echo $db->sf("published")=="Y" ? "checked=\"checked\"" : "" ?> /></td>
    </tr>
    <tr class="row1"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_NAME') ?>:</td>
      <td width="69%" > 
        <input type="text" class="inputbox" name="name" value="<?php $db->sp("name") ?>" size="32" />
      </td>
    </tr>
    <tr class="row1">
      <td class="labelcell"><?php
          echo JText::_('VM_PAYMENT_CLASS_NAME');
          ?>
      </td>
      <td width="69%">
      	<?php 
     	echo vmPaymentMethod::list_available_classes( 'element', $db->sf("element") ? $db->sf("element") : 'payment' );
      	echo vmToolTip( JText::_('VM_PAYMENT_CLASS_NAME_TIP') ); ?>
      </td>
    </tr>
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_ENABLE_PROCESSOR') ?>:</td>
      <td width="69%" >
      <?php 
          $payment_process = $db->f("type"); 
          $payment_types = array( "" => JText::_('VM_PAYMENT_FORM_CC'), 
                              "Y" => JText::_('VM_PAYMENT_FORM_USE_PP'), 
                              "B" => JText::_('VM_PAYMENT_FORM_BANK_DEBIT'), 
                              "N" => JText::_('VM_PAYMENT_FORM_AO'), 
                              "P" => JText::_('VM_PAYMENT_FORM_FORMBASED') );
          $i = 0;
          foreach( $payment_types as $value => $description) {
            echo "<input type=\"radio\" onchange=\"check()\" name=\"type\" id=\"type$i\" value=\"$value\"";
            echo $payment_process == $value ? " checked=\"checked\">\n" : ">\n";
            echo '<label for="type'.$i.'">'.$description . "</label><br />";
            $i++;
          }
      ?>
      </td>
    </tr>
    <tr class="row1">
      <td colspan="2"><hr /></td>
    </tr>
    <tr class="row0">
      <td class="labelcell"><div id="accepted_creditcards1"></div></td>
      <td width="69%"><div id="accepted_creditcards2"></div></td>
    </tr>
    <div id="accepted_creditcards_store"></div>
    <tr class="row1">
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_SHOPPER_GROUP') ?>:</td>
      <td width="69%" ><?php 
      		echo ps_shopper_group::list_shopper_groups("shopper_group_id", $db->sf("shopper_group_id")) ?> 
      </td>
    </tr>
    <tr class="row1"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_DISCOUNT') ?>:</td>
      <td width="69%" > 
        <input type="text" class="inputbox" name="discount" size="6"  value="<?php $db->sp("discount"); ?>" />
        <?php echo vmToolTip( JText::_('VM_PAYMENT_METHOD_DISCOUNT_TIP') ); ?>
      </td>
    </tr>
    
    <tr class="row1"> 
      <td class="labelcell"><?php echo JText::_('VM_PRODUCT_DISCOUNT_AMOUNTTYPE') ?>:</td>
      <td width="69%"> 
        <input type="radio" class="inputbox" id="discount_is_percent0" name="discount_is_percentage" value="1" <?php if($db->sf("discount_is_percentage")==1) echo "checked=\"checked\""; ?> />
        <label for="discount_is_percent0"><?php echo JText::_('VM_PRODUCT_DISCOUNT_ISPERCENT') ?></label>&nbsp;&nbsp;&nbsp;
        <?php echo vmToolTip( JText::_('VM_PRODUCT_DISCOUNT_ISPERCENT_TIP') ); ?><br />
        <input type="radio" class="inputbox" id="discount_is_percent1" name="discount_is_percentage" value="0" <?php if($db->sf("discount_is_percentage")==0) echo "checked=\"checked\""; ?> />
        <label for="discount_is_percent1"><?php echo JText::_('VM_PRODUCT_DISCOUNT_ISTOTAL') ?></label>
      </td>
    </tr>
    <tr class="row1"> 
    	<td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_DISCOUNT_MAX_AMOUNT') ?>:</td>
    	<td ><input type="text" name="discount_max_amount" value="<?php $db->sp('discount_max_amount') ?>" size="5" /></td>
   	</tr>
   	
    <tr class="row1"> 
    	<td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_DISCOUNT_MIN_AMOUNT') ?>:</td>
    	<td ><input type="text" name="discount_min_amount" value="<?php $db->sp('discount_min_amount') ?>" size="5" /></td>
   	</tr>
   	
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_LIST_ORDER') ?>:</td>
      <td width="69%" > 
        <input type="text" class="inputbox" name="list_order" size="4" maxlength="4" value="<?php $db->sp("list_order"); ?>" />
      </td>
    </tr>
    <tr class="row0"> 
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>
<?php
        $tabs->endTab();
        $tabs->startTab( JText::_('VM_CONFIG'), "config-page");
        
        if( $db->sf('element') ) {
	        $parameters = new vmParameters($db->f('params'), ADMINPATH.'plugins/payment/'.basename($db->f('element')).'.xml', 'payment' );
	        echo $parameters->render();
        }
        echo '<br />
<strong>'.JText::_('VM_PAYMENT_EXTRAINFO').':';
		echo vmToolTip( JText::_('VM_PAYMENT_EXTRAINFO_TIP') ) 
	?>
<br />
<textarea class="inputbox" name="payment_extrainfo" cols="120" rows="20"><?php echo htmlspecialchars( $db->sf("payment_extrainfo") ); ?></textarea>
<?php
$tabs->endTab();
$tabs->endPane();

// Add necessary hidden fields
$formObj->hiddenField( 'id', $payment_method_id );

$funcname = !empty($payment_method_id) ? "paymentMethodUpdate" : "paymentMethodAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.payment_method_list', $option );
?>
  
  <script type="text/javascript">
function check() {
   if (document.adminForm.type[0].checked == true || document.adminForm.type[1].checked == true) {
      document.getElementById('accepted_creditcards1').innerHTML = '<strong><?php echo JText::_('VM_PAYMENT_ACCEPTED_CREDITCARDS') ?>:';
      if (document.getElementById('accepted_creditcards_store').innerHTML != '')
        document.getElementById('accepted_creditcards2').innerHTML ='<input type="text" name="accepted_creditcards" value="' + document.getElementById('accepted_creditcards_store').innerHTML + '" class="inputbox" />';
      else
        document.getElementById('accepted_creditcards2').innerHTML = '<?php ps_creditcard::creditcard_checkboxes( $db->f("accepted_creditcards") ); ?>';
   }
   else {
    try {
      document.getElementById('accepted_creditcards_store').innerHTML = document.adminForm.accepted_creditcards.value;
    }
    catch (e) {}
    document.getElementById('accepted_creditcards1').innerHTML = '';
    document.getElementById('accepted_creditcards2').innerHTML = '';
  }
}
check();
</script>