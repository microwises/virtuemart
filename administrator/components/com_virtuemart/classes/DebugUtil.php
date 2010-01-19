<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
 * VirtueMart debugging utility functions.
 *
 * VM has the ability to show a debug section in the web browser.  The old code
 * simply checked if DEBUG was enabled, and then displayed the debug section to
 * all clients.  As part of the logging enhancements, IP address-specific debug
 * is now possible - i.e., you can turn on debug output for your own IP address
 * without affecting the rest of the clients browsing your store.
 *
 * Note: Although this file only contains one function at the moment, I envision
 * future debugging enhancements down the road, and this would be a logical
 * place to put everything.
 *
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage classes
 * @author Mike Mills (mike@MikeMillsConsulting.com)
 * @copyright Copyright (C) 2008 Mike Mills. All rights reserved.
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
 * Check if we should enable debug, optionally using client IP address if
 * VM_DEBUG_IP_ADDRESS is enabled.
 *
 * @return true if debug should be on for this client, false otherwise.
 */
function vmShouldDebug()
{
    if((DEBUG == '1') && ((VM_DEBUG_IP_ENABLED != '1') || ((VM_DEBUG_IP_ENABLED == '1') && (strcmp($_SERVER['REMOTE_ADDR'], VM_DEBUG_IP_ADDRESS) == 0))))
    {
        return true;
    }

    return false;
}


?>
