<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id
* @package VirtueMart
* @copyright Copyright (C) 2008 Soeren Eberhardt. All rights reserved.
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
<html> 
<head>
<title><?php echo JText::_('VM_ENQUIRY_MAIL_CUSTOMER_QUESTION');?></title>
<style type="text/css">
<!--
.Stil1 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
.Stil2 {font-family: Verdana, Arial, Helvetica, sans-serif}
-->
</style>
</head>
<body> 

    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="10" >
        <tr valign="top" bgcolor="#CCCCCC"> 
            <td align="left" class="Stil2" colspan="2">
                <b><?php echo $vendorname ?><b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo $contact_name . " " . JText::_('VM_ENQUIRY_MAIL_HAS_REQUESTED') ?>
                <a href="<?php echo $product_url ?>" title="<?php echo $product_name ?>"><?php echo $product_name ?></a>&nbsp;(<?php echo JText::_('VM_ENQUIRY_MAIL_PRODUCT_SKU') ?>:&nbsp;<?php echo $product_sku ?>)
            </td>
        </tr>
        <tr>
            <td width="auto">
                <?php echo $product_thumb ?>
            </td>
            <td> 
                <?php echo $product_s_description ?>  
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr align="left" bgcolor="#CCCCCC">
            <td class="Stil2" colspan="2">
                <b><?php echo JText::_('VM_ENQUIRY_MAIL_QUESTION');?></b>
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <td colspan="2"  style="border:1px solid #000000;">
                <?php echo $subject ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo $contact_name ?>, <a href="mailto:<?php echo $contact_email ?> "><?php echo $contact_email ?></a>
            </td>
        </tr>
    </table>
    
</body>
</html>
