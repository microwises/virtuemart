<?php 
define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );
//if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * Virtuemart Categorie SOA Connector
 *
 * THis file generate wsdl dynamicly whith good <soap:address location = ....
 *
 * @package    mod_vm_soa
 * @subpackage classes
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */

 /** loading framework **/
include_once('VM_Commons.php');

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
$vmConfig = VmConfig::loadConfig();

//end of loading conf

$filename = $conf['wsdl_cat'];

$string = file_get_contents('VM_Categories.wsdl',"r");
// G�n�re : <body text='black'>
//$wsdlReplace = str_replace("___HOST___", $conf['URL'], $string);
$wsdlReplace = $string;

//Get URL + BASE From Joomla conf
if (empty($conf['BASESITE']) && empty($conf['URL']) ){
	//$wsdlReplace = str_replace('http://___HOST___/___BASE___/', $URL_BASE, $wsdlReplace);
	$wsdlReplace = str_replace('http://___HOST___/___BASE___/administrator/components/com_vm_soa/services/', JURI::root(false), $wsdlReplace);
}
// Else Get URL + BASE form SOA For VM Conf
else if (empty($conf['BASESITE']) && !empty($conf['URL'])){
	$wsdlReplace = str_replace("___HOST___", $conf['URL'], $string);
	$wsdlReplace = str_replace("___BASE___/", $conf['BASESITE'], $wsdlReplace);
} else {
	$wsdlReplace = str_replace("___HOST___", $conf['URL'], $string);
	$wsdlReplace = str_replace("___BASE___", $conf['BASESITE'], $wsdlReplace);
}
$wsdlReplace = str_replace("___SERVICE___", $conf['EP_cat'], $wsdlReplace);


if ($vmConfig->get('soap_ws_cat_on')==1){
	header('Content-type: text/xml; charset=UTF-8'); 
	header("Content-Length: ".strlen($wsdlReplace));
	echo $wsdlReplace;
}
else{
	echo "This Web Service (Categories) is disabled";
}
?>