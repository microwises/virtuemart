<?php
/**
*
* Handle the category view
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 2703 2011-02-11 22:06:12Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport('joomla.application.component.view');

/**
* Handle the category view
*
* @package VirtueMart
* @author Max Milbers
* @todo add full path to breadcrumb
*/
class VirtuemartViewCategories extends JView {

	public function display($tpl = null) {

		$document = JFactory::getDocument();

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();

		/* Set the helper path */
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');

		/* Load helpers */
		$this->loadHelper('image');

		$categoryModel = $this->getModel('category');
	    $categoryId = JRequest::getInt('virtuemart_category_id', 0);
	    $vendorId = 1; //Todo change that for multivendor

//		$categoryId = 0;	//The idea is that you can choose a parent catgory, this value should come from the joomla view parameter stuff
		$category = $categoryModel->getCategory($categoryId);
		if($category->children)	$categoryModel->addImages($category->children);

	    /* Add the category name to the pathway */
		$pathway->addItem($category->category_name); //Todo what should be shown up?
	    $this->assignRef('category', $category);

	    /* Set the titles */
		$document->setTitle($category->category_name); //Todo same here, what should be shown up?


		//Todo think about which metatags should be shown in the categories view
	    if ($category->metadesc) {
			$document->setDescription( $category->metadesc );
		}
		if ($category->metakey) {
			$document->setMetaData('keywords', $category->metakey);
		}
		if ($category->metarobot) {
			$document->setMetaData('robots', $category->metarobot);
		}

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title', $category->category_description);  //Maybe better category_name
		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $category->metaauthor);
		}

	    if(empty($category->category_template)){
	    	$catTpl = VmConfig::get('categorytemplate');
	    }else {
	    	$catTpl = $category->category_template;
	    }

		//Do we need that here? It should show the general category template or the shop template
	    shopFunctionsF::setVmTemplate($this,$catTpl,0,$category->category_layout);

		parent::display($tpl);
	}
}


//no closing tag