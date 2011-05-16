<?php
/**
*
* custom controller
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: custom.php 3039 2011-04-14 22:37:04Z Electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author Max Milbers
 */
class VirtuemartControllerCustom extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

//		$this->setMainLangKey('CUSTOM');
		$this->registerTask( 'add',  'edit' );
	    $this->registerTask( 'apply',  'save' );

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$this->view = $this->getView('custom', $viewType);

	}


	/**
	 * Shows the product files list screen
	 */
	function edit() {
		/* Create the view object */
//		$view = $this->getView('custom', 'html');

		/* Default model */
		$this->view->setModel( $this->getModel( 'custom', 'VirtueMartModel' ), true );

		/* Set the layout */
//		switch (JRequest::getCmd('task')) {

		$this->view->setModel( $this->getModel( 'user', 'VirtueMartModel' ), true );
		$this->view->setLayout('edit');


		/* Now display the view. */
		$this->view->display();
	}
	/**
	 * for ajax call custom
	 */
	function viewJson() {

		/* Create the view object. */
		$view = $this->getView('custom', 'json');

		/* Standard model */
		$view->setModel( $this->getModel( 'custom', 'VirtueMartModel' ), true );

		/* Now display the view. */
		$view->display(null);
	}

	function save(){

		$fileModel = $this->getModel('custom');

		//Now we try to determine to which this custom should be long to
		$data = JRequest::get('post');
		if(!empty($data['virtuemart_product_id'])){
			$table = $fileModel->getTable('products');
			$type = 'product';
		} else if (!empty($data['virtuemart_category_id'])){
			$table = $fileModel->getTable('categories');
			$type = 'category';
		} else if (!empty($data['virtuemart_manufacturer_id'])){
			$table = $fileModel->getTable('manufacturers');
			$type = 'manufacturer';
//		} else if ($data['virtuemart_vendor_id']){
//			$table = $this->getTable('vendors');
//			$type = 'vendor';
		} else {

		}

		if(empty($table)){
			if ($id = $fileModel->store()) {
				$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_SAVED_SUCCESS');
			} else {
				$msg = $fileModel->getError();
			}
		} else {
			if ($id = $fileModel->storeCustom($data,$table,$type)) {
			$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_SAVED_SUCCESS');
			} else {
				$msg = $fileModel->getError();
			}
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=custom&task=edit&virtuemart_custom_id='.$id;
		} else {
			$redirection = 'index.php?option=com_virtuemart&view=custom';
		}

		$this->setRedirect($redirection, $msg);
	}

	/**
	 * Handle the cancel task
	 *
	 * @author Max Milbers
	 */
	public function cancel()
	{
		$msg = JText::_('COM_VIRTUEMART_OPERATION_CANCELED');
		//Todo, in case redirect to product
		$this->setRedirect('index.php?option=com_virtuemart&view=custom', $msg);
	}

	/**
	 * Handle the remove task
	 *
	 * @author Max Milbers, Jseros
	 */
	public function remove()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token, trying deleting custom' );

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$msg = '';

		JArrayHelper::toInteger($cid);

		if(count($cid) < 1) {
			$msg = JText::_('COM_VIRTUEMART_SELECT_ITEM_TO_DELETE');
			$mainframe->redirect('index.php?option=com_virtuemart&view=custom', $msg, 'error');
			return;
		}

		$customModel = $this->getModel('custom');

		if (!$customModel->delete($cid)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_CUSTOM_FIELD_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_DELETED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=custom', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers
	 */
	public function publish() {
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$customModel = $this->getModel('custom');
		if (!$customModel->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_CUSTOM_FIELD_COULD_NOT_BE_PUBLISHED');
		}
		else{
			$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=custom', $msg);
	}
	/**
	 * Toggle is_hidden fied
	 *@Author Kohl patrick
	 * @author Max Milbers
	 */
	public function toggle_is_hidden() {
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$customModel = $this->getModel('custom');
		if (!$customModel->toggle('is_hidden')) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_CUSTOM_FIELD_COULD_NOT_BE_TOGGLED');
		}
		else{
			$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_TOGGLED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=custom', $msg);
	}
	/**
	 * Toggle admin_only
	 *@Author Kohl patrick
	 * @author Max Milbers
	 */
	public function toggle_admin_only() {
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$customModel = $this->getModel('custom');
		if (!$customModel->toggle('admin_only')) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_CUSTOM_FIELD_COULD_NOT_BE_PUBLISHED');
		}
		else{
			$msg = JText::_('COM_VIRTUEMART_CUSTOM_FIELD_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=custom', $msg);
	}

}
// pure php no closing tag
