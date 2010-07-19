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

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'user.php' );
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );

class user_info
{
	
	/**
	 * Take a data array and save any address info found in the array.
	 *
	 * @param array $_data (Posted) user data
	 * @param sting $_table Table name to write to, null (default) not to write to the database
	 * @param boolean $_cart Attention, this was deleted, the address to cart is now done in the controller (True to write to the session (cart))
	 * @return boolean True if the save was successful, false otherwise.
	 */
	function storeAddress($_data, $_table = null, $new = false)
	{
		
		$_data = self::_prepareUserFields($_data, 'BT',$new);
		if ($_table !== null) {
			$_userinfo   = $this->getTable($_table);
			if (!$_userinfo->bind($_data)) {
				$this->setError($_userinfo->getError());
				dump($_userinfo,'storeAddress bind ERROR $_userinfo');
				return false;
			}

			if (!$_userinfo->store()) { // Write data to the DB
				$this->setError($_userinfo->getError());
				dump($_userinfo,'storeAddress Store ERROR $_userinfo');
				return false;
			}	
		}
		
		
//		self::saveAddressInCart($_data,self::getUserFields('BT'),'BT');

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
				if (!$_userinfo->store()) { // Write data to the DB
					$this->setError($_userinfo->getError());
					dump($_userinfo,'storeAddress $_userinfo');
					return false;
				}
			}

//			if ($_cart) {
//				dump ($_shipto,'storeAddress self::saveAddressInCart ST');
//				self::saveAddressInCart($_shipto,self::getUserFields('ST'),'ST');
//			}
		}
		return true;
	}

	function _prepareUserFields($_data, $_type,$new)
	{
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
		dump($_prepareUserFields, 'getUserFields');
		return $_prepareUserFields;
	}


	function saveAddressInCart($_data, $_fields, $_type) {
		//JPATH_COMPONENT does not work, because it is used in FE and BE
		require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'cart.php');
		$_cart = cart::getCart();
		$_address = array();
		
		if(is_array($_data)){
			foreach ($_fields as $_fld) {
				$name = $_fld->name;
				$_address[$name] = $_data[$name];
				dump($_data,'saveAddressInCart is array '.$name);
			}
			
		} else {
			foreach ($_fields as $_fld) {
				$name = $_fld->name;
				$_address[$name] = $_data->{$name};
			}
			dump($_data,'saveAddressInCart is object');
		}
		dump($_data,'saveAddressInCart');
		$_cart[$_type] = $_address;
		cart::setCart($_cart);
	}

	function address2cartanonym ($data, $_type)
	{
//		$_userFields = self::getTestUserFields($_type);
		$_userFields = self::getUserFields($_type);
		self::saveAddressInCart($data, $_userFields, $_type);
	}


		
	function getAddress ($_model, $_fields, $_type)
	{
		//JPATH_COMPONENT does not work, because it is used in FE and BE
		require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'cart.php');
		$_cart = cart::getCart();
		$_address = new stdClass();
		if(!empty($_cart[$_type])){
			$_data = $_cart[$_type];
			foreach ($_data as $_k => $_v) {
				$_address->{$_k} = $_v;
			}
		}

//		$_data = $_model->getUserFieldsByUser($_fields, $_address, (($_type == 'ST')?'shipto_':''));
		$_data = $_model->getUserFieldsByUser($_fields, $_address);
		return $_data;
	}
	
	//	function saveAddressFromFormToCart($_data, $_fields, $_type) {
//		//JPATH_COMPONENT does not work, because it is used in FE and BE
//		require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'cart.php');
//		$_cart = cart::getCart();
//		$_address = array();
//		foreach ($_fields as $_fld) {
//			$name = $_fld->name;
//			$_address[$name] = $_data[$name];
//		}
//			
//		$_cart[$_type] = $_address;
//		cart::setCart($_cart);
//	}
	
//	function address2cart ($_usr_inf_id, $_type)
//	{
//		$_usr = new VirtueMartModelUser();
//		$_usr->setCurrent();
//		$_address = $_usr->getUserAddress(0, $_usr_inf_id, '');
//		$_userFields = self::getUserFields($_type);
//		echo '<br />self::saveAddressInCart address2cart<br />';
//		self::saveAddressInCart($_address[0], $_userFields, $_type);
//	}
}
// No closing tag