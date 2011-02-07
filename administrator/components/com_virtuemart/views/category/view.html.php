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
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('image');
		
		$model = $this->getModel();
        $layoutName = JRequest::getVar('layout', 'default');
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $category = $model->getCategory();

        $isNew = ($category->category_id < 1);

		if ($layoutName == 'edit') {
			if ( $isNew ) {
				JToolBarHelper::title(  JText::_('VM_CATEGORY_LIST_LBL' ).': <small><small>[ New ]</small></small>', 'vm_categories_48');
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CATEGORY_LIST_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_categories_48');
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel('cancel', 'Close');

				$relationInfo = $model->getRelationInfo( $category->category_id );
				$this->assignRef('relationInfo', $relationInfo);
			}


			$parent = $model->getParentCategory( $category->category_id );
			$this->assignRef('parent', $parent);
			
			
			$templateList = array();
			$JVersion = new JVersion();
			if ( ! $JVersion->isCompatible('1.6.0')) {
				$defaulttemplate = array();
				$defaulttemplate[0] = new stdClass;
				$defaulttemplate[0] -> name = JText::_('VM_CATEGORY_TEMPLATE_DEFAULT');
				$defaulttemplate[0] -> directory = 0;
				
				
//				$templateList[] = $defaulttemplate;
				
				require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'template.php');
				$templatesList = TemplatesHelper::parseXMLTemplateFiles(JPATH_SITE.DS.'templates');
				$templateList = array_merge($defaulttemplate,$templatesList);
//				dump($templateList, 'vor rsort my template list');
//				rsort($templateList);
//				dump($templateList, 'nach rsort my template list');
//				$templateList = rsort($templateList);
//				$templateList[] = JHTML::_('select.option', JText::_('VM_CATEGORY_TEMPLATE_DEFAULT'), 0, 'name' );
//				$templateList = rsort($templateList);
				dump($templateList, 'my template list');
			} else {
				//TODO add else for 1.6 Joomla! versions. 
				// because parseXMLTemplateFiles no longer exists.				
			}
			
			$this->assignRef('jTemplateList', $templateList);
			
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'config.php');
			$categoryLayoutList = VirtueMartModelConfig::getLayoutList('category');
			$this->assignRef('categoryLayouts', $categoryLayoutList);

			$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
			$this->assignRef('productLayouts', $productLayouts);
			
			$categorylist = ShopFunctions::categoryListTree(array($parent->category_id));

			$this->assignRef('category', $category);
			$this->assignRef('categorylist', $categorylist);
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CATEGORY_LIST_LBL' ), 'vm_categories_48' );
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			/**
			* Commented out for future use
			JToolBarHelper::custom('toggleShared', 'icon-32-new', '', JText::_('VM_CATEGORY_SHARE'), true);
			JToolBarHelper::custom('toggleShared', 'icon-32-new', '', JText::_('VM_CATEGORY_UNSHARE'), true);
			*/
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$categories = $model->getCategoryTree(false);
        	$categoriesSorted = $model->sortCategoryTree($categories);

			$pagination = $model->getPagination();

			$lists = array();
			$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
			$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

			$this->assignRef('pagination',	$pagination);
			$this->assignRef('categories', $categoriesSorted['categories']);
			$this->assignRef('depthList', $categoriesSorted['depth_list']);
			$this->assignRef('rowList',	$categoriesSorted['row_list']);
			$this->assignRef('idList', $categoriesSorted['id_list']);
			$this->assignRef('lists', $lists);
		}


		parent::display($tpl);
	}

}
// pure php no closing tag
