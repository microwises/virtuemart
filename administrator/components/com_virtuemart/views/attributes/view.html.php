<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
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
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewAttributes extends JView {

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');

		/* Get the task */
		$task = JRequest::getVar('task');

		/* Load helpers */
		$this->loadHelper('adminMenu');
		/* Get the product */
		if (JRequest::getInt('product_id') > 0) $product = $this->get('ProductDetails', 'product');
		else $product = false;

		/* Handle any publish/unpublish */
		switch ($task) {
			case 'add':
			case 'edit':
				/* Load the attribute */
				$attribute = $this->get('Attribute');

				/* Load the list order */
				if ($task == 'add') $lists['listorder'] = JText::_('CMN_NEW_ITEM_LAST');
				else {
					$listorder = $this->get('ListOrder');
					$lists['listorder'] = JHTML::_('select.genericlist', $listorder, 'listorder', '', 'value', 'text', $attribute->attribute_list);
				}

				/* Assign values */
				$this->assignRef('attribute', $attribute);
				$this->assignRef('lists', $lists);

				/* Toolbar */
				if ($task == 'add') $text = JText::_( 'ADD_ATTRIBUTE' );
				else {
					$text = JText::_( 'EDIT_ATTRIBUTE' );
					JRequest::setVar('cid', $attribute->product_id);
					$product = $this->get('ProductDetails', 'product');
				}
				if ($product) $text .= ' :: '.$product->product_sku.' :: '.$product->product_name;
				JToolBarHelper::title($text, 'vm_product_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				break;
			default:
				/* Get the attributes */
				$attributeslist = $this->get('AttributesList');
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				/* Toolbar */
				if ($product) $text = ' :: '.$product->product_sku.' :: '.$product->product_name;
				else $text = '';
				JToolBarHelper::title(JText::_( 'ATTRIBUTES_LIST' ).$text, 'vm_product_48');
				JToolBarHelper::deleteListX();
				if (JRequest::getInt('product_id') > 0) JToolBarHelper::addNew();

				/* Assign the data */
				$this->assignRef('attributeslist', $attributeslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists', $lists);
				break;
		}

		parent::display($tpl);
	}

}
// pure php no closing tag
