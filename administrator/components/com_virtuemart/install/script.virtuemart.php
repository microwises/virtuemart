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

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('JPATH_VM_ADMINISTRATOR') or define('JPATH_VM_ADMINISTRATOR', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart');

// hack to prevent defining these twice in 1.6 installation
if (!defined('_VM_SCRIPT_INCLUDED')) {
	define('_VM_SCRIPT_INCLUDED', true);


	/**
	 * VirtueMart custom installer class
	 */
	class com_virtuemartInstallerScript {


		/**
		 * method must be called after preflight
		 * Sets the paths and loads VMFramework config
		 */
		public function loadVm() {
			$this->path = JInstaller::getInstance()->getPath('extension_administrator');

			if(empty($this->path)){
				$this->path = JPATH_VM_ADMINISTRATOR;
			}
			require_once($this->path.DS.'helpers'.DS.'config.php');
			JTable::addIncludePath($this->path.DS.'tables');
			JModel::addIncludePath($this->path.DS.'models');

		}

		public function checkIfUpdate(){

			$update = false;
			$db = JFactory::getDBO();
			$q = 'SHOW TABLES LIKE "%virtuemart_adminmenuentries%"'; //=>jos_virtuemart_shipment_plg_weight_countries
			$db->setQuery($q);
			if($db->loadResult()){

				$q = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
				$db->setQuery($q);
				$result = $db->loadResult();

				if (empty($result)) {
					$update = false;
				} else {
					$update = true;
				}
			} else {
				$update = false;
			}

			$this->update = $update;
			return $update;
		}


		/**
		 * Pre-process method (e.g. install/upgrade) and any header HTML
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 * @return boolean True if VM exists, null otherwise
		 */
		/*		public function preflight ($type, $parent=null) {


		}*/


		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install ($loadVm = true) {

			if($loadVm) $this->loadVm();

			if($this->checkIfUpdate()){
				return $this->update($loadVm);
			}

			if(version_compare(JVERSION,'1.6.0','ge')) {
				$fields = array('data'=>'`data` varchar(30480) NULL AFTER `time`');
				$this->alterTable('#__session',$fields);
			}

			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			//			if(!class_exists('VirtueMartModelUpdatesMigration')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'updatesMigration.php');
			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));

			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql',$lang);
			$model->execSQLFile($this->path.DS.'install'.DS.'install_essential_data.sql',$lang);
			$model->execSQLFile($this->path.DS.'install'.DS.'install_required_data.sql',$lang);

			$id = $model->determineStoreOwner();
			$model->setStoreOwner($id);

			//copy sampel media
			$src = $this->path .DS. 'assets' .DS. 'images' .DS. 'vmsampleimages';
			// 			if(version_compare(JVERSION,'1.6.0','ge')) {

			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'shipment');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'payment');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'category');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'category'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'manufacturer');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'manufacturer'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'product');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'product'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'forSale');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'forSale'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'typeless');


			$dst = JPATH_ROOT .DS. 'images' .DS. 'stories' .DS. 'virtuemart';

			$this->recurse_copy($src,$dst);

			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));
			if(!class_exists('GenericTableUpdater')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
			$updater = new GenericTableUpdater();
			$updater->createLanguageTables();


			$this->displayFinished(false);

			//include($this->path.DS.'install'.DS.'install.virtuemart.html.php');

			// perhaps a redirect to updatesMigration here rather than the html file?
			//			$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			return true;
		}


		/**
		 * creates a folder with empty html file
		 *
		 * @author Max Milbers
		 *
		 */
		public function createIndexFolder($path){
			if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');
			if(JFolder::create($path)) {
				if(!JFile::exists($path .DS. 'index.html')){
					JFile::copy(JPATH_ROOT.DS.'components'.DS.'index.html', $path .DS. 'index.html');
				}
				return true;
			}
			return false;
		}

		/**
		 * Update script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function update ($loadVm = true) {

			if($loadVm) $this->loadVm();

			if(!$this->checkIfUpdate()){
				return $this->install($loadVm);
			}

			$this->_db = JFactory::getDBO();

			if(empty($this->path)) $this->path = JPATH_VM_ADMINISTRATOR;

			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));

			if(!class_exists('VirtueMartModelUpdatesMigration')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'updatesmigration.php');
			$model = new VirtueMartModelUpdatesMigration(); //JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql',$lang);
			// 			$this->displayFinished(true);
			//return false;

			if(version_compare(JVERSION,'1.6.0','ge')) {
				$fields = array('data'=>'`data` varchar(30480) NULL AFTER `time`');
				$this->alterTable('#__session',$fields);
			}

			$q = 'SHOW INDEX FROM `#__virtuemart_categories` WHERE Key_name = "idx_slug"; ';
			$this->_db->setQuery($q);
			if($this->_db->loadResult()){
				$query = 'ALTER TABLE  `#__virtuemart_categories` DROP INDEX  `idx_slug`';
				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					VmError('Script.virtuemart update: Deleting of #__virtuemart_categories idx_ slug failed '.$this->_db->getErrorMsg());
				} else {
					vmdebug('Script.virtuemart update: I deleted the column '.$this->_db->getQuery());
				}
			}


			//Shipping methods
			$query = 'SHOW TABLES LIKE "%virtuemart_shippingcarriers%"';
			$this->_db->setQuery($query);
			if($this->_db->loadResult()){

				$query = 'SHOW TABLES LIKE "%virtuemart_shipmentmethods%"';
				$this->_db->setQuery($query);
				$res = $this->_db->loadResult();
				if(empty($res)){
					$query = 'ALTER TABLE `#__virtuemart_shippingcarriers` RENAME TO `#__virtuemart_shipmentmethods`';
					$this->_db->setQuery($query);
					$this->_db->query();

					$query = 'ALTER TABLE `#__virtuemart_shipmentmethods`  DROP INDEX `virtuemart_shippingcarrier_id` ';
					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}

			$fields = array('virtuemart_shippingcarrier_id'=>'`virtuemart_shipmentmethod_id` mediumint(1) UNSIGNED NOT NULL AUTO_INCREMENT',
																	'shipping_carrier_jplugin_id'=>'`shipment_jplugin_id` int(11) NOT NULL',
																	'shipping_carrier_name'=>"`shipment_name` char(200) NOT NULL DEFAULT ''",
																	'shipping_carrier_desc'=>"`shipment_desc` text NOT NULL COMMENT 'Description'",
																	'shipping_carrier_element'=>"`shipment_element` varchar(50) NOT NULL DEFAULT ''",
																	'shipping_carrier_params'=>' `shipment_params` text NOT NULL',
																	'shipping_carrier_value'=>"`shipment_value` decimal(10,2) NOT NULL DEFAULT '0.00'",
																	'shipping_carrier_package_fee'=>"`shipment_package_fee` decimal(10,2) NOT NULL DEFAULT '0.00'",
																	'shipping_carrier_vat_id'=>"`shipment_vat_id` int(11) NOT NULL DEFAULT '0'"
			);
			$this->alterTable('#__virtuemart_shipmentmethods',$fields);

			$query = 'SHOW TABLES LIKE "%virtuemart_shippingcarrier_shoppergroups%"';
			$this->_db->setQuery($query);
			if($this->_db->loadResult()){

				$query = 'SHOW TABLES LIKE "%virtuemart_shipmentmethod_shoppergroups%"';
				$this->_db->setQuery($query);
				$res = $this->_db->loadResult();
				if(empty($res)){
					$query = 'ALTER TABLE `#__virtuemart_shippingcarrier_shoppergroups` RENAME TO `#__virtuemart_shipmentmethod_shoppergroups`';
					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}

			$fields = array('virtuemart_shippingcarrier_id'=>"`virtuemart_shipmentmethod_id` SERIAL ");
			$this->alterTable('#__virtuemart_shipmentmethod_shoppergroups',$fields);

			//vmuser:
			$fields = array('virtuemart_shippingcarrier_id'=>'`virtuemart_shipmentmethod_id` int NOT NULL DEFAULT "0"');
			$this->alterTable('#__virtuemart_vmusers',$fields);

			// orders :
			$fields = array('payment_method_id'=>'`virtuemart_paymentmethod_id` INT(11 ) NOT NULL ',
					'ship_method_id'=>'`virtuemart_shipmentmethod_id` INT(11 ) NOT NULL ',
					'order_shipping'=>'`order_shipment` decimal(10,2) DEFAULT NULL ',
					'order_shipping_tax'=>'`order_shipment_tax` decimal(10,2) DEFAULT NULL ',
			);
			$this->alterTable('#__virtuemart_orders',$fields);


			$fields = array('config'=>'`vendor_params` VARCHAR( 255 )  NOT NULL DEFAULT ""');
			$this->alterTable('#__virtuemart_vendors',$fields);

			$this->updateWeightUnit();
			$this->updateDimensionUnit();

			$tablenames = array('shipment'=>'weight_countries','payment'=>'standard','payment'=>'paypal');
			$this->renamePsPluginTables($tablenames);

			//delete old config file
			// 			$this->renewConfigManually = !JFile::delete($this->path.DS.'virtuemart.cfg');
			// 			if(!$this->renewConfigManually){

			// 				$model = JModel::getInstance('config', 'VirtueMartModel');
			// 				if (!class_exists('VirtueMartModelConfig')
			// 				)require($this->path . DS . 'models' . DS . 'config.php');
			// 				$model -> deleteConfig();

			// probably should just go to updatesMigration rather than the install success screen
			// 			include($this->path.DS.'install'.DS.'install.virtuemart.html.php');
			//		$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			// 			$tablesToRename = array(  '#__virtuemart_shippingcarrier_shoppergroups' => '#__virtuemart_shipmentmethod_shoppergroups'
			// 									);

			if(!class_exists('GenericTableUpdater')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
			$updater = new GenericTableUpdater();

			$updater->portOldLanguageToNewTables((array)$lang);

			$updater->updateMyVmTables();

			$this->changeShoppergroupDataSetAnonShopperToOne();

			$this->migrateCustomPluginTableIntoCustoms();

			$this->updateJParamsToVmParams($tablenames);

			$this->updateAdminMenuEntry();

			if($loadVm) $this->displayFinished(true);

			return true;
		}

		/**
		 * @author Max Milbers
		 * @param unknown_type $tablename
		 * @param unknown_type $fields
		 * @param unknown_type $command
		 */
		private function alterTable($tablename,$fields,$command='CHANGE'){

			if(empty($this->_db)){
				$this->_db = JFactory::getDBO();
			}

			$query = 'SHOW COLUMNS FROM `'.$tablename.'` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadResultArray(0);

			foreach($fields as $fieldname => $alterCommand){
				if(in_array($fieldname,$columns)){
					$query = 'ALTER TABLE `'.$tablename.'` '.$command.' COLUMN `'.$fieldname.'` '.$alterCommand;

					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}

		}

		/**
		 *
		 * @author Max Milbers
		 * @param unknown_type $table
		 * @param unknown_type $field
		 * @param unknown_type $action
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function checkAddFieldToTable($table,$field,$fieldType){

			$query = 'SHOW COLUMNS FROM `'.$table.'` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadResultArray(0);

			if(!in_array($field,$columns)){


				$query = 'ALTER TABLE `'.$table.'` ADD '.$field.' '.$fieldType;
				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					$app = JFactory::getApplication();
					$app->enqueueMessage('Install checkAddFieldToTable '.$this->_db->getErrorMsg() );
					return false;
				} else {
					return true;
				}
			}
			return false;
		}

		/**
		 *
		 * @author Valérie Isaksen
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function updateWeightUnit(  ) {
			if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
			$weightUnitMigrateValues = Migrator::getWeightUnitMigrateValues();
			return $this->updateUnit(  'product_weight_uom', $weightUnitMigrateValues) ;
		}

		/**
		 *
		 * @author Valérie Isaksen
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function updateDimensionUnit(   ) {
			if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
			$dimensionUnitMigrateValues = Migrator::getDimensionUnitMigrateValues();
			return $this->updateUnit(  'product_lwh_uom', $dimensionUnitMigrateValues) ;
		}

		private function changeShoppergroupDataSetAnonShopperToOne(){

			$q = 'SELECT * FROM `#__virtuemart_shoppergroups` WHERE virtuemart_shoppergroup_id = "1" ';
			$this->_db->setQuery($q);
			$sgroup = $this->_db->loadAssoc();

			if($sgroup['default']!=2){
				if(!class_exists('TableShoppergroups')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'shoppergroups.php');
				$db = JFactory::getDBO();
				$table = new TableShoppergroups($db);
				$stdgroup = null;
				$stdgroup = array('virtuemart_shoppergroup_id' => 1,
									'virtuemart_vendor_id'	=> 1,
									'shopper_group_name'		=> '-anonymous-',
									'shopper_group_desc'		=> 'Shopper group for anonymous shoppers',
									'default'					=> 2,
									'published'					=> 1,
									'shared'						=> 1
				);
				$table -> bindChecknStore($stdgroup);

				$sgroup['virtuemart_shoppergroup_id'] = 0;
				$table = new TableShoppergroups($db);
				$table -> bindChecknStore($sgroup);
				vmdebug('changeShoppergroupDataSetAnonShopperToOne $table',$table);
			}
		}

		/**
		 *
		 * @author Valérie Isaksen
		 * @param unknown_type $field
		 * @param array $UnitMigrateValues
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function updateUnit(  $field, $UnitMigrateValues) {

			$ok=true;
			foreach ($UnitMigrateValues as $old => $new) {
				$query = 'UPDATE  `#__virtuemart_products` SET `'.$field.'` = "' . $new . '" WHERE  `'.$field.'` = "' . $old.'" ';
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {

					vmError('Install updateUnit '. $field.' '. $this->_db->getErrorMsg());
					$ok=false;
				}
			}
			if (!$ok) return false;
			$query = 'SHOW COLUMNS FROM `#__virtuemart_products` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadResultArray(0);
			if(!in_array($field,$columns)){
				$query = "ALTER TABLE  `#__virtuemart_products` CHANGE  `".$field."`  `".$field."` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  'Kg'";
				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					vmError('Install updateUnit '.$field.' '.$this->_db->getErrorMsg() );
					return false;
				} else {
					return true;
				}
			}
			return false;
		}

		private function updateAdminMenuEntry() {

			if(empty($this->db)){
				$this->db = JFactory::getDBO();
			}

			$query = 'UPDATE `#__virtuemart_adminmenuentries` SET `name`="COM_VIRTUEMART_SHIPMENTMETHOD_S", `view`="shipmentmethod" WHERE `id`="16" LIMIT 1';
			$this->db->setQuery($query);
			$this->db->query($query);

			$q = 'SELECT `id` FROM `#__virtuemart_adminmenuentries` WHERE `view` = "creditcard" ';
			$this->db->setQuery($q);
			$res = $this->db->loadResult();
			if($res){
				$query = 'DELETE FROM `#__virtuemart_adminmenuentries` WHERE `view`="creditcard" LIMIT 1;';
				$this->db->setQuery($query);
				$this->db->query($query);
			}


		}

		private function renamePsPluginTables($tablenames){

			foreach($tablenames as $key => $name){
				$query = 'SHOW TABLES LIKE "%_virtuemart_order_shipper_'.$name.'%"'; //=>jos_virtuemart_shipment_plg_weight_countries
				$this->_db->setQuery($query);
				if($this->_db->loadResult()){
					$query = 'ALTER TABLE `#__virtuemart_order_shipper_'.$name.'` RENAME TO `#__virtuemart_'.$key.'_plg_'.$name.'`';
					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}

		}


		private function migrateCustomPluginTableIntoCustoms(){

			$error = false;
			if(!class_exists('JParameter')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );

			$query = 'SHOW TABLES LIKE "%virtuemart_customplugins%"';
			$this->_db->setQuery($query);
			if($this->_db->loadResult()){
				$q = 'SELECT * FROM `#__virtuemart_customplugins` ';
				$this->_db->setQuery($q);

				$items = $this->_db->loadAssocList();
				if(!empty($items) and count($items)>0){
					$db = JFactory::getDBO();
					foreach($items as $item){

						//getTable
						if(!class_exists('TableCustoms'))require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'customs.php');
						$table = new TableCustoms($db);
						$params = new JParameter($item['custom_params']);
						// 				vmdebug('migrateCustomPluginTableIntoCustoms',$params);
						$str = '';
						foreach($params->getParams() as $pName => $pValue){
							vmdebug('migrateCustomPluginTableIntoCustoms '.$pName.' '.$pValue );
							$str .= $pName.'='.json_encode($pValue).'|';
						}
						$item['custom_params'] = $str;

						$table->bindChecknStoreNoLang($item,true);
						$errors = $table->getErrors();
						if(!empty($errors)){
							foreach($errors as $error){
								vmError($error);
							}
							$error = true;
						}
					}

					if(!$error){
						$q = 'DROP TABLE `#__virtuemart_customplugins` ';
						$this->_db->setQuery($q);
						$this->_db->query();
					}
				}
			}


		}

		/**
		 *
		 * Enter description here ...
		 * @param unknown_type $tablenames
		 */
		private function updateJParamsToVmParams(){

			if(!class_exists('JParameter')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );

			$tablenames = array('payment','shipment');

			foreach($tablenames as $name){
				$query = 'SHOW TABLES LIKE "%virtuemart_'.$name.'methods"';
				$this->_db->setQuery($query);
				if($this->_db->loadResult()){
					$q = 'SELECT `virtuemart_'.$name.'method_id`,`'.$name.'_params` FROM `#__virtuemart_'.$name.'methods` ';

					$this->_db->setQuery($q);
					$items = $this->_db->loadAssocList();

					foreach($items as $item){
						if(strpos("\n",$item[$name.'_params'])!==false and strpos("|",$item[$name.'_params'])===false){
							vmInfo('Old params format recognised in table '.$name);
							$params = new JParameter($item[$name.'_params']);
							$str = '';
							foreach($params->getParams() as $pName => $pValue){
								vmdebug('migrateCustomPluginTableIntoCustoms '.$pName.' '.$pValue );
								$str .= $pName.'='.json_encode($pValue).'|';
							}
							$q = 'UPDATE `#__virtuemart_'.$name.',methods` SET `'.$name.'_params`='.$str.' WHERE `virtuemart_'.$name.'method_id`="'.$item['virtuemart_'.$name.'method_id'].'" ';
							$this->_db->setQuery($q);
							if(!$this->_db->query()){
								vmError('updateJParamsToVmParams '.$this->_db->getErrorMsg());
							}

						}
					}
				}

			}

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

				$this->loadVm();
				// 				VmConfig::loadConfig(true);

				$db = JFactory::getDBO();
				$q = 'SHOW TABLES LIKE "%virtuemart_configs%"'; //=>jos_virtuemart_shipment_plg_weight_countries
				$db->setQuery($q);
				$res = $db->loadResult();
				if(!empty($res)){
					JRequest::setVar(JUtility::getToken(), '1', 'post');
					$config = JModel::getInstance('config', 'VirtueMartModel');
					$config->setDangerousToolsOff();
				}

			}

			//Test if vm1.1 is installed and rename file to avoid conflicts
			if(JFile::exists(JPATH_VM_ADMINISTRATOR.DS.'toolbar.php')){
				JFile::move('toolbar.php','toolbar.vm1.php',JPATH_VM_ADMINISTRATOR);
			}

			//Prevents overwriting existing file.
			// 			if(!JFile::exists(JPATH_VM_ADMINISTRATOR.DS.'virtuemart_defaults.cfg')){
			// 				JFile::copy('virtuemart_defaults.cfg-dist','virtuemart_defaults.cfg',JPATH_VM_ADMINISTRATOR);
			// 			}

			return true;
		}

		/**
		 * copy all $src to $dst folder and remove it
		 *
		 * @author Max Milbers
		 * @param String $src path
		 * @param String $dst path
		 * @param String $type modules, plugins, languageBE, languageFE
		 */
		private function recurse_copy($src,$dst ) {

			$dir = opendir($src);
			$this->createIndexFolder($dst);

			if(is_resource($dir)){
				while(false !== ( $file = readdir($dir)) ) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if ( is_dir($src .DS. $file) ) {
							$this->recurse_copy($src .DS. $file,$dst .DS. $file);
						}
						else {
							if(JFile::exists($dst .DS. $file)){
								if(!JFile::delete($dst .DS. $file)){
									$app = JFactory::getApplication();
									$app -> enqueueMessage('Couldnt delete '.$dst .DS. $file);
								}
							}
							if(!JFile::move($src .DS. $file,$dst .DS. $file)){
								$app = JFactory::getApplication();
								$app -> enqueueMessage('Couldnt move '.$src .DS. $file.' to '.$dst .DS. $file);
							}
						}
					}
				}
				closedir($dir);
				if (is_dir($src)) JFolder::delete($src);
			} else {
				$app = JFactory::getApplication();
				$app -> enqueueMessage('Couldnt read dir '.$dir.' source '.$src);
			}

		}

		public function displayFinished($update){

			$lang = JFactory::getLanguage();
			//Load first english files
			$lang->load('com_virtuemart.sys',JPATH_ADMINISTRATOR,'en_GB',true);
			$lang->load('com_virtuemart',JPATH_ADMINISTRATOR,'en_GB',true);

			//load specific language
			$lang->load('com_virtuemart.sys',JPATH_ADMINISTRATOR,null,true);
			$lang->load('com_virtuemart',JPATH_ADMINISTRATOR,null,true);
			?>
<link
	rel="stylesheet"
	href="components/com_virtuemart/assets/css/install.css"
	type="text/css" />
<link
	rel="stylesheet"
	href="components/com_virtuemart/assets/css/toolbar_images.css"
	type="text/css" />

<div align="center">
	<table
		width="100%"
		border="0">
		<tr>
			<td
				valign="top"
				align="center"><a
				href="http://virtuemart.net"
				target="_blank"> <img
					border="0"
					align="center"
					src="components/com_virtuemart/assets/images/vm_menulogo.png"
					alt="Cart" /> </a> <br /> <br />
				<h2>


				<?php echo JText::_('COM_VIRTUEMART_INSTALLATION_WELCOME') ?></h2>
			</td>
			<td>
				<h2>


				<?php
				if($update){
					echo JText::_('COM_VIRTUEMART_UPGRADE_SUCCESSFUL');
					/*					if($this->renewConfigManually){
						echo '<br />'.JText::_('When you got an error deleting the virtuemart.cfg file <br />
					Delete this file manually (administrator/components/com_virtuemart/virtuemart.cfg) and please use
					"renew config from file" in Tools => Updates/Migration');
					}*/
					echo '<br />'.JText::_('COM_VIRTUEMART_EXTENSION_UPGRADE_REMIND');

				} else {
					echo JText::_('COM_VIRTUEMART_INSTALLATION_SUCCESSFUL');
				}
				?>
				</h2> <br />

				<div id="cpanel">


				<?php
				if(!$update){
					?>
					<div class="icon">
						<a
							href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData&token='.JUtility::getToken()) ?>">
							<span class="vmicon48 vm_install_48"></span> <br />





						<?php echo JText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA'); ?>
							</a>
					</div>





		<?php } ?>

				<div class="icon">
				<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&task=disableDangerousTools&token='.JUtility::getToken() ) ?>">
					<span class="vmicon48 vm_frontpage_48"></span>
					<br /><?php echo JText::_('COM_VIRTUEMART_INSTALL_GO_SHOP') ?>
				</a>
				</div>




			</td>
		</tr>
	</table>
</div>





<?php
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
		$upgrade = $vmInstall->checkIfUpdate();

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
		// 		$vmInstall->preflight('uninstall');

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
