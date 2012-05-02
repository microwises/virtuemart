<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD, mwattier, pablo
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
* Model for VirtueMart Product Files
*
* @package	VirtueMart
* @author RolandD
*/
class VirtueMartModelWaitingList extends VmModel {

	/**
	* Load the customers on the waitinglist
	*/
	public function getWaitingusers($virtuemart_product_id) {
		
		if (!$virtuemart_product_id) { return false; }
		
		//Sanitize param
		$virtuemart_product_id  = (int) $virtuemart_product_id;
		
		$db = JFactory::getDBO();
		$q = 'SELECT name, username, virtuemart_user_id, notify_email, notified, notify_date FROM `#__virtuemart_waitingusers`
				LEFT JOIN `#__users` ON `virtuemart_user_id` = `id`
				WHERE `virtuemart_product_id`=' .$virtuemart_product_id ;
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	/**
	* Notify customers product is back in stock
	* @author RolandD
	* @author Christopher Rouseel
	* @todo Add Itemid &Itemid='.$sess->getShopItemid()
	* @todo Do something if the mail cannot be send
	* @todo Update mail from
	* @todo Get the from name/email from the vendor
	*/
	public function notifyList ($virtuemart_product_id) {
		if (!$virtuemart_product_id) { return false; }

		//sanitize id
		$virtuemart_product_id = (int)$virtuemart_product_id;
		
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$vars = array();

		$db = JFactory::getDBO();
		$q = "SELECT * FROM #__virtuemart_waitingusers ";
		$q .= "WHERE notified = '0' AND virtuemart_product_id = ".$virtuemart_product_id;
		$db->setQuery($q);
		$waiting_users = $db->loadObjectList();

		/* Load the product details */
		$q = "SELECT product_name FROM #__virtuemart_products WHERE virtuemart_product_id = ".$virtuemart_product_id;
		$db->setQuery($q);
		$vars['productName'] = $db->loadResult();

		/*TODO old URL here Now get the url information */
		$vars['url'] = JURI::root().JRoute::_('index.php?page=shop.product_details&flypage=shop.flypage&virtuemart_product_id='.$virtuemart_product_id.'&option=com_virtuemart');

		foreach ($waiting_users as $key => $waiting_user) {
			$vars['user'] = $waiting_user;
			if (shopFunctionsF::renderMail('waitinglist', $waiting_user->notify_email, $vars)) {
				$this->update($waiting_user->notify_email, $virtuemart_product_id);
			}
		}
		return true;
	}
	
	/**
	 * Add customer to the waiting list for specific product
	 *
	 * @author Seyi Awofadeju
	 * @return insert_id if the save was successful, false otherwise.
	 */
	public function adduser($data) {
		JRequest::checkToken() or jexit( 'Invalid Token, in notify customer');

		
		$field = $this->getTable('waitingusers');

		if (!$field->bind($data)) { // Bind data
			vmError($field->getError());
			return false;
		}

		if (!$field->check()) { // Perform data checks
			vmError($field->getError());
			return false;
		}

		$_id = $field->store();
		if ($_id === false) { // Write data to the DB
			vmError($field->getError());
			return false;
		}


		//jexit();
		return $_id ;
	}
/* 	public function getUsers($product_id ,$statut='waiting') {
		$StatutWhiteList = null;
		$statut ="";
		$order_stock_handle=null;
		$db = JFactory::getDBO();
		switch ($listType) {
			case 'waiting':
			// 
				$q = 'SELECT name, username, virtuemart_user_id, notify_email, notified, notify_date FROM `#__virtuemart_waitingusers`
					JOIN `#__users` ON `virtuemart_user_id` = `id`
					WHERE `virtuemart_product_id`=' .$virtuemart_product_id ;
				break;
			case 'delivered':
				// Only delivered product(stock Out), in most case Shipped;
				$order_stock_handle="O";
			case 'reserved':
				// Only booked,reserved product;
				if ($order_stock_handle===null) $order_stock_handle="R";
				$db->setQuery('SELECT `order_status_code` FROM `#__virtuemart_orderstates` WHERE `order_stock_handle`="'.$order_stock_handle.'"');
				if ( $StatutWhiteList = $db->loadResultArray() )
					$statut = ' AND order_status IN ( "'.implode ( '","' , $StatutWhiteList).'") ';
			case 'all':
				$q ='SELECT ou.* ,sum(product_quantity) as quantity FROM `#__virtuemart_order_userinfos` as ou 
					JOIN `#__virtuemart_order_items` AS oi using (`virtuemart_order_id`)
					WHERE ou.`address_type`="BT" AND oi.`virtuemart_product_id`='.(int)$product_id.$statut;
				$q.=' GROUP BY ou.`email` ORDER BY ou.`last_name` ASC';
				break;
		}
		$db->setQuery($q);
		$infos = $db->loadAssocList('virtuemart_order_userinfo_id');
		$customers = array();
		foreach ($infos as $key => $info)
		{
			$customers[$key] = array();
			$customers[$key]['customer_phone'] = !empty($info['phone_1']) ? $info['phone_1'] : (!empty($info['phone_2']) ? $info['phone_2'] :'-');
			$customers[$key]['customer_name']  = $info['first_name'].' '.$info['last_name'] ;
			$customers[$key]['email'] = $info['email'];
			$customers[$key]['mail_to'] = 'mailto:'.$info['email'];
			$customers[$key]['quantity'] = $info['quantity'];
		}
		return $customers;

	} */
	public function getProductShoppersByStatus($product_id,$states ) {

		if (empty( $states )) return false;
		$orderstatusModel = VmModel::getModel('orderstatus');
		$orderStates = $orderstatusModel->getOrderStatusNames();
		
		foreach ($states as &$status)
			if (!array_key_exists($status,$orderStates)) unset($status);
		if (empty( $states )) return false;

		$q ='SELECT ou.* ,sum(product_quantity) as quantity FROM `#__virtuemart_order_userinfos` as ou 
			JOIN `#__virtuemart_order_items` AS oi using (`virtuemart_order_id`)
			WHERE ou.`address_type`="BT" AND oi.`virtuemart_product_id`='.(int)$product_id;
		if (count($orderStates) !== count($states) ) 
			$q.=' AND order_status IN ( "'.implode ( '","' , $states).'") ';
		$q.=' GROUP BY ou.`email` ORDER BY ou.`last_name` ASC';
		$this->_db->setQuery($q);
		$infos = $this->_db->loadAssocList('virtuemart_order_userinfo_id');

		$customers = array();
		foreach ($infos as $key => $info)
		{
			$customers[$key] = array();
			$customers[$key]['customer_phone'] = !empty($info['phone_1']) ? $info['phone_1'] : (!empty($info['phone_2']) ? $info['phone_2'] :'-');
			$customers[$key]['customer_name']  = $info['first_name'].' '.$info['last_name'] ;
			$customers[$key]['email'] = $info['email'];
			$customers[$key]['mail_to'] = 'mailto:'.$info['email'];
			$customers[$key]['quantity'] = $info['quantity'];
		}
		return $customers;
	}
}
// pure php no closing tag
