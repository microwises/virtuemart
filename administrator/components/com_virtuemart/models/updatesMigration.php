<?php
/**
 * Data module for updates and migrations
 *
 * @package	VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers, RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vendorhelper.php');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');

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
	//if (!empty($_SESSION['vmLatestVersion'])) {
	//		return $_SESSION['vmLatestVersion'];
	//	}
	//	$VMVERSION =& new vmVersion();
	$url = "http://virtuemart.net/index2.php?option=com_versions&catid=1&myVersion={".VmConfig::getInstalledVersion()."}&task=latestversionastext";
	$result = VmConnector::handleCommunication($url);
	//if ($result !== false) {
	//	// Cache the result for later use
	//		$_SESSION['vmLatestVersion'] = $result;
	//	}
	return $result;
    }


    /**
     * Add existing Joomla users into the Virtuemart database.
     *
     * @author Max Milbers, RickG
     */
    function integrateJoomlaUsers() {
	$db = JFactory::getDBO();
	$query = "SELECT `id`, `registerDate`, `lastvisitDate` FROM `#__users`";
	$db->setQuery($query);
	$row = $db->loadObjectList();

	foreach ($row as $user) {
	    $query = "INSERT INTO `#__vm_shopper_vendor_xref` VALUES ('" . $user->id . "', '1', '5', '')";
	    $db->setQuery($query);
	    if (!$db->query()) {
		JError::raiseNotice(1, 'integrateJUsers INSERT '.$user->id.' INTO #__vm_shopper_vendor_xref FAILED' );
	    }

	    $query = "INSERT INTO `#__vm_user_info` (`user_info_id`, `user_id`, `address_type`, `cdate`, `mdate`) ";
	    $query .= "VALUES( '" . md5(uniqid('virtuemart')) . "', '" . $user->id . "', 'BT', UNIX_TIMESTAMP('" . $user->registerDate . "'), UNIX_TIMESTAMP('" . $user->lastvisitDate."'))";
	    $db->setQuery($query);
	    if (!$db->query()) {
		JError::raiseNotice(1, 'integrateJUsers INSERT '.$user->id.' INTO #__vm_user_info FAILED' );
	    }
	}
    }


    /**
     * @author Max Milbers
     */
    function determineStoreOwner() {
	$user_id = Vendor::getUserIdByVendorId(1);
	if (isset($user_id)) {
	    $user = JFactory::getUser($user_id);
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

	$db->setQuery('SELECT * FROM  `#__vm_auth_user_vendor` WHERE `vendor_id`= "1" ');
	$db->query();
	$oldVendorId = $db->loadResult();

	$db->setQuery('SELECT * FROM  `#__vm_auth_user_vendor` WHERE `user_id`= "' . $userId . '" ');
	$db->query();
	$oldUserId = $db->loadResult();

	if (!isset($oldVendorId) && !isset($oldUserId)) {
	    $db->setQuery('INSERT `#__vm_auth_user_vendor` (`user_id`, `vendor_id`) VALUES ("' . $userId . '", "1")');
	    if ($db->query() == false) {
		JError::raiseNotice(1, 'setStoreOwner ' . $userId . ' was not possible to execute INSERT __vm_auth_user_vendor');
	    }
	    else {
		JError::raiseNotice(1, 'setStoreOwner INSERT __vm_auth_user_vendor '.$userId);
	    }
	}
	else {
	    if (!isset($oldUserId)) {
		$db->setQuery( 'UPDATE `#__vm_auth_user_vendor` SET `user_id` ="'.$userId.'" WHERE `vendor_id` = "1" ');
	    }
	    else {
		$db->setQuery( 'UPDATE `#__vm_auth_user_vendor` SET `vendor_id` = "1" WHERE `user_id` ="'.$userId.'" ');
	    }
	    if ($db->query() == false ) {

	    }
	}

	$db->setQuery( 'UPDATE `#__vm_user_info` SET `user_is_vendor` = "1" WHERE `user_id` ="'.$userId.'"');
	$db->query();
	if (!$db->query()) {
	    JError::raiseNotice(1, 'setStoreOwner failed Update. User with id = ' . $userId . ' not found in table');
	    return 0;
	}
	else {
	    return $userId;
	}

    }


    /**
     * @author Max Milbers
     */
    function setUserToShopperGroup($userId=0) {
	# insert the user <=> group relationship
	$db = JFactory::getDBO();
	$db->setQuery("INSERT INTO `#__vm_auth_user_group`
				SELECT user_id, 
					CASE `perms` 
					    WHEN 'admin' THEN 0
					    WHEN 'storeadmin' THEN 1
					    WHEN 'shopper' THEN 2
					    WHEN 'demo' THEN 3
					    ELSE 2 
					END
				FROM #__vm_user_info
				WHERE address_type='BT' ");
	$db->query();

	$db->setQuery( "UPDATE `#__vm_auth_user_group` SET `group_id` = '0' WHERE `user_id` ='" . $userId . "' ") ;
	$db->query();
    }


    /**
     * Installs sample data to the current database.
     *
     * @author Max Milbers, RickG
     * @params $userId User Id to add the user_info and vendor sample data to
     */
    function installSampleData($userId = null) {
	if ($userId == null) {
	    $userId = $this->determineStoreOwner();
	}

	$vmLogIdentifier = 'VirtueMart';

	$fields = array();

	$fields['user_id'] =  $userId;
	$fields['address_type'] =  "BT";
	$fields['company'] =  "Washupito''s the User";
	$fields['title'] =  "Sire";
	$fields['last_name'] =  "upito";
	$fields['first_name'] =  "Wash";
	$fields['middle_name'] =  "the cheapest";
	$fields['phone_1'] =  "555-555-555";
	$fields['address_1'] =  "vendorra road 8";
	$fields['city'] =  "Canangra";
	$fields['state'] =  "72";
	$fields['country'] =  "13";
	if (!$this->storeSampleUserInfo($fields)) {
	    JError::raiseNotice(1, $this->getError());
	}

	unset($fields);
	$currencyFields = array();
	$currencyFields[0] = 'EUR';
	$currencyFields[1] = 'USD';

	$fields = array();
	$fields['vendor_id'] = Vendor::getVendorIdByUserId($userId);
	$fields['vendor_name'] =  "Washupito";
	$fields['vendor_phone'] =  "555-555-1212";
	$fields['vendor_store_name'] =  "Washupito''s Tiendita";
	$fields['vendor_store_desc'] =  " <p>We have the best tools for do-it-yourselfers.  Check us out! </p> <p>We were established in 1969 in a time when getting good tools was expensive, but the quality was good.  Now that only a select few of those authentic tools survive, we have dedicated this store to bringing the experience alive for collectors and master mechanics everywhere.</p> 		<p>You can easily find products selecting the category you would like to browse above.</p>	";
	$fields['vendor_full_image'] =  "c19970d6f2970cb0d1b13bea3af3144a.gif";
	$fields['vendor_currency '] =  "EUR";
	$fields['vendor_accepted_currencies'] = $currencyFields;
	$fields['vendor_currency_display_style'] =  "1|&euro;|2|,|.|0|0";
	$fields['vendor_terms_of_service'] =  "<h5>You haven''t configured any terms of service yet. Click <a href=administrator/index2.php?page=store.store_form&option=com_virtuemart>here</a> to change this text.</h5>";
	$fields['vendor_url'] = JURI::root();
	$fields['vendor_name'] =  "Washupito";
	if (!$this->storeSampleVendor($fields)) {
	    JError::raiseNotice(1, $this->getError());
	}

	$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data.sql';
	$this->execSQLFile($filename);
    }


    /**
     * Bind the post data to the user info table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
     */
    function storeSampleUserInfo($data) {
	$table = $this->getTable('user_info');

	// Bind the form fields to the unser info table
	if (!$table->bind($data)) {
	    $this->setError($table->getError());
	    return false;
	}

	// Make sure the user info record is valid
	if (!$table->check()) {
	    $this->setError($table->getError());
	    return false;
	}

	// Save the user info record to the database
	if (!$table->store()) {
	    $this->setError($table->getError());
	    return false;
	}

	return true;
    }


    /**
     * Bind the post data to the vendor table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
     */
    function storeSampleVendor($data) {
	$table = $this->getTable('vendor');

	// Bind the form fields to the vendor table
	if (!$table->bind($data)) {
	    $this->setError($table->getError());
	    return false;
	}

	// Make sure the vendor record is valid
	if (!$table->check()) {
	    $this->setError($table->getError());
	    return false;
	}

	// Save the vendor record to the database
	if (!$table->store()) {
	    $this->setError($table->getError());
	    return false;
	}

	return true;
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
	$queries = JInstallerHelper::splitSql($buffer);

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
		    JError::raiseWarning(1, 'JInstaller::install: '.JText::_('SQL Error')." ".$db->stderr(true));
		    return false;
		}
	    }
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


    function removeAllVMTables() {
	$db = JFactory::getDBO();
	$config = JFactory::getConfig();
	$db->setQuery("SHOW TABLES LIKE '".$config->getValue('config.dbprefix')."vm_%'");
	if (!$tables = $db->loadResultArray()) {
	    $this->setError = $db->getErrorMsg();
	    return false;
	}

	foreach ($tables as $table) {
	    $db->setQuery('DROP TABLE ' . $table);
	    $db->query();
	}

	return true;
    }
}
?>