<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: customer_info.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
?>
<!-- Customer Information --> 
    <table border="0" cellspacing="0" cellpadding="2" width="100%">
        <tr class="sectiontableheader">
            <th colspan="2" align="left"><?php echo JText::_('VM_ORDER_PRINT_CUST_BILLING_LBL') ?></th>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_ORDER_PRINT_COMPANY') ?>: </td>
           <td width="90%">
           <?php
             $db->p("company");
           ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_SHOPPER_LIST_NAME') ?>: </td>
           <td width="90%"><?php
             echo $db->f("first_name"). " " . $db->f("middle_name") ." " . $db->f("last_name"); ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_ADDRESS') ?>: </td>
           <td width="90%">
           <?php
             $db->p("address_1");
             echo "<br />";
             $db->p("address_2");
           ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right">&nbsp;</td>
           <td width="90%">
           <?php
             $db->p("city");
             echo ", ";
             // for state, can be used: state_name, state_2_code, state_3_code
             $db->p("state_2_code");
             echo " ";
             $db->p("zip");
             echo "<br /> ";
             // for country, can be used: country_name, country_2_code, country_3_code
             $db->p("country_name");
           ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_ORDER_PRINT_PHONE') ?>: </td>
           <td width="90%">
           <?php
             $db->p("phone_1");
           ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap"width="10%" align="right"><?php echo JText::_('VM_ORDER_PRINT_FAX') ?>: </td>
           <td width="90%">
           <?php
             $db->p("fax");
           ?>
           </td>
        </tr>
        <tr>
           <td nowrap="nowrap" width="10%" align="right"><?php echo JText::_('VM_ORDER_PRINT_EMAIL') ?>: </td>
           <td width="90%">
           <?php
             $db->p("email");
           ?>
           </td>
        </tr>
        <tr><td align="center" colspan="2"><a href="<?php $sess->purl( SECUREURL ."index.php?page=account.billing&next_page=$page"); ?>">
            (<?php echo JText::_('VM_UDATE_ADDRESS') ?>)</a>
            </td>
        </tr>
    </table>
    <!-- customer information ends -->
    <br />
