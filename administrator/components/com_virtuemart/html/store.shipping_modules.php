<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* This file lists all shipping modules. It's in a file that's not called shipping_module_list
* because we currently can't add or remove shipping modules automatically!
*
* @version $Id: store.shipping_modules.php 1760 2009-05-03 22:58:57Z Aravot $
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

require_once( CLASSPATH. "shippingMethod.class.php" );
$ps_shipping_method = new vmshippingMethod;

 ?>
 <table width="100%" cellspacing="0" cellpadding="4" border="0">
  <tr>
    <td>
      <br />&nbsp;&nbsp;&nbsp;<img src="<?php echo VM_ADMIN_ICON_URL ?>icon_48/vm_ups_48.png" border="0" />
      <br /><br />
    </td>
    <td><span class="sectionname"><?php echo JText::_('VM_SHIPPING_MODULE_LIST_LBL') ?></span></td>
  </tr>
</table>

<?php
$rows = $ps_shipping_method->method_list();
if ( !$rows ) {
     echo JText::_('VM_NO_SEARCH_RESULT');
}
else {
?>
  <table width="100%" class="adminlist">
    <tr> 
      <th width="20">#</th>
      <th width="20"><?php echo ucfirst(JText::_('VM_ENABLED')).'?' ?></th>
      <th class="title"><?php echo JText::_('VM_SHIPPING_MODULE_LIST_NAME') ?></th>
      <th class="title"><?php echo JText::_('VM_SHIPPING_MODULE_LIST_E_VERSION') ?></th>
      <th class="title"><?php echo JText::_('VM_SHIPPING_MODULE_LIST_HEADER_AUTHOR') ?></th>
      <th class="title"><?php echo JText::_('URL') ?></th>
      <th class="title"><?php echo JText::_('CMN_EMAIL') ?></th>
      <th class="title"><?php echo JText::_('VM_PRODUCT_DESC_TITLE') ?></th>
    </tr>
<?php
    $i = 0;
    global $PSHOP_SHIPPING_MODULES;
    foreach( $rows as $row ) {
    	$db->query("INSERT INTO #__{vm}_plugins (`name`, `element`, `folder`, `ordering`, `published`, `shopper_group_id`, `vendor_id`)
    	VALUES('".$row['name']."', '".$row['name']."','shipping', '$i', 0,5,1)");
      	$i++;
         ?> 
      <tr class="row<?php echo $i%2 ?>"> 
        <td><?php echo( $i ); ?></td>
        <td><?php 
          if( in_array($row['name'], $PSHOP_SHIPPING_MODULES ) )
            echo "<img src=\"$mosConfig_live_site/administrator/images/tick.png\" border=\"0\" alt=\"" . JText::_('VM_ISSHIP_LIST_PUBLISH_LBL') . "\"  align=\"center\"/>";
        ?></td>
        <td width="19%"><?php
        echo $row["name"];
        echo "<br/>"; 
        
        if( $row['name'] == "zone_shipping" ) {
        	echo "<a href=\"".$sess->url( $_SERVER['PHP_SELF']."?page=zone.zone_list" )."\">";
        }
        elseif( $row['name'] == "standard_shipping" ) {
        	echo "<a href=\"".$sess->url( $_SERVER['PHP_SELF']."?page=shipping.rate_list.php" )."\">";
        }
        elseif( $row['name'] == "no_shipping" ) {
        	//
        }	
        else {
              echo "<a href=\"".$sess->url( $_SERVER['PHP_SELF']."?page=store.shipping_module_form&shipping_module=".$row['name'] )."\">";
        }
        
        if( $row['name'] != 'no_shipping' ) {
        	echo JText::_('VM_ISSHIP_FORM_UPDATE_LBL')."</a>";
        }
        
          ?>
        </td>
        <td width="7%"><?php echo $row["version"]; ?></td>
        <td width="24%"><?php echo $row["author"]; ?></td>
        <td width="10%"><?php echo "<a target=\"_blank\" href=\"http://".$row["authorurl"]."\">".$row["authorurl"]."</a>"; ?>&nbsp;</td>
        <td width="10%"><?php echo $row["authoremail"]; ?>&nbsp;</td>
        <td width="50%"><?php echo $row["description"]; ?>&nbsp;</td>
      </tr>
  <?php 
  } 
?> 
</table>
<?php 

}
?>
