<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );


/**
 * VirtueMart Logging Initialization.
 *
 * This file is included only once, inside virtuemart_parser.php.  External
 * applications that run outside of VirtueMart - such as PayPal notification
 * (notify.php) should also include this file.
 *
 * Logging has been modified so that instead of just a single "vmLogger",
 * which used to only log to the display, there are now three loggers:
 *
 * $vmLogger - This used to be the display logger; it is now a composite
 *             logger that forwards log messages to both the display _and_
 *             file loggers.  Note that the composite logger itself does
 *             not check message priorities against the logging level; each
 *             child logger does that itself.  This means that the display
 *             logger can be set to "WARNING" and the file logger can
 *             be set to "DEBUG".  Then, if you do something like:
 *            
 *                 $vmLogger->debug("This is a debug message.");
 *
 *             ...the message will get logged by the file logger (because
 *             it's log level is DEBUG), but NOT by the display logger
 *             (because it's log level is WARNING.)
 *
 *
 * $vmDisplayLogger - The actual display logger.  Note that, due to the
 *                    way the display logger is implemented, log messages
 *                    with a priority >PEAR_LOG_DEBUG will always go to
 *                    the display.  Debug-priority messages will only be
 *                    shown if the DEBUG option is enabled in the VM admin
 *                    configuration panel.  Also, display logging can now
 *                    be restricted by client IP address, also within the
 *                    VM admin configuration panel.
 *
 *
 * $vmFileLogger    - The file logger.  Note that, if file logging is
 *                    disabled, a "null" logger will be instantiated in
 *                    it's place.  This is so that code using vmFileLogger
 *                    will continue to function without error, and without
 *                    having to actually test if the file logger is enabled.
 *
 *                    If file logging is enabled, but the logger cannot be
 *                    created, then a message will be written to the
 *                    display (using the vmDisplayLogger), and then a
 *                    "null" logger will be created in it's place (for the
 *                    same reason as noted above.)
 *
 *                    The log file can be enabled/disabled via the VM admin
 *                    config panel; this is also where the log file name is
 *                    specified, along with the log level and formatting
 *                    options (such as inclusion of remote IP address,
 *                    username [if logged in], and VM session ID.)
 *
 * Note that, by my reasoning, pretty much all logging output intended for
 * the display should also go to the file.  So, you would normally use
 * $vmLogger instead of just $vmDisplayLogger.
 * However, there are many cases where you would only want to log to
 * file and not have the output go to display.  In these cases, you would
 * use the $vmFileLogger.
 *
 * All three loggers are available via $GLOBALS[] as:
 *
 *     $GLOBALS['vmLogger']            //The composite logger
 *     $GLOBALS['vmDisplayLogger']     //The display logger
 *     $GLOBALS['vmFileLogger']        //The file logger
 *
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage Log
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

// bass28 8/24/09 - Hack to keep this code working without virtuemart.cfg
$classPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'classes'.DS;

require_once($classPath."Log/Log.php");
require_once($classPath."Log/composite.php");
require_once($classPath."Log/display.php");
require_once($classPath."Log/file.php");


$vmLogger        = null;
$vmDisplayLogger = null;
$vmFileLogger    = null;
    

/* The $vmLogIdentifier is intended to separate different sources of logging
   information - such as VirtueMart itself, or external apps like the PayPal
   notification script (notify.php).
*/
if(!isset($vmLogIdentifier))
    $vmLogIdentifier = '';

/* The existing display logger starts out with a log level of PEAR_LOG_TIP.
   However, no debug-levwel output will be sent to the display unless the DEBUG
   option is turned on inside the VM admin configuration panel. */

$vmDisplayLoggerConf = array( 'buffering' => true );
$vmDisplayLogger = &vmLog::singleton('display', '', $vmLogIdentifier, $vmDisplayLoggerConf, PEAR_LOG_TIP);


/* Use a null logger if file logging is disabled or if there is an error.  This
   is so that code using the logger will continue to work without problem. */

if(VM_LOGFILE_ENABLED != '1')
    $vmFileLogger = &vmLog::singleton('null');
else {
    $vmFileLoggerConf = array('mode' => 0600, 'timeFormat' => '%X %x', 'lineFormat' => VM_LOGFILE_FORMAT);
    $vmFileLogger = &vmLog::singleton('file', VM_LOGFILE_NAME, $vmLogIdentifier, $vmFileLoggerConf, vmLog::stringToPriorityPEAR(VM_LOGFILE_LEVEL));

    if($vmFileLogger == false)
    {
        $vmDisplayLogger->warning(JText::_VM_ADMIN_CFG_LOGFILE_ERROR);
        $vmFileLogger = &vmLog::singleton('null');
    }
}

$vmLogger = &vmLog::singleton('composite');

$vmLogger->addChild($vmDisplayLogger);
$vmLogger->addChild($vmFileLogger);
$vmLogger->open();
$GLOBALS['vmLogger'] =& $vmLogger;
$GLOBALS['vmDisplayLogger'] =& $vmDisplayLogger;
$GLOBALS['vmFileLogger'] =& $vmFileLogger;


?>
