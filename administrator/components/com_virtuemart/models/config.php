<?php
/**
 *
 * Data module for shop configuration
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @author RickG
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
 * Model class for shop configuration
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @author RickG
 */
class VirtueMartModelConfig extends JModel {


	/**
	 * Retrieve a list of layouts from the default and choosen templates directory.
	 *
	 * @author Max Milbers
	 * @param name of the view
	 * @return object List of flypage objects
	 */
	function getLayoutList($view) {

		$dirs[] = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.$view.DS.'tmpl';

		//This does not work, joomla takes only overrides of their standard template
		//		$tplpath = VmConfig::get('vmtemplate',0);
		//So we lookf for template overrides in the joomla standard template

		//This method does not work, we get the Template of the backend
		//$app = JFactory::getApplication('site');
		//$tplpath = $app->getTemplate();vmdebug('template',$tplpath);
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$q = 'SELECT `template` FROM `#__template_styles` WHERE `client_id` ="0" AND `home`="1" ';
		} else {
			$q = 'SELECT `template` FROM `#__templates_menu` WHERE `client_id` ="0" ';
		}

		$db = JFactory::getDBO();
		$db->setQuery($q);

		$tplnames = $db->loadResult();
		if($tplnames){
			if(is_dir(JPATH_ROOT.DS.'templates'.DS.$tplnames.DS.'html'.DS.'com_virtuemart'.DS.$view)){
				$dirs[] = JPATH_ROOT.DS.'templates'.DS.$tplnames.DS.'html'.DS.'com_virtuemart'.DS.$view;
			}
		}

		$result = '';
		$alreadyAddedFile = array();
		foreach($dirs as $dir){
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					//Handling directly for extension is much cleaner
					$path_info = pathinfo($file);
					if(empty($path_info)){
						vmError('Attention file '.$file.' has no extension in view '.$view.' and directory '.$dir);
					}
					if ($path_info['extension'] == 'php' && !in_array($file,$alreadyAddedFile)) {
						$alreadyAddedFile[] = $file;
						//There is nothing to translate here
						$result[] = JHTML::_('select.option', $file, $path_info['filename']);
					}
				}
			}
		}
		return $result;
	}


	/**
	 * Retrieve a list of possible images to be used for the 'no image' image.
	 *
	 * @author RickG
	 * @author Max Milbers
	 * @return object List of image objects
	 */
	function getNoImageList() {

		//TODO set config value here
		$dirs[] = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'vmgeneral';

		$tplpath = VmConfig::get('vmtemplate',0);
		if($tplpath){
			if(is_dir(JPATH_ROOT.DS.'templates'.DS.$tplpath.DS.'images'.DS.'vmgeneral')){
				$dirs[] = JPATH_ROOT.DS.'templates'.DS.$tplpath.DS.'images'.DS.'vmgeneral';
			}
		}

		$result = '';

		foreach($dirs as $dir){
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
						if (filetype($dir.DS.$file) != 'dir') {
							$result[] = JHTML::_('select.option', $file, JText::_(str_replace('.php', '', $file)));
						}
					}
				}
			}
		}
		return $result;
	}


	/**
	 * Retrieve a list of currency converter modules from the plugins directory.
	 *
	 * @author RickG
	 * @return object List of theme objects
	 */
	function getCurrencyConverterList() {
		$dir = JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter';
		$result = '';

		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != '.svn') {
					$info = pathinfo($file);
					if ((filetype($dir.DS.$file) == 'file') && ($info['extension'] == 'php')) {
						$result[] = JHTML::_('select.option', $file, JText::_($file));
					}
				}
			}
		}

		return $result;
	}


	/**
	 * Retrieve a list of modules.
	 *
	 * @author RickG
	 * @return object List of module objects
	 */
	function getModuleList() {
		$db = JFactory::getDBO();

		$query = 'SELECT `module_id`, `module_name` FROM `#__virtuemart_modules` ';
		$query .= 'ORDER BY `module_id`';
		$db->setQuery($query);

		return $db->loadObjectList();
	}


	/**
	 * Retrieve a list of Joomla content items.
	 *
	 * @author RickG
	 * @return object List of content objects
	 */
	function getContentLinks() {
		$db = JFactory::getDBO();

		$query = 'SELECT `id`, CONCAT(`title`, " (", `title_alias`, ")") AS text FROM `#__content` ';
		$query .= 'ORDER BY `id`';
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Retrieve a list of Order By Fields
	 *
	 * @author Kohl Patrick
	 * @return array of order list
	 */
	function getOrderByFields( $orderByChecked ) {
// 		vmdebug('$orderByChecked',$orderByChecked);
// 		if (empty ($orderByChecked)) $orderByChecked = array('product_sku','category_name','mf_name','product_name');
// 		else if (!is_array($orderByChecked)) $orderByChecked = array($orderByChecked);
		$orderByFields = new stdClass();
		$orderByFields->checkbox ='<div  class="threecols"><ul>';

		$orderByFieldsArray = array('p.virtuemart_product_id', 'p.product_sku','pp.product_price','c.category_name','c.category_description',
		'm.mf_name', 'p.product_s_desc', 'p.product_desc', 'p.product_weight', 'p.product_weight_uom', 'p.product_length', 'p.product_width',
		'p.product_height', 'p.product_lwh_uom', 'p.product_in_stock', 'p.low_stock_notification', 'p.product_available_date',
		'p.product_availability', 'p.product_special', 'ship_code_id', 'p.created_on', 'p.modified_on', 'p.product_name', 'p.product_sales',
		'p.product_unit', 'p.product_packaging', 'p.intnotes', 'p.metadesc', 'p.metakey', 'p.metarobot', 'p.metaauthor');
		foreach ($orderByFieldsArray as $key => $field ) {
			if (!empty($orderByChecked) && in_array($field, $orderByChecked) ) {
				$checked = 'checked="checked"';
			}
			else {
				$checked = '';
			}

			$fieldWithoutPrefix = $field;
			$dotps = strrpos($fieldWithoutPrefix, '.');
			if($dotps!==false){
				$prefix = substr($field, 0,$dotps+1);
				$fieldWithoutPrefix = substr($field, $dotps+1);
			}

			$text = JText::_('COM_VIRTUEMART_'.strtoupper($fieldWithoutPrefix)) ;
			$orderByFields->select[] =  JHTML::_('select.option', $field, $text) ;
			$orderByFields->checkbox.= '<li><label for="' .$field.$key. '">' .$text. '</label><input type="checkbox" id="' .$field.$key. '" name="browse_orderby_fields[]" value="' .$field. '" ' .$checked. ' /></li>';


		}
		$orderByFields->checkbox .='</ul></div>';
		return $orderByFields;
	}
	/**
	 * Retrieve a list of search Fields
	 *
	 * @author Kohl Patrick
	 * @return array of order list
	 */
	function getSearchFields( $searchChecked ) {

// 		if (empty ($searchChecked)) $searchChecked = array('p.product_sku','c.category_name','c.category_description','m.mf_name','p.product_name', 'p.product_s_desc');
// 		else if (!is_array($searchChecked))
		if (empty ($searchChecked)){
			$searchChecked = array();
		} else if (!is_array($searchChecked)) {
			$searchChecked = array($searchChecked);
		}
		$searchFields ='<div  class="threecols"><ul>';
		$searchFieldsArray = array('p.product_sku','pp.product_price','c.category_name','c.category_description','m.mf_name','p.product_name',
		'p.product_s_desc', 'p.product_desc', 'p.product_weight', 'p.product_weight_uom', 'p.product_length', 'p.product_width', 'p.product_height',
		'p.product_lwh_uom', 'p.product_in_stock', 'p.low_stock_notification', 'p.product_available_date', 'p.product_availability', 'p.product_special',
		'ship_code_id', 'p.created_on', 'p.modified_on',  'p.product_sales','p.product_unit', 'p.product_packaging', 'p.intnotes',
		'p.metadesc', 'p.metakey', 'p.metarobot', 'p.metaauthor');
		foreach ($searchFieldsArray as $key => $field ) {
			if (in_array($field, $searchChecked) ) {
				$checked = 'checked="checked"';
			}
			else {
				$checked = '';
			}

			$fieldWithoutPrefix = $field;
			$dotps = strrpos($fieldWithoutPrefix, '.');
			if($dotps!==false){
				$prefix = substr($field, 0,$dotps+1);
				$fieldWithoutPrefix = substr($field, $dotps+1);
			}

			$text = JText::_('COM_VIRTUEMART_'.strtoupper($fieldWithoutPrefix)) ;

			$searchFields.= '<li><label for="' .$field.$key. '">' .$text. '</label><input type="checkbox" id="' .$field.$key. '" name="browse_search_fields[]" value="' .$field. '" ' .$checked. ' /></li>';
		}
		$searchFields .='</ul></div>';
		return $searchFields;
	}


	/**
	 * Save the configuration record
	 *
	 * @author RickG
	 * @return boolean True is successful, false otherwise
	 */
	function store($data) {

		JRequest::checkToken() or jexit( 'Invalid Token, in store config');

		//ATM we want to ensure that only one config is used
		$config = VmConfig::loadConfig();
		$config->setParams($data);

		$confData = array();
		$query = 'SELECT * FROM `#__virtuemart_configs`';
		$this->_db->setQuery($query);
		if($this->_db->loadResult()){
			$confData['virtuemart_config_id'] = 1;
		} else {
			$confData['virtuemart_config_id'] = 0;
		}

		$urls = array('assets_general_path','media_category_path','media_product_path','media_manufacturer_path','media_vendor_path');
		foreach($urls as $urlkey){
				$url = $config->get($urlkey);
				$length = strlen($url);
				if(strrpos($url,'/')!=($length-1)){
					$config->set($urlkey,$url.'/');
					vmInfo('Corrected media path '.$urlkey.' added missing /');
				}
		}

		$confData['config'] = $config->toString();

		$confTable = $this->getTable('configs');
		if (!$confTable->bindChecknStore($confData)) {
			$this->setError($confTable->getError());
		}

		// Load the newly saved values into the session.
		VmConfig::loadConfig(true);

		return true;
	}

	/**
	 * Dangerous tools get disabled after execution an operation which needed that rights.
	 * This is the function actually doing it.
	 *
	 * @author Max Milbers
	 */
	function setDangerousToolsOff(){

		VmConfig::loadConfig(true);
		$dangerousTools = VmConfig::readConfigFile(true);

		if( $dangerousTools){
			$uri = JFactory::getURI();
			$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
			$lang = JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_STILL_ENABLED',JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link);
			VmInfo($lang);
		} else {
			$data['dangeroustools'] = 0;
			$data['virtuemart_config_id'] = 1;
			$this->store($data);
		}

	}

	public function remove() {

		$table = $this->getTable('configs');

		if (!$table->delete(1)) {
			vmError(get_class( $this ).'::remove '.$id.' '.$table->getError(),'Cannot delete config');
			return false;
		}

		return true;
	}

	/**
	 * This function deletes a config stored in the database
	 *
	 * @author Max Milbers
	 */
	function deleteConfig(){

		if($this->remove()){
			return VmConfig::loadConfig(true);
		} else {
			return false;
		}




	}

}

//pure php no closing tag