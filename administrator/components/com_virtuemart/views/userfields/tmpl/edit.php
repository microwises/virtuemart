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
?>

<form id="adminform" action="index.php" method="post" name="adminForm" id="adminForm">


<div class="col50">
	<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_USERFIELD_DETAILS'); ?></legend>
	<table class="admintable">

		<tr>
			<td width="110" class="key">
				<label for="type">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_TYPE') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['type']; ?>
			</td>
		</tr>

		<!-- Start Type specific attributes -->
		<tr>
			<td colspan="2" style="text-align:left;height: 0px;overflow: auto;">
				<div id="divText">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_TEXTFIELD_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<tr>
								<td width="110" class="key">
									<label for="maxlength">
										<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_MAXLENGTH'); ?>:
									</label>
								</td>
								<td>
									<input class="inputbox" type="text" name="maxlength" id="maxlength" size="5" value="<?php echo $this->userField->maxlength; ?>" />
								</td>
							</tr>
						</table>
					</fieldset>
				</div>

				<div id="divColsRows">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_TEXTAREA_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<tr>
								<td width="110" class="key">
									<label for="cols">
										<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_COLUMNS'); ?>:
									</label>
								</td>
								<td>
									<input class="inputbox" type="text" name="cols" id="cols" size="5" value="<?php echo $this->userField->cols; ?>" />
								</td>
							</tr>
							<tr>
								<td width="110" class="key">
									<label for="rows">
										<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_ROWS'); ?>:
									</label>
								</td>
								<td>
									<input class="inputbox" type="text" name="rows" id="rows" size="5" value="<?php echo $this->userField->rows; ?>" />
								</td>
							</tr>
						</table>
					</fieldset>
				</div>

				<div id="divShopperGroups">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_EUVATID_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<tr>
								<td width="110" class="key">
									<label for="virtuemart_shoppergroup_id">
										<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_EUVATID_MOVESHOPPER'); ?>:
									</label>
								</td>
								<td>
									<?php echo $this->lists['shoppergroups'];?>
								</td>
							</tr>
						</table>
					</fieldset>
				</div>

				<div id="divAgeVerification">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_AGEVERIFICATION_ATTRIBUTES'); ?></legend>
						<table class="admintable">
							<tr>
								<td width="110" class="key">
									<label for="minimum_age">
										<?php echo JText::_('COM_VIRTUEMART_FIELDS_AGEVERIFICATION_MINIMUM'); ?>:
									</label>
								</td>
								<td>
									<?php echo $this->lists['minimum_age'];?>
								</td>
							</tr>
						</table>
					</fieldset>
				</div>

				<div id="divWeb">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_FIELDS_WEBADDRESS'); ?></legend>
						<table class="admintable">
							<tr>
								<td width="110" class="key">
									<label for="webaddresstype">
										<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_TYPE'); ?>:
									</label>
								</td>
								<td>
									<?php echo $this->lists['webaddresstypes'];?>
								</td>
							</tr>
						</table>
					</fieldset>
				</div>

				<div id="divValues" style="text-align:left;height: 200px;overflow: auto;">
					<fieldset>
					<legend><?php echo JText::_('COM_VIRTUEMART_USERFIELDS_ADDVALUES_TIP'); ?></legend>
						<input type="button" class="button" onclick="insertRow();" value="<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_ADDVALUE') ?>" />
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
			</td>
		</tr>
		<!-- End Type specific attributes -->

		<tr>
			<td width="110" class="key">
				<label for="name">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_NAME') ?>:
				</label>
			</td>
			<td>
				<input class="validate[required,funcCall[checkName]] inputbox" type="text" name="name" id="name" size="50" value="<?php
					echo $this->userField->name;
				?>" <?php
					echo ($this->userField->sys ? 'readonly="readonly"' : '');
				?> />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_TITLE') ?>:
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

		<tr>
			<td width="110" class="key">
				<label for="description">
					<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_DESCRIPTION') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->editor->display('description',  $this->userField->description, '100%;', '250', '75', '20', array('image', 'pagebreak', 'readmore') ) ; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="required">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_REQUIRED') ?>?:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['required']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="registration">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_REGISTRATION') ?>?:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['registration']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="account">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_ACCOUNT') ?>?:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['account']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="shipping">
					<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_SHIPPING') ?>?:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['shipping']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="readonly">
					<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_READONLY') ?>?:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['readonly']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="published">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED') ?>:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['published']; ?>
				</fieldset>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="size">
					<?php echo JText::_('COM_VIRTUEMART_USERFIELDS_SIZE') ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="size" id="size" size="5" value="<?php echo $this->userField->size; ?>" />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="virtuemart_vendor_id">
					<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['vendors'];?>
			</td>
		</tr>

	</table>
	</fieldset>
</div>
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="virtuemart_userfield_id" value="<?php echo $this->userField->virtuemart_userfield_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="valueCount" value="<?php echo $this->valueCount; ?>" />
<input type="hidden" name="controller" value="userfields" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php 
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>

<?php $duration = 500;
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
function insertRow() {
	var oTable = getObject("fieldValuesBody");
	var oRow, oCell ,oCellCont, oInput;
	var i, j;
	i=document.adminForm.valueCount.value;
	i++;
	// Create and insert rows and cells into the first body.
	oRow = document.createElement("TR");
	oTable.appendChild(oRow);

	oCell = document.createElement("TD");
	oInput=document.createElement("INPUT");
	oInput.name="vNames["+i+"]";
	oInput.setAttribute('id',"vNames_"+i);
	oCell.appendChild(oInput);
	oRow.appendChild(oCell);

	oCell = document.createElement("TD");
	oInput=document.createElement("INPUT");
	oInput.name="vValues["+i+"]";
	oInput.setAttribute('id',"vValues_"+i);
	oCell.appendChild(oInput);

	oRow.appendChild(oCell);
	oInput.focus();

	document.adminForm.valueCount.value=i;
}

function disableAll() {
	var elem;
	try{
		divValues.slideOut();
		divColsRows.slideOut();
		divWeb.slideOut();
		divShopperGroups.slideOut();
		divAgeVerification.slideOut();
		divText.slideOut();

	} catch(e){ }
	if (elem=getObject('vNames[0]')) {
		elem.setAttribute('mosReq',0);
	}
}

function toggleType( type ) {
	disableAll();
	<?php if (!$this->userField->sys) : ?>
	prep4SQL (document.adminForm.name);
	<?php endif; ?>
	setTimeout ('selType( \'' + type + '\' )', <?php echo ( $duration + 150 ) ?>);
}

function selType(sType) {
	var elem;
	switch (sType) {
		case 'editorta':
		case 'textarea':
			divText.toggle();
			divColsRows.toggle();
		break;

		case 'euvatid':
			divShopperGroups.toggle();
			break;
		case 'age_verification':
			divAgeVerification.toggle();
			break;

		case 'emailaddress':
		case 'password':
		case 'text':
			divText.toggle();
		break;

		case 'select':
		case 'multiselect':
			divValues.toggle();
			if (elem=getObject('vNames[0]')) {
				elem.setAttribute('mosReq',1);
			}
		break;

		case 'radio':
		case 'multicheckbox':
			divColsRows.toggle();
			divValues.toggle();
			if (elem=getObject('vNames[0]')) {
				elem.setAttribute('mosReq',1);
			}
		break;

		case 'webaddress':
			divWeb.toggle();
		break;

		case 'delimiter':
		default:
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
	if (jQuery('#adminform').validationEngine('validate')== true) submitform(pressbutton);
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
var divValues = new Fx.Slide('divValues' , {duration: <?php echo $duration;?> });
var divColsRows = new Fx.Slide('divColsRows' , {duration: <?php echo $duration;?> });
var divWeb = new Fx.Slide('divWeb' , {duration: <?php echo $duration;?> });
var divShopperGroups = new Fx.Slide('divShopperGroups' , {duration: <?php echo $duration;?> });
var divAgeVerification = new Fx.Slide('divAgeVerification' , {duration: <?php echo $duration;?> });
var divText = new Fx.Slide('divText' , {duration: <?php echo $duration;?> } );
toggleType('<?php echo $this->userField->type;?>');
</script>
