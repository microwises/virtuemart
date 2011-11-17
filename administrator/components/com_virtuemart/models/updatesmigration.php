<?php
/**
*
* Data module for updates and migrations
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

// Load the model framework
jimport( 'joomla.application.component.model');


/**
 * Model class for updates and migrations
 *
 * @package	VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers, RickG
 */
class VirtueMartModelUpdatesMigration extends JModel {

    /**
     * Checks the VirtueMart Server for the latest available Version of VirtueMart
     *
     * @return string Example: 1.1.2
     */
    function getLatestVersion() {

    	if(!class_exists('VmConnector')) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');

		$url = "http://virtuemart.net/index2.php?option=com_versions&catid=1&myVersion={".VmConfig::getInstalledVersion()."}&task=latestversionastext";
		$result = VmConnector::handleCommunication($url);

		return $result;
    }


    /**
     * @author Max Milbers
     */
    function determineStoreOwner() {
		if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
		$virtuemart_user_id = VirtueMartModelVendor::getUserIdByVendorId(1);
		if (isset($virtuemart_user_id) && $virtuemart_user_id > 0) {
		    $this->_user = JFactory::getUser($virtuemart_user_id);
		}
		else {
		    $this->_user = JFactory::getUser();
		}
		return $this->_user->id;
    }


    /**
     * @author Max Milbers
     */
    function setStoreOwner($userId=0) {

		if (empty($userId)) {
		    $userId = $this->determineStoreOwner();
		}

		$oldUserId	= "";
		$oldVendorId = "";

		$db = JFactory::getDBO();

		$db->setQuery('SELECT * FROM  `#__virtuemart_vmusers` WHERE `virtuemart_vendor_id`= "1" ');
		$db->query();
		$oldVendorId = $db->loadResult();

		$db->setQuery('SELECT * FROM  `#__virtuemart_vmusers` WHERE `virtuemart_user_id`= "' . $userId . '" ');
		$db->query();
		$oldUserId = $db->loadResult();

		if (empty($oldVendorId) && empty($oldUserId)) {
		    $db->setQuery('INSERT `#__virtuemart_vmusers` (`virtuemart_user_id`, `user_is_vendor`, `virtuemart_vendor_id`, `perms`) VALUES ("' . $userId . '", "1","1","admin")');
		    if ($db->query() == false) {
				JError::raiseWarning(1, 'setStoreOwner was not possible to execute INSERT __vmusers for virtuemart_user_id '.$userId);
		    }
		    else {
		    	return $userId;
		    }
		}
		else {
		    if (empty($oldUserId)) {
				$db->setQuery( 'UPDATE `#__virtuemart_vmusers` SET `virtuemart_user_id` ="'.$userId.'", `user_is_vendor` = "1", `perms` = "admin" WHERE `virtuemart_vendor_id` = "1" ');
		    }
		    else {
				$db->setQuery( 'UPDATE `#__virtuemart_vmusers` SET `virtuemart_vendor_id` = "1", `user_is_vendor` = "1", `perms` = "admin" WHERE `virtuemart_user_id` ="'.$userId.'" ');
				$db->setQuery( 'UPDATE `#__virtuemart_vmusers` SET `virtuemart_vendor_id` = "0", `user_is_vendor` = "0", `perms` = "" WHERE `virtuemart_user_id` ="'.$oldUserId.'" ');

				//$db->setQuery( 'SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "'.$oldUserId.'" ');
		    }

		    if ($db->query() == false ) {
				JError::raiseWarning(1, 'UPDATE __vmusers failed for virtuemart_user_id '.$userId);
		    } else {
		    	return $userId;
		    }
		}

    }


    /**
     * Syncs user permission
     *
     * @param int virtuemart_user_id
     * @return bool true on success
     * @author Christopher Roussel
     */
    function setUserToPermissionGroup ($userId=0) {
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');

		$usersTable = $this->getTable('vmusers');
		$usersTable->load((int)$userId);

		$perm = Permissions::getInstance();
		$usersTable->perms = $perm->getPermissions($userId);

		$result = $usersTable->check();
		if ($result) {
			$result = $usersTable->store();
		}

		if (!$result) {
			$errors = $usersTable->getErrors();
			foreach($errors as $error) {
				$this->setError(get_class( $this ).'::setUserToPermissionGroup user '.$error);
			}
			return false;
		}

		$xrefTable = $this->getTable('vmuser_shoppergroups');
		$data = $xrefTable->load((int)$userId);

		if (empty($data)) {
			$data = array('virtuemart_user_id'=>$userId, 'virtuemart_shoppergroup_id'=>'0');

			if (!$xrefTable->save($data)) {
				$errors = $xrefTable->getErrors();
				foreach($errors as $error){
					$this->setError(get_class( $this ).'::setUserToPermissionGroup xref '.$error);
				}
				return false;
			}
		}

		return true;
    }


    /**
     * Installs sample data to the current database.
     *
     * @author Max Milbers, RickG
     * @params $userId User Id to add the userinfo and vendor sample data to
     */
    function installSampleData($userId = null) {

	if ($userId == null) {
	    $userId = $this->determineStoreOwner();
	}


	$fields['username'] =  $this->_user->username;
	$fields['virtuemart_user_id'] =  $userId;
	$fields['address_type'] =  'BT';
	// Don't change this company name; it's used in install_sample_data.sql
	$fields['company'] =  "Washupito's the virtual mart";
	$fields['title'] =  'Sire';
	$fields['last_name'] =  'upito';
	$fields['first_name'] =  'Wash';
	$fields['middle_name'] =  'the cheapest';
	$fields['phone_1'] =  '555-555-555';
	$fields['address_1'] =  'vendorra road 8';
	$fields['city'] =  'Canangra';
	$fields['zip'] =  '055555';
	$fields['virtuemart_state_id'] =  '361';
	$fields['virtuemart_country_id'] =  '195';
	$fields['virtuemart_shoppergroup_id'] = '';
	//Dont change this, atm everything is mapped to mainvendor with id=1
	$fields['user_is_vendor'] =  '1';
	$fields['virtuemart_vendor_id'] = '1';
	$fields['vendor_name'] =  'Washupito';
	$fields['vendor_phone'] =  '555-555-1212';
	$fields['vendor_store_name'] =  "Washupito's Tiendita";
	$fields['vendor_store_desc'] =  ' <p>We have the best tools for do-it-yourselfers.  Check us out! </p> <p>We were established in 1969 in a time when getting good tools was expensive, but the quality was good.  Now that only a select few of those authentic tools survive, we have dedicated this store to bringing the experience alive for collectors and master mechanics everywhere.</p> 		<p>You can easily find products selecting the category you would like to browse above.</p>	';
	//$fields['virtuemart_media_id'] =  1;
	$fields['vendor_currency'] =  47;
	$fields['vendor_accepted_currencies'] = '52,26,47,144';
	$fields['vendor_terms_of_service'] =  '<h5>You haven&#39;t configured any terms of service yet. Click <a href="'.JURI::base(true).'/index.php?option=com_virtuemart&view=user&task=editshop">here</a> to change this text.</h5>';
	$fields['vendor_url'] = JURI::root();
	$fields['vendor_name'] =  'Washupito';
	$fields['perms']='admin';

	if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
	$usermodel = new VirtueMartModelUser();
	$usermodel->setId($userId);

	//Save the VM user stuff
	if(!$usermodel->store($fields)){
		$this->setError(JText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA')  );
		JError::raiseWarning('', JText::_('COM_VIRTUEMART_RAISEWARNING_NOT_ABLE_TO_SAVE_USER_DATA'));
	}

	$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data.sql';
	if(!$this->execSQLFile($filename)){
		$this->setError(JText::_('Problems execution of SQL File '.$filename));
	} else {
		$this->setError(JText::_('COM_VIRTUEMART_SAMPLE_DATA_INSTALLED'));
	}

	return true;

    }


    function restoreSystemDefaults() {

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRemove', $this);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_required_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRestore', $this);
    }

    function restoreSystemTablesCompletly() {

		$this->removeAllVMTables();

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

		if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$migrator->createLanguageTables();

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRestore', $this);
    }

    /**
     * Parse a sql file executing each sql statement found.
     *
     * @author Max Milbers
     */
    function execSQLFile($sqlfile) {

		// Check that sql files exists before reading. Otherwise raise error for rollback
		if ( !file_exists($sqlfile) ) {
		    vmError('No SQL file provided!');
		    return false;
		}

		// Create an array of queries from the sql file
		jimport('joomla.installer.helper');
		$queries = JInstallerHelper::splitSql(file_get_contents($sqlfile));

		if (count($queries) == 0) {
		    vmError('SQL file has no queries!');
		    return false;
		}
		$ok = true;
		$db = JFactory::getDBO();
		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $query) {
		    $query = trim($query);
		    if ($query != '' && $query{0} != '#') {
			$db->setQuery($query);
				if (!$db->query()) {
				    JError::raiseWarning(1, 'JInstaller::install: '.$sqlfile.' '.JText::_('COM_VIRTUEMART_SQL_ERROR')." ".$db->stderr(true));
				    $ok = false;
				}
		    }
		}

		return $ok;
    }


/**
 * Done by akeeba release system now
 *
/*    function uploadAndInstallUpdate($packageName) {
		if (!$packageName) {
		    $this->_error = 'No package name provided!';
		    return false;
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');

		$config = JFactory::getConfig();
		$destn = $config->getValue('config.tmp_path').DS.basename($packageName);

		if (!JFile::upload($packageName, $destn)) {
		    $this->setError('Error uploading update package!');
		    return false;
		}

		jimport('joomla.installer.installer');
		$jinstaller = JInstaller::getInstance();
		die($destn);
		$jinstaller->install($destn);
    }*/


    /**
     * Delete all Virtuemart tables.
     *
     * @return True if successful, false otherwise
     */
    function removeAllVMTables() {
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();

		$prefix = $config->getValue('config.dbprefix').'virtuemart_%';
		$db->setQuery('SHOW TABLES LIKE "'.$prefix.'"');
		if (!$tables = $db->loadResultArray()) {
		    $this->setError = $db->getErrorMsg();
		    return false;
		}

		$app = JFactory::getApplication();
		foreach ($tables as $table) {

		    $db->setQuery('DROP TABLE ' . $table);
		    if($db->query()){
		    	$droppedTables[] = substr($table,strlen($prefix)-1);
		    } else {
		    	$errorTables[] = $table;
		    	$app->enqueueMessage('Error drop virtuemart table ' . $table);
		    }
		}


		if(!empty($droppedTables)){
			$app->enqueueMessage('Dropped virtuemart table ' . implode(', ',$droppedTables));
		}

	    if(!empty($errorTables)){
			$app->enqueueMessage('Error dropping virtuemart table ' . implode($errorTables,', '));
			return false;
		}

		return true;
    }


    /**
     * Remove all the data from all Virutmeart tables.
     *
     * @return boolean True if successful, false otherwise.
     */
    function removeAllVMData() {
		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRemove', $this);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_data.sql';
		$this->execSQLFile($filename);

		return true;
    }
}

//pure php no tag
