<?php
defined('_JEXEC') or die('Restricted access');


class KlarnaProductPrice {
    private $api;
    private $path;
    private $webroot;
    private $logo;
    private $terms_link;
    private $eid;
    private $checkout;

    public function __construct ($api, $eid, $path, $webroot, $checkout = null) {
        if (! $api instanceof Klarna) {
            throw new KlarnaApiException("api must be an instance of Klarna");
        }

        $this->terms_link = "#"; //replaced by NL to a different document
        $this->api = $api;
        $this->path = $path;
        $this->eid = $eid;
        $this->webroot = $webroot;
        if ($checkout !== null) {
            $this->checkout = $checkout;
        }
        $this->logo = null;
    }

    public function __setLogo($logo) {
        $this->logo = $logo;
    }

    public function show($price, $currency, $country, $lang = null, $page = null) {
        if (!is_numeric ($country)) {
            $country = KlarnaCountry::fromCode ($country);
        } else {
            $country = intval ($country);
        }

        // we will always use the language for the country to get the correct
        // terms and conditions aswell as the correct name for 'Klarna Konto'
        $lang = KlarnaLanguage::getCode ($this -> api ->
            getLanguageForCountry ($country));

        if( $page === null || ($page != KlarnaFlags::PRODUCT_PAGE && $page != KlarnaFlags::CHECKOUT_PAGE)) {
            $page = KlarnaFlags::PRODUCT_PAGE;
        }

        if ( !$this->api->checkCountryCurrency($country, $currency)) {
            return false;
        }

        $types = array(KlarnaPClass::CAMPAIGN,
                    KlarnaPClass::ACCOUNT,
                    KlarnaPClass::FIXED);
        if ($this->checkout === null) {
            $this->checkout = new KlarnaAPI ($country, $lang, 'part', $price, $page, $this->api, $types, $this->path);
            $this->checkout->addSetupValue ('web_root', $this->webroot);
            $this->checkout->addSetupValue ('path_img', $this->webroot . VMKLARNAPLUGINWEBROOT.'/klarna/assets/images/');
            $this->checkout->addSetupValue ('path_js', $this->web_root . VMKLARNAPLUGINWEBROOT . "/klarna/assets/js/");
            $this->checkout->addSetupValue ('path_css', $this->webroot . VMKLARNAPLUGINWEBROOT.  '/klarna/assets/css/');
            if ($country  == KlarnaCountry::DE) {
                $this->checkout->addSetupValue ('asterisk', '*');
            }
            $this->checkout -> setCurrency($currency);
        }

        if ($price > 0 && count ($this->checkout->aPClasses) > 0) {
            $monthlyCost = array();
            $minRequired = array();

            $sMonthDefault = null;

            $sTableHtml = "";
            foreach ($this->checkout->aPClasses as $pclass) {
                if ($sMonthDefault === null || $pclass['monthlyCost'] < $sMonthDefault) {
                    $sMonthDefault = $pclass['monthlyCost'];
                }

                if ($pclass['pclass']->getType() == KlarnaPClass::ACCOUNT) {
                    $pp_title = JText::_('VMPAYMENT_KLARNA_PPBOX_ACCOUNT');
                } else {
                    $pp_title = $pclass['pclass']->getMonths() . " " . JText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH') ;
                }
                $pp_price = $pclass['monthlyCost'];
                $sTableHtml .= $this->checkout->retrieveHTML(null, array('pp_title' => html_entity_decode ($pp_title), 'pp_price' => $pp_price), $this->path . '/klarna/html/pp_box_template.html');
            }
            if ($this->logo == "short") {
                $notice = "notice_nl_cart.jpg";
            } else if ($this->logo == "long") {
                $notice = "notice_nl.jpg";
            } else if ($page == KlarnaFlags::CHECKOUT_PAGE) {
                $notice = "notice_nl_cart.jpg";
            } else {
                $notice = "notice_nl.jpg";
            }
            $aInputValues    = array();
            $aInputValues['defaultMonth']   = $sMonthDefault;
            $aInputValues['monthTable']     = $sTableHtml;
            $aInputValues['eid']            = $this->eid;
            $aInputValues['country']        = KlarnaCountry::getCode ($country);
            $aInputValues['nlBanner']       = (($country == KlarnaCountry::NL) ? '<div class="nlBanner"><img src="'.$this->webroot.'images/account/'.$notice.'" /></div>' : "");

            return $this->checkout->retrieveHTML($aInputValues, null, $this->path . '/klarna/tmpl/productprice_layout.html');
        }
    }
}

