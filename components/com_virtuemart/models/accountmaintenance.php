<?php
/**
 * Default data model for Account maintenance
 *
 * @package     VirtueMart
 * @author      RolandD
 * @copyright   Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Default model class for Virtuemart
 *
 * @package	VirtueMart
 * @author RolandD
 *
 */
class VirtueMartModelAccountmaintenance extends JModel {
	
	/**
	* Save the shopper details
	*/
	public function saveShopper() {
		$db = JFactory::getDBO();
		$my = JFactory::getUser();
		//global $my, $perm, $sess, $vmLogger, $page;
		// $vmLogger->err( 'ps_shopper update' );
		/* Load the authorizations */
		$auth = JRequest::getVar('auth');
		/* Get the user id */
		$user_id = $auth['user_id'];
			
		/* Vendor is fixed */
		$vendor_id = 1;
		
		/* Load the posted data */
		$post = JRequest::get('post', 4);
		
		//require_once(CLASSPATH. 'ps_user.php' );
		if (empty($post['username'])) $post['username'] = $my->username;
		$post['name'] = $post['first_name'].' '.$post['last_name'];
		$post['id'] = $user_id;
		$post['gid'] = $my->gid;

		//I think this is now useless by Max Milbers
//		if ( VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) {
//			ps_user::saveUser( $d );
//		}
        
		$accountFields = shopFunctions::getUserFields('account', false, '', true);
		if (VmConfig::get('vm_registration_type') == 'SILENT_REGISTRATION' 
			|| VmConfig::get('vm_registration_type') == 'NO_REGISTRATION' 
			|| (VmConfig::get('vm_registration_type') == 'OPTIONAL_REGISTRATION' && empty($post['register_account'] ))) {
				$skipFields = array( 'username', 'password', 'password2');
		}
        if ($my->id > 0 
        	|| (VmConfig::get('vm_registration_type') != 'NORMAL_REGISTRATION' 
        	&& VmConfig::get('vm_registration_type') != 'OPTIONAL_REGISTRATION')) {
            	$skipFields = array( 'username', 'password', 'password2');
        }
		if ($my->id) $skipFields[] = 'email';
		
		/* Validate fields and check for missing required fields */
		$missing = array();
		$provided_required = true;
		foreach ($accountFields['required_fields'] as $name => $type)  {
			/* Skip the fields that do not need to be checked */
			if (in_array($name, $skipFields)) continue;
			
			switch ($type) {
				case 'age_verification':
					/**
					* The Age Verification here is just a simple check if the selected date
					* is a birthday older than the minimum age (default: 18)
					*/
					$post[$name] = JRequest::getInt('birthday_selector_year')
													.'-'.JRequest::getInt('birthday_selector_month')
													.'-'.JRequest::getInt('birthday_selector_day');
					$min_age = VmConfig::get('minimum_age', 18);
					$min_date = (date('Y') - $min_age).'-'.date('n').'-'.date('j');
					
					/* Age check */
					if ($post[$name] > $min_date) {
						$provided_required = false;
						$missing[] = JText::_($accountFields['allfields'][$name]);
					}
					break;
				case 'captcha':
					/** @todo Implement captcha again */
					break;
				case 'euvatid':
					/* Do nothing when the EU VAT ID field was left empty */
					if (empty($post[$name])) {
						$provided_required = false;
						$missing[] = JText::_($accountFields['allfields'][$name]);
					}
					else {
						/* Check the VAT ID against the validation server of the European Union */
						$post['isValidVATID'] = shopFunctions::validateEUVat($post[$name]);
						$post['__euvatid_field'] = $post[$name];
					}
					break; 
				default:
					if (empty($post[$name])) {
						$provided_required = false;
						$missing[] = JText::_($accountFields['allfields'][$name]);
					}
					break;
			}
		}
		
		/* Check for any missing fields */
		if (!$provided_required) return array(false, implode(', ', $missing));
		/* All fields are filled, lets store them */
		else {
			if (!shopFunctions::validateEmail($post['email'])) {
				//$vmLogger->err( 'Please provide a valide email address for the registration.' );
				return array(false, JText::_('REGWARN_MAIL'));
			}
			/* Set the access level */
			$post['perms'] = 'shopper';
			
			/* Clean up the user fields */
			$userfields_model = JRequest::getVar('userfields_model');
			foreach ($accountFields['details'] as $userField ) {
				if (!in_array($userField->name, $skipFields)) {
					$value = (array_key_exists($userField->name, $post)) ? $post[$userField->name] : null;
					$post[$userField->name] = $userfields_model->prepareFieldDataSave($userField->type, $userField->name, $value);
				}
			}
			/* Update the vm_user_info table */
			$table_user_info = $this->getTable('user_info');
			
			/* Bind the posted data */
			$post['mdate'] = time();
			/* Billing address is for now fixed to -default- */
			if ($post['address_type'] == 'BT') $post['address_type_name'] = '-default-';
			$table_user_info->bind($post);
			
			/* Store the new details */
			if ($table_user_info->store()) return array(true, '');
			else {
				?><pre><?php
				print_r($table_user_info);
				?></pre><?php
				exit;
				return array(false, '');
			}
			
			//Can be used here, because a validation was already done by Max Milbers
			// ps_user::setUserInfoWithEmail($fields,$user_id, " AND address_type='BT'");
	
	
			// UPDATE #__{vm}_shopper group relationship
			$q = "SELECT shopper_group_id FROM #__{vm}_shopper_vendor_xref ";
			$q .= "WHERE user_id = '".$user_id."'";
			$db->query($q);
	
			if (!$db->num_rows()) {
				//add
	
				$shopper_db = new ps_DB;
				// get the default shopper group
				$q =  "SELECT shopper_group_id from #__{vm}_shopper_group WHERE ";
				$q .= "`default`='1'";
				$shopper_db->query($q);
				if (!$shopper_db->num_rows()) {  // when there is no "default", take the first in the table
					$q =  "SELECT shopper_group_id from #__{vm}_shopper_group";
					$shopper_db->query($q);
				}
	
				$shopper_db->next_record();
				$my_shopper_group_id = $shopper_db->f("shopper_group_id");
				if (empty($d['customer_number'])) {
					$d['customer_number'] = "";
				}
	
				$q  = "INSERT INTO #__{vm}_shopper_vendor_xref ";
				$q .= "(user_id,vendor_id,shopper_group_id) ";
				$q .= "VALUES ('";
				$q .= $_SESSION['auth']['user_id'] . "','";
				$q .= $vendor_id. "','";
				$q .= $my_shopper_group_id. "')";
				$db->query($q);
			}
			//TODO In this table is stored the information of the userid of the vendor
			//so this must be worked out in a completly other way by Max Milbers
	//		$q = "SELECT user_id FROM #__{vm}_auth_user_vendor ";
	//		$q .= "WHERE user_id = '".$_SESSION['auth']['user_id']."'";
	//		$db->query($q);
	//		if (!$db->num_rows()) {
	//			// Insert vendor relationship
	//			$q = "INSERT INTO #__{vm}_auth_user_vendor (user_id,vendor_id)";
	//			$q .= " VALUES ";
	//			$q .= "('" . $_SESSION['auth']['user_id'] . "','";
	//			$q .= $_SESSION['ps_vendor_id'] . "') ";
	//			$db->query($q);
	//		}
	
			return True;
		}
	}
}
?>