<?php
/**
 * @version $Id: epay.php 2281 2010-01-31 19:02:47Z Milbo $
 * @package VirtueMart
 * @subpackage Payment
 * @copyright (C) 2007-2008 Thomas Knudsen
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * VirtueMart is Free Software.
 * VirtueMart comes with absolute no warranty.
 *
 * www.virtuemart.net

 * The ps_epay class, containing the payment processing code
 *  for transactions with PBS, Nordea, Danske Bank, eWire etc 
 *  supported by the ePay Payment Gateway (www.epay.dk)
 */

class plgPaymentEpay extends vmPaymentPlugin {
	
	var $payment_code = "EPAY" ;
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
	function plgPaymentEpay( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	
	function showPaymentForm( &$db, $user, $dbbt ) {

		echo JText::_( 'VM_CHECKOUT_EPAY_PAYMENT_CHECKOUT_HEADER' ) ;
		?>
<script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script>
<script type="text/javascript">
function printCard(cardId) {
	document.write ("<table border=0 cellspacing=10 cellpadding=10>");
	switch (cardId) {
		case 1: document.write ("<input type=hidden name=cardtype value=1>"); break;
		case 2: document.write ("<input type=hidden name=cardtype value=2>"); break;
		case 3: document.write ("<input type=hidden name=cardtype value=3>"); break;
		case 4: document.write ("<input type=hidden name=cardtype value=4>"); break;
		case 5: document.write ("<input type=hidden name=cardtype value=5>"); break;
		case 6: document.write ("<input type=hidden name=cardtype value=6>"); break;
		case 7: document.write ("<input type=hidden name=cardtype value=7>"); break;
		case 8: document.write ("<input type=hidden name=cardtype value=8>"); break;
		case 9: document.write ("<input type=hidden name=cardtype value=9>"); break;
		case 10: document.write ("<input type=hidden name=cardtype value=10>"); break;
		case 12: document.write ("<input type=hidden name=cardtype value=12>"); break;
		case 13: document.write ("<input type=hidden name=cardtype value=13>"); break;
		case 14: document.write ("<input type=hidden name=cardtype value=14>"); break;
		case 15: document.write ("<input type=hidden name=cardtype value=15>"); break;
		case 16: document.write ("<input type=hidden name=cardtype value=16>"); break;
		case 17: document.write ("<input type=hidden name=cardtype value=17>"); break;
		case 18: document.write ("<input type=hidden name=cardtype value=18>"); break;
		case 19: document.write ("<input type=hidden name=cardtype value=19>"); break;
		case 21: document.write ("<input type=hidden name=cardtype value=21>"); break;
		case 22: document.write ("<input type=hidden name=cardtype value=22>"); break;
	}
	document.write ("</table>");
}
</script>
<form action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post" name="ePay" target="ePay_window" id="ePay">
	<input type="hidden" name="merchantnumber" value="<?php echo $this->params->get('EPAY_MERCHANTNUMBER') ?>" />
	<input type="hidden" name="amount" value="<?php echo round( $db->f( "order_total" ) * 100, 2 ) ?>" /> 
	<input 	type="hidden" name="currency" value="<?php echo $this->calculateePayCurrency( $db->f('order_id') ) ?>" /> 
	<input type="hidden" name="orderid" value="<?php echo $order_id ?>" />
	<input type="hidden" name="ordretext" value="" />
<?php
		if( $this->params->get('EPAY_CALLBACK') == "1" ) {
			echo '<input type="hidden" name="callbackurl" value="' . JURI::base() . '/index.php?page=checkout.epay_result&accept=1&sessionid=' . $sessionid . '&option=com_virtuemart&Itemid=1" />' ;
		}
		?>
<input type="hidden" name="accepturl"
	value="<?php
		echo JURI::base() ?>/index.php?page=checkout.epay_result&accept=1&sessionid=<?php
		echo $sessionid ?>&option=com_virtuemart&Itemid=1" />
<input type="hidden" name="declineurl"
	value="<?php
		echo JURI::base() ?>/index.php?page=checkout.epay_result&accept=0&sessionid=<?php
		echo $sessionid ?>&option=com_virtuemart&Itemid=1" />
<input type="hidden" name="group" value="<?php
		echo $this->params->get('EPAY_GROUP') ?>" /> <input
	type="hidden" name="instant" value="<?php
		echo $this->params->get('EPAY_INSTANT_CAPTURE') ?>" />
<input type="hidden" name="language" value="<?php
		echo $this->params->get('EPAY_LANGUAGE') ?>" />
<input type="hidden" name="authsms" value="<?php
		echo $this->params->get('EPAY_AUTH_SMS') ?>" />
<input type="hidden" name="authmail"
	value="<?php
		echo $this->params->get('EPAY_AUTH_MAIL') . (strlen( $this->params->get('EPAY_AUTH_MAIL') ) > 0 && $this->params->get('EPAY_AUTHEMAILCUSTOMER') == 1 ? ";" : "") . ($this->params->get('EPAY_AUTHEMAILCUSTOMER') == 1 ? $user->email : "") ;
		?>" />
<input type="hidden" name="windowstate"
	value="<?php
		echo $this->params->get('EPAY_WINDOW_STATE') ?>" /> <input type="hidden"
	name="use3D" value="<?php
		echo $this->params->get('EPAY_3DSECURE') ?>" /> <input type="hidden"
	name="addfee" value="<?php
		echo $this->params->get('EPAY_ADDFEE') ?>" /> <input type="hidden"
	name="subscription" value="<?php
		echo $this->params->get('EPAY_SUBSCRIPTION') ?>" /> <input
	type="hidden" name="MD5Key"
	value="<?php
		if( $this->params->get('EPAY_MD5_TYPE') == 2 )
			echo md5( $this->calculateePayCurrency( $order_id ) . round( $db->f( "order_total" ) * 100, 2 ) . $order_id . $this->params->get('EPAY_MD5_KEY') ) ?>">
<?php
		$cardtypes = explode(',', $this->params->get('EPAY_CARDTYPES'));
		foreach( $cardtypes as $cardnum ) {
			echo "<script type=\"text/javascript\">printCard($cardnum)</script>" ;
		}
		?>
</form>
<script type="text/javascript">open_ePay_window();</script>
<br>
<table border="0" width="100%">
	<tr>
		<td><input type="button" onClick="open_ePay_window()"
			value="<?php
		echo JText::_( 'VM_CHECKOUT_EPAY_BUTTON_OPEN_WINDOW' ) ?>"></td>
		<td width="100%" id="flashLoader"></td>
	</tr>
</table>
<br />
<br />
<br />
<?php
		echo JText::_( 'VM_CHECKOUT_EPAY_PAYMENT_CHECKOUT_FOOTER' ) ?>
<br />
<br />
<img alt="ePay Logo" src="components/com_virtuemart/shop_image/ps_image/epay_images/epay_logo.gif" border="0" />
&nbsp;&nbsp;&nbsp;
<img src="components/com_virtuemart/shop_image/ps_image/epay_images/mastercard_securecode.gif" alt="Mastercard" 	border="0" />
&nbsp;&nbsp;&nbsp;
<img src="components/com_virtuemart/shop_image/ps_image/epay_images/pci.gif" alt="PCI" border="0" />
&nbsp;&nbsp;&nbsp;
<img src="components/com_virtuemart/shop_image/ps_image/epay_images/verisign_secure.gif" alt="Verisign -secure" border="0" />
&nbsp;&nbsp;&nbsp;
<img src="components/com_virtuemart/shop_image/ps_image/epay_images/visa_secure.gif" alt="Visa -secure" border="0" />
&nbsp;&nbsp;&nbsp;
<?php
	}
	function calculateePayCurrency( $order_id ) {
		//$db = & new ps_DB( ) ;
		$db = JFactory::getDBO();
		$currency_code = "208" ;
		$q = "SELECT order_currency FROM #__vm_orders where order_id = " . $order_id ;
		$db->query( $q ) ;
		if( $db->next_record() ) {
			$currency_code = plgPaymentEpay::get_iso_code( $db->f( "order_currency" ) ) ;
		}
		return $currency_code ;
	}
	//
	// The complete list of country currency codes. 
	//
	function get_iso_code( $code ) {
		switch( $code) {
			case 'ADP' :
				return '020' ;
			break ;
			case 'AED' :
				return '784' ;
			break ;
			case 'AFA' :
				return '004' ;
			break ;
			case 'ALL' :
				return '008' ;
			break ;
			case 'AMD' :
				return '051' ;
			break ;
			case 'ANG' :
				return '532' ;
			break ;
			case 'AOA' :
				return '973' ;
			break ;
			case 'ARS' :
				return '032' ;
			break ;
			case 'AUD' :
				return '036' ;
			break ;
			case 'AWG' :
				return '533' ;
			break ;
			case 'AZM' :
				return '031' ;
			break ;
			case 'BAM' :
				return '977' ;
			break ;
			case 'BBD' :
				return '052' ;
			break ;
			case 'BDT' :
				return '050' ;
			break ;
			case 'BGL' :
				return '100' ;
			break ;
			case 'BGN' :
				return '975' ;
			break ;
			case 'BHD' :
				return '048' ;
			break ;
			case 'BIF' :
				return '108' ;
			break ;
			case 'BMD' :
				return '060' ;
			break ;
			case 'BND' :
				return '096' ;
			break ;
			case 'BOB' :
				return '068' ;
			break ;
			case 'BOV' :
				return '984' ;
			break ;
			case 'BRL' :
				return '986' ;
			break ;
			case 'BSD' :
				return '044' ;
			break ;
			case 'BTN' :
				return '064' ;
			break ;
			case 'BWP' :
				return '072' ;
			break ;
			case 'BYR' :
				return '974' ;
			break ;
			case 'BZD' :
				return '084' ;
			break ;
			case 'CAD' :
				return '124' ;
			break ;
			case 'CDF' :
				return '976' ;
			break ;
			case 'CHF' :
				return '756' ;
			break ;
			case 'CLF' :
				return '990' ;
			break ;
			case 'CLP' :
				return '152' ;
			break ;
			case 'CNY' :
				return '156' ;
			break ;
			case 'COP' :
				return '170' ;
			break ;
			case 'CRC' :
				return '188' ;
			break ;
			case 'CUP' :
				return '192' ;
			break ;
			case 'CVE' :
				return '132' ;
			break ;
			case 'CYP' :
				return '196' ;
			break ;
			case 'CZK' :
				return '203' ;
			break ;
			case 'DJF' :
				return '262' ;
			break ;
			case 'DKK' :
				return '208' ;
			break ;
			case 'DOP' :
				return '214' ;
			break ;
			case 'DZD' :
				return '012' ;
			break ;
			case 'ECS' :
				return '218' ;
			break ;
			case 'ECV' :
				return '983' ;
			break ;
			case 'EEK' :
				return '233' ;
			break ;
			case 'EGP' :
				return '818' ;
			break ;
			case 'ERN' :
				return '232' ;
			break ;
			case 'ETB' :
				return '230' ;
			break ;
			case 'EUR' :
				return '978' ;
			break ;
			case 'FJD' :
				return '242' ;
			break ;
			case 'FKP' :
				return '238' ;
			break ;
			case 'GBP' :
				return '826' ;
			break ;
			case 'GEL' :
				return '981' ;
			break ;
			case 'GHC' :
				return '288' ;
			break ;
			case 'GIP' :
				return '292' ;
			break ;
			case 'GMD' :
				return '270' ;
			break ;
			case 'GNF' :
				return '324' ;
			break ;
			case 'GTQ' :
				return '320' ;
			break ;
			case 'GWP' :
				return '624' ;
			break ;
			case 'GYD' :
				return '328' ;
			break ;
			case 'HKD' :
				return '344' ;
			break ;
			case 'HNL' :
				return '340' ;
			break ;
			case 'HRK' :
				return '191' ;
			break ;
			case 'HTG' :
				return '332' ;
			break ;
			case 'HUF' :
				return '348' ;
			break ;
			case 'IDR' :
				return '360' ;
			break ;
			case 'ILS' :
				return '376' ;
			break ;
			case 'INR' :
				return '356' ;
			break ;
			case 'IQD' :
				return '368' ;
			break ;
			case 'IRR' :
				return '364' ;
			break ;
			case 'ISK' :
				return '352' ;
			break ;
			case 'JMD' :
				return '388' ;
			break ;
			case 'JOD' :
				return '400' ;
			break ;
			case 'JPY' :
				return '392' ;
			break ;
			case 'KES' :
				return '404' ;
			break ;
			case 'KGS' :
				return '417' ;
			break ;
			case 'KHR' :
				return '116' ;
			break ;
			case 'KMF' :
				return '174' ;
			break ;
			case 'KPW' :
				return '408' ;
			break ;
			case 'KRW' :
				return '410' ;
			break ;
			case 'KWD' :
				return '414' ;
			break ;
			case 'KYD' :
				return '136' ;
			break ;
			case 'KZT' :
				return '398' ;
			break ;
			case 'LAK' :
				return '418' ;
			break ;
			case 'LBP' :
				return '422' ;
			break ;
			case 'LKR' :
				return '144' ;
			break ;
			case 'LRD' :
				return '430' ;
			break ;
			case 'LSL' :
				return '426' ;
			break ;
			case 'LTL' :
				return '440' ;
			break ;
			case 'LVL' :
				return '428' ;
			break ;
			case 'LYD' :
				return '434' ;
			break ;
			case 'MAD' :
				return '504' ;
			break ;
			case 'MDL' :
				return '498' ;
			break ;
			case 'MGF' :
				return '450' ;
			break ;
			case 'MKD' :
				return '807' ;
			break ;
			case 'MMK' :
				return '104' ;
			break ;
			case 'MNT' :
				return '496' ;
			break ;
			case 'MOP' :
				return '446' ;
			break ;
			case 'MRO' :
				return '478' ;
			break ;
			case 'MTL' :
				return '470' ;
			break ;
			case 'MUR' :
				return '480' ;
			break ;
			case 'MVR' :
				return '462' ;
			break ;
			case 'MWK' :
				return '454' ;
			break ;
			case 'MXN' :
				return '484' ;
			break ;
			case 'MXV' :
				return '979' ;
			break ;
			case 'MYR' :
				return '458' ;
			break ;
			case 'MZM' :
				return '508' ;
			break ;
			case 'NAD' :
				return '516' ;
			break ;
			case 'NGN' :
				return '566' ;
			break ;
			case 'NIO' :
				return '558' ;
			break ;
			case 'NOK' :
				return '578' ;
			break ;
			case 'NPR' :
				return '524' ;
			break ;
			case 'NZD' :
				return '554' ;
			break ;
			case 'OMR' :
				return '512' ;
			break ;
			case 'PAB' :
				return '590' ;
			break ;
			case 'PEN' :
				return '604' ;
			break ;
			case 'PGK' :
				return '598' ;
			break ;
			case 'PHP' :
				return '608' ;
			break ;
			case 'PKR' :
				return '586' ;
			break ;
			case 'PLN' :
				return '985' ;
			break ;
			case 'PYG' :
				return '600' ;
			break ;
			case 'QAR' :
				return '634' ;
			break ;
			case 'ROL' :
				return '642' ;
			break ;
			case 'RUB' :
				return '643' ;
			break ;
			case 'RUR' :
				return '810' ;
			break ;
			case 'RWF' :
				return '646' ;
			break ;
			case 'SAR' :
				return '682' ;
			break ;
			case 'SBD' :
				return '090' ;
			break ;
			case 'SCR' :
				return '690' ;
			break ;
			case 'SDD' :
				return '736' ;
			break ;
			case 'SEK' :
				return '752' ;
			break ;
			case 'SGD' :
				return '702' ;
			break ;
			case 'SHP' :
				return '654' ;
			break ;
			case 'SIT' :
				return '705' ;
			break ;
			case 'SKK' :
				return '703' ;
			break ;
			case 'SLL' :
				return '694' ;
			break ;
			case 'SOS' :
				return '706' ;
			break ;
			case 'SRG' :
				return '740' ;
			break ;
			case 'STD' :
				return '678' ;
			break ;
			case 'SVC' :
				return '222' ;
			break ;
			case 'SYP' :
				return '760' ;
			break ;
			case 'SZL' :
				return '748' ;
			break ;
			case 'THB' :
				return '764' ;
			break ;
			case 'TJS' :
				return '972' ;
			break ;
			case 'TMM' :
				return '795' ;
			break ;
			case 'TND' :
				return '788' ;
			break ;
			case 'TOP' :
				return '776' ;
			break ;
			case 'TPE' :
				return '626' ;
			break ;
			case 'TRL' :
				return '792' ;
			break ;
			case 'TRY' :
				return '949' ;
			break ;
			case 'TTD' :
				return '780' ;
			break ;
			case 'TWD' :
				return '901' ;
			break ;
			case 'TZS' :
				return '834' ;
			break ;
			case 'UAH' :
				return '980' ;
			break ;
			case 'UGX' :
				return '800' ;
			break ;
			case 'USD' :
				return '840' ;
			break ;
			case 'UYU' :
				return '858' ;
			break ;
			case 'UZS' :
				return '860' ;
			break ;
			case 'VEB' :
				return '862' ;
			break ;
			case 'VND' :
				return '704' ;
			break ;
			case 'VUV' :
				return '548' ;
			break ;
			case 'XAF' :
				return '950' ;
			break ;
			case 'XCD' :
				return '951' ;
			break ;
			case 'XOF' :
				return '952' ;
			break ;
			case 'XPF' :
				return '953' ;
			break ;
			case 'YER' :
				return '886' ;
			break ;
			case 'YUM' :
				return '891' ;
			break ;
			case 'ZAR' :
				return '710' ;
			break ;
			case 'ZMK' :
				return '894' ;
			break ;
			case 'ZWD' :
				return '716' ;
			break ;
		}
		//
		// As default return 208 for Danish Kroner
		//
		return '208' ;
	}

}
