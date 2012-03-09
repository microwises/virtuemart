<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Userfields
* @author Oscar van Eijk
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

vmJsApi::JvalideForm();
AdminUIHelper::startAdminArea();
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_USERFIELD_DETAILS');
// vmdebug ('$this->userField',$this->userField);
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">


<div class="col50">
	<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_USERFIELD_DETAILS'); ?></legend>
	<table class="admintable" width="100%">
		<?php echo VmHTML::row('raw','COM_VIRTUEMART_FIELDMANAGER_TYPE', $this->lists['type'] ); ?>

		<!-- Start Type specific attributes -->
		<tr>
			<td colspan="2" style="text-align:left;overflow: auto;" id="toggler">
				<div id="divText">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_TEXTFIELD_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<?php echo VmHTML::row('input','COM_VIRTUEMART_USERFIELDS_MAXLENGTH','maxlength',$this->userField->maxlength,'class="inputbox"','',5); ?>
						</table>
					</fieldset>
				</div>

				<div id="divColsRows">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_TEXTAREA_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<?php echo VmHTML::row('input','COM_VIRTUEMART_USERFIELDS_COLUMNS','cols',$this->userField->cols,'class="inputbox"','',5); ?>
							<?php echo VmHTML::row('input','COM_VIRTUEMART_USERFIELDS_ROWS','rows',$this->userField->rows,'class="inputbox"','',5); ?>
						</table>
					</fieldset>
				</div>

				<div id="divShopperGroups">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_EUVATID_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<?php echo VmHTML::row('raw','COM_VIRTUEMART_USERFIELDS_EUVATID_MOVESHOPPER', $this->lists['shoppergroups'] ); ?>
						</table>
					</fieldset>
				</div>

				<div id="divAgeVerification" style="text-align:left;height: 280px;overflow: auto;">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_AGEVERIFICATION_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<?php echo VmHTML::row('raw','COM_VIRTUEMART_FIELDS_AGEVERIFICATION_MINIMUM', $this->lists['minimum_age'] ); ?>
						</table>
					</fieldset>
				</div>

				<div id="divWeb">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_WEBADDRESS'); ?></legend>
						<table class="admintable">
							<?php echo VmHTML::row('raw','COM_VIRTUEMART_FIELDMANAGER_TYPE', $this->lists['webaddresstypes'] ); ?>
						</table>
					</fieldset>
				</div>

				<div id="divValues" style="text-align:left;height: 200px;overflow: auto;">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_USERFIELDS_ADDVALUES_TIP'); ?></legend>
						<input type="button" class="button insertRow" value="<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_ADDVALUE') ?>" />
						<table align=left id="divFieldValues" cellpadding="4" cellspacing="1" border="0" width="100%" class="admintable">
							<thead>
								<tr>
									<th class="title" width="20%"><?php echo JText::_('COM_VIRTUEMART_TITLE') ?></th>
									<th class="title" width="80%"><?php echo JText::_('COM_VIRTUEMART_VALUE') ?></th>
								</tr>
							</thead>
							<tbody id="fieldValuesBody"><?php echo $this->lists['userfield_values'];?></tbody>
						</table>
					</fieldset>
				</div>
				<div id="divPlugin" style="text-align:left;height: 200px;overflow: auto;">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_USERFIELDS_PLUGIN_TIP'); ?></legend>
						<table class="admintable">
							<thead>
								<tr>
									<th class="title" width="20%"><?php echo JText::_('COM_VIRTUEMART_TITLE') ?></th>
									<th class="title" width="80%"><?php echo JText::_('COM_VIRTUEMART_VALUE') ?></th>
								</tr>
							</thead>
							<tbody id="fieldPluginBody">
							<?php
								echo $this->userFieldPlugin;
							?>
							</tbody>
						</table>
					</fieldset>
				</div>
			</td>
		</tr>
		<!-- End Type specific attributes -->

		<tr>
			<td width="110" class="key">
				<label for="name">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_NAME') ?>
				</label>
			</td>
			<td>
				<input  type="text" name="name" id="name" size="50" value="<?php
					echo $this->userField->name;
				?>" <?php
					echo ($this->userField->sys ? 'readonly="readonly"' : '');
					$readonly=$this->userField->sys ? 'readonly' : ''
				?> class="validate[required,funcCall[checkName]] inputbox <?php echo $readonly ?> " />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_TITLE') ?>
				</label>
			</td>
			<td>
                            <?php
                            $lang = JFactory::getLanguage();
                            $text = $lang->hasKey($this->userField->title) ? JText::_($this->userField->title) : $this->userField->title;
                            ?>
				<input class="inputbox" type="text" name="title" id="title" size="50" value="<?php echo $this->userField->title; ?>" />(<?php echo $text ?>)
			</td>
		</tr>
		<?php echo VmHTML::row('editor','COM_VIRTUEMART_USERFIELDS_DESCRIPTION','description', $this->userField->description,'100%','300', array('image','pagebreak', 'readmore') ); ?>

		<?php echo $this->lists['required']; ?>
		<?php echo $this->lists['registration']; ?>
		<?php echo $this->lists['account']; ?>
		<?php echo $this->lists['shipment']; ?>
		<?php echo $this->lists['readonly']; ?>
		<?php echo $this->lists['published']; ?>
		<?php echo VmHTML::row('input','COM_VIRTUEMART_USERFIELDS_SIZE','size',$this->userField->size,'class="inputbox"','',5); ?>
		<?php echo VmHTML::row('input','COM_VIRTUEMART_ORDERING','ordering',$this->userField->ordering,'class="inputbox"','',5); ?>
		<?php if(Vmconfig::get('multix','none')!=='none'){
				echo VmHTML::row('raw','COM_VIRTUEMART_VENDOR', $this->lists['vendors'] );
			} ?>
	</table>
	</fieldset>
</div>

<input type="hidden" name="virtuemart_userfield_id" value="<?php echo $this->userField->virtuemart_userfield_id; ?>" />
<input type="hidden" name="valueCount" value="<?php echo $this->valueCount; ?>" />
<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>

<?php $duration = 650;
$db = JFactory::getDBO();
$db->setQuery("SHOW COLUMNS FROM `#__virtuemart_userfields`");
$existingFields = '"'.implode('","',$db->loadResultArray()).'"';

?>
<script type="text/javascript">
function getObject(obj) {
	var strObj;
	if (document.all) {
		strObj = document.all.item(obj);
	} else if (document.getElementById) {
		strObj = document.getElementById(obj);
	}
	return strObj;
}

jQuery(".insertRow").click( function() {
	nr = jQuery('#fieldValuesBody tr').length ;
	row = '<tr><td><input type="text" name="vNames['+nr+']" value="Mr"></td><td><input type="text" name="vValues['+nr+']" value="Mr"></td></tr>';
	jQuery('#fieldValuesBody').append( row );
});

jQuery(".readonly").click( function(e) {
	return false})

jQuery('select#type').chosen().change(function() {
		selected = jQuery(this).find( 'option:selected').val() ;
	toggleType(selected)
})
function toggleType( sType ) {
	jQuery('#toggler').children('div').slideUp();
	jQuery('input[name="vNames[0]"]').attr("mosReq", 0);
	<?php if (!$this->userField->sys) : ?>
	prep4SQL (document.adminForm.name);
	<?php endif; ?>
	switch (sType) {
		case 'editorta':
		case 'textarea':
			jQuery('#divText').slideDown();
			jQuery('#divColsRows').slideDown();
		break;

		case 'euvatid':
			jQuery('#divShopperGroups').slideDown();
			break;
		case 'age_verification':
			jQuery('#divAgeVerification').slideDown();
			break;

		case 'emailaddress':
		case 'password':
		case 'text':
			jQuery('#divText').slideDown();
		break;

		case 'select':
		case 'multiselect':
			jQuery('#divValues').slideDown();
			jQuery('input[name="vNames[0]"]').attr("mosReq", 1);

		break;

		case 'radio':
		case 'multicheckbox':
			jQuery('#divColsRows').slideDown();
			jQuery('#divValues').slideDown();
			jQuery('input[name="vNames[0]"]').attr("mosReq", 1);

		break;

		case 'webaddress':
			jQuery('#divWeb').slideDown();
		break;

		case 'delimiter':
		break;
		default:
			jQuery('#divPlugin').slideDown();
		break;


	}
}
<?php if (! $this->userField->virtuemart_userfield_id ) { ?>
function checkName(field, rules, i, options){
	name = field.val();
	field.val(name.replace(/[^0-9a-zA-Z\_]+/g,''));
	var existingFields = new Array(<?php echo $existingFields ?>);
	if(jQuery.inArray(name,existingFields) > -1) {
		return options.allrules.onlyLetterNumber.alertText;
	}
	var pattern = new RegExp(/^[0-9a-zA-Z\_]+$/);
	if ( !pattern.test(name) ) {
		return options.allrules.onlyLetterNumber.alertText;
	}
}
<?php } ?>
function submitbutton(pressbutton) {
	if (pressbutton=='cancel') submitform(pressbutton);
	if (jQuery('#adminForm').validationEngine('validate')== true) submitform(pressbutton);
	else return false ;
}
function prep4SQL(o){
	if(o.value!='') {
		o.value=o.value.replace(/[^0-9a-zA-Z\_]+/g,'');
	}
}
<?php if($this->userField->virtuemart_userfield_id > 0) : ?>
document.adminForm.name.readOnly = true;
<?php endif; ?>
toggleType('<?php echo $this->userField->type;?>');

//<?php if ($this->userField->type !== "E") { ?>jQuery('#userField_plg').hide();<?php } ?>

    jQuery('#field_type').change(function () {
	var $selected = jQuery(this).val();
	if ($selected == "E" ) jQuery('#userField_plg').show();
	else { jQuery('#userField_plg').hide();
	    jQuery('#userfield_jplugin_id option:eq(0)').attr("selected", "selected");
	    jQuery('#userfield_jplugin_id').change();
	}

    });
    jQuery('#userfield_jplugin_id').change(function () {
	var $id = jQuery(this).val();
	jQuery('#plugin-Container').load( 'index.php?option=com_virtuemart&view=userfield&task=viewJson&format=json&userfield_jplugin_id='+$id , function() { jQuery(this).find("[title]").vm2admin('tips',tip_image) });

    });
</script>