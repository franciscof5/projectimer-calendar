<style>
<!--
input.backgroundColorPreview {
    text-align: center;
}

input#day_background_color_preview {
    background-color: <?php echo $dayBackgroundColor; ?>;
}

input#day_box_background_color_preview {
    background-color: <?php echo $dayBoxBackgroundColor; ?>;
}

input#weekday_background_color_preview {
    background-color: <?php echo $weekdayBackgroundColor; ?>;
}
-->
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
	<h2>Calendar Archives Settings - By <a href="http://www.sanisoft.com" target="_blank">SANIsoft</a></h2>
    <form action="options.php" method="post">
        <?php settings_fields('ProjectimerCalendar_optionsGroup'); ?>
        <?php do_settings_sections('calendar-archives'); ?>
        <?php submit_button(); ?>
    </form>
    <h5>WordPress plugin by <a href="http://www.sanisoft.com/blog/author/amitbadkas/">Amit Badkas</a></h5>
</div>
<script src="<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!--
jQuery(document).ready(function()
{
    jQuery(".backgroundColorField").keyup(function()
    {
        jQuery("#" + this.id + "_preview").css("background-color", this.value);
    });
});
-->
</script>