<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: admin.module_form.php 1760 2009-05-03 22:58:57Z Aravot $
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
global $perm;
$module_id = JRequest::getVar(  'module_id' );
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

if (!empty( $module_id )) {
  $q = "SELECT * from #__{vm}_module where module_id='$module_id'";
  $db->query($q);
  $db->next_record();
}

//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_MODULE_FORM_LBL') );
//Then Start the form
$formObj->startForm();
?> 
<table class="adminform">
    <tr> 
      <td width="24%" align="right" ><?php echo JText::_('VM_MODULE_FORM_NAME') ?>:</td>
      <td width="76%" > 
        <input type="text" class="inputbox" name="module_name" value="<?php echo $db->sf("module_name") ?>" size="32" <?php if( $ps_module->is_core( $db->f("module_name"))) { echo 'readonly="readonly"'; } ?> />
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right" ><?php echo JText::_('VM_MODULE_FORM_PERMS') ?>:</td>
      <td width="76%" > 
        <?php
        $module_perms = explode( ',', $db->f("module_perms") );
        $perm->list_perms( 'module_perms', $module_perms, 5, true );
        ?>
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right" ><?php echo JText::_('VM_MODULE_FORM_MENU') ?>:</td>
      <td width="76%">
      	<?php echo ps_html::yesNoSelectList('published', $db->f('published'), '1', '0') ?> 
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right"><?php echo JText::_('VM_MODULE_FORM_ORDER') ?>:</td>
      <td width="76%" > 
        <input type="text" class="inputbox" name="list_order" size="3" maxlength="2" value="<?php $db->sp("list_order") ?>" />
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2" >&nbsp; </td>
    </tr>
    <tr> 
      <td valign="top" align="right" ><?php echo JText::_('VM_MODULE_FORM_DESCRIPTION') ?>:</td>
      <td valign="top" >&nbsp;</td>
    </tr>
    <tr align="center"> 
      <td valign="top" colspan="2" > 
        <textarea name="module_description" cols="50" rows="10"><?php $db->sp("module_description") ?></textarea>
      </td>
    </tr>
    <tr> 
      <td width="24%" >&nbsp;</td>
      <td width="76%" >&nbsp;</td>
    </tr>
    <tr> 
      <td valign="top" colspan="2" align="center">&nbsp;</td>
    </tr>
    
  </table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'module_id', $module_id );

$funcname = !empty($module_id) ? "moduleUpdate" : "moduleAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, 'admin.module_list', $option );
?>