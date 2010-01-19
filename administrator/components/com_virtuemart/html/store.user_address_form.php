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
?>
<h2><?php echo JText::_('VM_USER_FORM_ADDRESS_INFO_LBL') ?></h2>
<?php if ($user_info_id) {
   $q = "SELECT * from #__users, #__{vm}_user_info ";
   $q .= "where (#__{vm}_user_info.user_info_id='$user_info_id' OR";
   $q .= " (#__users.user_info_id='$user_info_id') ";
   //Old use
//   $q .= "AND #__{vm}_auth_user_vendor.vendor_id='$hVendor_id'";
   $db->query($q);
   $db->next_record();
}
?> 
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" name="adminForm">
  <table width="100%" border="0" cellspacing="0" cellpadding="2" >
    <tr> 
      <td colspan="2" nowrap align="right" > 
        <div align="left"><b><?php echo JText::_('VM_USER_FORM_SHIPTO_LBL') ?></b></div>
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_ADDRESS_LABEL') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="address_type_name" value="<?php $db->sp("address_type_name") ?>" size="18">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_FIRST_NAME') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="first_name" size="18" value="<?php $db->sp("first_name") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_LAST_NAME') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="last_name" size="18" value="<?php $db->sp("last_name") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_MIDDLE_NAME') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="middle_name" size="16" value="<?php $db->sp("middle_name") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_TITLE') ?>:</td>
      <td width="79%" ><?php $ps_html->list_user_title($db->sf("title")); ?></td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_COMPANY_NAME') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="company" size="24" value="<?php $db->sp("company") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_ADDRESS_1') ?>: </td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="address_1" size="24" value="<?php $db->sp("address_1") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_ADDRESS_2') ?>: </td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="address_2" size="24" value="<?php $db->sp("address_2") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_CITY') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="city" size="18" value="<?php $db->sp("city") ?>">
      </td>
    </tr>
    <?php if (CAN_SELECT_STATES == '1') { ?>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_STATE') ?>:</td>
      <td width="79%" > 
        <?php $ps_html->list_states("state", $db->sp("state")) ?>
      </td>
    </tr>
    <?php } ?>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_ZIP') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="zip" size="10" value="<?php $db->sp("zip") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_COUNTRY') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="country" size="16" value="<?php $db->sp("country") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" > <?php echo JText::_('VM_USER_FORM_PHONE') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="phone_1" size="12" value="<?php $db->sp("phone_1") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" ><?php echo JText::_('VM_USER_FORM_FAX') ?>:</td>
      <td width="79%" > 
        <input type="text" class="inputbox" name="fax" size="12" value="<?php $db->sp("fax") ?>">
      </td>
    </tr>
    <tr> 
      <td width="21%" nowrap align="right" >&nbsp; </td>
      <td width="79%" >&nbsp;</td>
    </tr>
    <tr> 
      <td width="21%" > 
        <input type="hidden" name="address_type" value="ST">
      </td>
      <td width="79%" > 
        <input type="hidden" name="user_info_id" value="<?php echo $user_info_id ?>">
        <input type="hidden" name="func" value="<?php if ($user_info_id) echo "userAddressUpdate"; else echo "userAddressAdd"; ?>">
        <input type="hidden" name="page" value="<?php echo $modulename?>.user_form">
        <input type="hidden" name="cache" value="0">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="option" value="com_virtuemart">
      </td>
    </tr>
  </table>

</form>

