<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * Klarna
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
if (!class_exists('KlarnaAPI'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');

class KlarnaVm2API extends KlarnaAPI {
    /*
     * $a_sCountry: 3 letters country code
     * $a_sLangISO: if null, retricves from 3 letlers country code
     */

    public function __construct($a_sCountry, $a_sLangISO, $a_sType, $a_iSum, $a_iFlag, &$a_oKlarna = null, $aTypes = null, $sPath = null) {
	parent::__construct($a_sCountry, $a_sLangISO, $a_sType, $a_iSum, $a_iFlag, $a_oKlarna, $aTypes, $sPath);
    }

    function retrieveLayout($a_aParams, $a_aValues,   $aTemplateData = null) {
	if ($a_aValues != null)
	    $this->aInputValues = array_merge($this->aInputValues, $a_aValues);


	if ($a_aParams != null)
	    $this->aInputParameters = array_merge($this->aInputParameters, $a_aParams);

	if (is_array($this->aPClasses)) {
	    $this->aInputValues['paymentPlan'] = '';
	    foreach ($this->aPClasses as $pclass) {
		if ($pclass['default'] === true) {
		    $this->aInputValues['paymentPlan'] = $pclass['pclass']->getId();
		    break;
		}
	    }
	}
	if (strtolower($this->sCountryCode) == 'de') {
	    $vendor_id = 1;
	    $link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $this->aSetupSettings['agb_link'] = $link;
	}
	if ($this->sType != "spec") {
	    $this->aSetupSettings['conditionsLink'] = $aTemplateData['conditions'];
	}
	//$tmplLayout = $this->sType . "_" . strtolower($this->sCountryCode);
	$tmplLayout = 'paiment' ;
	return vmPlugin::renderByLayout($tmplLayout, array('checkout' => $this->oKlarna->checkoutHTML(),
		    'input' => $this->aInputParameters,
		    'value' => $this->aInputValues,
		    'setup' => $this->aSetupSettings,
		    'sType' => $this->sType
			), 'klarna', 'payment');
    }

}

