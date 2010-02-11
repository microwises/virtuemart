<?php
/**
*
* Account billing template
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
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

/**
* @todo Test optional registration
*/
?>
<div style="float:left;width:90%;text-align:right;"> 
    <span>
    	<a href="#" onclick="if(submitregistration()) {document.adminForm.submit(); return false;}">
    		<img border="0" src="administrator/images/save_f2.png" name="submit" alt="<?php echo JText::_('CMN_SAVE') ?>" />
    	</a>
    </span>
    <span style="margin-left:10px;">
    <a href="<?php echo VmConfig::get('secureurl')."index.php?option=com_virtuemart&view=accountmaintenance"; ?>">
    		<img src="administrator/images/back_f2.png" alt="<?php echo JText::_('BACK') ?>" border="0" />
    	</a>
    </span>
</div>
<div style="width:90%;">
<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=accountmaintenance'); ?>" method="post" name="adminForm">
<?php
if (!empty($this->fields['required_fields']))  {
	echo '<div style="padding:5px;text-align:center;"><strong>(* = '.JText::_('CMN_REQUIRED').')</strong></div>';
}

/* Load the form validation code */
shopFunctions::printJsFormValidation($this->fields['required_fields'], $this->fields['details']);

$delimiter = 0;
foreach ($this->fields['details'] as $field) {
	/* Check if the field needs to be skipped */
	if (in_array($field->name, $this->skipfields)) continue;
	
	/* Set the title */
	$key = $field->title;
	if( $key[0] == '_') $key = substr($key, 1, strlen($key)-1);
	$field->title = JText::_($key);
	
	/* A delimiter marks the beginning of a new fieldset and the end of a previous fieldset */
	if ($field->type == 'delimiter') {
		if ($delimiter > 0) echo "</fieldset>\n";
		if (VmConfig::get('vm_registration_type') == 'SILENT_REGISTRATION' && $field->title == JText::_('VM_ORDER_PRINT_CUST_INFO_LBL')) continue;
		echo '<fieldset><legend class="sectiontableheader">'.$field->title.'</legend>';
		$delimiter++;
		continue;
	}
	
	/* No idea what this is for */
	// if (!isset($default[$field->name])) {
	// 	$default[$field->name] = $field->default;
	// }
		
	/* Set readonly status */
	$readonly = $field->readonly ? ' readonly="readonly"' : '';
	
	/**
	* Agreed field
	* @todo Add Term of Service popup
	*/
	if ($field->name == 'agreed') {
		$field->title = '<script type="text/javascript">//<![CDATA[
		document.write(\'<label for="agreed_field">'. str_replace("'","\\'",JText::_('VM_I_AGREE_TO_TOS')) .'</label><a href="javascript:void window.open(\\\''.JURI::root().'index2.php?option=com_virtuemart&page=shop.tos&pop=1\\\', \\\'win2\\\', \\\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\\\');">\');
		document.write(\' ('.JText::_('VM_STORE_FORM_TOS') .')</a>\');
		//]]></script>';
	}
	if( $field->name == 'username' && VmConfig::get('vm_registration_type') == 'OPTIONAL_REGISTRATION' ) {
		echo '<div class="formLabel">
			<input type="checkbox" id="register_account" name="register_account" value="1" class="inputbox" onchange="showFields( this.checked, new Array(\'username\', \'password\', \'password2\') );if( this.checked ) { document.adminForm.remember.value=\'yes\'; } else { document.adminForm.remember.value=\'yes\'; }" checked="checked" />
			</div>
			<div class="formField">
			<label for="register_account">'.JText::_('VM_REGISTER_ACCOUNT').'</label></div>';
	} 
	else if( $field->name == 'username' ) {
		echo '<input type="hidden" id="register_account" name="register_account" value="1" />';
	}
	
	/* Start the div for the label */
	echo '<div id="'.$field->name.'_div" class="formLabel ';
	/* Check for fields user did not fill in */
	if (stristr(JRequest::getVar('missing', '' ), $field->name)) echo JText::_('missing');
	echo '">';
	/* Add the label */
	echo '<label for="'.$field->name.'_field">'.$field->title.'</label>';
	if (array_key_exists($field->name, $this->fields['required_fields'])) echo '<strong>'.JText::_('VM_REQUIRED_FIELD').'</strong>';
	
	/* Finish label div and start field div */
	echo ' </div><div class="formField" id="'.$field->name.'_input">'."\n";
   	
	/**
	* This is the most important part of this file
	* Here we print the field & its contents!
	*/
	switch ($field->name) {
		case 'title':
			echo shopFunctions::listUserTitle($this->userinfo->title, 'id="title_field"');
			break;
		case 'country_id':
			echo shopFunctions::renderCountryList($this->userinfo->country_id);
			break;
		case 'state_id':
			echo shopFunctions::renderStateList($this->userinfo->state_id, $this->userinfo->country_id, 'country_id');
			break;
		case 'agreed':
			echo '<input type="checkbox" id="agreed_field" name="agreed" value="1" class="inputbox" />';
			break;
		case 'password':
		case 'password2':
			echo '<input type="password" id="'.$field->name.'_field" name="'.$field->name.'" size="30" class="inputbox" />'."\n";
			break;
		default:
			$value = $field->name;
			switch( $field->type ) {
				case 'date':
					$maxlength = $field->maxlength ? 'maxlength="'.$field->maxlength.'"' : '';
					echo JHTML::_( 'calendar', $this->userinfo->$value, $field->name, $field->name.'_field', '%d-%m-%Y', 'size="25"');
					break;
				case 'text':
				case 'emailaddress':
				case 'webaddress':
				case 'euvatid':	   						
					$maxlength = $field->maxlength ? 'maxlength="'.$field->maxlength.'"' : '';
					echo '<input type="text" id="'.$field->name.'_field" name="'.$field->name.'" size="'.$field->size.'" value="'.$this->userinfo->$value.'" class="inputbox" '.$maxlength . $readonly . ' />'."\n";
					break;
				case 'textarea':
					echo '<textarea name="'.$field->name.'" id="'.$field->name.'_field" cols="'.$field->cols.'" rows="'.$field->rows.'" '.$readonly.'>'.$this->userinfo->$value.'</textarea>';
					break;
				case 'editorta':
					echo $this->editor->display($field->name, $this->userinfo->$value, '100%;', '550', $field->cols, $field->rows, array('pagebreak', 'readmore')) ;
					break;
				case 'checkbox':
					$checked = ($this->userinfo->$value) ? 'checked="checked"' : '';
					echo '<input type="checkbox" name="'.$field->name.'" id="'.$field->name.'_field" value="1" '.$checked.'/>';
					break;
				case 'age_verification':
					if ($this->userinfo->$value) {
						$birthday = $this->userinfo->$value;
						$date_array = explode('-', $birthday );
						$year = $date_array[0];
						$month = $date_array[1];
						$day = $date_array[2];
					}
					else {
						$day = null;
						$month = null;
						$year = JRequest::getInt('birthday_selector_year', date('Y'));
					}
					echo shopFunctions::listDays('birthday_selector_day', JRequest::getInt('birthday_selector_day', $day));
					echo shopFunctions::listMonths('birthday_selector_month', JRequest::getInt('birthday_selector_month', $month));
					echo shopFunctions::listYears('birthday_selector_year', JRequest::getInt('birthday_selector_year', $year), $year-100, $year);
					break;
				case 'captcha':
					/** @todo implement new version */
					break;
				// Begin of a fallthrough
				case 'multicheckbox':
				case 'select':
				case 'multiselect':
				case 'radio':
					$k = $this->userinfo->$value;
					$Values = $field->values;
					$multi="";
					$rowFieldValues['lst_'.$field->name] = '';
					if ($field->type=='multiselect') $multi="multiple='multiple'";		
					if (count($Values) > 0) {
						if($field->type=='radio') {
							$rowFieldValues['lst_'.$field->name] = vmCommonHTML::radioListTable( $Values, $field->name, 
								'class="inputbox" size="1" ', 
								'fieldvalue', 'fieldtitle', $k, $field->cols, $field->rows, $field->size, $field->required);
						} 
						else {
							$ks=explode("|*|",$k);
							$k = array();
							foreach($ks as $kv) {
								$k[]->fieldvalue=$kv;
							}
							if($field->type=='multicheckbox') {
								$rowFieldValues['lst_'.$field->name] = shopFunctions::checkboxListTable( $Values, $field->name."[]", 
									'class="inputbox" size="'.$field->size.'" '.$multi, 
									'fieldvalue', 'fieldtitle', $k, $field->cols, $field->rows, $field->size, $field->required);
							} 
							else {
								$select_name = ($field->type == 'multiselect') ? $field->name."[]" : $field->name;
								$rowFieldValues['lst_'.$field->name] = JHTML::_('select.genericlist', $Values, $select_name, 
										'class="inputbox" size="'.$field->size.'" '.$multi, 
										'fieldvalue', 'fieldtitle', $k, false, true);
								}
							}
						}
						// no break! still a fallthrough
						echo $rowFieldValues['lst_'.$field->name];
						break;
				}
				break;
		}
		if ($field->description != '') echo JHTML::tooltip($field->description);
		echo '<br /></div><br style="clear:both;" />';
}
if( $delimiter > 0) {
	echo "</fieldset>\n";
}
echo '</div>';
if (VmConfig::get('vm_registration_type') == 'OPTIONAL_REGISTRATION') {
	echo '<script type="text/javascript">
	//<![CDATA[
   function showFields( show, fields ) {
	if( fields ) {
		for (i=0; i<fields.length;i++) {
			if( show ) {
				document.getElementById( fields[i] + \'_div\' ).style.display = \'\';
				document.getElementById( fields[i] + \'_input\' ).style.display = \'\';
			} else {
				document.getElementById( fields[i] + \'_div\' ).style.display = \'none\';
				document.getElementById( fields[i] + \'_input\' ).style.display = \'none\';
			}
		}
	}
   }
   try {
	showFields( document.getElementById( \'register_account\').checked, new Array(\'username\', \'password\', \'password2\') );
   } catch(e){}
   //]]>
   </script>';
}
?>

<div align="center">	
	<input type="submit" value="<?php echo JText::_('CMN_SAVE') ?>" class="button" onclick="return( submitregistration());" />
</div>
<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option'); ?>" />
  <input type="hidden" name="task" value="shopperupdate" />
  <input type="hidden" name="user_info_id" value="<?php echo $this->userinfo->user_info_id; ?>" />
  <input type="hidden" name="user_id" value="<?php echo $this->auth["user_id"] ?>" />
  <input type="hidden" name="address_type" value="BT" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>