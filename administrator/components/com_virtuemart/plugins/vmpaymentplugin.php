<?php

/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmpaymentplugin.php 4601 2011-11-03 15:50:01Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');

abstract class vmPaymentPlugin extends vmPSPlugin {

	private $_virtuemart_paymentmethod_id = 0;
	private $_payment_name = '';


	/**
	 * This event is fired during the checkout process. It allows the shopper to select
	 * one of the available payment methods.
	 * It should display a radio button (name: virtuemart_paymentmethod_id) to select the payment method. Other
	 * information (like credit card info) might be selected as well.
	 * @author Max Milbers
	 * @param object  VirtueMartCart $cart The cart object
	 * @param integer $selectedPayment of an already selected payment method ID, if any
	 * @return array html to display the radio button
	 *
	 *
	public function plgVmOnSelectPayment(VirtueMartCart $cart, $selectedPayment=0) {

		if ($this->getPluginMethods($cart->vendorId) === false) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_PAYMENT'));
				return;
			} else {
				return;
			}
		}
		$html = array();
		foreach ($this->methods as $payment) {
			if ($this->checkConditions($cart, $payment, $cart->pricesUnformatted)) {
				//vmdebug('plgVmOnSelectPayment', $payment->payment_name, $payment->payment_params);
				$params = new JParameter($payment->payment_params);
				$paymentSalesPrice = $this->calculateSalesPrice($params->get('cost', 0), $this->getPaymentTaxId($params, $cart));
				$payment->payment_name = $this->renderPluginName($payment);
				$html [] = $this->getPluginHtml($payment, $selectedPayment, $paymentSalesPrice);
			}
		}

		return $html;
	}


	abstract function plgVmOnCheckoutCheckData() ;


	/**
	 * plgVmOnPaymentResponseReceived
	 * This event is fired when the payment method returns to the shop after the transaction
	 *
	 *  the payment itself should send in the URL the parameters needed
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param int $virtuemart_order_id : should return the virtuemart_order_id
	 * @param text $html: the html to display
	 * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function plgVmOnPaymentResponseReceived(&$virtuemart_order_id, &$html) {
		return null;
	}

	/**
	 * This event is fired when  the user return to the shop without doing the transaction.
	 *
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL.
	 * The order previously created is deleted.. the cart is not emptied, so the user can change the payment, and re-order.
	 * The payment itself should decide which parameter is necessary
	 *
	 * @param int $virtuemart_order_id : return virtuemart_order_id
	 * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function plgVmOnPaymentUserCancel(&$virtuemart_order_id) {

		return null;
	}

	/**
	 * This event is fired when the payment method notifies you when an event occurs that affects a transaction.
	 * Typically,  the events may also represent authorizations, Fraud Management Filter actions and other actions,
	 * such as refunds, disputes, and chargebacks.
	 *
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param $return_context: it was given and sent in the payment form. The notification should return it back.
	 * Used to know which cart should be emptied, in case it is still in the session.
	 * @param int $virtuemart_order_id : payment  order id
	 * @param char $new_status : new_status for this order id.
	 * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 *
	function plgVmOnNotification(&$return_context, &$virtuemart_order_id, &$new_status) {
		if ($this->_name != $pelement) {
			return null;
		}
		return false;
	}


	/**
	 * plgVmConfirmedOrderRenderPaymentForm
	 * This event is fired after the order has been created
	 * All plugins *must* reimplement this method.
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 * @author Valérie Isaksen
	 * @param the actual order number. IT IS THE ORDER NUMBER THAT MuST BE SENT TO THE FORM. DONT PUT virtuemart_order_id which is a primary key for the order table.
	 * @param orderData
	 * @param contains the session id. Should be sent to the form. And the payment will sent it back.
	 *                  Will be used to empty the cart if necessary, and semnd the order email.
	 * @param the payment form to display. But in some case, the bank can be called directly.
	 * @param false if it should not be changed, otherwise new staus
	 * @return returns 1 if the Cart should be deleted, and order sent
	 */
	abstract function plgVmConfirmedOrderRenderPaymentForm($order_number, $orderData, $return_context, &$html, &$new_status);


// 	/**
// 	 * This functions gets the used and configured payment method
// 	 * pelement of this class determines the used jplugin.
// 	 * The right payment method is determined by the vendor and the jplugin id.
// 	 *
// 	 * This function sets the used payment plugin as variable of this class
// 	 * @author Max Milbers
// 	 *
// 	 */
// 	protected function getVmParams($vendorId=0, $payment_id=0) {

// 		if (!$vendorId)
// 		$vendorId = 1;
// 		$db = JFactory::getDBO();

// 		$q = 'SELECT `payment_params` FROM #__virtuemart_paymentmethods
//         		WHERE `virtuemart_paymentmethod_id`="' . $payment_id . '" ';
// 		$db->setQuery($q);
// 		return $db->loadResult();
// 	}

	function getCosts($params, $cart_prices) {
		return 0;
	}

	/**
	 * Check if the payment conditions are fulfilled for this payment method
	* @author: Valerie Isaksen
	*
	* @param $cart_prices: cart prices
	* @param $payment
	* @return true: if the conditions are fulfilled, false otherwise
	*
	*/

	protected function checkConditions($cart, $payment, $cart_prices) {

		$params = new JParameter($payment->payment_params);


// 		if(empty($cart_prices['salesPrice']))
		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $params->get('min_amount', 0) AND $amount <= $params->get('max_amount', 0)
		OR
		($params->get('min_amount', 0) <= $amount AND ($params->get('max_amount', '') == '') ));

		return $amount_cond;
	}

}
