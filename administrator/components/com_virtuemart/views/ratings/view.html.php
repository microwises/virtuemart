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

		/* Load helpers */
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');


		//
		// Figure out maximum rating scale (default is 5 stars)
		$this->max_rating = VmConfig::get('vm_maximum_rating_scale',5);
		$this->assignRef('max_rating', $this->max_rating);

		$model = $this->getModel();
		$viewName=ShopFunctions::SetViewTitle('vm_reviews_48');
		$this->assignRef('viewName',$viewName);

		/* Get the task */
		$task = JRequest::getVar('task');
		switch ($task) {
			case 'add':
				// @todo: adding is slightly different (not supported for now, from admin page).
			case 'edit':
				/* Get the data */
				$rating = $model->getRating();

				ShopFunctions::addStandardEditViewCommands();

				/* Assign the data */
				$this->preprocess($rating);
				$this->assignRef('rating', $rating);

				break;
			default:
				/* Get the data */
				$ratingslist = $model->getRatings();
				dump($ratingslist,'$ratingslist');
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists = array();
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				ShopFunctions::addStandardDefaultViewCommands();

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
		$dateformat = VmConfig::get('dateformat','%Y-%m-%d %H:%M');

		if (is_array($ratings)) {
			foreach($ratings as $row) {
				// Cap ratings to the shop scale
//				if ($row->rating > $this->max_rating) {
//					$row->rating = $this->max_rating;
//				}
				// Date formatting
				$date= JFactory::getDate($row->modified_on, $tzoffset);
				$row->reviewDate =  $date->toFormat($dateformat);
			}
		} else {
//			if ($ratings->rating > $this->max_rating) {
//				$ratings->rating = $this->max_rating;
//			}
			// Date formatting
			$date= JFactory::getDate($ratings->modified_on, $tzoffset);
			$ratings->reviewDate = $date->toFormat($dateformat);
		}
		return $ratings;
	}
}
// pure php no closing tag
