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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');

/**
 * Calculator Controller
 *
 * @package    VirtueMart
 * @subpackage Calculation tool
 * @author Max Milbers
 */
class VirtuemartControllerCalc extends VmController {
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	public function __construct() {
		parent::__construct();

	}

	/**
	 * Default view without task
	 *
	 * @author Max Milbers
	 */
	public function Calc() {

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		// Pushing default model
		$model = $this->getModel();
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		$view->setModel( $this->getModel( 'category', 'VirtueMartModel' ));

		parent::display();
	}

	/**
	 * Handle the edit task
	 *
     * @author Max Milbers
	 */
	public function edit(){

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		$view->setModel( $this->getModel( 'currency', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'user', 'VirtueMartModel' ));

		parent::edit();

	}


	/**
	 * We want to allow html so we need to overwrite some request data
	 *
	 * @author Max Milbers
	 */
	function save(){

		$data = JRequest::get('post');

		$data['calc_name'] = JRequest::getVar('calc_name','','post','STRING',JREQUEST_ALLOWHTML);
		$data['calc_descr'] = JRequest::getVar('calc_descr','','post','STRING',JREQUEST_ALLOWHTML);

		parent::save($data);
	}


	/**
	* Save the calc order
	*
	* @author jseros
	*/
	public function orderUp()
	{
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		//capturing virtuemart_calc_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', JText::_('COM_VIRTUEMART_NO_ITEMS_SELECTED') );
			return false;
		}

		//getting the model
		$model = $this->getModel('calc');

		if ($model->orderCalc($id, -1)) {
			$msg = JText::_('COM_VIRTUEMART_ITEM_MOVED_UP');
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', $msg );
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

		//capturing virtuemart_calc_id
		$id = 0;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', JText::_('COM_VIRTUEMART_NO_ITEMS_SELECTED') );
			return false;
		}

		//getting the model
		$model = $this->getModel('calc');

		if ($model->orderCalc($id, 1)) {
			$msg = JText::_('COM_VIRTUEMART_ITEM_MOVED_DOWN');
		} else {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=calc', $msg );
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

		$model = $this->getModel('calc');

		if ($model->setOrder($cid)) {
			$msg = JText::_('COM_VIRTUEMART_NEW_ORDERING_SAVED');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=calc', $msg );
	}

}
// pure php no closing tag
