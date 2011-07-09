<?php
/**
 * VirtueMart script file
 *
 * This file is executed during install/upgrade and uninstall
 *
 * @author Max Milbers, RickG, impleri
 * @package VirtueMart
 */
defined('_JEXEC') or die('Restricted access');

//Maybe it is possible to set this within the xml file note by Max Milbers
@ini_set( 'memory_limit', '32M' );
@ini_set( 'max_execution_time', '120' );

jimport( 'joomla.application.component.model');

// hack to prevent defining these twice in 1.6 installation
if (!defined('_VM_SCRIPT_INCLUDED')) {
	define('_VM_SCRIPT_INCLUDED', true);

	/**
	 * VirtueMart custom installer class
	 */
	class com_virtuemartInstallerScript {

		/**
		 * Pre-process method (e.g. install/upgrade) and any header HTML
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 * @return boolean True if VM exists, null otherwise
		 */
		public function preflight ($type, $parent=null) {
			$db = JFactory::getDBO();
			$query = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result->idCount > 0) {
				// return true so com_install wrapper will know what to do in j1.5
				if ($parent == null) {
					return true;
				}
				$parent->getParent()->setUpgrade(true);
			}
		}

		/**
		 * Load VM Core
		 *
		 * @return string Path to VM admin root
		 */
		public function loadVm() {
			$path = JInstaller::getInstance()->getPath('extension_administrator');

			require_once($path.DS.'helpers'.DS.'config.php');
			JTable::addIncludePath($path.DS.'tables');
			JModel::addIncludePath($path.DS.'models');
			VmConfig::loadConfig();
			return $path;
		}

		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install ($parent=null) {
			$path = $this->loadVm();
			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($path.DS.'install'.DS.'install_essential_data.sql');
			$model->execSQLFile($path.DS.'install'.DS.'install_required_data.sql');

			include($path.DS.'install'.DS.'install.virtuemart.html.php');

			// perhaps a redirect to updatesMigration here rather than the html file?
//			$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			return true;
		}

		/**
		 * Update script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function update ($parent=null) {
			$path = $this->loadVm();
			// probably should just go to updatesMigration rather than the install success screen
			include($path.DS.'install'.DS.'install.virtuemart.html.php');
	//		$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			return true;
		}

		/**
		 * Uninstall script
		 * Triggers before database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function uninstall ($parent=null) {
			$path = $this->loadVm();
			include($path.DS.'install'.DS.'uninstall.virtuemart.html.php');

			return true;
		}

		/**
		 * Post-process method (e.g. footer HTML, redirect, etc)
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 */
		public function postflight ($type, $parent=null) {
			if ($type != 'uninstall') {
				$this->loadVm();
				// is this going to be used?
				// Get the uploaded file information
	//			$userfile = JRequest::getVar('install_package', null, 'files', 'array' );

				$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
				$model->integrateJoomlaUsers();
				$model->setStoreOwner();

				$config = JModel::getInstance('config', 'VirtueMartModel');
				$config->setDangerousToolsOff();
			}
			return true;
		}
	}

	/**
	 * Legacy j1.5 function to use the 1.6 class install/update
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_install() {
		$vmInstall = new com_virtuemartInstallerScript();
		$upgrade = $vmInstall->preflight('install');
		$vmInstall->loadVm();

		if ((VmConfig::isJ15())) {
			$method = ($upgrade) ? 'update' : 'install';
			$vmInstall->$method();
			$vmInstall->postflight($method);
		}

		return true;
	}

	/**
	 * Legacy j1.5 function to use the 1.6 class uninstall
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_uninstall() {
		$vmInstall = new com_virtuemartInstallerScript();
		$vmInstall->preflight('uninstall');
		$vmInstall->loadVm();

		if (VmConfig::isJ15()) {
			$vmInstall->uninstall();
			$vmInstall->postflight('uninstall');
		}

		return true;
	}

} // if(defined)

// pure php no tag
