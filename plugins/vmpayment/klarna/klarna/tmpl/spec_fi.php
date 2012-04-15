
<!-- KLARNA BOX -->
<?php echo $viewData['checkout']; ?>
<script type="text/javascript">
     <!--
            klarna.countryCode = '<?php echo $viewData['setup']['countryCode'] ; ?>';
            klarna.language_spec = '<?php echo $viewData['setup']['langISO'] ; ?>';
            klarna.sum = '<?php echo $viewData['setup']['sum'] ; ?>';
            klarna.eid = '<?php echo $viewData['setup']['eid'] ; ?>';
            klarna.flag = '<?php echo $viewData['setup']['flag'] ; ?>';
            klarna.unary_checkout = '<?php echo @$viewData['setup']['unary_checkout'] ; ?>';
            klarna.type = 'spec';
            klarna.lang_companyNotAllowed = '<?php echo JText::_('VMPAYMENT_KLARNA_COMPANY_NOT_ALLOWED'); ?>';
            klarna.pid = '<?php echo $viewData['setup']['payment_id'] ; ?>';
            if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
                klarna.red_baloon_content = '<?php echo @$viewData['setup']['red_baloon_content'] ; ?>';
                klarna.red_baloon_box = '<?php echo @$viewData['setup']['red_baloon_paymentBox'] ; ?>';
            }

            klarna.lang_personNum    = '<?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?>';
            klarna.lang_orgNum        = '<?php echo JText::_('VMPAYMENT_KLARNA_ORGANISATION_NUMBER'); ?>';

            klarna.spec_ITId        = '<?php echo $viewData['input']['invoice_type'] ; ?>';

            klarna.invoice_name   = '<?php echo $viewData['setup']['invoice_name'] ; ?>';
            klarna.part_name      = '<?php echo $viewData['setup']['part_name'] ; ?>';
            klarna.spec_name      = '<?php echo $viewData['setup']['spec_name'] ; ?>';

            // Mapping to the real field names which may be prefixed
            klarna.params_spec = {
                companyName: '<?php echo $viewData['input']['companyName'] ; ?>',
                socialNumber: '<?php echo $viewData['input']['socialNumber'] ; ?>',
                firstName: '<?php echo $viewData['input']['firstName'] ; ?>',
                lastName: '<?php echo $viewData['input']['lastName'] ; ?>',
                gender: '<?php echo $viewData['input']['gender'] ; ?>',
                street: '<?php echo $viewData['input']['street'] ; ?>',
                homenumber: '<?php echo $viewData['input']['homenumber'] ; ?>',
                house_extension: '<?php echo $viewData['input']['house_extension'] ; ?>',
                city: '<?php echo $viewData['input']['city'] ; ?>',
                zipcode: '<?php echo $viewData['input']['zipcode'] ; ?>',
                reference: '<?php echo $viewData['input']['reference'] ; ?>',
                phoneNumber: '<?php echo $viewData['input']['phoneNumber'] ; ?>',
                emailAddress: '<?php echo $viewData['input']['emailAddress'] ; ?>',
                invoiceType: '<?php echo $viewData['input']['invoiceType'] ; ?>',
                shipmentAddressInput: '<?php echo $viewData['input']['shipmentAddressInput'] ; ?>'
             }




    //-->
</script>
<script src="https://static.klarna.com/external/js/klarnaspecial.js" type="text/javascript"></script>
<script type="text/javascript">
jQuery(function (){
    klarna.methodReady('spec');
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
            openAgreement('<?php echo $viewData['setup']['countryCode'] ; ?>');
        </script>
        <div class="klarna_box_top">
            <div id="klarna_box_spec_top_right" class="klarna_box_top_right">
                <div class="klarna_box_top_flag">
                    <div class="box_active_language">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/<?php echo $viewData['setup']['langISO'] ; ?>.png"
                            alt="<?php echo $viewData['setup']['langISO'] ; ?>" /> <img
                            src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/arrow_down.gif" alt=""
                            style="float: right; padding: 6px 2px 0 0; margin: 0" />
                    </div>
                    <div class="klarna_box_top_flag_list">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/en.png" alt="en" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/da.png" alt="da" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/de.png" alt="de" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/fi.png" alt="fi" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/nl.png" alt="nl" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/nb.png" alt="nb" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/sv.png" alt="sv" />
                    </div>
                </div>
                <div class="klarna_box_top_agreement">
                    <a id="specialCampaignPopupLink" href="javascript:ShowKlarnaSpecialPaymentPopup()"></a>
                </div>
                <div class="klarna_box_bottom_languageInfo">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/notice.png"
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_FI'); ?>" />
                </div>
            </div>
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>logo/klarna_logo.png"
                alt="<?php echo JText::_('VMPAYMENT_KLARNA_IMG_LOGO_ACCOUNT'); ?>" class="klarna_logo" id="klarna_logo_spec" />
        </div>
        <div class="klarna_box_bottom">
            <div class="klarna_box_bottom_contents">
                <div class="klarna_box_bottom_left">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SPEC_PAYMENT'); ?></div>
                        <ol id="paymentPlan"><?php echo $this->renderPClasses(); ?>
                        </ol>
                        <input type="hidden" name="<?php echo $viewData['input']['paymentPlan'] ; ?>"
                            value="<?php echo @$viewData['value']['paymentPlan'] ; ?>" class="paymentPlan" />
                        <div class="klarna_box_bottom_content_listPriceInfo">
                            <?php echo JText::_('VMPAYMENT_KLARNA_PRICES_ARE_IN_SEK'); ?></div>
                    </div>
                </div>
                <div class="klarna_box_bottom_right">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title" id="perOrg_title"><?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_SPEC_FI'); ?>" type="text"
                            name="<?php echo $viewData['input']['socialNumber'] ; ?>" value="<?php echo @$viewData['value']['socialNumber'] ; ?>"
                            class="Klarna_fullwidth" />
                        <div class="klarna_box_bottom_input_combo" style="width: 100%"
                            id="box_private">
                            <div id="left" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_FIRST_NAME'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_FIRSTNAME'); ?>" type="text"
                                    name="<?php echo $viewData['input']['firstName'] ; ?>" value="<?php echo @$viewData['value']['firstName'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_LAST_NAME'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_LASTNAME'); ?>" type="text"
                                    name="<?php echo $viewData['input']['lastName'] ; ?>" value="<?php echo @$viewData['value']['lastName'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_FI'); ?>" type="text"
                                    name="<?php echo $viewData['input']['phoneNumber'] ; ?>" value="<?php echo @$viewData['value']['phoneNumber'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_STREET'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_STREETADDRESS'); ?>" type="text"
                                    name="<?php echo $viewData['input']['street'] ; ?>" value="<?php echo @$viewData['value']['street'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_ZIP'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_ZIP'); ?>" type="text"
                                    name="<?php echo $viewData['input']['zipcode'] ; ?>" value="<?php echo @$viewData['value']['zipcode'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_CITY'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_CITY'); ?>" type="text"
                                    name="<?php echo $viewData['input']['city'] ; ?>" value="<?php echo @$viewData['value']['city'] ; ?>"
                                    style="width: 100%" />
                            </div>
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
<input type="hidden" name="<?php echo $viewData['input']['emailAddress'] ; ?>"
    value="<?php echo @$viewData['value']['emailAddress'] ; ?>" />
<!-- END KLARNA BOX -->
