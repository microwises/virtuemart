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
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');
jimport('joomla.html.pane');

/**
 * HTML View class for maintaining the list of categories
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 */
class VirtuemartViewCategory extends VmView {

	function display($tpl = null) {

		$this->loadHelper('html');

		$model = $this->getModel();
		$layoutName = $this->getLayout();

		if ($layoutName == 'edit') {

			$category = $model->getCategory('',false);

			if (isset($category->category_name)) $name = $category->category_name; else $name ='';
			$this->SetViewTitle('CATEGORY',$name);


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

			$this->addStandardEditViewCommands($category->virtuemart_category_id);
		}
		else {
			$this->SetViewTitle('CATEGORY_S');

			$keyWord ='';
			$categories = $model->getCategoryTree(0,0,false);

			$this->assignRef('categories', $categories);

			$this->assignRef('model',	$model);


			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model);

		}

		parent::display($tpl);
	}

}

// pure php no closing tag
