<?php
/**
*
* Manufacturer View
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of manufacturers
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author Patrick Kohl
 */
class VirtuemartViewManufacturer extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		// get necessary models
		$model = $this->getModel();
		$categoryModel = $this->getModel('manufacturercategories');
        $virtuemart_manufacturercategories_id	= $mainframe->getUserStateFromRequest( $option.'virtuemart_manufacturercategories_id', 'virtuemart_manufacturercategories_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );

		$viewName=ShopFunctions::SetViewTitle('vm_manufacturer_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			$manufacturer = $model->getManufacturer();
       		$isNew = ($manufacturer->virtuemart_manufacturer_id < 1);

			$model->addImages($manufacturer);
			$this->assignRef('manufacturer',	$manufacturer);

			 /* Process the images */
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			$mediaModel -> setId($manufacturer->virtuemart_media_id);
			$image = $mediaModel->getFile('manufacturer','image');

			$manufacturerCategories = $categoryModel->getManufacturerCategories();
			$this->assignRef('manufacturerCategories',	$manufacturerCategories);

			ShopFunctions::addStandardEditViewCommands();
        }
        else {


			ShopFunctions::addStandardDefaultViewCommands();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);


			$manufacturers = $model->getManufacturers();
			$this->assignRef('manufacturers',	$manufacturers);
			$categoryFilter = $categoryModel->getCategoryFilter();

			$list['virtuemart_manufacturercategories_id'] =  JHTML::_('select.genericlist',   $categoryFilter, 'virtuemart_manufacturercategories_id', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $virtuemart_manufacturercategories_id );
			$list['search'] = $search;

			$this->assignRef('list', $list);


		}


		parent::display($tpl);
	}

}
// pure php no closing tag
