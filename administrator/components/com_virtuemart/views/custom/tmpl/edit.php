<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: media_edit.php 3049 2011-04-17 07:01:44Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
vmJsApi::JvalideForm();
AdminUIHelper::startAdminArea();

?>
<form name="adminForm" id="adminForm" method="post" action="">
<fieldset>
<legend><?php echo JText::_('COM_VIRTUEMART_PRODUCT_CUSTOM_FIELD'); ?></legend>
<?php
$this->customfields->addHidden('view','custom');
$this->customfields->addHidden('task','');
$this->customfields->addHidden(JUtility::getToken(),1);
if ($this->custom->custom_parent_id) $this->customfields->addHidden('custom_parent_id',$this->custom->custom_parent_id);
$attribute_id = JRequest::getVar('attribute_id', '');
if(!empty($attribute_id)) $this->customfields->addHidden('attribute_id',$attribute_id);

echo $this->customfields->displayCustomFields('',$this->custom); ?>
<table class="adminform" id="custom_plg">
	<tr>
		<td class="labelcell"><?php echo JText::_('COM_VIRTUEMART_SELECT_CUSTOM_PLUGIN' ) ."<br /> ". $this->pluginList ?></td>


	</tr>
	<tr>
		<td>
		<div id="plugin-Container">
		<?php
		if($this->customPlugin->custom_jplugin_id){
				echo $this->customPlugin->custom_name .'<br />' ;
	        //$parameters = new vmParameters($this->paym->custom_params, JPATH_PLUGINS.DS.'vmcustom'.DS.basename($this->paym->custom_element).'.xml', 'plugin' );
                $parameters = new vmParameters($this->customPlugin->custom_params,  $this->customPlugin->custom_element , 'plugin' ,'vmcustom');
              // vmdebug ( 'csutomplg',$this->customPlugin->custom_element);
	        echo $rendered = $parameters->render(); ?>

			<?php
        } else { ?>
             <?php echo JText::_('COM_VIRTUEMART_SELECT_CUSTOM_PLUGIN' );
           } ?>
		<div>
		</td>
	</tr>
</table>
	</fieldset>
	<?php if($this->customPlugin->custom_jplugin_id){ ?>
		<input type="hidden" name="id" value="<?php echo $this->customPlugin->id ?>" >
	<?php } ?>
</form>
<script type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton=='cancel') submitform(pressbutton);
	if (jQuery('#adminForm').validationEngine('validate')== true) submitform(pressbutton);
	else return false ;
}
<?php if ( $this->custom->field_type !== "E" ){ ?>jQuery('#custom_plg').hide();<?php } ?>
jQuery('#field_type').change(function () {
	var $selected = jQuery(this).val();
	if ($selected == "E" ) jQuery('#custom_plg').show();
	else { jQuery('#custom_plg').hide();
		jQuery('#custom_jplugin_id option:eq(0)').attr("selected", "selected");
		jQuery('#custom_jplugin_id').change();
	}

});
jQuery('#custom_jplugin_id').change(function () {
	var $id = jQuery(this).val();
	jQuery('#plugin-Container').load( 'index.php?option=com_virtuemart&view=custom&task=viewJson&format=json&custom_jplugin_id='+$id , function() { jQuery(this).find("[title]").vm2admin('tips',tip_image) });

});
</script>
<?php AdminUIHelper::endAdminArea(); ?>