<?php
/**
*
* Coupon View
*
* @package	VirtueMart
* @subpackage Coupon
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of Coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG
 */
class VirtuemartViewCoupon extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();

        $coupon = $model->getCoupon();

		$viewName = JText::_('COM_VIRTUEMART_CONTROLLER_COUPON');
		$this->assignRef('viewName',$viewName); 
		$taskName = JText::_('COM_VIRTUEMART_'.JRequest::getVar('task', 'list'));
		JToolBarHelper::title( JText::sprintf( 'COM_VIRTUEMART_STRING1_STRING2' ,$viewName, $taskName , 'vm_coupon_48');

        $layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {
			if ($coupon->virtuemart_coupon_id < 1) {
				// Set a default expiration date
				$_expTime = explode(',', VmConfig::get('coupons_default_expire','14,D'));

				if (!empty( $_expTime[1]) && $_expTime[1] == 'W') {
					$_expTime[0] = $_expTime[0] * 7;
					$_expTime[1] = 'D';
				}
				if (version_compare(PHP_VERSION, '5.3.0', '<')) {
					$_dtArray = getdate(time());
					if ($_expTime[1] == 'D') {
						$_dtArray['mday'] += $_expTime[0];
					} elseif ($_expTime[1] == 'M') {
						$_dtArray['mon'] += $_expTime[0];
					} elseif ($_expTime[1] == 'Y') {
						$_dtArray['year'] += $_expTime[0];
					}
					$coupon->coupon_expiry_date =
						  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds']
						, $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
				} else {
					$_expDate = new DateTime();
					$_expDate->add(new DateInterval('P'.$_expTime[0].$_expTime[1]));
					$coupon->coupon_expiry_date = $_expDate->format("U");
				}
			} 
			JToolBarHelper::divider();
			JToolBarHelper::save();
                        JToolBarHelper::apply();
			JToolBarHelper::cancel();
			$this->assignRef('coupon',	$coupon);
        }
        else {
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$coupons = $model->getCoupons();
			$this->assignRef('coupons',	$coupons);
		}

		$dateformat = VmConfig::get('dateformat');
		$this->assignRef('dateformat',	$dateformat);

		parent::display($tpl);
	}

}
// pure php no closing tag
