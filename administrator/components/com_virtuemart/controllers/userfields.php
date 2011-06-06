<?php
/**
*
* Userfields controller
*
* @package	VirtueMart
* @subpackage Userfields
* @author Oscar van Eijk
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
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage Userfields
 * @author     Oscar van Eijk
 */
class VirtuemartControllerUserfields extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct(){
		parent::__construct('virtuemart_userfield_id');

	}

	function Userfields(){

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('userfields', $viewType);
		$view->loadHelper('paramhelper');

		// Push a model into the view
		$model = $this->getModel('userfields');

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		parent::display();
	}


	/**
	 * Handle the edit task
	 */
	function edit(){

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('userfields', $viewType);

		// Load the additional models
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'shoppergroup', 'VirtueMartModel' ));

		
		parent::edit();
	}

	// /**
	 // * Move an item up in the grid.
	 // */
	// function orderup()
	// {
		// $model = $this->getModel('userfields');
		// if (!$model->move(-1)) {
			// $msg = JText::_($model->getError());
		// } else {
			// $msg = '';
		// }
		// $this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	// }

	// /**
	 // * Move an item down in the grid.
	 // */
	// function orderdown()
	// {
		// $model = $this->getModel('userfields');
		// if (!$model->move(1)) {
			// $msg = JText::_($model->getError());
		// } else {
			// $msg = '';
		// }
		// $this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	// }

	// /**
	 // * Save the given grid ordering.
	 // */
	// function saveorder()
	// {
		// $cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		// $order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		// JArrayHelper::toInteger($cid);
		// JArrayHelper::toInteger($order);

		// $model = $this->getModel('userfields');
		// $model->saveorder($cid, $order);

		// $msg = 'New ordering saved';
		// $this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	// }
}

//No Closing tag
