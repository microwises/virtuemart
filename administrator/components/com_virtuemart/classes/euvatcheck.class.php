<?php
/**
 * VAT ID Checker
 *
 * @link http://ec.europa.eu/taxation_customs/vies/faqvies.do
 * @link http://www.csvimproved.com/
 * @author RolandD Cyber Produksi
 */
 
/**
 * Validates an EU VAT number against the VIES
 */
class VmEUVatCheck {
	
	/** @var string the URL to the VIES */
	var $viesurl = 'http://ec.europa.eu/taxation_customs/vies/api/checkVatPort?wsdl';
	
	/** @var array contains the data to pass to the VIES */
	var $param = array('countryCode' => '', 'vatNumber' => '');
	
	/** @var array contains the data to pass to the VIES */
	var $validvatid = false;
	
	/** @var boolean whether or not the field should be processed */
	var $output = false;
	
	function VmEUVatCheck($uservatid) {
		global $vmLogger;
		
		/* Load the SOAP library */
		require_once('nusoap/nusoap.php');
		
		/* Check for proxy settings */
		if (VmConfig::get('vm_proxy_url') != '') {
			if (!stristr(VmConfig::get('vm_proxy_url'), 'http')) {
				$proxyURL['host'] = VmConfig::get('vm_proxy_url');
				$proxyURL['scheme'] = 'http';
			} 
			else $proxyURL = parse_url(VmConfig::get('vm_proxy_url'));
		}
		else $proxyURL = '';
		
		/* Use the proxy and initialise the client */
		if( !empty($proxyURL) ) {
			$vmLogger->debug( 'Setting up proxy: '.$proxyURL['host'].':'.VmConfig::get('vm_proxy_port') );
			/* Proxy without authentication */
			$this->client = new nusoap_client($this->viesurl, true, $proxyURL['host'], VmConfig::get('vm_proxy_port'));
			
			/* proxy with authentication */
			if  (VmConfig::get('vm_proxy_user') != '') {
               $vmLogger->debug( 'Using proxy authentication!' );
			   $this->client = new nusoap_client($this->viesurl, true, $proxyURL['host'], VmConfig::get('vm_proxy_port'), VmConfig::get('vm_proxy_user'), VmConfig::get('vm_proxy_pass'));
            }

		}
		/* Do not use the proxy and initialise the client */
		else {
			$this->client = new nusoap_client($this->viesurl, true);
		}
		
		/* Check if there is no error on initialisation */
		$err = $this->client->getError();
		
		/* We have an error, return false since we can't check the VAT ID */
		if ($err) {
			return false;
		}
		else {
			/* See if we can use cURL */
			if (function_exists( 'curl_init' ) && function_exists( 'curl_exec' )) $this->client->setUseCurl(true);
			
			/* Set the parameters to pass to VIES */
			$countrycode = substr($uservatid, 0, 2);
			$vatnumber = substr($uservatid, 2);
			
			$param = array('countryCode' => $countrycode, 'vatNumber' => $vatnumber);
			
			/* Call the VIES to check the VAT ID */
			$this->client->call('checkVat', $param);
			
			/* Check if anything has gone wrong */
			if ($this->client->fault) {
				$vmLogger->debug( 'There was a problem with the VAT ID Check!' );
				return false;
			}
			else {
				/* See if we received an error */
				if ($this->client->getError()) {
					// There was an error, return false as we cannot check the VAT ID
					$vmLogger->debug( $this->client->getError() ); 
					return false;
				} 
				/* We have a valid response, process the response */
				else {
					$vmLogger->debug( 'EU VAT ID Check completed.' );
					/* Strip all garbage before the actual XML content */
					$xmltxt = trim(substr($this->client->response, strpos ($this->client->response, '<?xml')));
					
					/* Create an XML parser */
					$this->parser = xml_parser_create();
					xml_set_object($this->parser,$this); 
					xml_set_element_handler($this->parser,"startElement","endElement"); 
					xml_set_character_data_handler($this->parser, "characterData"); 
					xml_parse($this->parser, $xmltxt);
					xml_parser_free($this->parser);
					
					/* Data is processed, return the outcome */
					// return $this->validvatid;
				}
			}
		}
	}
	
	/**
	 * What to do when the parser finds a start element
	 *
	 * @param $parser object 
	 * @param $element_name string name of the element found
	 */
	function startElement($parser, $element_name, $attributes) {
		switch($element_name) {
			case "VALID" : $this->output = true;
				break;
			default:
				$this->output = false;
				break;
		}
	}
	
	/**
	 * What to do when the parser finds a closing element
	 *
	 * @param $parser object 
	 * @param $element_name string name of the element found
	 */
	function endElement($parser, $element_name) {
	}
	
	/**
	 * What to do when the parser finds data in the element
	 *
	 * @param $parser object 
	 * @param $xml_data string data found in between tags
	 */
	function characterData($parser, $xml_data) {
		if ($this->output) {
			if ($xml_data) {
				if ($xml_data == "false") $this->validvatid = false;
				else if ($xml_data == "true") $this->validvatid = true;
			}
			$this->output = false;
		}
	}
}
?>
