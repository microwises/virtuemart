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
	 * Akeeba release system tasks
	 * Update
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
     */
    function installSampleData() {

		$model = $this->getModel('updatesMigration');

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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

		$model = $this->getModel('updatesMigration');
		$msg = $model->integrateJoomlaUsers();

		$this->setRedirect($this->redirectPath, $msg);
    }


    /**
     *
     * @author Max Milbers
     */
    function setStoreOwner(){

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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

    	if(VmConfig::get('dangeroustools',false)){
 			$model = $this->getModel('updatesMigration');
 			$data = JRequest::get('get');
			JRequest::setVar($data['token'],'1','post');
			JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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

    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMTABlES_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
    		$model = $this->getModel('updatesMigration');
    		$data = JRequest::get('get');
			JRequest::setVar($data['token'],'1','post');
			JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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

    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_VMDATA_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
			$model = $this->getModel('updatesMigration');
			$data = JRequest::get('get');
			JRequest::setVar($data['token'],'1','post');
			JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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

    	$msg = JText::_('COM_VIRTUEMART_SYSTEM_ALLVMDATA_DELETED');
    	if(VmConfig::get('dangeroustools',false)){
    		$data = JRequest::get('get');
			JRequest::setVar($data['token'],'1','post');
			JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

 			$this -> installer -> populateVmDatabase("delete_essential.sql");
			$this -> installer -> populateVmDatabase("delete_data.sql");
			$this->setDangerousToolsOff();
    	} else {
			 $msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath,$msg);
    }


    function deleteRestorable() {

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

		if(VmConfig::get('dangeroustools',true)){

			$data = JRequest::get('get');
			JRequest::setVar($data['token'],'1','post');
			JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

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
	
	function migrateVmOneProducts(){

		$data = JRequest::get('get');
		JRequest::setVar($data['token'],'1','post');
		JRequest::checkToken() or jexit( 'Invalid Token, in '.JRequest::getWord('task') );

		if(!VmConfig::get('dangeroustools',true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath,$msg);
			return false;
		}
		
		$this->_app = JFactory::getApplication(); dump($this->_app,'app');
		$this->_db = JFactory::getDBO();
		
		$this->_test = false;
		
		//Object to hold old against new ids. We wanna port as when it setup fresh, so no importing of old ids!
		$this->_oldToNew = new stdClass();
		
		$this->_portMedia();
		
		$this->_categoryPorter();

		$this->_portManufacturer();
		
		//Now we have the new ids for the medias,categories,taxes,discounts and manufacturers now lets port the products
		$this->_productPorter();
		
		dump($this->_oldToNew,'$this->_oldToNew');
		$msg = 'Migration worked smoothed and finished';
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
		if(empty($oldProducts)) $this->_app->enqueueMessage('_productPorter '.$this->_db->getErrorMsg() );
		dump($oldProducts,'$oldProducts');
		return $ok;
	}
	
	private function _categoryPorter(){
		
		$ok = true;

		$q ='SELECT * FROM #__vm_category';
		$this->_db->setQuery($q);
		$oldCategories = $this->_db->loadAssocList();
		
		$this->_app->enqueueMessage($this->_db->getQuery());
		dump($oldCategories,'_categoryPorter $oldCategories');

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
		dump($oldCategoriesX,'_categoryPorter $oldCategoriesX');
		
		$category = array();
		$new_id = 0;		
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
		else $msg = 'Seems there was an error porting '.count($this->_oldToNew->cats).' categories ';
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
			$manu['virtuemart_manufacturercategories_id'] = $oldmanu['mf_category_id'];
			$manu['mf_url'] = $oldmanu['mf_url'];
			$manu['published'] = 1;
				
			if(!class_exists('TableManufacturers')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'manufacturers.php');
			$table = JTable::getInstance('manufacturers', 'Table', array() );
			
			//$table = $this->getTable('manufacturers');

			if(!$this->_test){
				$category = $table->bindChecknStore($category);
		    	$errors = $table->getErrors();
				foreach($errors as $error){
					$this->setError($error);
					$ok = false;
				}
				$oldtonewManus[$oldmanu['manufacturer_id']] = $category['virtuemart_manufacturer_id'];
			} else {
				$oldtonewManus[$oldmanu['manufacturer_id']] = $oldmanu['manufacturer_id'];
			}					
		}
		
		return $ok;
	}
	
	private function _portMedia(){
		
		$ok = true;
		
		return $ok;
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
