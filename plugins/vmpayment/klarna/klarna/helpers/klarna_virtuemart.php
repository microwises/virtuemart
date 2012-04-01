<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

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

define('KLARNA_MODULE_VERSION', '5.0.3');
if (!class_exists('Klarna'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
class Klarna_virtuemart extends Klarna {

    public function __construct() {
        $this->VERSION = 'PHP:VirtueMart2:'.KLARNA_MODULE_VERSION.':r74';
        Klarna::$debug =  false;
    }
}

