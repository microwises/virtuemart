<?php
/**
*
* updatesMigration controller
*
* @package	VirtueMart
* @subpackage updatesMigration
* @author Max Milbers, RickG
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
 * updatesMigration Controller
 *
 * @package    VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers
 */
class VirtuemartControllerUpdatesMigration extends JController {

    private $installer;

    /**
     * Method to display the view
     *
     * @access	public
     */
    function __construct() {
	parent::__construct();

	$document = JFactory::getDocument();
	$viewType = $document->getType();
	$view = $this->getView('updatesMigration', $viewType);

	// Push a model into the view
	$model = $this->getModel('updatesMigration');
	if (!JError::isError($model)) {
	    $view->setModel($model, true);
	}
    }

    /**
     * Display the upgrade view
     *
     * @author RickG
     */
    function display() {
	parent::display();
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function checkForLatestVersion() {
	$model = $this->getModel('updatesMigration');
	JRequest::setVar('latestverison', $model->getLatestVersion());
	JRequest::setVar('view', 'updatesMigration');

	parent::display();
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function installSampleData() {
	$model = $this->getModel('updatesMigration');
	$model->installSampleData();

	$msg = JText::_('Sample data installed!!');
	$this->setRedirect('index.php?option=com_virtuemart&view=updatesmigration', $msg);
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function userSync() {
	$model = $this->getModel('updatesMigration');
	$model->integrateJoomlaUsers();

	$msg = JText::_('Users Syncronized!');
	$this->setRedirect('index.php?option=com_virtuemart&view=updatesmigration', $msg);
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function restoreSystemDefaults() {
	$model = $this->getModel('updatesMigration');
	$model->restoreSystemDefaults();

	$msg = JText::_('System defaults restored!');
	$this->setRedirect('index.php?option=com_virtuemart&view=updatesmigration', $msg);
    }


    /**
     * Remove all the Virtuemart tables from the database.
     *
     * @author RickG
     */
    function deleteVmTables() {
	$model = $this->getModel('updatesMigration');
	if (!$model->removeAllVMTables()) {
	    $this->setRedirect('index.php?option=com_virtuemart', $model->getError());
	}
	else {
	    $this->setRedirect('index.php?option=com_installer');
	}
    }


    function deleteVmData() {
	$model = $this->getModel('updatesMigration');
	if (!$model->removeAllVMData()) {
	    $this->setRedirect('index.php?option=com_virtuemart', $model->getError());
	}
	else {
	    $this->setRedirect('index.php?option=com_virtuemart&view=updatesmigration');
	}
    }


    function deleteAll() {
	$this -> installer -> populateVmDatabase("delete_essential.sql");
	$this -> installer -> populateVmDatabase("delete_data.sql");
	$this->setRedirect(JPATH_ADMINISTRATOR, $msg);
    }


    function deleteRestorable() {
	$this -> installer -> populateVmDatabase("delete_restoreable.sql");
	$this->setRedirect(JPATH_ADMINISTRATOR, $msg);
    }




}
