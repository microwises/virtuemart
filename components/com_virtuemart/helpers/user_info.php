<?php
/**
 * Helper class to retrieve and store user info from and in session and tables.
 *
 * @package	VirtueMart
 * @subpackage User
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

require_once(JPATH_COMPONENT.DS.'helpers'.DS.'cart.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'user.php' );
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );

class user_info
{
	
	/**
	 * Take a data array and save any address info found in the array.
	 *
	 * @param array $_data (Posted) user data
	 * @param sting $_table Table name to write to, null (default) not to write to the database
	 * @param boolean $_cart True to write to the session (cart). Default: false
	 * @return boolean True if the save was successful, false otherwise.
	 */
	function storeAddress($_data, $_table = null, $_cart = false)
	{
		$_data = self::_prepareUserFields($_data, 'BT');
		if ($_table !== null) {
			$_userinfo   =& $this->getTable($_table);
			if (!$_userinfo->bind($_data)) {
				$this->setError($_userinfo->getError());
				return false;
			}
			if (!$_userinfo->store()) { // Write data to the DB
				$this->setError($_userinfo->getError());
				return false;
			}
		}

		if ($_cart) {
			self::saveAddressInCart($_data);
		}

		// Check for fields with the the 'shipto_' prefix; that means a (new) shipto address.
		$_shipto = array();
		$_pattern = '/^shipto_/';
		foreach ($_data as $_k => $_v) {
			if (preg_match($_pattern, $_k)) {
				$_new = preg_replace($_pattern, '', $_k);
				$_shipto[$_new] = $_v;
			}
		}
		if (count($_shipto) > 0) {
			$_shipto = self::_prepareUserFields($_shipto, 'ST');

			// The user_is_vendor must be copied to make sure users won't be listed twice
			$_shipto['user_is_vendor'] = $_data['user_is_vendor'];
			// Set the address type
			$_shipto['address_type'] = 'ST';

			if ($_table !== null) {
				if (!$_userinfo->bind($_shipto)) {
					$this->setError($_userinfo->getError());
					return false;
				}
				if (!$_userinfo->store()) { // Write data to the DB
					$this->setError($_userinfo->getError());
					return false;
				}
			}

			if ($_cart) {
				self::saveAddressInCart($_shipto, $_prepareUserFields);
			}
		}
		return true;
	}

	function _prepareUserFields($_data, $_type)
	{
		$_userFieldsModel = new VirtueMartModelUserfields();
		$_prepareUserFields = self::getUserFields($_type);
		
		// Format the data
		foreach ($_prepareUserFields as $_fld) {
			$_data[$_fld->name] = $_userFieldsModel->prepareFieldDataSave($_fld->type, $_fld->name, $_data[$_fld->name]);
		}
		return $_data;
	}

	function getUserFields($_type, $_dynamic = false)
	{
		// We need an instance here, since the getUserFields() method uses inherited objects and properties,
		// VirtueMartModelUserfields::getUserFields() won't work
		$_userFieldsModel = new VirtueMartModelUserfields();
		if ($_type == 'ST') {
			$_prepareUserFields = $_userFieldsModel->getUserFields(
									 'shipping'
									, array() // Default toggles
			);
		} else { // BT
			if ($_dynamic) {
				// The user is not logged in (anonymous), so we need tome extra fields
				$_prepareUserFields = $_userFieldsModel->getUserFields(
										 'account'
										, array() // Default toggles
										, array('delimiter_userinfo', 'username', 'password', 'password2', 'agreed') // Skips
				);
			} else {
				$_prepareUserFields = $_userFieldsModel->getUserFields(
										 'account'
										, array() // Default toggles
										, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed') // Skips
				);
			}
		}
		return $_prepareUserFields;
	}

	function getTestUserFields()
	{
		// We need an instance here, since the getUserFields() method uses inherited objects and properties,
		// VirtueMartModelUserfields::getUserFields() won't work
		$_userFieldsModel = new VirtueMartModelUserfields();
//		if ($_dynamic) {
			// The user is not logged in (anonymous), so we need tome extra fields
			$_prepareUserFields = $_userFieldsModel->getUserFields(
									 'shipping'
									, array('required'=>true,'delimiters'=>true,'captcha'=>true,'system'=>false)
				, array('delimiter_userinfo', 'username', 'password', 'password2', 'address_type_name','address_type','user_is_vendor') // Skips
									
			);
//		} else {
//			$_prepareUserFields = $_userFieldsModel->getUserFields(
//									 'shipping'
//									, array('required'=>true,'delimiters'=>true,'captcha'=>true,'system'=>false)
//									, array('delimiter_userinfo', 'username', 'email', 'password', 'password2') // Skips
//
//			);
//		}

		return $_prepareUserFields;
	}
	
	function saveAddressInCart($_data, $_fields, $_type)
	{
		$_cart = cart::getCart();
		$_address = array();
		foreach ($_fields as $_fld) {
			$_address[$_fld->name] = $_data->{$_fld->name};
		}
		if ($_type == 'ST') {
			$_cart['address_shipto_id'] = $_data->user_info_id;
		} else {
			$_cart['address_billto_id'] = $_data->user_info_id;
		}
		$_cart[$_type] = $_address;
		cart::setCart($_cart);
	}

	function address2cart ($_usr_inf_id, $_type)
	{
		$_usr = new VirtueMartModelUser();
		$_usr->setCurrent();
		$_address = $_usr->getUserAddress(0, $_usr_inf_id, '');
		$_userFields = self::getUserFields($_type);
		self::saveAddressInCart($_address[0], $_userFields, $_type);
	}

	function getAddress ($_model, $_fields, $_type)
	{
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'cart.php');
		$_cart = cart::getCart();
		$_address = new stdClass();
		if(!empty($_cart[$_type])){
			
			$_data = $_cart[$_type];

			foreach ($_data as $_k => $_v) {
				$_address->{$_k} = $_v;
			}
		}

		$_data = $_model->getUserFieldsByUser($_fields, $_address, (($_type == 'ST')?'shipto':''));
		return $_data;
	}
}
// No closing tag