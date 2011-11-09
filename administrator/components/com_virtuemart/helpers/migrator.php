<?php
/**
 *
 * @version $Id:connection.php 431 2006-10-17 21:55:46 +0200 (Di, 17 Okt 2006) soeren_nb $
 * @package VirtueMart
 * @subpackage classes
 * @copyright Copyright (C) 2004-2007 soeren, 2009-2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

if(!class_exists('JModel'))
require(JPATH_ROOT . DS . 'libraries' . DS . 'joomla' . DS . 'application' . DS . 'component' . DS . 'model.php');
if(!class_exists('VmModel'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');


class Migrator extends VmModel{

	//Object to hold old against new ids. We wanna port as when it setup fresh, so no importing of old ids!

	private $_test = false;

	public function __construct(){

		JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'tables');

		$this->_app = JFactory::getApplication();
		$this->_db = JFactory::getDBO();
		$this->_oldToNew = new stdClass();
		$this->starttime = microtime(true);

		$max_execution_time = ini_get('max_execution_time');
		$jrmax_execution_time= JRequest::getInt('max_execution_time');

		if(!empty($jrmax_execution_time)){
// 			vmdebug('$jrmax_execution_time',$jrmax_execution_time);
			if($max_execution_time!==$jrmax_execution_time) @ini_set( 'max_execution_time', $jrmax_execution_time );
		}

		$this->maxScriptTime = ini_get('max_execution_time')*0.90-1;	//Lets use 5% of the execution time as reserve to store the progress


		$memory_limit = ini_get('memory_limit');
		if($memory_limit<128)  @ini_set( 'memory_limit', '128M' );

		$this->maxMemoryLimit = $this->return_bytes(ini_get('memory_limit')) * 0.75;		//Lets use 25 % as reserve
		//$this->maxMemoryLimit = $this -> return_bytes('20M');

		// 		ini_set('memory_limit','35M');
		$q = 'SELECT `id` FROM `#__virtuemart_migration_oldtonew_ids` ';
		$this->_db->setQuery($q);
		$res = $this->_db->loadResult();
		if(empty($res)){
			$q = 'INSERT INTO `#__virtuemart_migration_oldtonew_ids` (`id`) VALUES ("1")';
			$this->_db->setQuery($q);
			$this->_db->query();
			$this->_app->enqueueMessage('Start with a new migration process and setup log maxScriptTime '.$this->maxScriptTime.' maxMemoryLimit '.$this->maxMemoryLimit/(1024*1024));
		} else {
			$this->_app->enqueueMessage('Found prior migration process, resume migration maxScriptTime '.$this->maxScriptTime.' maxMemoryLimit '.$this->maxMemoryLimit/(1024*1024));
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

	function getMigrationProgress($group){

		$q = 'SELECT `'.$group.'` FROM `#__virtuemart_migration_oldtonew_ids` WHERE `id` = "1" ';

		$this->_db->setQuery($q);
		$result = $this->_db->loadResult();
		if(empty($result)){
			$result = array();
		} else {
			$result = unserialize($result);
			if(!$result){
				$result = array();
			}
		}

		return $result;

	}

	function storeMigrationProgress($group,$array){

		//$q = 'UPDATE `#__virtuemart_migration_oldtonew_ids` SET `'.$group.'`="'.implode(',',$array).'" WHERE `id` = "1"';
		$q = 'UPDATE `#__virtuemart_migration_oldtonew_ids` SET `'.$group.'`="'.serialize($array).'" WHERE `id` = "1"';

		$this->_db->setQuery($q);
		if(!$this->_db->query()){
			$this->_app->enqueueMessage('storeMigrationProgress failed to update query'.$this->_db->getQuery());
			$this->_app->enqueueMessage('and ErrrorMsg '.$this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	function migrateGeneral(){

		$result = $this->portMedia();
		$result = $this->portShoppergroups();
		$result = $this->portCategories();
		$result = $this->portManufacturerCategories();
		$result = $this->portManufacturers();
		// 		$result = $this->portOrderStatus();

		$time = microtime(true) - $this->starttime;
		vmInfo('Worked on general migration for '.$time.' seconds');
		vmRamPeak('Migrate general vm1 info ended ');
		return $result;
	}

	function migrateUsers(){

		$result = $this->portShoppergroups();
		$result = $this->portUsers();

		$time = microtime(true) - $this->starttime;
		vmInfo('Worked on user migration for '.$time.' seconds');
		vmRamPeak('Migrate shoppers ended ');
		return $result;
	}

	function migrateProducts(){

		$result = $this->portMedia();

		$result = $this->portCategories();
		$result = $this->portManufacturerCategories();
		$result = $this->portManufacturers();
		$result = $this->portProducts();

		$time = microtime(true) - $this->starttime;
		$this->_app->enqueueMessage('Worked on general migration for '.$time.' seconds');

		return $result;
	}

	function migrateOrders(){

		$result = $this->portMedia();
		$result = $this->portCategories();
		$result = $this->portManufacturerCategories();
		$result = $this->portManufacturers();
		$result = $this->portProducts();

		// 		$result = $this->portOrderStatus();
		$result = $this->portOrders();
		$time = microtime(true) - $this->starttime;
		vmInfo('Worked on migration for '.$time.' seconds');

		return $result;
	}

	function migrateAllInOne(){

		$result = $this->portMedia();

		$result = $this->portShoppergroups();
		$result = $this->portUsers();

		$result = $this->portCategories();
		$result = $this->portManufacturerCategories();
		$result = $this->portManufacturers();
		$result = $this->portProducts();

		//$result = $this->portOrderStatus();
		$result = $this->portOrders();
		$time = microtime(true) - $this->starttime;
		$this->_app->enqueueMessage('Worked on migration for '.$time.' seconds');

		vmRamPeak('Migrate all ended ');
		return $result;
	}

	public function portMedia(){

		$ok = true;
		
		//Prevents search field from interfering with syncronization
		JRequest::setVar('searchMedia', '');

		//$imageExtensions = array('jpg','jpeg','gif','png');

		if(!class_exists('VirtueMartModelMedia'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'media.php');
		$this->mediaModel = new VirtueMartModelMedia();
		//First lets read which files are already stored
		$this->storedMedias = $this->mediaModel->getFiles(false, true);

		//check for entries without file
		foreach($this->storedMedias as $media){

			$media_path = JPATH_ROOT.DS.str_replace('/',DS,$media->file_url);
			if(!file_exists($media_path)){
				vmInfo('File for '.$media->file_url.' is missing');

				//The idea is here to test if the media with missing data is used somewhere and to display it
				//When it not used, the entry should be deleted then.
/*				$q = 'SELECT * FROM `#__virtuemart_category_medias` as cm,
											`#__virtuemart_product_medias` as pm,
											`#__virtuemart_manufacturer_medias` as mm,
											`#__virtuemart_vendor_medias` as vm
						WHERE cm.`virtuemart_media_id` = "'.$media->virtuemart_media_id.'"
						OR pm.`virtuemart_media_id` = "'.$media->virtuemart_media_id.'"
						OR mm.`virtuemart_media_id` = "'.$media->virtuemart_media_id.'"
						OR vm.`virtuemart_media_id` = "'.$media->virtuemart_media_id.'" ';

				$this->_db->setQuery($q);
				$res = $this->_db->loadResultArray();
				vmdebug('so',$res);
				if(count($res)>0){
					vmInfo('File for '.$media->file_url.' is missing, but used ');
				}
				*/
			}
		}


		$countTotal = 0;
		//We do it per type
		$url = VmConfig::get('media_product_path');
		$type = 'product';
		$count = $this->_portMediaByType($url, $type);
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT', $count, $type, $url));

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return $msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_NOT_FINISH', $countTotal);
		}

		$url = VmConfig::get('media_category_path');
		$type = 'category';
		$count = $this->_portMediaByType($url, $type);
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT', $count, $type, $url));

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return $msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_NOT_FINISH', $countTotal);
		}

		$url = VmConfig::get('media_manufacturer_path');
		$type = 'manufacturer';
		$count = $this->_portMediaByType($url, $type);
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT', $count, $type, $url));

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return $msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_NOT_FINISH', $countTotal);
		}

		$url = VmConfig::get('media_vendor_path');
		$type = 'vendor';
		$count = $this->_portMediaByType($url, $type);
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT', $count, $type, $url));

		return $msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_FINISH', $countTotal);
	}

	private function _portMediaByType($url, $type){

		$knownNames = array();
		//create array of filenames for easier handling
		foreach($this->storedMedias as $media){
			if($media->file_type == $type){

				//Somehow we must use here the right char encoding, so that it works below
				// in line 320
				$knownNames[] = $media->file_url;
			}
		}
// 		vmdebug('my known paths of type'.$type,$knownNames);
		$filesInDir = array();
		$foldersInDir = array();

		$path = str_replace('/', DS, $url);
		//$dir = JPATH_ROOT.DS.$path;
		$foldersInDir = array(JPATH_ROOT . DS . $path);
		while(!empty($foldersInDir)){
			foreach($foldersInDir as $dir){
				$subfoldersInDir = null;
				$subfoldersInDir = array();
				$relUrl = str_replace(DS, '/', substr($dir, strlen(JPATH_ROOT . DS)));
				if($handle = opendir($dir)){
					while(false !== ($file = readdir($handle))){

						//$file != "." && $file != ".." replaced by strpos
						if(!empty($file) && strpos($file,'.')!==0  && $file != 'index.html'){

							$filetype = filetype($dir . DS . $file);
							$relUrlName = '';
							$relUrlName = $relUrl.$file;
// 						vmdebug('my relative url ',$relUrlName);

							//We port all type of media, regardless the extension
							if($filetype == 'file'){
								if(!in_array($relUrlName, $knownNames)){
									$filesInDir[] = array('filename' => $file, 'url' => $relUrl);
								}
							}else {
								if($filetype == 'dir' && $file != 'resized'){
									$subfoldersInDir[] = $dir.$file.DS;
// 									vmdebug('my sub folder ',$dir.$file);
								}
							}
						}
					}
				}
			}
			$foldersInDir = $subfoldersInDir;
			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){

				break;
			}
		}
		//echo '<pre>'.print_r($filesInDir,1).'</pre>';
//		die;
		$i = 0;
		foreach($filesInDir as $file){

			$data = null;
			$data = array('file_title' => $file['filename'],
		    'virtuemart_vendor_id' => 1,
		    'file_description' => $file['filename'],
		    'file_meta' => $file['filename'],
		    'file_url' => $file['url'] . $file['filename'],
	    	 'media_published' => 1
			);
			if($type == 'product')
			$data['file_is_product_image'] = 1;
			$this->mediaModel->setId(0);
			$success = $this->mediaModel->store($data, $type);
			$errors = $this->mediaModel->getErrors();
			foreach($errors as $error){
				$this->_app->enqueueMessage('Migrator ' . $error);
			}
			$this->mediaModel->resetErrors();
			if($success) $i++;
			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				vmError('Attention script time too short, no time left to store the media, please rise script execution time');
				break;
			}
		}

		return $i;
	}

	private function portShoppergroups(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}
		$ok = true;

		$q = 'SELECT * FROM #__vm_shopper_group';
		$this->_db->setQuery($q);
		$oldShopperGroups = $this->_db->loadAssocList();
		if(empty($oldShopperGroups)) $oldShopperGroups = array();

		$oldtoNewShoppergroups = array();
		$alreadyKnownIds = $this->getMigrationProgress('shoppergroups');

		$starttime = microtime(true);
		$i = 0;
		foreach($oldShopperGroups as $oldgroup){

			if(!array_key_exists($oldgroup['shopper_group_id'],$alreadyKnownIds)){
				$sGroups = null;
				$sGroups = array();
				//$category['virtuemart_category_id'] = $oldcategory['category_id'];
				$sGroups['virtuemart_vendor_id'] = $oldgroup['vendor_id'];
				$sGroups['shopper_group_name'] = $oldgroup['shopper_group_name'];

				$sGroups['shopper_group_desc'] = $oldgroup['shopper_group_desc'];
				$sGroups['published'] = 1;
				$sGroups['default'] = $oldgroup['default'];

				$table = $this->getTable('shoppergroups');

				$sGroups = $table->bindChecknStore($sGroups);
				$errors = $table->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->setError($error);
					}
					break;
				}

				$oldtoNewShoppergroups[$oldgroup['shopper_group_id']] = $sGroups['virtuemart_shoppergroup_id'];
				unset($sGroups['virtuemart_shoppergroup_id']);
				$i++;
			} else {
				$oldtoNewShoppergroups[$oldgroup['shopper_group_id']] = $alreadyKnownIds[$oldgroup['shopper_group_id']];
			}


			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				break;
			}
		}

		$time = microtime(true) - $starttime;
		$this->_app->enqueueMessage('Processed '.$i.' vm1 shoppergroups time: '.$time);

		$this->storeMigrationProgress('shoppergroups',$oldtoNewShoppergroups);

	}

	private function portUsers(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}

		$oldToNewShoppergroups = $this->getMigrationProgress('shoppergroups');
		if(empty($oldToNewShoppergroups)){
			vmInfo('portUsers getMigrationProgress shoppergroups ' . $this->_db->getErrorMsg());
			return false;
		}

		if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
		$userModel = new VirtueMartModelUser();

		$ok = true;
		$continue = true;

		//approximatly 110 users take a 1 MB
		$maxItems = $this->_getMaxItems('Users');


		// 		$maxItems = 10;
		$i=0;
		$startLimit = 0;
		while($continue){

			//Lets load all users from the joomla hmm or vm? VM1 users does NOT exist
			$q = 'SELECT `p`.*,`ui`.*,`svx`.*,`aug`.*,`ag`.*,`vmu`.virtuemart_user_id FROM #__users AS `p`
								LEFT OUTER JOIN #__vm_user_info AS `ui` ON `ui`.user_id = `p`.id
								LEFT OUTER JOIN #__vm_shopper_vendor_xref AS `svx` ON `svx`.user_id = `p`.id
								LEFT OUTER JOIN #__vm_auth_user_group AS `aug` ON `aug`.user_id = `p`.id
								LEFT OUTER JOIN #__vm_auth_group AS `ag` ON `ag`.group_id = `aug`.group_id
								LEFT OUTER JOIN #__virtuemart_vmusers AS `vmu` ON `vmu`.virtuemart_user_id = `p`.id
								WHERE ISNULL (`vmu`.virtuemart_user_id)  LIMIT '.$startLimit.','.$maxItems ;

			$res = self::loadCountListContinue($q,$startLimit,$maxItems,'port shoppers');
			$oldUsers = $res[0];
			$startLimit = $res[1];
			$continue = $res[2];

			$starttime = microtime(true);

			foreach($oldUsers as $user){

				$user['virtuemart_country_id'] = $this->getCountryIdByCode($user['country']);
				$user['virtuemart_state_id'] = $this->getCountryIdByCode($user['state']);

				if(!empty($user['shopper_group_id'])){
					$user['virtuemart_shoppergroups_id'] = $oldToNewShoppergroups[$user['shopper_group_id']];
				}

				//Solution takes vm1 original values, but is not tested (does not set mainvendor)
				//if(!empty($user['group_name'])){
				//    $user['perms'] = $user['group_name'];
				//
				//} else {
				$user['user_is_vendor'] = 0;
				if($user['gid'] == 25){
					$user['perms'] = 'admin';
// 					$user['user_is_vendor'] = 1;
				}elseif($user['gid'] == 24){
					$user['perms'] = 'storeadmin';
				}else {
					$user['perms'] = 'shopper';
				}
				//}

				$user['virtuemart_user_id'] = $user['id'];
				$userModel->setUserId($user['id']);

				//Save the VM user stuff
				if(!$user=$userModel->saveUserData($user)){
					vmError(JText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA'));
				}

				$userinfo   = $this->getTable('userinfos');
				if (!$userinfo->bindChecknStore($user)) {
					vmError($userinfo->getError());
				}

				if(!empty($user['user_is_vendor']) && $user['user_is_vendor'] === 1){
					if (!$userModel->storeVendorData($user)){
						vmError($userModel->getError());
					}
				}

				$i++;
				/*	if($i>24){
					$continue = false;
					break;
					}*/
				if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
					$continue = false;
					break;
				}

				$errors = $userModel->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						vmError($error);
					}
					$userModel->resetErrors();
					$continue = false;
					//break;
				}
			}
		}
		$time = microtime(true) - $starttime;
		vmInfo('Processed '.$i.' vm1 users time: '.$time);

		//adresses
		$starttime = microtime(true);
		$continue = true;
		$startLimit = 0;
		$i = 0;
		while($continue){

			$q = 'SELECT `ui`.* FROM #__vm_user_info as `ui`
					LEFT OUTER JOIN #__virtuemart_userinfos as `vui` ON `vui`.`virtuemart_user_id` = `ui`.`user_id`
					WHERE `ui`.`address_type` = "ST" AND  ISNULL (`vui`.`virtuemart_user_id`) LIMIT '.$startLimit.','.$maxItems;

			$res = self::loadCountListContinue($q,$startLimit,$maxItems,'port ST addresses');
			$oldUsersAddresses = $res[0];
			$startLimit = $res[1];
			$continue = $res[2];


			if(empty($oldUsersAddresses)) return $ok;

			//$alreadyKnownIds = $this->getMigrationProgress('staddress');
			$oldtonewST = array();

			foreach($oldUsersAddresses as $oldUsersAddi){

				// 			if(!array_key_exists($oldcategory['virtuemart_userinfo_id'],$alreadyKnownIds)){
				$oldUsersAddi['virtuemart_user_id'] = $oldUsersAddi['user_id'];

				$oldUsersAddi['virtuemart_country_id'] = $this->getCountryIdByCode($oldUsersAddi['country']);
				$oldUsersAddi['virtuemart_state_id'] = $this->getCountryIdByCode($oldUsersAddi['state']);

				if(!$virtuemart_userinfo_id = $userModel->storeAddress($oldUsersAddi)){
					$userModel->setError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
				}

				$errors = $userModel->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->_app->enqueueMessage('Migration: ' . $error);
					}
					$userModel->resetErrors();
					break;
				}
				$i++;
				if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
					$continue = false;
					break;
				}

			}
		}

		$time = microtime(true) - $starttime;
		vmInfo('Processed '.$i.' vm1 users ST adresses time: '.$time);
		return $ok;
	}

	private function portCategories(){

		if(!class_exists('VirtueMartModelCategory'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'category.php');
		$catModel = new VirtueMartModelCategory();

		$default_category_browse = JRequest::getString('migration_default_category_browse','');
		vmdebug('migration_default_category_browse '.$default_category_browse);

		$default_category_fly = JRequest::getString('migration_default_category_fly','');

		$portFlypages = JRequest::getInt('migration_default_category_fly',0);

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}
		$ok = true;

		$q = 'SELECT * FROM #__vm_category';
		$this->_db->setQuery($q);
		$oldCategories = $this->_db->loadAssocList();

		$alreadyKnownIds = $this->getMigrationProgress('cats');
		$oldtonewCats = array();

		$category = array();
		$i = 0;
		foreach($oldCategories as $oldcategory){

			if(!array_key_exists($oldcategory['category_id'],$alreadyKnownIds)){
				$category = null;
				$category = array();
				//$category['virtuemart_category_id'] = $oldcategory['category_id'];
				$category['virtuemart_vendor_id'] = $oldcategory['vendor_id'];
				$category['category_name'] = $oldcategory['category_name'];

				$category['category_description'] = $oldcategory['category_description'];
				$category['published'] = $oldcategory['category_publish'] == 'Y' ? 1 : 0;
				$category['created_on'] = $oldcategory['cdate'];
				$category['modified_on'] = $oldcategory['mdate'];

// 				if($default_category_browse!=$oldcategory['category_browsepage']){
// 					$browsepage = $oldcategory['category_browsepage'];
// 					if (strcmp($browsepage, 'managed') ==0 ) {
// 						$browsepage="browse_".$oldcategory['products_per_row'];
// 					}
// 					$category['category_layout'] = $browsepage;
// 				}

// 				if($portFlypages && $default_category_fly!=$oldcategory['category_flypage']){
// 					$category['category_product_layout'] = $oldcategory['category_flypage'];
// 				}

				//idea was to do it by the layout, but we store this information additionally for enhanced pagination
				$category['products_per_row'] = $oldcategory['products_per_row'];
				$category['ordering'] = $oldcategory['list_order'];

				if(!empty($oldcategory['category_full_image'])){
					$category['virtuemart_media_id'] = $this->_getMediaIdByName($oldcategory['category_full_image'],'category');
				}

				$catModel->setId(0);
				$category_id = $catModel->store($category);
				$errors = $catModel->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->setError($error);
						$ok = false;
					}
					break;
				}
// 				$table = $this->getTable('categories');

// 				$category = $table->bindChecknStore($category);
// 				$errors = $table->getErrors();
// 				if(!empty($errors)){
// 					foreach($errors as $error){
// 						$this->setError($error);
// 						$ok = false;
// 					}
// 					break;
// 				}

				$oldtonewCats[$oldcategory['category_id']] = $category_id;
				unset($category['virtuemart_category_id']);
				$i++;
			} else {
				$oldtonewCats[$oldcategory['category_id']] = $alreadyKnownIds[$oldcategory['category_id']];
			}

			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				break;
			}

		}

		$this->storeMigrationProgress('cats',$oldtonewCats);
		if($ok)
		$msg = 'Looks everything worked correct, migrated ' . $i . ' categories ';
		else {
			$msg = 'Seems there was an error porting ' . $i . ' categories ';
			foreach($this->getErrors() as $error){
				$msg .= '<br />' . $error;
			}
		}
		$this->_app->enqueueMessage($msg);


		$q = 'SELECT * FROM #__vm_category_xref ';
		$this->_db->setQuery($q);
		$oldCategoriesX = $this->_db->loadAssocList();

		$alreadyKnownIds = $this->getMigrationProgress('catsxref');
		$category = array();
		$new_id = 0;
		$oldtonewCatsXref = array();
		$i = 0;
		if(!empty($oldCategoriesX)){
			foreach($oldCategoriesX as $oldcategoryX){
				if(!array_key_exists($oldcategoryX['category_parent_id'],$alreadyKnownIds)){
					$new_id = $oldtonewCats[$oldcategoryX['category_parent_id']];
					$category['category_parent_id'] = $new_id;

					$new_id = $oldtonewCats[$oldcategoryX['category_child_id']];
					$category['category_child_id'] = $new_id;

					$table = $this->getTable('category_categories');

					$category = $table->bindChecknStore($category);
					$errors = $table->getErrors();
					if(!empty($errors)){
						foreach($errors as $error){
							$this->setError($error);
							$ok = false;
						}
						break;
					}

					$oldtonewCatsXref[$oldcategoryX['category_parent_id']] = $category['virtuemart_category_id'];
					unset($category['virtuemart_category_id']);
					$i++;
				} else {
					$oldtonewCatsXref[$oldcategoryX['category_parent_id']] = $alreadyKnownIds[$oldcategoryX['category_parent_id']];
				}

				if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
					break;
				}
			}

			$this->storeMigrationProgress('catsxref',$oldtonewCatsXref);
			if($ok)
			$msg = 'Looks everything worked correct, migrated ' . $i . ' categories xref ';
			else {
				$msg = 'Seems there was an error porting ' . $i . ' categories xref ';
				foreach($this->getErrors() as $error){
					$msg .= '<br />' . $error;
				}
			}
			$this->_app->enqueueMessage($msg);

			return $ok;
		}else {
			$this->_app->enqueueMessage('No categories to import');
			return $ok;
		}
	}

	private function portManufacturerCategories(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}
		$ok = true;

		$q = 'SELECT * FROM #__vm_manufacturer_category';
		$this->_db->setQuery($q);
		$oldMfCategories = $this->_db->loadAssocList();

		if(!class_exists('TableManufacturercategories')) require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'manufacturercategories.php');

		$alreadyKnownIds = $this->getMigrationProgress('mfcats');
		$oldtonewMfCats = array();

		$mfcategory = array();
		$i=0;
		foreach($oldMfCategories as $oldmfcategory){

			if(!array_key_exists($oldmfcategory['mf_category_id'],$alreadyKnownIds)){
				//$category['virtuemart_category_id'] = $oldcategory['category_id'];
				$mfcategory = null;
				$mfcategory = array();
				$mfcategory['mf_category_name'] = $oldmfcategory['mf_category_name'];
				$mfcategory['mf_category_desc'] = $oldmfcategory['mf_category_desc'];

				$table = $this->getTable('manufacturercategories');

				$mfcategory = $table->bindChecknStore($mfcategory);
				$errors = $table->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->setError($error);
						$ok = false;
					}
					break;
				}

				$oldtonewMfCats[$oldmfcategory['mf_category_id']] = $mfcategory['virtuemart_manufacturercategories_id'];
				$i++;
			} else {
				$oldtonewMfCats[$oldmfcategory['mf_category_id']] = $alreadyKnownIds[$oldmfcategory['mf_category_id']];
			}

			unset($mfcategory['virtuemart_manufacturercategories_id']);

			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				break;
			}
		}
		$this->storeMigrationProgress('mfcats',$oldtonewMfCats);

		if($ok)
		$msg = 'Looks everything worked correct, migrated ' .$i . ' manufacturer categories ';
		else {
			$msg = 'Seems there was an error porting ' . $i . ' manufacturer categories ';
			$msg .= $this->getErrors();
		}

		$this->_app->enqueueMessage($msg);

		return $ok;
	}

	private function portManufacturers(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}
		$ok = true;

		$q = 'SELECT * FROM #__vm_manufacturer ';
		$this->_db->setQuery($q);
		$oldManus = $this->_db->loadAssocList();

// 		vmdebug('my old manus',$oldManus);
		$oldtonewManus = array();
		$oldtoNewMfcats = $this->getMigrationProgress('mfcats');
		$alreadyKnownIds = $this->getMigrationProgress('manus');

		$i =0 ;
		foreach($oldManus as $oldmanu){
			if(!array_key_exists($oldmanu['manufacturer_id'],$alreadyKnownIds)){
				$manu = null;
				$manu = array();
				$manu['mf_name'] = $oldmanu['mf_name'];
				$manu['mf_email'] = $oldmanu['mf_email'];
				$manu['mf_desc'] = $oldmanu['mf_desc'];
				$manu['virtuemart_manufacturercategories_id'] = $oldtoNewMfcats[$oldmanu['mf_category_id']];
				$manu['mf_url'] = $oldmanu['mf_url'];
				$manu['published'] = 1;

				if(!class_exists('TableManufacturers'))
				require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'manufacturers.php');
				$table = $this->getTable('manufacturers');

				$table->bindChecknStore($manu);
				$errors = $table->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->setError($error);
						vmError($error);
						$ok = false;
					}
					break;
				}
				$oldtonewManus[$oldmanu['manufacturer_id']] = $manu['virtuemart_manufacturer_id'];
				//unset($manu['virtuemart_manufacturer_id']);
				$i++;
			} else {
				$oldtonewManus[$oldmanu['manufacturer_id']] = $alreadyKnownIds[$oldmanu['manufacturer_id']];
			}

			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				break;
			}
		}

		$this->storeMigrationProgress('manus',$oldtonewManus);

		if($ok)
		$msg = 'Looks everything worked correct, migrated ' .$i . ' manufacturers ';
		else {
			$msg = 'Seems there was an error porting ' . $i . ' manufacturers ';
			$msg .= $this->getErrors();
		}
		$this->_app->enqueueMessage($msg);
	}

	private function portProducts(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}

                $mediaIdFilename = array();
		$ok = true;
		$mediaIdFilename = array();

		//approximatly 100 products take a 1 MB
		$maxItems = $this->_getMaxItems('Products');

		$startLimit = 0;
		$i=0;
		$continue = true;
		while($continue){

			$q = 'SELECT * FROM #__vm_product AS `p`
					LEFT JOIN #__vm_product_price ON #__vm_product_price.product_id = `p`.product_id
					LEFT JOIN #__vm_product_mf_xref ON #__vm_product_mf_xref.product_id = `p`.product_id LIMIT '.$startLimit.','.$maxItems;
			$this->_db->setQuery($q);
			$oldProducts = $this->_db->loadAssocList();
			if(empty($oldProducts)){
				$this->_app->enqueueMessage('_productPorter ' . $this->_db->getErrorMsg());
				$continue = false;
				return false;
			} else {
				$this->_app->enqueueMessage('Found '.count($oldProducts).' vm1 products to import' );
				$startLimit += $maxItems;
				if(count($oldProducts)<$maxItems){
					$continue = false;
				}
			}

			//vmdebug('in product migrate $oldProducts ',$oldProducts);

			if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'product.php');

			$productModel = new VirtueMartModelProduct();


			/* Not in VM1
			 slug low_stock_notification intnotes metadesc metakey metarobot metaauthor layout published

			 created_on created_by modified_on modified_by
			 product_override_price override link

			 Not in VM2
			 product_thumb_image product_full_image attribute
			 custom_attribute child_options quantity_options child_option_ids
			 shopper_group_id    product_list
			 */


			$alreadyKnownIds = $this->getMigrationProgress('products');
			$oldToNewCats = $this->getMigrationProgress('cats');
			$user = JFactory::getUser();

			$oldtonewProducts = array();
			$oldtonewManus = $this->getMigrationProgress('manus');


			//There are so many names the same, so we use the loaded array and manipulate it

			foreach($oldProducts as $product){

				if(!array_key_exists($product['product_id'],$alreadyKnownIds)){

					$product['virtuemart_vendor_id'] = $product['vendor_id'];

					if(!empty($product['manufacturer_id'])){
						if(!empty($oldtonewManus[$product['manufacturer_id']])) {
							$product['virtuemart_manufacturer_id'] = $oldtonewManus[$product['manufacturer_id']];
						}
					}

					$q = 'SELECT `category_id` FROM #__vm_product_category_xref WHERE #__vm_product_category_xref.product_id = "'.$product['product_id'].'" ';
					$this->_db->setQuery($q);
					$productCats = $this->_db->loadResultArray();

					$productcategories = array();
					if(!empty($productCats)){
						foreach($productCats as $cat){
							//product has category_id and categories?
							if(!empty($oldToNewCats[$cat])){
// 								$product['virtuemart_category_id'] = $oldToNewCats[$cat];
								//This should be an array, or is it not in vm1? not cleared, may need extra foreach
								$productcategories[] = $oldToNewCats[$cat];
							} else {
								vmInfo('Coulndt find category for product, maybe just not in a category');
							}
						}
					}

					$product['categories'] = $productcategories;

					$product['published'] = $product['product_publish'] == 'Y' ? 1 : 0;

					$product['product_price_quantity_start'] = $product['price_quantity_start'];
					$product['product_price_quantity_end'] = $product['price_quantity_end'];

					$product['created_on'] = $this->_changeToStamp($product['cdate']);
					$product['modified_on'] = $this->_changeToStamp($product['mdate']); //we could remove this to set modified_on today
					$product['product_available_date'] = $this->_changeToStamp($product['product_available_date']);

					if(!empty($product['product_weight_uom'])){
						$product['product_weight_uom'] = $this->parseWeightUom($product['product_weight_uom']);
					}

					if(!empty($product['product_lwh_uom'])){
						$product['product_lwh_uom'] = $this->parseDimensionUom($product['product_lwh_uom']);
					}
					//$product['created_by'] = $user->id;
					//$product['modified_by'] = $user->id;

					$product['product_currency'] = $this->_ensureUsingCurrencyId($product['product_currency']);

					if(empty($product['product_name'] )){
						$product['product_name'] =  $product['product_sku'].':'.$product['product_id'].':'.$product['product_s_desc'];
					}

					// Here we  look for the url product_full_image and check which media has the same
					// full_image url
					if(!empty($product['product_full_image'])){
						$product['virtuemart_media_id'] = $this->_getMediaIdByName($product['product_full_image'],'product');
					}

					if(!empty($alreadyKnownIds[$product['product_id']])){
						$product['product_parent_id'] = $alreadyKnownIds[$product['product_id']];
					} else {
						$product['product_parent_id'] = 0;
					}

					$product['virtuemart_product_id'] = $productModel->store($product);

					$errors = $productModel->getErrors();
					if(!empty($errors)){
						foreach($errors as $error){
							vmError('Migration: '.$i.' ' . $error);
						}
						vmdebug('Product add error',$product);
						$productModel->resetErrors();
						$continue = false;
						break;
					}
					$i++;

					$oldtonewProducts[$product['product_id']] = $product['virtuemart_product_id'];

				} else {
					$oldtonewProducts[$product['product_id']] = $alreadyKnownIds[$product['product_id']];
				}
// 				unset($product['virtuemart_product_id']);
				if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){

					$continue = false;
					break;
				}

			}
		}

		$this->storeMigrationProgress('products',$oldtonewProducts);
		vmInfo('Migration: '.$i.' products processed ');

		return $ok;
	}

	/**
	 * Finds the media id in the vm2 table for a given filename
	 *
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 *
	 */
	var $mediaIdFilename = array();

	function _getMediaIdByName($filename,$type){
		if(!empty($this->mediaIdFilename[$type][$filename])){

			return $this->mediaIdFilename[$type][$filename];
		} else {
			$q = 'SELECT `virtuemart_media_id` FROM `#__virtuemart_medias`
										WHERE `file_title`="' .  $this->_db->getEscaped($filename) . '"
										AND `file_type`="' . $this->_db->getEscaped($type) . '"';
			$this->_db->setQuery($q);
			$virtuemart_media_id = $this->_db->loadResult();
			if($this->_db->getErrors()){
				vmError('Error in _getMediaIdByName',$this->_db->getErrorMsg());
			}
			if(!empty($virtuemart_media_id)){
				$this->mediaIdFilename[$type][$filename] = $virtuemart_media_id;
				return $virtuemart_media_id;
			} else {

				vmdebug('nothing found for '.$type.' '.$filename);
			}
		}
	}

	function portOrders(){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			return;
		}

		//approximatly 100 products take a 1 MB
		$maxItems = $this->_getMaxItems('Orders');

		$startLimit = 0;
		$i = 0;
		$continue=true;
		while($continue){

			$q = 'SELECT `o`.*, `op`.*, `o`.`order_number` as `vm1_order_number`, `o2`.`order_number` as `nr2` FROM `#__vm_orders` as `o`
				LEFT OUTER JOIN `#__vm_order_payment` as `op` ON `op`.`order_id` = `o`.`order_id`
				LEFT JOIN `#__virtuemart_orders` as `o2` ON `o2`.`order_number` = `o`.`order_number`
				WHERE ISNULL (o2.order_number) LIMIT '.$startLimit.','.$maxItems;

			$res = self::loadCountListContinue($q,$startLimit,$maxItems,'port Orders');
			$oldOrders = $res[0];
			$startLimit = $res[1];
			$continue = $res[2];

			if(!class_exists('VirtueMartModelOrderstatus'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orderstatus.php');

			$oldtonewOrders = array();

			//Looks like there is a problem, when the data gets tooo big,
			//solved now with query directly ignoring already ported orders.
			$alreadyKnownIds = $this->getMigrationProgress('orders');
			$newproductIds = $this->getMigrationProgress('products');
			$orderCodeToId = $this->createOrderStatusAssoc();

			foreach($oldOrders as $order){

				if(!array_key_exists($order['order_id'],$alreadyKnownIds)){
					$orderData = new stdClass();

					$orderData->virtuemart_order_id = null;
					$orderData->virtuemart_user_id = $order['user_id'];
					$orderData->virtuemart_vendor_id = $order['vendor_id'];
					$orderData->order_number = $order['vm1_order_number'];
					$orderData->order_pass = 'p' . substr(md5((string)time() . $order['order_number']), 0, 5);
					//Note as long we do not have an extra table only storing addresses, the virtuemart_userinfo_id is not needed.
					//The virtuemart_userinfo_id is just the id of a stored address and is only necessary in the user maintance view or for choosing addresses.
					//the saved order should be an snapshot with plain data written in it.
					//		$orderData->virtuemart_userinfo_id = 'TODO'; // $_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
					$orderData->order_total = $order['order_total'];
					$orderData->order_subtotal = $order['order_subtotal'];
					$orderData->order_tax = $order['order_tax'];
					$orderData->order_tax_details = null; // TODO What's this?? Which data needs to be serialized?  I dont know also
					$orderData->order_shipment = $order['order_shipment'];
					$orderData->order_shipment_tax = $order['order_shipment_tax'];
					if(!empty($_cart->couponCode)){
						$orderData->coupon_code = $order['coupon_code'];
						$orderData->coupon_discount = $order['coupon_discount'];
					}
					$orderData->order_discount = $order['order_discount'];
					//$orderData->order_currency = null; // TODO; Max: the currency should be in the cart somewhere!
					//$orderData->order_status = $orderCodeToId[$order['order_status']];
					$orderData->order_status = $order['order_status'];

					if(isset($_cart->virtuemart_currency_id)){
						$orderData->user_currency_id = $order['order_currency'];
						//$orderData->user_currency_rate = $order['order_status'];
					}
					$orderData->payment_method_id = $order['payment_method_id'];
					$orderData->ship_method_id = $order['ship_method_id'];
					//$orderData->order_status_id = $oldToNewOrderstates[$order['order_status']]


					$_filter = JFilterInput::getInstance(array('br', 'i', 'em', 'b', 'strong'), array(), 0, 0, 1);
					$orderData->customer_note = $_filter->clean($order['customer_note']);
					$orderData->ip_address = $order['ip_address'];

					$orderData->created_on = $this->_changeToStamp($order['cdate']);
					$orderData->modified_on = $this->_changeToStamp($order['mdate']); //we could remove this to set modified_on today

					$orderTable = $this->getTable('orders');
					$newId = $orderTable->bindChecknStore($orderData);
					$errors = $orderTable->getErrors();
					if(!empty($errors)){
						foreach($errors as $error){
							$this->_app->enqueueMessage('Migration orders: ' . $error);
						}
						$continue = false;
						break;
					}
					$i++;
					$newId = $oldtonewOrders[$order['order_id']] = $orderTable->virtuemart_order_id;

					$q = 'SELECT * FROM `#__vm_order_item` WHERE `order_id` = "'.$order['order_id'].'" ';
					$this->_db->setQuery($q);
					$oldItems = $this->_db->loadAssocList();
					//$this->_app->enqueueMessage('Migration orderhistories: ' . $newId);
					foreach($oldItems as $item){
						$item['virtuemart_order_id'] = $newId;
						$item['product_id'] = $newproductIds[$item['product_id']];
						//$item['order_status'] = $orderCodeToId[$item['order_status']];
						$product['created_on'] = $this->_changeToStamp($item['cdate']);
						$product['modified_on'] = $this->_changeToStamp($item['mdate']); //we could remove this to set modified_on today

						$orderItemsTable = $this->getTable('order_items');
						$orderItemsTable->bindChecknStore($item);
						$errors = $orderItemsTable->getErrors();
						if(!empty($errors)){
							foreach($errors as $error){
								$this->_app->enqueueMessage('Migration orderitems: ' . $error);
							}
							$continue = false;
							break;
						}
					}

					$q = 'SELECT * FROM `#__vm_order_history` WHERE `order_id` = "'.$order['order_id'].'" ';
					$this->_db->setQuery($q);
					$oldItems = $this->_db->loadAssocList();

					foreach($oldItems as $item){
						$item['virtuemart_order_id'] = $newId;
						//$item['order_status_code'] = $orderCodeToId[$item['order_status_code']];


						$orderHistoriesTable = $this->getTable('order_histories');
						$orderHistoriesTable->bindChecknStore($item);
						$errors = $orderHistoriesTable->getErrors();
						if(!empty($errors)){
							foreach($errors as $error){
								$this->_app->enqueueMessage('Migration orderhistories: ' . $error);
							}
							$continue = false;
							break;
						}
					}

					$q = 'SELECT * FROM `#__vm_order_user_info` WHERE `order_id` = "'.$order['order_id'].'" ';
					$this->_db->setQuery($q);
					$oldItems = $this->_db->loadAssocList();

					if (!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

					foreach($oldItems as $item){
						$item['virtuemart_order_id'] = $newId;
						$item['virtuemart_country_id'] = ShopFunctions::getCountryIDByName($item['country']);
						$item['virtuemart_state_id'] = ShopFunctions::getStateIDByName($item['state']);

						$item['email'] = $item['user_email'];
						$orderUserinfoTable = $this->getTable('order_userinfos');
						$orderUserinfoTable->bindChecknStore($item);
						$errors = $orderUserinfoTable->getErrors();
						if(!empty($errors)){
							foreach($errors as $error){
								$this->_app->enqueueMessage('Migration orderuserinfo: ' . $error);
							}
							$continue = false;
							break;
						}
					}

					//$this->_app->enqueueMessage('Migration: '.$i.' order processed new id '.$newId);
				} else {
					$oldtonewOrders[$order['order_id']] = $alreadyKnownIds[$order['order_id']];
				}

				if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
					$continue = false;

					break;
				}
			}
		}
		$this->storeMigrationProgress('orders',$oldtonewOrders);
		vmInfo('Migration: '.$i.' orders processed ');
	}

	function portOrderStatus(){

		$q = 'SELECT * FROM `#__vm_order_status` ';

		$this->_db->setQuery($q);
		$oldOrderStatus = $this->_db->loadAssocList();

		if(!class_exists('VirtueMartModelOrderstatus'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orderstatus.php');
		$orderstatusModel = new VirtueMartModelOrderstatus();
		$oldtonewOrderstates = array();
		//$alreadyKnownIds = $this->getMigrationProgress('orderstates');
		$i = 0;
		foreach($oldOrderStatus as $status){
			if(!array_key_exists($status['order_status_id'],$alreadyKnownIds)){
				$status['virtuemart_orderstate_id'] = 0;
				$status['virtuemart_vendor_id'] = $status['vendor_id'];
				$status['ordering'] = $status['list_order'];
				$status['published'] = 1;

				$newId = $orderstatusModel->store($status);
				$errors = $orderstatusModel->getErrors();
				if(!empty($errors)){
					foreach($errors as $error){
						$this->_app->enqueueMessage('Migration: ' . $error);
					}
					$orderstatusModel->resetErrors();
					//break;
				}
				$oldtonewOrderstates[$status['order_status_id']] = $newId;
				$i++;
			} else {
				//$oldtonewOrderstates[$status['order_status_id']] = $alreadyKnownIds[$status['order_status_id']];
			}

			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				break;
			}
		}

		$oldtonewOrderstates = array_merge($oldtonewOrderstates,$alreadyKnownIds);
		$oldtonewOrderstates = array_unique($oldtonewOrderstates);

		vmInfo('Migration: '.$i.' orderstates processed ');
		return;
	}

	private function _changeToStamp($dateIn){

		$date = JFactory::getDate($dateIn);
		return $date->toMySQL();
	}

	private function _ensureUsingCurrencyId($curr){

		$currInt = '';
		if(!empty($curr)){
			$this->_db = JFactory::getDBO();
			$q = 'SELECT `virtuemart_currency_id` FROM `#__virtuemart_currencies` WHERE `currency_code_3`="' . $this->_db->getEscaped($curr) . '"';
			$this->_db->setQuery($q);
			$currInt = $this->_db->loadResult();
			if(empty($currInt)){
				JError::raiseWarning(E_WARNING, 'Attention, couldnt find currency id in the table for id = ' . $curr);
			}
		}

		return $currInt;
	}

	private function _getMaxItems($name){

		$maxItems = 50;
		$freeRam =  ($this->maxMemoryLimit - memory_get_usage(true))/(1024 * 1024) ;
		$maxItems = (int)$freeRam * 100;
		if($maxItems<=0){
			$maxItems = 50;
			vmWarn('Your system is low on RAM! Limit set: '.$this->maxMemoryLimit.' used '.memory_get_usage(true)/(1024 * 1024).' MB and php.ini '.ini_get('memory_limit'));
		}
		vmdebug('Migrating '.$name.', free ram left '.$freeRam.' so limit chunk to '.$maxItems);
		return $maxItems;
	}

	/**
	 * Gets the virtuemart_country_id by a country 2 or 3 code
	 *
	 * @author Max Milbers
	 * @param string $name Country 3 or Country 2 code (example US for United States)
	 * return int virtuemart_country_id
	 */
	private function getCountryIdByCode($name){
		if(empty($name)){
			return 0;
		}

		if(strlen($name) == 2){
			$countryCode = 'country_2_code';
		}else {
			$countryCode = 'country_3_code';
		}

		$q = 'SELECT `virtuemart_country_id` FROM `#__virtuemart_countries`
				WHERE `' . $countryCode . '` = "' . $this->_db->getEscaped($name) . '" ';
		$this->_db->setQuery($q);

		return $this->_db->loadResult();
	}

	/**
	 * Gets the virtuemart_country_id by a country 2 or 3 code
	 *
	 * @author Max Milbers
	 * @param string $name Country 3 or Country 2 code (example US for United States)
	 * return int virtuemart_country_id
	 */
	private function getStateIdByCode($name){
		if(empty($name)){
			return 0;
		}

		if(strlen($name) == 2){
			$code = 'country_2_code';
		}else {
			$code = 'country_3_code';
		}

		$q = 'SELECT `virtuemart_state_id` FROM `#__virtuemart_states`
				WHERE `' . $code . '` = "' . $this->_db->getEscaped($name) . '" ';
		$this->_db->setQuery($q);

		return $this->_db->loadResult();
	}

	/**
	 *
	 *
	 * @author Max Milbers
	 */
	private function createOrderStatusAssoc(){

		$q = 'SELECT * FROM `#__virtuemart_orderstates` ';
		$this->_db->setQuery($q);
		$orderstats = $this->_db->loadAssocList();
		$xref = array();
		foreach($orderstats as $status){

			$xref[$status['order_status_code']] = $status['virtuemart_orderstate_id'];
		}

		return $xref;
	}

	/**
	 * parse the entered string to a standard unit
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 *
	 */
	private function parseWeightUom($weightUnit){

		$weightUnit = strtolower($weightUnit);
		$weightUnitMigrateValues = self::getWeightUnitMigrateValues();
		return $this->parseUom($weightUnit,$weightUnitMigrateValues );

	}

	/**
	 *
	 * parse the entered string to a standard unit
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 *
	 */
	private function parseDimensionUom($dimensionUnit){

		$dimensionUnitMigrateValues = self::getDimensionUnitMigrateValues();
		$dimensionUnit = strtolower($dimensionUnit);
		return $this->parseUom($dimensionUnit,$dimensionUnitMigrateValues );

	}

	/**
	 *
	 * parse the entered string to a standard unit
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 *
	 */
	private function parseUom($unit, $migrateValues){
		$new="";
		$unit = strtolower($unit);
		foreach ($migrateValues as $old => $new) {
			if (strpos($unit, $old) !== false) {
				return $new;
			}
		}
	}

	/**
	 *
	 * get new Length Standard Unit
	 * @author Valerie Isaksen
	 *
	 */
	function getDimensionUnitMigrateValues() {

		$dimensionUnitMigrate=array (
                  'mm' => 'MM'
                  , 'cm' => 'CM'
                  , 'm' => 'M'
                  , 'yd' => 'YD'
                  , 'foot' => 'FT'
                  , 'ft' => 'FT'
                  , 'inch' => 'IN'
                  );
                  return $dimensionUnitMigrate;
	}
	/**
	 *
	 * get new Weight Standard Unit
	 * @author Valerie Isaksen
	 *
	 */
	function getWeightUnitMigrateValues() {
		$weightUnitMigrate=array (
                  'kg' => 'KG'
                  , 'kilos' => 'KG'
                  , 'gr' => 'GR'
                  , 'pound' => 'LB'
                  , 'livre' => 'LB'
                  , 'once' => 'OZ'
                  , 'ounce' => 'OZ'
                  );
                  return $weightUnitMigrate;
	}

	/**
	 * Helper function, was used to determine the difference of an loaded array (from vm19
	 * and a loaded object of vm2
	 */
	private function showVmDiff(){

		//$product = $productModel->getProduct(0);

		$productK = array();
		$attribsImage = get_object_vars($product);

		foreach($attribsImage as $k => $v){
			$productK[] = $k;
		}

		$oldproductK = array();
		foreach($oldProducts[0] as $k => $v){
			$oldproductK[] = $k;
		}

		$notSame = array_diff($productK, $oldproductK);
		$names = '';
		foreach($notSame as $name){
			$names .= $name . ' ';
		}
		$this->_app->enqueueMessage('_productPorter  array_intersect ' . $names);

		$notSame = array_diff($oldproductK, $productK);
		$names = '';
		foreach($notSame as $name){
			$names .= $name . ' ';
		}
		$this->_app->enqueueMessage('_productPorter  ViceVERSA array_intersect ' . $names);
	}

	function loadCountListContinue($q,$startLimit,$maxItems,$msg){

		$continue = true;
		$this->_db->setQuery($q);
		if(!$this->_db->query()){
			vmError($msg.' db error '. $this->_db->getErrorMsg());
			vmError($msg.' db error '. $this->_db->getQuery());
			$entries = array();
			$continue = false;
		} else {
			$entries = $this->_db->loadAssocList();
			$count = count($entries);
			vmInfo($msg. ' found '.$count.' vm1 entries for migration ');
			$startLimit += $maxItems;
			if($count<$maxItems){
				$continue = false;
			}
		}

		return array($entries,$startLimit,$continue);
	}

	function portCurrency(){

		$this->setRedirect($this->redirectPath);
		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_currency_id`,
		  `currency_name`,
		  `currency_code_2`,
		  `currency_code` AS currency_code_3,
		  `currency_numeric_code`,
		  `currency_exchange_rate`,
		  `currency_symbol`,
		`currency_display_style` AS `_display_style`
			FROM `#__virtuemart_currencia` ORDER BY virtuemart_currency_id';
		$db->setQuery($q);
		$result = $db->loadObjectList();

		foreach($result as $item){

			//			$item->virtuemart_currency_id = 0;
			$item->currency_exchange_rate = 0;
			$item->published = 1;
			$item->shared = 1;
			$item->virtuemart_vendor_id = 1;

			$style = explode('|', $item->_display_style);

			$item->currency_nbDecimal = $style[2];
			$item->currency_decimal_symbol = $style[3];
			$item->currency_thousands = $style[4];
			$item->currency_positive_style = $style[5];
			$item->currency_negative_style = $style[6];

			$db->insertObject('#__virtuemart_currencies', $item);
		}

		$this->setRedirect($this->redirectPath);
	}

	/**
	 * Method to restore all virtuemart tables in a database with a given prefix
	 *
	 * @access	public
	 * @param	string	Old table prefix
	 * @return	boolean	True on success.
	 */
	function restoreDatabase($prefix='bak_vm_') {
		// Initialise variables.
		$return = true;

		$this->_db = JFactory::getDBO();

		// Get the tables in the database.
		if ($tables = $this->_db->getTableList()) {
			foreach ($tables as $table) {
				// If the table uses the given prefix, back it up.
				if (strpos($table, $prefix) === 0) {
					// restore table name.
					$restoreTable = str_replace($prefix, '#__vm_', $table);

					// Drop the current active table.
					$this->_db->setQuery('DROP TABLE IF EXISTS '.$this->_db->nameQuote($restoreTable));
					$this->_db->query();

					// Check for errors.
					if ($this->_db->getErrorNum()) {
						$this->setError($this->_db->getErrorMsg());
						$return = false;
					}

					// Rename the current table to the backup table.
					$this->_db->setQuery('RENAME TABLE '.$this->_db->nameQuote($table).' TO '.$this->_db->nameQuote($restoreTable));
					$this->_db->query();

					// Check for errors.
					if ($this->_db->getErrorNum()) {
						$this->setError($this->_db->getErrorMsg());
						$return = false;
					}
				}
			}
		}

		return $return;
	}

}

