jQuery(document).ready(function()
{
    console.log("escondend");
        jQuery(".calendar-container").each(function(i) {
            console.log(";"+i);
            if(i>0)
                jQuery(this).css("display", "none");
        })
    var counter = 0;

    jQuery('.calendar-container').each(function(index)
    {
        jQuery(this).attr('id', 'calendar-' + index);

        var caption = '';

        if (0 < index)
        {
            caption += '<a href="#" onclick="jQuery(\'#calendar-' + index + '\').hide(); jQuery(\'#calendar-' + (index - 1) + '\').show(); return false;">&lt;</a> ';
        }

        caption += jQuery('.month-year-caption', this).html();

        jQuery('.month-year-caption', this).html(caption);

        if (0 < index)
        {
            jQuery(this).hide();
        }

        counter = index;
    });

    for (var i = 0; i < counter; i++)
    {
        var caption = jQuery('#calendar-' + i + ' .month-year-caption', this).html();

        caption += ' <a href="#" onclick="jQuery(\'#calendar-' + i + '\').hide(); jQuery(\'#calendar-' + (i + 1) + '\').show(); return false;">&gt;</a>';

        jQuery('#calendar-' + i + ' .month-year-caption').html(caption);
    }
});