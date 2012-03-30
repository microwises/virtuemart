if(typeof klarna_invoice_fee == 'undefined') {
    var klarna_invoice_fee = 0;
}
if(typeof global_sum == 'undefined') {
    var global_sum = 0;
}
if(typeof ajax_path == 'undefined') {
    var ajax_path = 'klarnaAjax.php';
}

// Workaround for old jQuery versions
// if (typeof jQuery.prototype.focusin == "undefined") {
    // jQuery.prototype.focusin = jQuery.prototype.focus;
// }
// if (typeof jQuery.prototype.focusout == "undefined") {
    // jQuery.prototype.focusout = jQuery.prototype.blur;
// }
// if (typeof jQuery.prototype.closest == "undefined") {
    // jQuery.prototype.closest = jQuery.prototype.parents;
// }

var klarnaGeneralLoaded = true;
var red_baloon_busy = false;
var blue_baloon_busy = false;
var address_busy = false;
var baloons_moved = false;
var flagChange_active = false;
var changeLanguage_busy = false;
var openBox_busy = false;
var showing_companyNotAlowed_box = false;
var gChoice;

var klarna_js_loaded = true;

var klarna = {
    errorHandler: {
        show: function(parentBox, message, code, type) {
            var errorHTML = '<div class="klarna_errMsg"><span>'+message+'</span></div>';
            errorHTML += '<div class="klarna_errDetails">';
            if ( type != '' ) {
                errorHTML += '<span class="klarna_errType">'+type+'</span>';
            }
            if ( code != '' ) {
                errorHTML += '<span class="klarna_errCode">#'+code+'</span></div>';
            }

            if (jQuery('#klarna_red_baloon').length == 0) {
                klarna.errorHandler.create();
            }

            jQuery('#klarna_red_baloon_content').html(errorHTML);
            showRedBaloon(parentBox);
        },

        /**
         * Creates the red baloon used to show error messages
         */
        create: function() {
            jQuery(
                '<div class="klarna_red_baloon" id="klarna_red_baloon">' +
                '<div class="klarna_red_baloon_top"></div>' +
                '<div class="klarna_red_baloon_middle" id="klarna_red_baloon_content"></div>' +
                '<div class="klarna_red_baloon_bottom"></div>' +
                '</div>').appendTo('body');
        }
    }
};

Address = function (companyName, firstName, lastName, street, zip, city, countryCode) {
    this.companyName = companyName;
    this.firstName = firstName;
    this.lastName = lastName;
    this.street = street;
    this.zip = zip;
    this.city = city;
    this.countryCode = countryCode;
    this.isCompany = (this.companyName.length > 0);
};

Address.fromXML = function (elem) {
    return new Address(
        jQuery(elem).find('companyName').text(),
        jQuery(elem).find('first_name').text(),
        jQuery(elem).find('last_name').text(),
        jQuery(elem).find('street').text(),
        jQuery(elem).find('zip').text(),
        jQuery(elem).find('city').text(),
        jQuery(elem).find('countryCode').text()
    );
};

Address.Mode = function Mode() {}
Address.Single = new Address.Mode();
Address.Multi = new Address.Mode();

Address.prototype.inputValue = function () {
    return [(this.isCompany
                ? this.companyName
                : (this.firstName + '|' + this.lastName)),
        this.street,
        this.zip,
        this.city,
        this.countryCode].join('|');
}

Address.prototype.render = function (mode) {
    if (mode == Address.Single) {
        return '<p>' +
            (this.isCompany
                ? this.companyName
                : (this.firstName + ' ' + this.lastName)) + '</p>' +
            '<p>' + this.street + '</p>' +
            '<p>' + this.zip + ' ' + this.city + '</p>' +
            '<p>' + this.countryCode + '</p>';
    } else if (mode == Address.Multi) {
        return '<option value="' + this.inputValue() + '">' +
            (this.isCompany
                ? this.companyName
                : (this.firstName + ' ' + this.lastName)) +
            ', ' + this.street +
            ', ' + this.zip + ' ' + this.city +
            ', ' + this.countryCode;
    }
}

AddressCollection = function (addresses) {
    this.addresses = addresses;
    this.mode = addresses.length > 1 ? Address.Multi : Address.Single;
}

AddressCollection.fromXML = function (elem) {
    var multi = (jQuery('address', elem).length > 1);

    return new AddressCollection(jQuery('address', elem).map(function () {
        var addr = Address.fromXML(this);
        return addr;
    }));
}

AddressCollection.prototype.render = function (to, inputName) {
    var box = jQuery(to).find('.klarna_box_bottom_address_content');
    box.empty();
    if (this.mode == Address.Single) {
        var inputValue = this.addresses[0].inputValue();
        var input = jQuery('<input type="hidden" name="' + inputName + '" value="' + inputValue + '" />')
        box.append(input);
        box.append(this.addresses[0].render(Address.Single));
    } else if (this.mode == Address.Multi) {
        var select = jQuery('<select name="' + inputName + '">')
        box.append(select);

        jQuery.each(this.addresses, function(i, addr) {
            select.append(addr.render(Address.Multi));
        });
    }
}

function getPaymentOption () {
    if (jQuery('input[type=radio][name=' + global_pid + ']').length > 0)
        var box = jQuery('input[type=radio][name=' + global_pid + ']:checked');
    else
        var box = jQuery('input[type=hidden][name=' + global_pid + ']');

    return jQuery(box).val();
}

function hidePaymentOption (box, animate) {
    if (typeof animate == 'undefined') {
        animate = false;
    }

    if (animate) {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').
            css({'display': 'none'});
    } else {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').
        fadeOut('fast');
    }

    jQuery(box).animate({'min-height': '55px'}, 200);
    showHideIlt(jQuery(box).find('.klarna_box_ilt'), false, animate);
}

function showPaymentOption (box, animate, currentMinHeight, different_language) {
    if (typeof animate == 'undefined') {
        animate = false;
    }

    if (animate) {
        jQuery(box).animate({"min-height": currentMinHeight}, 200, function () {
            showHideIlt(jQuery(this).find('.klarna_box_ilt'), true);
            jQuery(this).find('.klarna_box_bottom').fadeIn('fast', function () {
                jQuery('.klarna_box_bottom_content_loader').fadeOut();

                if (showing_companyNotAlowed_box)
                {
                    hideRedBaloon();
                }
            });
            jQuery(this).find('.klarna_box_top_right').fadeIn('fast');

            if (different_language)
                jQuery(this).find('.klarna_box_bottom_languageInfo').fadeIn('fast');
                jQuery('.klarna_box_bottom_languageInfo').fadeIn('fast');

            openBox_busy = false;
        });
    } else {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').fadeIn('fast');
        showHideIlt(jQuery(box).find('.klarna_box_ilt'), true, animate);
    }
}

function initPaymentSelection () {
    var choice = getPaymentOption();
    gChoice = jQuery(document).find('input[value="'+choice+'"]').attr("id");
    if (choice != invoice_name) {
        hidePaymentOption(jQuery('#klarna_box_invoice'));
    } else {
        showPaymentOption(jQuery('#klarna_box_invoice'));
    }

    if (choice != part_name) {
        hidePaymentOption(jQuery('#klarna_box_part'));
    } else {
        showPaymentOption(jQuery('#klarna_box_part'));
    }

    if (choice != spec_name) {
        hidePaymentOption(jQuery('#klarna_box_spec'));
    } else {
        showPaymentOption(jQuery('#klarna_box_spec'));
    }
	// jQuery(document).find('input[type=radio][name='+global_pid+']').each(function () {

		// klarna_box_container
	// }
    //
	jQuery('input.klarnaPayment').each(function () {
        var value = jQuery(this).val();
        // If value is a number it can't be used so we fallback to id.
        if (!isNaN(value)) {
             var value = jQuery(this).attr('id');
        }
        // jQuery(this).parent().parent().click(function (){
            // choosePaymentOption(value);
        // });
        jQuery(this).click(function (){
            choosePaymentOption(value);
        });
    });
}

//Load when document finished loading
jQuery(document).ready(function (){
    var baloon = jQuery('.klarna_baloon').clone();
    jQuery('.klarna_baloon').remove();

    var baloon3 = jQuery('.klarna_blue_baloon').clone();
    jQuery('.klarna_blue_baloon').remove();


    jQuery('body').append(baloon);
    jQuery('body').append(baloon3);

    doDocumentIsReady();

    jQuery('.klarna_box_bottom_languageInfo').remove();

    if (!global_unary_checkout) {
        initPaymentSelection();
    }

    baloons_moved = true;
});

function choosePaymentOption (choice) {
    if (openBox_busy == false)
    {
        hideRedBaloon();
        hideBlueBaloon();
        openBox_busy = true;
        jQuery(document).find('input[value="'+choice+'"]').attr("checked", "checked");
        jQuery(document).find('input[id="'+choice+'"]').attr("checked", "checked");
        if (choice == invoice_name)
        {
            hidePaymentOption(jQuery('#klarna_box_part'), true);
            hidePaymentOption(jQuery('#klarna_box_spec'), true);
            showPaymentOption(jQuery('#klarna_box_invoice'), true,
                currentMinHeight_invoice, invoice_different_language);
            invoice_active = true;

        }
        else if (choice == part_name)
        {
            hidePaymentOption(jQuery('#klarna_box_invoice'), true);
            hidePaymentOption(jQuery('#klarna_box_spec'), true);
            showPaymentOption(jQuery('#klarna_box_part'), true,
                currentMinHeight_part, part_different_language);
            part_active = true;
        }
        else if (choice == spec_name)
        {
            hidePaymentOption(jQuery('#klarna_box_invoice'), true);
            hidePaymentOption(jQuery('#klarna_box_part'), true);
            showPaymentOption(jQuery('#klarna_box_spec'), true,
                currentMinHeight_spec, spec_different_language);
            spec_active = true;
        }
        else {
            jQuery('#klarna_box_part_top_right').fadeOut('fast');
            jQuery('#klarna_box_invoice_top_right').fadeOut('fast');
            jQuery('#klarna_box_spec_top_right').fadeOut('fast');

            jQuery('.klarna_box_bottom').fadeOut('fast', function () {
                jQuery(this).find('.klarna_box_ilt').fadeOut('fast');
                jQuery('#klarna_box_invoice').animate({"min-height": "55px"}, 200);
                jQuery('#klarna_box_part').animate({"min-height": "55px"}, 200);
                jQuery('#klarna_box_spec').animate({"min-height": "55px"}, 200);

                jQuery('.klarna_box_bottom_languageInfo').fadeOut('fast');

                invoice_active = false;
                openBox_busy = false;
            });
        }
    }
    chosen = choice;
}

function setGender (context, gender) {
    // This should be refactored to not be able to set other non-gender radio buttons
    var value;
    if (gender == 'm' || gender == '1')
    {
        jQuery('.Klarna_radio[value=1]', context).attr('checked', 'checked');
    }
    else if (gender == 'f' || gender == '0')
    {
        jQuery('.Klarna_radio[value=0]', context).attr('checked', 'checked');
    }
}

/**
 * Hook up jQuery callbacks for the given klarna_box_container(s) or
 * all klarna options in the document
 */
function initPaymentOptions(opts) {
    if (typeof opts == 'undefined') {
        opts = jQuery(document);
    }

    if(typeof InitKlarnaSpecialPaymentElements != 'undefined')
        InitKlarnaSpecialPaymentElements('specialCampaignPopupLink', global_eid, global_countryCode);

    // P-Classes box actions
    jQuery('.klarna_box', opts).find('ol').find('li').mouseover(function (){
        if (jQuery(this).attr("id") != "click")
            jQuery(this).attr("id", "over");
    }).mouseout(function (){
        if (jQuery(this).attr("id") != "click")
            jQuery(this).attr("id", "");
    }).click(function (){
        // Reset list and move chosen icon to newly selected pclass
        chosen = jQuery(this).parent("ol").find('img')
        resetListBox(jQuery(this).parent("ol"));
        chosen.appendTo(jQuery(this).find('div'));
        jQuery(this).attr("id", "click");

        // Update input field with pclass id
        var value = jQuery(this).find('span').html();
        var name = jQuery(this).parent("ol").attr("id");

        jQuery(this).closest('.klarna_box').find("input.paymentPlan").attr("value", value);
    });

    if (global_countryCode == "de" || global_countryCode == "nl")
    {
        setGender(opts, gender);
    }

    // Input field on focus
    // jQuery('.klarna_box', opts).find('input').focusin(function () {
        // setBaloonInPosition(jQuery(this), false);
    // }).focusout(function () {
        // hideBaloon();
    // });

    // Chosing the active language
    jQuery('.box_active_language', opts).click(function () {
        if (flagChange_active == false)
        {
            flagChange_active = true;

            jQuery(this).parent().find('.klarna_box_top_flag_list').slideToggle('fast', function () {
                if (jQuery(this).is(':visible'))
                {
                    jQuery(this).parent('.klarna_box_top_flag').animate({opacity: 1.0}, 'fast');
                }
                else {
                    jQuery(this).parent('.klarna_box_top_flag').animate({opacity: 0.4}, 'fast');
                }

                flagChange_active = false;
            });
        }
    });

    jQuery('.klarna_box_top_flag_list img', opts).click(function (){
        if (changeLanguage_busy == false)
        {
            changeLanguage_busy = true;

            var newIso = jQuery(this).attr("alt");

            jQuery('#box_active_language', opts).attr("src", jQuery(this).attr("src"));

            var box = jQuery(this).parents('.klarna_box_container');
            var params;
            var values;
            var type;
            var boxType = box.find('.klarna_box').attr("id");

            if (boxType == "klarna_box_invoice")
            {
                params = params_invoice;
                type = "invoice";
            }
            else if (boxType == "klarna_box_part")
            {
                params = params_part;
                type = "part";
            }
            else if (boxType == "klarna_box_spec")
            {
                params = params_spec;
                type = "spec";
            }
            else {
				console.log(boxType);
                return ;
            }

            changeLanguage(box, params, newIso, global_countryCode, type);
        }
    });

    setTimeout('prepareRedBaloon()', 1000);

    jQuery('.klarna_box_bottom_languageInfo', opts).mousemove(function (e) {
        showBlueBaloon(e.pageX, e.pageY, jQuery(this).find('img').attr("alt"));
    });

    jQuery('.klarna_box_bottom_languageInfo', opts).mouseout(function () {
        hideBlueBaloon();
    });

    jQuery('input.gender.Klarna_radio', opts).bind('change', function () {
        gender = jQuery(this).val();
    });

    jQuery('.Klarna_pnoInputField', opts).each(function (){
        var pnoField = jQuery(this);

        jQuery(this).bind("keyup change blur focus", function (){
            pnoUpdated(jQuery(this),
                (jQuery(this).parents('.klarna_box').attr("id") == "klarna_box_invoice"));
        });
    });
}

function doDocumentIsReady ()
{
    currentMinHeight_invoice = jQuery('#klarna_box_invoice').height();
    currentMinHeight_part = jQuery('#klarna_box_part').height();
    currentMinHeight_spec = jQuery('#klarna_box_spec').height();



    initPaymentOptions();
}

function pnoUpdated (box, companyAllowed) {
    var pno_value = jQuery.trim(jQuery(box).val());

    // Set the PNO to the other fields
    jQuery('.Klarna_pnoInputField').val(pno_value);

    // Do check
    if (pno_value != "") {
        jQuery('.klarna_box_bottom_content_loader').is(":hidden").fadeIn('fast');


        if (!validateSocialSecurity(pno_value)) {
            jQuery('.klarna_box_bottom_content_loader').fadeOut('fast');
            jQuery('.klarna_box_bottom_address').is(":visible").slideUp('fast');
        } else {
            getAddress (jQuery(box).closest('.klarna_box'), pno_value, companyAllowed);
        }
    } else {
        jQuery('.referenceDiv').is(":visible").slideUp('fast');
        // jQuery('.referenceDiv').is(":hidden").css({"display":"none"}); //Ilogic !
        jQuery('.klarna_box_bottom_content_loader').fadeOut('fast');

        jQuery('.klarna_box_bottom_address').is(":visible").slideUp('fast');
		//jQuery('.klarna_box_bottom_address').is(":hidden").css({"display":"none"}); // Ilogic !

    }
}

/**
 * Showing and hiding the ILT questions
 *
 * @param field
 * @param show
 * @param animate
 */
function showHideIlt (field, show, animate)
{
    if (show == false)
    {
        if (animate == true)
            field.slideUp('fast');
        else
            field.hide();
    }
    else {
        var length = field.find('.klarna_box_iltContents').find('.klarna_box_ilt_question').length;

        if (length > 0)
        {
            if (animate == true)
                field.slideDown('fast');
            else
                field.show();
        }

    }
}

function prepareRedBaloon ()
{
    if (red_baloon_content != '') {
        if ( typeof code == 'undefined' ) {
            code = '';
        }
        klarna.errorHandler.show(jQuery('#'+red_baloon_box), red_baloon_content, code, '');
    }
}

function getAddress (parentBox, pno_value, companyAllowed)
{
    if (!address_busy)
    {
        address_busy = true;

        data = {
            action: 'getAddress',
            country: global_countryCode,
            pno: pno_value
        }

        // Get the new klarna_box
        jQuery.ajax({
            type: "GET",
            url: ajax_path,
            data: data,
            success: function(xml){
                jQuery(xml).find('error').each(function() {
                    var msg = jQuery(this).find('message').text();
                    var code = jQuery(this).find('code').text();
                    var type = jQuery(this).find('type').text();
                    jQuery('.klarna_box_bottom_content_loader').fadeOut('fast', function () {
                        address_busy = false;
                    });
                    klarna.errorHandler.show(parentBox, msg, code, type);
                });

                jQuery(xml).find('getAddress').each(function() {
                    addresses = AddressCollection.fromXML(this);

                    if (typeof params_invoice != "undefined")
                        addresses.render('#klarna_box_invoice', params_invoice['shipmentAddressInput']);

                    if (typeof params_part != "undefined")
                        addresses.render('#klarna_box_part', params_part['shipmentAddressInput']);

                    if (typeof params_spec != "undefined")
                        addresses.render('#klarna_box_spec', params_spec['shipmentAddressInput']);

                    jQuery.each(addresses.addresses, function(i, addr) {
                        if (addr.isCompany) {
                            jQuery('#invoiceType').val("company");
                            jQuery('.referenceDiv').slideDown('fast');

                            if (addresses.mode == Address.Single)
                            {
                                jQuery('.klarna_box_bottom').animate({"min-height": "300px"},'fast');
                            }

                            if (companyAllowed == false && typeof lang_companyNotAllowed != "undefined")
                            {
                                showRedBaloon(jQuery(box));
                                jQuery('#klarna_red_baloon_content div').html(lang_companyNotAllowed);
                                showing_companyNotAlowed_box = true;
                            }
                            else {
                                hideRedBaloon();
                            }
                        } else {
                            jQuery('#invoiceType').val("private");
                            jQuery(document).find('.referenceDiv').slideUp('fast');

                            jQuery('.klarna_box_bottom').animate({"min-height": "250px"},'fast');

                            if (showing_companyNotAlowed_box)
                                hideRedBaloon();
                        }
                    });

                    jQuery('.klarna_box_bottom_address').slideDown('fast');
                    jQuery('.klarna_box_bottom_content_loader').fadeOut('fast', function () {
                        address_busy = false;
                        hideRedBaloon();
                    });
                });
                address_busy = false;
            }
        });
    }
}

function showBlueBaloon (x, y, text)
{
    jQuery('#klarna_blue_baloon_content div').html(text);

    var top = (y - jQuery('#klarna_blue_baloon').height())-5;

    var left = (x - (jQuery('#klarna_blue_baloon').width()/2)+5);

    jQuery('#klarna_blue_baloon').css({"left": left, "top": top});

    jQuery('#klarna_blue_baloon').show();
}

function hideBlueBaloon ()
{
    jQuery('#klarna_blue_baloon').hide();
}

function showRedBaloon (box) {
    if (red_baloon_busy)
        return;

    red_baloon_busy = true;
    var field;
    if (typeof box == 'undefined') {
        if (gChoice == "klarna_invoice") {
            box = jQuery('#klarna_box_invoice');
        } else if (gChoice == "klarna_partPayment") {
            box = jQuery('#klarna_box_part');
        } else if (gChoice == "klarna_SpecCamp") {
            box = jQuery('#klarna_box_spec');
        }
    }

    if (typeof box != 'undefined') {
        field = box.find('.klarna_logo');
    }

    if (typeof field == 'undefined' || field.length == 0) {
        field = jQuery('.klarna_logo:visible');
    }

    var position = field.offset();
    var top = (position.top - jQuery('#klarna_red_baloon').height()) + (jQuery('#klarna_red_baloon').height() / 6);
    if (top < 0) top = 10;
    position.top = top;

    var left = (position.left + field.width()) - (jQuery('#klarna_red_baloon').width() / 2);

    position.left = left;

    jQuery('#klarna_red_baloon').css(position);

    jQuery('#klarna_red_baloon').fadeIn('slow', function () {
        red_baloon_busy = false;

        setTimeout('fadeRedBaloon()', 3000);
    });
}

function fadeRedBaloon ()
{
    if (red_baloon_busy)
        return;

    jQuery('#klarna_red_baloon').addClass('klarna_fading_baloon');
}

function hideRedBaloon ()
{
    if (red_baloon_busy)
        return;

    if (jQuery('#klarna_red_baloon').is(':visible') && !red_baloon_busy)
    {
        jQuery('#klarna_red_baloon').fadeOut('fast', function () {
            red_baloon_busy = false;
            showing_companyNotAlowed_box = false;
        });
    }
}

/**
 * This function is only available for swedish social security numbers
 */
function validateSocialSecurity (vPNO)
{
    if (typeof vPNO == 'undefined')
        return false;

    return vPNO.match(/^([1-9]{2})?[0-9]{6}[-\+]?[0-9]{4}$/)
}

function resetListBox (listBox)
{
    listBox.find('li').each(function (){
        if (jQuery(this).attr("id") == "click")
        {
            jQuery(this).attr("id", "");
        }

        jQuery(this).find('div').find('img').remove();
    });
}

function hideBaloon (callback)
{
    if (jQuery('#klarna_baloon').is(":visible"))
    {
        jQuery('#klarna_baloon').fadeOut('fast', function (){
            if( callback ) callback();

            return true;
        });
    }
    else {
        if( callback ) callback();
        return true;
    }
}

function setBaloonInPosition (field, red_baloon)
{
    hideBaloon(function (){
        var position = field.offset();
        var name = field.attr('name');
        var value = field.attr('alt');

        if (!value && !red_baloon)
        {
            return false;
        }

        if (!red_baloon)
        {
            jQuery('#klarna_baloon_content div').html(value);

            var top = position.top - jQuery('#klarna_baloon').height();
            if (top < 0) top = 10;
            position.top = top;

            var left = (position.left + field.width()) - (jQuery('#klarna_baloon').width() - 50);

            position.left = left;

            jQuery('#klarna_baloon').css(position);

            jQuery('#klarna_baloon').fadeIn('fast');
        }
        else {
            var top = position.top - jQuery('#klarna_red_baloon').height();
            if (top < 0) top = 10;
            position.top = top;

            var left = (position.left + field.width()) - (jQuery('#klarna_red_baloon').width() - 50);

            position.left = left;

            jQuery('#klarna_red_baloon').css(position);

            jQuery('#klarna_red_baloon').fadeIn('fast');
        }
    });
}

function saveDates(replaceBox) {
    select_part_bday = jQuery(replaceBox).find('#selectBox_part_bday').val();
    select_part_bmonth = jQuery(replaceBox).find('#selectBox_part_bmonth').val();
    select_part_byear = jQuery(replaceBox).find('#selectBox_part_year').val();

    select_spec_bday = jQuery(replaceBox).find('#selectBox_spec_bday').val();
    select_spec_bmonth = jQuery(replaceBox).find('#selectBox_spec_bmonth').val();
    select_spec_byear = jQuery(replaceBox).find('#selectBox_spec_year').val();

    select_bday = jQuery(replaceBox).find('#selectBox_bday').val();
    select_bmonth = jQuery(replaceBox).find('#selectBox_bmonth').val();
    select_byear = jQuery(replaceBox).find('#selectBox_year').val();
}

function changeLanguage (replaceBox, params, newIso, country, type)
{
    var paramString    = "";
    var valueString = "";

    data = {
        action: 'languagepack',
        subAction: 'klarna_box',
        type: type,
        newIso: newIso,
        country: country,
        sum: global_sum,
        fee: klarna_invoice_fee,
        flag: global_flag
    }

    // include current field values in request so that the values can be used
    // in the translation
    for (var attr in params) {
        data['params[' + attr + ']'] = params[attr];
        var inputValue = jQuery(replaceBox).find('input[name=' + params[attr] + ']').val();
        if (typeof inputValue != "undefined") {
            data['values[' + attr + ']'] = inputValue;
        }
    }
	virtuemart_paymentmethod_id = jQuery(replaceBox).parents('table').find('.klarmaPaiement').val();
	data['cid'] = virtuemart_paymentmethod_id;
    saveDates(replaceBox);
    jQuery.ajax({
        type: "GET",
        url: ajax_path,
        data: data,
        success: function(response){
			console.log(response);
            if (jQuery(response).find('.klarna_box'))
            {
                replaceBox.find('.klarna_box').remove();
                replaceBox.append(jQuery(response).find('.klarna_box'));
                if (type == "invoice")
                {
                    if (newIso != global_language_invoice)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });

                    klarna_invoiceReady();
                }
                if (type == "part")
                {
                    if(newIso != global_language_part)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });

                    klarna_partReady();
                }

                if (type == "spec")
                {
                    if(newIso != global_language_spec)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });

                    klarna_specReady();
                }
                initPaymentOptions(replaceBox);
            } else {
                alert("Error, block not found. Response:\n\n"+response);
            }
        }
    });
}
