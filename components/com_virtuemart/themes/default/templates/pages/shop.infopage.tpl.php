<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage themes
* @copyright Copyright (C) 2008 soeren - All rights reserved.
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
//TODO !
require_once( CLASSPATH . 'ps_vendor.php');
?>
<h3><?php echo $v_name;?></h3>
<br />
  <div align="center">
    <a href="<?php $db->p("vendor_url") ?>" target="blank">
      <img border="0" src="<?php echo IMAGEURL ?>vendor/<?php echo $v_logo; ?>">
    </a>
  </div>
  <br /><br />
  <table align="center" cellspacing="0" cellpadding="0" border="0">
      <tr valign="top"> 
        <th colspan="2" align="center" class="sectiontableheader">
          <strong><?php echo JText::_('VM_STORE_FORM_CONTACT_LBL') ?></strong>
        </th>
        </tr>
        <tr valign="top">
	<td align="center" colspan="2"><br />
        <?php echo ps_vendor::formatted_store_address( true, 1 ); ?>
        <br /><br /></td>
  </tr>

        <tr>
      <td valign="top" align="center" colspan="2">
          <br /><?php echo JText::_('VM_STORE_FORM_CONTACT_LBL') ?>:&nbsp;<?php echo $v_title ." " . $v_first_name . " " . $v_last_name ?>
          <br /><?php echo JText::_('VM_STORE_FORM_PHONE') ?>:&nbsp;<?php $db->p("phone_1");?>
          <br /><?php echo JText::_('VM_STORE_FORM_FAX') ?>:&nbsp;<?php echo $v_fax ?>
          <br /><?php echo JText::_('VM_STORE_FORM_EMAIL') ?>:&nbsp;<?php echo $v_email; ?><br />
          <br /><a href="<?php $db->p("vendor_url") ?>" target="_blank"><?php $db->p("vendor_url") ?></a><br />
      </td>
        </tr>
        <tr>
      <td valign="top" align="left" colspan="2">
          <br /><?php $db->p("vendor_store_desc") ?><br />
      </td>
        </tr>
</table>