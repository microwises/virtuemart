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

	$this->installSampleSQL();
// 	$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data.sql';
// 	if(!$this->execSQLFile($filename)){
// 		$this->setError(JText::_('Problems execution of SQL File '.$filename));
// 	} else {
// 		$this->setError(JText::_('COM_VIRTUEMART_SAMPLE_DATA_INSTALLED'));
// 	}

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

		if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$migrator->createLanguageTables();

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

		if(!class_exists('GenericTableUpdater')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
		$updater = new GenericTableUpdater();
		$updater->createLanguageTables();

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



   function installSampleSQL(){

    	//One idea, install sql, using _virtuemart_sample
    	//then adjust migrator capable of using _virtuemart_sample and to import them
    	//problem is the differnt format of the tables

   	//other idea is to create arrays like the formdata and store the stuff and update the following arrays with the valid ids


   	$db = JFactory::getDBO();

   	$q = "INSERT INTO `#__virtuemart_medias` (`virtuemart_media_id`, `virtuemart_vendor_id`, `file_title`, `file_description`, `file_meta`, `file_mimetype`, `file_type`, `file_url`, `file_url_thumb`, `created_on`, `modified_on`, `published`, `file_is_product_image`, `file_is_downloadable`, `file_is_forSale`, `shared`, `file_params`) VALUES
(1, 1, 'black shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/fc2f001413876a374484df36ed9cf775.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(2, 1, 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', '', 'image/jpeg','category', 'images/stories/virtuemart/category/fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(3, 1, 'green shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/756ff6d140e11079caf56955060f1162.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(4, 1, 'wooden shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/1b0c96d67abdbea648cd0ea96fd6abcb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(5, 1, 'black shovel', 'the', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/520efefd6d7977f91b16fac1149c7438.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(6, 1, '480655b410d98a5cc3bef3927e786866.jpg', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/480655b410d98a5cc3bef3927e786866.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(7, 1, 'nice saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(8, 1, 'our ladder', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8cb8d644ef299639b7eab25829d13dbc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(9, 1, 'Hamma', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/578563851019e01264a9b40dcf1c4ab6.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(10, 1, 'drill', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/1ff5f2527907ca86103288e1b7cc3446.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(11, 1, 'circular saw', 'for the fine cut', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/9a4448bb13e2f7699613b2cfd7cd51ad.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(12, 1, 'chain saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8716aefc3b0dce8870360604e6eb8744.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(13, 1, 'hand shovel', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/cca3cd5db813ee6badf6a3598832f2fc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(14, 1, 'manufacturer', '', '', 'image/jpeg', 'manufacturer', 'images/stories/virtuemart/manufacturer/manufacturersample.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(15, 1, 'Washupito', '', '', 'image/jpeg', 'vendor', 'images/stories/virtuemart/vendor/washupito.gif', '', NULL, NULL, 1, 1, 0, 0, 0, '');";

$db->setQuery($q);
$db->query();


   	$q = "INSERT IGNORE INTO `#__virtuemart_calcs` (`virtuemart_calc_id`, `virtuemart_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `created_on`, `modified_on`, `published`, `shared`) VALUES
(1, 1, 'Tax 9.25%', 'A simple tax for all products regardless the category', 'Tax', '+%', 9.25, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL,  1, 0),
(2, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DBTax', '-', 2, '47', 1, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0),
(3, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0);


INSERT IGNORE INTO `#__virtuemart_calc_categories` (`id`, `virtuemart_calc_id`, `virtuemart_category_id`) VALUES
(2, 3, 1),
(5, 4, 2);

INSERT IGNORE INTO `#__virtuemart_calc_shoppergroups` (`id`, `virtuemart_calc_id`, `virtuemart_shoppergroup_id`) VALUES
(11, 0, 5);";

$db->setQuery($q);
$db->query();


$q = "INSERT INTO `#__virtuemart_medias` (`virtuemart_media_id`, `virtuemart_vendor_id`, `file_title`, `file_description`, `file_meta`, `file_mimetype`, `file_type`, `file_url`, `file_url_thumb`, `created_on`, `modified_on`, `published`, `file_is_product_image`, `file_is_downloadable`, `file_is_forSale`, `shared`, `file_params`) VALUES
(1, 1, 'black shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/fc2f001413876a374484df36ed9cf775.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(2, 1, 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', '', 'image/jpeg','category', 'images/stories/virtuemart/category/fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(3, 1, 'green shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/756ff6d140e11079caf56955060f1162.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(4, 1, 'wooden shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/1b0c96d67abdbea648cd0ea96fd6abcb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(5, 1, 'black shovel', 'the', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/520efefd6d7977f91b16fac1149c7438.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(6, 1, '480655b410d98a5cc3bef3927e786866.jpg', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/480655b410d98a5cc3bef3927e786866.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(7, 1, 'nice saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(8, 1, 'our ladder', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8cb8d644ef299639b7eab25829d13dbc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(9, 1, 'Hamma', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/578563851019e01264a9b40dcf1c4ab6.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(10, 1, 'drill', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/1ff5f2527907ca86103288e1b7cc3446.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(11, 1, 'circular saw', 'for the fine cut', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/9a4448bb13e2f7699613b2cfd7cd51ad.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(12, 1, 'chain saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8716aefc3b0dce8870360604e6eb8744.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(13, 1, 'hand shovel', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/cca3cd5db813ee6badf6a3598832f2fc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(14, 1, 'manufacturer', '', '', 'image/jpeg', 'manufacturer', 'images/stories/virtuemart/manufacturer/manufacturersample.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(15, 1, 'Washupito', '', '', 'image/jpeg', 'vendor', 'images/stories/virtuemart/vendor/washupito.gif', '', NULL, NULL, 1, 1, 0, 0, 0, '');


--  Dumping data for `#__virtuemart_calcs`

INSERT IGNORE INTO `#__virtuemart_calcs` (`virtuemart_calc_id`, `virtuemart_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `created_on`, `modified_on`, `published`, `shared`) VALUES
(1, 1, 'Tax 9.25%', 'A simple tax for all products regardless the category', 'Tax', '+%', 9.25, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL,  1, 0),
(2, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DBTax', '-', 2, '47', 1, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0),
(3, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0);



-- Dumping data for table `#__virtuemart_calc_categories`

INSERT IGNORE INTO `#__virtuemart_calc_categories` (`id`, `virtuemart_calc_id`, `virtuemart_category_id`) VALUES
(2, 3, 1),
(5, 4, 2);


-- Dumping data for table `#__virtuemart_calc_shoppergroups`

INSERT IGNORE INTO `#__virtuemart_calc_shoppergroups` (`id`, `virtuemart_calc_id`, `virtuemart_shoppergroup_id`) VALUES
(11, 0, 5);


-- Dumping data for table `#__virtuemart_categories`

INSERT INTO `#__virtuemart_categories` (`virtuemart_category_id`, `virtuemart_vendor_id`,`published`, `created_on`, `modified_on`, `category_template`, `category_layout`, `category_product_layout`, `products_per_row`, `ordering`, `limit_list_start`, `limit_list_step`, `limit_list_max`, `limit_list_initial`, `metarobot`, `metaauthor`) VALUES
(1, 1, 1, NULL, NULL, '0', 'default', 'default', 3, 1, 0, 10, 0, 10, '', ''),
(2, 1, 1, NULL, NULL, '', '', '', 4, 2, NULL, NULL, NULL, NULL, '', ''),
(3, 1, 1, NULL, NULL, '', '', '', 2, 3, NULL, NULL, NULL, NULL, '', ''),
(4, 1, 1, NULL, NULL, '', '', '', 1, 4, NULL, NULL, NULL, NULL, '', ''),
(5, 1, 1, NULL, NULL, '', '', '', 1, 5, NULL, NULL, NULL, NULL, '', '');

INSERT INTO `#__virtuemart_categories_".VMLANG."` (`virtuemart_category_id`, `category_name`, `category_description`, `metadesc`, `metakey`, `slug`) VALUES
(1, 'Hand Tools', 'Hand Tools', '', '', 'handtools'),
(2, 'Power Tools', 'Power Tools', '', '', 'powertools'),
(3, 'Garden Tools', 'Garden Tools', '', '', 'gardentools'),
(4, 'Outdoor Tools', 'Outdoor Tools', '', '', 'outdoortools'),
(5, 'Indoor Tools', 'Indoor Tools', '', '', 'indoortools');


-- Dumping data for table `#__virtuemart_category_categories`

INSERT IGNORE INTO `#__virtuemart_category_categories` (`category_parent_id`, `category_child_id`) VALUES
( 0, 1),
( 0, 2),
( 0, 3),
( 2, 4),
( 2, 5);


-- Dumping data for table `#__virtuemart_category_medias`

INSERT IGNORE INTO `#__virtuemart_category_medias` (`id`,`virtuemart_category_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 1),
(NULL, 2, 2),
(NULL, 3, 3),
(NULL, 4, 4),
(NULL, 5, 5);


-- Dumping data for table `#__virtuemart_customs`

INSERT INTO `#__virtuemart_customs` (`virtuemart_custom_id`, `custom_parent_id`, `admin_only`, `custom_title`, `custom_tip`, `custom_value`, `custom_field_desc`, `field_type`, `is_list`, `is_hidden`, `is_cart_attribute`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
(3, 1, 0, 'Integer', 'Make a choice', '100', 'number', 'I', 0, 0, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(4, 1, 0, 'Yes or no ?', 'Boolean', '0', 'Only 2 choices', 'B', 0, 0, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(7, 0, 0, 'Photo', 'Give a media ID as defaut', '1', 'Add a photo', 'M', 0, 0, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(9, 0, 0, 'Size', 'Change the size', '30', 'CM', 'V', 0, 0, 1, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(11, 0, 0, 'Group of fields', 'Add fields to this parent and they are added all at once', 'I''m a parent', 'Add many fields', 'P', 0, 0, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(12, 1, 0, 'I''m a string', 'Here you can add some text', 'Please enter a text', 'Comment', 'S', 0, 0, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(13, 0, 0, 'Color', '', 'Choose a color', 'Colors', 'S', 0, 0, 1, 1, '2011-05-26 04:06:08', 62, '2011-05-26 04:06:08', 62, '0000-00-00 00:00:00', 0),
(14, 0, 0, 'add a showel', 'The best choice', '', 'Showels', 'M', 0, 0, 1, 1, '2011-05-26 04:11:35', 62, '2011-05-26 04:11:35', 62, '0000-00-00 00:00:00', 0);


-- Dumping data for table  `#__virtuemart_product_customfields`

INSERT INTO `#__virtuemart_product_customfields` (`virtuemart_product_id`,`virtuemart_custom_id`,`custom_value`,`custom_price`,`custom_param`,`published`,`created_on`,`created_by`,`modified_on`,`modified_by`,`locked_on`,`locked_by`,`ordering`) VALUES
(6,4,'0','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,3,'100','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,2,'Plz enter a text','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,7,'1','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(8,11,'7','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,11,'8','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,12,'4','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,12,'2','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'13','8',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'4','20',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'3','12',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'1','15',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'yellow','0.75',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'red','0.5',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'Blue','0',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'150','60',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'100','50',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'60','40',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'50','20',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0);


-- Dumping data for table `#__virtuemart_manufacturers`

INSERT INTO `#__virtuemart_manufacturers` (`virtuemart_manufacturer_id`, `virtuemart_manufacturercategories_id`, `published`) VALUES
(1, 1, 1);

INSERT INTO `#__virtuemart_manufacturers_".VMLANG."` (`virtuemart_manufacturer_id`, `mf_name`, `mf_email`, `mf_desc`, `mf_url`, `slug`) VALUES
(1, 'Manufacturer', ' manufacturer@example.org', 'An example for a manufacturer', 'http://www.example.org', 'manufacturer-example');


-- Dumping data for table `#__virtuemart_manufacturercategories`

INSERT INTO `#__virtuemart_manufacturercategories` (`virtuemart_manufacturercategories_id`, `published`) VALUES
(1, 1);

INSERT INTO `#__virtuemart_manufacturercategories_".VMLANG."` (`virtuemart_manufacturercategories_id`, `mf_category_name`, `mf_category_desc`, `slug`) VALUES
(1, '-default-', 'This is the default manufacturer category', '-default-');


-- Dumping data for table `#__virtuemart_manufacturer_medias`

INSERT IGNORE INTO `#__virtuemart_manufacturer_medias` (`id`,`virtuemart_manufacturer_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 14);


INSERT INTO `#__virtuemart_products` (`virtuemart_product_id`, `virtuemart_vendor_id`, `product_parent_id`, `product_sku`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `product_ordered`, `low_stock_notification`, `product_available_date`, `product_availability`, `product_special`, `product_sales`, `product_unit`, `product_packaging`, `product_params`, `hits`, `intnotes`, `metarobot`, `metaauthor`, `layout`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
(1, 1, 0, 'G01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 10, 0, 5, '2010-02-21 00:00:00', '48h.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(2, 1, 0, 'G02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 76, 0, 5, '2010-02-21 00:00:00', '3-5d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(3, 1, 0, 'G03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 32, 0, 5, '2010-02-21 00:00:00', '7d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(4, 1, 0, 'G04', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 98, 0, 5, '2010-02-21 00:00:00', 'on-order.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(5, 1, 0, 'H01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 32, 0, 5, '2010-02-21 00:00:00', '1-4w.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(6, 1, 0, 'H02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 500, 0, 5, '2011-12-21 00:00:00', '24h.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(7, 1, 0, 'P01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 45, 0, 5, '2011-12-21 00:00:00', '48h.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(8, 1, 0, 'P02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 33, 0, 5, '2010-12-21 00:00:00', '3-5d.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(9, 1, 0, 'P03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 3, 0, 5, '2011-07-21 00:00:00', '2-3d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(10, 1, 0, 'P04', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 2, 0, 5, '2010-12-21 00:00:00', '1-2m.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(11, 1, 1, 'G01-01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(12, 1, 1, 'G01-02', 10.0000, '', 0.0000, 0.0000, 0.0000, '', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(13, 1, 1, 'G01-03', 10.0000, '', 0.0000, 0.0000, 0.0000, '', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(14, 1, 2, 'L01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 22, 0, 5, '2011-12-21 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(15, 1, 2, 'L02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
(16, 1, 2, 'L03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0);

INSERT INTO `#__virtuemart_products_".VMLANG."` (`virtuemart_product_id`, `product_name`, `product_s_desc`, `product_desc`, `metadesc`, `metakey`, `slug`) VALUES
(1, 'Hand Shovel', '<p>Nice hand shovel to dig with in the yard.</p>\r\n', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'hand-shovel'),
(2, 'Ladder', 'A really long ladder to reach high places.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'ladder'),
(3, 'Shovel', 'Nice shovel.  You can dig your way to China with this one.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'shovel'),
(4, 'Smaller Shovel', 'This shovel is smaller but you\'ll be able to dig real quick.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'smaller-shovel'),
(5, 'Nice Saw', 'This saw is great for getting cutting through downed limbs.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'nice-saw'),
(6, 'Hammer', 'A great hammer to hammer away with.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5\" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'hammer'),
(7, 'Chain Saw', 'Don\'t do it with an axe.  Get a chain saw.', '\r\n<ul>  <li>Tool-free tensioner for easy, convenient chain adjustment  </li><li>3-Way Auto Stop; stops chain a fraction of a second  </li><li>Automatic chain oiler regulates oil for proper chain lubrication  </li><li>Small radius guide bar reduces kick-back  </li></ul>  <br />  <b>Specifications</b><br />  12.5 AMPS   <br />   16\" Bar Length   <br />   3.5 HP   <br />   8.05 LBS. Weight   <br />\r\n', '', '', 'chain-saw'),
(8, 'Circular Saw', 'Cut rings around wood.  This saw can handle the most delicate projects.', '\r\n<ul>  <li>Patented Sightline; Window provides maximum visibility for straight cuts  </li><li>Adjustable dust chute for cleaner work area  </li><li>Bail handle for controlled cutting in 90ÔøΩ to 45ÔøΩ applications  </li><li>1-1/2 to 2-1/2 lbs. lighter and 40% less noise than the average circular saw                     </li><li><b>Includes:</b>Carbide blade  </li></ul>  <br />  <b>Specifications</b><br />  10.0 AMPS   <br />   4,300 RPM   <br />   Capacity: 2-1/16\" at 90ÔøΩ, 1-3/4\" at 45ÔøΩ<br />\r\n', '', '', 'circular-saw'),
(9, 'Drill', 'Drill through anything.  This drill has the power you need for those demanding hole boring duties.', '\r\n<font color=\"#000000\" size="3"><ul><li>High power motor and double gear reduction for increased durability and improved performance  </li><li>Mid-handle design and two finger trigger for increased balance and comfort  </li><li>Variable speed switch with lock-on button for continuous use  </li><li><b>Includes:</b> Chuck key &amp; holder  </li></ul>  <br />  <b>Specifications</b><br />  4.0 AMPS   <br />   0-1,350 RPM   <br />   Capacity: 3/8" Steel, 1" Wood   <br /><br />  </font>\r\n', '', '', 'drill'),
(10, 'Power Sander', 'Blast away that paint job from the past.  Use this power sander to really show them you mean business.', '\r\n<ul>  <li>Lever activated paper clamps for simple sandpaper changes  </li><li>Dust sealed rocker switch extends product life and keeps dust out of motor  </li><li>Flush sands on three sides to get into corners  </li><li>Front handle for extra control  </li><li>Dust extraction port for cleaner work environment   </li></ul>  <br />  <b>Specifications</b><br />  1.2 AMPS    <br />   10,000 OPM    <br />\r\n', '', '', 'power-sander'),
(11, 'Hand Shovel', '', '', '', '', 'hand-shovel-g01'),
(12, 'Hand Shovel', '', '', '', '', 'hand-shovel-g02'),
(13, 'Hand Shovel', '', '', '', '', 'hand-shovel-g03'),
(14, 'Metal Ladder', '', '', '', '', 'metal-ladder'),
(15, 'Wooden Ladder', '', '', '', '', 'wooden-ladder'),
(16, 'Plastic Ladder', '', '', '', '', 'plastic-ladder');

INSERT IGNORE INTO `#__virtuemart_product_medias` (`id`,`virtuemart_product_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 13),
(NULL, 2, 8),
(NULL, 3, 5),
(NULL, 4, 4),
(NULL, 5, 7),
(NULL, 6, 9),
(NULL, 7, 12),
(NULL, 8, 11),
(NULL, 9, 10),
(NULL, 10, 6);

INSERT IGNORE INTO `#__virtuemart_vendor_medias` (`id`,`virtuemart_vendor_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 15);
--
-- Dumping data for table `#__virtuemart_product_categories`
--

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_category_id`, `virtuemart_product_id`, `ordering`) VALUES
(1, 1, NULL),
(3, 2, NULL),
(3, 3, NULL),
(3, 4, NULL),
(1, 5, NULL),
(1, 6, NULL),
(4, 7, NULL),
(2, 8, NULL),
(5, 9, NULL),
(2, 10, NULL);


--
-- Dumping data for table `#__virtuemart_product_manufacturers`
--

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `virtuemart_manufacturer_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1);

--
-- Dumping data for table `#__virtuemart_product_prices`
--

INSERT INTO `#__virtuemart_product_prices` (`virtuemart_product_price_id`, `virtuemart_product_id`, `product_price`, `override`, `product_override_price`, `product_tax_id`, `product_discount_id`, `product_currency`, `product_price_vdate`, `product_price_edate`, `virtuemart_shoppergroup_id`, `price_quantity_start`, `price_quantity_end`) VALUES
(1, 5, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0,  5, 0, 0),
(2, 1, '4.49000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(3, 2, '39.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(4, 3, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(5, 4, '17.99000', 1, '77.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(6, 6, '4.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(7, 7, '149.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(8, 8, '220.90000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(9, 9, '48.12000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(10, 10, '74.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(11, 11, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 6, 0, 0),
(12, 12, '14.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(13, 13, '79.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(14, 14, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(15, 15, '59.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(16, 16, '3.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 6, 0, 0);

--
-- Dumping data for table `#__virtuemart_shoppergroups`
--

INSERT IGNORE INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `virtuemart_vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`) VALUES
(NULL, 1, 'Gold Level', 'Gold Level Shoppers.', 0),
(NULL, 1, 'Wholesale', 'Shoppers that can buy at wholesale.', 0);

--
-- Dumping data for table `#__virtuemart_worldzones`
--

INSERT INTO `#__virtuemart_worldzones` (`virtuemart_worldzone_id`, `zone_name`, `zone_cost`, `zone_limit`, `zone_description`, `zone_tax_rate`) VALUES
(1, 'Default', '6.00', '35.00', 'This is the default Shipment Zone. This is the zone information that all countries will use until you assign each individual country to a Zone.', 2),
(2, 'Zone 1', '1000.00', '10000.00', 'This is a zone example', 2),
(3, 'Zone 2', '2.00', '22.00', 'This is the second zone. You can use this for notes about this zone', 2),
(4, 'Zone 3', '11.00', '64.00', 'Another usefull thing might be details about this zone or special instructions.', 2);

INSERT INTO `#__virtuemart_userfield_values` (`virtuemart_userfield_value_id`, `virtuemart_userfield_id`, `fieldtitle`, `fieldvalue`, `sys`, `ordering`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
(null, 10, 'Mr', 'Mr', 0, 0, '', 0, '', 0, '', 0),
(null, 10, 'Mrs', 'Mrs', 0, 1, '', 0, '', 0, '', 0);";

$db->setQuery($q);

if (!$_db->query()) {
	JError::raiseWarning(1, 'VmConfig::installVMConfig: '.JText::_('COM_VIRTUEMART_SQL_ERROR').' '.$_db->stderr(true));
}

/*   	//First write the shoppergroups
   	$sGroups = array( 'shopper_group_name' => 'Gold Level',
   							'shopper_group_desc' => 'Gold Level Shoppers.'
   	);
   	$table = $this->getTable('shoppergroups');
   	$table->bindChecknStore($sGroups);

   	$sGroups = array( 'shopper_group_name' => 'Wholesale',
   	   					'shopper_group_desc' => 'Shoppers that can buy at wholesale.'
   	);
   	$table = $this->getTable('shoppergroups');
   	$table->bindChecknStore($sGroups);

   	if(!class_exists('VirtueMartModelCalc')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'calc.php');
   	$calcModel = new VirtueMartModelCalc();

   	$calc = array();
   	$calc['virtuemart_vendor_id'] = 1;
   	$calc['calc_name'] = 'Tax 9.25%';
   	$calc['calc_descr'] = 'A simple tax for all products regardless the category';
   	$calc['calc_kind'] = 'Tax';
   	$calc['calc_value_mathop'] ='+%';
   	$calc['calc_value'] = 9.25;
   	$calc['calc_currency'] = 47;
   	$calc['calc_shopper_published'] = 1;
   	$calc['calc_vendor_published'] = 1;
   	$calc['publish_up'] = '2011-11-11 11:11:11';
   	$calc['shared'] = 0;
   	$calc['ordering'] = 0;
   	$calc['published'] = 1;

   	$vatRule = $calcModel->store($calc);

   	$calc = array();
   	$calc['virtuemart_vendor_id'] = 1;
   	$calc['calc_name'] = 'Discount for all Hand Tools';
   	$calc['calc_descr'] = 'Discount for all Hand Tools 2 euro';
   	$calc['calc_kind'] = 'DBTax';
   	$calc['calc_value_mathop'] ='-';
   	$calc['calc_value'] = 2;
   	$calc['calc_currency'] = 47;
   	$calc['calc_shopper_published'] = 1;
   	$calc['calc_vendor_published'] = 1;
   	$calc['publish_up'] = '2011-11-11 11:11:11';
   	$calc['shared'] = 0;
   	$calc['ordering'] = 0;
   	$calc['published'] = 1;

   	$discountRule = $calcModel->store($calc);

   	$calc = array();
   	$calc['virtuemart_vendor_id'] = 1;
   	$calc['calc_name'] = 'Duty for Powertools';
   	$calc['calc_descr'] = 'A tax that only effects a certain category, Power Tools, and Shoppergroup';
   	$calc['calc_kind'] = 'Tax';
   	$calc['calc_value_mathop'] =' +%';
   	$calc['calc_value'] = 9.25;
   	$calc['calc_currency'] = 47;
   	$calc['calc_shopper_published'] = 1;
   	$calc['calc_vendor_published'] = 1;
   	$calc['publish_up'] = '2011-11-11 11:11:11';
   	$calc['shared'] = 0;
   	$calc['ordering'] = 0;
   	$calc['published'] = 1;

   	$dutyRule = $calcModel->store($calc);

   	if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'product.php');
   	$productModel = new VirtueMartModelProduct();
*/

    }
}

//pure php no tag
