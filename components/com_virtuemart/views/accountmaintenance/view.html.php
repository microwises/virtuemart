<?php
/**
*
* Account Maintenance view
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

// Load the view framework
jimport( 'joomla.application.component.view' );
/**
 * Account maintenance
 */
class VirtueMartViewAccountmaintenance extends JView {
	
	function display($tpl = null) {
		$mainframe = JFactory::getApplication();
		$pathway	= $mainframe->getPathway();
		$task = JRequest::getCmd('task');
		
		/* Set the helper path */
		$this->addHelperPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers');
		
		/* Load the authorizations */
		$this->loadHelper('permissions');
		$perm = Permissions::getInstance();
		$user = JFactory::getUser();
		
		/* Check if the user is logged in */
		 if (!$perm->isRegisteredCustomer($user->id)) {
			$task = false;
			$this->setLayout('accountmaintenance');
		}
		switch ($task) {
			case 'accountbilling':
				/* Set some path information */
				$mainframe->setPageTitle(JText::_('VM_ACCOUNT_BILLING'));
				$pathway->addItem(JText::_('ACCOUNTINFORMATION'), 'index.php?option=com_virtuemart&view=accountmaintenance');
				$pathway->addItem(JText::_('BILLING'));
				
				/* Load the helpers */
				$this->loadHelper('shopFunctions');
				
				/* Handle NO_REGISTRATION */
				$skip_fields = array();
				if (VmConfig::get('vm_registration_type') == 'NO_REGISTRATION' ) {
					// global $default;
					// $default['email'] = $db->f('email');
					$skip_fields = array( 'username', 'password', 'password2' );
				}
				
				/* Load the user fields */
				$fields = shopFunctions::getUserFields('account');
				$userinfo = shopFunctions::getUserDetails($user->id,"",""," AND address_type='BT'");
				
				/* Load the editor */
				$editor = JFactory::getEditor(); 
				
				/* Assign data */
				$this->assignRef('fields', $fields);
				$this->assignRef('skipfields', $skip_fields);
				$this->assignRef('userinfo', $userinfo);
				$this->assignRef('editor', $editor);
				
				break;
			case 'accountshipping':
				/* Set some path information */
				$mainframe->setPageTitle(JText::_('VM_ACCOUNT_SHIPPING'));
				$pathway->addItem(JText::_('ACCOUNTINFORMATION'), 'index.php?option=com_virtuemart&view=accountmaintenance');
				$pathway->addItem(JText::_('SHIPPING'));
				
				/* Load the helpers */
				$this->loadHelper('shopFunctions');
				
				/* Load the shipping addresses fields */
				$shipping_addresses = $this->get('ShippingAddresses');
				
				/* Assign data */
				$this->assignRef('shipping_addresses', $shipping_addresses);
				break;
			case 'addshipto':
			case 'editshipto':
				$mainframe->setPageTitle(JText::_('VM_ACCOUNT_SHIPPING'));
				$pathway->addItem(JText::_('ACCOUNTINFORMATION'), 'index.php?option=com_virtuemart&view=accountmaintenance');
				$pathway->addItem(JText::_('SHIPPING'), 'index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping');
				$pathway->addItem(JText::_('SHIPPING_EDIT'));
				
				/* Load the helpers */
				$this->loadHelper('shopFunctions');
				
				/* Handle NO_REGISTRATION */
				$skip_fields = array();
				if (VmConfig::get('vm_registration_type') == 'NO_REGISTRATION' ) {
					// global $default;
					// $default['email'] = $db->f('email');
					$skip_fields = array( 'username', 'password', 'password2' );
				}
				
				/* Load the user fields */
				$fields = shopFunctions::getUserFields('shipping');
				if ($task == 'editshipto') $userinfo = shopFunctions::getUserDetails($user->id,"",""," AND address_type='ST' AND user_info_id='".JRequest::getVar('user_info_id')."'");
				else $userinfo = null;
				
				/* Assign data */
				$this->assignRef('fields', $fields);
				$this->assignRef('skipfields', $skip_fields);
				$this->assignRef('userinfo', $userinfo);
				break;
			case 'order':
				
				break;
			default:
				/* Set some path information */
				$mainframe->setPageTitle(JText::_('VM_ACCOUNT_TITLE'));
				$pathway->addItem(JText::_('ACCOUNTINFORMATION'));
				
				/* Load some orders */
				$orders = $this->get('ListOrders');
				$this->assignRef('orders', $orders);
				break;
		}
		
		/* Assign data */
		$this->assignRef('user', $user);
		$this->assignRef('perm', $perm);
		
		/* Display it all */
		parent::display($tpl); 
	}
}

?>