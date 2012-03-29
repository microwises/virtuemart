var part_active = false;
var part_different_language = false;

// Load when document finished loading
jQuery(document).ready(function() {
    klarna_partReady();
});

function klarna_partReady() {
    var foundBox = false;
    var currentMinHeight_part = jQuery('#klarna_box_part').height();
    // Select birthdate and fill years box
    if (global_countryCode == "de" || global_countryCode == "nl")
    {
        var date = new Date();
        for (i = date.getFullYear(); i >= 1900; i--)
        {
            jQuery('<option/>').val(i).text(i).appendTo('#selectBox_part_year')
        }

        if(typeof select_part_bday != "undefined") {
            jQuery('#selectBox_part_bday').val(select_part_bday);
        }

        if(typeof select_part_bmonth != "undefined") {
            jQuery('#selectBox_part_bmonth').val(select_part_bmonth);
        }

        if(typeof select_part_byear != "undefined") {
            jQuery('#selectBox_part_year').val(select_part_byear);
        }
    }

    // Chosing the active language
    jQuery('#box_active_language').click(function() {
        jQuery('.klarna_box_top_flag_list').slideToggle('fast', function() {
            if (jQuery(this).is(':visible')) {
                jQuery('.klarna_box_top_flag').animate( {
                    opacity : 1.0
                }, 'fast');
            } else {
                jQuery('.klarna_box_top_flag').animate( {
                    opacity : 0.4
                }, 'fast');
            }
        });
    });

}

function resetListBox(listBox) {
    listBox.find('li').each(function() {
        if (jQuery(this).attr("id") == "click") {
            jQuery(this).attr("id", "");
        }

        jQuery(this).find('div').find('img').remove();
    });
}
