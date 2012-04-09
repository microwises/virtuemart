<?php

/**
 *
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
 * http://virtuemart.org
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Fetches pclasses
 *
 */
if (!class_exists('Klarna'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
if (!class_exists('klarna_virtuemart'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');
if (!class_exists('KlarnaHandler'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class JElementKlarnaPclasses extends JElement {

    /**
     * Element name
     *
     * @access       protected
     * @var          string
     */
    var $_name = 'KlarnaPclasses';

    function fetchElement($name, $value, &$node, $control_name) {
//return;
//TODO SELFCALL AJAX
	// Base name of the HTML control.
	$ctrl = $control_name . '[' . $name . ']';
	JHTML::script('klarna_admin.js', VMKLARNAPLUGINWEBASSETS . 'js/', false);
	JHTML::stylesheet('klarna_admin.css', VMKLARNAPLUGINWEBASSETS. 'css/', false);
	$cid = jrequest::getvar('cid',null,'array');
	if (is_Array($cid)) $vmMethoId = $cid[0];
	else $vmMethoId = $cid;
	$pclassesLink = JURI::root().'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=getPclasses&cid='.(int)$vmMethoId;

	$html = '
		<fieldset id="klarna_pclasses" class="klarna">
		<legend id="pclass_field"><span class="expand_arrow"></span>PClasses </legend>
		<span id="PClassesSuccessResult" style="font-size: 15px;"></span>
		<div id="pclasses">';

	ob_start();
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'pclasses_html.php');
	$html .= ob_get_clean();
	if ($total == 0) { 
		$html .= '
		<span class="no_pclasses">No pclasses in database. <a href="'.$pclassesLink.'">Fetch PClasses</a></span></br>';
	}
	$html .='
		</div>
	</fieldset>
	<span class="update_pclasses">
		<a class="button_klarna" href="'.$pclassesLink.'">Update PClasses</a>
	</span><span id="pclasses_update_msg"></span>
	<div class="clear"></div>';
	return $html;

  }
}