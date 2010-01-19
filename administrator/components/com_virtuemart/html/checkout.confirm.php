<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
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

require_once(CLASSPATH.'ps_payment_method.php');
$ps_payment_method = new ps_payment_method;
?>

<h3><?php echo JText::_('VM_ORDER_CONFIRM_MNU') ?></h3>

<?php include(PAGEPATH."ro_basket.php"); ?>

<BR>
<?php
if ($checkout) {

?>
<form action="<?php echo SECUREURL ?>" method="POST" name="Checkout">
<input type="hidden" name="zone_qty" value="<?php echo $zone_qty ?>" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="page" value="<?php echo $modulename?>.thankyou" />
<input type="hidden" name="func" value="checkoutcomplete" />
<input type="hidden" name="user_id" value=<?php echo $auth["user_id"];?>" />
  <input type="hidden" name="ship_to_info_id" value="<?php echo $ship_to_info_id ?>" />
  <!-- customer information --> 
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="top" width="48%"> 
        <table border="0" cellspacing="0" cellpadding="2" width="100%">
          <tr class="sectiontableheader"> 
            <td colspan="2"><b><?php

//$q  = "SELECT * from #__users WHERE ";
//$q .= "id='" . $auth["user_id"] . "' ";
//$q .= "AND address_type='BT'";
//$db->query($q);
//if(!$db->num_rows()) {
//    $q  = "SELECT * from #__{vm}_user_info WHERE ";
//    $q .= "user_id='" . $auth["user_id"] . "' ";
//    $q .= "AND address_type='BT'";
//    $db->query($q);
//}

require_once(CLASSPATH. "ps_user.php");
$db = ps_user::get_user_details($auth["user_id"],array("*"),"", "AND `u`.`address_type`='ST'" );

$db->next_record();
?><?php echo JText::_('VM_CHECKOUT_CONF_BILLINFO') ?></b></td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_COMPANY') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("company");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"> <b><?php echo JText::_('VM_CHECKOUT_CONF_NAME') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("first_name");
     echo " ";
     $db->p("middle_name");
     echo " ";
     $db->p("last_name");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"> <b><?php echo JText::_('VM_CHECKOUT_CONF_ADDRESS') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("address_1");
     echo "<BR>";
     $db->p("address_2");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right">&nbsp;</td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("city");
     echo ",";
     $db->p("state");
     echo " ";
     $db->p("zip");
     echo "<br> ";
     $db->p("country");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_PHONE') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("phone_1");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_FAX') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("fax");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_EMAIL') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php if (!$db->f("email")) { $db->p("email"); } else $db->f("email"); ?>
            </td>
          </tr>
        </table>
      </td>
      <td valign="top" width="52%"> 
        <table border="0" cellspacing="0" cellpadding="2" width="100%">
          <tr class="sectiontableheader"> 
            <td colspan="2"><b><?php
        
		//This seems not necessary anymore    
//    $q  = "SELECT * from #__users WHERE ";
//    $q .= "user_info_id='$ship_to_info_id' ";
//    $db->query($q);
//    
//    if (!$db->num_rows()) {
//        $q  = "SELECT * from #__{vm}_user_info WHERE ";
//        $q .= "user_info_id='$ship_to_info_id'";
//        $db->query($q);
//    }
//    $db->next_record();
    
?><?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO') ?></b></td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO_COMPANY') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("company");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b> <?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO_NAME') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("first_name");
     echo " ";
     $db->p("middle_name");
     echo " ";
     $db->p("last_name");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b> <?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO_ADDRESS') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("address_1");
     echo "<BR>";
     $db->p("address_2");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b></b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("city");
     echo ",";
     $db->p("state");
     echo " ";
     $db->p("zip");
     echo "<br> ";
     $db->p("country");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO_PHONE') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("phone_1");
   ?> </td>
          </tr>
          <tr> 
            <td width="10%" align="right"><b><?php echo JText::_('VM_CHECKOUT_CONF_SHIPINFO_FAX') ?>: </b></td>
            <td width="90%" nowrap="nowrap"> <?php
     $db->p("fax");
   ?> </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!-- Customer Information Ends --> 
  <!-- Customer Shipping --> 
  <?php 
if (IS_ENABLE AND $weight_total!=0) {
include(PAGEPATH."/checkout.shipping_selected.php"); 
}
?><!-- END Customer Shipping --><BR>

<!-- Begin Payment Infomation -->
  <table border="0" cellspacing="0" cellpadding="2" width="100%">
    <tr class="sectiontableheader"> 
      <td colspan="2"><b><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO') ?></b></td>
    </tr>
    <tr> 
      <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_METHOD') ?>: </td>
      <td><?php $ps_payment_method->list_method($db->sf("payment_method_id")) ?></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_NAMECARD') ?>*: </td>
      <td> 
        <input type="text" class="inputbox" name="order_payment_name" value="<?php echo $order_payment_name ?>">
      </td>
    </tr>
    <tr> 
      <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_CCNUM') ?>*: </td>
      <td> 
        <input type="text"  class="inputbox" name="order_payment_number" value="<?php echo $order_payment_number ?>">
      </td>
    </tr>
    <tr> 
      <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_EXDATE') ?>*: </td>
      <td><?php $ps_html->list_month("order_payment_expire_month") . "/" . $ps_html->list_year("order_payment_expire_year") ?></td>
    </tr>
  </table>
<!-- End payment information -->
<BR>
*<?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_REQINFO') ?>.
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr align=center>
  <td><input type="submit" class="button" name="submit" value="<?php echo JText::_('VM_CHECKOUT_CONF_PAYINFO_COMPORDER') ?>"></td>
</tr>
</table>

</form>
<!-- Body ends here -->
<?php 
}
?>
