<?php
defined('_JEXEC') or die('Restricted access');

/**
 * The Klarna AJAX provider.
 * This class provides data for AJAX calls
 *
 * @package     Klarna Standard Kassa API
 * @version     2.0.0
 * @since       2011-10-10
 * @link        http://integration.klarna.com/
 * @copyright   Copyright (c) 2011 Klarna AB (http://klarna.com)
 */

if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');


class KlarnaAjax {
    private $api;
    private $template;
    private $coSetup;

    public function __construct ($api, $eid, $path, $webroot) {
        if (! $api instanceof Klarna) {
            throw new KlarnaApiException("api must be an instance of Klarna");
        }

        $this->api = $api;
        $this->eid = $eid;
        $this->path = $path;
        // $this->webroot = $webroot;
        $this->coSetup = array();
    }

    public function __setTemplate($template) {
        if ( is_array($template)) {
            $this->template = array_merge($this->template, $template);
        } else {
            $this->template['name'] = $template;
        }
    }

    public function __addSetupValues($arr) {
        if(is_array($arr)) {
            $this->coSetup = array_merge($this->coSetup, $arr);
        }
    }

    public function languagepack () {
        $sSubAction    = JRequest::getWord ('subAction');
        if (!isset($this->template['name']) || $this->template['name'] == '') {
            $this->template['name'] = "default";
        }
        if ($sSubAction == "klarna_box")
        {
            $sNewISO = JRequest::getWord('newIso');
            $sCountry = JRequest::getWord('country');
            $iSum = JRequest::getFloat('sum', 0);
            $iInvoiceFee = JRequest::getFloat('fee', 0);
            $iFlag = JRequest::getInt('flag');
            $sType = JRequest::getWord('type');
			$aParams = JRequest::getVar('params');
            $aValues = JRequest::getVar('values');

            // foreach($aValues as $key => $value) {
                // $aValues[$key] = utf8_encode($value);
            // }
            // foreach($aParams as $key => $value) {
                // $aParams[$key] = utf8_decode($value);
            // }

            if ($sType != "part" && $sType != "invoice" && $sType != "spec")
            {
                throw new KlarnaApiException("Invalid paramters");
            }

            $this->api->setCountry ($sCountry);

            if ($sType == 'spec') {
                $types = array(KlarnaPClass::SPECIAL);
            } else {
                $types = array(KlarnaPClass::CAMPAIGN,  KlarnaPClass::ACCOUNT, KlarnaPClass::FIXED);
            }
			if (!class_exists('KlarnaVm2API'))
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
			$oApi = new KlarnaVm2API ($sCountry, $sNewISO, $sType, $iSum, $iFlag, $this->api, $types, VMKLARNAPLUGINWEBROOT);
            //$oApi = new KlarnaAPI ($sCountry, $sNewISO, $sType, $iSum, $iFlag, $this->api, $types, $this->path);

            // $oApi->addSetupValue ('web_root', $this->webroot);
            // $oApi->setPaths ();
            $oApi->addSetupValue ('eid', $this->eid);
            if ($sType == 'invoice') {
                $oApi->setInvoiceFee($iInvoiceFee);
            }
            $oApi->setCurrency ($this -> api -> getCurrency());
            if (count($this->coSetup) > 0) {
                $oApi->addMultipleSetupValues($this->coSetup);
            }
			return $oApi->retrieveLayout($aParams, $aValues);
            // if ($sType == 'spec') {

                // return $oApi->retrieveHTML($aParams, $aValues, null, $this->template);
            // } else {
                // return $oApi->retrieveHTML ($aParams, $aValues);
            // }
        }
        else if ($sSubAction == 'jsLanguagePack')
        {
            $sNewISO = JRequest::getWord ('newIso');
            $sFetch = "";
        }
        else {
            throw new KlarnaApiException("Invalid sub-action");
        }
    }

    public function getAddress () {
        $aSessionCalls = array();

        // Check the session for calls
        if (array_key_exists ('klarna_address', $_SESSION))
        {
            $sSessionCalls = base64_decode ($_SESSION['klarna_address']);
            $aSessionCalls = unserialize ($sSessionCalls);
        }

        $sPNO    = JRequest::getWord('socialNumber'); //JRequest::getWord('pno');
        $sCountry = strtolower(JRequest::getWord('country'));

        if (array_key_exists ($sPNO, $aSessionCalls))
        {
            $addrs    = $aSessionCalls[$sPNO];
        }
        else
        {
            $addrs = $this->api->getAddresses ($sPNO, null, KlarnaFlags::GA_GIVEN);

            $aSessionCalls[$sPNO] = $addrs;
            $_SESSION['klarna_address'] = base64_encode (serialize ($aSessionCalls));
        }

        $sString  = "<?xml version='1.0'"."?".">\n"; //eval breaks at question-mark gt
        $sString .= "<getAddress>\n";

        //This example only works for GA_GIVEN.
        foreach ($addrs as $index => $addr) {
            if ($addr->isCompany) {
                $implode = array(
                    'companyName' => $addr->getCompanyName (),
                    'street' =>  $addr->getStreet (),
                    'zip' =>  $addr->getZipCode (),
                    'city' =>  $addr->getCity (),
                    'countryCode' => $addr->getCountryCode ()
                );
            }
            else {
                $implode = array(
                    'first_name' => $addr->getFirstName (),
                    'last_name' => $addr->getLastName (),
                    'street' => $addr->getStreet (),
                    'zip' => $addr->getZipCode (),
                    'city' => $addr->getCity (),
                    'countryCode' => $addr->getCountryCode ()
                );
            }

            $sString .= "<address>\n";

            foreach ($implode as $key => $val) {
                $sString    .= "<".$key.">" . Klarna::num_htmlentities($val) . "</".$key.">\n";
            }

            $sString .= "</address>\n";
        }

        $sString .= "</getAddress>";
        return array('type' => 'text/xml', 'value' => $sString);
    }
}
