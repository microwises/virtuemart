<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage AccountMaintenance
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

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerAccountmaintenance extends JController
{
    
	public function __construct() {
		parent::__construct();
		
		$this->registerTask('addshipto', 'editshipto');
	}
	
	public function Accountmaintenance() {
		/* Create the view */
		$view = $this->getView('accountmaintenance', 'html');
		
		/* Add the default model */
		$view->setModel( $this->getModel( 'accountmaintenance', 'VirtuemartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('accountmaintenance');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* Modify the billing address in front-end
	* @author RolandD
	*/
	public function accountBilling() {
		/* Create the view */
		$view = $this->getView('accountmaintenance', 'html');
	
		/* Add the default model */
		$view->setModel($this->getModel( 'accountmaintenance', 'VirtuemartModel' ), true);
		
		/* Set the layout */
		$view->setLayout('accountbilling');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* Modify the shipping address in front-end
	* @author RolandD
	*/
	public function accountShipping() {
		/* Create the view */
		$view = $this->getView('accountmaintenance', 'html');
	
		/* Add the default model */
		$view->setModel($this->getModel( 'accountmaintenance', 'VirtuemartModel' ), true);
		
		/* Set the layout */
		$view->setLayout('accountshipping');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* List an order in the front-end
	*
	* @author RolandD
	*/
	public function accountOrder() {
		/* Create the view */
		$view = $this->getView('accountmaintenance', 'html');
	
		/* Add the default model */
		$view->setModel($this->getModel( 'accountmaintenance', 'VirtuemartModel' ), true);
		
		/* Set the layout */
		$view->setLayout('accountorder');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* Send the user to the add/edit shipping address 
	* 
	* @author RolandD
	* @access public
	*/
	public function editShipto() {
		/* Create the view */
		$view = $this->getView('accountmaintenance', 'html');
	
		/* Add the default model */
		$view->setModel($this->getModel( 'accountmaintenance', 'VirtuemartModel' ), true);
		
		/* Set the layout */
		$view->setLayout('accountshipping_edit');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* Update shoppers billing address
	*/
	public function shopperUpdate() {
		$mainframe = JFactory::getApplication();
		/* Check for request forgeries */
		if (JRequest::checkToken()) {
			/* Load the model object */
			$model = $this->getModel('accountmaintenance');
			/* Add model path */
			JController::addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models');
			$userfields_model = $this->getModel('userfields');
			JRequest::setVar('userfields_model', $userfields_model);
			
			$msgtype = '';
			$result = $model->saveShopper();
			if ($result[0]) {
				$msg = JText::_('ACCOUNT_SAVED_SUCCESSFULLY');
				$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance', $msg);
			}
			else {
				$msg = JText::_('ACCOUNT_NOT_SAVED_SUCCESSFULLY').'<br />'.$result[1];
				$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance&task=accountbilling', $msg, 'error');
			}
		}
		else {
			$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance', JText::_('INVALID_TOKEN'), 'error');
		}
			
	}
	
	/**
	* Add a shipping address
	*
	* @author RolandD
	*/
	public function addShippingAddress() {
		$mainframe = JFactory::getApplication();
		/* Check for request forgeries */
		if (JRequest::checkToken()) {
			$db = JFactory::getDBO();
			/* Load the model object */
			$model = $this->getModel('accountmaintenance');
			/* Add model path */
			JController::addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models');
			$userfields_model = $this->getModel('userfields');
			JRequest::setVar('userfields_model', $userfields_model);
			
			$msgtype = '';
			if ($model->getAddShippingAddress()) {
				$msg = JText::_('SHIPPING_ADDRESS_SAVED_SUCCESSFULLY');
				
			}
			else {
				$msg = JText::_('SHIPPING_ADDRESS_NOT_SAVED_SUCCESSFULLY').'<br />'.$db->getErrorMsg();
				$msgtype = 'error';
			}
			$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping', $msg, $msgtype);
		}
		else {
			$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping', JText::_('INVALID_TOKEN'), 'error');
		}
	}
	
	/**
	* Remove a shipping address
	*
	* @author RolandD
	*/
	public function removeshippingaddress() {
		$mainframe = JFactory::getApplication();
		/* Check for request forgeries */
		if (JRequest::checkToken()) {
			$db = JFactory::getDBO();
			/* Load the model object */
			$model = $this->getModel('accountmaintenance');
			$msgtype = '';
			if ($model->getRemoveShippingAddress()) $msg = JText::_('SHIPPING_ADDRESS_REMOVED_SUCCESSFULLY');
			else {
				$msg = JText::_('SHIPPING_ADDRESS_NOT_REMOVED_SUCCESSFULLY').'<br />'.$db->getErrorMsg();
				$msgtype = 'error';
			}
			$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping', $msg, $msgtype);
		}
		else {
			$mainframe->redirect('index.php?option=com_virtuemart&view=accountmaintenance&task=accountshipping', JText::_('INVALID_TOKEN'), 'error');
		}
	}
}
?>
