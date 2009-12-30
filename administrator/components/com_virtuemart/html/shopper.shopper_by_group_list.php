<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: shopper.shopper_by_group_list.php 1760 2009-05-03 22:58:57Z Aravot $
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

<h2>List User by Group</h2> 
<?php $q = "SELECT * from #__users ";
$q .= "WHERE #__users.address_type='BT'";
$q .= "order by #__users.username"; 
$db->query($q);  
?> 
<table width="100%" class="adminlist">
  <tr > 
    <th width="23%"><?php echo JText::_('VM_SHOPPER_LIST_USERNAME') ?></th>
    <th width="46%"><?php echo JText::_('VM_SHOPPER_LIST_NAME') ?></th>
    <th width="31%"><?php echo JText::_('VM_SHOPPER_LIST_GROUP') ?></th>
  </tr>
  <tr> 
    <td colspan="3"> 
      <hr>
    </td>
  </tr>
  <?php
while ($db->next_record()) { ?> 
  <tr nowrap > 
    <td width="23%"><?php
$url = $PHP_SELF . "?page=admin.shopper_form&user_id=";
$url .= $db->f("id");
echo "<A HREF=" . $sess->url($url) . ">";
echo $db->f("username"); 
echo "</A>"; ?></td>
    <td width="46%"><?php echo $db->f("bill_first_name") . " ";
echo $db->f("bill_middle_name") . " ";
echo $db->f("bill_last_name"); ?></td>
    <td width="31%">&nbsp;</td>
  </tr>
  <?php } ?> 
</table>
