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
     * Add existing Joomla users into the Virtuemart database.
     *
     * @author Max Milbers, RickG
     */
    function integrateJoomlaUsers() {

	    $msg = JText::_('COM_VIRTUEMART_START_SYNCRONIZING');
		$db = JFactory::getDBO();
		$query = "SELECT `id`, `registerDate`, `lastvisitDate` FROM `#__users`";
		$db->setQuery($query);
		$row = $db->loadObjectList();

		foreach ($row as $user) {

			$query = 'INSERT IGNORE INTO `#__virtuemart_vmusers` (`virtuemart_user_id`,`user_is_vendor`,`virtuemart_vendor_id`,`customer_number`,`perms` ) VALUES ("'. $user->id .'",0,0,null,"shopper")';
			$db->setQuery($query);
		    if (!$db->query()) {
				JError::raiseNotice(1, 'integrateJUsers INSERT '.$user->id.' INTO #__virtuemart_vmusers FAILED' );
		    }

			$q = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_shoppergroups` WHERE `default`="1" AND `virtuemart_vendor_id`="1" ';
			$this->_db->setQuery($q);
			$default_virtuemart_shoppergroup_id=$this->_db->loadResult();

			$query = 'INSERT IGNORE INTO `#__virtuemart_vmuser_shoppergroups` VALUES (null,"' . $user->id . '", "'.$default_virtuemart_shoppergroup_id.'")';
		    $db->setQuery($query);
		    if (!$db->query()) {
				JError::raiseNotice(1, 'integrateJUsers INSERT '.$user->id.' INTO #__virtuemart_vmuser_shoppergroups FAILED' );
		    }

		    $query = "INSERT IGNORE INTO `#__virtuemart_userinfos` (`virtuemart_userinfo_id`, `virtuemart_user_id`, `address_type`, `created_on`, `modified_on`) ";
		    $query .= "VALUES( '" . md5(uniqid('virtuemart')) . "', '" . $user->id . "', 'BT', UNIX_TIMESTAMP('" . $user->registerDate . "'), UNIX_TIMESTAMP('" . $user->lastvisitDate."'))";
		    $db->setQuery($query);
		    if (!$db->query()) {
				JError::raiseNotice(1, 'integrateJUsers INSERT '.$user->id.' INTO #__virtuemart_userinfos FAILED' );
		    }
		}
		$msg = JText::_('COM_VIRTUEMART_USERS_SYNCRONIZED');
		return $msg;
    }


    /**
     * @author Max Milbers
     */
    function determineStoreOwner() {
		if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
		$virtuemart_user_id = VirtueMartModelVendor::getUserIdByVendorId(1);
		if (isset($virtuemart_user_id) && $virtuemart_user_id > 0) {
		    $user = JFactory::getUser($virtuemart_user_id);
		}
		else {
		    $user = JFactory::getUser();
		}
		return $user->id;
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

//	$currencyFields = array();
//	$currencyFields[0] = 47; //EUR
//	$currencyFields[1] = 144; //USD
////
//	$fields = array();

//	$fields['virtuemart_userinfo_id'] = $db->loadResult();
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
	//Dont change this, atm everything is mapped to mainvendor with id=1
	$fields['user_is_vendor'] =  '1';
	$fields['virtuemart_vendor_id'] = '1';
	$fields['vendor_name'] =  'Washupito';
	$fields['vendor_phone'] =  '555-555-1212';
	$fields['vendor_store_name'] =  "Washupito's Tiendita";
	$fields['vendor_store_desc'] =  ' <p>We have the best tools for do-it-yourselfers.  Check us out! </p> <p>We were established in 1969 in a time when getting good tools was expensive, but the quality was good.  Now that only a select few of those authentic tools survive, we have dedicated this store to bringing the experience alive for collectors and master mechanics everywhere.</p> 		<p>You can easily find products selecting the category you would like to browse above.</p>	';
	$fields['virtuemart_media_id'] =  1;
	$fields['vendor_currency'] =  47;
	$fields['vendor_accepted_currencies'] = '52,26,47,144';
	$fields['vendor_terms_of_service'] =  "<h5>You haven't configured any terms of service yet. Click <a href=administrator/index.php?option=com_virtuemart&view=user&task=editshop>here</a> to change this text.</h5>";
	$fields['vendor_url'] = JURI::root();
	$fields['vendor_name'] =  'Washupito';
	$fields['perms']='admin';

	if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
	$usermodel = new VirtueMartModelUser();
	$usermodel->setId($userId);
	$usermodel->store($fields);
   	$errors = $usermodel->getErrors();
   	$msg ='';
	if(empty($errors)) $msg = 'user id of the mainvendor is '.$sid;
	foreach($errors as $error){
//		$msg .= ($error).'<br />';
		$this->setError($error);
	}
//		$this->setError($usermodel->getError());
//	    JError::raiseNotice(1, 'Problems saving user and/or vendor data of the sample store '.$this->getError());
//	}

	$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data.sql';
	if(!$this->execSQLFile($filename)){
//		$msg .= JText::_('Problems execution of SQL File '.$filename);
		$this->setError(JText::_('Problems execution of SQL File '.$filename));
	} else {
		$this->setError(JText::_('COM_VIRTUEMART_SAMPLE_DATA_INSTALLED'));
//		$msg .= JText::_('COM_VIRTUEMART_SAMPLE_DATA_INSTALLED');
	}

	return true;

    }


    function restoreSystemDefaults() {

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_required_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$this->installVMconfig();

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

    }

    function restoreSystemTablesCompletly() {

		$this->removeAllVMTables();

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$this->installVMconfig();

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

    }

    /**
     * Parse a sql file executing each sql statement found.
     *
     * @author Max Milbers
     */
    function execSQLFile($sqlfile) {
	// Check that sql files exists before reading. Otherwise raise error for rollback
	if ( !file_exists($sqlfile) ) {
	    $this->setError('No SQL file provided!');
	    return false;
	}

	// Create an array of queries from the sql file
	jimport('joomla.installer.helper');
	$queries = JInstallerHelper::splitSql(file_get_contents($sqlfile));

	if (count($queries) == 0) {
	    $this->setError('SQL file has no queries!');
	    return false;
	}

	$db = JFactory::getDBO();
	// Process each query in the $queries array (split out of sql file).
	foreach ($queries as $query) {
	    $query = trim($query);
	    if ($query != '' && $query{0} != '#') {
		$db->setQuery($query);
		if (!$db->query()) {
		    JError::raiseWarning(1, 'JInstaller::install: '.JText::_('COM_VIRTUEMART_SQL_ERROR')." ".$db->stderr(true));
		    return false;
		}
	    }
	}

	return true;
    }


	/**
	 * Read the file vm_config.dat from the install directory, compose the SQL to write
	 * the config record and store it to the dabase.
	 *
	 * @param $_section Section from the virtuemart_defaults.cfg file to be parsed. Currently, only 'config' is implemented
	 * @return Boolean; true on success, false otherwise
	 * @author Oscar van Eijk
	 */
	public function installVMconfig($_section = 'config')
	{
		$_datafile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'virtuemart_defaults.cfg';
		if (!file_exists($_datafile)) {
			JError::raiseWarning(500, 'The data file with the default configuration could not be found. You must configure the shop manually.');
			return false;
		}
		$_section = '['.strtoupper($_section).']';
		$_data = fopen($_datafile, 'r');
		$_configData = array();
		$_switch = false;
		while ($_line = fgets ($_data)) {
			$_line = trim($_line);
			if (strpos($_line, '#') === 0) {
				continue; // Commentline
			}
			if ($_line == '') {
				continue; // Empty line
			}
			if (strpos($_line, '[') === 0) {
				// New section, check if it's what we want
				if (strtoupper($_line) == $_section) {
					$_switch = true; // Ok, right section
				} else {
					$_switch = false;
				}
				continue;
			}
			if (!$_switch) {
				continue; // Outside a section or inside the wrong one.
			}
			if (preg_match_all('/\{(\w+?)\}/', $_line, $_matches)) {
				foreach ($_matches[1] as $_match) {
					if (defined($_match)) {
						$_line = preg_replace("/\{$_match\}/", constant($_match), $_line);
					}
				}
			}
			if (strpos($_line, '=') === false) {
				$_line .= '=';
			}
			$_configData[] = $_line;
		}

		fclose ($_data);

		$_value = join('\n', $_configData);
		if (!$_value) {
			return false; // Nothing to do
		}

		if ($_section == '[CONFIG]') {
			$_qry = "INSERT INTO `#__virtuemart_configs` (`virtuemart_config_id`, `config`) VALUES (null, '$_value')";
		}
		// Other sections can be implemented here

		// Write to the DB
		$_db = JFactory::getDBO();
		$_db->setQuery($_qry);
		if (!$_db->query()) {
			JError::raiseWarning(1, 'JInstaller::install: '.JText::_('COM_VIRTUEMART_SQL_ERROR').' '.$_db->stderr(true));
			return false;
		}
		return true;
	}

    function uploadAndInstallUpdate($packageName) {
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
    }


    /**
     * Delete all Virtuemart tables.
     *
     * @return True if successful, false otherwise
     */
    function removeAllVMTables() {
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();

	    $prefix = $config->getValue('config.dbprefix').'vm_%';
		$db->setQuery('SHOW TABLES LIKE "'.$prefix.'"');
		if (!$tables = $db->loadResultArray()) {
		    $this->setError = $db->getErrorMsg();
	//	    return false;
		}

		foreach ($tables as $table) {

			//lets rename them instead drop
//			$db->setQuery('RENAME TABLE '.$table.' TO old'.$table);

		    $db->setQuery('DROP TABLE ' . $table);
		    if($db->query()){
		    	$droppedTables[] = substr($table,strlen($prefix)-1);
		    } else {
		    	$errorTables[] = $table;
		    	$app->enqueueMessage('Error drop virtuemart table ' . $table);
		    }
		}

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
		$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_data.sql';
		$this->execSQLFile($filename);

		return true;
    }
}

//pure php no tag