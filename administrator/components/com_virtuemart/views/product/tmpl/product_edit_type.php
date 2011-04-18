<?php
/**
*
* Handle the type
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_type.php 2978 2011-04-06 14:21:19Z alatak $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
	/* $typeTable  : Table to update/save for the product
	 * product_type_tables[XXX] has to converted to #__vm_product_type_XXX on save
	 */
	$typeTable = 'product_type_tables['.$type->product_type_id.']';
	/*class for valide engine javascript*/
	static $validate = array ( 'I'=>'onlyNumberSp', 'F'=>'number','D'=>'dateTime','A'=>'date','M'=>'time','T'=>'Text','S'=>'shortText],length[1,255','L'=>'link','U'=>'url','P'=>'phone');
	?>
	<input type="hidden" value="<?php echo $type->product_type_id; ?>" name="<?php echo $typeTable ?>" />
	<fieldset style="display:inline-block;float :left;width:48%;">
		<legend><?php echo $type->product_type_name ?> <?php echo $type->product_type_description ?></legend>
		
		<table class="adminform">
			<?php 
			$i = 0;
			
			foreach ($type->parameter as $p) {
				// TODO multi values for 1 parameter ?
				$value = $type->value[0][$p->parameter_name];
				$size = 20;
				
				?>

				<?php
				if ($p->parameter_type!="B") {
					$name = $typeTable.'['.$p->parameter_name.']';
					$id = ' id="id_'.$type->product_type_id.$p->parameter_name.'" ';
					echo "<tr class=\"row".$i++ % 2 . "\">\n  <td width=\"21%\" height=\"2\" valign=\"top\"><div style=\"text-align:right;font-weight:bold;\">";
					echo $p->parameter_label;
					echo ":</div>\n  </td>\n  <td width=\"79%\" valign=\"top\" >";

					if (!empty($p->parameter_values)) { // List of values
						$fields=explode(";",$p->parameter_values);
						$multiSelect ='';
						$selectFields = array() ;
						if ($p->parameter_type=="V") { //  Type: Multiple Values
							$multiSelect = 'multiple="multiple" size="'.count($fields).'"';
							$name .= '[]';
						}
						foreach($fields as $field){
								$selectFields[] = JHTML::_('select.option', $field, $field, $name);
								
							}
							if (!empty ($value)) $selected = explode(";",$value); 
							else $selected = '';
						echo JHTML::_('Select.genericlist', $selectFields, $name , $multiSelect, $name, 'text', $selected );
					}
					else { // Input field
						$class ='';
						switch( $p->parameter_type ) {
							case "S": // Short Text
							$size = 50;
							case "I": // Integer
							case "F": // Float
							case "D": // Date & Time
							case "A": // Date
							case "M": // Time

							$validateType = $validate[$p->parameter_type];
							echo '    <input type="text" class="inputbox validate[required,custom['.$validateType.']" '.$id.' name="'.$name.'" value="'.$value.'" size="'.$size.'" />';
							break;
							case "T": // Text

							echo '<textarea class="inputbox validate[required,custom[onlyLetterNumber]" '.$id.' name="'.$name.'" cols="35" rows="6" >';
							echo $value.'</textarea>';
							break;
							case "C": // Char
							echo '    <input type="text" class="inputbox validate[required,custom[onlyLetterNumber],length[1,1]" '.$id.' name="'.$name.'" value="'.$value.'" size="5" />';
							break;
							case "V": // Multiple Values
							echo '    <input type="text" class="inputbox"  name="'.$name.'" value="'.$value.'" size="20" />';
							break;
								default: // Default type Short Text
								echo '    <input type="text" class="inputbox" name="'.$name.'" value="'.$value.'" size="20" />';
							}
					}

					if ($p->parameter_description) {
						echo "&nbsp;";
						echo JHTML::tooltip($p->parameter_description);
					}
					echo " ".$p->parameter_unit;
					if ($p->parameter_default) {
						echo " (".jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT').": ";
						echo $p->parameter_default.")";
					}
					echo " [ ".jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE').": ";
					switch( $p->parameter_type ) {
						case "I": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_INTEGER'); break;	// Integer
						case "T": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TEXT'); break; 	// Text
						case "S": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_SHORTTEXT'); break; // Short Text
						case "F": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_FLOAT'); break; 	// Float
						case "C": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_CHAR'); break; 	// Char
						case "D": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATETIME')." ";	// Date & Time
						echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT')." ";
						echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT');
						break;
						case "A": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE')." ";		// Date
						echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE_FORMAT');
						break;
						case "M": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME')." ";		// Time
						echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME_FORMAT');
						break;
						case "V": echo jText::_('VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_TYPE_MULTIVALUE'); break; 	// Multiple Value
					}
					echo " ] ";
				}
				else {
					echo "<tr>\n  <td colspan='2' height='2' ><hr/>";
				}
				?>
				</td>
			</tr>

			<?php 
			}
		?>
		</table>
	</fieldset>
	