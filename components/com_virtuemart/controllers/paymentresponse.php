<?php

/**
 *
 * Controller for the Payement Response
 *
 * @package	VirtueMart
 * @subpackage paymentResponse
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 3388 2011-05-27 13:50:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Controller for the payment response view
 *
 * @package VirtueMart
 * @subpackage paymentResponse
 * @author Valérie Isaksen
 *
 */
class VirtueMartControllerPaymentresponse extends JController {

    /**
     * Construct the cart
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }


     /**
     * PaymentResponseReceived()
     * From the payment page, the user returns to the shop. The order email is sent, and the cart emptied.
        *
     * @author Valerie Isaksen
     *
     */
    function PaymentResponseReceived() {

        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        JPluginHelper::importPlugin('vmpayment');
          $pm = JRequest::getInt('pm', 0);
        $pelement = JRequest::getWord('pelement' );

        $return_context="";
        $dispatcher = JDispatcher::getInstance();
$html="";
        $returnValues = $dispatcher->trigger('plgVmOnPaymentResponseReceived', array('pelement' => $pelement,
                                                                'virtuemart_payment_id' => $pm,                                                              
                                                                'order_id' =>&$orderId, 
                                                                'html' => &$html
                                                                ) );

        foreach ($returnValues as $returnValue) {
            if ($returnValue !== null) {
                 if ($returnValue == 1) {
                   if ($orderId) {
                    if (!class_exists('VirtueMartCart'))
                        require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
                    // get the correct cart / session
                    $cart = VirtueMartCart::getCart();
                    // send the email only if payment has been accepted
                    if (!class_exists('VirtueMartModelOrders'))
                        require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
                    $order = new VirtueMartModelOrders();
                     $cart = VirtueMartCart::getCart();
                    $cart->sentOrderConfirmedEmail($order->getOrder($orderId));
                   
                    $cart->removeCartFromSession();
                    break; // This was the active plugin, so there's nothing left to do here.
                 }
                 }
            }
            // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
        }
        JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_CART_THANKYOU'));
          JRequest::setVar('paymentResponseHtml', $html);
        $view = $this->getView('paymentresponse', 'html');
        $layoutName = JRequest::getVar('layout', 'default');
        $view->setLayout($layoutName);

        /* Display it all */
        $view->display();
    }

    /**
     * PaymentUserCancel()
     * From the payment page, the user has cancelled the order. The order previousy created is deleted.
     * The cart is not emptied, so the user can reorder if necessary.
     * then delete the order
     * @author Valerie Isaksen
     *
     */
    function PaymentUserCancel() {
        
        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        if (!class_exists('VirtueMartCart'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

        JPluginHelper::importPlugin('vmpayment');
        $pelement = JRequest::getWord('pelement');
         $pm = JRequest::getInt('pm', 0);
        $dispatcher = JDispatcher::getInstance();
        $returnValues = $dispatcher->trigger('plgVmOnPaymentUserCancel', array('pelement' => $pelement,
                                                'virtuemart_paymentmethod_id'=>$pm,
                                                'order_id'=>&$orderId  ));
        foreach ($returnValues as $returnValue) {
            if ($returnValue !== null) {
                 if ($returnValue == 1) {
                    // $returnValue[]
                    JRequest::setVar('paymentResponse', $returnValue);
                    if (!class_exists('VirtueMartCart'))
                        require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
                    if ($orderId) {
                        // send the email only if payment has been accepted
                        if (!class_exists('VirtueMartModelOrders'))
                            require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
                        $order = new VirtueMartModelOrders();
                         $order->remove(array('order_id' => $orderId));
                    }
                    break; // This was the active plugin, so there's nothing left to do here.
                 }
            }
            // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
        }


        $view = $this->getView('paymentresponse', 'html');
        $layoutName = JRequest::getWord('layout', 'default');
        $view->setLayout($layoutName);
        JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_PAYMENT_USER_CANCEL'));
       
        /* Display it all */
        $view->display();
    }

    /**
     * Attention this is the function which processs the response of the payment plugin
     *
     * @author Valerie Isaksen
     * @return success of update
     */
    function paymentNotification() {
        $data = JRequest::get('post');
        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        JPluginHelper::importPlugin('vmpayment');
        $pelement = JRequest::getVar('pelement');
          $pm = JRequest::getInt('pm', 0);
        $dispatcher = JDispatcher::getInstance();
        $returnValues = $dispatcher->trigger('plgVmOnPaymentNotification', array('pelement' => $pelement,
                                                'virtuemart_paymentmethod_id'=>$pm,
                                                'return_context'=>&$return_context,
                                                 'order_id'=>&$orderId,
                                                'new_status'=>&$new_status  ));


        if (!class_exists('VirtueMartModelOrders'))
            require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
            foreach ($returnValues as $returnValue) {
                if ($returnValue !== null) {
                     if ($returnValue == 1) {
                        // $returnValue[]

                        if (!class_exists('VirtueMartCart'))
                            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
                        // remove cart from session
                        $session = JFactory::getSession( array('id'=>$return_context));
                        $session->set('vmcart', 0, 'vm');
                        if ($orderId) {
                            // send the email only if payment has been accepted
                            if (!class_exists('VirtueMartModelOrders'))
                                require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
                            $modelOrder = new VirtueMartModelOrders();
                            $order['order_status']=$new_status;
                            $order['virtuemart_order_id']=$orderId;
                            //$modelOrder -> updateOrderStatus($order);
                        }
                        break; // This was the active plugin, so there's nothing left to do here.
                     }
                }
            // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
        }

    }

}

//pure php no Tag
