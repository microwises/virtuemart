<?php

/**
 *
 * Controller for the cart
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
 * Controller for the cart view
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

    function PaymentResponseReceived() {

        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        JPluginHelper::importPlugin('vmpayment');
        $pelement = JRequest::getVar('pelement');
        $dispatcher = JDispatcher::getInstance();
        $returnValues = $dispatcher->trigger('plgVmOnPaymentResponseReceived', array('pelement' => $pelement));
        foreach ($returnValues as $returnValue) {
            if ($returnValue !== null) {
                JRequest::setVar('paymentResponse', $returnValue);
                if (!class_exists('VirtueMartCart'))
                    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
                $cart = VirtueMartCart::getCart();
                $cart->removeCartFromSession();
                break; // This was the active plugin, so there's nothing left to do here.
            }
            // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
        }
        JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_CART_THANKYOU'));
        $view = $this->getView('paymentresponse', 'html');
        $layoutName = JRequest::getVar('layout', 'default');
        $view->setLayout($layoutName);

        /* Display it all */
        $view->display();
    }

    function PaymentUserCancel() {

        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        if (!class_exists('VirtueMartCart'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
        $view = $this->getView('paymentresponse', 'html');
        $layoutName = JRequest::getVar('paymentResponse', 'default');
        $view->setLayout($layoutName);
        JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_PAYMENT_USER_CANCEL'));
        $cart = VirtueMartCart::getCart();
        $cart->removeCartFromSession();
        /* Display it all */
        $view->display();
    }

    function paymentNotification() {
        $data = JRequest::get('post');
        if (!class_exists('vmPaymentPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
        JPluginHelper::importPlugin('vmpayment');
        $pelement = JRequest::getVar('pelement');
        $dispatcher = JDispatcher::getInstance();
        $retValues = $dispatcher->trigger('plgVmOnPaymentNotification', array('pelement' => $pelement));
    }

}

//pure php no Tag
