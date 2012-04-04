
<!-- KLARNA BOX -->
<?php echo $params['checkout']; ?>
<script type="text/javascript">
     <!--
            klarna.global_countryCode = '<?php echo $params['setup']['countryCode'] ; ?>';
            klarna.global_language_spec = '<?php echo $params['setup']['langISO'] ; ?>';
            klarna.global_sum = '<?php echo $params['setup']['sum'] ; ?>';
            klarna.global_eid = '<?php echo $params['setup']['eid'] ; ?>';
            klarna.global_flag = '<?php echo $params['setup']['flag'] ; ?>';
            klarna.global_unary_checkout = '<?php echo $params['setup']['unary_checkout'] ; ?>';
            klarna.global_type = 'spec';
            klarna.lang_companyNotAllowed = '<?php echo JText::_('VMPAYMENT_KLARNA_COMPANY_NOT_ALLOWED'); ?>';
            klarna.global_pid = '<?php echo $params['setup']['payment_id'] ; ?>';
            if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
                klarna.red_baloon_content = '<?php echo $params['setup']['red_baloon_content'] ; ?>';
                klarna.red_baloon_box = '<?php echo $params['setup']['red_baloon_paymentBox'] ; ?>';
            }

            klarna.invoice_name   = '<?php echo $params['setup']['invoice_name'] ; ?>';
            klarna.part_name      = '<?php echo $params['setup']['part_name'] ; ?>';
            klarna.spec_name      = '<?php echo $params['setup']['spec_name'] ; ?>';

            // Mapping to the real field names which may be prefixed
            klarna.params_spec = {
                companyName: '<?php echo $params['input']['companyName'] ; ?>',
                socialNumber: '<?php echo $params['input']['socialNumber'] ; ?>',
                firstName: '<?php echo $params['input']['firstName'] ; ?>',
                lastName: '<?php echo $params['input']['lastName'] ; ?>',
                gender: '<?php echo $params['input']['gender'] ; ?>',
                street: '<?php echo $params['input']['street'] ; ?>',
                homenumber: '<?php echo $params['input']['homenumber'] ; ?>',
                house_extension: '<?php echo $params['input']['house_extension'] ; ?>',
                city: '<?php echo $params['input']['city'] ; ?>',
                zipcode: '<?php echo $params['input']['zipcode'] ; ?>',
                reference: '<?php echo $params['input']['reference'] ; ?>',
                phoneNumber: '<?php echo $params['input']['phoneNumber'] ; ?>',
                emailAddress: '<?php echo $params['input']['emailAddress'] ; ?>',
                invoiceType: '<?php echo $params['input']['invoiceType'] ; ?>',
                shipmentAddressInput: '<?php echo $params['input']['shipmentAddressInput'] ; ?>'
             }




    //-->
</script>
<script src="https://static.klarna.com/external/js/klarnaspecial.js" type="text/javascript"></script>
<script type="text/javascript">
jQuery(function (){
    klarna_specReady();
});
</script>
<div class="klarna_baloon" id="klarna_baloon" style="display: none">
    <div class="klarna_baloon_top"></div>
    <div class="klarna_baloon_middle" id="klarna_baloon_content">
        <div></div>
    </div>
    <div class="klarna_baloon_bottom"></div>
</div>
<div class="klarna_blue_baloon" id="klarna_blue_baloon"
    style="display: none">
    <div class="klarna_blue_baloon_top"></div>
    <div class="klarna_blue_baloon_middle" id="klarna_blue_baloon_content">
        <div></div>
    </div>
    <div class="klarna_blue_baloon_bottom"></div>
</div>
<div class="klarna_box_container">
    <div class="klarna_box" id="klarna_box_spec">
        <script type="text/javascript">
            openAgreement('<?php echo $params['setup']['countryCode'] ; ?>');
        </script>
        <div class="klarna_box_top">
            <div id="klarna_box_spec_top_right" class="klarna_box_top_right">
                <div class="klarna_box_top_flag">
                    <div class="box_active_language">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/<?php echo $params['setup']['langISO'] ; ?>.png"
                            alt="<?php echo $params['setup']['langISO'] ; ?>" /> <img
                            src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/arrow_down.gif" alt=""
                            style="float: right; padding: 6px 2px 0 0; margin: 0" />
                    </div>
                    <div class="klarna_box_top_flag_list">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/en.png" alt="en" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/da.png" alt="da" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/de.png" alt="de" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/fi.png" alt="fi" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/nl.png" alt="nl" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/nb.png" alt="nb" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/flags/sv.png" alt="sv" />
                    </div>
                </div>
                <div class="klarna_box_top_agreement">
                    <a id="specialCampaignPopupLink" href="javascript:ShowKlarnaSpecialPaymentPopup()"></a>
                </div>
                <div class="klarna_box_bottom_languageInfo">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/notice.png"
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_SE'); ?>" />
                </div>
            </div>
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>logo/klarna_logo.png"
                alt="<?php echo JText::_('VMPAYMENT_KLARNA_IMG_LOGO_ACCOUNT'); ?>" class="klarna_logo" id="klarna_logo_spec" />
        </div>
        <div class="klarna_box_bottom">
            <div class="klarna_box_bottom_contents">
                <div class="klarna_box_bottom_left">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SPEC_PAYMENT'); ?></div>
                        <ol id="paymentPlan"><?php echo $this->renderPClasses(); ?>
                        </ol>
                        <input type="hidden" name="<?php echo $params['input']['paymentPlan'] ; ?>"
                            value="<?php echo @$params['value']['paymentPlan'] ; ?>" class="paymentPlan" />
                        <div class="klarna_box_bottom_content_listPriceInfo">
                            <?php echo JText::_('VMPAYMENT_KLARNA_PRICES_ARE_IN_SEK'); ?></div>
                    </div>
                </div>
                <div class="klarna_box_bottom_right">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SOCIALSECURITYNUMBER'); ?></div>
                        <div class="klarna_box_bottom_content_loader">
                            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/loader1.gif" alt="" />
                        </div>
                        <input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_SE'); ?>"
                            name="<?php echo $params['input']['socialNumber'] ; ?>" value=""
                            class="Klarna_pnoInputField" />
                        <div class="referenceDiv" style="display: none">
                            <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_REFERENCE'); ?></div>
                            <input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_REFERENCE'); ?>"
                                name="<?php echo $params['input']['reference'] ; ?>" value="<?php echo @$params['value']['reference'] ; ?>"
                                class="Klarna_fullwidth" />
                        </div>
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_SE'); ?>" type="text"
                            name="<?php echo $params['input']['phoneNumber'] ; ?>" value="<?php echo @$params['value']['phoneNumber'] ; ?>"
                            class="Klarna_fullwidth" /> <br /> <br />
                        <div class="klarna_box_bottom_address" style="display: none">
                            <div class="klarna_box_bottom_address_title"><?php echo JText::_('VMPAYMENT_KLARNA_DELIVERY_ADDRESS'); ?></div>
                            <div class="klarna_box_bottom_address_content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="klarna_box_ilt">
            <div class="klarna_box_ilt_title"><?php echo JText::_('VMPAYMENT_KLARNA_ILT_TITLE'); ?></div>
            <div class="klarna_box_iltContents"></div>
        </div>
    </div>
</div>
<input type="hidden" name="<?php echo $params['input']['emailAddress'] ; ?>"
    value="<?php echo @$params['value']['emailAddress'] ; ?>" />
<!-- END KLARNA BOX -->
