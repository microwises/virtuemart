<?php
/**
*
* Calc controller
*
* @package	VirtueMart
* @subpackage Calc
* @author Max Milbers, jseros
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
 * Calculator Controller
 *
 * @package    VirtueMart
 * @subpackage Calculation tool
 * @author Max Milbers
 */
class VirtuemartControllerPaymentmethod extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	public function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
	    $this->registerTask( 'apply',  'save' );
		$document =& JFactory::getDocument();				
		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('paymentmethod', $viewType);

		// Pushing default model
		$paymModel = $this->getModel('paymentmethod');
		if (!JError::isError($paymModel)) {
			$view->setModel($paymModel, true);
		}
		/* Product category functions */
//		$view->setModel( $this->getModel( 'category', 'VirtueMartModel' ));

	}
	
	/**
	 * Display the view
	 *
	 * @author RickG	 
	 */
	public function display() {
		parent::display();
	}
	
	
	/**
	 * Handle the edit task
	 *
     * @author Max Milbers
	 */
	public function edit(){
		JRequest::setVar('controller', 'paymentmethod');
		JRequest::setVar('view', 'paymentmethod');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}		
	
	
	/**
	 * Handle the cancel task
	 *
	 * @author Max Milbers
	 */
	public function cancel()
	{
		$msg = JText::_('Operation Canceled!!');
		$this->setRedirect('index.php?option=com_virtuemart&view=paymentmethod', $msg);
	}	
	
	
	/**
	 * Handle the save task
	 *
	 * @author Max Milbers, Jseros	 
	 */	
	public function save(){
		$paymModel = $this->getModel('paymentmethod');
		$cmd = JRequest::getCmd('task');

		if ($id = $paymModel->store()) {
			$msg = JText::_('VM_PAYM_SAVED_SUCCESS');
		}
		else {
			$msg = JText::_($paymModel->getError());
		}

		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=paymentmethod&task=edit&cid[]='.$id;
		}
		else{
			$redirection = 'index.php?option=com_virtuemart&view=paymentmethod';
		}

		$this->setRedirect($redirection, $msg);
}
	/**
	 * Handle the remove task
	 *
	 * @author Max Milbers, Jseros	 
	 */		
	public function remove()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$msg = '';

		JArrayHelper::toInteger($cid);

		if(count($cid) < 1) {
			$msg = JText::_('Select an item to delete');
			$mainframe->redirect('index.php?option=com_virtuemart&view=paymentmethod', $msg, 'error');
			return;
		}

		$paymModel = $this->getModel('paymentmethod');

		if (!$paymModel->delete($cid)) {
			$msg = JText::_('VM_ERROR_CATEGORIES_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_( 'VM_CALC_DELETED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', $msg);
	}
	
	
	/**
	 * Handle the publish task
	 *
	 * @author Jseros, Max Milbers	 
	 */		
	public function publish()
	{
		$paymModel = $this->getModel('paymentmethod');
		if (!$paymModel->publish(true)) {
			$msg = JText::_('VM_ERROR_CALC_COULD_NOT_BE_PUBLISHED');
		}
		else{
			$msg = JText::_('VM_CALC_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', $msg);
	}
	
	
	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers, Jseros	 
	 */		
	function unpublish()
	{
		$paymModel = $this->getModel('paymentmethod');
		if (!$paymModel->publish(false)) {
			$msg = JText::_('VM_ERROR_CATEGORIES_COULD_NOT_BE_UNPUBLISHED');
		}
		else{
			$msg = JText::_('VM_CALC_UNPUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', $msg);
	}


//	/**
//	 * Handle the shopper publish/unpublish action
//	 *
//	 * @author jseros
//	 */
//	public function toggleShopper()
//	{
//		$mainframe = JFactory::getApplication();
//
//		// Check token
//		JRequest::checkToken() or jexit( 'Invalid Token' );
//
//		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
//		$msg = '';
//
//		JArrayHelper::toInteger($cid);
//
//		if(count($cid) < 1) {
//			$msg = JText::_('Select an item to toggle');
//			$mainframe->redirect('index.php?option=com_virtuemart&view=paymentmethod', $msg, 'error');
//		}
//
//		$paymModel = $this->getModel('paymentmethod');
//		$status = $paymModel->shopperPublish($cid);
//
//		if( $status == 1 ){
//			$msg = JText::_('VM_CALC_SHOPPER_PUBLISH_SUCCESS');
//		}
//		elseif( $status == -1 ){
//			$msg = JText::_('VM_CALC_SHOPPER_UNPUBLISH_SUCCESS');
//		}
//
//		$mainframe->redirect('index.php?option=com_virtuemart&view=paymentmethod', $msg);
//	}
//
//
//	/**
//	 * Handle the vendor publish/unpublish action
//	 *
//	 * @author jseros
//	 */
//	public function toggleVendor()
//	{
//		$mainframe = JFactory::getApplication();
//
//		// Check token
//		JRequest::checkToken() or jexit( 'Invalid Token' );
//
//		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
//		$msg = '';
//
//		JArrayHelper::toInteger($cid);
//
//		if(count($cid) < 1) {
//			$msg = JText::_('Select an item to toggle');
//			$mainframe->redirect('index.php?option=com_virtuemart&view=paymentmethod', $msg, 'error');
//		}
//
//		$paymModel = $this->getModel('paymentmethod');
//		$status = $paymModel->vendorPublish($cid);
//
//		if( $status == 1 ){
//			$msg = JText::_('VM_CALC_VENDOR_PUBLISH_SUCCESS');
//		}
//		elseif( $status == -1 ){
//			$msg = JText::_('VM_CALC_VENDOR_UNPUBLISH_SUCCESS');
//		}
//
//		$mainframe->redirect('index.php?option=com_virtuemart&view=paymentmethod', $msg);
//	}


	/**
	* Save the paym order
	*
	* @author jseros
	*/
	public function orderUp()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		//capturing paym_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', JText::_('No Items Selected') );
			return false;
		}

		//getting the model
		$model = $this->getModel('paymentmethod');

		if ($model->orderCalc($id, -1)) {
			$msg = JText::_( 'Item Moved Up' );
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', $msg );
	}


	/**
	* Save the calc order
	*
	* @author jseros
	*/
	public function orderDown()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		//capturing calc_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', JText::_('No Items Selected') );
			return false;
		}

		//getting the model
		$model = $this->getModel('paymentmethod');

		if ($model->orderCalc($id, 1)) {
			$msg = JText::_( 'Item Moved Down' );
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=paymentmethod', $msg );
	}


	/**
	* Save the categories order
	*/
	public function saveOrder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('paymentmethod');

		if ($model->setOrder($cid)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=paymentmethod', $msg );
	}

}
?>
