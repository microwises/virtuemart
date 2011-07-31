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

	echo 'called old script';
/*	//Maybe it is possible to set this within the xml file note by Max Milbers
	@ini_set( 'memory_limit', '32M' );
	@ini_set( 'max_execution_time', '120' );

	$db = JFactory::getDBO();
	$model = new VirtueMartModelUpdatesMigration();

	//$query = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
	$query = 'SHOW TABLES LIKE #__virtuemart_adminmenuentries';
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

		JTable::addIncludePath(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'tables');

		$model->setStoreOwner();
	} else {

		$query = 'SHOW COLUMNS FROM `#__virtuemart_products` ';
		$db->setQuery($query);
		$columns = $db->loadResultArray(0);

		if(!in_array('product_ordered',$columns)){
			echo 'is in array';
			$query = 'ALTER TABLE `#__virtuemart_products` ADD product_ordered int(11)';
			$db->setQuery($query);
			$db->query();
		}

	}


	if ($newInstall) {
		// Get the uploaded file information
//		$userfile = JRequest::getVar('install_package', null, 'files', 'array' );
	}


	//Is there an old virtuemart around? then delete the old toolbar
	jimport('joomla.filesystem.file');
	$dspath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'toolbar.virtuemart.php';
	if(JFile::exists($dspath)){
		JFile::delete($dspath);
	}

	$installOk = true;

	JRequest::setVar('newInstall', $newInstall);
	include(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.virtuemart.html.php');

	//$model = $this->getModel('config');
	JModel::addIncludePath(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'models');
	$model = JModel::getInstance('config', 'VirtueMartModel');
	$model->setDangerousToolsOff();

	return $installOk;*/
}

// pure php no tag