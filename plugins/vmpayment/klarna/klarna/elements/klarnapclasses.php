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
	
	$document = &JFactory::getDocument();
	$document->addScriptDeclaration('
		jQuery(function($) {
			$(".update_pclasses a").click( function(e){
				e.preventDefault();
				form = $(this).parents("form") ;

				var link = $(this).attr("href");
				var datas = $(this).parents("form").serializeArray();
					datas.push({"name":"redirect","value":"no"});
					datas.push({"name":"task","value":"save"});
				$.post(link,datas,function(data) {
					if (data = "ok") {
						console.log("update table");
						datas.push({"name":"view","value":"plugin"});
						datas.push({"name":"name","value":"klarna"});
						datas.push({"name":"task","value":"plugin"});
						$("#pclasses").load(link+" #pclasses",datas,function() {
							console.log("update pclasse");
						});
					}
				});



				return false;
			});
		});
	');
	ob_start();
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'pclasses_html.php');
	return ob_get_clean();

    }

}