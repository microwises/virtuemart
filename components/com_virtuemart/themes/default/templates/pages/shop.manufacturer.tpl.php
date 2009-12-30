<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.manufacturer.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage themes
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
mm_showMyFileName( __FILE__ );
?>
<h3><?php echo $mf_name;?></h3>
  
  <table align="center"cellspacing="0" cellpadding="0" border="0">
      <tr valign="top"> 
        <th colspan="2" align="center"class="sectiontableheader">
          <strong><?php echo JText::_('VM_MANUFACTURER_FORM_INFO_LBL') ?></strong>
        </th>
      </tr>
      <tr valign="top">
        <td align="center"colspan="2"><br />
          <?php echo "&nbsp;" . $mf_name . "<br />"; ?>
          <br /><br />
        </td>
      </tr>
  
      <tr>
        <td valign="top" align="center"colspan="2">
            <br /><?php echo JText::_('VM_STORE_FORM_EMAIL') ?>:&nbsp;
            <a href="mailto:<?php echo $mf_email; ?>"><?php echo $mf_email; ?></a>
            <br />
            <br /><a href="<?php echo $mf_url ?>" target="_blank"><?php echo $mf_url ?></a><br />
        </td>
      </tr>
      <tr>
        <td valign="top" align="left" colspan="2">
            <br /><?php echo $mf_desc ?><br />
        </td>
      </tr>
    
  </table>