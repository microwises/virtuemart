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
class VirtuemartViewRatings extends JView {

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();
		/* Load helpers */
		$this->loadHelper('adminMenu');

		/* Get the task */
		$task = JRequest::getVar('task');

		switch ($task) {
			case 'add':
			case 'edit':
				/* Get the data */
				$rating = $this->get('Rating');

				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_REVIEW_EDIT' ).' :: '.$rating->product_name, 'vm_product_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();

				/* Assign the data */
				$this->assignRef('rating', $rating);
				break;
			default:
				/* Get the data */
				$ratingslist = $this->get('Ratings');

				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_REVIEWS' ), 'vm_reviews_48');
				JToolBarHelper::publish();
				JToolBarHelper::unpublish();
				JToolBarHelper::deleteListX();
				JToolBarHelper::editListX();

				/* Assign the data */
				$this->assignRef('ratingslist', $ratingslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists',	$lists);
				break;
		}

		parent::display($tpl);
	}

}
?>
