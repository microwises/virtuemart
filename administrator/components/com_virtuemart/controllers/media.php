<?php
/**
*
* Media controller
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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author Max Milbers
 */
class VirtuemartControllerMedia extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		$this->registerTask( 'add',  'edit' );
	    $this->registerTask( 'apply',  'save' );

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$this->view = $this->getView('media', $viewType);

	}


	/**
	 * Shows the product files list screen
	 */
	function edit() {
		/* Create the view object */
//		$view = $this->getView('media', 'html');

		/* Default model */
		$this->view->setModel( $this->getModel( 'media', 'VirtueMartModel' ), true );

		/* Set the layout */
//		switch (JRequest::getCmd('task')) {
//			case 'add':
//
//			case 'edit':
				$this->view->setModel( $this->getModel( 'user', 'VirtueMartModel' ), true );
				$this->view->setLayout('media_edit');
//				break;
//			default:
//				$this->view->setLayout('media');
//				break;
//		}

		/* Now display the view. */
		$this->view->display();
	}

	function save(){

		// Check token, how does this really work?
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$fileModel = $this->getModel('media');

		if ($id = $fileModel->store()) {
			$msg = JText::_('VM_FILE_SAVED_SUCCESS');
		}
		else {
			$msg = $fileModel->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=media&task=edit&file_id='.$id;
		} else {
			$redirection = 'index.php?option=com_virtuemart&view=media';
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
		$msg = JText::_('VM_OPERATION_CANCELED');
		//Todo, in case redirect to product
		$this->setRedirect('index.php?option=com_virtuemart&view=media', $msg);
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
			$msg = JText::_('VM_SELECT_ITEM_TO_DELETE');
			$mainframe->redirect('index.php?option=com_virtuemart&view=media', $msg, 'error');
			return;
		}

		$mediaModel = $this->getModel('media');

		if (!$mediaModel->delete($cid)) {
			$msg = JText::_('VM_ERROR_MEDIA_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_( 'VM_MEDIA_DELETED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=media', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers
	 */
	public function publish() {
		// Check token
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mediaModel = $this->getModel('media');
		if (!$mediaModel->publish(true)) {
			$msg = JText::_('VM_ERROR_MEDIA_COULD_NOT_BE_PUBLISHED');
		}
		else{
			$msg = JText::_('VM_MEDIA_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=media', $msg);
	}

}
// pure php no closing tag
