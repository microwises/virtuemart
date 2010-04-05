<?php
/**
*
* Default data model for Account maintenance
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

// Load the model framework
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
					/** @link http://code.google.com/p/joomla15captcha/ */
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
					switch ($name) {
						case 'state_id':
							$q = "SELECT COUNT(country_id) AS country 
								FROM #__vm_state 
								WHERE country_id = ".$post['country_id']." 
								GROUP BY country_id";
							$db->setQuery($q);
							$country = $db->loadResult();
							if ($country == 1) {
								if (empty($post[$name])) {
									$provided_required = false;
									$missing[] = JText::_($accountFields['allfields'][$name]);
								}
							}
							break;
						default:
							if (empty($post[$name])) {
								$provided_required = false;
								$missing[] = JText::_($accountFields['allfields'][$name]);
							}
							break;
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
			if ($table_user_info->store()) {
				/* Update the Joomla user information */
				$user = JUser::getInstance($post['user_id']);
				$user->set('email', $post['email']);
				$user->set('username', $post['username']);
				
				/* Handle the password */
				if (array_key_exists('password_field', $post) && array_key_exists('password2_field', $post) && $post['password_field'] == $post['password2_field']) {
					$salt		= JUserHelper::genRandomPassword(32);
					$crypt		= JUserHelper::getCryptedPassword($post['password_field'], $salt);
					$user->setParam('password', $crypt.':'.$salt);
				}
				/* Store the user */
				if ($user->save()) return array(true, '');
				else return array(false, '');
			}
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
	
	/**
	* Load all the shipping addresses 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return array of objects containing the shipping addresses
	*/
	public function getShippingAddresses() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$q = "SELECT *
			FROM #__vm_user_info
			WHERE address_type = 'ST'
			AND user_id = ".$user->id;
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	* Add a shipping addresses 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return bool true on successul add otherwise false
	*/
	public function getAddShippingAddress() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		$table = $this->getTable('user_info');
		$post = JRequest::get('post');
		$hash_secret = "VirtueMartIsCool";
		$timestamp = time();
		
		/* Bind the data */
		$table->bind($post);
		
		/* Some fine tuning  */
		$table->user_info_id = md5(uniqid($hash_secret));
		$table->address_type = 'ST';
		$table->cdate = $timestamp;
		$table->mdate = $timestamp;
		
		if ($table->store()) return true;
		else return false;
	}
	
	/**
	* Remove a shipping addresses 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return bool true on successul remove otherwise false
	*/
	public function getRemoveShippingAddress() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		$table = $this->getTable('user_info');
		
		/* Bind the data */
		$table->load(JRequest::getVar('user_info_id'));
		
		if ($table->delete()) return true;
		else return false;
	}
	
	/**
	* Load all the users orders 
	* 
	* @author RolandD
	* @return array of objects with order info
	*/
	public function getListOrders() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$q = "SELECT o.order_id, o.cdate, o.order_total, s.order_status_name
			FROM #__vm_orders o
			LEFT JOIN #__vm_order_status s
			ON s.order_status_code = o.order_status
			WHERE user_id = ".$user->id;
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	* Load the details of the selected order 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return object with the order details
	*/
	public function getOrderDetails() {
		$db = JFactory::getDBO();
		$order_id = JRequest::getInt('order_id');
		$q = "SELECT * 
			FROM #__vm_orders o
			LEFT JOIN #__vm_order_user_info u
			ON u.order_id = o.order_id
			LEFT JOIN #__vm_order_status s
			ON s.order_status_code = o.order_status
			WHERE o.order_id = ".$order_id;
		$db->setQuery($q);
		return $db->loadObject();
		
	}
	
	/**
	* Load the items of the selected order 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return array list of objects with the order items
	*/
	public function getOrderItemDetails() {
		$db = JFactory::getDBO();
		$order_id = JRequest::getInt('order_id');
		$q = "SELECT * 
			FROM #__vm_order_item
			WHERE order_id = ".$order_id;
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	* Load the items of the selected order 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @return array list of objects with the order items
	*/
	public function getOrderPayment() {
		$db = JFactory::getDBO();
		$order_id = JRequest::getInt('order_id');
		$q = "SELECT p.*, ".VmConfig::get('vm_decrypt_function', 'AES_DECRYPT')."(order_payment_number,'".VmConfig::get('encode_key')."') AS account_number, m.name
			FROM #__vm_order_payment p
			LEFT JOIN #__vm_payment_method m
			ON m.id = p.payment_method_id
			WHERE order_id = ".$order_id;
		$db->setQuery($q);
		return $db->loadObject();
	}
	
	/**
	* Proxy function for getting vendor information 
	* 
	* @author RolandD
	* @todo
	* @see 
	* @access public
	* @param 		
	* @return
	*/
	public function getVendor($vendor_id) {
		if (empty($vendor_id)) return false;
		else {
			JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
			$model = JModel::getInstance('Vendor', 'VirtueMartModel');
			return $model->getVendor($vendor_id);
		}
	}
}
?>