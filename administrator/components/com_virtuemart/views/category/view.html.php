<?php

/**
 *
 * Category View
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
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
jimport('joomla.html.pane');

/**
 * HTML View class for maintaining the list of categories
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 */
class VirtuemartViewCategory extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminui');
		$this->loadHelper('shopFunctions');
		//		$this->loadHelper('image');

		$model = $this->getModel();
		$layoutName = JRequest::getWord('layout', 'default');

		if ($layoutName == 'edit') {

			$category = $model->getCategory('',false);

			if (isset($category->category_name)) $name = $category->category_name; else $name ='';
			$viewName=ShopFunctions::SetViewTitle('CATEGORY',$name);
			$this->assignRef('viewName', $viewName);



			$this->assignRef('viewName',$viewName);
			$model->addImages($category);

			if ( $category->virtuemart_category_id > 1 ) {
				$relationInfo = $model->getRelationInfo( $category->virtuemart_category_id );
				$this->assignRef('relationInfo', $relationInfo);
			}

			$parent = $model->getParentCategory( $category->virtuemart_category_id );
			$this->assignRef('parent', $parent);

			if(!class_exists('ShopFunctions'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
			$templateList = ShopFunctions::renderTemplateList(JText::_('COM_VIRTUEMART_CATEGORY_TEMPLATE_DEFAULT'));

			$this->assignRef('jTemplateList', $templateList);

			if(!class_exists('VirtueMartModelConfig'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'config.php');
			$categoryLayoutList = VirtueMartModelConfig::getLayoutList('category');
			$this->assignRef('categoryLayouts', $categoryLayoutList);

			$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
			$this->assignRef('productLayouts', $productLayouts);

			$categorylist = ShopFunctions::categoryListTree(array($parent->virtuemart_category_id));

			$this->assignRef('category', $category);
			$this->assignRef('categorylist', $categorylist);

			ShopFunctions::addStandardEditViewCommands();
		}
		else {
			$viewName = ShopFunctions::SetViewTitle('CATEGORY_S');
			$this->assignRef('viewName', $viewName);

			/**
			 * Commented out for future use
			 JToolBarHelper::custom('toggleShared', 'icon-32-new', '', JText::_('COM_VIRTUEMART_CATEGORY_SHARE'), true);
			 JToolBarHelper::custom('toggleShared', 'icon-32-new', '', JText::_('COM_VIRTUEMART_CATEGORY_UNSHARE'), true);
			 */

// 			$keyWord = JRequest::
			$keyWord ='';
			$categories = $model->getCategoryTree(0,0,false);
// 			vmdebug('$categories',$categories);
			$this->assignRef('categories', $categories);

			$this->assignRef('model',	$model);


			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);
		}

		parent::display($tpl);
	}

}

// pure php no closing tag
