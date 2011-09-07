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

		$update = false;

		$db = JFactory::getDBO();

		$q = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
		$db->setQuery($q);
		$result = $db->loadResult();

		if (empty($result)) {
		$update = false;
		} else {
		$update = true;
		}


		// return true so com_install wrapper will know what to do in j1.5
		if ($parent == null) {
		return $update;
		}

		//$parent->getParent()->setUpgrade($update);

		}*/


		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install ($parent=null) {

			$this->loadVm();

			if($this->checkIfUpdate()){
				return $this->update();
			}

			$this->alterSessionTable();
			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			//			if(!class_exists('VirtueMartModelUpdatesMigration')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'updatesMigration.php');
			$model = JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_essential_data.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_required_data.sql');

			$model->setStoreOwner();

			//copy sampel media
			$src = $this->path .DS. 'assets' .DS. 'images' .DS. 'vmsampelimages';
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

			if(JFolder::create($path)) {
				JFile::copy(JPATH_ROOT.DS.'components'.DS.'index.html', $path .DS. 'index.html');
			}

		}

		/**
		 * Update script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function update ($parent=null) {

			$this->loadVm();

			if(!$this->checkIfUpdate()){
				return $this->install();
			}

			$this->db = JFactory::getDBO();
			/*			$query = 'SHOW COLUMNS FROM `#__virtuemart_products` ';
			 $db->setQuery($query);
			$columns = $db->loadResultArray(0);

			if(!in_array('product_ordered',$columns)){

			$query = 'ALTER TABLE `#__virtuemart_products` ADD product_ordered int(11)';
			$db->setQuery($query);
			$db->query();
			}*/

			$this->checkAddFieldToTable('#__virtuemart_products','product_ordered','int(11)');

			$this->alterSessionTable();
			$this->alterOrderItemsTable();

			$this->updateWeightUnit();
			$this->updateDimensionUnit();

			$this->displayFinished(true);
			// probably should just go to updatesMigration rather than the install success screen
			// 			include($this->path.DS.'install'.DS.'install.virtuemart.html.php');
			//		$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			return true;
		}

		private function alterSessionTable(){

			if(version_compare(JVERSION,'1.6.0','ge')) {
				if(empty($this->db)){
					$this->db = JFactory::getDBO();
				}
				$query = 'SHOW COLUMNS FROM `#__session` ';
				$this->db->setQuery($query);
				$columns = $this->db->loadResultArray(0);
				if(in_array('data',$columns)){
					$query = 'ALTER TABLE `#__session` CHANGE COLUMN `data` `data` LONGTEXT NULL AFTER `time`;';
					$this->db->setQuery($query);
					$this->db->query();
				}
			}
		}
		/**
		 *
		 * @author Valérie Isaksen
		 * @param unknown_type $table
		 * @param unknown_type $field
		 * @param unknown_type $action
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function alterOrderItemsTable(){

			if(empty($this->db)){
				$this->db = JFactory::getDBO();
			}
			$query = 'SHOW COLUMNS FROM `#__virtuemart_order_items` ';
			$this->db->setQuery($query);
			$columns = $this->db->loadResultArray(0);
			if(in_array('order_item_name',$columns)){
				$query = 'ALTER TABLE `#__virtuemart_order_items` CHANGE COLUMN `order_item_name` `order_item_name` VARCHAR( 255 )  NOT NULL DEFAULT "" ;';
				$this->db->setQuery($query);
				return $this->db->query();
			}
			return false;
		}

		/**
		 *
		 * @author Max Milbers
		 */
		private function alterOrderHistoriesTable(){

			if(empty($this->db)){
				$this->db = JFactory::getDBO();
			}
			$query = 'SHOW COLUMNS FROM `#__virtuemart_order_histories` ';
			$this->db->setQuery($query);
			$columns = $this->db->loadResultArray(0);
			if(in_array('date_added',$columns)){
				$query = 'ALTER TABLE `#__virtuemart_order_histories` DROP COLUMN `date_added`;';
				$this->db->setQuery($query);
				return $this->db->query();
			}
			return false;

			;
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
			$this->db->setQuery($query);
			$columns = $this->db->loadResultArray(0);

			if(!in_array($field,$columns)){


				$query = 'ALTER TABLE `'.$table.'` ADD '.$field.' '.$fieldType;
				$this->db->setQuery($query);
				if(!$this->db->query()){
					$app = JFactory::getApplication();
					$app->enqueueMessage('Install checkAddFieldToTable '.$this->db->getErrorMsg() );
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
				$this->db->setQuery($query);
				if (!$this->db->query()) {

					vmError('Install updateUnit '. $field.' '. $this->db->getErrorMsg());
					$ok=false;
				}
			}
			if (!$ok) return false;
			$query = 'SHOW COLUMNS FROM `#__virtuemart_products` ';
			$this->db->setQuery($query);
			$columns = $this->db->loadResultArray(0);
			if(!in_array($field,$columns)){
				$query = "ALTER TABLE  `#__virtuemart_products` CHANGE  `".$field."`  `".$field."` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  'Kg'";
				$this->db->setQuery($query);
				if(!$this->db->query()){
					vmError('Install updateUnit '.$field.' '.$this->db->getErrorMsg() );
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
				VmConfig::loadConfig(true);
				$config = JModel::getInstance('config', 'VirtueMartModel');
				$config->setDangerousToolsOff();
			}

			//Test if vm1.1 is installed and rename file to avoid conflicts
			if(JFile::exists(JPATH_VM_ADMINISTRATOR.DS.'toolbar.php')){
				JFile::move('toolbar.php','toolbar.vm1.php',JPATH_VM_ADMINISTRATOR);
			}
			if(JFile::exists(JPATH_VM_ADMINISTRATOR.DS.'toolbar.php')){
				JFile::move('toolbar.virtuemart.php','toolbar.virtuemart.vm1.php',JPATH_VM_ADMINISTRATOR);
			}

			return true;
		}

		/**
		 * copy all $src to $dst folder and remove it
		 * Enter description here ...
		 * @param String $src path
		 * @param String $dst path
		 * @param String $type modules, plugins, languageBE, languageFE
		 */
		private function recurse_copy($src,$dst ) {

			$dir = opendir($src);
			@mkdir($dst);

			if(is_resource($dir)){
				while(false !== ( $file = readdir($dir)) ) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if ( is_dir($src .DS. $file) ) {
							$this->recurse_copy($src .DS. $file,$dst .DS. $file);
						}
						else {
							JFile::move($src .DS. $file,$dst .DS. $file);
						}
					}
				}
				closedir($dir);
				//if (is_dir($src)) $this->RemoveDir($src, true);
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
					echo '<br />'.JText::_('Please use "renew config from file" in Tools => Updates/Migration');
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
							<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart') ?>">
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
