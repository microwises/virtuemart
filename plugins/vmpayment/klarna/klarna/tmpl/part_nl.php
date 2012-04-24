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

            klarna.select_part_bday    = '<?php echo @$viewData['value']['birth_day'] ; ?>';
            klarna.select_part_bmonth    = '<?php echo @$viewData['value']['birth_month'] ; ?>';
            klarna.select_part_byear    = '<?php echo @$viewData['value']['birth_year'] ; ?>';
            klarna.gender                = '<?php echo @$viewData['value']['gender'] ; ?>';

            klarna.invoice_name   = '<?php echo $viewData['setup']['invoice_name'] ; ?>';
            klarna.part_name      = '<?php echo $viewData['setup']['part_name'] ; ?>';
            klarna.spec_name      = '<?php echo $viewData['setup']['spec_name'] ; ?>';

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
                shipmentAddressInput: '<?php echo $viewData['input']['shipmentAddressInput'] ; ?>',
                birth_day: '<?php echo $viewData['input']['birth_day'] ; ?>',
                birth_month: '<?php echo $viewData['input']['birth_month'] ; ?>',
                birth_year: '<?php echo $viewData['input']['birth_year'] ; ?>'
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
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_NL'); ?>" />
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
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
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
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SEX'); ?></div>
                        <input type="radio" name="<?php echo $viewData['input']['gender'] ; ?>" value="1" id="part_male"
                            class="Klarna_radio gender" />
                        <div class="klarna_box_bottom_radio_title" style="float: left">
                            <label for="part_male"><?php echo JText::_('VMPAYMENT_KLARNA_SEX_MALE'); ?></label>
                        </div>
                        <input type="radio" name="<?php echo $viewData['input']['gender'] ; ?>" value="0"
                            id="part_female" class="Klarna_radio gender" />
                        <div class="klarna_box_bottom_radio_title" style="float: none">
                            <label for="part_female"><?php echo JText::_('VMPAYMENT_KLARNA_SEX_FEMALE'); ?></label>
                        </div>
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
                        <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_NL'); ?>" type="text"
                            name="<?php echo $viewData['input']['phoneNumber'] ; ?>" value="<?php echo @$viewData['value']['phoneNumber'] ; ?>"
                            class="Klarna_fullwidth" />
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div style="float: left; width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_STREET'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_STREETADDRESS'); ?>" type="text"
                                    name="<?php echo $viewData['input']['street'] ; ?>" value="<?php echo @$viewData['value']['street'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div style="float: left; width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_HOMENUMBER'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_HOUSENUMBER'); ?>" type="text"
                                    name="<?php echo $viewData['input']['homenumber'] ; ?>" value="<?php echo @$viewData['value']['homenumber'] ; ?>"
                                    style="width: 97%" />
                            </div>
                            <div style="float: right; width: 20%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_HOUSENUMBER_ADDITION'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_HOUSE_EXTENSION'); ?>" type="text"
                                    name="<?php echo $viewData['input']['house_extension'] ; ?>"
                                    value="<?php echo @$viewData['value']['house_extension'] ; ?>" style="width: 95%" size="5" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 60%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_ZIP'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_ZIP'); ?>" type="text"
                                    name="<?php echo $viewData['input']['zipcode'] ; ?>" value="<?php echo @$viewData['value']['zipcode'] ; ?>"
                                    style="width: 98%" />
                            </div>
                            <div id="right" style="width: 40%">
                                <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_CITY'); ?></div>
                                <input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_CITY'); ?>" type="text"
                                    name="<?php echo $viewData['input']['city'] ; ?>" value="<?php echo @$viewData['value']['city'] ; ?>"
                                    style="width: 100%" />
                            </div>
                        </div>
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_BIRTHDAY'); ?></div>
                        <div class="klarna_box_bottom_input_combo" style="width: 100%">
                            <div id="left" style="width: 30%">
                                <select style="width: 98%" name="<?php echo $viewData['input']['birth_day'] ; ?>"
                                    id="selectBox_part_bday">
                                    <option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_DAY'); ?></option>
                                    <option value="01">01</option>
                                    <option value="02">02</option>
                                    <option value="03">03</option>
                                    <option value="04">04</option>
                                    <option value="05">05</option>
                                    <option value="06">06</option>
                                    <option value="07">07</option>
                                    <option value="08">08</option>
                                    <option value="09">09</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                    <option value="16">16</option>
                                    <option value="17">17</option>
                                    <option value="18">18</option>
                                    <option value="19">19</option>
                                    <option value="20">20</option>
                                    <option value="21">21</option>
                                    <option value="22">22</option>
                                    <option value="23">23</option>
                                    <option value="24">24</option>
                                    <option value="25">25</option>
                                    <option value="26">26</option>
                                    <option value="27">27</option>
                                    <option value="28">28</option>
                                    <option value="29">29</option>
                                    <option value="30">30</option>
                                    <option value="31">31</option>
                                </select>
                            </div>
                            <div id="left" style="width: 40%">
                                <select style="width: 98%" name="<?php echo $viewData['input']['birth_month'] ; ?>"
                                    id="selectBox_part_bmonth">
                                    <option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_MONTH'); ?></option>
                                    <option value="01"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_1'); ?></option>
                                    <option value="02"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_2'); ?></option>
                                    <option value="03"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_3'); ?></option>
                                    <option value="04"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_4'); ?></option>
                                    <option value="05"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_5'); ?></option>
                                    <option value="06"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_6'); ?></option>
                                    <option value="07"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_7'); ?></option>
                                    <option value="08"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_8'); ?></option>
                                    <option value="09"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_9'); ?></option>
                                    <option value="10"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_10'); ?></option>
                                    <option value="11"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_11'); ?></option>
                                    <option value="12"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_12'); ?></option>
                                </select>
                            </div>
                            <div id="right" style="width: 30%">
                                <select style="width: 100%" name="<?php echo $viewData['input']['birth_year'] ; ?>"
                                    id="selectBox_part_year">
                                    <option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_YEAR'); ?></option>
                                </select>
                            </div>
                            <div class="klarna_box_bottom_input_combo" style="width: 100%">
                                <div class="klarna_box_bottom_title" style="width: 90%; margin-top: 3px">
                                    <em><?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_BILLING_SAME_AS_SHIPPING'); ?><em>
                                </div>
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
