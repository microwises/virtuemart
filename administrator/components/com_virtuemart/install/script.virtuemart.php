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

// 		public function __construct() {

// 		}

		/**
		 * method must be called after preflight
		 * Sets the paths and loads VMFramework config
		 */
		public function loadVm() {
			$this->path = JInstaller::getInstance()->getPath('extension_administrator');

			require_once($this->path.DS.'helpers'.DS.'config.php');
			JTable::addIncludePath($this->path.DS.'tables');
			JModel::addIncludePath($this->path.DS.'models');

		}

		/**
		 * Pre-process method (e.g. install/upgrade) and any header HTML
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 * @return boolean True if VM exists, null otherwise
		 */
		public function preflight ($type, $parent=null) {

			$update = false;

			$db = JFactory::getDBO();
			
			$q = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
			$db->setQuery($q);
			$result = $db->loadResult();
			echo 'Result <pre>'.print_r($result,1).'</pre>';
			if (empty($result)) {
				$update = false;
			} else {
				$update = true;
			}
			
			echo 'Update? <pre>'.print_r($update,1).'</pre>';
			
			JRequest::setVar('update', $update);
			// return true so com_install wrapper will know what to do in j1.5
			if ($parent == null) {
				return $update;
			}

			$parent->getParent()->setUpgrade($update);

		}


		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install ($parent=null) {
			echo 'execute install';
			$this->loadVm();
			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_essential_data.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_required_data.sql');

			$model->setStoreOwner();

			include($this->path.DS.'install'.DS.'install.virtuemart.html.php');

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
			echo 'execute update';
			$this->loadVm();
			$db = JFactory::getDBO();
			$query = 'SHOW COLUMNS FROM `#__virtuemart_products` ';
			$db->setQuery($query);
			$columns = $db->loadResultArray(0);

			if(!in_array('product_ordered',$columns)){

				$query = 'ALTER TABLE `#__virtuemart_products` ADD product_ordered int(11)';
				$db->setQuery($query);
				$db->query();
			}

			// probably should just go to updatesMigration rather than the install success screen
			include($this->path.DS.'install'.DS.'install.virtuemart.html.php');
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

			$this->loadVm();
			include($this->path.DS.'install'.DS.'uninstall.virtuemart.html.php');

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

				// is this going to be used?
				// Get the uploaded file information
	//			$userfile = JRequest::getVar('install_package', null, 'files', 'array' );

// 				$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
// 				$model->integrateJoomlaUsers();
// 				$model->setStoreOwner();
				$this->loadVm();
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

		if(version_compare(JVERSION,'1.6.0','ge')) {
			// Joomla! 1.6 code here
		} else {
			// Joomla! 1.5 code here
			$method = ($upgrade) ? 'update' : 'install';
			$vmInstall->$method();
			$vmInstall->postflight($method);
		}

/*		if ((VmConfig::isJ15())) {
			$method = ($upgrade) ? 'update' : 'install';
			$vmInstall->$method();
			$vmInstall->postflight($method);
		}*/

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

		if(version_compare(JVERSION,'1.6.0','ge')) {
			// Joomla! 1.6 code here
		} else {
			$vmInstall->uninstall();
			$vmInstall->postflight('uninstall');
		}

/*		if (VmConfig::isJ15()) {
			$vmInstall->uninstall();
			$vmInstall->postflight('uninstall');
		}*/

		return true;
	}

} // if(defined)

// pure php no tag
