<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * SelfCall to plugins(ajax)
 * @author Valérie Isaksen
 * @version $Id:
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class KlarnaSelfCall {

	/*
	 * Ajax call to get Pclasses
	 * and create table if not exist
	 * only called from BE when adding a new country/code ...
	 * Click on update/Fetch PClasses
	 * @author Patrick Kohl
	 *
	 */
	function getPclasses() {
		jimport('phpxmlrpc.xmlrpc');
		$handler = new KlarnaHandler ;
		// call klarna server for pClasses
		$methodid = jrequest::getInt('methodid');
		if (!class_exists( 'VmModel' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');
		$model = VmModel::getModel('paymentmethod');
		$payment = $model->getPayment();
		if (!class_exists( 'vmParameters' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'parameterparser.php');
		$parameters = new vmParameters($payment,  $payment->payment_element , 'plugin' ,'vmpayment');
		$data = $parameters->getParamByName('data');
		// echo "<pre>";print_r($data);
		echo $handler->fetchPClasses($data);
		// echo result with tmpl ?
	}
}

