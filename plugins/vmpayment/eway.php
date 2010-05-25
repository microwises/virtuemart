<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: eway.php 2225 2010-01-19 23:18:41Z rolandd $
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

/**
* This class let's you handle transactions with the eWay Payment Gateway
*
*/
class plgPaymentEway extends vmPaymentPlugin {
	
	var $payment_code = "EWAY" ;
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.2.0
	 */
	function plgPaymentEway( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
   
  /**************************************************************************
  ** name: process_payment()
  ** returns: true if transaction is OK
  *			  false if error in transaction
  ***************************************************************************/
    function process_payment($order_number, $order_total, &$d) {
        global $vendor_name, $vmLogger;
        $auth = $_SESSION['auth'];
        
        
        /* eWAY Gateway Location (URI) */
        if( $this->params->get('DEBUG') == "0" )
            define( "GATEWAY_URL", "https://www.eway.com.au/gateway_cvn/xmlpayment.asp");
        else
            define( "GATEWAY_URL", "https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp");
            
        // Get user billing information
        $db = JFactory::getDBO();
        
        $qt = "SELECT * FROM #__users WHERE id='".$auth["user_id"]."' AND address_type='BT'";
        $db->query($qt);
        $db->next_record();
        
        // WE need the $order_total in cents!
        $order_total = $order_total * 100;
        
        // We need to show the year with two digits only
        $year = substr( $_SESSION['ccdata']['order_payment_expire_year'], 2, 2 );
        
		$my_trxn_number = uniqid( "eway_" );
        $payer_name_is = $_SESSION['ccdata']['order_payment_name'];
        
		$eway = new EwayPayment( $this->params->get('EWAY_CUSTID'), GATEWAY_URL );

		$eway->setCustomerFirstname( $db->f("first_name") );
		$eway->setCustomerLastname( $db->f("last_name") );
		$eway->setCustomerEmail( $db->f("email") );
		$eway->setCustomerAddress( $db->f("address_1") );
		$eway->setCustomerPostcode( $db->f("zip") );
		$eway->setCustomerInvoiceDescription( $vendor_name." Order" );
		$eway->setCustomerInvoiceRef( $order_number );
		$eway->setCardHoldersName( $payer_name_is + '' );
		$eway->setCardNumber( $_SESSION['ccdata']['order_payment_number'] );
		$eway->setCardExpiryMonth( $_SESSION['ccdata']['order_payment_expire_month'] );
		$eway->setCardExpiryYear( $year + '' );
		$eway->setCardCVN( $_SESSION['ccdata']['credit_card_code'] );
		$eway->setTrxnNumber( $my_trxn_number );
		$eway->setTotalAmount( $order_total );
        
        if( $eway->doPayment() == EWAY_TRANSACTION_OK ) {
			
			$d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS');
            //Catch Transaction ID
            $d["order_payment_trans_id"] = $eway->getTrxnNumber();
            //$d["error"] = "";
            return true;
		} 
        else {
			$vmLogger->err( JText::_('VM_PAYMENT_ERROR',false).": "
                            .$eway->getErrorMessage() );
            //Catch Transaction ID
            $d["order_payment_trans_id"] = $eway->getTrxnNumber();
            return false;
		}
    }
   
}

 /*
  * class EwayPayment
  * Electronic Payment XML Interface for eWAY
  *
  * (c) Copyright Matthew Horoschun, CanPrint Communications 2005.
  *
  * $Id: eway.php 2225 2010-01-19 23:18:41Z rolandd $
  *
  * Date:    2005-04-18
  * Version: 2.0
  *
  */

define( 'EWAY_DEFAULT_GATEWAY_URL', 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp' );
define( 'EWAY_DEFAULT_CUSTOMER_ID', '87654321' );

define( 'EWAY_CURL_ERROR_OFFSET', 1000 );
define( 'EWAY_XML_ERROR_OFFSET',  2000 );

define( 'EWAY_TRANSACTION_OK',       0 );
define( 'EWAY_TRANSACTION_FAILED',   1 );
define( 'EWAY_TRANSACTION_UNKNOWN',  2 );


class EwayPayment {
    var $parser;
    var $xmlData;
    var $currentTag;
    
    var $myGatewayURL;
    var $myCustomerID;
    
    var $myTotalAmount;
    var $myCustomerFirstname;
    var $myCustomerLastname;
    var $myCustomerEmail;
    var $myCustomerAddress;
    var $myCustomerPostcode;
    var $myCustomerInvoiceDescription;
    var $myCustomerInvoiceRef;
    var $myCardHoldersName;
    var $myCardNumber;
    var $myCardExpiryMonth;
    var $myCardExpiryYear;
    var $myCardCVN;
    var $myTrxnNumber;
    var $myOption1;
    var $myOption2;
    var $myOption3;
    
    var $myResultTrxnStatus;
    var $myResultTrxnNumber;
    var $myResultTrxnOption1;
    var $myResultTrxnOption2;
    var $myResultTrxnOption3;
    var $myResultTrxnReference;
    var $myResultTrxnError;
    var $myResultAuthCode;
    var $myResultReturnAmount;
	var $myCardName;
    
    var $myError;
    var $myErrorMessage;

    /***********************************************************************
     *** Class Constructor                                               ***
     ***********************************************************************/
    function EwayPayment( $customerID = EWAY_DEFAULT_CUSTOMER_ID, $gatewayURL = EWAY_DEFAULT_GATEWAY_URL ) {
        $this->myCustomerID = $customerID;
        $this->myGatewayURL = $gatewayURL;
    }


    /***********************************************************************
     *** XML Parser - Callback functions                                 ***
     ***********************************************************************/
    function epXmlElementStart ($parser, $tag, $attributes) {
        $this->currentTag = $tag;
    }
    
    function epXmlElementEnd ($parser, $tag) {
        $this->currentTag = "";
    }
    
    function epXmlData ($parser, $cdata) {
        $this->xmlData[$this->currentTag] = $cdata;
    }
    
    /***********************************************************************
     *** SET values to send to eWAY                                      ***
     ***********************************************************************/
    function setCustomerID( $customerID ) {
        $this->myCustomerID = $customerID;
    }
    
    function setTotalAmount( $totalAmount ) {
        $this->myTotalAmount = $totalAmount;
    }
    
    function setCustomerFirstname( $customerFirstname ) {
        $this->myCustomerFirstname = $customerFirstname;
    }
    
    function setCustomerLastname( $customerLastname ) {
        $this->myCustomerLastname = $customerLastname;
    }
    
    function setCustomerEmail( $customerEmail ) {
        $this->myCustomerEmail = $customerEmail;
    }
    
    function setCustomerAddress( $customerAddress ) {
        $this->myCustomerAddress = $customerAddress;
    }
    
    function setCustomerPostcode( $customerPostcode ) {
        $this->myCustomerPostcode = $customerPostcode;
    }
    
    function setCustomerInvoiceDescription( $customerInvoiceDescription ) {
        $this->myCustomerInvoiceDescription = $customerInvoiceDescription;
    }
    
    function setCustomerInvoiceRef( $customerInvoiceRef ) {
        $this->myCustomerInvoiceRef = $customerInvoiceRef;
    }
    
    function setCardHoldersName( $cardHoldersName ) {
        $this->myCardHoldersName = $cardHoldersName;
    }
    
    function setCardNumber( $cardNumber ) {
        $this->myCardNumber = $cardNumber;
    }
    
    function setCardExpiryMonth( $cardExpiryMonth ) {
        $this->myCardExpiryMonth = $cardExpiryMonth;
    }
    
    function setCardExpiryYear( $cardExpiryYear ) {
        $this->myCardExpiryYear = $cardExpiryYear;
    }
    
    function setCardCVN( $cardCVN ) {
        $this->myCardCVN = $cardCVN;
    }
    
    function setTrxnNumber( $trxnNumber ) {
        $this->myTrxnNumber = $trxnNumber;
    }
    
    function setOption1( $option1 ) {
        $this->myOption1 = $option1;
    }
    
    function setOption2( $option2 ) {
        $this->myOption2 = $option2;
    }
    
    function setOption3( $option3 ) {
        $this->myOption3 = $option3;
    }

    /***********************************************************************
     *** GET values returned by eWAY                                     ***
     ***********************************************************************/
    function getTrxnStatus() {
        return $this->myResultTrxnStatus;
    }
    
    function getTrxnNumber() {
        return $this->myResultTrxnNumber;
    }
    
    function getTrxnOption1() {
        return $this->myResultTrxnOption1;
    }
    
    function getTrxnOption2() {
        return $this->myResultTrxnOption2;
    }
    
    function getTrxnOption3() {
        return $this->myResultTrxnOption3;
    }
    
    function getTrxnReference() {
        return $this->myResultTrxnReference;
    }
    
    function getTrxnError() {
        return $this->myResultTrxnError;
    }
    
    function getAuthCode() {
        return $this->myResultAuthCode;
    }
    
    function getReturnAmount() { 
        return $this->myResultReturnAmount;
    }

    function getError()
    {
        if( $this->myError != 0 ) {
            // Internal Error
            return $this->myError;
        } else {
            // eWAY Error
            if( $this->getTrxnStatus() == 'True' ) {
                return EWAY_TRANSACTION_OK;
            } elseif( $this->getTrxnStatus() == 'False' ) {
                return EWAY_TRANSACTION_FAILED;
            } else {
                return EWAY_TRANSACTION_UNKNOWN;
            }
        }
    }

    function getErrorMessage()
    {
        if( $this->myError != 0 ) {
            // Internal Error
            return $this->myErrorMessage;
        } else {
            // eWAY Error
            return $this->getTrxnError();
        }
    }

    /***********************************************************************
     *** Business Logic                                                  ***
     ***********************************************************************/
    function doPayment() {
        $xmlRequest = "<ewaygateway>".
                "<ewayCustomerID>".htmlentities( $this->myCustomerID )."</ewayCustomerID>".
                "<ewayTotalAmount>".htmlentities( $this->myTotalAmount)."</ewayTotalAmount>".
                "<ewayCustomerFirstName>".htmlentities( $this->myCustomerFirstname )."</ewayCustomerFirstName>".
                "<ewayCustomerLastName>".htmlentities( $this->myCustomerLastname )."</ewayCustomerLastName>".
                "<ewayCustomerEmail>".htmlentities( $this->myCustomerEmail )."</ewayCustomerEmail>".
                "<ewayCustomerAddress>".htmlentities( $this->myCustomerAddress )."</ewayCustomerAddress>".
                "<ewayCustomerPostcode>".htmlentities( $this->myCustomerPostcode )."</ewayCustomerPostcode>".
                "<ewayCustomerInvoiceDescription>".htmlentities( $this->myCustomerInvoiceDescription )."</ewayCustomerInvoiceDescription>".
                "<ewayCustomerInvoiceRef>".htmlentities( $this->myCustomerInvoiceRef )."</ewayCustomerInvoiceRef>".
                "<ewayCardHoldersName>".htmlentities( $this->myCardName )."</ewayCardHoldersName>".
                "<ewayCardNumber>".htmlentities( $this->myCardNumber )."</ewayCardNumber>".
                "<ewayCardExpiryMonth>".htmlentities( $this->myCardExpiryMonth )."</ewayCardExpiryMonth>".
                "<ewayCardExpiryYear>".htmlentities( $this->myCardExpiryYear )."</ewayCardExpiryYear>".
                "<ewayTrxnNumber>".htmlentities( $this->myTrxnNumber )."</ewayTrxnNumber>".
                "<ewayOption1>".htmlentities( $this->myOption1 )."</ewayOption1>".
                "<ewayOption2>".htmlentities( $this->myOption2 )."</ewayOption2>".
                "<ewayOption3>".htmlentities( $this->myOption3 )."</ewayOption3>".
                "<ewayCVN>".htmlentities( $this->myCardCVN )."</ewayCVN>".
                "</ewaygateway>";

        /* Use CURL to execute XML POST and write output into a string */
        $ch = curl_init( $this->myGatewayURL );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $xmlRequest );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 240 );
        $xmlResponse = curl_exec( $ch );
		//exit;
        
        // Check whether the curl_exec worked.
        if( curl_errno( $ch ) == CURLE_OK ) {
            // It worked, so setup an XML parser for the result.
            $this->parser = xml_parser_create();
            
            // Disable XML tag capitalisation (Case Folding)
            xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, FALSE);
            
            // Define Callback functions for XML Parsing
            xml_set_object($this->parser, &$this);
            xml_set_element_handler ($this->parser, "epXmlElementStart", "epXmlElementEnd");
            xml_set_character_data_handler ($this->parser, "epXmlData");
            
            // Parse the XML response
            xml_parse($this->parser, $xmlResponse, TRUE);
            
            if( xml_get_error_code( $this->parser ) == XML_ERROR_NONE ) {
                // Get the result into local variables.
                $this->myResultTrxnStatus = $this->xmlData['ewayTrxnStatus'];
                $this->myResultTrxnNumber = $this->xmlData['ewayTrxnNumber'];
                $this->myResultTrxnOption1 = $this->xmlData['ewayTrxnOption1'];
                $this->myResultTrxnOption2 = $this->xmlData['ewayTrxnOption2'];
                $this->myResultTrxnOption3 = $this->xmlData['ewayTrxnOption3'];
                $this->myResultTrxnReference = $this->xmlData['ewayTrxnReference'];
                $this->myResultAuthCode = $this->xmlData['ewayAuthCode'];
                $this->myResultReturnAmount = $this->xmlData['ewayReturnAmount'];
                $this->myResultTrxnError = $this->xmlData['ewayTrxnError'];
                $this->myError = 0;
                $this->myErrorMessage = '';
            } else {
                // An XML error occured. Return the error message and number.
                $this->myError = xml_get_error_code( $this->parser ) + EWAY_XML_ERROR_OFFSET;
                $this->myErrorMessage = xml_error_string( $myError );
            }
            // Clean up our XML parser
            xml_parser_free( $this->parser );
        } else {
            // A CURL Error occured. Return the error message and number. (offset so we can pick the error apart)
            $this->myError = curl_errno( $ch ) + EWAY_CURL_ERROR_OFFSET;
            $this->myErrorMessage = curl_error( $ch );
        }
        // Clean up CURL, and return any error.
        curl_close( $ch );
        return $this->getError();
    }
}
?>
