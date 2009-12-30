<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: linkpoint.php 1760 2009-05-03 22:58:57Z Aravot $
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

/* modified by Atlanticom to overcome ampersand issues and provide human friendly linkpoint order numbers 
*  and overcome duplicate order number issue with linkpoint
*  Requires an additional table added to the database called (mos or) jos_vm_linkpoint with two fields
*  Id   type int()
*  Last Attempt   type varchar(11)
*
*  Then add 1 record to the table, with an Id of 1, and Last Attempt of WEB-xxx (where xxx is your last good order number)
*/


/**
* The linkpoint class, containing the payment processing code
* for transactions with linkpoint.net or yourpay.com
* contains code for Recurring billing an/or PreAuth Options
*
* Installation:  You must have the linkpoint/yourpay.com API  file (lphp.php) in the
* current working directory, or your php includes directory.
* you also should have your public key file provided by linkpoint/yourpay.com secured
* in a directory outside of the webroot, but readable by the webserver daemon owner (ie; nobody)
*
* In the administrator console of VirtueMart -> Payment Method List -> Creditcard LP -> Configuration
* you can insert your store number, and public key location.
*
* Any questions, email jimmy@freshstation.org
* @copyright (C) 2005 James McMillan
*/

define ('LP_VERIFIED_STATUS', 'C');

class plgPaymentLinkpoint extends vmPaymentPlugin {
	
	var $payment_code = "LP" ;
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
	function plgPaymentLinkpoint( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}


  /**************************************************************************
  ** name: process_payment()
  ** created by: James McMillan
  ** description: process transaction linkpoint.net
  ** parameters: $order_number, the number of the order, we're processing here
  **            $order_total, the total $ of the order
  ** returns: T/F
  ***************************************************************************/
   function process_payment($order_number, $order_total, &$d) {
      global $vmLogger;

	  // We must include the yourpay/linkpoint api file. 
	  require( ADMINPATH."plugins/payment/lphp.php" );

	  // Declare new linkpoint php class
	  $mylphp =& new lphp();

        global $vendor_mail, $vendor_currency, $database;

		//This is the id of the mainvendor because the payment mehthods are not vendorrelated yet
//		$hVendor_id = $_SESSION['ps_vendor_id'];
		$hVendor_id = 1;         $auth = $_SESSION['auth'];
        
        $ps_checkout = new ps_checkout;

        require_once(ADMINPATH."plugins/payment/".__CLASS__.".cfg.php");


        // Get user billing information
        $dbbt = new ps_DB;
        $qt = "SELECT * FROM `#__{vm}_user_info` WHERE user_id='".$auth["user_id"]."' AND address_type='BT'";
        $dbbt->query($qt);
        $dbbt->next_record();
        $user_info_id = $dbbt->f("user_info_id");
        if( $user_info_id != $d["ship_to_info_id"]) {
            // Get user billing information
            $dbst =& new ps_DB;
            $qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$d["ship_to_info_id"]."' AND address_type='ST'";
            $dbst->query($qt);
            $dbst->next_record();
        }
        else {
            $dbst = $dbbt;
        }


        // Start gathering the information needed for the XML transaction
        $cuname = substr($dbbt->f("first_name"), 0, 25) . " " . substr($dbbt->f("last_name"), 0, 25);

        // The following should be static for linkpoint, if not, change to the specified host (secure/staging.linkpt.net)
        $myorder["host"]       = "secure.linkpt.net";
        $myorder["port"]       = "1129";
        $myorder["keyfile"]    = $this->params->get('LP_KEYFILE');
        $myorder["configfile"] = $this->params->get('LP_LOGIN'); 

//Atlanticom Mod - Adding substitution for ampersand sign to correct
//XML rejection of linkpoint transactions including same (usually in company name).

        $myorder["name"]     = str_replace("&","",$cuname);
        $myorder["company"]  = str_replace("&","",substr($dbbt->f("company"), 0, 50));
        $myorder["address1"] = str_replace("&","",substr($dbbt->f("address_1"), 0, 60));
        $myorder["address2"] = str_replace("&","",substr($dbbt->f("address_2"), 0, 60));
        $myorder["city"]     = str_replace("&","",substr($dbbt->f("city"), 0, 40));
        $myorder["state"]    = str_replace("&","",substr($dbbt->f("state"), 0, 40));
        $myorder["zip"]      = str_replace("&","",substr($dbbt->f("zip"), 0, 20));
        $myorder["country"]  = str_replace("&","",substr($dbbt->f("country"), 0, 60));
        $myorder["phone"]    = str_replace("&","",substr($dbbt->f("phone_1"), 0, 25));
        $myorder["fax"]      = str_replace("&","",substr($dbbt->f("fax"), 0, 25));
        $myorder["email"]    = str_replace("&","",$dbbt->f("email"));

//End Atlanticom Mod

        $myorder["cardnumber"]    = $_SESSION['ccdata']['order_payment_number'];
        $myorder["cardexpmonth"]  = $_SESSION['ccdata']['order_payment_expire_month'];
        $myorder["cardexpyear"]   = substr($_SESSION['ccdata']['order_payment_expire_year'],2,2);
        $myorder["cvmindicator"]  = "provided";
        $myorder["cvmvalue"]      = $_SESSION['ccdata']['credit_card_code'];
        $myorder["chargetotal"]   = $order_total;

//Atlanticom Mod: Let's anticipate the next order_id (an auto increment field) that will be used for this
//payment if it is successful, and what the heck... let's append the word WEB to it.

        // Get last attempt
        $dbLP = new ps_DB;
        $qt = "SELECT * FROM #__{vm}_linkpoint WHERE Id=1";
        $dbLP->query($qt);
        $dbLP->next_record();
        $LP_LastAttempt = $dbLP->f("LastAttempt");
        $LP_LastAttemptParts = explode("-",$LP_LastAttempt);
        if($LP_LastAttemptParts[2] == "")
          $LP_next_suffix = "a";
        else {
          $this_char = ord($LP_LastAttemptParts[2]);
          $LP_next_suffix = chr($this_char + 1);
        }

        $dbord = new ps_DB;
        $qord = "SELECT MAX(order_id)+1 As expected_order_id FROM #__{vm}_orders";
        $dbord->query($qord);
        $dbord->next_record();
        $expected_order_id = $dbord->f("expected_order_id");
        //has this order # already been attempted and failed?
        if($LP_LastAttemptParts[1] == $expected_order_id){
          //we need to increment the attempt.
          $this_order_id = "WEB-" . $expected_order_id . "-" . $LP_next_suffix;
        } else {
          //it's a new order number
           $this_order_id = "WEB-" . $expected_order_id;       
        }
        $myorder["oid"] = $this_order_id;

        //save this attempt to the database
        $q = "UPDATE #__{vm}_linkpoint SET LastAttempt = '" . $this_order_id . "' WHERE Id=1";
        $dbLP->query($q);

//old code
        // Working on a fix for this orderid, this process seems to send "Duplicate transaction"
        // if the user made a typo the first time they entered their card number.  All in all, it works
        // but their could ba a change.
        // $myorder["oid"] = $order_number; // need to clean this up, no offence Soeren, but those order numbers are a mess.
//end old code

//END MOD

// Debugging - Let me see the output.
        //$myorder["debugging"]="true";
// debugging (can move this block around to force result)
//			$vmLogger->err( "Credit Card Processing Under Test" );
//			$d["order_payment_log"] = "Credit Card Processing Under Test";
//			$d["order_payment_log"] .= "Please Call In Your Order";
//			$d["order_payment_trans_id"] = "test1";
//			return False;


	  if ($this->params->get('LP_RECURRING') == "YES") {

	//if we are doing recurring billing, and the payments are not processed imedeately, we should run a Pre-Auth
	// This is mostly if you are offering a customer x ammount of free days for a service, if you are charging the card
	// at this time, you can uncomment the following 2 lines Pre-Auth part .
		if ($this->params->get('LP_PREAUTH') == "YES") {
  
		  $myorder["ordertype"]     = "PREAUTH";
		  // Process the PREAUTH
		  $result = $mylphp->curl_process($myorder);
  
		  if ($result["r_approved"] != "APPROVED") {   // transaction failed, print the reason
			$vmLogger->err( $result["r_error"] );
			$d["order_payment_log"] = $result["r_error"];
			$d["order_payment_log"] .= $result["r_message"];
			$d["order_payment_trans_id"] = $result["r_ordernum"];
			return False;
		  }
		}
  
		$myorder["action"] = "SUBMIT";
		$myorder["installments"] = -1;
		$myorder["periodicity"] = 'monthly';
		// We will give them 30 days free.
		$myorder["startdate"] =  date(Ymd,time()+2592000);
		$myorder["threshold"] =  3;
		$myorder["ordertype"]     = "SALE";
		
		// If everything worked out fine, then process the order here and leave the class.  Saved by the Bell
		$result = $mylphp->curl_process($myorder);
		
		if ($result["r_approved"] != "SUBMITTED")    // transaction failed, print the reason
		{
		   $vmLogger->err( $result["r_error"] );
		   $d["order_payment_log"] = $result["r_error"];
		   $d["order_payment_log"] .= $result["r_message"];
		   $d["order_payment_trans_id"] = $result["r_ordernum"];
		   return False;
		}
		else    // Success, let's return
		{
		   $d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
		   $d["order_payment_log"] = $result["r_approved"];
		   // Catch Transaction ID
		   $d["order_payment_trans_id"] = $result["r_ordernum"];
		   return True;
		}
	  }
	  else{
  		// Not recurring, just plain old sale.
  		$myorder["ordertype"]     = "SALE";

      // If everything worked out fine, then process the order.
  	  $result = $mylphp->curl_process($myorder);

  	  if ($result["r_approved"] != "APPROVED")    // transaction failed, print the reason
  	  {
  		 $vmLogger->err( $result["r_error"] . "\n" . $result["r_message"] );
  		 $d["order_payment_log"] = $result["r_error"];
  		 $d["order_payment_log"] .= $result["r_message"];
  		 $d["order_payment_trans_id"] = $result["r_ordernum"];
  		 return False;
  	  }
  	  else    // Success, let's return
  	  {
  		 $d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
  		 $d["order_payment_log"] = $result["r_approved"];
  		 // Catch Transaction ID
  		 $d["order_payment_trans_id"] = $result["r_ordernum"];
  		 return True;
  	  }
    } //close recurring or normal
  }//close function
} //close class
?>