<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_userfield.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage classes
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

class ps_userfield extends vmAbstractObject {
	
	var $_key = 'fieldid';
	
	function validateOnSave( &$d ) {
		global $vmLogger;
		/*
		if( !$this->validate($d)) {
			return false;
		}*/
		switch($d['type']) {
			case 'date':
				$d['cType']='DATE';
				break;
			case 'editorta':
			case 'textarea':
			case 'multiselect':
			case 'multicheckbox':
				$d['cType']='MEDIUMTEXT';
				break;	
			case 'letterman_subscription':
			case 'yanc_subscription':
			case 'anjel_subscription':
			case 'ccnewsletter_subscription':
				// Set params = 
				$d['params'] = 'newsletter='.substr($d['type'],0,strpos($d['type'], '_') )."\n";
				$d['type'] = 'checkbox';
			case 'checkbox':
				$d['cType']='TINYINT';
				break;
			case 'euvatid':
				$d['params'] = 'shopper_group_id='.$d['shopper_group_id']."\n";
				$d['cType']='VARCHAR(255)';
				break;
			case 'age_verification':
				$d['params'] = 'minimum_age='.(int)$d['minimum_age']."\n";
			default:
				$d['cType']='VARCHAR(255)';
				break;
		}
		$db = new ps_DB();
		
		$sql="SELECT COUNT(*) as num_rows FROM `#__{vm}_userfield` WHERE name='".$db->getEscaped($d['name'])."'";
		if( !empty($d['fieldid'])) {
			$sql .= ' AND fieldid != '.intval($d['fieldid']);
		}
		$db->query($sql); $db->next_record();
		if($db->f('num_rows')) {
			$vmLogger->err( sprintf(JText::_('VM_USERFIELD_ERR_ALREADY'),$d['name']) );
			return false;
		}

		return true;
	}
	
	function saveField( &$d ) {
		global $my, $mosConfig_live_site;
		
		$db = new ps_DB();
		
		if ($d['type'] == 'webaddress') {
			$d['rows'] = $d['webaddresstypes'];
			if ( !(($d['rows'] == 0) || ($d['rows'] == 2)) ) {
				$d['rows'] = 0;
			}
		}

		$d['name'] = str_replace(" ", "", strtolower($d['name']));

		if( !$this->validateOnSave($d)) {
			return false;
		}
		// Prevent unpublishing and renaming of IMPORTANT Fields like "email", "username", "password",...
		$fieldObj = $this->get( $d['fieldid'] );
		if( $fieldObj !== false ) {
			if( in_array( $fieldObj->f('name'), $this->getSkipFields() )) {
				$d['name'] = $fieldObj->f('name');
				$d['required'] = $fieldObj->f('required');
				$d['published'] = $fieldObj->f('published');
			}
		}
		$fields = array(
					'name' => vmGet($d, 'name' ), 
					'title' => vmGet($d, 'title' ), 
					'description' => vmGet($d, 'description' ), 
					'type' => vmGet($d, 'type' ), 
					'maxlength' => vmGet($d, 'maxlength' ), 
					'size' => vmGet($d, 'size' ), 
					'required' => vmGet($d, 'required' ), 
					'ordering' => vmGet($d, 'ordering' ), 
					'cols' => vmGet($d, 'cols' ), 
					'rows' => vmGet($d, 'rows' ), 
					'value' => vmGet($d, 'value' ), 
					'default' => vmGet($d, 'default' ), 
					'published' => vmGet($d, 'published' ), 
					'registration' => vmGet($d, 'registration' ), 
					'shipping' => vmGet($d, 'shipping' ), 
					'account' => vmGet($d, 'account' ), 
					'readonly' => vmGet($d, 'readonly' ), 
					'calculated' => vmGet($d, 'calculated' ), 
					'params' => vmGet($d, 'params' ),
					'vendor_id' => vmGet($_SESSION, 'ps_vendor_id', 1 )
					 );
		if( !empty($d['fieldid']) ) {
			// existing record
			$db->buildQuery( 'UPDATE', '#__{vm}_userfield', $fields ,'WHERE `fieldid` ='. intval($d['fieldid'] ) );
			$db->query();
			
			if( $d['type'] != 'delimiter') {
				$this->changeColumn( $d['name'], $d['cType'], 'update');
			}

		} else {
			// add a new record			
			$sql="SELECT MAX(ordering) as max FROM #__{vm}_userfield";
			$db->query($sql); $db->next_record();
			$d['ordering'] = $db->f('max')+1;			

			$db->buildQuery( 'INSERT', '#__{vm}_userfield', $fields );
			$db->query();
			
			$_REQUEST['fieldid'] = $db->last_insert_id();
			if( $d['type'] != 'delimiter') {
				$this->changeColumn( $d['name'], $d['cType'], 'add');
			}
		}
		$fieldNames = vmGet( $d, 'vNames', array() );
		$fieldValues = vmGet( $d, 'vValues', array() );
		
		$j=1;
		if( !empty( $d['fieldid'] )) {
			$db->query( "DELETE FROM #__{vm}_userfield_values"
			. " WHERE fieldid=".(int)$d['fieldid'] );
		} else {
			$db->query( "SELECT MAX(fieldid) as max FROM `#__{vm}_userfield`" );
			$maxID=$db->loadResult();
			$d['fieldid']=$maxID;
		}
		$n=count( $fieldNames );
		for($i=0; $i <= $n; $i++) {
			if(trim($fieldNames[$i])!=null || trim($fieldNames[$i])!='') {
				$fields = array('fieldid' => (int)$d['fieldid'],
										'fieldtitle' => htmlspecialchars($fieldNames[$i]),
				 						'fieldvalue' => htmlspecialchars($fieldValues[$i]),
										'ordering' => $j );
				$db->buildQuery( 'INSERT', '#__{vm}_userfield_values', $fields );
				$db->query();
				$j++;
			}
		}
		$GLOBALS['vmLogger']->info(JText::_('VM_USERFIELD_SAVED'));
		return true;
	}
	/**
	 * Add, change or drop fields from the VirtueMart user tables
	 * Currently these are: #__{vm}_user_info, #__{vm}_order_user_info
	 * @param string $column
	 * @param string $type The column type is determined in the validateOnSave function
	 * @param string $action Can be: add, update or delete
	 */
	function changeColumn( $column, $type, $action) {
		
		switch( $action ) {
			case 'add': $action = 'ADD'; break;
			case 'update': 
			case 'change': 
				$action = 'CHANGE'; break;
			case 'delete': $action = 'DROP'; break;
			default: $action = 'ADD'; break;
		}
		$db = new ps_DB();
		// The general shopper information table
		$special = '';
		if( $action=='CHANGE') {
			$special = "`$column`";
		}
		$sql="ALTER TABLE `#__{vm}_user_info` $action `$column` $special $type";
		$db->query($sql);
		// The table where the shopper information at the time of an order is stored
		$sql="ALTER TABLE `#__{vm}_order_user_info` $action `$column` $special $type";
		$db->query($sql);
		
	}
	/**
	 * Remove a user field from the system
	 *
	 * @param int $cid
	 * @return boolean The result of the delete action
	 */
	function deleteField( &$d ) {
		global $db, $vmLogger;
		if( !is_array( @$d['fieldid'] )) {
			$d['fieldid'] = array( $d['fieldid']);
		}
		if ( count( @$d['fieldid'] ) < 1) {
			$vmLogger->err( JText::_('VM_USERFIELD_DELETE_SELECT') );
			return false;
		}

		foreach ($d['fieldid'] as $id) {
			$db->query('SELECT fieldid, name, title, ordering,sys FROM `#__{vm}_userfield` WHERE fieldid ='.intval($id));
			$db->next_record();
			
			if($db->f('sys')==1) {
				$vmLogger->err(sprintf(JText::_('VM_USERFIELD_DELETE_ERR_SYSTEM'),$db->f('name')));
				continue;
			}
			else {
				if( $db->f('type') != 'delimiter') {
					$this->changeColumn( $db->f('name'), '', 'delete');
				}
				
				$db->query('DELETE FROM `#__{vm}_userfield` WHERE fieldid='.(int)$id. ' LIMIT 1' );
				
				$db->query( 'UPDATE `#__{vm}_userfield` SET ordering = ordering-1 WHERE ordering > '.intval($db->f('ordering')));
				$vmLogger->info( sprintf(JText::_('VM_USERFIELD_DELETED'),$db->f('name')) );
			}
		}
		
		return true;
	}
	
	/**
	 * This allows us to print the user fields on
	 * the various sections of the shop
	 *
	 * @param array $rowFields An array returned from ps_database::loadObjectlist
	 * @param array $skipFields A one-dimensional array holding the names of fields that should NOT be displayed
	 * @param ps_DB $db A ps_DB object holding ovalues for the fields
	 * @param boolean $startform If true, print the starting <form...> tag
	 */
	function listUserFields( $rowFields, $skipFields=array(), $db = null, $startForm = true ) {
		global $mm_action_url, $ps_html, $my, $default, $mainframe, $vm_mainframe,
			$vendor_country_3_code, $mosConfig_live_site, $mosConfig_absolute_path, $page;
		
		$dbf = new ps_DB();
		
		if( $db === null ) {
			$db = new ps_DB();
		}
		$default['country'] = $vendor_country_3_code;
		
		$missing = JRequest::getVar(  'missing', '' );		

		// collect all required fields
		$required_fields = Array(); 
		foreach( $rowFields as $field ) {
			if( $field->required == 1 ) {
				$required_fields[$field->name] = $field->type;
			}
			$allfields[$field->name] = $field->name;
		}
		foreach( $skipFields as $skip ) {			
			unset($required_fields[$skip]); 
		}
		
		// Form validation function
		if( !vmIsAdminMode() ) {
			ps_userfield::printJS_formvalidation( $required_fields, $rowFields );
		} else {
			echo vmCommonHTML::scriptTag('', 'function submitregistration() { return true }');
		}
//		if( file_exists( $mosConfig_absolute_path .'/includes/js/mambojavascript.js') ) {
//			$vm_mainframe->addScript( 'includes/js/mambojavascript.js' );
//		}
		
		if( $startForm ) {
			echo '<form action="'. $mm_action_url .basename($_SERVER['PHP_SELF']) .'" method="post" name="adminForm">';
		}
		echo '
		<div style="width:90%;">';
			
		if( !empty( $required_fields ))  {
			echo '<div style="padding:5px;text-align:center;"><strong>(* = '.JText::_('CMN_REQUIRED').')</strong></div>';
		  	 
		}
		$delimiter = 0;
	   	foreach( $rowFields as $field) {
	   		if( !isset( $default[$field->name] )) {
	   			$default[$field->name] = $field->default;
	   		}
	   		$readonly = $field->readonly ? ' readonly="readonly"' : '';
	   		if( in_array( $field->name, $skipFields )) {
	   			continue;
	   		}
	   		// Title handling.
	   		$key = $field->title;
			if( $key[0] == '_') {
				$key = substr($key, 1, strlen($key)-1);
			}
	   		$field->title = JText::_($key);
	   		if( $field->name == 'agreed') {
	   			$field->title = '<script type="text/javascript">//<![CDATA[
				document.write(\'<label for="agreed_field">'. str_replace("'","\\'",JText::_('VM_I_AGREE_TO_TOS')) .'</label><a href="javascript:void window.open(\\\''. $mosConfig_live_site .'/index2.php?option=com_virtuemart&page=shop.tos&pop=1\\\', \\\'win2\\\', \\\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\\\');">\');
				document.write(\' ('.JText::_('VM_STORE_FORM_TOS') .')</a>\');
				//]]></script>
				<noscript>
					<label for="agreed_field">'. JText::_('VM_I_AGREE_TO_TOS') .'</label>
					<a target="_blank" href="'. $mosConfig_live_site .'/index.php?option=com_virtuemart&amp;page=shop.tos" title="'. JText::_('VM_I_AGREE_TO_TOS') .'">
					 ('.JText::_('VM_STORE_FORM_TOS').')
					</a></noscript>';
	   		}
	   		if( $field->name == 'username' && VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' ) {
				echo '<div class="formLabel">
						<input type="checkbox" id="register_account" name="register_account" value="1" class="inputbox" onchange="showFields( this.checked, new Array(\'username\', \'password\', \'password2\') );if( this.checked ) { document.adminForm.remember.value=\'yes\'; } else { document.adminForm.remember.value=\'yes\'; }" checked="checked" />
					</div>
					<div class="formField">
						<label for="register_account">'.JText::_('VM_REGISTER_ACCOUNT').'</label>
					</div>
					';
			} elseif( $field->name == 'username' ) {
				echo '<input type="hidden" id="register_account" name="register_account" value="1" />';
			}
	   		// a delimiter marks the beginning of a new fieldset and
	   		// the end of a previous fieldset
	   		if( $field->type == 'delimiter') {
	   			if( $delimiter > 0) {
	   				echo "</fieldset>\n";
	   			}
	   			if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' && $field->title == JText::_('VM_ORDER_PRINT_CUST_INFO_LBL') && $page == 'checkout.index' ) {
	   				continue;
	   			}
	   			echo '<fieldset>
				     <legend class="sectiontableheader">'.$field->title.'</legend>
';
	   			$delimiter++;
	   			continue;
	   		}
	   		
	   		echo '<div id="'.$field->name.'_div" class="formLabel ';
	   		if (stristr($missing,$field->name)) {
	   			echo 'missing';
	   		}
	   		echo '">';
	        echo '<label for="'.$field->name.'_field">'.$field->title.'</label>';
	        if( isset( $required_fields[$field->name] )) {
	        	echo '<strong>* </strong>';
	        }
	      	echo ' </div>
	      <div class="formField" id="'.$field->name.'_input">'."\n";
	      	
	      	/**
	      	 * This is the most important part of this file
	      	 * Here we print the field & its contents!
	      	 */
	   		switch( $field->name ) {
	   			case 'title':
	   				$ps_html->list_user_title($db->sf('title'), "id=\"title_field\"");
	   				break;
	   			
	   			case 'country':
	   				if( in_array('state', $allfields ) ) {
	   					$onchange = "onchange=\"changeStateList();\"";
	   				}
	   				else {
	   					$onchange = "";
	   				}
	   				$ps_html->list_country("country", $db->sf('country'), "id=\"country_field\" $onchange");
	   				break;
	   			
	   			case 'state':
//	   				echo $ps_html->list_states("vendor_state", $db->sf("vendor_state"));
	   				echo $ps_html->dynamic_state_lists( "country", "state", $db->sf('country'), $db->sf('state') );
				    echo "<noscript>\n";
				    $ps_html->list_states("state", $db->sf('state'), "", "id=\"state_field\"");
				    echo "</noscript>\n";
	   				break;
				case 'agreed':
					echo '<input type="checkbox" id="agreed_field" name="agreed" value="1" class="inputbox" />';
					break;
				case 'password':
				case 'password2':
					echo '<input type="password" id="'.$field->name.'_field" name="'.$field->name.'" size="30" class="inputbox" />'."\n";
		   			break;
					
	   			default:
	   				
	   				switch( $field->type ) {
	   					case 'date':
							echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/calendar.js');
//							if( vmIsJoomla( '1.5', '>=' ) ) {
								// in Joomla 1.5, the name of calendar lang file is changed...
								echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/lang/calendar-en-GB.js');
//							} else {
//								echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/lang/calendar-en.js');
//							}
							echo vmCommonHTML::linkTag( $mosConfig_live_site .'/includes/js/calendar/calendar-mos.css');
	   					
	   						$maxlength = $field->maxlength ? 'maxlength="'.$field->maxlength.'"' : '';
					        echo '<input type="text" id="'.$field->name.'_field" name="'.$field->name.'" size="'.$field->size.'" value="'. ($db->sf($field->name)?$db->sf($field->name):'') .'" class="inputbox" '.$maxlength . $readonly . ' />'."\n";
					        echo '<input name="reset" type="reset" class="button" onclick="return showCalendar(\''.$field->name.'_field\', \'y-mm-dd\');" value="..." />';
	   						break;
	   					case 'text':
	   					case 'emailaddress':
	   					case 'webaddress':
	   					case 'euvatid':	   						
	   						$maxlength = $field->maxlength ? 'maxlength="'.$field->maxlength.'"' : '';
					        echo '<input type="text" id="'.$field->name.'_field" name="'.$field->name.'" size="'.$field->size.'" value="'. ($db->sf($field->name)?$db->sf($field->name):'') .'" class="inputbox" '.$maxlength . $readonly . ' />'."\n";
				   			break;
				   			
						case 'textarea':
							echo '<textarea name="'.$field->name.'" id="'.$field->name.'_field" cols="'.$field->cols.'" rows="'.$field->rows.'" '.$readonly.'>'.$db->sf($field->name).'</textarea>';
							break;
							
						case 'editorta':
							editorArea( $field->name, $db->sf($field->name), $field->name, '300', '150', $field->cols, $field->rows );			
							break;
							
						case 'checkbox':
							echo '<input type="checkbox" name="'.$field->name.'" id="'.$field->name.'_field" value="1" '. ($db->sf($field->name) ? 'checked="checked"' : '') .'/>';
							break;
						case 'age_verification':
							$year = vmRequest::getInt('birthday_selector_year', date('Y'));
							if( $db->f($field->name) ) {
								$birthday = $db->f($field->name);
								$date_array = explode('-', $birthday );
								$year = $date_array[0];
								$month = $date_array[1];
								$day = $date_array[2];
							}
							ps_html::list_days('birthday_selector_day', vmRequest::getInt('birthday_selector_day', @$day));
							ps_html::list_month('birthday_selector_month', vmRequest::getInt('birthday_selector_month', @$month));							
							ps_html::list_year('birthday_selector_year', $year, $year-100, $year);
							break;
						case 'captcha':
							if (file_exists($mosConfig_absolute_path.'/administrator/components/com_securityimages/client.php')) {
								include ($mosConfig_absolute_path.'/administrator/components/com_securityimages/client.php');
								// Note that this package name must be used on the validation site too! If both are not equal, validation will fail
								$packageName = 'securityVMRegistrationCheck';
								echo insertSecurityImage($packageName);
								echo getSecurityImageText($packageName);
							}
							break;
						// Begin of a fallthrough
						case 'multicheckbox':
						case 'select':
						case 'multiselect':
						case 'radio':
							$k = $db->f($field->name);
							$dbf->setQuery( "SELECT fieldtitle,fieldvalue FROM #__{vm}_userfield_values"
							. "\n WHERE fieldid = ".$field->fieldid
							. "\n ORDER BY ordering" );
							$Values = $dbf->loadObjectList();
							$multi="";
							$rowFieldValues['lst_'.$field->name] = '';
							if($field->type=='multiselect') $multi="multiple='multiple'";		
							if(count($Values) > 0) {
								if($field->type=='radio') {
									$rowFieldValues['lst_'.$field->name] = vmCommonHTML::radioListTable( $Values, $field->name, 
										'class="inputbox" size="1" ', 
										'fieldvalue', 'fieldtitle', $k, $field->cols, $field->rows, $field->size, $field->required);
								} else {
									$ks=explode("|*|",$k);
									$k = array();
									foreach($ks as $kv) {
										$k[]->fieldvalue=$kv;
									}
									if($field->type=='multicheckbox') {
										$rowFieldValues['lst_'.$field->name] = vmCommonHTML::checkboxListTable( $Values, $field->name."[]", 
											'class="inputbox" size="'.$field->size.'" '.$multi, 
											'fieldvalue', 'fieldtitle', $k, $field->cols, $field->rows, $field->size, $field->required);
									} else {
										$rowFieldValues['lst_'.$field->name] = vmCommonHTML::selectList( $Values, $field->name."[]", 
											'class="inputbox" size="'.$field->size.'" '.$multi, 
											'fieldvalue', 'fieldtitle', $k);
									}
								}
							}
							// no break! still a fallthrough
							echo $rowFieldValues['lst_'.$field->name];
							break;
	   				}
	   				break;
	   		}
	   		if( $field->description != '') {
	   			echo vmToolTip( $field->description );
	   		}
	   		echo '<br /></div>
				      <br style="clear:both;" />';
	   }
		if( $delimiter > 0) {
			echo "</fieldset>\n";
		}
	   echo '</div>';
	   if( VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION') {
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
	}
	
	function prepareFieldDataSave($fieldType,$fieldName,$value=null) {
		global $_POST;
		$sqlFormat = "Y-m-d";
		switch($fieldType) {
			case 'date': 
				$value=vmGetUnEscaped($value);
				break;
			case 'webaddress':
				if (isset($_POST[$fieldName."Text"]) && ($_POST[$fieldName."Text"])) {
					$oValuesArr=array();
					$oValuesArr[0]=htmlspecialchars(str_replace(array('mailto:','http://','https://'),'',
									vmGetUnEscaped($value)));
					$oValuesArr[1]=htmlspecialchars(str_replace(array('mailto:','http://','https://'),'',
									vmGetUnEscaped((isset($_POST[$fieldName."Text"]) ? $_POST[$fieldName."Text"] : ""))));
					$value = implode("|*|",$oValuesArr);
				} else {
					$value= htmlspecialchars(str_replace(array('mailto:','http://','https://'),'',vmGetUnEscaped($value)));
				}
				break;
			case 'email': 
				$value=htmlspecialchars(str_replace(array('mailto:','http://','https://'),'',vmGetUnEscaped($value)));
				break;
			case 'editorta': 
				$value=vmGetUnEscaped($value);
				break;
			case 'multiselect':
			case 'multicheckbox':
			case 'select':
				if( is_array( $value )) { $value = implode("|*|",$value); }
				$value = htmlspecialchars( vmGetUnEscaped( $value ) );
				break;
			case 'delimiter':
				break;
			default:
				$value=htmlspecialchars(vmGetUnEscaped($value));
				break;
		}
		return $value;
		
	}
	/**
	 * This function allows you to get an object list of user fields
	 *
	 * @param string $section The section the fields belong to (e.g. 'registration' or 'account')
	 * @param boolean $required_only
	 * @param mixed $sys When left empty, doesn't filter by sys
	 * @return array
	 */
	function getUserFields( $section = 'registration', $required_only=false, $sys = '', $exclude_delimiters=false, $exclude_skipfields=false ) {
		$db = new ps_DB();
		
		$q = "SELECT f.* FROM `#__{vm}_userfield` f"
			. "\n WHERE f.published=1";
		if( $section != 'bank' && $section != '') {
			$q .= "\n AND f.`$section`=1";
		}
		elseif( $section == 'bank' ) {
			$q .= "\n AND f.name LIKE '%bank%'";
		}
		if( $exclude_delimiters ) {
			$q .= "\n AND f.type != 'delimiter' ";
			}
		if( $required_only ) {
			$q .= "\n AND f.required=1";
		}
		if( $sys !== '') {
			if( $sys == '1') { $q .= "\n AND f.sys=1"; }
			elseif( $sys == '0') { $q .= "\n AND f.sys=0"; }
		}
		if( $exclude_skipfields ) {
			$q .= "\n AND FIND_IN_SET( f.name, '".implode(',', ps_userfield::getSkipFields())."') = 0 ";
		}
		$q .= "\n ORDER BY f.ordering";
		
		$db->setQuery( $q );
		$userFields = $db->loadObjectList();
		
		return $userFields;
	}
	/**
	 * Returns an array of fieldnames which are NOT used for VirtueMart tables
	 *
	 * @return array Field names which are to be skipped by VirtueMart db functions
	 */
	function getSkipFields() {
		return array( 'username', 'password', 'password2', 'agreed' );
	}
	/**
	 * Prints a JS function to validate all fields
	 * given in the array $required_fields
	 * Does only test if non-empty (or if no options are selected)
	 * Includes a check for a valid email-address
	 *
	 * @param array $required_fields The list of form elements that are to be validated
	 * @param string $formname The name for the form element
	 * @param string $div_id_postfix The ID postfix to identify the label for the field
	 */
	function printJS_formValidation( $required_fields, $allfields, $formname = 'adminForm', $functioname='submitregistration', $div_id_postfix = '_div' ) {
        global  $page, $mainframe, $vm_mainframe;
        
        $field_list = implode( "','", array_keys( $required_fields ) );
        $field_list = str_replace( "'email',", '', $field_list );
        $field_list = str_replace( "'username',", '', $field_list );
        $field_list = str_replace( "'password',", '', $field_list );
        $field_list = str_replace( "'password2',", '', $field_list );
        
        echo '
            <script language="javascript" type="text/javascript">//<![CDATA[
            function '.$functioname.'() {
                var form = document.'.$formname.';
                var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");
                var isvalid = true;
                var required_fields = new Array(\''. $field_list.'\');
            	for (var i=0; i < required_fields.length; i++) {
                    formelement = eval( \'form.\' + required_fields[i] );
                    ';
       	echo "
                    if( !formelement ) { 
                            formelement = document.getElementById( required_fields[i]+'_field0' );
                            var loopIds = true;
                    }
                    if( !formelement ) { continue; }
                    if (formelement.type == 'radio' || formelement.type == 'checkbox') {
                        if( loopIds ) {
                                var rOptions = new Array();
                                for(var j=0; j<30; j++ ) {
                                        rOptions[j] = document.getElementById( required_fields[i] + '_field' + j );
                                        if( !rOptions[j] ) { break; }
                                }
                        } else {
                                var rOptions = form[formelement.getAttribute('name')];
                        }
                        var rChecked = 0;
                        if(rOptions.length > 1) {
                                for (var r=0; r < rOptions.length; r++) {
                                        if( !rOptions[r] ) { continue; }
                                        if (rOptions[r].checked) {      rChecked=1; }
                                }
                        } else {
                                if (formelement.checked) {
                                        rChecked=1;
                                }
                        }
                        if(rChecked==0) {
                        	document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                            isvalid = false;
                    	}
                    	else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
                        }                               
                    }
                    else if( formelement.options ) {
                        if(formelement.selectedIndex.value == '') {
                                document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                                isvalid = false;
                        } 
                        else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                                document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
                        }
                    }
                    else {
                        if (formelement.value == '') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className += ' missing';
                            isvalid = false;
                        }
                        else if (document.getElementById(required_fields[i]+'$div_id_postfix').className == 'formLabel missing') {
                            document.getElementById(required_fields[i]+'$div_id_postfix').className = 'formLabel';
	                    }
    	        	}
	            }
            ";
       	$optional_check = '';
		if( VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION') {
			$optional_check = '&& form.register_account.checked';
		}
	    // We have skipped email in the first loop above!
	    // Now let's handle email address validation
	    if( isset( $required_fields['email'] )) {
	    
	   		echo '
			if( !(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(form.email.value))) {
				alert( \''. str_replace("'","\\'",JText::_('REGWARN_MAIL',false)) .'\');
				return false;
			}';

		}
		if( isset( $required_fields['username'] )) {
		
			echo '
			if ((r.exec(form.username.value) || form.username.value.length < 3)'.$optional_check.') {
				alert( "'. sprintf(JText::_('VALID_AZ09',false), JText::_('USERNAME',false), 2) .'" );
				return false;
            }';
        }
        if( isset($required_fields['password']) ) {
			if( $page == 'checkout.index') {
                echo '
                if (form.password.value.length < 6 '.$optional_check.') {
                    alert( "'.JText::_('REGWARN_PASS',false) .'" );
					return false;
                } else if (form.password2.value == ""'.$optional_check.') {
                    alert( "'. JText::_('REGWARN_VPASS1',false) .'" );
                    return false;
                } else if (r.exec(form.password.value)'.$optional_check.') {
                    alert( "'. sprintf( JText::_('VALID_AZ09',false), JText::_('PASSWORD',false), 6 ) .'" );
                    return false;
                }';
        	}
            echo '
                if ((form.password.value != "") && (form.password.value != form.password2.value)'.$optional_check.'){
                    alert( "'. JText::_('REGWARN_VPASS2',false) .'" );
                    return false;
                }';
        }
        if( isset( $required_fields['agreed'] )) {
			echo '
            if (!form.agreed.checked) {
				alert( "'. JText::_('VM_AGREE_TO_TOS',false) .'" );
				return false;
			}';
		}
		foreach( $allfields as $field ) {		
			if(  $field->type == 'euvatid' ) {
				$euvatid = $field->name;
				break;
			}			
		}
		if( !empty($euvatid) ) {
			$vm_mainframe->addScript( 'components/'.VM_COMPONENT_NAME.'/js/euvat_check.js');
			echo '
			if( form.'.$euvatid.'.value != \'\' ) {
				if( !isValidVATID( form.'.$euvatid.'.value )) {
					alert( \''.addslashes(JText::_('VALID_EUVATID',false)).'\' );
					return false;
				}
			}';
		}
		// Finish the validation function
		echo '
			if( !isvalid) {
				alert("'.addslashes( JText::_('CONTACT_FORM_NC',false) ) .'" );
			}
			return isvalid;
		}
	            //]]>
	    </script>';
	}
}
?>
