<?php
/**
*
* Coupon View
*
* @package	VirtueMart
* @subpackage Coupon
* @author RickG
 * @author Valerie Isaksen
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
 * @author Valerie Isaksen
 */

if (!class_exists('VirtueMartModelCurrency'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
if (!class_exists('VirtueMartModelVendor'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
class VirtuemartViewCoupon extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminui');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('html');

		$model = $this->getModel();

		$coupon = $model->getCoupon();
		$viewName=ShopFunctions::SetViewTitle('', $coupon->coupon_code);
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getWord('layout', 'default');


// 		if(Vmconfig::get('multix','none')!=='none'){
// 				$vendorList= ShopFunctions::renderVendorList($coupon->virtuemart_vendor_id);
// 				$this->assignRef('vendorList', $vendorList);
// 		}

		 $vendorModel = new VirtueMartModelVendor();
	    $vendorModel->setId(1);
	    $vendor = $vendorModel->getVendor();

	    $currencyModel = new VirtueMartModelCurrency();
	    $currencyModel = $currencyModel->getCurrency($vendor->vendor_currency);
	    $this->assignRef('vendor_currency', $currencyModel->currency_symbol);

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

			$this->assignRef('coupon',	$coupon);

			ShopFunctions::addStandardEditViewCommands();
        } else {

			$coupons = $model->getCoupons();
			$this->assignRef('coupons',	$coupons);
			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);
		}

		parent::display($tpl);
	}

}
// pure php no closing tag
