<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: order.order_status_form.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

require_once(CLASSPATH.'ps_order_status.php');
$ps_order_status = new ps_order_status();

$order_status_id = JRequest::getVar(  'order_status_id' );
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_ORDER_STATUS_FORM_LBL') );
//Then Start the form
$formObj->startForm();

$readonly = '';
global $hVendor;
(int)$vendor_id = $hVendor -> getVendorIdByUserId($auth['user_id']);
if (!empty($order_status_id)) {
  	$q = "SELECT * FROM #__{vm}_order_status WHERE order_status_id='$order_status_id'";
  	if( !$perm->check( "admin" ))
		$q .= "AND vendor_id='$vendor_id' "; 
  	$db->query($q);  
  	$db->next_record();
  	if( in_array( $db->f('order_status_code'), $ps_order_status->_protected_status_codes ) ) {
	  	$readonly = 'readonly="readonly"';
  	}
}
?><br />
<table class="adminform">

    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_ORDER_STATUS_FORM_CODE') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="order_status_code" value="<?php $db->sp('order_status_code') ?>" size="4" maxlength="1" <?php echo $readonly ?> />
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_ORDER_STATUS_FORM_NAME') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="order_status_name" value="<?php $db->sp("order_status_name") ?>" size="25" />
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_MANUFACTURER_FORM_DESCRIPTION') ?>:</td>
      <td> 
       	<?php editorArea( 'order_status_description', $db->sf("order_status_description"), 'order_status_description', 500, 250, 75, 25 ); ?>
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_ORDER_STATUS_FORM_LIST_ORDER') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="list_order" value="<?php $db->sp("list_order") ?>" size="3" maxlength="3" />
      </td>
    </tr>
    <tr align="center">
      <td colspan="2">&nbsp;</td>
    </tr>    
</table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'order_status_id', $order_status_id );

$funcname = !empty($order_status_id) ? "orderstatusupdate" : "orderstatusadd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.order_status_list', $option );
?>
