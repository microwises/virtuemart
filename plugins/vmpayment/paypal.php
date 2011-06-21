<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: ps_paypal.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'paypal ':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id$
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
 * http://virtuemart.org
 */

class plgVMPaymentPaypal extends vmPaymentPlugin {

	var $pelement;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgVMPaymentPaypal(& $subject, $config) {
		$this->_pelement = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct($subject, $config);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Oscar van Eijk
	 */
	protected function _createTable()
	{
		$scheme = DbScheme::get_instance();
		$scheme->create_scheme('#__virtuemart_order_payment_'.$this->_pelement);
		$schemeCols = array(
			 'id' => array (
					 'type' => 'int'
					,'length' => 11
					,'auto_inc' => true
					,'null' => false
			)
			,'virtuemart_order_id' => array (
					 'type' => 'int'
					,'length' => 11
					,'null' => false
			)
			,'payment_method_id' => array (
					 'type' => 'text'
					,'null' => false
			)
		);
		$schemeIdx = array(
			 'idx_order_payment' => array(
					 'columns' => array ('virtuemart_order_id')
					,'primary' => false
					,'unique' => false
					,'type' => null
			)
		);
		$scheme->define_scheme($schemeCols);
		$scheme->define_index($schemeIdx);
		if (!$scheme->scheme(true)) {
			JError::raiseWarning(500, $scheme->get_db_error());
		}
		$scheme->reset();
	}
	/* this add the paiement on the select list choice*/
	public function plgVmOnSelectPayment($cart, $selectedPayment=0)
	{

		   if (  $this->getPaymentMethods($cart->vendorId) === false) {
                if (empty($this->_name)) {
                    $app = JFactory::getApplication();
                    $app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_PAYMENT'));
                    return;
                } else {
                    //return JText::sprintf('COM_VIRTUEMART_SHIPPER_NOT_VALID_FOR_THIS_VENDOR', $this->_name , $cart->vendorId );
                    return;
                }
            }
            $html="";
             $logos = $this->_getPaymentLogos( $this->params->get('payment_logos','') );
            foreach ($this->payments as $payment) {
                $payment->payment_name=$logos.' '.$payment->payment_name;
                $html .= $this->getPaymentHtml($payment, $selectedPayment,   $cart);
              }

            return $html;
	}
	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnCheckoutCheckPaymentData()
	 *	Here have to give all value for the BANK
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnConfirmedOrderStorePaymentData()
	 * @author Oscar van Eijk
	 */


	function plgVmOnConfirmedOrderStorePaymentData ($orderNr, $orderData, $priceData)
	{
		if (!$this->selectedThisPayment($this->_pelement, $orderData->virtuemart_paymentmethod_id)) {
			return null; // Another method was selected, do nothing
		}
           $paramstring= $this->getVmPaymentParams($vendorId=0,$orderData->virtuemart_paymentmethod_id);
             $params = new JParameter( $paramstring);
		$returnValue = 'P'; // TODO Read the status from the parameters

		// Load the required helpers
		//if(!class_exists('VmConnector')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'connection.php');

		if (!class_exists('VirtueMartModelOrders'))	require( JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orders.php' );

		$usr =& JFactory::getUser();

		$usrBT = $orderData->BT;
		$usrST = (($orderData->ST === null) ? $orderData->BT : $orderData->ST);

		$database = JFactory::getDBO();

		$vendorID = 1 ; //$orderData->virtuemart_vendor_id; TODO
		$vendorCurrency = VirtueMartModelVendor::getVendorCurrency($vendorID);
                $merchant_email= $this->_getMerchantEmail($params);

 		$testReq = $params->get('DEBUG') == 1 ? 'YES' : 'NO';
//		dump($orderData,'info commande');
//		dump($priceData,'info prix');

		$post_variables = Array(
			'cmd' => '_ext-enter' ,
			'redirect_cmd' => '_xclick' ,
			'upload' => '1' ,
			'business' => $merchant_email ,
			'receiver_email' => $merchant_email ,
			'item_name' => JText::_( 'COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER' ) . ': ' . $orderNr ,
			'order_number' =>$orderNr,
			"order_id" => $orderNr,
			"invoice" => $orderNr ,
			"amount" => $priceData['billTotal'] ,
			"shipping" => $priceData['order_shipping'],
			"currency_code" => $vendorCurrency ,
			"address_override" => "1" ,
			"first_name" => $usrBT[ 'first_name' ] ,
			"last_name" => $usrBT[ 'last_name' ] ,
			"address1" => $usrBT[ 'address_1' ] ,
			"address2" => $usrBT[ 'address_2' ] ,
			"zip" => $usrBT[ 'zip' ] ,
			"city" => $usrBT[ 'city' ] ,
			"state" =>  ShopFunctions::getCountryByID($usrBT[ 'viruemart_state_id' ] ),
			"country" => ShopFunctions::getCountryByID($usrST['virtuemart_country_id'],'country_3_code') ,
			"email" => $usrBT[ 'email' ] ,
			"night_phone_b" => $usrBT[ 'phone_1' ] ,
                        "return" =>  JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=paymentResponse&task=paymentResponse&pelement='.$this->pelement.'&order_number=' . $orderNr ), // TO VERIFY
                         "notify_url" => JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=paymentResponse&task=paymentNotification&pelement='.$this->pelement.'&order_number=' . $orderNr ), // TO VERIFY send the bank payment statut
                        "cancel_return" => JROUTE::_(JURI::root().'index.php?option=com_virtuemart') , // TO VERIFY
			"undefined_quantity" => "0" ,
			"test_ipn" => $params->get('debug') ,
			"pal" => "NRUBJXESJTY24" ,
			"no_shipping" => "1" ,
			"no_note" => "1" ) ;
	//warning HTPPS 		"cpp_header_image" => $vendor_image_url ,

		$qstring = '';
		foreach($post_variables AS $k => $v){
			$qstring .= (empty($qstring) ? '' : '&')
					. urlencode($k) . '=' . urlencode($v);
		}

		// Prepare data that should be stored in the database
		$dbValues['virtuemart_order_id'] = $orderNr;
		$dbValues['payment_method_id'] = $orderData->virtuemart_paymentmethod_id;
		// TODO wait for PAYPAL return ???
		$this->writePaymentData($dbValues, '#__virtuemart_order_payment_' . $this->_pelement);
		// Send to PAYPAL TODO Sandbox choice ???
		$url= $this->_getPaypalUrl($params);
		$mainframe = JFactory::getApplication();
                $mainframe->redirect("https://".$url,$qstring);
		

		return 'P'; // Does not return anyway... Set order status to Pending.  TODO Must be a plugin parameter
	}

      

           function plgVmOnPaymentResponseReceived( ) {
               return null;

           }
	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderPaymentBE($virtuemart_order_id, $paymethod_id)
	{

		if (!$this->selectedThisMethod($this->_pelement, $paymethod_id)) {
			return null; // Another method was selected, do nothing
		}
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_order_payment_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);
		if (!($payment = $db->loadObject())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist">'."\n";
		$html .= '	<thead>'."\n";
		$html .= '		<tr>'."\n";
		$html .= '			<th>'.JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL').'</th>'."\n";
//		$html .= '			<th width="40%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NAME').'</th>'."\n";
//		$html .= '			<th width="30%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER').'</th>'."\n";
//		$html .= '			<th width="17%">'.JText::_('VM_ORDER_PRINT_EXPIRE_DATE').'</th>'."\n";
		$html .= '		</tr>'."\n";
		$html .= '	</thead>'."\n";
		$html .= '	<tr>'."\n";
		$html .= '		<td>'.$this->getThisMethodName($paymethod_id).'</td>'."\n";
//		$html .= '		<td></td>'."\n";
//		$html .= '		<td></td>'."\n";
//		$html .= '		<td></td>'."\n";
		$html .= '	<tr>'."\n";
		$html .= '</table>'."\n";
		return $html;
	}

/*
     * This method returns the logo image form the shipper
     */

    protected function _getPaymentLogos($logo_list ) {
        $logos=array();
            if (!empty($logo_list)) {
                if (!is_array($logo_list)) {
                    $logos[0] = $logo_list;
                } else {
                    $logos = $logo_list;
                }
            }
        $img = "";
        /* TODO: chercher chemin dynamique */
        $path = JURI::base() . "images" . DS . "stories" . DS . "virtuemart" . DS . "payment" . DS;
        $img = "";
        foreach ($logos as $logo) {
            $img .= '<img align="middle" src="' . $path . $logo . '"   > ';
        }
        return $img;
    }

    /**
     * Validates the IPN data
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _validateIPN( $data )
    {
        $secure_post = $this->params->get( 'secure_post', '0' );
        $paypal_url = $this->_getPaypalURL();

        $req = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            if ($key != 'view' && $key != 'layout') {
                $value = urlencode($value);
                $req .= "&$key=$value";
            }
        }

        // post back to PayPal system to validate
        $header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        //$header .= "Host: " . $this->_getPostURL(false) . ":443\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        if ($secure_post) {
            // If possible, securely post back to paypal using HTTPS
            // Your PHP server will need to be SSL enabled
            $fp = fsockopen ('ssl://' . $paypal_url , 443, $errno, $errstr, 30);
        }
        else {
            $fp = fsockopen ($paypal_url, 80, $errno, $errstr, 30);
        }

        if ( ! $fp) {
            return JText::sprintf('PAYPAL ERROR POSTING IPN DATA BACK', $errstr, $errno);
        }
        else {
            fputs ($fp, $header . $req);
            while ( ! feof($fp)) {
                $res = fgets ($fp, 1024); //echo $res;
                if (strcmp ($res, 'VERIFIED') == 0) {
                    return '';
                }
                elseif (strcmp ($res, 'INVALID') == 0) {
                    return JText::_('PAYPAL ERROR IPN VALIDATION');
                }
            }
        }

        fclose($fp);
        return '';
    }
function _getMerchantEmail($params) {
    return $params->get('sandox') ? $params->get('email_sandbox_merchant') :  $params->get('email_merchant');

}

function _getPaypalUrl($params) {
  $url = $params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';     
            $url =    $url . '/cgi-bin/webscr';
        return $url;

}

}

// No closing tag
