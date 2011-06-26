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
		
		$this->_app = JFactory::getApplication();
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
		$model->setDangerousToolsOff();

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
	
	function migrateVmOneUsers(){
		
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');
		
		//Lets load all users from the joomla hmm or vm?
		$ok= true;
		
		$q ='SELECT * FROM #__vm_users AS `p`
		LEFT OUTER JOIN #__vm_product_price ON #__vm_product_price.product_id = `p`.product_id 
		LEFT OUTER JOIN #__vm_product_category_xref ON #__vm_product_category_xref.product_id = `p`.product_id 
		LEFT OUTER JOIN #__vm_product_mf_xref ON #__vm_product_mf_xref.product_id = `p`.product_id '; 
		$this->_db->setQuery($q);
		$oldProducts = $this->_db->loadAssocList();
		if(empty($oldProducts)) $this->_app->enqueueMessage('_productPorter '.$this->_db->getErrorMsg() );
		
		
		$this->setRedirect($this->redirectPath,$msg);
	}
	
	function migrateVmOneOrders(){
		
		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
		
		
		$this->setRedirect($this->redirectPath,$msg);
	}
	
	function migrateVmOneProducts(){

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );
		$this->checkPermissionForTools();
			
		//Attention ! only for developing 
		//$updatesMigrationModel = $this->getModel('updatesMigration');
		//$updatesMigrationModel->removeAllVMData();

		if(!VmConfig::get('dangeroustools',true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath,$msg);
			return false;
		}
		
		$this->_db = JFactory::getDBO();
		
		$this->_test = false;
		
		//Object to hold old against new ids. We wanna port as when it setup fresh, so no importing of old ids!
		$this->_oldToNew = new stdClass();
		
		//$this->_portMedia();
		
		$this->_categoryPorter();
		
        $this->_manufacturerCategoryPorter();
		$this->_portManufacturer();
		
		//Now we have the new ids for the medias,categories,taxes,discounts and manufacturers now lets port the products
		$this->_productPorter();
		
		//dump($this->_oldToNew,'$this->_oldToNew');
		$msg = 'Migration worked smoothly and finished';
		$this->setRedirect($this->redirectPath,$msg);
	}
	
	private function _productPorter(){
		
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
		
		if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'product.php');
		$productModel = new VirtueMartModelProduct();
		//$product = $productModel->getProduct(0);
		
/*		$productK = array();
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
		$this->_app->enqueueMessage('_productPorter  ViceVERSA array_intersect '.$names );*/

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
		
		
 		//There are so many names the same, so we use the loaded array and manipulate it
 		foreach($oldProducts as $product){
 			
			$product['virtuemart_vendor_id'] = $product['vendor_id'];
			$product['virtuemart_manufacturer_id'] =  $this->_oldToNew->manus[$product['manufacturer_id']] ;
			
			//product has category_id and categories?
			$product['virtuemart_category_id'] = $this->_oldToNew->cats[$product['category_id']];
			//This should be an array, or is it not in vm1? not cleared, may need extra foreach
			$product['categories'] = $this->_oldToNew->cats[$product['category_id']];
			
			$product['published'] = $product['product_publish']=='Y'? 1:0;
			 
			$product['product_price_quantity_start'] = $product['price_quantity_start'];
			$product['product_price_quantity_end'] = $product['price_quantity_end'];
				
			$product['created_on'] = $this->_changeToStamp($product['cdate']);
			$product['modified_on'] = $this->_changeToStamp($product['mdate']); //we could remove this to set modified_on today
			$product['product_available_date'] = $this->_changeToStamp($product['product_available_date']);
			
			$product['created_by'] = $this->_changeToStamp($product['cdate']);
			$product['modified_by'] = $this->_changeToStamp($product['cdate']);
			
			$product['product_currency'] = $this->_ensureUsingCurrencyId($product['product_currency']);
			//Unsolved Here we must look for the url product_full_image and check which media has the same 
			// full_image
			//$product['virtuemart_media_id'] =
			
			$productModel->store($product);

/*			$data = null;
			$data = array();
			
			//$data[''] = $product['product_id'];
		$data['virtuemart_vendor_id'] = $product['vendor_id'];
			$data['product_parent_id'] = $product['product_parent_id'];
/*			$data[''] = $product['product_s_desc'];
			$data[''] = $product['product_desc'];
			
			$data[''] = $product['product_thumb_image'];	//Write function to get id
			$data[''] = $product['product_full_image'];
			
			$data[''] = $product['product_publish'];
			$data[''] = $product['product_weight'];
			$data[''] = $product['product_weight_uom'];
			$data[''] = $product['product_length'];
			$data[''] = $product['product_width'];
			$data[''] = $product['product_height'];
			$data[''] = $product['product_lwh_uom'];
			$data[''] = $product['product_url'];
			$data[''] = $product['product_in_stock'];
			
			$data[''] = $product['product_available_date'];	//Write function to change dateformat
			
			$data[''] = $product['product_availability'];
			$data[''] = $product['product_special'];
			$data[''] = $product['product_discount_id'];
			$data[''] = $product['ship_code_id'];
			$data[''] = $product['cdate'];
			$data[''] = $product['mdate'];
			$data[''] = $product['product_name'];
			$data[''] = $product['product_sales'];
			$data[''] = $product['attribute'];
			$data[''] = $product['custom_attribute'];
			$data[''] = $product['product_tax_id'];
			$data[''] = $product['product_unit'];
			$data[''] = $product['product_packaging'];
			$data[''] = $product['child_options'];
			$data[''] = $product['quantity_options'];
			$data[''] = $product['child_option_ids'];
			$data[''] = $product['product_order_levels'];
			//$data[''] = $product['product_price_id'];
			$data[''] = $product['product_price'];
			$data[''] = $product['product_currency'];
			$data[''] = $product['product_price_vdate'];
			$data[''] = $product['product_price_edate'];
			$data[''] = $product['shopper_group_id'];
			$data[''] = $product['price_quantity_start'];
			$data[''] = $product['price_quantity_end'];
			$data[''] = $product['category_id'];
			$data[''] = $product['product_list'];
			$data[''] = $product['manufacturer_id'];*/
		}

		//dump($oldProducts,'$oldProducts');
		return $ok;
	}
	
	private function _categoryPorter(){
		
		$ok = true;

		$q ='SELECT * FROM #__vm_category';
		$this->_db->setQuery($q);
		$oldCategories = $this->_db->loadAssocList();
		
		$this->_app->enqueueMessage($this->_db->getQuery());
		//dump($oldCategories,'_categoryPorter $oldCategories');

		$oldtonewCats = array();
		
		$category = array();
		foreach($oldCategories as $oldcategory){
				
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
				$oldtonewCats[$oldcategory['category_id']] = $category['virtuemart_category_id'];
				unset($category['virtuemart_category_id']);
			} else {
				$oldtonewCats[$oldcategory['category_id']] = $oldcategory['category_id'];
			}
			
		}

		$this->_oldToNew->cats = $oldtonewCats;
		
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
					$msg .= $this->getErrors();
			}
			$this->_app -> enqueueMessage($msg);
			
			return $ok;
			
		} else {
			$this->_app -> enqueueMessage('No categories to import');
			return $ok;
		}
		

	}
	
	private function _manufacturerCategoryPorter(){
		
		$ok = true;

		$q ='SELECT * FROM #__vm_manufacturer_category';
		$this->_db->setQuery($q);
		$oldMfCategories = $this->_db->loadAssocList();
		
		$this->_app->enqueueMessage($this->_db->getQuery());
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
        
	private function _portManufacturer(){

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
		$count = $this->portMediaByType(VmConfig::get('media_product_path'),'product');
		$countTotal +=  $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));
		
		$url = VmConfig::get('media_category_path');
		$count = $this->portMediaByType(VmConfig::get('media_category_path'),'category');
		$countTotal += $count;
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));
		$type = 'category';
		
		$url = VmConfig::get('media_manufacturer_path');
		$countTotal += $count;
		$count = $this->portMediaByType(VmConfig::get('media_manufacturer_path'),'manufacturer');
		$this->_app->enqueueMessage(JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT',$count, $type, $url));	
		$type = 'manufacturer';
		
		//$this->portMediaByType(VmConfig::get('media_path'),'shop');
		
		$msg = JText::sprintf('COM_VIRTUEMART_UPDATE_PORT_MEDIA_RESULT_FINISH',$countTotal);	
		$this->setRedirect($this->redirectPath,$msg);
		
		return $ok;
	}
	
	function portMediaByType($url,$type){
		
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
				if ($handle = opendir($dir)) {
			    	while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
							$info = pathinfo($file); //dump($info,'pathinfo');
							//We port all type of media, regardless the extension
							if ((filetype($dir.DS.$file) == 'file') && !in_array($file,$knownNames)) {
								$filesInDir[] = $file;
						    } else{
						    	if(filetype($dir.DS.$file)== 'dir' && $file!='resized'){
						    		$subfoldersInDir[] = $dir.DS.$file;
								}	
							}
						}
				    }
				}
			}
			$foldersInDir = $subfoldersInDir;
			dump($foldersInDir);
		}

		
		$i=0;
		foreach($filesInDir as $filename){
			
			$data = array(	'file_title'=>$filename,
							'virtuemart_vendor_id'=>1,
							'file_description'=>$filename,
							'file_meta'=>$filename,
							'file_url'=>$url.$filename,
							//'file_url_thumb'=>$url.'resized/'.$filename,
							'media_published'=>1
							);
			if($type=='product')$data['file_is_product_image'] = 1;
			$this->mediaModel->setId(0);
			$success = $this->mediaModel->store($data,$type);
			if($success) $i++;
		}
		
		return $i;

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
