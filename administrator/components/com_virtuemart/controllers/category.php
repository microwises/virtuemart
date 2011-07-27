<?php
/**
*
* Category controller
*
* @package	VirtueMart
* @subpackage Category
* @author RickG, jseros
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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Category Controller
 *
 * @package    VirtueMart
 * @subpackage Category
 * @author jseros, Max Milbers
 */
class VirtuemartControllerCategory extends VmController {

	public function __construct() {
		parent::__construct();

	}

	public function Category(){

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		// Pushing default model
		$model = $this->getModel();
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		parent::display();
	}

	/**
	 * We want to allow html so we need to overwrite some request data
	 *
	 * @author Max Milbers
	 */
	function save(){

		$data = JRequest::get('post');

		$data['category_name'] = JRequest::getVar('category_name','','post','STRING',JREQUEST_ALLOWHTML);
		$data['category_description'] = JRequest::getVar('category_description','','post','STRING',JREQUEST_ALLOWHTML);

		//TODO multi-x
		$data['virtuemart_vendor_id'] = 1;

		parent::save($data);
	}

	/**
	 * Handle the shared/unshared action
	 *
	 * @author jseros
	 */
	public function toggleShared()
	{
		$mainframe = JFactory::getApplication();

		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$msg = '';

		JArrayHelper::toInteger($cid);

		if(count($cid) < 1) {
			$msg = JText::_('COM_VIRTUEMART_SELECT_ITEM_TO_TOGGLE');
			$mainframe->redirect('index.php?option=com_virtuemart&view=category', $msg, 'error');
		}

		$categoryModel = $this->getModel('category');
		$status = $categoryModel->share($cid);

		if( $status == 1 ){
			$msg = JText::_('COM_VIRTUEMART_CATEGORY_SHARED_SUCCESS');
		}
		elseif( $status == -1 ){
			$msg = JText::_('COM_VIRTUEMART_CATEGORY_UNSHARED_SUCCESS');
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=category', $msg);
	}


	/**
	* Save the category order
	*
	* @author jseros
	*/
	public function orderUp()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		//capturing virtuemart_category_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=category', JText::_('COM_VIRTUEMART_NO_ITEMS_SELECTED') );
			return false;
		}

		//getting the model
		$model = $this->getModel('category');

		if ($model->orderCategory($id, -1)) {
			$msg = JText::_('COM_VIRTUEMART_ITEM_MOVED_UP');
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=category', $msg );
	}


	/**
	* Save the category order
	*
	* @author jseros
	*/
	public function orderDown()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		//capturing virtuemart_category_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=category', JText::_('COM_VIRTUEMART_NO_ITEMS_SELECTED') );
			return false;
		}

		//getting the model
		$model = $this->getModel('category');

		if ($model->orderCategory($id, 1)) {
			$msg = JText::_('COM_VIRTUEMART_ITEM_MOVED_DOWN');
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=category', $msg );
	}


	/**
	* Save the categories order
	*/
	public function saveOrder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );	//is sanitized
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('category');

		$order	= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($order);

		if ($model->setOrder($cid,$order)) {
			$msg = JText::_('COM_VIRTUEMART_NEW_ORDERING_SAVED');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=category', $msg );
	}

}
