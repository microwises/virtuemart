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

if(!class_exists('VmController'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmcontroller.php');

/**
 * updatesMigration Controller
 *
 * @package    VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers
 */
class VirtuemartControllerUpdatesMigration extends VmController{

    private $installer;

    /**
     * Method to display the view
     *
     * @access	public
     */
    function __construct(){
	parent::__construct();

	// $this->setMainLangKey('MIGRATION');
	$document = JFactory::getDocument();
	$viewType = $document->getType();
	$view = $this->getView('updatesMigration', $viewType);

	// Push a model into the view
	$model = $this->getModel('updatesMigration');
	if(!JError::isError($model)){
	    $view->setModel($model, true);
	}
    }

    /**
     * Call at begin of every task to check if the permission is high enough.
     * Atm the standard is at least vm admin
     * @author Max Milbers
     */
    private function checkPermissionForTools(){
	//Hardcore Block, we may do that better later
	if(!class_exists('Permissions'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'permissions.php');
	if(!Permissions::getInstance()->check('admin')){
	    $msg = 'Forget IT';
	    $this->setRedirect('index.php?option=com_virtuemart', $msg);
	}
	return true;
    }

    /**
     * Akeeba release system tasks
     * Update
     * @author Max Milbers
     */
    function liveUpdate(){

	$this->setRedirect('index.php?option=com_virtuemart&view=liveupdate.', $msg);
    }

    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function checkForLatestVersion(){
	$model = $this->getModel('updatesMigration');
	JRequest::setVar('latestverison', $model->getLatestVersion());
	JRequest::setVar('view', 'updatesMigration');

	parent::display();
    }

    /**
     * Install sample data into the database
     *
     * @author RickG
     * @author Max Milbers
     */
    function installSampleData(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$model = $this->getModel('updatesMigration');

	$msg = $model->installSampleData();

	$this->setRedirect($this->redirectPath, $msg);
    }

    /**
     * Install sample data into the database
     *
     * @author RickG
     * @author Max Milbers
     */
    function userSync(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$model = $this->getModel('updatesMigration');
	$msg = $model->integrateJoomlaUsers();

	$this->setRedirect($this->redirectPath, $msg);
    }

    /**
     * Sets the storeowner to the currently logged in user
     * He needs to have admin rights todo so
     *
     * @author Max Milbers
     */
    function setStoreOwner(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$model = $this->getModel('updatesMigration');
	$msg = $model->setStoreOwner();

	$this->setRedirect($this->redirectPath, $msg);
    }

    /**
     * Install sample data into the database
     *
     * @author RickG
     * @author Max Milbers
     */
    function restoreSystemDefaults(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	if(VmConfig::get('dangeroustools', false)){

	    $model = $this->getModel('updatesMigration');
	    $model->restoreSystemDefaults();

	    $msg = JText::_('COM_VIRTUEMART_SYSTEM_DEFAULTS_RESTORED');
	    $msg .= ' User id of the main vendor is ' . $model->setStoreOwner();
	    $this->setDangerousToolsOff();
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}

	$this->setRedirect($this->redirectPath, $msg);
    }

    /**
     * Remove all the Virtuemart tables from the database.
     *
     * @author RickG
     * @author Max Milbers
     */
    function deleteVmTables(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMTABlES_DELETED');
	if(VmConfig::get('dangeroustools', false)){
	    $model = $this->getModel('updatesMigration');

	    if(!$model->removeAllVMTables()){
		$this->setDangerousToolsOff();
		$this->setRedirect('index.php?option=com_virtuemart', $model->getError());
	    }
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}
	$this->setRedirect('index.php?option=com_installer', $msg);
    }

    /**
     * Deletes all dynamical created data and leaves a "fresh" installation without sampeldata
     * OUTDATED
     * @author Max Milbers
     *
     */
    function deleteVmData(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMDATA_DELETED');
	if(VmConfig::get('dangeroustools', false)){
	    $model = $this->getModel('updatesMigration');

	    if(!$model->removeAllVMData()){
		$this->setDangerousToolsOff();
		$this->setRedirect('index.php?option=com_virtuemart', $model->getError());
	    }
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}

	$this->setRedirect($this->redirectPath, $msg);
    }

    function deleteAll(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$msg = JText::_('COM_VIRTUEMART_SYSTEM_ALLVMDATA_DELETED');
	if(VmConfig::get('dangeroustools', false)){

	    $this->installer->populateVmDatabase("delete_essential.sql");
	    $this->installer->populateVmDatabase("delete_data.sql");
	    $this->setDangerousToolsOff();
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}

	$this->setRedirect($this->redirectPath, $msg);
    }

    function deleteRestorable(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	$msg = JText::_('COM_VIRTUEMART_SYSTEM_RESTVMDATA_DELETED');
	if(VmConfig::get('dangeroustools', false)){
	    $this->installer->populateVmDatabase("delete_restoreable.sql");
	    $this->setDangerousToolsOff();
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}


	$this->setRedirect($this->redirectPath, $msg);
    }

    function refreshCompleteInstall(){

	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	if(VmConfig::get('dangeroustools', true)){

	    $model = $this->getModel('updatesMigration');

	    $model->restoreSystemTablesCompletly();

	    $model->integrateJoomlaUsers();
	    $id = $model->determineStoreOwner();
	    $sid = $model->setStoreOwner($id);
	    $model->setUserToPermissionGroup($id);
	    $model->installSampleData($id);
	    $errors = $model->getErrors();

	    $msg = '';
	    if(empty($errors))
		$msg = 'System succesfull restored and sampeldata installed, user id of the mainvendor is ' . $sid;
	    foreach($errors as $error){
		$msg .= ( $error) . '<br />';
	    }

	    $this->setDangerousToolsOff();
	}else {
	    $msg = $this->_getMsgDangerousTools();
	}

	$this->setRedirect($this->redirectPath, $msg);
    }

    /**
     * This function resets the flag in the config that dangerous tools can't be executed anylonger
     * This is a security feature
     *
     * @author Max Milbers
     */
    function setDangerousToolsOff(){

	$model = $this->getModel('config');
	//$model->setDangerousToolsOff();
    }

    /**
     * Sends the message to the user that the tools are disabled.
     *
     * @author Max Milbers
     */
    function _getMsgDangerousTools(){
	$uri = JFactory::getURI();
	$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
	$msg = JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_DISABLED', $link);
	return $msg;
    }

    function portMedia(){
	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
	$migrator = new Migrator();
	$result = $migrator->portMedia();

	$this->setRedirect($this->redirectPath, $result);
    }

    function migrateAllInOne(){
	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));
	$this->checkPermissionForTools();

	if(!VmConfig::get('dangeroustools', true)){
	    $msg = $this->_getMsgDangerousTools();
	    $this->setRedirect($this->redirectPath, $msg);
	    return false;
	}

	require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
	$migrator = new Migrator();
	$result = $migrator->migrateAllInOne();
	$msg = 'Migration finished';
	$this->setRedirect($this->redirectPath, $msg);
    }

}

