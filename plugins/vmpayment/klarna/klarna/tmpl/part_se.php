<?php  defined('_JEXEC') or die(); ?>
<!-- KLARNA BOX -->
<?php echo $viewData['checkout']; ?>
<script type="text/javascript">
     <!--
            klarna.countryCode = '<?php echo $viewData['setup']['countryCode'] ; ?>';
            klarna.language_part = '<?php echo $viewData['setup']['langISO'] ; ?>';
            klarna.sum = '<?php echo $viewData['setup']['sum'] ; ?>';
            klarna.flag = '<?php echo $viewData['setup']['flag'] ; ?>';
            klarna.type = 'part';
            klarna.unary_checkout = '<?php echo @$viewData['setup']['unary_checkout'] ; ?>';
            klarna.lang_companyNotAllowed = '<?php echo JText::_('VMPAYMENT_KLARNA_COMPANY_NOT_ALLOWED'); ?>';
            klarna.pid = '<?php echo $viewData['setup']['payment_id'] ; ?>';
            if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
                klarna.red_baloon_content = '<?php echo @$viewData['setup']['red_baloon_content'] ; ?>';
                klarna.red_baloon_box = '<?php echo @$viewData['setup']['red_baloon_paymentBox'] ; ?>';
            }

            klarna.lang_personNum    = '<?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?>';
            klarna.lang_orgNum        = '<?php echo JText::_('VMPAYMENT_KLARNA_ORGANISATION_NUMBER'); ?>';

            klarna.part_ITId        = '<?php echo $viewData['input']['invoice_type'] ; ?>';

            klarna.invoice_name   = '<?php echo $viewData['setup']['invoice_name'] ; ?>';
            klarna.part_name      = '<?php echo $viewData['setup']['part_name'] ; ?>';
            klarna.spec_name      = '<?php echo $viewData['setup']['spec_name'] ; ?>';

            if (typeof klarna.red_baloon_paymentBox == "undefined")
            {
                klarna.red_baloon_paymentBox = '<?php echo @$viewData['setup']['red_baloon_paymentBox'] ; ?>';
            }
            else {
                if (klarna.red_baloon_paymentBox == "")
                    klarna.red_baloon_paymentBox = '<?php echo @$viewData['setup']['red_baloon_paymentBox'] ; ?>';
            }

            // Mapping to the real field names which may be prefixed
            klarna.params_part = {
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
<script type="text/javascript">
jQuery(document).ready(function() {
    klarna.methodReady('part');
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
    <div class="klarna_box" id="klarna_box_part">
        <script type="text/javascript">
            openAgreement('<?php echo $viewData['setup']['countryCode'] ; ?>');
        </script>
        <div class="klarna_box_top">
            <div id="klarna_box_part_top_right" class="klarna_box_top_right">
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
                    <a href="javascript:ShowKlarnaPopup('<?php echo $viewData['setup']['eid'] ; ?>', '<?php echo $viewData['setup']['sum'] ; ?>','part')">
                        <?php echo JText::_('VMPAYMENT_KLARNA_KLARNA_ACCOUNT_AGREEMENT'); ?> </a>
                </div>
                <div class="klarna_box_bottom_languageInfo">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/notice.png"
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_SE'); ?>" />
                </div>
            </div>
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>logo/klarna_account_<?php echo $viewData['setup']['countryCode'] ; ?>.png"
                id="klarna_logo_part" class="klarna_logo"
                alt="<?php echo JText::_('VMPAYMENT_KLARNA_IMG_LOGO_ACCOUNT'); ?>"/>
        </div>
        <div class="klarna_box_bottom">
            <div class="klarna_box_bottom_contents">
                <div class="klarna_box_bottom_left">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PART_PAYMENT'); ?></div>
                        <ol id="paymentPlan"><?php echo $this->renderPClasses(); ?>
                        </ol>
                        <input type="hidden" name="<?php echo $viewData['input']['paymentPlan'] ; ?>"
                            value="<?php echo @$viewData['value']['paymentPlan'] ; ?>" class="paymentPlan" />
                        <div class="klarna_box_bottom_content_listPriceInfo">
                            <?php // echo JText::_('VMPAYMENT_KLARNA_PRICES_ARE_IN_SEK'); ?></div>
                    </div>
                </div>
                <div class="klarna_box_bottom_right">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SOCIALSECURITYNUMBER'); ?></div>
                        <div id="socialNumberLoader"
                            class="klarna_box_bottom_content_loader">
                            <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/loader1.gif" alt="" />
                        </div>
                        <input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_SE'); ?>"
                            name="<?php echo $viewData['input']['socialNumber'] ; ?>" value=""
                            class="Klarna_pnoInputField" />
                        <div class="referenceDiv" style="display: none">
                            <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_REFERENCE'); ?></div>
                            <input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_REFERENCE'); ?>"
                                name="<?php echo $viewData['input']['reference'] ; ?>" value="<?php echo @$viewData['value']['reference'] ; ?>"
                                class="Klarna_fullwidth" />
                        </div>
			<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_EMAIL'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_SE'); ?>" type="text"
                            name="<?php echo $viewData['input']['emailAddress'] ; ?>" value="<?php echo @$viewData['value']['emailAddress'] ; ?>"
                            class="Klarna_fullwidth" /> <br /> <br />
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_SE'); ?>" type="text"
                            name="<?php echo $viewData['input']['phoneNumber'] ; ?>" value="<?php echo @$viewData['value']['phoneNumber'] ; ?>"
                            class="Klarna_fullwidth" /> <br /> <br />
                        <div class="klarna_box_bottom_address" style="display: none">
                            <div class="klarna_box_bottom_address_title"><?php echo JText::_('VMPAYMENT_KLARNA_DELIVERY_ADDRESS'); ?></div>
                            <div class="klarna_box_bottom_address_content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="<?php echo $viewData['input']['emailAddress'] ; ?>"
    value="<?php echo @$viewData['value']['emailAddress'] ; ?>" />
<!-- END KLARNA BOX -->
