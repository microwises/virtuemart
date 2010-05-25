<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* @version $Id: montrada.php 2225 2010-01-19 23:18:41Z rolandd $
* @package VirtueMart
* @subpackage Payment
* @copyright (C) 2006 Benjamin Schirmer
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net

* The montrada class, containing the payment processing code
*  for transactions with montrada.de
 */
class plgPaymentMontrada extends vmPaymentPlugin {
	
	var $payment_code = "MO" ;
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
	function plgPaymentMontrada( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
   
  /**************************************************************************
  ** name: process_payment()
  ** created by: Benjamin Schirmer
  ** description: process transaction with Montrada GmbH
  ** parameters: $order_number, the number of the order, we're processing here
  **            $order_total, the total $ of the order
  ** returns: TRUE if transaction was succesfull
  *			  FALSE if not
  ***************************************************************************/
   function process_payment($order_number, $order_total, &$d) {
        
        global $vendor_mail, $vendor_currency, $vmLogger;
        
		//This is the id of the mainvendor because the payment mehthods are not vendorrelated yet
//		$hVendor_id = $_SESSION['ps_vendor_id'];
		$hVendor_id = 1;         $auth = $_SESSION['auth'];
        $ps_checkout = new ps_checkout;
        
        // Get user billing information
        $db = JFactory::getDBO();
        
        $qt = "SELECT * FROM #__{vm}_user_info WHERE user_id='".$auth["user_id"]."' AND address_type='BT'";
        $dbbt->query($qt);
        $dbbt->next_record();
        $user_info_id = $dbbt->f("user_info_id");
        if( $user_info_id != $d["ship_to_info_id"]) {
            // Get user billing information
            $dbst = & JFactory::getDBO();
            $qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$d["ship_to_info_id"]."' AND address_type='ST'";
            $dbst->query($qt);
            $dbst->next_record();
        }
        else {
            $dbst = $dbbt;
        }

        $host = "posh.montrada.de";
        $port = 443;
        $path = "/posh/cmd/posh/tpl/txn_result.tpl";  

        //Montrada vars to send
        $formdata = array (
            'command' => 'authorization',
            'orderid' => substr($order_number, 0, 20),
            'creditc' => $_SESSION['ccdata']['order_payment_number'],
            'expdat' => substr($_SESSION['ccdata']['order_payment_expire_year'], 2, 2).$_SESSION['ccdata']['order_payment_expire_month'],
            'currency' => $vendor_currency,
            'amount' => $order_total*100,
            'cvcode' => $_SESSION['ccdata']['credit_card_code']
        );
        
        //build the post string
        $poststring = '';
        foreach($formdata AS $key => $val){
            $poststring .= urlencode($key) . "=" . urlencode($val) . "&";
        }
        // strip off trailing ampersand
        $poststring = substr($poststring, 0, -1);
        
        /* DEBUG Message */
        if ($this->params('DEBUG')) {
            $vmLogger->debug( wordwrap($poststring, 60, "<br/>", 1) );
        }
        
        if( function_exists( "curl_init" )) {
        
            $CR = curl_init();
            curl_setopt($CR, CURLOPT_URL, "https://".$host.$path);
            curl_setopt($CR, CURLOPT_POST, 1);
            curl_setopt($CR, CURLOPT_FAILONERROR, true); 
            curl_setopt($CR, CURLOPT_POSTFIELDS, $poststring);
            curl_setopt($CR, CURLOPT_USERPWD, $this->params('MO_USERNAME').":".$this->params('MO_PASSWORD'));
            curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
             
            // No PEER certificate validation...as we don't have 
            // a certificate file for it to authenticate the host www.ups.com against!
            curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);
            //curl_setopt($CR, CURLOPT_SSLCERT , "/usr/locale/xxxx/clientcertificate.pem");
            
            $result = curl_exec( $CR );
            
            $error = curl_error( $CR );
            if( !empty( $error )) {
              $vmLogger->err( curl_error( $CR )
                              ."<br/><span class=\"message\">".JText::_('VM_PAYMENT_INTERNAL_ERROR')." montrada.de</span>" );
              return false;
            }
            else {
                //echo $result; exit();
            }
            curl_close( $CR );
        }
        else {
        
            $fp = fsockopen("ssl://".$host, $port, $errno, $errstr, $timeout = 60);
            if(!$fp){
                //error tell us
                $vmLogger->err( "$errstr ($errno)" );
            }
            else {
    
                //send the server request
                fputs($fp, "POST $path HTTP/1.1\r\n");
                fputs($fp, "Host: $host\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ".strlen($poststring)."\r\n");
                fputs($fp, "Authorization: Basic ".base64_encode($this->params('MO_USERNAME').":".$this->params('MO_PASSWORD'))."\r\n");                
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $poststring . "\r\n\r\n");
                
                //Get the response header from the server

                $data = "";
                while (!feof($fp)) {
                   $data .= fgets ($fp, 1024);
                }
                $data = explode("\r\n\r\n", $data);
                $result = trim( $data[1] );
                
          }
        }

        /* DEBUG Message */
        if ($this->params('DEBUG'))
            $vmLogger->debug( wordwrap( urldecode($result), 60, "<br/>", 1) );
        
        // Split Response-Data
        $data = explode("&", $result);
        foreach ($data as $var)
        {
           $var = explode("=", $var);
           $key = urldecode( $var[0] );
           $value = urldecode( $var[1] );
           
           $response[$key] = $value;
        }
        
        // Array of posherr values that get displayed
        $posherr1 = array("0", "100", "2014", "2016", "2018", "2040", "2042", "2048", "2090".
                          "2092", "2094", "2202", "2204");
        /* Display these error messages (ordered by id)
            0	(Transaktion erfolgreich abgeschlossen)
            100	(Transaktion ohne Erfolg abgeschlossen)
            2014	(Kartennummer, Parameter 'creditc' falsch)
            2016	(G�ltigkeitsdatum, Parameter 'expdat' falsch)
            2018	(Kartenpr�fwert, Parameter 'cvcode' falsch)
            2040	(Anfang oder L�nge der Kartennummer falsch)
            2042	(Pr�fsumme der Kartennummer falsch)
            2048	(Karte abgelaufen)
            2090	(Bankleitzahl, Parameter 'bankcode' falsch)
            2092	(Kontonummer, Parameter 'account' falsch)
            2094	(Name, Parameter 'cname' falsch)
            2202	(Bankleitzahl unbekannt)
            2204	(Kontonummer paSst nicht zur Bankleitzahl)        
        */        
        // Array of rc values that get display if posherr=100
        $rc1 = array("000", "005", "033", "091", "096");
        // Approved - Success!
        if (isset($response['posherr']) && ($response['posherr'] == 0)) {
           $d["order_payment_log"] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').": ";
           $d["order_payment_log"] .= $response['rmsg'];
           // Catch Transaction ID
           $d["order_payment_trans_id"] = $response['trefnum'];

           return True;
           
           $db = JFactory::getDBO();
           $q = "UPDATE #__{vm}_order_payment SET order_payment_code='',order_payment_number='',order_payment_expire='' WHERE order_id=$order_number";
           $db->query($q);
           $db->next_record();
        } 
        else
        {
           if ($response['posherr'] = "") $response['posherr'] = -1;
           $vmLogger->err( JText::_('VM_PAYMENT_ERROR',false)." ($response[posherr])" );
           
           if (in_array($response['posherr'], $posherr1))
           {
                 if ($response['posherr'] == 100)
                 {
                        if (in_array($response['rc'], $rc1))
                               $vmLogger->err( $response['rmsg'] );
                 } else {
                 $vmLogger->err( $response['rmsg'] );
                 }
           }
           $d["order_payment_log"] = $response['rmsg'];
           // Catch Transaction ID
           $d["order_payment_trans_id"] = $response['retrefnr'];
           return False;
        }
   }   
}
?>