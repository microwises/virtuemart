<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: store.export_form.php 1760 2009-05-03 22:58:57Z Aravot $
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
require_once( CLASSPATH . 'ps_export.php' );

$export_id = JRequest::getVar( 'export_id', "");
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

$vars['export_enabled'] = "Y";
$default['export_class'] = 'ps_xmlexport';

if (!empty($export_id)) {
	$q = "SELECT * FROM #__{vm}_export WHERE vendor_id='$hVendor_id' AND ";
	$q .= "export_id='$export_id'";
	$db->query($q);
	$db->next_record();
}

if ( $db->f("export_class") ) {

	if (include_once( CLASSPATH."export/".$db->f("export_class").".php" ))
	eval( "\$_EXPORT = new ".$db->f("export_class")."();");
} else {
	include_once( CLASSPATH."export/ps_xmlexport.php" );
	$_EXPORT = new ps_xmlexport();
}
//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_EXPORT_MODULE_FORM_LBL') );
//Then Start the form
$formObj->startForm();

?>
<br />
<?php
$tabs = new vmTabPanel(0, 1, 'exportform');
$tabs->startPane('content-pane');
$tabs->startTab( JText::_('VM_EXPORT_MODULE_FORM_LBL'), 'global-page');
?>
<table class="adminform">
    <tr class="row0">
      <td class="labelcell"><?php echo JText::_('VM_ISSHIP_LIST_PUBLISH_LBL') ?>?:</td>
      <td><input type="checkbox" name="export_enabled" class="inputbox" value="Y" <?php echo $db->sf('export_enabled')=="Y" ? "checked=\"checked\"" : "" ?> /></td>
    </tr>
    <tr class="row1"> 
      <td class="labelcell"><?php echo JText::_('VM_EXPORT_MODULE_FORM_NAME') ?>:</td>
      <td width="69%" > 
      <?php if ($db->f('iscore')) {
      	$db->sp('export_name');
      } else {?>
        <input type="text" class="inputbox" name="export_name" value="<?php $db->sp('export_name') ?>" size="32" />
        <?php } ?>
      </td>
    </tr> 
   <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_EXPORT_MODULE_FORM_DESC') ?>:</td>
      <td width="69%" > 
      <?php if ($db->f('iscore')) {
      	echo  nl2br($db->sf('export_desc'));
      } else { ?>
        <textarea class="inputbox" name="export_desc" cols="40" rows="8"><?php echo htmlspecialchars( $db->sf('export_desc') ); ?></textarea>
        <?php } ?>
      </td>
    </tr>
    <tr class="row1">
      <td class="labelcell"><?php
      echo JText::_('VM_EXPORT_CLASS_NAME');
          ?>
      </td>
      <td width="69%">
      <?php if ($db->f('iscore')) {
      	$db->sp('export_class');
      } else { 
      	echo ps_export::list_available_classes( 'export_class', ($db->sf("export_class") ? $db->sf("export_class") : $default['export_class']) );
      	echo mm_ToolTip( JText::_('VM_EXPORT_CLASS_NAME_TIP') ); 
      }?>
      </td>
    </tr>

    <tr class="row0"> 
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>
<?php
$tabs->endTab();
$tabs->startTab( JText::_('VM_CONFIG'), "config-page");

if( $_EXPORT->has_configuration() ) {
	$ps_html->writableIndicator( CLASSPATH."export/".$_EXPORT->classname.".cfg.php" );
}

$_EXPORT->show_configuration();

echo '<br />
<strong>'.JText::_('VM_EXPORT_CONFIG').':';
echo mm_ToolTip( JText::_('VM_EXPORT_CONFIG_TIP') )
	?>
<br />
<textarea class="inputbox" name="export_config" cols="120" rows="20"><?php echo htmlspecialchars( $db->sf('export_config') ); ?></textarea>
<?php
$tabs->endTab();
$tabs->endPane();

// Add necessary hidden fields
$formObj->hiddenField( 'export_id', $export_id );
$formObj->hiddenField( 'iscore', $db->sf('iscore') ); //prevents from deleting and editing description
if($db->sf('iscore')) {
	$formObj->hiddenField( 'export_name', $db->sf('export_name') );
	$formObj->hiddenField( 'export_desc', htmlspecialchars($db->sf('export_desc')) );
	$formObj->hiddenField( 'export_class', $db->sf('export_class') );
	
}

$funcname = !empty($export_id) ? "ExportUpdate" : "ExportAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.export_list', $option );
?>
