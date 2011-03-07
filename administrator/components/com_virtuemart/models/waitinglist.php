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
	public function getWaitingusers() {
		$db = JFactory::getDBO();
		$q = 'SELECT name, username, user_id, notify_email, notified, notify_date FROM `#__vm_waiting_list`
				LEFT JOIN `#__users` ON `user_id` = `id`
				WHERE `product_id`=' . JRequest::getInt('product_id');
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	/**
	* Notify customers product is back in stock
	* @author RolandD
	* @todo Add Itemid &Itemid='.$sess->getShopItemid()
	* @todo Do something if the mail cannot be send
	* @todo Update mail from
	* @todo Get the from name/email from the vendor
	*/
	public function notifyList($product_id=false) {
		if (!$product_id) return false;
		else {
			$mainframe = Jfactory::getApplication('site');
			$db = JFactory::getDBO();

			$q = "SELECT * FROM #__vm_waiting_list ";
			$q .= "WHERE notified = '0' AND product_id = ".$product_id;
			$db->setQuery($q);
			$waiting_users = $db->loadObjectList();

			/* Load the product details */
			$q = "SELECT product_name FROM #__vm_product WHERE product_id = ".$product_id;
			$db->setQuery($q);
			$product_name = $db->loadResult();

			foreach ($waiting_users as $key => $waiting_user) {
				/* Lets make the e-mail up from the info we have */
				$notice_subject = JText::sprintf('PRODUCT_WAITING_LIST_EMAIL_SUBJECT', $product_name);

				/* Now get the url information */
				$url = JURI::root().JRoute::_('index.php?page=shop.product_details&flypage=shop.flypage&product_id='.$product_id.'&option=com_virtuemart');
				$notice_body = JText::sprintf('PRODUCT_WAITING_LIST_EMAIL_TEXT', $product_name, $url);

				/* Get the mailer start */
				$mailer = shopFunctions::loadMailer();
				//by Max Milbers
				//$from_email = ps_vendor::get_vendor_fields(1,array("email"),"");
				$mailer->From = $mainframe->getCfg('mailfrom');
				$mailer->FromName = $mainframe->getCfg('sitename');
				$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
				$mailer->AddAddress($waiting_user->notify_email);
				$mailer->setBody($notice_body);
				$mailer->setSubject($notice_subject);

				/* Send the mail */
				if (!$mailer->Send()) {

				}
				else {
					/* Clear the mail details */
					$mailer->ClearAddresses();
					$this->update($waiting_user->notify_email, $product_id);
				}
			}
			return true;
		}
	}

	/**
	* Updates the waitinglist
	* @author RolandD
	*/
//	public function update($shopper_email,$product_id) {
//		$db = JFactory::getDBO();
//
//		$q = "UPDATE #__vm_waiting_list SET notified='1' WHERE ";
//		$q .= "notify_email = ".$db->Quote($shopper_email)." AND ";
//		$q .= "product_id = ".$product_id;
//		$db->setQuery($q);
//		if ($db->query()) return true;
//		else return false;
//	}

	/*
	** VALIDATION FUNCTIONS
	**
	*/

//	function validate_add(&$d) {
//		global $vmLogger;
//		$db = new ps_DB;
//
//		$q = "SELECT waiting_list_id from #__{vm}_waiting_list WHERE ";
//		$q .= "notify_email='" . $d["notify_email"] . "' AND ";
//		$q .= "product_id='" . $d["product_id"] . "' AND notified='0'";
//		$db->query($q);
//		if ($db->next_record()) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_ERR_ALREADY') );
//			return False;
//		}
//		if (!$d["notify_email"]) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_ERR_EMAIL_ENTER') );
//			return False;
//		}
//		if (!vmValidateEmail($d["notify_email"])) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_ERR_EMAIL_NOTVALID') );
//			return False;
//		}
//		if (!$d["product_id"]) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_ERR_PRODUCT') );
//			return False;
//		}
//		return True;
//	}

//	function validate_delete($d) {
//		global $vmLogger;
//
//		if (!$d["notify_email"]) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_DELETE_SELECT') );
//			return False;
//		}
//		if (!vmValidateEmail($d["notify_email"])) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_ERR_EMAIL_ENTER') );
//			return False;
//		}
//		if (!$d["product_id"]) {
//			$vmLogger->err( JText::_('VM_WAITING_LIST_DELETE_ERR_PRODUCT') );
//			return False;
//		}
//		return True;
//	}

	/**
	* Creates a new waiting list entry
	*
	* @author mwattier
	* @param $product_id
	* @return
	*/

//	function add(&$d) {
//		global $auth;
//		$db = new ps_DB;
//
//		if (!$this->validate_add($d)) {
//			return False;
//		}
//		$q = "INSERT INTO #__{vm}_waiting_list (product_id, user_id, notify_email)";
//		$q .= " VALUES ('";
//		$q .= $d["product_id"] . "','";
//		$q .= $auth['user_id'] . "','";
//		$q .= $d["notify_email"] . "')";
//		$db->query($q);
//		$db->next_record();
//		return True;
//
//	}

	/**
	* Should delete a category and add categories under it.
	*
	* @author pablo
	* @param $product_id
	* @return
	*/

//	function delete(&$d) {
//		$db = new ps_DB;
//
//		if (!$this->validate_delete($d)) {
//			return False;
//		}
//		$q = "DELETE from #__{vm}_waiting_list where notify_email='" . $d["notify_email"] . "'";
//		$q .= " AND product_id='" .$d["product_id"] ."'";
//		$db->query($q);
//		$db->next_record();
//		return True;
//
//	}

	/**
	* Will notify all people who have not been notified
	*
	* @author
	* @param string $product_id the ID of the product
	* @return true
	*/

//	function notify_list($product_id) {
//		global $sess,  $mosConfig_fromname;
//
//		$option = JRequest::getVar(  'option' );
//
//		if (!$product_id) {
//			return False;
//		}
//
//		//by Max Milbers
//		$from_email = ps_vendor::get_vendor_fields(1,array("email"),"");
//
//		$db = new ps_DB;
//		$q = "SELECT * FROM #__{vm}_waiting_list WHERE ";
//		$q .= "notified='0' AND product_id='$product_id'";
//		$db->query($q);
//
//		require_once( CLASSPATH. 'ps_product.php');
//		$ps_product = new ps_product;
//
//		while ($db->next_record()) {
//			// get the product name for the e-mail
//			$product_name = $ps_product->get_field($product_id, "product_name");
//
//			// lets make the e-mail up from the info we have
//			$notice_subject = sprintf(JText::_('PRODUCT_WAITING_LIST_EMAIL_SUBJECT'), $product_name);
//
//			// now get the url information
//			$url = URL . "index.php?page=shop.product_details&flypage=shop.flypage&product_id=$product_id&option=$option&Itemid=".$sess->getShopItemid();
//			$notice_body = sprintf(JText::_('PRODUCT_WAITING_LIST_EMAIL_TEXT'), $product_name, $url);
//
//			// send the e-mail
//			$shopper_email = $db->f("notify_email");
//			vmMail($from_email, $mosConfig_fromname, $shopper_email, $notice_subject, $notice_body, "");
//
//			$this->update( $shopper_email, $product_id);
//
//		}
//		return True;
//	}
}
// pure php no closing tag
