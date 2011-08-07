<?php 
define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );
//if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * Virtuemart Users SOA Connector
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
define('DS', DIRECTORY_SEPARATOR);

$soa_dir 	= dirname(__FILE__);
$jpath 		= realpath( dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.'' );
$jadminpath = realpath( dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'' );

define('JPATH_BASE',$jadminpath );

if (file_exists(JPATH_BASE . '/includes/defines.php')) {
	include_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE.'/includes/framework.php';
require_once JPATH_BASE.'/includes/helper.php';
require_once JPATH_BASE.'/includes/toolbar.php';

// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('site');

// Initialise the application.
$app->initialise();

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'tables');

global $mosConfig_live_site;


$mosConfig_live_site = JURI::root(false);
$URL_BASE;
if( $mosConfig_live_site[strlen( $mosConfig_live_site)-1] == '/' ) {
	$URL_BASE = $mosConfig_live_site;
}
else {
	$URL_BASE = $mosConfig_live_site.'/';
}

include('../vm_soa_conf.php');
//end of loading conf

$filename = $conf['wsdl_users'];

$string = file_get_contents('VM_Users.wsdl',"r");

$wsdlReplace = $string;

//Get URL + BASE From Joomla conf
if (empty($conf['BASESITE']) && empty($conf['URL']) ){
	$wsdlReplace = str_replace('http://___HOST___/___BASE___/administrator/components/com_vm_soa/services/', $URL_BASE, $wsdlReplace);
}
// Else Get URL + BASE form SOA For VM Conf
else if (empty($conf['BASESITE']) && !empty($conf['URL'])){
	$wsdlReplace = str_replace("___HOST___", $conf['URL'], $string);
	$wsdlReplace = str_replace("___BASE___/", $conf['BASESITE'], $wsdlReplace);
} else {
	$wsdlReplace = str_replace("___HOST___", $conf['URL'], $string);
	$wsdlReplace = str_replace("___BASE___", $conf['BASESITE'], $wsdlReplace);
}
$wsdlReplace = str_replace("___SERVICE___", $conf['EP_users'], $wsdlReplace);


//$taille = filesize($filename);
//$file = readfile($filename);
header('Content-type: text/xml; charset=UTF-8'); 
header("Content-Length: ".(strlen($wsdlReplace)+1));

if ($conf['users_actif']=="on"){
	echo $wsdlReplace;
}
else{
	echo "This Web Service (Users) is disabled";
}
?>