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

/**
 * Category Controller
 *
 * @package    VirtueMart
 * @subpackage Category
 * @author jseros
 */
class VirtuemartControllerCategory extends JController {

	/**
	 * Contructor
	 *
	 * @access	public
	 * @author
	 */
	public function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
	    $this->registerTask( 'apply',  'save' );

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('category', $viewType);

		// Pushing default model
		$categoryModel = $this->getModel('category');
		if (!JError::isError($categoryModel)) {
			$view->setModel($categoryModel, true);
		}
	}

	/**
	 * Display any category view
	 *
	 * @author RickG, jseros
	 */
	public function display() {
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
     * @author RickG
	 */
	public function edit()
	{
		JRequest::setVar('controller', 'category');
		JRequest::setVar('view', 'category');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}


	/**
	 * Handle the cancel task
	 *
	 * @author RickG
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=category');
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG, jseros, RolandD
	 */
	public function save() {
		$categoryModel = $this->getModel('category');

		if ($id = $categoryModel->store()) $msg = JText::_('VM_CATEGORY_SAVED_SUCCESS');
		else $msg = $categoryModel->getError();

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=category&task=edit&cid[]='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=category';

		$this->setRedirect($redirection, $msg);
	}


	/**
	 * Handle the remove task
	 *
	 * @author RickG, jseros
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
			$mainframe->redirect('index.php?option=com_virtuemart&view=category', $msg, 'error');
			return;
		}

		$categoryModel = $this->getModel('category');

		if (!$categoryModel->delete($cid)) {
			$msg = JText::_('VM_ERROR_CATEGORIES_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_( 'VM_CATEGORY_DELETED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=category', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author RickG, jseros
	 */
	public function publish()
	{
		$categoryModel = $this->getModel('category');
		if (!$categoryModel->publish(true)) {
			$msg = JText::_('VM_ERROR_CATEGORIES_COULD_NOT_BE_PUBLISHED');
		}
		else{
			$msg = JText::_('VM_CATEGORY_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=category', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author RickG, jseros
	 */
	public function unpublish()
	{
		$categoryModel = $this->getModel('category');
		if (!$categoryModel->publish(false)) {
			$msg = JText::_('VM_ERROR_CATEGORIES_COULD_NOT_BE_UNPUBLISHED');
		}
		else{
			$msg = JText::_('VM_CATEGORY_UNPUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=category', $msg);
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
			$msg = JText::_('Select an item to toggle');
			$mainframe->redirect('index.php?option=com_virtuemart&view=category', $msg, 'error');
		}

		$categoryModel = $this->getModel('category');
		$status = $categoryModel->share($cid);

		if( $status == 1 ){
			$msg = JText::_('VM_CATEGORY_SHARED_SUCCESS');
		}
		elseif( $status == -1 ){
			$msg = JText::_('VM_CATEGORY_UNSHARED_SUCCESS');
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

		//capturing category_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=category', JText::_('No Items Selected') );
			return false;
		}

		//getting the model
		$model = $this->getModel('category');

		if ($model->orderCategory($id, -1)) {
			$msg = JText::_( 'Item Moved Up' );
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

		//capturing category_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=category', JText::_('No Items Selected') );
			return false;
		}

		//getting the model
		$model = $this->getModel('category');

		if ($model->orderCategory($id, 1)) {
			$msg = JText::_( 'Item Moved Down' );
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

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('category');

		if ($model->setOrder($cid)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=category', $msg );
	}

}
