<?php
/**
* Account Maintenance view
*
* @package VirtueMart
* @author RolandD
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

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
		$auth = JRequest::getVar('auth');
		
		/* Check if the user is logged in */
		if (!$auth['is_registered_customer']) {
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
				if (Vmconfig::getVar('vm_registration_type') == 'NO_REGISTRATION' ) {
					// global $default;
					// $default['email'] = $db->f('email');
					$skip_fields = array( 'username', 'password', 'password2' );
				}
				
				/* Load the user fields */
				$fields = shopFunctions::getUserFields('account');
				$userinfo = shopFunctions::getUserDetails($auth["user_id"],"",""," AND address_type='BT'");
				
				/* Load the editor */
				$editor = JFactory::getEditor(); 
				
				/* Assign data */
				$this->assignRef('fields', $fields);
				$this->assignRef('skipfields', $skip_fields);
				$this->assignRef('userinfo', $userinfo);
				$this->assignRef('editor', $editor);
				
				break;
			default:
				/* Set some path information */
				$mainframe->setPageTitle(JText::_('VM_ACCOUNT_TITLE'));
				$pathway->addItem(JText::_('ACCOUNTINFORMATION'));
				
				/* Load the logged in user */
				$user = JFactory::getUser();
				
				/* Assign data */
				$this->assignRef('user', $user);
				break;
		}
		
		/* Assign data */
		$this->assignRef('auth', $auth);
		
		/* Display it all */
		parent::display($tpl); 
	}
}

?>