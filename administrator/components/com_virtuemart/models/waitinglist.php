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
class VirtueMartModelWaitingList extends JModel {

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


}
// pure php no closing tag
