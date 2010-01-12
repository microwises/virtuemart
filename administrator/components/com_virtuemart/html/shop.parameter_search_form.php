<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* Paramater search for phpShop
* @author Zdenek Dvorak (zdenek.dvorak@seznam.cz)
*
* @version $Id: shop.parameter_search_form.php 1760 2009-05-03 22:58:57Z Aravot $
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

$q  = "SELECT * FROM #__{vm}_product_type ";
$q .= "WHERE product_type_id='$product_type_id' ";
$q .= "AND published='1'";
$db->query($q);

$browsepage = $db->f("product_type_browsepage");

$vm_mainframe->setPageTitle( JText::_('VM_PARAMETER_SEARCH') );
$pathway[] = $vm_mainframe->vmPathwayItem( JText::_('VM_PARAMETER_SEARCH') );
$vm_mainframe->vmAppendPathway($pathway);

echo "<h2>".JText::_('VM_PARAMETER_SEARCH')."</h2>";

	if (!$db->next_record()) { // There is no published Product Type
		echo JText::_('VM_PARAMETER_SEARCH_BAD_PRODUCT_TYPE');
	}
	else {
		echo "<table width=\"100%\" border=\"0\">\n<tr><td width=\"40%\">";
		echo JText::_('VM_PARAMETER_SEARCH_IN_CATEGORY').": ".$db->f("product_type_name");
		// Reset form
		echo "</td><td align=\"center\">";
		echo "<form action=\"".$sess->url( $mm_action_url.basename($_SERVER['PHP_SELF']). "?page=shop.parameter_search_form&product_type_id=". $product_type_id ). "\" method=\"post\" name=\"reset\">\n";
		echo "<input type=\"submit\" class=\"button\" name=\"reset\" value=\"";
		echo JText::_('VM_PARAMETER_SEARCH_RESET_FORM') ."\">\n</form>";
		echo "</td><td width=\"40%\">&nbsp;</td></tr></table>\n";

?>

<form action="<?php echo URL ?>index.php" method="post" name="attr_search">
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="page" value="shop.browse" />
<input type="hidden" name="product_type_id" value="<?php echo $product_type_id ?>" />
<input type="hidden" name="Itemid" value="<?php echo $sess->getShopItemid() ?>" />
<br />

<?php 
	$q  = "SELECT * FROM #__{vm}_product_type_parameter ";
	$q .= "WHERE product_type_id=$product_type_id ";
	$q .= "ORDER BY parameter_list_order";
	$db->query($q);
	
	?>
	<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<?php
	/********************************************************
	** BrowsePage - You can use your tepmlate for searching:
	** 1) write file with html table (without tags <table> and </table>) and 
	**    take its name into variable browsepage in Product Type
	** 2) You can use this page from tag <!-- Default list of parameters - BEGIN --> to
	**    tag <!-- Default list of parameters - END --> and changed it.
	** 3) tag {product_type_<product_type_id>_<parameter_name>} will be replaced input field, or select field
	**    tag {product_type_<product_type_id>_<parameter_name>_comp} will be replaced comparison
	**        for this parameter. It is important for correct SQL question.
	**    tag {product_type_<product_type_id>_<parameter_name>_value} will be replaced value for this
	**        parameter (when you click on button "Change Parametes" in Browse page).
	********************************************************/
	if (!empty($browsepage)) { // show browsepage
		/** 
		*   Read the template file into a String variable.
		*
		* function read_file( $file, $defaultfile='') ***/
		$template = read_file( PAGEPATH."templates/".$browsepage.".php");
		//$template = str_replace( "{product_type_id}", $product_type_id, $template );	// If you need this, use it...
		while ($db->next_record()) {
			$item_name = "product_type_$product_type_id"."_".$db->f("parameter_name");
			$parameter_values=$db->f("parameter_values");
			$get_item_value = JRequest::getVar( $item_name, "");
			$get_item_value_comp = JRequest::getVar( $item_name."_comp", "");
			$parameter_type = $db->f("parameter_type");
			
			// Replace parameter value
			$template = str_replace( "{".$item_name."_value}", $get_item_value, $template );
				
			// comparison
			if (!empty($parameter_values) && $db->f("parameter_multiselect")=="Y") {
				if ($parameter_type == "V") { // type: Multiple Values
					// Multiple section List of values - comparison FIND_IN_SET
					$comp  = "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
					$comp .= "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
					$comp .= "<option value=\"find_in_set_all\"".(($get_item_value_comp=="find_in_set_all")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FIND_IN_SET_ALL')."</option>\n";
					$comp .= "<option value=\"find_in_set_any\"".(($get_item_value_comp=="find_in_set_any")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FIND_IN_SET_ANY')."</option>\n";
					$comp .= "</select></td>";
				}
				else { // type: all other
					// Multiple section List of values - no comparison
					$comp = "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"in\" />\n</td>\n";
				}
			}
			else {
				switch( $parameter_type ) {
					case "C": // Char
						if (!empty($parameter_values)) { // List of values - no comparison
							$comp = "<input type=\"hidden\" name=\"".$item_name."_comp\" value=\"eq\" />\n";
							break;
						}
					case "I": // Integer
					case "F": // Float
					case "D": // Date & Time
					case "A": // Date
					case "M": // Time
						$comp  = "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
						$comp .= "<option value=\"lt\"".(($get_item_value_comp=="lt")?" selected":"").">&lt;</option>\n";
						$comp .= "<option value=\"le\"".(($get_item_value_comp=="le")?" selected":"").">&lt;=</option>\n";
						$comp .= "<option value=\"eq\"".(($get_item_value_comp=="eq")?" selected":"").">=</option>\n";
						$comp .= "<option value=\"ge\"".((empty($get_item_value_comp)||$get_item_value_comp=="ge")?" selected":"").">&gt;=</option>\n";
						$comp .= "<option value=\"gt\"".(($get_item_value_comp=="gt")?" selected":"").">&gt;</option>\n";
						$comp .= "<option value=\"ne\"".(($get_item_value_comp=="ne")?" selected":"").">&lt;&gt;</option>\n";
						$comp .= "</select>\n";
						break;
					case "T": // Text
						if (!empty($parameter_values)) { // List of values - no comparison
							$comp = "<input type=\"hidden\" name=\"".$item_name."_comp\" value=\"texteq\" />\n";
							break;
						}
						$comp  = "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
						$comp .= "<option value=\"like\"".(($get_item_value_comp=="like")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_LIKE')."</option>\n";
						$comp .= "<option value=\"notlike\"".(($get_item_value_comp=="notlike")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_NOT_LIKE')."</option>\n";
						$comp .= "<option value=\"fulltext\"".(($get_item_value_comp=="fulltext")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FULLTEXT')."</option>\n";
						$comp .= "</select>";
						break;
					case "S": // Short Text
					default:  // Default type Short Text
						if (!empty($parameter_values)) { // List of values - no comparison
							$comp = "<input type=\"hidden\" name=\"".$item_name."_comp\" value=\"texteq\" />\n";
							break;
						}
						$comp  = "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
						$comp .= "<option value=\"like\"".(($get_item_value_comp=="like")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_LIKE')."</option>\n";
						$comp .= "<option value=\"notlike\"".(($get_item_value_comp=="notlike")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_NOT_LIKE')."</option>\n";
						$comp .= "</select></td>";
				}
			}
			// Relace parameter comparison
			$template = str_replace( "{".$item_name."_comp}", $comp, $template );
			
			// Parameter field
			if (!empty($parameter_values)) { // List of values
				$fields=explode(";",$parameter_values);
				$attr = "<select class=\"inputbox\" name=\"$item_name";
				if ($db->f("parameter_multiselect")=="Y") {
					$size = min(count($fields),6);
					$attr .= "[]\" multiple size=\"$size\">\n";
					$selected_value = array();
					$get_item_value = JRequest::getVar( $item_name, array());
					foreach($get_item_value as $value) {
						$selected_value[$value] = 1;
					}
					foreach($fields as $field) {
						$attr .= "<option value=\"$field\"".(($selected_value[$field]==1) ? " selected>" : ">"). $field."</option>\n";
					}
				}
				else {
					$attr .= "\">\n";
					$attr .= "<option value=\"\">".JText::_('VM_SELECT')."</option>\n";
					foreach($fields as $field) {
						$attr .= "<option value=\"$field\"".(($get_item_value==$field) ? " selected>" : ">"). $field."</option>\n";
					}
				}
				$attr .= "</select>";
			}
			else { // Input field					
				switch( $parameter_type ) {
					case "I": // Integer
					case "F": // Float
					case "D": // Date & Time
					case "A": // Date
					case "M": // Time
						$attr = "<input type=\"text\" class=\"inputbox\"  name=\"$item_name\" value=\"$get_item_value\" size=\"20\" />";
						break;
					case "T": // Text
						$attr = "<textarea class=\"inputbox\" name=\"$item_name\" cols=\"35\" rows=\"6\" >$get_item_value</textarea>";
						break;
					case "C": // Char
						$attr = "<input type=\"text\" class=\"inputbox\"  name=\"$item_name\" value=\"$get_item_value\" size=\"5\" />";
						break;
					case "S": // Short Text
					default: // Default type Short Text
						$attr = "<input type=\"text\" class=\"inputbox\" name=\"$item_name\" value=\"$get_item_value\" size=\"50\" />";
				}
			}
			// Relace parameter
			$template = str_replace( "{".$item_name."}", $attr, $template );
		}
		echo $template;
	}
	else { // show default list of parameters
		echo "\n\n<!-- Default list of parameters - BEGIN -->\n";
		
		while ($db->next_record()) {
			$parameter_type = $db->f("parameter_type");
			if ($parameter_type!="B") {
				echo "<tr>\n  <td width=\"35%\" height=\"2\" valign=\"top\"><div align=\"right\"><strong>";
				echo $db->f("parameter_label");
			
				if ($db->f("parameter_description")) {
					echo "&nbsp;";
					echo mm_ToolTip($db->f("parameter_description"),JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DESCRIPTION'));
				}
				echo "&nbsp;:</strong></div>\n  </td>\n";
				
				$parameter_values=$db->f("parameter_values");
				$item_name = "product_type_$product_type_id"."_".$db->f("parameter_name");
				$get_item_value = JRequest::getVar( $item_name, "");
				$get_item_value_comp = JRequest::getVar( $item_name."_comp", "");
			
				
				// comparison
				if (!empty($parameter_values) && $db->f("parameter_multiselect")=="Y") {
					if ($parameter_type == "V") { // type: Multiple Values
						// Multiple section List of values - comparison FIND_IN_SET
						echo "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
						echo "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
						echo "<option value=\"find_in_set_all\"".(($get_item_value_comp=="find_in_set_all")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FIND_IN_SET_ALL')."</option>\n";
						echo "<option value=\"find_in_set_any\"".(($get_item_value_comp=="find_in_set_any")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FIND_IN_SET_ANY')."</option>\n";
						echo "</select></td>";
					}
					else { // type: all other
						// Multiple section List of values - no comparison
						echo "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"in\" />\n</td>\n";
					}
				}
				else {
					switch( $parameter_type ) {
						case "C": // Char
							if (!empty($parameter_values)) { // List of values - no comparison
								echo "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"eq\" />\n</td>\n";
								break;
							}
						case "I": // Integer
						case "F": // Float
						case "D": // Date & Time
						case "A": // Date
						case "M": // Time
							echo "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
							echo "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
							echo "<option value=\"lt\"".(($get_item_value_comp=="lt")?" selected":"").">&lt;</option>\n";
							echo "<option value=\"le\"".(($get_item_value_comp=="le")?" selected":"").">&lt;=</option>\n";
							echo "<option value=\"eq\"".(($get_item_value_comp=="eq")?" selected":"").">=</option>\n";
							echo "<option value=\"ge\"".((empty($get_item_value_comp)||$get_item_value_comp=="ge")?" selected":"").">&gt;=</option>\n";
							echo "<option value=\"gt\"".(($get_item_value_comp=="gt")?" selected":"").">&gt;</option>\n";
							echo "<option value=\"ne\"".(($get_item_value_comp=="ne")?" selected":"").">&lt;&gt;</option>\n";
							echo "</select></td>";
							break;
						case "T": // Text
							if (!empty($parameter_values)) { // List of values - no comparison
								echo "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"texteq\" />\n</td>\n";
								break;
							}
							echo "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
							echo "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
							echo "<option value=\"like\"".(($get_item_value_comp=="like")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_LIKE')."</option>\n";
							echo "<option value=\"notlike\"".(($get_item_value_comp=="notlike")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_NOT_LIKE')."</option>\n";
							echo "<option value=\"fulltext\"".(($get_item_value_comp=="fulltext")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_FULLTEXT')."</option>\n";
							echo "</select></td>";
							break;
						case "V": // Multiple Value
							echo "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"find_in_set\" />\n</td>\n";
							break;
						case "S": // Short Text
						default:  // Default type Short Text
							if (!empty($parameter_values)) { // List of values - no comparison
								echo "<td><input type=\"hidden\" name=\"".$item_name."_comp\" value=\"texteq\" />\n</td>\n";
								break;
							}
							echo "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
							echo "<select class=\"inputbox\" name=\"".$item_name."_comp\">\n";
							echo "<option value=\"like\"".(($get_item_value_comp=="like")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_LIKE')."</option>\n";
							echo "<option value=\"notlike\"".(($get_item_value_comp=="notlike")?" selected":"").">".JText::_('VM_PARAMETER_SEARCH_IS_NOT_LIKE')."</option>\n";
							echo "</select></td>";
					}
				}
				
				if (!empty($parameter_values)) { // List of values
					$fields=explode(";",$parameter_values);
					echo "<td width=\"55%\" height=\"2\" valign=\"top\">\n";
					echo "<select class=\"inputbox\" name=\"$item_name";
					if ($db->f("parameter_multiselect")=="Y") {
						$size = min(count($fields),6);
						echo "[]\" multiple size=\"$size\">\n";
						$selected_value = array();
						$get_item_value = JRequest::getVar( $item_name, array());
						foreach($get_item_value as $value) {
							$selected_value[$value] = 1;
						}
						foreach($fields as $field) {
							echo "<option value=\"$field\"".(($selected_value[$field]==1) ? " selected>" : ">"). $field."</option>\n";
						}
					}
					else {
						echo "\">\n";
						echo "<option value=\"\">".JText::_('VM_SELECT')."</option>\n";
						foreach($fields as $field) {
							echo "<option value=\"$field\"".(($get_item_value==$field) ? " selected>" : ">"). $field."</option>\n";
						}
					}
					echo "</select>";
				}
				else { // Input field					
					echo "<td width=\"55%\" height=\"2\">\n";
					switch( $parameter_type ) {
						case "I": // Integer
						case "F": // Float
						case "D": // Date & Time
						case "A": // Date
						case "M": // Time
							echo "<input type=\"text\" class=\"inputbox\"  name=\"$item_name\" value=\"$get_item_value\" size=\"20\" />";
							break;
						case "T": // Text
							echo "<textarea class=\"inputbox\" name=\"$item_name\" cols=\"35\" rows=\"6\" >$get_item_value</textarea>";
							break;
						case "C": // Char
							echo "<input type=\"text\" class=\"inputbox\"  name=\"$item_name\" value=\"$get_item_value\" size=\"5\" />";
							break;
						case "S": // Short Text
						default: // Default type Short Text
							echo "<input type=\"text\" class=\"inputbox\" name=\"$item_name\" value=\"$get_item_value\" size=\"50\" />";
					}
				}
				echo " ".$db->f("parameter_unit");
				switch( $parameter_type ) {
					case "D": // Date & Time
						echo " (".JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT')." ";
						echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT').")";
						break;
					case "A": // Date
						echo " (".JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT').")";
						break;
					case "M": // Time
						echo " (".JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT').")";
						break;
				}
			}
			else { // Break line (type == "B")
				echo "<tr>\n  <td colspan=\"3\" height=\"2\" ><hr>";
			}
			echo "  </td>\n</tr>";
			
		}
		echo "\n<!-- Default list of parameters - END -->\n\n";		
	}
	
	// Add search according to price:	
	$item_name = "price";
	$get_item_value = JRequest::getVar( $item_name, "");
	$get_item_value_comp = JRequest::getVar( $item_name."_comp", "");
	
	echo "<tr>\n  <td width=\"35%\" height=\"2\" valign=\"top\"><div align=\"right\"><strong>";
	echo JText::_('VM_CART_PRICE')."&nbsp;:</strong></div>\n  </td>\n";
	// comparison
	echo "<td width=\"10%\" height=\"2\" valign=\"top\" align=\"center\">\n";
	echo "<select class=\"inputbox\" name=\"price_comp\">";
	echo "<option value=\"lt\"".(($get_item_value_comp=="lt")?" selected":"").">&lt;</option>\n";
	echo "<option value=\"le\"".((empty($get_item_value_comp)||$get_item_value_comp=="le")?" selected":"").">&lt;=</option>\n";
	echo "<option value=\"eq\"".(($get_item_value_comp=="eq")?" selected":"").">=</option>\n";
	echo "<option value=\"ge\"".(($get_item_value_comp=="ge")?" selected":"").">&gt;=</option>\n";
	echo "<option value=\"gt\"".(($get_item_value_comp=="gt")?" selected":"").">&gt;</option>\n";
	echo "<option value=\"ne\"".(($get_item_value_comp=="ne")?" selected":"").">&lt;&gt;</option>\n";
	echo "</select></td>";
	// input text
	echo "\n<td> <input type=\"text\" class=\"inputbox\"  name=\"price\" value=\"$get_item_value\" size=\"20\" /></td>\n</tr>";	
	
	// Search Button
?>	
	<tr><td colspan="3" height="2" >&nbsp;</td></tr>
	<tr><td colspan="3" height="2" ><div align="center">
		<input type="submit" class="button" name="search" value="<?php echo JText::_('VM_SEARCH_TITLE') ?>">
		</div></td>
	</tr>
</table>
<?php
  } // end - There is a published Product Type
/** Changed Product Type - End */
?>
</form>
