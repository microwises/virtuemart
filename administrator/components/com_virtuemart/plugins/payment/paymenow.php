<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: paymenow.php 1755 2009-05-01 22:45:17Z rolandd $
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

class plgPaymentPaymenow extends vmPaymentPlugin {
	
	var $payment_code = "PN";
	
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
	function plgPaymentPaymenow( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
   
  /**************************************************************************
  ** name: process_payment()
  ** created by: ryan
  ** description: process transaction for PayMeNow
  ** parameters: $order_number, the number of the order, we're processing here
  **            $order_total, the total $ of the order
  ** returns: 
  ***************************************************************************/
   function process_payment($order_number, $order_total, &$d) {
        global $vmLogger;
        
        require_once( CLASSPATH.'connectionTools.class.php');
        $vars = array(
             "action" => "ns_quicksale_cc",
             "ecxid"  => $this->params->get('PN_LOGIN'),
             "amount" => "$order_total",
             "ccname" => $_SESSION['ccdata']['order_payment_name'],
             "ccnum"  => $_SESSION['ccdata']['order_payment_number'],
             "expmon" => $_SESSION['ccdata']['order_payment_expire_month'],
             "expyear"=> $_SESSION['ccdata']['order_payment_expire_year']
        );
		//build the post string
		$poststring = '';
		foreach($vars AS $key => $val){
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
        $results = vmConnector::handleCommunication("https://trans.atsbank.com/cgi-bin/trans.cgi", $poststring);
        
        if (stristr($results, "Accepted")) {
            #Clean up the cart, send out the emails, and display thankyyou page.
            return true;
        }
        else {
            if ($reason = stristr($results, "Declined"))
            {
            $vmLogger->err( "The transaction was declined because of: <strong>$reason</strong><br />" );
            }
            else
            {
            $vmLogger->err( "FATAL ERROR! Declined for an unknown reason, possibly a server misconfiguration error.<br/>$results" );
            }
            return false;
        }
        
        #echo $results;

   }
   
}
?>