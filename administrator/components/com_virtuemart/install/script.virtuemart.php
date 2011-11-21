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
		 * method must be called after preflight
		 * Sets the paths and loads VMFramework config
		 */
		public function loadVm() {
			$this->path = JInstaller::getInstance()->getPath('extension_administrator');

			require_once($this->path.DS.'helpers'.DS.'config.php');
			JTable::addIncludePath($this->path.DS.'tables');
			JModel::addIncludePath($this->path.DS.'models');

		}

		public function checkIfUpdate(){

			$update = false;

			//Execute always the base installation file
			//			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			//			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');

			$db = JFactory::getDBO();
			$q = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
			$db->setQuery($q);
			$result = $db->loadResult();

			if (empty($result)) {
				$update = false;
			} else {
				$update = true;
			}

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
				$fields = array('data'=>'`data` LONGTEXT NULL AFTER `time`');
				$this->alterTable('#__session',$fields);
			}

			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			//			if(!class_exists('VirtueMartModelUpdatesMigration')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'updatesMigration.php');
			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_essential_data.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_required_data.sql');

			$id = $model->determineStoreOwner();
			$model->setStoreOwner($id);

			//copy sampel media
			$src = $this->path .DS. 'assets' .DS. 'images' .DS. 'vmsampleimages';
			// 			if(version_compare(JVERSION,'1.6.0','ge')) {

			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'category');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'category'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'manufacturer');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'manufacturer'.DS.'resized');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'product');
			$this->createIndexFolder(JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart'.DS.'product'.DS.'resized');

			// 			}

			$dst = JPATH_ROOT .DS. 'images' .DS. 'stories' .DS. 'virtuemart';

			$this->recurse_copy($src,$dst);

			if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
			$migrator = new Migrator();
			$migrator->createLanguageTables();

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


// 			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
// 			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');
// 			$this->displayFinished(true);
			//return false;

			if(version_compare(JVERSION,'1.6.0','ge')) {
				$fields = array('data'=>'`data` varchar(30480) NULL AFTER `time`');
				$this->alterTable('#__session',$fields);
			}


			//Shipping methods
			$query = 'SHOW TABLES LIKE "%virtuemart_shippingcarriers%"';
			$this->_db->setQuery($query);
			if($this->_db->loadResult()){
				$query = 'ALTER TABLE `#__virtuemart_shippingcarriers` RENAME TO `#__virtuemart_shipmentmethods`';
				$this->_db->setQuery($query);
				$this->_db->query();

				$fields = array('virtuemart_shippingcarrier_id'=>'`virtuemart_shipmentmethod_id` SERIAL',
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
			}

			$query = 'SHOW TABLES LIKE "%virtuemart_shippingcarrier_shoppergroups%"';
			$this->_db->setQuery($query);
			if($this->_db->loadResult()){

				$query = 'ALTER TABLE `#__virtuemart_shippingcarrier_shoppergroups` RENAME TO `#__virtuemart_shipmentmethod_shoppergroups`';
				$this->_db->setQuery($query);
				$this->_db->query();

				$fields = array('virtuemart_shippingcarrier_id'=>"`virtuemart_shipmentmethod_id` SERIAL ");
				$this->alterTable('#__virtuemart_shipmentmethod_shoppergroups',$fields);
			}

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


			$this->alterVendorsTable();

			$this->updateWeightUnit();
			$this->updateDimensionUnit();

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

			$updater = new genericTableUpdater;
			$updater->updateMyVmTables();

			$config = &JFactory::getConfig();
			$lang = $config->getValue('language');
			if(!class_exists('Migrator')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'migrator.php');
			$migrator = new Migrator();
			$migrator->portOldLanguageToNewTables((array)$lang);

			$this->changeShoppergroupDataSetAnonShopperToOne();

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

		private function alterVendorsTable(){

			if(empty($this->_db)){
				$this->_db = JFactory::getDBO();
			}
			$query = 'SHOW COLUMNS FROM `#__virtuemart_vendors` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadResultArray(0);
			if(in_array('config',$columns)){
				$query = 'ALTER TABLE `#__virtuemart_vendors` CHANGE COLUMN `config` `vendor_params` VARCHAR( 255 )  NOT NULL DEFAULT "" ;';
				$this->_db->setQuery($query);
				return $this->_db->query();
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

				$table = new TableShoppergroups();
				$stdgroup == null;
				$stdgroup = array('virtuemart_shoppergroup_id' => 1,
									'virtuemart_vendor_id'	=> 1,
									'shopper_group_name'		=> '-anonymous-',
									'shopper_group_desc'		=> 'shopper group for anoymous shoppers',
									'default'					=> 2,
									'published'					=> 1,
									'shared'						=> 1
				);
				$table -> bindChecknStore($stdgroup);

				$sgroup['virtuemart_shoppergroup_id'] = 0;
				$table = new TableShoppergroups();
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
				JRequest::setVar(JUtility::getToken(), '1', 'post');
				$config = JModel::getInstance('config', 'VirtueMartModel');
				$config->setDangerousToolsOff();
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
							if(!JFile::exists($dst .DS. $file) && !JFile::move($src .DS. $file,$dst .DS. $file)){
								vmError('Couldnt move '.$src .DS. $file.' to '.$dst .DS. $file);
							}
						}
					}
				}
				closedir($dir);
				if (is_dir($src)) JFolder::delete($src);
			} else {
				vmError('Couldnt read dir '.$dir.' source '.$src);
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


/**
 * Class to update the tables according to the install.sql db file
 *
 * @author Milbo
 *
 */
class genericTableUpdater{

	public function __construct(){

		JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'tables');

		$this->_app = JFactory::getApplication();
		$this->_db = JFactory::getDBO();
// 		$this->_oldToNew = new stdClass();
		$this->starttime = microtime(true);

		$max_execution_time = ini_get('max_execution_time');
		$jrmax_execution_time= JRequest::getInt('max_execution_time',120);

		if(!empty($jrmax_execution_time)){
			// 			vmdebug('$jrmax_execution_time',$jrmax_execution_time);
			if($max_execution_time!==$jrmax_execution_time) @ini_set( 'max_execution_time', $jrmax_execution_time );
		}

		$this->maxScriptTime = ini_get('max_execution_time')*0.90-1;	//Lets use 5% of the execution time as reserve to store the progress


		$memory_limit = ini_get('memory_limit');
		if($memory_limit<128)  @ini_set( 'memory_limit', '128M' );

		$this->maxMemoryLimit = $this->return_bytes(ini_get('memory_limit')) * 0.85;

		$config = JFactory::getConfig();
		$this->_prefix = $config->getValue('config.dbprefix');

	}

	public function updateMyVmTables(){

		$file = JPATH_VM_ADMINISTRATOR.DS.'install'.DS.'install.sql';
		$data = fopen($file, 'r');

		$tables = array();
		$tableDefStarted = false;
		while ($line = fgets ($data)) {
			$line = trim($line);
			if (empty($line)) continue; // Empty line

			if (strpos($line, '#') === 0) continue; // Commentline
			if (strpos($line, '--') === 0) continue; // Commentline

			if(strpos($line,'CREATE TABLE IF NOT EXISTS')!==false){
				$tableDefStarted = true;
				$fieldLines = array();
				$tableKeys = array();
				$start = strpos($line,'`');

				$tablename = trim(substr($line,$start+1,-3));
// 				vmdebug('my $tablename ',$start,$end,$line);
			} else if($tableDefStarted && strpos($line,'KEY')!==false){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				if(strrpos($line,',')==strlen($line)-1){
					$line = substr($line,0,-1);
				}
				$tableKeys[$keyName] = $line;

			} else if(strpos($line,'ENGINE')!==false){
				$tableDefStarted = false;

				$start = strpos($line,"COMMENT='");
				$temp = substr($line,$start+9);
				$end = strpos($temp,"'");
				$comment = substr($temp,0,$end);

				$tables[$tablename] = array($fieldLines, $tableKeys,$comment);
			} else if($tableDefStarted){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				$line = trim(substr($line,$end+2));
				if(strrpos($line,',')==strlen($line)-1){
					$line = substr($line,0,-1);
				}

				$fieldLines[$keyName] = $line;
			}
		}

// 	vmdebug('Parsed tables',$tables); //return;
		$this->_db->setQuery('SHOW TABLES LIKE "%_virtuemart_%"');
		if (!$existingtables = $this->_db->loadResultArray()) {
			$this->setError = $this->_db->getErrorMsg();
			return false;
		}


		$demandedTables = array();
		//TODO ignore admin menu table
		foreach ($tables as $tablename => $table){
			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$demandedTables[] = $tablename;
			if(in_array($tablename,$existingtables)){
				$this -> compareUpdateTable($tablename,$table);
// 				unset($todelete[$tablename]);
			} else {
				$this->createTable($tablename,$table);
			}
		}

		$tablesWithLang = array('categories','manufacturercategories','manufacturers','paymentmethods','shipmentmethods','products','vendors');
		$alangs = VmConfig::get('active_languages');
		foreach($alangs as $lang){
			foreach($tablesWithLang as $tablewithlang){
				$demandedTables[] = $this->_prefix.'virtuemart_'.$tablewithlang.'_'.$lang;
			}
		}
		$demandedTables[] = $this->_prefix.'virtuemart_configs';

		$todelete = array();
		foreach ($existingtables as $tablename){
			if(!in_array($tablename,$demandedTables) and strpos($tablename,'_plg_')){
				$todelete[] = $tablename;
			}
		}
		$this->dropTables($todelete);

	}

	public function compareUpdateTable($tablename,$table){

		$this->alterColumns($tablename,$table[0]);

		reset($table[0]);
		$first_key = key($table[0]);

// 		$this->alterKey($tablename,$table[1],$first_key);

	}

	public function createTable($tablename,$table){

		$q = 'CREATE TABLE IF NOT EXISTS `'.$tablename.'` (
				';
		foreach($table[0] as $fieldname => $alterCommand){
			$q .= '`'.$fieldname.'` '.$alterCommand.'
			';
		}

		foreach($table[1] as $name => $value){
			$q .= '`'.$name.'` '.$value.',
						';
		}
		$q = substr($q,0,-1);

		$q = ") ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='".$table[3]."' AUTO_INCREMENT=1 ;";

		$this->_db->setQuery($query);
		if(!$this->_db->query()){
			$this->_app->enqueueMessage('createTable ERROR :'.$this->_db->getErrorMsg() );
		}
		$this->_app->enqueueMessage($q);
	}

	public function dropTables($todelete){
		if(empty($todelete)) return;
		$q = 'DROP ';// .implode(',',$todelete);
		foreach($todelete as $tablename){
			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$q .= $tablename.', ';
		}
		$q = substr($q,0,-1);

// 		$this->_db->setQuery($q);
// 		if(!$this->_db->query()){
// 			$this->_app->enqueueMessage('dropTables ERROR :'.$this->_db->getErrorMsg() );
// 		}
		$this->_app->enqueueMessage($q);
	}


	private function alterKey($tablename,$keys,$firstField){

		// 		vmdebug('$first_key',$first_key,$table[1]);
		$demandFieldNames = array();
		foreach($keys as $i=>$line){
			$demandedFieldNames[] = $i;
		}

		$query = "SHOW INDEXES  FROM `".$tablename."` ";	//SHOW {INDEX | INDEXES | KEYS}
		$this->_db->setQuery($query);
// 		$keys = $this->_db->loadResultArray(0);
		if(!$eKeys = $this->_db->loadObjectList()){
			$this->_app->enqueueMessage('alterKey show index:'.$this->_db->getErrorMsg() );
		}
		$after ='';

		$knownFieldNames = array();
		foreach($eKeys as $eKey){
			if(strpos($eKey->Key_name,'PRIMARY')!==false){
				continue; //We ignore Primaries
			}
			$knownFieldNames[] = $eKey->Key_name;
		}
// 		vmdebug('$knownKeyNames',$knownFieldNames);
		$i = 0;
		$columnsToDelete = $knownFieldNames;
		foreach($keys as $name =>$value){

			if($i===0){
				$dropit = '';
				if(in_array('PRIMARY KEY', $knownFieldNames)){
					$dropit = "DROP PRIMARY KEY , ";
				}
				$query = "ALTER TABLE `".$tablename."` ".$dropit." ADD PRIMARY KEY (`".$firstField."`);" ;

				$action = 'ALTER';
				$i++;
			}

			if(strpos($value,'PRIMARY')!==false){
				continue; //We ignore Primaries
			}

// 			if(!in_array($name,$demandFieldNames)){
// 				$query =  "ALTER TABLE `".$tablename."` DROP INDEX ".$name;
// 				$action = 'DROP';
// 			} else
			vmdebug('$name '.$name.' value '.$value);
			if(in_array($name, $knownFieldNames)){
				if(strpos($value,'KEY')) $type = 'KEY'; else $type = 'INDEX';
				$query = "ALTER TABLE `".$tablename."` DROP  ".$type." `".$name."` , ADD ".$value ;
				$action = 'ALTER';
			} else {
				$query = "ALTER TABLE `".$tablename."` ADD ".$value ;
				$action = 'ADD';
			}

			$this->_db->setQuery($query);
			if(!$this->_db->query()){
				$this->_app = JFactory::getApplication();
				$this->_app->enqueueMessage('alterKey '.$action.' INDEX '.$key.': '.$this->_db->getErrorMsg() );
			}
		}

		$eKeys = array();
		$query = "SHOW INDEXES  FROM `".$tablename."` ";	//SHOW {INDEX | INDEXES | KEYS}
		$this->_db->setQuery($query);
		if(!$eKeys = $this->_db->loadObjectList()){
			$this->_app->enqueueMessage('alterKey show index:'.$this->_db->getErrorMsg() );
		}

		$knownKeyNames = array();
		foreach($eKeys as $eKey){
			if(strpos($eKey->Key_name,'PRIMARY')!==false){
				continue; //We ignore Primaries
			}
			$knownKeyNames[] = $eKey->Key_name;
		}

		vmdebug('$known key Names',$knownKeyNames);
		foreach($knownFieldNames as $fieldname){

			if(!in_array($fieldname, $demandedFieldNames)){
				$query = 'ALTER TABLE `'.$tablename.'` DROP `'.$fieldname.'` ';
				$action = 'DROP';
				$dropped++;

// 				$this->_db->setQuery($query);
// 				if(!$this->_db->query()){
					$this->_app->enqueueMessage('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
// 				}
			}

		}

	}

	/**
	* @author Max Milbers
	* @param unknown_type $tablename
	* @param unknown_type $fields
	* @param unknown_type $command
	*/
	private function alterColumns($tablename,$fields){

		$demandFieldNames = array();
		foreach($fields as $i=>$line){
			$demandFieldNames[] = $i;
		}

		$query = 'SHOW FULL COLUMNS  FROM `'.$tablename.'` ';
		$this->_db->setQuery($query);
		$fullColumns = $this->_db->loadObjectList();
		$columns = $this->_db->loadResultArray(0);

// 		$columnsToDelete = $columns;
		$after ='';
		$dropped = 0;
		$altered = 0;
		$added = 0;
		$this->_app = JFactory::getApplication();

		foreach($fields as $fieldname => $alterCommand){
// 			vmdebug('$fieldname',$fieldname,$alterCommand);
// 			if(!in_array($fieldname,$demandFieldNames)){

// 				vmdebug('DROP command '.$fieldname);
// 			}
			if(empty($alterCommand)){
				vmdebug('empty alter command '.$fieldname);
				continue;
			}
			else if(in_array($fieldname,$columns)){
				$query='';
				$key=array_search($fieldname, $columns);
				$oldColumn = $this->reCreateColumnByTableAttributes($fullColumns[$key]);

				$compare = strcasecmp( $oldColumn, $alterCommand);

				if (!empty($compare)) {
				    $query = 'ALTER TABLE `'.$tablename.'` CHANGE COLUMN `'.$fieldname.'` `'.$fieldname.'` '.$alterCommand;
				    $action = 'CHANGE';
				    $altered++;
				    vmdebug('$fullColumns',$fullColumns[$key]);
				    vmdebug('Alter field ',$oldColumn,$alterCommand,$compare);
				}
			}
			else {
				$query = 'ALTER TABLE `'.$tablename.'` ADD '.$fieldname.' '.$alterCommand.' '.$after;
				$action = 'ADD';
				$added++;
			}
			if (!empty($query)) {
			    $this->_db->setQuery($query);
			    if(!$this->_db->query()){
				    $this->_app->enqueueMessage('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
			    }
			    $after = 'AFTER '.$fieldname;
			}
		}

		foreach($columns as $fieldname){

			if(!in_array($fieldname, $demandFieldNames)){
				$query = 'ALTER TABLE `'.$tablename.'` DROP COLUMN `'.$fieldname.'` ';
				$action = 'DROP';
				$dropped++;

				$this->_db->setQuery($query);
				if(!$this->_db->query()){
					$this->_app->enqueueMessage('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
				}
			}
		}
		$this->_app->enqueueMessage('Tablename '.$tablename.' dropped: '.$dropped.' altered: '.$altered.' added: '.$added);

		return true;

	}

	private function reCreateColumnByTableAttributes($fullColumn){

		$oldColumn = $fullColumn->Type;

		if($this->notnull($fullColumn->Null)){

// 			if(empty($fullColumn->Default)){
// 				$default = $fullColumn->Extra;
// 			} else {
// 				$default = $fullColumn->Default;
// 			}
			$oldColumn .= $this->notnull($fullColumn->Null).$this->getdefault($fullColumn->Default);
		}
		$oldColumn .= $this->primarykey($fullColumn->Key).$this->formatComment($fullColumn->Comment);

		return $oldColumn;
	}
// 	$oldColumn=$fullColumns[$key]->Type.  .$this->primarykey($fullColumns[$key]->Key).$this->formatComment($fullColumns[$key]->Comment);

	private function formatComment($comment){
		if(!empty($comment)){
			return ' COMMENT \''.$comment.'\'';
		} else {
			return '';
		}

	}
	private function notnull($string){
	    if ($string=='NO') {
		return  ' NOT NULL';
	    } else {
		return '';
	    }
	}
	private function primarykey($string){
	    if ($string=='PRI') {
		return  ' AUTO_INCREMENT';
	    } else {
		return '';
	    }
	}
	private function getdefault($string){
	    if (isset($string)) {
		return  " DEFAULT '".$string."'";
	    } else {
		return '';
	    }
	}
	private function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

}
// pure php no tag
