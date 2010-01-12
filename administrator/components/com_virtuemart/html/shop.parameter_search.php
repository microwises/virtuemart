<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* Advanced Attributes search for phpShop
* @author Zdenek Dvorak (zdenek.dvorak@seznam.cz)
* @version $Id: shop.parameter_search.php 1760 2009-05-03 22:58:57Z Aravot $
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
$vm_mainframe->setPageTitle( JText::_('VM_PARAMETER_SEARCH') );

$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_ADVANCED_SEARCH'), $sess->url($mm_action_url.basename($_SERVER['PHP_SELF']).'?page=shop.parameter_search'));
$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_PARAMETER_SEARCH'));
$vm_mainframe->vmAppendPathway($pathway);
?>
<h2><?php echo JText::_('VM_PARAMETER_SEARCH') ?></h2>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
	<td><?php echo JText::_('VM_PARAMETER_SEARCH_TEXT1') ?></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>
		
<?php
	$q  = "SELECT * FROM #__{vm}_product_type ";
	$q .= "WHERE published='1' ";
	$q .= "ORDER BY product_type_list_order";
	$db->query($q);
	echo '<table width="100%" border="0" cellpadding="2" cellspacing="0">';
	while ($db->next_record()) {
		echo "<tr><td>";
		echo "<a href=\"".$sess->url( $mm_action_url."index.php?page=shop.parameter_search_form&product_type_id=".$db->f("product_type_id"))."\">";
		echo $db->f("product_type_name");
		echo "</a></td>\n<td>";
		echo $db->f("product_type_description");
		echo "</td></tr>";
	}
	echo "</table>\n";
	
	if ($db->num_rows() == 0) {
		echo JText::_('VM_PARAMETER_SEARCH_NO_PRODUCT_TYPE');
	}
?>
	</td>
</tr>
</table>
<!-- /** Changed Product Type - End */ -->

