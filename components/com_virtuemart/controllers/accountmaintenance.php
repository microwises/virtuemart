<?php
/**
 * @package		VirtueMart
 */

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
	* Update shoppers billing address
	*/
	public function shopperUpdate() {
		$mainframe = Jfactory::getApplication();
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
}
?>
