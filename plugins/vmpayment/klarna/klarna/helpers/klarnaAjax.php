<?php
// Setup the joomla variable and constants
//defined('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of Klarna
 * @author Valérie Isaksen
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

// Import joomlas xmlrpc library
jimport('phpxmlrpc.xmlrpc');



$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
$session =& JFactory::getSession();

$cData = "NOR"; //KlarnaHandler::getCountryData($_SESSION['auth']['country']);
//$iMode = (KLARNA_MODE == 1) ? Klarna::LIVE : Klarna::BETA;
$iMode=Klarna::BETA;
$oKlarna = new Klarna_virtuemart();
//$oKlarna->config($cData['eid'], $cData['secret'], $cData['country'], $cData['language'], $cData['currency'], $iMode, KLARNA_PC_TYPE, KlarnaHandler::getPCUri(), false);
$oKlarna->config($cData['eid'], $cData['secret'], $cData['country'], $cData['language'], $cData['currency'], $iMode, 'mysql', KlarnaHandler::getPCUri(), false);
// TODO
$web_root    = JUri::base() . 'plugins/vmpayment/klarana/klarna/klarna_api/checkout/';
define ('KLARNA_SPEC_ACTIVE_TEMPLATE', 'default'); // TODO ADDED by vlc
$kAjax = new KlarnaAjax ($oKlarna, $cData['eid'], dirname(__FILE__) . '/checkout/', $web_root);
$kAjax->__setTemplate(KlarnaHandler::getLocalTemplate(KLARNA_SPEC_ACTIVE_TEMPLATE));

$dispatcher = new KlarnaDispatcher($kAjax);
$dispatcher->charset = 'ISO-8859-1';
$dispatcher->dispatch ();
