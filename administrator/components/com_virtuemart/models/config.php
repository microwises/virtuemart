<?php
/**
*
* Data module for shop configuration
*
* @package	VirtueMart
* @subpackage Config
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
 * @author RickG
 */
class VirtueMartModelConfig extends JModel {

    /**
     * Retrieve a list of themes from the themes directory.
     *
     * @author RickG
     * @return object List of theme objects
     */
    function getThemeList() {
//		$dir = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'themes';
//		$result = '';
//	
//		if ($handle = opendir($dir)) {
//		    while (false !== ($file = readdir($handle))) {
//				if ($file != "." && $file != ".." && $file != '.svn') {
//				    if (filetype($dir.DS.$file) == 'dir') {
//					$result[] = JHTML::_('select.option', $file, JText::_($file));
//				    }
//				}
//		    }
//		}
//	
//		return $result;
    }


    /**
     * Retrieve a list of category templates from the templates directory.
     *
     * @author RickG
     * @return object List of template objects
     */
    function getTemplateList() {
//	$dir = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'themes';
//	$dir .= DS.VmConfig::get('theme').DS.'templates'.DS.'browse';
//	$result = '';
//
//	if ($handle = opendir($dir)) {
//	    while (false !== ($file = readdir($handle))) {
//		if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
//		    if (filetype($dir.DS.$file) != 'dir') {
//			$result[] = JHTML::_('select.option', $file, JText::_(str_replace('.php', '', $file)));
//		    }
//		}
//	    }
//	}
//	$result[] = JHTML::_('select.option', 'managed', JText::_('managed'));
//
//	return $result;
    }


    /**
     * Retrieve a list of flypages from the templates directory.
     *
     * @author RickG
     * @return object List of flypage objects
     */
    function getFlypageList() {
//	$dir = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'themes';
//	$dir .= DS.VmConfig::get('theme').DS.'templates'.DS.'product_details';
//	$result = '';
//
//	if ($handle = opendir($dir)) {
//	    while (false !== ($file = readdir($handle))) {
//		if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
//		    if (filetype($dir.DS.$file) != 'dir') {
//			$result[] = JHTML::_('select.option', $file, JText::_(str_replace('.php', '', $file)));
//		    }
//		}
//	    }
//	}

//	return $result;
    }


    /**
     * Retrieve a list of possible images to be used for the 'no image' image.
     *
     * @author RickG
     * @return object List of image objects
     */
    function getNoImageList() {
//	$dir = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'themes';
//	$dir .= DS.VmConfig::get('theme').DS.'images';
//	$result = '';
//
//	if ($handle = opendir($dir)) {
//	    while (false !== ($file = readdir($handle))) {
//		if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
//		    if (filetype($dir.DS.$file) != 'dir') {
//			$result[] = JHTML::_('select.option', $file, JText::_(str_replace('.php', '', $file)));
//		    }
//		}
//	    }
//	}

//	return $result;
    }


    /**
     * Retrieve a list of currency converter modules from the plugins directory.
     *
     * @author RickG
     * @return object List of theme objects
     */
    function getCurrencyConverterList() {
	$dir = JPATH_COMPONENT_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter';
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
     * Retrieve a list of possible order statuses.
     *
     * @author RickG
     * @return object List of status objects
     */
    function getOrderStatusList() {
	$db = JFactory::getDBO();

	$query = 'SELECT `order_status_code`, `order_status_name` FROM `#__vm_order_status` ';
	$query .= ' ORDER BY `#__vm_order_status`.`order_status_name`';
	$db->setQuery($query);

	return $db->loadObjectList();
    }


    /**
     * Retrieve a list of modules.
     *
     * @author RickG
     * @return object List of module objects
     */
    function getModuleList() {
	$db = JFactory::getDBO();

	$query = 'SELECT `module_id`, `module_name` FROM `#__vm_module` ';
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
     * Retrieve the configuration record
     *
     * @author RickG
     * @return object A JParameter of the configuration
     */
    function getConfig() {
	$db = JFactory::getDBO();

	$query = "SELECT `config` FROM `#__vm_config` WHERE `config_id` = 1";
	$db->setQuery($query);
	$config = $db->loadResult();
	if ($config) {
	    $params = new JParameter($config);
	    
	    $media_category_path = $params->get('media_category_path') ;
		if(empty($media_category_path)){
			$params->set('media_category_path','images/stories/virtuemart/category/');	
		}
		$media_product_path = $params->get('media_product_path') ;
		if(empty($media_product_path)){
			$params->set('media_product_path','images/stories/virtuemart/product/');	
		}
	}
	else {
	    $params = new JParameter('');
	    $params->set('store_name', 'My Super Store');
	    $params->set('currency', 'EUR');

	    $q = "INSERT INTO #__vm_config (config) VALUES(".$db->Quote($params->toString()).")";
	    $db->setQuery($q);
	    $db->query();
	    echo $db->getErrorMsg();
	}

	return $params;
    }


    /**
     * Save the configuration record
     *
     * @author RickG
     * @return boolean True is successful, false otherwise
     */
    function store($data) {
	if ($data) {
	    $curConfigParams = $this->getConfig();
	    $curConfigParams->bind($data);

	    $db = JFactory::getDBO();
	    $query = "UPDATE #__vm_config SET config = " . $db->Quote($curConfigParams->toString());
	    $db->setQuery($query);
	    if (!$db->query()) {
		$this->setError($table->getError());
		return false;
	    }
	}
	else {
	    $this->setError('No configuration parameters to save!');
	    return false;
	}

	return true;
    }
}
?>