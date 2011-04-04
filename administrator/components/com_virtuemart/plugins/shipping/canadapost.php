<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage shipping
 * @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */
require_once ( ADMINPATH.'plugins/shipping/minixml/minixml.inc.php') ;
/**
 */
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function fetchArray( &$xmldoc, $path, $tag, $fields ) {
	$response = & $xmldoc->getElementByPath( $path ) ;
	if( ! is_object( $response ) )
		return array() ;
	
	$children = & $response->getAllChildren() ;
	
	$count = 0 ;
	$array = array() ;
	for( $i = 0 ; $i < $response->numChildren() ; $i ++ ) {
		if( $tag == $children[$i]->name() ) {
			;
			foreach( $fields as $field ) {
				$name = $children[$i]->getElement( $field ) ;
				$array[$count][$field] = $name->getValue() ;
			}
			$count ++ ;
		}
	}
	
	return $array ;
}

function fetchValue( &$xmldoc, $path ) {
	$e = $xmldoc->getElementByPath( $path ) ;
	return is_object( $e ) ? $e->getValue() : "" ;
}

class plgShippingCanadapost extends vmShippingPlugin {
	
	var $debug = false ;
	
	//		$server = "206.191.4.228",
	//		$port = 30000,
	//		$merchant_cpcid = "CPC_DEMO_XML",
	

	var $error = false ;
	var $err_msg = "" ;
	var $xml_request = "" ;
	var $xml_response = "" ;
	var $fp ; // socket handle
	

	var $xml_response_tree = array() ;
	var $shipping_methods = array() ;
	var $shipping_comment = "" ;
	
	var $to_city = "" ;
	var $to_provState = "" ;
	var $to_country = "" ;
	var $to_postal_code = "" ;
	
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.2.0
	 */
	function plgShippingCanadapost( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
		
		$this->server = $this->params->get('CP_SERVER') ;
		$this->port = $this->params->get('CP_PORT') ;
		$this->merchant_cpcid = $this->params->get('MERCHANT_CPCID') ;
		$this->_initRequestXML() ;
	}
	
	function addItem( $quantity, $weight, $length, $width, $height, $description ) {
		$this->xml_request .= "
		<item>
			<quantity>" . htmlspecialchars( $quantity ) . "</quantity>
			<weight>" . htmlspecialchars( $weight ) . "</weight>
			<length>" . htmlspecialchars( $length ) . "</length>
			<width>" . htmlspecialchars( $width ) . "</width>
			<height>" . htmlspecialchars( $height ) . "</height>
			<description>" . htmlspecialchars( $description ) . "</description>
		</item>
" ;
	}
	
	function getQuote( $city, $provstate, $country, $postal_code ) {
		$this->_shipTo( $city, $provstate, $country, $postal_code ) ;
		$this->_sendRequestXML() ;
		$this->_getResponseXML() ;
		$this->_xmlToQuote() ;
	}
	
	function _initRequestXML() {
		
		$this->xml_request = "<?phpxml version=\"1.0\" ?>
<eparcel>
	<language>" . JText::_( 'COM_VIRTUEMART_CANADAPOST_SEND_LANGUAGE_CODE' ) . "</language>
	<ratesAndServicesRequest>
		<merchantCPCID>" . $this->merchant_cpcid . "</merchantCPCID>
		<lineItems>" ;
		//					<itemsPrice>" . $p->price * $qty . "</itemsPrice>
	}
	
	// if no Postal Code input, Canada Post will return statusCode 5000 and statusMessage "XML parsing error ".
	function _shipTo( $city, $provstate, $country, $postal_code ) {
		$this->to_city = $city ;
		$this->to_provState = $provstate ;
		$this->to_country = $country ;
		$this->to_postal_code = $postal_code ;
		
		$this->xml_request .= "
		</lineItems>
" . (strlen( $this->to_city ) > 0 ? "<city>" . htmlspecialchars( $this->to_city ) . "</city>\n" : "") . (strlen( $this->to_provState ) > 0 ? "		<provOrState>" . htmlspecialchars( $this->to_provState ) . "</provOrState>\n" : "		<provOrState> </provOrState>\n") . (strlen( $this->to_country ) > 0 ? "		<country>" . htmlspecialchars( $this->to_country ) . "</country>\n" : "") . (strlen( $this->to_postal_code ) > 0 ? "		<postalCode>" . htmlspecialchars( $this->to_postal_code ) . "</postalCode>\n" : "		<postalCode> </postalCode>\n") . "
	</ratesAndServicesRequest>
</eparcel>
" ;
	}
	
	function _sendRequestXML() {
		$this->fp = fsockopen( $this->server, $this->port, $errno, $errstr, 30 ) ;
		if( ! $this->fp ) {
			die( "Open Socket Error: $errstr ($errno)<br>\n" ) ;
			$this->error = true ;
			$this->error_msg = $errstr ;
		} else
			fwrite( $this->fp, $this->xml_request ) ;
	}
	
	function _getResponseXML() {
		if( ! $this->fp )
			return ;
		while( ! feof( $this->fp ) )
			$this->xml_response .= fgets( $this->fp, 4096 ) ;
		fclose( $this->fp ) ;
	}
	
	function _xmlToQuote() {
		$xd = new MiniXMLDoc( $this->xml_response ) ;
		
		$startTag = 'eparcel/error/' ;
		$this->statusCode = fetchValue( $xd, $startTag . 'statusCode' ) ;
		if( $this->statusCode != "" ) {
			$this->error = true ;
			$this->error_msg = fetchValue( $xd, $startTag . 'statusMessage' ) ;
		} else {
			$this->error = false ;
			$startTag = 'eparcel/ratesAndServicesResponse/' ;
			$this->shipping_comment = fetchValue( $xd, $startTag . 'comment' ) ;
			$shipping_fields = array( "name" , "rate" , "shippingDate" , "deliveryDate" , "deliveryDayOfWeek" , "nextDayAM" , "packingID" ) ;
			$this->shipping_methods = fetchArray( $xd, $startTag, 'product', $shipping_fields ) ;
		}
	}
	
	function get_shipping_rate_list( &$d ) {
		global  $CURRENCY_DISPLAY ;
		
		$d["ship_to_info_id"] = JRequest::getVar( "ship_to_info_id" ) ;
		
		$dbst = new ps_DB( ) ;
		$q = "SELECT * from #__{vm}_user_info, #__{vm}_country WHERE user_info_id='" . $d["ship_to_info_id"] . "' AND ( country=country_2_code OR country=country_3_code)" ;
		$dbst->query( $q ) ;
		$dbst->next_record() ;
		
		$cart = $_SESSION['cart'] ;
		$dboi = new ps_DB( ) ;
		for( $i = 0 ; $i < $cart["idx"] ; $i ++ ) {
			$r = "SELECT product_id,product_name,product_weight,product_length,product_width " ;
			$r .= "FROM #__{vm}_product WHERE product_id='" . $cart[$i]["product_id"] . "'" ;
			$dboi->query( $r ) ;
			$dboi->next_record() ;
			
			//		echo ($cart[$i]["quantity"]." ".$dboi->f("product_weight")." ".$dboi->f("product_length")." ".$dboi->f("product_width")." ".$dboi->f("product_height")." ".$dboi->f("product_name"));
			$this->addItem( $cart[$i]["quantity"], $dboi->f( "product_weight" ) ? $dboi->f( "product_weight" ) : 0, $dboi->f( "product_length" ) ? $dboi->f( "product_length" ) : 0, $dboi->f( "product_width" ) ? $dboi->f( "product_width" ) : 0, $dboi->f( "product_height" ) ? $dboi->f( "product_height" ) : 0, $dboi->f( "product_name" ) ) ;
			//		$this->addItem( $cart[$i]["quantity"], $dboi->f("product_weight"), 10, 10, 10, $dboi->f("product_name")) ;
		}
		
		$this->getQuote( urlencode( $dbst->f( "city" ) ), urlencode( $dbst->f( "country_2_code" ) == "US" ? $dbst->f( "state" ) : "" ), $dbst->f( "country_2_code" ), $dbst->f( "zip" ) ) ;
		
		$i = 0 ;
		if( ! $this->error ) {
			?>
<table width="100%">
	<tr class="sectiontableheader">
		<th>&nbsp;</th>
		<th><?php
			echo JText::_( 'COM_VIRTUEMART_ISSHIP_LIST_CARRIER_LBL' ) ?></th>
		<th><?php
			echo JText::_( 'COM_VIRTUEMART_CANADAPOST_FORM_HANDLING_DATE' ) ?><sup>1</sup></th>
		<th><?php
			echo JText::_( 'COM_VIRTUEMART_CANADAPOST_FORM_HANDLING_LBL' ) ?><sup>2</sup></th>
	</tr>
      <?php
      		$returnArr = array();
			foreach( $this->shipping_methods as $m ) {
				
				$shipping_rate_id = urlencode( $this->_name . "|" . $m["name"] . "|" . $m["deliveryDate"] . "|" . $m["rate"] ) ;
				$_SESSION[$shipping_rate_id] = 1 ;
				
				// formatting of the shipping date returned by Canada Post
				$delivery_date = $m["deliveryDate"] ;
				if( ($timestamp = strtotime( $delivery_date )) === - 1 ) {
					$delivery_date = html_entity_decode( $m["deliveryDate"] ) ;
				} else {
					if( JText::_( 'COM_VIRTUEMART_CANADAPOST_SEND_LANGUAGE_CODE' ) == "FR" ) {
						setlocale( LC_TIME, 'fr' ) ;
						$delivery_date = strftime( '%A %d %B %Y', $timestamp ) ;
					} else {						
						$delivery_date = vmFormatDate( $timestamp ) ;
					}
				}

				// Adding taxes to the rates returned by Canada Post
				// First : add the federal tax (FT) to the shipping rate -> R * (1+FT%) = R1
				// Second : add the provincial tax (PT) to the rate R1 -> R1 * (1+PT%) = R2
				$R1 = $m["rate"] * (1 + ($this->params->get('CP_FEDERAL_TAX') / 100)) ;
				$R2 = $R1 * (1 + ($this->params->get('CP_PROVINCIAL_TAX') / 100)) ;
				
				$returnArr[] = array('shipping_rate_id' => $shipping_rate_id,
									'carrier' => 'Canada Post',
									'rate_name' =>  html_entity_decode( $m["name"] ),
									'rate' => $R2,
									'rate_tip' => 'Les frais d�exp�dition sont calcul�s en ajoutant les services de Postes Canada aux co�ts de manutention. Taxes incluses.',
									'delivery_date' => $delivery_date,
									'delivery_date_tip' => 'La date de livraison est calcul�e en ajoutant les normes de livraison de Postes Canada au d�lai d�ex�cution des commandes.',
								);
				
			} // foreach			

			// print "<hr>\n\n\n" ;
			// print "Request XML:<br><form action='http://" . CP_SERVER . ":" . CP_PORT . "' method='post' target='_blank' ><textarea name='XMLRequest' style='width:100%;height:400px;background-color:#f2f2f2'>\n" . $this->xml_request . "\n\n</textarea><br><input type='submit' value='Send to Canada Post'></form>";
			// print "<br><br>Return XML:<br><form><textarea style='width:100%;height:400px;background-color:#f2f2f2'>\n" . $this->xml_response . "\n\n</textarea></form>";

			return $returnArr ;
		
		} else {
			// Post the Error !!!
			$GLOBALS['vmLogger']->err( $this->error_msg );
			return false;
		}
	}
	
	function get_shipping_rate( &$d ) {
		$shipping_rate_id = JRequest::getVar( "shipping_rate_id" ) ;
		$is_arr = explode( "|", urldecode( urldecode( $shipping_rate_id ) ) ) ;
		$order_shipping = $is_arr[3] ;
		
		return $order_shipping ;
	}
	
	function get_shippingtax_rate() {
		global $vars ;
		// We have to do a trick here, because there are two tax rates
		$total_amount = $this->get_rate( $vars ) ;
		$R2 = $total_amount / (1 + ($this->params->get('CP_PROVINCIAL_TAX') / 100)) ;
		$R1 = $R2 / (1 + ($this->params->get('CP_FEDERAL_TAX') / 100)) ;
		$tax_amount = $total_amount - $R1 ;
		$tax_rate = $tax_amount / $total_amount ;
		
		return $tax_rate ;
	}

	
}

?>