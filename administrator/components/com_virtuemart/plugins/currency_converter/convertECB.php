<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* ECB Currency Converter Module
* 
* @version $Id: convertECB.php 1510 2008-08-08 19:11:42Z soeren_nb $
* @package VirtueMart
* @subpackage classes
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
 * This class uses the currency rates provided by an XML file from the European Central Bank
 * Requires cURL or allow_url_fopen
 */
class convertECB {
	
	var $archive = true;
	var $last_updated = '';
	
	var $document_address = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
	
	var $info_address = 'http://www.ecb.int/stats/eurofxref/';
	var $supplier = 'European Central Bank';
	
	/**
	 * Converts an amount from one currency into another using
	 * the rate conversion table from the European Central Bank
	 *
	 * @param float $amountA
	 * @param string $currA defaults to $vendor_currency
	 * @param string $currB defaults to $GLOBALS['product_currency'] (and that defaults to $vendor_currency)
	 * @return mixed The converted amount when successful, false on failure
	 */
	function convert( $amountA, $currA='', $currB='' ) {
		global $mosConfig_cachepath, $mosConfig_live_site, $mosConfig_absolute_path,
				$mosConfig_offset, $vendor_currency, $vmLogger;
	
		// global $vendor_currency is DEFAULT!
		if( !$currA ) {
			$currA = $vendor_currency;
		}
		if( !$currB ) {
			$currB = $GLOBALS['product_currency'];
		}
		// If both currency codes match, do nothing
		if( $currA == $currB ) {		
			return $amountA;
		}
		if( $GLOBALS['converter_array'] == '') {
			setlocale(LC_TIME, "en-GB");
			$now = time() + 3600; // Time in ECB (Germany) is GMT + 1 hour (3600 seconds)
			if (date("I")) {
				$now += 3600; // Adjust for daylight saving time
			}
			$weekday_now_local = gmdate('w', $now); // week day, important: week starts with sunday (= 0) !!
			$date_now_local = gmdate('Ymd', $now);
			$time_now_local = gmdate('Hi', $now);
			$time_ecb_update = '1415';
			if( is_writable($mosConfig_cachepath) ) {
				$store_path = $mosConfig_cachepath;
			}
			else {
				$store_path = $mosConfig_absolute_path."/media";
			}
			  
			$archivefile_name = $store_path.'/daily.xml';
			$ecb_filename = $this->document_address;
			$val = '';

		
			if(file_exists($archivefile_name) && filesize( $archivefile_name ) > 0 ) {
			  	// timestamp for the Filename
			  	$file_datestamp = date('Ymd', filemtime($archivefile_name)); 
			  	
		    	// check if today is a weekday - no updates on weekends
		    	if( date( 'w' ) > 0 && date( 'w' ) < 6 
		    		// compare filedate and actual date
		    		&& $file_datestamp != $date_now_local
		    		// if localtime is greater then ecb-update-time go on to update and write files
		    		&& $time_now_local > $time_ecb_update) {
		    		$curr_filename = $ecb_filename;	
		    	}
		    	else {
				  	$curr_filename = $archivefile_name;
		    		$this->last_updated = $file_datestamp;
				  	$this->archive = false;	
		    	}
			}
			else {
				$curr_filename = $ecb_filename;
			}
			  
			if( !is_writable( $store_path )) {
			  $this->archive = false;
			  $vmLogger->debug( "The file $archivefile_name can't be created. The directory $store_path is not writable" );
			}
			if( $curr_filename == $ecb_filename ) {
				// Fetch the file from the internet
				require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'connectionTools.class.php');
				$contents = VmConnection::handleCommunication( $curr_filename );
				$this->last_updated = date('Ymd');
			}
			else {
				$contents = @file_get_contents( $curr_filename );
			}
			if( $contents ) {
				// if archivefile does not exist
				if( $this->archive ) {
					// now write new file
					file_put_contents( $archivefile_name, $contents );
				}
		
				$contents = str_replace ("<Cube currency='USD'", " <Cube currency='EUR' rate='1'/> <Cube currency='USD'", $contents);
				
				/* XML Parsing */
				require_once( $mosConfig_absolute_path. '/includes/domit/xml_domit_lite_include.php' );
				$xmlDoc =& new DOMIT_Lite_Document();
				if( !$xmlDoc->parseXML( $contents, false, true ) ) {
					$vmLogger->err( 'Failed to parse the Currency Converter XML document.');
					$GLOBALS['product_currency'] = $vendor_currency;
					return $amountA;
				}
				
				$currency_list = $xmlDoc->getElementsByTagName( "Cube" );
				// Loop through the Currency List
				for ($i = 0; $i < $currency_list->getLength(); $i++) {
					$currNode =& $currency_list->item($i);
					$currency[$currNode->getAttribute("currency")] = $currNode->getAttribute("rate");
					unset( $currNode );
				}
				$GLOBALS['converter_array'] = $currency;
			}
			else {
				$GLOBALS['converter_array'] = -1;
				$vmLogger->err( 'Failed to retrieve the Currency Converter XML document.');
				$GLOBALS['product_currency'] = $vendor_currency;
				return $amountA;
			}
		}
		$valA = isset( $GLOBALS['converter_array'][$currA] ) ? $GLOBALS['converter_array'][$currA] : 1;
		$valB = isset( $GLOBALS['converter_array'][$currB] ) ? $GLOBALS['converter_array'][$currB] : 1;
		
		$val = $amountA * $valB / $valA;
		//$vmLogger->debug('Converted '.$amountA.' '.$currA.' to '.$val.' '.$currB);
		
		return $val;
	} // end function convertecb
}
?>
