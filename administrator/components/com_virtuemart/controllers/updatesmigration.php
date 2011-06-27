<?php
/**
*
* updatesMigration controller
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

// Load the controller framework
jimport('joomla.application.component.controller');

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * updatesMigration Controller
 *
 * @package    VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers
 */
class VirtuemartControllerUpdatesMigration extends VmController {

    private $installer;

    /**
     * Method to display the view
     *
     * @access	public
     */
    function __construct() {
		parent::__construct();

		// $this->setMainLangKey('MIGRATION');
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('updatesMigration', $viewType);

		// Push a model into the view
		$model = $this->getModel('updatesMigration');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		
    }

     /**
      * Call at begin of every task to check if the permission is high enough.
      * Atm the standard is at least vm admin
      * @author Max Milbers
      */
     private function checkPermissionForTools(){
	     //Hardcore Block, we may do that better later
	     if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
	     if(!Permissions::getInstance()->check('admin') ){
		     $msg = 'Forget IT';
		     $this->setRedirect('index.php?option=com_virtuemart', $msg);
	     }
	     return true;
     }
	
     /**
      * Akeeba release system tasks
      * Update
      * @author Max Milbers
      */
     function liveUpdate(){

	     $this->setRedirect('index.php?option=com_virtuemart&view=liveupdate.', $msg);
     }

    /**
     * Install sample data into the database
     *
     * @author RickG
     */
    function checkForLatestVersion() {
		$model = $this->getModel('updatesMigration');
		JRequest::setVar('latestverison', $model->getLatestVersion());
		JRequest::setVar('view', 'updatesMigration');

		parent::display();
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
	 * @author Max Milbers
     */
    function installSampleData() {

    	$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
		$model = $this->getModel('updatesMigration');

		$msg = $model->installSampleData();

		$this->setRedirect($this->redirectPath, $msg);
    }


    /**
     * Install sample data into the database
     *
     * @author RickG
	 * @author Max Milbers
     */
    function userSync() {

    	$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');
		$msg = $model->integrateJoomlaUsers();

		$this->setRedirect($this->redirectPath, $msg);
    }


    /**
     * Sets the storeowner to the currently logged in user
	 * He needs to have admin rights todo so
	 * 
     * @author Max Milbers
     */
    function setStoreOwner(){

    	$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');
		$msg = $model->setStoreOwner();

		$this->setRedirect($this->redirectPath, $msg);

    }

    /**
     * Install sample data into the database
     *
     * @author RickG
	 * @author Max Milbers
     */
    function restoreSystemDefaults() {
    	
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
    	if(VmConfig::get('dangeroustools',false)){
    		
 			$model = $this->getModel('updatesMigration');
			$model->restoreSystemDefaults();

			$msg = JText::_('COM_VIRTUEMART_SYSTEM_DEFAULTS_RESTORED');
			$msg .= ' User id of the main vendor is '.$model->setStoreOwner();
			$this->setDangerousToolsOff();
    	} else {
            $msg = $this->_getMsgDangerousTools();
    	}

    	$this->setRedirect($this->redirectPath, $msg);
   }


    /**
     * Remove all the Virtuemart tables from the database.
     *
     * @author RickG
	 * @author Max Milbers
     */
    function deleteVmTables() {

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMTABlES_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
    		$model = $this->getModel('updatesMigration');

			if (!$model->removeAllVMTables()) {
				$this->setDangerousToolsOff();
			    $this->setRedirect('index.php?option=com_virtuemart', $model->getError());
			}
    	} else {
		 $msg = $this->_getMsgDangerousTools();
		}
    	$this->setRedirect('index.php?option=com_installer',$msg);
    }

	/**
	 * Deletes all dynamical created data and leaves a "fresh" installation without sampeldata
	 * OUTDATED
	 * @author Max Milbers
	 * 
	 */
    function deleteVmData() {

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMDATA_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
			$model = $this->getModel('updatesMigration');

			if (!$model->removeAllVMData()) {
				$this->setDangerousToolsOff();
			    $this->setRedirect('index.php?option=com_virtuemart', $model->getError());
			}

    	}else {
		 $msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath, $msg);
    }


    function deleteAll() {
    	
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_ALLVMDATA_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
 
 			$this -> installer -> populateVmDatabase("delete_essential.sql");
			$this -> installer -> populateVmDatabase("delete_data.sql");
			$this->setDangerousToolsOff();
    	} else {
			 $msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath,$msg);
    }


    function deleteRestorable() {

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
 		$msg = JText::_('COM_VIRTUEMART_SYSTEM_RESTVMDATA_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
			$this -> installer -> populateVmDatabase("delete_restoreable.sql");
			$this->setDangerousToolsOff();
    	} else {
			 $msg = $this->_getMsgDangerousTools();
		}


		$this->setRedirect($this->redirectPath,$msg);
    }

	function refreshCompleteInstall(){

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

		if(VmConfig::get('dangeroustools',true)){

			$model = $this->getModel('updatesMigration');

			$model -> restoreSystemTablesCompletly();

			$model->integrateJoomlaUsers();
			$id = $model->determineStoreOwner();
			$sid = $model->setStoreOwner($id);
			$model->setUserToPermissionGroup($id);
			$model->installSampleData($id);
			$errors = $model->getErrors();

			$msg = '';
			if(empty($errors)) $msg = 'System succesfull restored and sampeldata installed, user id of the mainvendor is '.$sid;
			foreach($errors as $error){
				$msg .= ($error).'<br />';
			}

			$this->setDangerousToolsOff();
		} else {
			 $msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath,$msg);

	}

	/**
	 * This function resets the flag in the config that dangerous tools can't be executed anylonger
	 * This is a security feature
	 *
	 * @author Max Milbers
	 */
	function setDangerousToolsOff(){

		$model = $this->getModel('config');
		//$model->setDangerousToolsOff();

	}

	/**
	 * Sends the message to the user that the tools are disabled.
	 * 
	 * @author Max Milbers
	 */
    function _getMsgDangerousTools() {
		$uri = JFactory::getURI();
        $link = $uri->root().'administrator/index.php?option=com_virtuemart&view=config';
        $msg = JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_DISABLED', $link);
        return $msg;
	}
	
	function portMedia(){
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
		$migrator = new Migrator();
		$result = $migrator->portMedia();
	
		$this->setRedirect($this->redirectPath,$result);
	}
	
	function migrateAllInOne(){
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
		if(!VmConfig::get('dangeroustools',true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath,$msg);
			return false;
		}
		
		$migrator = new Migrator();
		$result = $migrator->migrateAllInOne();
		$msg = 'Migration finished';
		$this->setRedirect($this->redirectPath,$msg);
	}
	
}


if(!class_exists('JModel'))require(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS.'application'.DS.'component'.DS.'model.php');
if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

 
class Migrator extends VmModel{
	
	//Object to hold old against new ids. We wanna port as when it setup fresh, so no importing of old ids!
	private $_oldToNew = null;
	private $_test = false;
	
	public function __construct() {
		
		JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
		
		$this->_app = JFactory::getApplication();
		$this->_db = JFactory::getDBO();
		$this->_oldToNew =  new stdClass();
	}

	function migrateAllInOne(){
		
		$result = $this->portMedia();
		
		$result = $this->portShoppergroups();
		$result = $this->portUsers();

		$result = $this->portCategories();
		$result = $this->portManufacturerCategories();
		$result = $this->portManufacturers();
		$result = $this->portProducts();
		
/*		//$result = $this->portOrders();  //*/
	}

	public function portMedia(){
		
		$ok = true;
		
		//$imageExtensions = array('jpg','jpeg','gif','png');

		if(!class_exists('TableManufacturers')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		$this->mediaModel = new VirtueMartModelMedia();
		//First lets read which files are already stored
		$this->storedMedias = $this->mediaModel->getFiles(false,true,false);
		
		$countTotal = 0;
		//We do it per type
		$url = VmConfig::get('media_product_path');
		$type = 'product';
		$count = $this->_portMediaByType($url,$type);
		$countTotal +=  $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));
		
		$url = VmConfig::get('media_category_path');
		$type = 'category';
		$count = $this->_portMediaByType($url,$type);
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));
		
		
		$url = VmConfig::get('media_manufacturer_path');
		$type = 'manufacturer';
		$count = $this->_portMediaByType($url,$type);
		$countTotal += $count;	
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));

		//$this->portMediaByType(VmConfig::get('media_path'),'shop');
		
		return $msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_FINISH',$countTotal);	

	}
	
	private function _portMediaByType($url,$type){
		
	    
	    
		$knownNames = array();
		//create array of filenames for easier handling
		foreach ($this->storedMedias as $media){
			if($media->file_type==$type){
				$lastIndexOfSlash= strrpos($media->file_url,'/');
	    		$name = substr($media->file_url,$lastIndexOfSlash+1);
				$knownNames[] = $name;				
			}
		}

		$filesInDir = array();
		$foldersInDir = array();
		
		$path = str_replace('/',DS,$url);
		//$dir = JPATH_ROOT.DS.$path;
		$foldersInDir = array(JPATH_ROOT.DS.$path);
		while(!empty($foldersInDir)){
			foreach($foldersInDir as $dir){
				$subfoldersInDir = null;
				$subfoldersInDir = array();
				$relUrl = str_replace(DS,'/',substr($dir,strlen(JPATH_ROOT.DS)));
				if ($handle = opendir($dir)) {
			    	while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != '.svn'  && $file != '.picasa.ini' && $file != 'index.html') {
							//$info = pathinfo($file);
							//dump($info,'pathinfo($file)');
							//dump(filetype($dir.DS.$file),'filetype($dir.DS.$file');
							$filetype = filetype($dir.DS.$file);
							//We port all type of media, regardless the extension
							if ($filetype == 'file' ) {
								if(!in_array($file,$knownNames)){
									$filesInDir[] = array('filename'=>$file,'url'=>$relUrl);
								}
						    } else{
						    	if($filetype == 'dir' && $file!='resized'){
						    		//dump($file,'dir ($file)');
						    		$subfoldersInDir[] = $dir.$file;
								}	
							} 
						}
				    }
				}
			}
			$foldersInDir = $subfoldersInDir;

		}
		//echo '<pre>'.print_r($filesInDir,1).'</pre>';
		
		$i=0;
		foreach($filesInDir as $file){
			//dump($file,'my file');
		//	$this->_app ->enqueueMessage('Migrator '.$file);
			$data = null;
			$data = array(	'file_title'=>$file['filename'],
							'virtuemart_vendor_id'=>1,
							'file_description'=>$file['filename'],
							'file_meta'=>$file['filename'],
							'file_url'=>$file['url'].'/'.$file['filename'],
							//'file_url_thumb'=>$url.'resized/'.$filename,
							'media_published'=>1
							);
			if($type=='product')$data['file_is_product_image'] = 1;
			$this->mediaModel->setId(0);
			$success = $this->mediaModel->store($data,$type);
			$errors = $this->mediaModel->getErrors();
			foreach($errors as $error){
				$this->_app ->enqueueMessage('Migrator '.$error);
			}
			if($success) $i++; 
		}
		
		return $i;

	}
	
	private function portShoppergroups(){
	
		$ok= true;
		
		$q ='SELECT * FROM #__vm_shopper_group';
		$this->_db->setQuery($q);
		$oldShopperGroups = $this->_db->loadAssocList();
		$oldtoNewShoppergroups = array();
		
		$sGroups = array();
		foreach($oldShopperGroups as $oldgroup){
				
			//$category['virtuemart_category_id'] = $oldcategory['category_id'];
			$sGroups['virtuemart_vendor_id'] = $oldgroup['vendor_id'];
			$sGroups['shopper_group_name'] = $oldgroup['shopper_group_name'];
			
			$sGroups['shopper_group_desc'] = $oldgroup['shopper_group_desc'];			
			$sGroups['published'] = 1;
			$sGroups['default'] = $oldgroup['default'];
			//$sGroups['ordering'] = $oldgroup['list_order']; //There is no ordering in vm1
			
			//if(!class_exists('TableCategories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'categories.php');
			//$table = JTable::getInstance('shoppergroups', 'Table', array() );
			$table =$this->getTable('shoppergroups');
			if(!$this->_test){
				$sGroups = $table->bindChecknStore($sGroups);
		    	$errors = $table->getErrors();
				foreach($errors as $error){
					$this->setError($error);
					$ok = false;
				}
				$oldtoNewShoppergroups[$oldgroup['shopper_group_id']] = $sGroups['virtuemart_shoppergroup_id'];
				unset($sGroups['virtuemart_shoppergroup_id']);
			} else {
				$oldtoNewShoppergroups[$oldgroup['shopper_group_id']] = $oldgroup['shopper_group_id'];
			}
			
		}

		$this->_oldToNew->shoppergroups = $oldtoNewShoppergroups;
		
	}
	
    private function portUsers(){

	//$model = $this->getModel('updatesMigration');
		
	//Lets load all users from the joomla hmm or vm? VM1 users does NOT exist
	$ok= true;

	$q ='SELECT * FROM #__users AS `p`
	LEFT OUTER JOIN #__vm_user_info ON #__vm_user_info.user_id = `p`.id 
	LEFT OUTER JOIN #__vm_shopper_vendor_xref ON #__vm_shopper_vendor_xref.user_id = `p`.id 
	LEFT OUTER JOIN #__vm_auth_user_group ON #__vm_auth_user_group.user_id = `p`.id
	LEFT OUTER JOIN #__vm_auth_group ON #__vm_auth_group.group_id = #__vm_auth_user_group.group_id ';
	$this->_db->setQuery($q);
	$oldUsers = $this->_db->loadAssocList();
	if(empty($oldUsers)) $this->_app->enqueueMessage('_productPorter '.$this->_db->getErrorMsg() );
	dump($oldUsers,'my VM1 Users');

	if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
	$userModel = new VirtueMartModelUser();

	foreach($oldUsers as $user){
			
	    $user['virtuemart_country_id'] = $this->getCountryIdByCode($user['country']);
	    $user['virtuemart_state_id'] = $this->getCountryIdByCode($user['state']);
	    $user['virtuemart_shoppergroups_id'] = $this->_oldToNew->shoppergroups[$user['shopper_group_id']];
	
//	    dump($user['virtuemart_shoppergroups_id'],'the vm1shoppergroupid '.$user['shopper_group_id'].' for vm2 ');

		//Solution takes vm1 original values, but is not tested (does not set mainvendor)
		//if(!empty($user['group_name'])){
		//    $user['perms'] = $user['group_name'];
		//    
		//} else {
	    if($user['gid']==25){
		    $user['perms'] = 'admin';
		    $user['user_is_vendor'] = 1;
	    } elseif($user['gid']==24){
		    $user['perms'] = 'storeadmin';
	    } else {
		    $user['perms'] = 'shopper';
	    }
		//}

	    $user['virtuemart_user_id'] = $user['id'];	
	    $userModel->setUserId($user['id']);
		dump($user,'my VM1 User to save');
	    //Save the VM user stuff
	    if(!$userModel->saveUserData($user)){
		$userModel->setError(JText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA')  );
		JError::raiseWarning('', JText::_('COM_VIRTUEMART_RAISEWARNING_NOT_ABLE_TO_SAVE_USER_DATA'));
	    }

	    if (!$userModel->storeAddress($user)) {
		$userModel->setError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
	    }

	    $userModel ->storeVendorData($user);
	    //dump($user,'my VM1 Users');
	    $errors = $userModel->getErrors();
	    foreach($errors as $error){
		$this->_app->enqueueMessage($error);
	    }

	    dump($errors,'my VM1 Users $errors');
	}

	//adresses
	$q = 'SELECT * FROM #__vm_user_info WHERE `address_type` = "ST" ';
	$this->_db->setQuery($q);
	$oldUsersAddresses = $this->_db->loadAssocList();
	foreach($oldUsersAddresses as $oldUsersAddi){
	    if (!$userModel->storeAddress($oldUsersAddi)) {
		$userModel->setError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
	    }
	    $errors = $userModel->getErrors();
	    foreach($errors as $error){
		$this->_app->enqueueMessage('Migration: '.$error);
	    }
	}
	dump($oldUsersAddresses,'my VM1 User addresses');

	return $ok;
    }
		
    private function portCategories(){
		
		$ok = true;

		$q ='SELECT * FROM #__vm_category';
		$this->_db->setQuery($q);
		$oldCategories = $this->_db->loadAssocList();
		
		//$this->_app->enqueueMessage($this->_db->getQuery());
		//dump($oldCategories,'_categoryPorter $oldCategories');

		$oldtonewCats = array();
		
		$category = array();
		foreach($oldCategories as $oldcategory){
			$category = null;
			$category = array();
			//$category['virtuemart_category_id'] = $oldcategory['category_id'];
			$category['virtuemart_vendor_id'] = $oldcategory['vendor_id'];
			$category['category_name'] = $oldcategory['category_name'];
			
			$category['category_description'] = $oldcategory['category_description'];			
			$category['published'] = $oldcategory['category_publish']=='Y'? 1:0;
			$category['created_on'] = $oldcategory['cdate'];
			$category['modified_on'] = $oldcategory['mdate'];
			$category['category_layout'] = $oldcategory['category_browsepage'];
			$category['category_product_layout'] = $oldcategory['category_flypage'];
		//	$category[''] = $oldcategory['products_per_row']; //now done by the layout
			$category['ordering'] = $oldcategory['list_order'];
			
			if(!class_exists('TableCategories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'categories.php');
			$table = JTable::getInstance('categories', 'Table', array() );

			if(!$this->_test){
				$category = $table->bindChecknStore($category);
		    	$errors = $table->getErrors();
				foreach($errors as $error){
					$this->setError($error);
					$ok = false;
				}
				//dump($category,'hmpf my data');
				//dump($oldcategory['category_id'],'hmpf $oldcategory["category_id"]');
				$oldtonewCats[$oldcategory['category_id']] = $category['virtuemart_category_id'];
				unset($category['virtuemart_category_id']);
			} else {
				$oldtonewCats[$oldcategory['category_id']] = $oldcategory['category_id'];
			}
			
		}

		$this->_oldToNew->cats = $oldtonewCats;
		dump($this->_oldToNew->cats,'hmpf my $this->_oldToNew->cats');
		$q ='SELECT * FROM #__vm_category_xref ';
		$this->_db->setQuery($q);
		$oldCategoriesX = $this->_db->loadAssocList();
		//dump($oldCategoriesX,'_categoryPorter $oldCategoriesX');
		
		$category = array();
		$new_id = 0;
		if(!empty($oldCategoriesX)){
			foreach($oldCategoriesX as $oldcategoryX){
				$new_id = $this->_oldToNew->cats[$oldcategoryX['category_parent_id']];
				$category['category_parent_id'] = $new_id;
				
				$new_id = $this->_oldToNew->cats[$oldcategoryX['category_child_id']];
				$category['category_child_id'] = $new_id;
				
				if(!class_exists('TableCategory_categories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'category_categories.php');
				$table = JTable::getInstance('category_categories', 'Table', array() );
				
				//$table = $this->getTable('category_categories');
	
				if(!$this->_test){
					$xdata = $table->bindChecknStore($category);
			    	$errors = $table->getErrors();
					foreach($errors as $error){
						$this->setError($error);
						
						$ok = false;
					}
				} else {
					
				}
			}
			
			if($ok) $msg = 'Looks everything worked correct, migrated '.count($this->_oldToNew->cats).' categories ';
			else {
					$msg = 'Seems there was an error porting '.count($this->_oldToNew->cats).' categories ';
					foreach($this->getErrors() as $error){
						$msg .= '<br />'.$error;
					}
					
			}
			$this->_app -> enqueueMessage($msg);
			
			return $ok;
			
		} else {
			$this->_app -> enqueueMessage('No categories to import');
			return $ok;
		}
		

	}
	
	private function portManufacturerCategories(){
		
		$ok = true;

		$q ='SELECT * FROM #__vm_manufacturer_category';
		$this->_db->setQuery($q);
		$oldMfCategories = $this->_db->loadAssocList();
		
		//$this->_app->enqueueMessage($this->_db->getQuery());
		//dump($oldCategories,'_categoryPorter $oldCategories');
		if(!class_exists('TableManufacturercategories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'manufacturercategories.php');
		$oldtonewMfCats = array();
		
		$mfcategory = array();
		foreach($oldMfCategories as $oldmfcategory){
				
			//$category['virtuemart_category_id'] = $oldcategory['category_id'];
			 
			$mfcategory['mf_category_name'] = $oldmfcategory['mf_category_name'];
			$mfcategory['mf_category_desc'] = $oldmfcategory['mf_category_desc'];
		 
			$table = JTable::getInstance('manufacturercategories', 'Table', array() );

			if(!$this->_test){
				$mfcategory = $table->bindChecknStore($mfcategory);
				$errors = $table->getErrors();
				foreach($errors as $error){
					$this->setError($error);
					$ok = false;
				}
				$oldtonewMfCats[$oldmfcategory['mf_category_id']] = $mfcategory['virtuemart_manufacturercategories_id'];
				unset($mfcategory['virtuemart_manufacturercategories_id']);
			} else {
				$oldtonewMfCats[$oldmfcategory['category_id']] = $oldmfcategory['category_id'];
			}
			
		}

		$this->_oldToNew->mfcats = $oldtonewMfCats;
		
		if($ok) $msg = 'Looks everything worked correct, migrated '.count($this->_oldToNew->mfcats).' manufacturer categories ';
		else {
			$msg = 'Seems there was an error porting '.count($this->_oldToNew->mfcats).' manufacturer categories ';
			$msg .= $this->getErrors();
		}

		$this->_app -> enqueueMessage($msg);
		
		return $ok;
	}
        
	private function portManufacturers(){

		$ok= true;

		$q ='SELECT * FROM #__vm_manufacturer ';
		$this->_db->setQuery($q);
		$oldManus = $this->_db->loadAssocList();

		$oldtonewManus = array();
		
		$manu = array();
		foreach($oldManus as $oldmanu){
			
			$manu['mf_name'] = $oldmanu['mf_name'];
			$manu['mf_email'] = $oldmanu['mf_email'];
			$manu['mf_desc'] = $oldmanu['mf_desc'];
			$manu['virtuemart_manufacturercategories_id'] = $this->_oldToNew->mfcats[$oldmanu['mf_category_id']];
			$manu['mf_url'] = $oldmanu['mf_url'];
			$manu['published'] = 1;
				
			if(!class_exists('TableManufacturers')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'manufacturers.php');
			$table = JTable::getInstance('manufacturers', 'Table', array() );
			
			//$table = $this->getTable('manufacturers');

			if(!$this->_test){
				$manu = $table->bindChecknStore($manu);
		    	$errors = $table->getErrors();
				foreach($errors as $error){
					$this->setError($error);
					$ok = false;
				}
				$oldtonewManus[$oldmanu['manufacturer_id']] = $manu['virtuemart_manufacturer_id'];
                                unset($manu['virtuemart_manufacturer_id']);
			} else {
				$oldtonewManus[$oldmanu['manufacturer_id']] = $oldmanu['manufacturer_id'];
			}

                        $this->_oldToNew->manus = $oldtonewManus;
		}
		

		if($ok) $msg = 'Looks everything worked correct, migrated '.count($this->_oldToNew->manus).' manufacturers ';
		else {
                    $msg = 'Seems there was an error porting '.count($this->_oldToNew->manus).' manufacturers ';
                    $msg .= $this->getErrors();
                }
		$this->_app -> enqueueMessage($msg);
	}
	
	private function portProducts(){
		
		$ok= true;
		
		$q ='SELECT * FROM #__vm_product AS `p`
		LEFT OUTER JOIN #__vm_product_price ON #__vm_product_price.product_id = `p`.product_id 
		LEFT OUTER JOIN #__vm_product_category_xref ON #__vm_product_category_xref.product_id = `p`.product_id 
		LEFT OUTER JOIN #__vm_product_mf_xref ON #__vm_product_mf_xref.product_id = `p`.product_id '; 
		$this->_db->setQuery($q);
		$oldProducts = $this->_db->loadAssocList();
		if(empty($oldProducts)){
			 $this->_app->enqueueMessage('_productPorter '.$this->_db->getErrorMsg() );
			 return false;
		}
		dump($oldProducts,'_productPorter $oldProducts');
		if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'product.php');
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
 		$user = JFactory::getUser();
		
		dump($this->_oldToNew->cats,'$this->_oldToNew->cats');
 		//There are so many names the same, so we use the loaded array and manipulate it
 		foreach($oldProducts as $product){
 			
			$product['virtuemart_vendor_id'] = $product['vendor_id'];
			$product['virtuemart_manufacturer_id'] =  $this->_oldToNew->manus[$product['manufacturer_id']] ;
			
			//product has category_id and categories?
			if(!empty($this->_oldToNew->cats[$product['category_id']])){
				
				$product['virtuemart_category_id'] = $this->_oldToNew->cats[$product['category_id']];
				//This should be an array, or is it not in vm1? not cleared, may need extra foreach
				$product['categories'] = $this->_oldToNew->cats[$product['category_id']];
				
			}
			
			//dump($this->_oldToNew->cats[$product['category_id']],'hmmmmm $this->_oldToNew->cats[$product["category_id"]]?');
			$product['published'] = $product['product_publish']=='Y'? 1:0;
			 
			$product['product_price_quantity_start'] = $product['price_quantity_start'];
			$product['product_price_quantity_end'] = $product['price_quantity_end'];
				
			$product['created_on'] = $this->_changeToStamp($product['cdate']);
			$product['modified_on'] = $this->_changeToStamp($product['mdate']); //we could remove this to set modified_on today
			$product['product_available_date'] = $this->_changeToStamp($product['product_available_date']);
			
			//$product['created_by'] = $user->id;
			//$product['modified_by'] = $user->id;
			
			$product['product_currency'] = $this->_ensureUsingCurrencyId($product['product_currency']);
			
			//Unsolved Here we must look for the url product_full_image and check which media has the same 
			// full_image url
			//$product['virtuemart_media_id'] =
			
			$productModel->store($product);
		}

		//dump($oldProducts,'$oldProducts');
		return $ok;
	}
	
	private function _changeToStamp(){
		
		$date = JFactory::getDate($data['publish_up']);
		return $date->toMySQL();
	}
	
	private function _ensureUsingCurrencyId($curr){

		$this->_db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_currency_id` FROM `#__virtuemart_currencies` WHERE `currency_code_3`="'.$this->_db->getEscaped($curr).'"';
		$this->_db->setQuery($q);
		$currInt = $this->_db->loadResult();
		if(empty($currInt)){
			JError::raiseWarning(E_WARNING,'Attention, couldnt find currency id in the table for id = '.$curr);
		}
		return $currInt;
	}

	/**
	 * Gets the virtuemart_country_id by a country 2 or 3 code
	 * 
	 * @author Max Milbers
	 * @param string $name Country 3 or Country 2 code (example US for United States)
	 * return int virtuemart_country_id
	 */
	private function getCountryIdByCode ($name){
		if (empty($name)) {
			return 0;
		}

		if(strlen($name)==2){
			$countryCode = 'country_2_code';
		} else {
			$countryCode = 'country_3_code';
		}
		
		$q = 'SELECT `virtuemart_country_id` FROM `#__virtuemart_countries` 
				WHERE `'.$countryCode.'` = "'.$this->_db->getEscaped($name).'" ';
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
	private function getStateIdByCode ($name){
		if (empty($name)) {
			return 0;
		}

		if(strlen($name)==2){
			$code = 'country_2_code';
		} else {
			$code = 'country_3_code';
		}
		
		$q = 'SELECT `virtuemart_state_id` FROM `#__virtuemart_states` 
				WHERE `'.$code.'` = "'.$this->_db->getEscaped($name).'" ';
		$this->_db->setQuery($q);
		
		return $this->_db->loadResult();

	}

	/**
	 * Helper function, was used to determine the difference of an loaded array (from vm19
	 * and a loaded object of vm2
	 */
	
	private function showVmDiff(){
		
		//$product = $productModel->getProduct(0);
		
		$productK = array();
		$attribsImage = get_object_vars($product);dump($attribsImage,'$attribsImage');
		foreach($attribsImage as $k=>$v){
			$productK[] = $k;
		}
		
		$oldproductK = array();
		foreach($oldProducts[0] as $k => $v){
			$oldproductK[] = $k;
		}
		dump($productK,'$productK');
		dump($oldproductK,'$oldproductK');
		$notSame = array_diff($productK,$oldproductK);
		$names = '';
		foreach($notSame as $name){
			$names .= $name.' ';
		}
		$this->_app->enqueueMessage('_productPorter  array_intersect '.$names );

		$notSame = array_diff($oldproductK,$productK);
		$names = '';
		foreach($notSame as $name){
			$names .= $name.' ';
		}
		$this->_app->enqueueMessage('_productPorter  ViceVERSA array_intersect '.$names );		
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

		foreach ($result as $item){

//			$item->virtuemart_currency_id = 0;
			$item->currency_exchange_rate = 0;
			$item->published = 1;
			$item->shared = 1;
			$item->virtuemart_vendor_id = 1;

			$style = explode('|',$item->_display_style);

			$item->currency_nbDecimal = $style[2];
			$item->currency_decimal_symbol = $style[3];
			$item->currency_thousands = $style[4];
			$item->currency_positive_style = $style[5];
			$item->currency_negative_style = $style[6];

			$db->insertObject('#__virtuemart_currencies', $item);
		}

		$this->setRedirect($this->redirectPath);
	}
}
