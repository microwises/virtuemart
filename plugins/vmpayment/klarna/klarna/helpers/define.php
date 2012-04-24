<?php

/**
 *
 * a special type of Klarna
 * @author Val√©rie Isaksen
 * @version $Id:
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
defined('JPATH_BASE') or die();
if (JVM_VERSION === 2) {
    if (!defined('JPATH_VMKLARNAPLUGIN'))
	define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna');
    if (!defined('VMKLARNAPLUGINWEBROOT'))
	define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment/klarna');
    if (!defined('VMKLARNAPLUGINWEBASSETS'))
	define('VMKLARNAPLUGINWEBASSETS', JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets');
} else {
    if (!defined('JPATH_VMKLARNAPLUGIN'))
	define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment');
    if (!defined('VMKLARNAPLUGINWEBROOT'))
	define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment');
    if (!defined('VMKLARNAPLUGINWEBASSETS'))
	define('VMKLARNAPLUGINWEBASSETS', JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets');
}
if (!defined('VMKLARNA_PC_TYPE'))
    define('VMKLARNA_PC_TYPE', 'json');
if (!defined('VMKLARNA_CONFIG_FILE'))
  define('VMKLARNA_CONFIG_FILE',JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna.cfg');
?>