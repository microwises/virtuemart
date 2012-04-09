
<!-- KLARNA BOX -->
<?php echo $params['checkout']; ?>
<script type="text/javascript">
     <!--
            klarna.countryCode = '<?php echo $params['setup']['countryCode'] ; ?>';
            klarna.language_invoice = '<?php echo $params['setup']['langISO'] ; ?>';
            klarna.klarna_invoice_fee = '<?php echo $params['setup']['fee'] ; ?>';
            klarna.flag = '<?php echo $params['setup']['flag'] ; ?>';
            klarna.type = 'invoice';
            klarna.sum = '<?php echo $params['setup']['sum'] ; ?>';
            klarna.pid = '<?php echo $params['setup']['payment_id'] ; ?>';
            klarna.unary_checkout = '<?php echo @$params['setup']['unary_checkout'] ; ?>';
            if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
                klarna.red_baloon_content = '<?php echo @$params['setup']['red_baloon_content'] ; ?>';
                klarna.red_baloon_box = '<?php echo @$params['setup']['red_baloon_paymentBox'] ; ?>';
            }

            klarna.select_bday        = '<?php echo @$params['value']['birth_day'] ; ?>';
            klarna.select_bmonth    = '<?php echo @$params['value']['birth_month'] ; ?>';
            klarna.select_byear    = '<?php echo @$params['value']['birth_year'] ; ?>';
            klarna.gender            = '<?php echo @$params['value']['gender'] ; ?>';

            klarna.invoice_name   = '<?php echo $params['setup']['invoice_name'] ; ?>';
            klarna.part_name      = '<?php echo $params['setup']['part_name'] ; ?>';
            klarna.spec_name      = '<?php echo $params['setup']['spec_name'] ; ?>';

            if (typeof klarna.red_baloon_paymentBox == "undefined")
            {
                klarna.red_baloon_paymentBox = '<?php echo @$params['setup']['red_baloon_paymentBox'] ; ?>';
            }
            else {
                if (klarna.red_baloon_paymentBox == "")
                    klarna.red_baloon_paymentBox = '<?php echo @$params['setup']['red_baloon_paymentBox'] ; ?>';
            }

            // Mapping to the real field names which may be prefixed
            klarna.params_invoice = {
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

             klarna.lang_personNum    = '<?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?>';
             klarna.lang_orgNum        = '<?php echo JText::_('VMPAYMENT_KLARNA_ORGANISATION_NUMBER'); ?>';

             klarna.invoice_ITId        = '<?php echo $params['input']['invoiceType'] ; ?>';




         //-->
         </script>
<script type="text/javascript">
jQuery( function (){
    klarna.methodReady('invoice');
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
    <div class="klarna_box" id="klarna_box_invoice">
        <script type="text/javascript">
            openAgreement('<?php echo $params['setup']['countryCode'] ; ?>');
        </script>
        <div class="klarna_box_top">
            <div id="klarna_box_invoice_top_right" class="klarna_box_top_right">
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
                    <a href="javascript:ShowKlarnaPopup('<?php echo $params['setup']['eid'] ; ?>', '<?php echo $params['setup']['fee'] ; ?>','invoice')">
                        <?php echo JText::_('VMPAYMENT_KLARNA_KLARNA_INVOICE_AGREEMENT'); ?> </a>
                </div>
                <div class="klarna_box_bottom_languageInfo">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>share/notice.png"
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_DK'); ?>" />
                </div>
            </div>
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'images/' ?>logo//klarna_invoice_<?php echo $params['setup']['countryCode'] ; ?>.png"
                alt="<?php echo JText::_('VMPAYMENT_KLARNA_IMG_LOGO_ACCOUNT'); ?>" id="klarna_logo_invoice"
                class="klarna_logo" />
        </div>
        <div class="klarna_box_bottom">
            <div class="klarna_box_bottom_contents">
                <div class="klarna_box_bottom_right">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_INVOICE_TYPE'); ?></div>
                        <input type="radio" name="<?php echo $params['input']['invoiceType'] ; ?>" value="private"
                            checked="checked" class="Klarna_radio" />
                        <div class="klarna_box_bottom_radio_title" style="float: left">
                            <label for="private"><?php echo JText::_('VMPAYMENT_KLARNA_INVOICE_TYPE_PRIVATE'); ?></label>
                        </div>
                        <input type="radio" name="<?php echo $params['input']['invoiceType'] ; ?>" value="company"
                            class="Klarna_radio" />
                        <div class="klarna_box_bottom_radio_title" style="float: none">
                            <label for="company"><?php echo JText::_('VMPAYMENT_KLARNA_INVOICE_TYPE_COMPANY'); ?></label>
                        </div>
                        <div class="klarna_box_bottom_title" id="invoice_perOrg_title"><?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_DK'); ?>" type="text"
                            name="<?php echo $params['input']['socialNumber'] ; ?>" value="<?php echo @$params['value']['socialNumber'] ; ?>"
                            class="Klarna_fullwidth" />
                        <div class="klarna_box_bottom_input_combo" style="width: 100%"
                            id="invoice_box_private">
                            <div id="left" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_FIRST_NAME'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_FIRSTNAME'); ?>" type="text"
                                    name="<?php echo $params['input']['firstName'] ; ?>" value="<?php echo @$params['value']['firstName'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_LAST_NAME'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_LASTNAME'); ?>" type="text"
                                    name="<?php echo $params['input']['lastName'] ; ?>" value="<?php echo @$params['value']['lastName'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo"
                            style="width: 100%; display: none" id="invoice_box_company">
                            <div id="left" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_COMPANY_NAME'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_COMPANYNAME'); ?>" type="text"
                                    name="<?php echo $params['input']['companyName'] ; ?>" value="<?php echo @$params['value']['companyName'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_REFERENCE'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_REFERENCE'); ?>" type="text"
                                    name="<?php echo $params['input']['reference'] ; ?>" value="<?php echo @$params['value']['reference'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_DK'); ?>" type="text"
                                    name="<?php echo $params['input']['phoneNumber'] ; ?>" value="<?php echo @$params['value']['phoneNumber'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_STREET'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_STREETADDRESS'); ?>" type="text"
                                    name="<?php echo $params['input']['street'] ; ?>" value="<?php echo @$params['value']['street'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_ZIP'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_ZIP'); ?>" type="text"
                                    name="<?php echo $params['input']['zipcode'] ; ?>" value="<?php echo @$params['value']['zipcode'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_CITY'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_CITY'); ?>" type="text"
                                    name="<?php echo $params['input']['city'] ; ?>" value="<?php echo @$params['value']['city'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_additional_information">
                            <?php echo @$params['setup']['additional_information'] ; ?>
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
