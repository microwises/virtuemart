var invoice_active = false;
var invoice_different_language = false;

// Load when document finished loading
jQuery(document).ready(function (){
    klarna_invoiceReady();
});

function klarna_invoiceReady ()
{
    var foundBox = false;
    var currentMinHeight_invoice = jQuery('#klarna_box_invoice').height();

    // Select birthdate and fill years box
    if (global_countryCode == "de" || global_countryCode == "nl")
    {
        // Years box
        var date = new Date();
        for (i = date.getFullYear(); i >= 1900; i--)
        {
            jQuery('<option/>').val(i).text(i).appendTo('#selectBox_year')
        }

        if(typeof select_bday != "undefined") {
            jQuery('#selectBox_bday').val(select_bday);
        }

        if(typeof select_bmonth != "undefined") {
            jQuery('#selectBox_bmonth').val(select_bmonth);
        }

        if(typeof select_byear != "undefined") {
            jQuery('#selectBox_year').val(select_byear);
        }
    }

    // Chosing the active language
    jQuery('#box_active_language').click(function () {
        jQuery('.klarna_box_top_flag_list').slideToggle('fast', function () {
            if (jQuery(this).is(':visible'))
            {
                jQuery('.klarna_box_top_flag').animate({opacity: 1.0}, 'fast');
            }
            else {
                jQuery('.klarna_box_top_flag').animate({opacity: 0.4}, 'fast');
            }
        });
    });

    if(typeof invoice_ITId != "undefined") {
        jQuery('input[name='+invoice_ITId+']').change(function (){
            var val = jQuery(this).val();

            if (val == "private")
            {
                jQuery('#invoice_perOrg_title').text(lang_personNum);
                jQuery('#invoice_box_private').slideDown('fast');
                jQuery('#invoice_box_company').slideUp('fast');
            }
            else if (val == "company")
            {
                jQuery('#invoice_perOrg_title').text(lang_orgNum);
                jQuery('#invoice_box_company').slideDown('fast');
                jQuery('#invoice_box_private').slideUp('fast');
            }
        });
    }
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
