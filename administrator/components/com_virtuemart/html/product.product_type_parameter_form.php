<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_type_parameter_form.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @author Zdenek Dvorak <Zdenek.Dvorak@seznam.cz>
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
global $ps_product_type_parameter;

$product_type_id = JRequest::getVar( 'product_type_id', 0);
$parameter_name = JRequest::getVar( 'parameter_name', "");
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;
$parameter_type = "";

$q = "SELECT * from #__{vm}_product_type WHERE product_type_id='$product_type_id'";
$db->query($q);
$db->next_record();

$title = JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_LBL')."<br>";
$title .= JText::_('VM_PRODUCT_TYPE_LBL') . ": ". $db->f("product_type_name"); 

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm();

if (!$product_type_id || !$db->f("product_type_name")) {
	echo "<span class=\"sectionname\">";
	echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NOT_FOUND');
	echo " <a href=\"".$_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_type_list\"> [";
	echo JText::_('VM_PRODUCT_TYPE_LIST_LBL')." ]</a>";
	echo "</span>";
}
else {
    $edit_parametr = false;  // Parametr not exist and it is created
    if ($parameter_name) {
      $q  = "SELECT * FROM #__{vm}_product_type_parameter ";
      $q .= "WHERE product_type_id=".$product_type_id." ";
      $q .= "AND parameter_name='".$parameter_name."'";
      $db->query($q);
      if( $db->next_record() ) {
	  	$parameter_type = $db->f("parameter_type");
		$edit_parametr = true;  // Parametr exist and it is edited
	}
}
?> 

  <table class="adminform">
    <tr> 
      <td width="25%" nowrap><div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NAME') ?>
        <?php echo mm_ToolTip(JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_NAME_DESCRIPTION')) ?> :</div>
      </td>
      <td width="75%">
        <input type="text" class="inputbox" name="parameter_name" size="60" value="<?php $db->sp('parameter_name') ?>" />
      </td>
    </tr>
    <tr>
      <td valign="top"><div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_LABEL') ?>:</div></td>
      <td width="75%">
		<textarea class="inputbox" name="parameter_label" cols="60" rows="3" ><?php $db->sp("parameter_label") ?></textarea>
      </td>
    </tr>
    <tr> 
      <td width="25%" nowrap valign="top"><div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DESCRIPTION') ?>:</div></td>
      <td width="75%" valign="top">
		<?php
		editorArea( 'editor1', $db->f("parameter_description"), 'parameter_description', '450', '200', '60', '6' );
		?>
      </td>
    </tr>
    <tr>
      <td><div align="right"><?php echo JText::_('VM_MODULE_LIST_ORDER') ?>: </div></td>
      <td valign="top"><?php 
        echo $ps_product_type_parameter->list_order_parameter( $db->f("product_type_id"), $db->f("parameter_name"), $db->f("parameter_list_order"));
        echo "<input type=\"hidden\" name=\"currentpos\" value=\"".$db->f("parameter_list_order")."\" />";
        ?>
      </td>
    </tr>
    <tr>
      <td colspan="2"><br /></td>
    </tr>
    <tr>
      <td><div align="right"><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE') ?>: </div></td>
      <td valign="top">
        <select class="inputbox" name="parameter_type">
	  <option value="I" <?php if ($parameter_type == "I") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_INTEGER') ?>
	  </option>
	  <option value="T" <?php if ($parameter_type == "T") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TEXT') ?>
	  </option>
	  <option value="S" <?php if ($parameter_type == "S") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_SHORTTEXT') ?>
	  </option>
	  <option value="F" <?php if ($parameter_type == "F") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_FLOAT') ?>
	  </option>
	  <option value="C" <?php if ($parameter_type == "C") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_CHAR') ?>
	  </option>
	  <option value="D" <?php if ($parameter_type == "D") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATETIME') ?>
	  </option>
	  <option value="A" <?php if ($parameter_type == "A") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE') ?>
	  </option>
	  <option value="M" <?php if ($parameter_type == "M") echo "selected=\"selected\""; ?> >
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME') ?>
	  </option>
	  <option value="V" <?php if ($parameter_type == "V") echo "selected=\"selected\""; ?> >
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_MULTIVALUE') ?>
	  </option>
	  <option value="B" <?php if ($parameter_type == "B") echo "selected=\"selected\""; ?> > 
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_BREAK') ?>
	  </option>
	</select>
	<input type="hidden" name="parameter_old_type" value="<?php echo $parameter_type ?>" />
      </td>
    </tr>
    <tr> 
      <td width="25%" nowrap valign="top"><div align="right">
        <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_VALUES') ?>:</div>
      </td>
      <td width="75%" valign="top">
        <input type="text" class="inputbox" name="parameter_values" size="60" value="<?php $db->sp('parameter_values') ?>" />
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_VALUES_DESCRIPTION') ?></td>
    </tr>
    <tr> 
      <td width="25%" nowrap valign="top"><div align="right">
        <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_MULTISELECT') ?>:</div>
      </td>
      <td width="75%" valign="top">
	    <input type="checkbox" name="parameter_multiselect" value="Y" <?php if ($db->sf("parameter_multiselect")=="Y") echo "checked" ?>/>
      </td>
    </tr>
    <tr>
      <td colspan="2"><br /></td>
    </tr>
    <tr> 
      <td width="25%" nowrap valign="top"><div align="right">
        <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT') ?>:</div></td>
      <td width="75%" valign="top">
        <input type="text" class="inputbox" name="parameter_default" size="60" value="<?php $db->sp('parameter_default') ?>" />
      </td>
    </tr>
	<tr>
	  <td>&nbsp;</td>
	  <td>
	    <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_DEFAULT_HELP_TEXT') ?>
	  </td>
	</tr>
    <tr> 
      <td width="25%" nowrap valign="top"><div align="right">
        <?php echo JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_UNIT') ?>:</div></td>
      <td width="75%" valign="top">
        <input type="text" class="inputbox" name="parameter_unit" size="60" value="<?php $db->sp('parameter_unit') ?>" />
      </td>
    </tr>
  </table>

<?php 

// Add necessary hidden fields
$formObj->hiddenField( 'parameter_old_name', $parameter_name );
$formObj->hiddenField( 'product_type_id', $product_type_id );

$funcname = ($edit_parametr) ? "ProductTypeUpdateParam" : "ProductTypeAddParam";

// finally close the form:
$formObj->finishForm( $funcname, $modulename.'.product_type_parameter_list', $option );
}
/** Changed Product Type - End*/
?>  
