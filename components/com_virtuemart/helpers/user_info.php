<?php
/**
 * Helper class to retrieve and store user info from and in session and tables.
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk, Max Milbers
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


class user_info
{
	function __construct()
	{
	}

	/**
	 * Take a data array and save any address info found in the array.
	 *
	 * @param array $_data (Posted) user data
	 * @param sting $_table Table name to write to, null (default) not to write to the database
	 * @param boolean $_cart Attention, this was deleted, the address to cart is now done in the controller (True to write to the session (cart))
	 * @return boolean True if the save was successful, false otherwise.
	 */
	function storeAddress($_data, $_table = null, $new = false){

		$_data = self::_prepareUserFields($_data, 'BT',$new);
		if ($_table !== null) {
			$_userinfo   = $this->getTable($_table);
			if (!$_userinfo->bind($_data)) {
				$this->setError($_userinfo->getError());
				return false;
			}
			if (!$_userinfo->check($_data)) {

				$this->setError($_userinfo->getError());
				return false;
			}
			if (!$_userinfo->store()) { // Write data to the DB
				$this->setError($_userinfo->getError());
				return false;
			}
		} else {
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
			$_shipto = self::_prepareUserFields($_shipto, 'ST', $new);

			// The user_is_vendor must be copied to make sure users won't be listed twice
			$_shipto['user_is_vendor'] = $_data['user_is_vendor'];
			// Set the address type
			$_shipto['address_type'] = 'ST';

			if ($_table !== null) {
				if (!$_userinfo->bind($_shipto)) {
					$this->setError($_userinfo->getError());
					return false;
				}
				if (!$_userinfo->check($_data)) {
					$this->setError($_userinfo->getError());
					return false;
				}
				if (!$_userinfo->store()) { // Write data to the DB
					$this->setError($_userinfo->getError());
					return false;
				}
			}

		}
		return true;
	}

	function _prepareUserFields($_data, $_type,$new)
	{
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$_userFieldsModel = new VirtueMartModelUserfields();
		$_prepareUserFields = self::getUserFields($_type);
		$data =$_data;
		// Format the data
		foreach ($_prepareUserFields as $_fld) {
			$_data[$_fld->name] = $_userFieldsModel->prepareFieldDataSave($_fld->type, $_fld->name, $_data[$_fld->name],$data);
		}

		return $_data;
	}

	function getUserFields($_type, $_dynamic = false)
	{
		// We need an instance here, since the getUserFields() method uses inherited objects and properties,
		// VirtueMartModelUserfields::getUserFields() won't work

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$_userFieldsModel = new VirtueMartModelUserfields();
		if ($_type == 'ST') {
			$_prepareUserFields = $_userFieldsModel->getUserFields(
									 'shipping'
									, array() // Default toggles
			);
		} else { // BT
//			if ($_dynamic) {
				// The user is not logged in (anonymous), so we need tome extra fields
				$_prepareUserFields = $_userFieldsModel->getUserFields(
										 'account'
										, array() // Default toggles
										, array('delimiter_userinfo', 'name', 'username', 'password', 'password2', 'agreed','user_is_vendor') // Skips
				);
//			} else {
//				$_prepareUserFields = $_userFieldsModel->getUserFields(
//										 'account'
//										, array() // Default toggles
//										, array('delimiter_userinfo', 'name', 'username', 'email', 'password', 'password2', 'agreed') // Skips
//				);
//			}
		}
		return $_prepareUserFields;
	}


	function saveAddressInCart($_data, $_fields, $_type) {
		//JPATH_COMPONENT does not work, because it is used in FE and BE
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$_cart = VirtueMartCart::getCart();
		$_address = array();

		if(is_array($_data)){
			foreach ($_fields as $_fld) {
				$name = $_fld->name;
				$_address[$name] = $_data[$name];
			}

		} else {
			foreach ($_fields as $_fld) {
				$name = $_fld->name;
				$_address[$name] = $_data->{$name};
			}
		}

		$_cart->$_type = $_address;
		$_cart->setCartIntoSession();
	}

	function address2cartanonym ($data, $_type)
	{
		$_userFields = self::getUserFields($_type);
		self::saveAddressInCart($data, $_userFields, $_type);
	}



	function getAddress ($_model, $_fields, $_type)
	{
		//JPATH_COMPONENT does not work, because it is used in FE and BE
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$_cart = VirtueMartCart::getCart(false);
		$_address = new stdClass();
		if(!empty($_cart->$_type)){
			$_data = $_cart->$_type;
			foreach ($_data as $_k => $_v) {
				$_address->{$_k} = $_v;
			}
		}

//		$_data = $_model->getUserFieldsByUser($_fields, $_address, (($_type == 'ST')?'shipto_':''));
		$_data = $_model->getUserFieldsByUser($_fields, $_address);
		return $_data;
	}

}
// No closing tag