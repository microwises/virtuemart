<?php
/**
*
* Config controller
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
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
 * Configuration Controller
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG
 */
class VirtuemartControllerConfig extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

//		$this->setMainLangKey('CONFIG');
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('config', $viewType);

		// Push a model into the view
		$model = $this->getModel('config');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model = $this->getModel('user');
		if (!JError::isError($model)) {
			$view->setModel($model, false);
		}
	}


//	/**
//	 * Handle the edit task
//	 *
//     * @author RickG
//	 */
//	function edit(){
//
//		parent::edit();
//	}


	/**
	 * Handle the save task
	 *
	 * @author RickG
	 */
	function save(){

		JRequest::checkToken() or jexit( 'Invalid Token' );
		$model = $this->getModel('config');
		$data = JRequest::get('post');

		if ($model->store($data)) {
			$msg = JText::_('COM_VIRTUEMART_CONFIG_SAVED');
			// Load the newly saved values into the session.
			VmConfig::getInstance();
		}
		else {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_virtuemart', $msg);
	}


	/**
	 * Handle the apply task
	 *
	 * @author RickG
	 */
	function apply(){

		JRequest::checkToken() or jexit( 'Invalid Token' );
		$model = $this->getModel('config');
		$data = JRequest::get('post');

		if ($model->store($data)) {
			$msg = JText::_('COM_VIRTUEMART_CONFIG_SAVED');
			// Load the newly saved values into the session.
			VmConfig::getInstance();
		}
		else {
			$msg = JText::_($model->getError());
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=config', $msg);
	}


	/**
	 * Overwrite the remove task
	 * Removing config is forbidden.
	 * @author Max Milbers
	 */
	function remove(){

//		$model = $this->getModel('config');
//		if (!$model->remove()) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_CONFIGS_COULD_NOT_BE_DELETED');
//		}
//		else {
//			$msg = JText::_('COM_VIRTUEMART_CONFIGS_DELETED');
//		}

		$this->setRedirect( $this->redirectPath , $msg);
	}
}

//pure php no tag
