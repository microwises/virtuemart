<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage	ratings
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
 * HTML View class for ratings (and customer reviews)
 *
 */
class VirtuemartViewRatings extends JView {
	public $max_rating;

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();
		/* Load helpers */
		$this->loadHelper('adminMenu');

		/* Get the task */
		$task = JRequest::getVar('task');
		//
		// Figure out maximum rating scale (default is 5 stars)
		$this->max_rating = VmConfig::get('vm_maximum_rating_scale',5);
		$this->assignRef('max_rating', $this->max_rating);

		switch ($task) {
			case 'add':
				// @todo: adding is slightly different (not supported for now, from admin page).
			case 'edit':
				/* Get the data */
				$rating = $this->get('Rating');

				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_RATING_EDIT_TITLE' ).' :: '.$rating->product_name, 'vm_product_48');
				JToolBarHelper::divider();
				JToolBarHelper::apply();
				JToolBarHelper::save();
				JToolBarHelper::cancel();

				/* Assign the data */
				$this->preprocess($rating);
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
				JToolBarHelper::divider();
				JToolBarHelper::publishList();
				JToolBarHelper::unpublishList();
				JToolBarHelper::deleteListX();
				JToolBarHelper::editListX();

				/* Assign the data */
				$this->preprocess($ratingslist);
				$this->assignRef('ratingslist', $ratingslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists',	$lists);

				break;
		}
		parent::display($tpl);
	}
	// Common preprocessing of retrieved values before passing to a template
	private function preprocess(&$ratings) {	
		// Figure out date format setting 
		$config = JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$dateformat = VmConfig::get('dateformat');
		if (empty($dateformat)) {$dateformat = '%Y-%m-%d %H:%M';} // temporary workaround
	
		if (is_array($ratings)) {
			foreach($ratings as $row) {
				// Cap ratings to the shop scale
				if ($row->user_rating > $this->max_rating) {
					$row->user_rating = $this->max_rating;
				}
				// Date formatting 
				$date= JFactory::getDate($row->time, $tzoffset);
				$row->reviewDate =  $date->toFormat($dateformat);
			}
		} else {
			if ($ratings->user_rating > $this->max_rating) {
				$ratings->user_rating = $this->max_rating;
			}
			// Date formatting 
			$date= JFactory::getDate($ratings->time, $tzoffset);
			$ratings->reviewDate = $date->toFormat($dateformat);
		}
		return $ratings;
	}
}
// pure php no closing tag
