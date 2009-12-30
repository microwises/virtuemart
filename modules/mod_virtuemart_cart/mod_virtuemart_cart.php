<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart MiniCart Module
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2004-2007 soeren - All Rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

//Start of routine output correct div to enable ajax update to display correctly
echo '<div class="vmCartModule">';

global $VM_LANG, $sess, $mm_action_url;

$_SESSION['vmUseGreyBox'] = $params->get( 'vmEnableGreyBox');
$_SESSION['vmCartDirection'] = $params->get( 'vmCartDirection');


include (PAGEPATH.'shop.basket_short.php') ; 

echo "</div>";
?>