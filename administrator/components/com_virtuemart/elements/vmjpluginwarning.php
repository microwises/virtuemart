<?php

/**
 *
 * @package	VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
class JElementVmjpluginwarning extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'jpluginwarning';

    function fetchElement($name, $value, &$node, $control_name) {

        $option = JRequest::getWord('option');
        if ($option == 'com_virtuemart')
            return null;
        else
            return "<strong>Please configure VirtueMart Shipping or Payment Plugins inside VirtueMart component</strong>";
    }

}