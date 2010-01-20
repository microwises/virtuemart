<?php
/**
*
* Store controller
*
* @package	VirtueMart
* @subpackage Store
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

/**
 * Store Controller
 *
 * @package    VirtueMart
 * @subpackage Store
 * @author RickG
 */
class VirtueMartControllerStore extends JController {

    /**
     * Method to display the view
     *
     * @access	public
     * @author
     */
    function __construct() {
	parent::__construct();

	// Register Extra tasks
	$this->registerTask( 'add',  'edit' );

	$document = JFactory::getDocument();
	$viewType = $document->getType();
	$view = $this->getView('store', $viewType);

	// Push a model into the view
	$model = $this->getModel('store');
	if (!JError::isError($model)) {
	    $view->setModel($model, true);
	}
	$model1 = $this->getModel('currency');
	if (!JError::isError($model1)) {
	    $view->setModel($model1, false);
	}
    }

    /**
     * The default store view
     */
    function display() {
	parent::display();
    }

    /**
     * Handle the edit task
     *
     * @author RickG
     */
    function edit() {
	JRequest::setVar('controller', 'store');
	JRequest::setVar('view', 'store');
	JRequest::setVar('layout', 'edit');
	JRequest::setVar('hidemenu', 1);

	parent::display();
    }


    /**
     * Handle the cancel task
     *
     * @author RickG
     */
    function cancel() {
	$this->setRedirect('index.php?option=com_virtuemart&view=store');
    }


    /**
     * Handle the save task
     *
     * @author RickG
     */
    function save() {
	$model = $this->getModel('store');

	if ($model->store()) {
	    $msg = JText::_('Store saved!');
	}
	else {
	    $msg = JText::_($model->getError());
	}

	$this->setRedirect('index.php?option=com_virtuemart&view=store', $msg);
    }


    /**
     * Handle the remove task
     *
     * @author RickG
     */
    function remove() {
	$model = $this->getModel('store');
	if (!$model->delete()) {
	    $msg = JText::_($model->getError());
	}
	else {
	    $msg = JText::_( 'Stores Deleted!');
	}

	$this->setRedirect( 'index.php?option=com_virtuemart&view=store', $msg);
    }


    /**
     * Handle the publish task
     *
     * @author RickG
     */
    function publish() {
	$model = $this->getModel('store');
	if (!$model->publish(true)) {
	    $msg = JText::_('Error: One or more stores could not be published!');
	}

	$this->setRedirect( 'index.php?option=com_virtuemart&view=store', $msg);
    }


    /**
     * Handle the publish task
     *
     * @author RickG
     */
    function unpublish() {
	$model = $this->getModel('store');
	if (!$model->publish(false)) {
	    $msg = JText::_('Error: One or more stores could not be unpublished!');
	}

	$this->setRedirect( 'index.php?option=com_virtuemart&view=store', $msg);
    }
}
?>
