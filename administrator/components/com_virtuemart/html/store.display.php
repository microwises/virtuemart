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

?>
<br />
<img src="<?php echo VM_ADMIN_ICON_URL ?>icon_48/vm_store_48.png" border="0" align="left" alt="Store Home" />
<h2 class="adminListHeader"><?php echo JText::_('VM_STORE_MOD') ?></h2>

<br /><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="2" align="right"> 
      <div align="left"><b><?php echo JText::_('VM_STORE_FORM_LBL') ?> </B>

<?php 
		$db = ps_vendor::get_vendor_details($vendor_id);   
?></div>
    </td>
  </tr>
  <tr> 
    <td width="22%" align="right" ><?php echo JText::_('VM_STORE_FORM_STORE_NAME') ?> :</td>
    <td width="78%" > <?php $db->sp("vendor_store_name") ?></td>
  </tr>
  <tr> 
    <td width="22%" align="right" ><?php echo JText::_('VM_VENDOR_LIST_VENDOR_NAME') ?> :</td>
    <td width="78%" > <?php $db->sp("vendor_name") ?> </td>
  </tr>
  <tr> 
    <td width="22%" align="right" >&nbsp;</td>
    <td width="78%" > <?php $db->sp("address_1") ?><?php 
if ($db->sf("address_2"))
$db->sp("address_2") 
?></td>
  </tr>
  <tr> 
    <td width="22%" align="right" >&nbsp;</td>
    <td width="78%" > <?php $db->sp("city") ?>, <?php $db->sp("state") ?> 
      <?php $db->sp("vzip") ?> <?php $db->sp("country") ?></td>
  </tr>
  <tr> 
    <td width="22%" align="right" ><?php echo JText::_('VM_STORE_FORM_PHONE') ?> :</td>
    <td width="78%" > <?php $db->sp("vendor_phone") ?></td>
  </tr>
  <tr> 
    <td colspan="2" class="topmenu"><b><?php echo JText::_('VM_STORE_FORM_CONTACT_LBL') ?></b></td>
  </tr>
  <tr> 
    <td width="22%" align="right" ><?php echo JText::_('VM_CART_NAME') ?> :</td>
    <td width="78%" > <?php $db->sp("title") ?> <?php $db->sp("first_name") ?> 
      <?php $db->sp("middle_name") ?> <?php $db->sp("last_name") ?></td>
  </tr>
  <tr> 
    <td width="22%" align="right" ><?php echo JText::_('VM_STORE_FORM_PHONE_1') ?> :</TD>
    <TD WIDTH="78%" > <?php $db->sp("phone_1") ?></TD>
  </TR>
  <TR> 
    <TD WIDTH="22%" ALIGN="right" ><?php echo JText::_('VM_STORE_FORM_PHONE_2') ?> :</TD>
    <TD WIDTH="78%" > <?php $db->sp("phone_2") ?></TD>
  </TR>
  <TR> 
    <TD WIDTH="22%" ALIGN="right" ><?php echo JText::_('VM_STORE_FORM_FAX') ?> :</TD>
    <TD WIDTH="78%" > <?php $db->sp("fax") ?></TD>
  </TR>
  <TR> 
    <TD WIDTH="22%" ALIGN="right" ><?php echo JText::_('VM_STORE_FORM_EMAIL') ?> :</TD>
    <TD WIDTH="78%" > <?php $db->sp("email") ?></TD>
  </TR>
  <TR> 
    <TD COLSPAN="2" ALIGN="center" >&nbsp; </TD>
  </TR>
</TABLE>
<h2>&nbsp;</H2>
