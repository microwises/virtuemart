<?php
/**
 * VirtueMart installation file.
 *
 * This installation file is executed after the XML manifest file is complete.
 * This installation function extracts some of the frontend and backend files
 * need for this component.
 *
 * @author Max Milbers, RickG
 * @package VirtueMart
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');
require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'updatesmigration.php');
require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();

function com_install(){

	//Maybe it is possible to set this within the xml file note by Max Milbers
	@ini_set( 'memory_limit', '32M' );
	@ini_set( 'max_execution_time', '120' );

	$db = JFactory::getDBO();
	$model = new VirtueMartModelUpdatesMigration();

	$query = "SELECT count(id) AS idCount FROM `#__vm_menu_admin`";
	$db->setQuery($query);
	$result = $db->loadObject();
	if ($result->idCount > 0) {
		$newInstall = false;
	}
	else {
		$newInstall = true;
	}

	if ($newInstall) {
		// Install Essential Data
		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$model->execSQLFile($filename);
		// Install Required Data
		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$model->execSQLFile($filename);

	}

	$model->integrateJoomlaUsers();
	$id = $model->determineStoreOwner();
	$model->setStoreOwner($id);

	if ($newInstall) {
		// Get the uploaded file information
//		$userfile = JRequest::getVar('install_package', null, 'files', 'array' );
//		dump($userfile,'my user file');
	}

	$installOk = true;

	include(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.virtuemart.html.php');

	return $installOk;
}

// pure php no tag