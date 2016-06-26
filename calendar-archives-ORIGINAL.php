<?php

/*
Plugin Name: Calendar Archives
Plugin URI: http://www.sanisoft.com/blog/2009/08/21/wordpress-plugin-calendar-archives/
Description: Calendar Archives is a visualization plugin for your WordPress site which creates yearly calendar for your posts. Create a new page (having 'no sidebars' layout) for your calendar archive and insert the code [calendar-archive] in the editor. Load this page and enjoy the view!
Version: 3.1
Author: Amit Badkas
Author URI: http://www.sanisoft.com/blog/author/amitbadkas/
*/

/*  Copyright 2013  Amit Badkas  (email : amit@sanisoft.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class CalendarArchives
{
    function __construct()
    {
        // Call method to build everything needed by constructor
        $this->CalendarArchives();
    }

    function CalendarArchives()
    {
        // Plugin's directory
        $pluginDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR;

        // Path to cache generated HTML calendar
        $this->cachePath = $pluginDirectory . 'cached_pages' . DIRECTORY_SEPARATOR;

        // Path to cache background images
        $this->backgroundImagesCachePath = $pluginDirectory . 'cached_images' . DIRECTORY_SEPARATOR;

        // Add shortcode handler
        add_shortcode('calendar-archive', array(&$this, 'display'));

        // Add 'options' page
        add_action('admin_menu', array(&$this, 'adminMenu'));

        // Need to delete cache whenever post added/edited/deleted
        add_action('delete_post', array(&$this, 'deleteCache'));
        add_action('edit_post', array(&$this, 'deleteCache'));
        add_action('save_post', array(&$this, 'deleteCache'));

        // Init
        add_action('admin_init', array(&$this, 'adminInit'));
    }

    function activate()
    {
        // Need to build options while activation
        $this->__getOptions();
    }

    function __getOptions()
    {
        // Create cache directory if not exists already
        if (!is_dir($this->cachePath) && @mkdir($this->cachePath))
        {
            chmod($this->cachePath, 0777);
        }

        // Create cache directory for images if not exists already
        if (!is_dir($this->backgroundImagesCachePath) && @mkdir($this->backgroundImagesCachePath))
        {
            chmod($this->backgroundImagesCachePath, 0777);
        }

        // Default options
        $default = $this->__getDefaultOptions();

        // Get saved options
        $saved = get_option('CalendarArchives_options');

        // If possible, merge default and saved options to default one
        if (is_array($saved) && 0 < count($saved))
        {
            $default = array_merge($default, $saved);
        }

        // If saved options are different from merged default options then save merged default options as new one
        if ($saved != $default)
        {
            update_option('CalendarArchives_options', $default);
        }

        // Return merged default options
        return $default;
    }

    function __getDefaultOptions()
    {
        // Default options
        $default = array
        (
            'box_dimension' => 140
            , 'browse_by_month' => ''
            , 'cache' => ''
            , 'day_background_color' => '#BFBC94'
            , 'day_box_background_color' => '#DBD7A8'
            , 'featured_image_as_background' => ''
            , 'first_day_of_week' => 0
            , 'hide_no_posts_months' => ''
            , 'layout' => 1
            , 'reverse_months' => ''
            , 'show_future_posts' => ''
            , 'show_images' => ''
            , 'weekday_background_color' => '#A7A37E'
        );
        return $default;
    }

    function deactivate()
    {
        // Delete plugin's options
        delete_option('CalendarArchives_options');
    }

    function plugin_action_links($links, $file)
    {
        // Plugin's file
        $pluginFile = basename(__FILE__);

        // If given file and plugin's file are same then prepend link of plugin's settings page
        if (basename($file) == $pluginFile)
        {
            array_unshift($links, '<a href="options-general.php?page=' . $pluginFile . '">Settings</a>');
        }

        // Return built links
        return $links;
    }

    function adminMenu()
    {
        // Add 'options' page to admin menu
        add_options_page('Calendar Archives Options', 'Calendar Archives', 'manage_options', basename(__FILE__), array(&$this, 'handleOptions'));
    }

    function adminInit()
    {
        // Get options, register settings and add 'usage' section
        $options = $this->__getOptions();
        register_setting('CalendarArchives_optionsGroup', 'CalendarArchives_options', array(&$this, 'sanitizeOptions'));
        add_settings_section('CalendarArchives_usage', 'Usage', array('CalendarArchivesOptions', 'sectionDescription_usage'), 'calendar-archives');

        // Add 'options' section and its fields
        add_settings_section('CalendarArchives_options', 'Options', array('CalendarArchivesOptions', 'sectionDescription_options'), 'calendar-archives');
        add_settings_field('cache', 'Use calendar cache', array('CalendarArchivesOptions', 'field_cache'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'cache', 'writeable' => is_writeable($this->cachePath)) + $options);
        add_settings_field('layout', 'Default layout (1 or 2)', array('CalendarArchivesOptions', 'field_layout'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'layout') + $options);
        add_settings_field('hide_no_posts_months', 'Hide months in which no posts', array('CalendarArchivesOptions', 'field_hideNoPostsMonths'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'hide_no_posts_months') + $options);
        add_settings_field('reverse_months', 'Reverse months', array('CalendarArchivesOptions', 'field_reverseMonths'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'reverse_months') + $options);
        add_settings_field('show_images', 'Show images', array('CalendarArchivesOptions', 'field_showImages'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'show_images', 'remoteImagesDownloadDisabled' => !(bool)ini_get('allow_url_fopen'), 'writeableForImages' => is_writeable($this->backgroundImagesCachePath)) + $options);
        add_settings_field('box_dimension', 'Box/image dimension', array('CalendarArchivesOptions', 'field_boxDimension'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'box_dimension') + $options);
        add_settings_field('first_day_of_week', 'First day of week is', array('CalendarArchivesOptions', 'field_firstDayOfWeek'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'first_day_of_week') + $options);
        add_settings_field('show_future_posts', 'Display upcoming posts', array('CalendarArchivesOptions', 'field_showFuturePosts'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'show_future_posts') + $options);
        add_settings_field('browse_by_month', 'Browse by month', array('CalendarArchivesOptions', 'field_browseByMonth'), 'calendar-archives', 'CalendarArchives_options', array('label_for' => 'browse_by_month') + $options);
        add_settings_field('featured_image_as_background', 'Use featured image as background', array('CalendarArchivesOptions', 'field_featuredImageAsBackground'), 'calendar-archives', 'CalendarArchives_options', $options);

        // Add 'background' section and its fields
        add_settings_section('CalendarArchives_background', 'Background Colors', array('CalendarArchivesOptions', 'sectionDescription_background'), 'calendar-archives');
        add_settings_field('weekday_background_color', 'Weekday', array('CalendarArchivesOptions', 'field_weekdayBackgroundColor'), 'calendar-archives', 'CalendarArchives_background', array('label_for' => 'weekday_background_color') + $options);
        add_settings_field('day_box_background_color', 'Day box', array('CalendarArchivesOptions', 'field_dayBoxBackgroundColor'), 'calendar-archives', 'CalendarArchives_background', array('label_for' => 'day_box_background_color') + $options);
        add_settings_field('day_background_color', 'Day (for first layout only)', array('CalendarArchivesOptions', 'field_dayBackgroundColor'), 'calendar-archives', 'CalendarArchives_background', array('label_for' => 'day_background_color') + $options);
    }

    function handleOptions()
    {
        // If admin wants to remove cached images
        if (isset($_GET['remove_cached_images']) && 'true' == $_GET['remove_cached_images'])
        {
            // Scan directory for cached images and remove them
            foreach ($this->__scanDirectory($this->backgroundImagesCachePath) as $directoryContent)
            {
                if (is_file($this->backgroundImagesCachePath . $directoryContent))
                {
                    unlink($this->backgroundImagesCachePath . $directoryContent);
                }
            }

            // Remove reference of 'remove cached images' flag from request URI and output success message
            $_SERVER['REQUEST_URI'] = str_replace('&remove_cached_images=true', '', $_SERVER['REQUEST_URI']);
            echo '<div class="updated settings-error" id="setting-error-settings_updated"><p><strong>Cached images have been removed successfully</strong></p></div>';
        }

        // Get plugin's options
        $options = $this->__getOptions();

        // Variables to use in form
        $dayBackgroundColor = (string)$options['day_background_color'];
        $dayBoxBackgroundColor = (string)$options['day_box_background_color'];
        $weekdayBackgroundColor = (string)$options['weekday_background_color'];

        // Include options form
        include('calendar-archives-options.php');
    }

    function __scanDirectory($directory)
    {
        // If 'scandir' function exists then use it
        if (function_exists('scandir'))
        {
            return scandir($directory);
        }

        // Initialize variable to store directory contents
        $directoryContents = array();

        // Open directory to read it
        $dh = opendir($directory);

        // If directory failed to open then no need to proceed further
        if (!$dh)
        {
            return $directoryContents;
        }

        // Read directory contents
        while (false !== ($filename = readdir($dh)))
        {
            $directoryContents[] = $filename;
        }

        // Close directory
        closedir($dh);

        // Sort directory contents
        sort($directoryContents);

        // Return directory contents
        return $directoryContents;
    }

    function sanitizeOptions($options)
    {
        // Delete cache
        $this->deleteCache();

        // Merge any missing default options
        $options += $this->__getDefaultOptions();

        // Sanitize 'use featured image as background' setting
        $options['featured_image_as_background'] = (string)$options['featured_image_as_background'];
        if (!in_array($options['featured_image_as_background'], array('always', 'no')))
        {
            $options['featured_image_as_background'] = '';
        }

        // Build options to save
        $options = array
        (
            'box_dimension' => (int)$options['box_dimension']
            , 'browse_by_month' => (string)$options['browse_by_month']
            , 'cache' => (string)$options['cache']
            , 'day_background_color' => (string)$options['day_background_color']
            , 'day_box_background_color' => (string)$options['day_box_background_color']
            , 'featured_image_as_background' => $options['featured_image_as_background']
            , 'first_day_of_week' => (int)$options['first_day_of_week']
            , 'hide_no_posts_months' => (string)$options['hide_no_posts_months']
            , 'layout' => (int)$options['layout']
            , 'reverse_months' => (string)$options['reverse_months']
            , 'show_future_posts' => (string)$options['show_future_posts']
            , 'show_images' => (string)$options['show_images']
            , 'weekday_background_color' => (string)$options['weekday_background_color']
        );
        return $options;
    }

    function deleteCache()
    {
        // Get all cache files
        $cacheFiles = glob($this->cachePath . '*.html');

        // If no cache files found then no need to proceed further
        if (!is_array($cacheFiles) || 0 >= count($cacheFiles))
        {
            return;
        }

        // Remove all cache files
        foreach ($cacheFiles as $cacheFile)
        {
            unlink($cacheFile);
        }
    }

    function display()
    {
        // Global DB object
        global $wpdb;

        // If year is set in URL then get it from there
        if (isset($_GET['calendar_year']))
        {
            $year = (int)$_GET['calendar_year'];
        }

        // If year is not set/valid then use current one
        if (!isset($year))
        {
            $year = date('Y');
        }

        // Initialize variable to store category
        $category = null;

        // If category is set in URL then get it from there
        if (isset($_GET['category']))
        {
            $category = (string)$_GET['category'];
        }

        // Get plugin's options
        $options = $this->__getOptions();

        // Initialize variable to store cache flag
        $cache = (bool)$options['cache'];

        // If cache is enabled and cache file exists then use it to display output
        if ($cache && is_file($cacheFile = $this->cachePath . $year . ($category ? '-' . $category : '') . '.html'))
        {
            return file_get_contents($cacheFile);
        }

        // By default, consider published posts only
        $postStatuses = array('publish');

        // If 'show future posts' flag is enabled then display future posts too
        if ((bool)$options['show_future_posts'])
        {
            $postStatuses[] = 'future';
        }

        // Build condition to display posts for given statuses
        $postStatuses = '("' . implode('", "', $postStatuses) . '")';

        // Conditions to fetch list of posts, by default fetch given year's posts
        $conditions = array('year' => 'YEAR(post_date) = ' . $year);

        // If category is set then filter posts using it
        if ($category)
        {
            // Get list of post IDs for given category
            $postIds = $wpdb->get_col('SELECT ' . $wpdb->term_relationships . '.object_id FROM ' . $wpdb->terms . ', ' . $wpdb->term_taxonomy . ', ' . $wpdb->term_relationships . ' WHERE ' . $wpdb->terms . '.term_id = ' . $wpdb->term_taxonomy . '.term_id AND ' . $wpdb->term_taxonomy . '.term_taxonomy_id = ' . $wpdb->term_relationships . '.term_taxonomy_id AND ' . $wpdb->term_taxonomy . '.taxonomy = "category" AND ' . $wpdb->terms . '.slug = "' . $category . '"');

            // Build posts fetching condition according to category
            $conditions['id'] = 'ID ' . (0 < count($postIds) ? 'IN (' . implode(', ', $postIds) . ')' : 'IS NULL');
        }

        // Get posts for given year
        $posts = $wpdb->get_results('SELECT * FROM ' . $wpdb->posts . ' WHERE post_status IN ' . $postStatuses . ' AND post_password = "" AND post_type = "post" AND ' . implode(' AND ', $conditions) . ' ORDER BY post_date ASC');

        // Initialize variable to store 'show images' flag
        $showImages = (bool)$options['show_images'];

        // Box dimension to use
        $boxDimension = (int)$options['box_dimension'];

        // Initialize variable used to store indexes for posts per day
        $postsPerDay = array();

        // Initialize variable used to store background images
        $backgroundImages = array();

        // Is download for remote images disabled?
        $remoteImagesDownloadDisabled = !(bool)ini_get('allow_url_fopen');

        // Loop through list of posts to build 'posts per day' data
        for ($index = 0; $index < count($posts); $index++)
        {
            // Post's time
            $postTime = strtotime($posts[$index]->post_date);

            // Post's month
            $month = (int)date('m', $postTime);

            // If no posts for given month then initialize array for it
            if (!isset($postsPerDay[$month]))
            {
                $postsPerDay[$month] = array();
            }

            // If no background images for given month then initialize array for it
            if (!isset($backgroundImages[$month]))
            {
                $backgroundImages[$month] = array();
            }

            // Post's month
            $day = (int)date('d', $postTime);

            // If no posts for given month's given day then initialize array for it
            if (!isset($postsPerDay[$month][$day]))
            {
                $postsPerDay[$month][$day] = array();
            }

            // If no background image for given month's given day then initialize boolean flag false for it
            if (!isset($backgroundImages[$month][$day]))
            {
                $backgroundImages[$month][$day] = false;
            }

            // Build needed data
            $postsPerDay[$month][$day][] = $index;

            // If 'show images' flag is enabled and no background image for given month's given day then proceed further
            if ($showImages && false === $backgroundImages[$month][$day])
            {
                // Initialize variable used to store background image
                $backgroundImage = false;

                // Use featured image as background, if applicable
                if ('no' != $options['featured_image_as_background'])
                {
                    // Get post's full-sized featured image
                    $featuredImage = get_the_post_thumbnail($posts[$index]->ID, 'full');

                    // Find and build background image from featured image
                    if (!empty($featuredImage))
                    {
                        $backgroundImage = $this->__findAndBuildBackgroundImage($boxDimension, $featuredImage, $posts[$index]->ID, $remoteImagesDownloadDisabled);
                    }

                    // If no featured image and need to use only featured image then continue to next post
                    if ((empty($featuredImage) || false === $backgroundImage) && 'always' == $options['featured_image_as_background'])
                    {
                        continue;
                    }
                }

                // By default, use post's content to search for background images
                if (false === $backgroundImage)
                {
                    $backgroundImage = $this->__findAndBuildBackgroundImage($boxDimension, $posts[$index]->post_content, $posts[$index]->ID, $remoteImagesDownloadDisabled);
                }

                // If background image built for current month and day then use it
                if (false !== $backgroundImage)
                {
                    $backgroundImages[$month][$day] = $backgroundImage;
                }
            }
        }

        // Get unique years for which posts are available
        $years = $wpdb->get_results('SELECT DISTINCT YEAR(post_date) AS year FROM ' . $wpdb->posts . ' WHERE post_status IN ' . $postStatuses . ' AND post_password = "" AND post_type = "post"' . (isset($conditions['id']) ? ' AND ' . $conditions['id'] : '') . ' ORDER BY post_date DESC');

        // Layout number to use
        $layout = (int)$options['layout'];

        // Various background colors to use
        $dayBackgroundColor = (string)$options['day_background_color'];
        $dayBoxBackgroundColor = (string)$options['day_box_background_color'];
        $weekdayBackgroundColor = (string)$options['weekday_background_color'];

        // Grab output
        ob_start();
        include('calendar-layout-' . $layout . '.css.php');
        include('calendar-archives-display.php');
        $result = ob_get_contents();
        ob_end_clean();

        // If cache is enabled then cache generated output
        if ($cache)
        {
            // Put generated output in cache file
            $this->__filePutContents($cacheFile, $result . '<!-- cached -->');

            // Make cache file world writeable
            chmod($cacheFile, 0777);
        }

        // Return generated output
        return $result;
    }

    function __findAndBuildBackgroundImage($boxDimension, $contentToSearchForImages, $postId, $remoteImagesDownloadDisabled)
    {
        // Initialize variable used to store list of matched images as per provided regular expression
        $matches = array();

        // Get all images from post's body
        preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)/i', $contentToSearchForImages, $matches);

        // If there are any image in post's body then proceed further
        foreach ($matches[1] as $backgroundImage)
        {
            // Initialize variable used to store image's name
            $backgroundImageName = $postId . '-' . md5($backgroundImage);

            // Background image's path
            $backgroundImagePath = $this->backgroundImagesCachePath . $backgroundImageName;

            // By default, no need to rename downloaded file
            $rename = false;

            // If image already downloaded then use it otherwise download it
            if (is_file($backgroundImagePath))
            {
                $rename = true;
            }
            else if (is_file($backgroundImagePath . '.gif'))
            {
                $backgroundImagePath = $backgroundImagePath . '.gif';
            }
            else if (is_file($backgroundImagePath . '.jpg'))
            {
                $backgroundImagePath = $backgroundImagePath . '.jpg';
            }
            else if (is_file($backgroundImagePath . '.png'))
            {
                $backgroundImagePath = $backgroundImagePath . '.png';
            }
            else if ($remoteImagesDownloadDisabled)
            {
                // Replace 'site URL' in background image's URL with 'absolute path'
                $backgroundImage = str_replace(get_option('siteurl') . '/', ABSPATH, $backgroundImage);

                // If image is not local then continue the loop
                if (!is_file($backgroundImage))
                {
                    continue;
                }

                // If image is local then copy it and prepare it for renaming
                copy($backgroundImage, $backgroundImagePath);
                $rename = true;
            }
            else if (false !== ($backgroundImageContents = @file_get_contents($backgroundImage)) && false !== $this->__filePutContents($backgroundImagePath, $backgroundImageContents))
            {
                chmod($backgroundImagePath, 0777);
                $rename = true;
            }
            else
            {
                continue;
            }

            // Get image's information
            $backgroundImageInformation = @getimagesize($backgroundImagePath);

            // If image is valid and its height and width not less than box dimension then use it
            if (false !== $backgroundImageInformation && $boxDimension <= $backgroundImageInformation[0] && $boxDimension <= $backgroundImageInformation[1])
            {
                // Background image's extension
                $backgroundImageExtension = str_replace('image/', '', $backgroundImageInformation['mime']);
                $backgroundImageExtension = (in_array($backgroundImageExtension, array('gif', 'png')) ? $backgroundImageExtension : 'jpg');

                // Rename downloaded image
                if ($rename)
                {
                    rename($backgroundImagePath, $backgroundImagePath . '.' . $backgroundImageExtension);
                }

                // Build background image's name and return it
                return $backgroundImageName . '.' . $backgroundImageExtension;
            }
        }

        // By default, return false
        return false;
    }

    function __filePutContents($filename, $data)
    {
        // If 'file_put_contents' function exists then use it
        if (function_exists('file_put_contents'))
        {
            return file_put_contents($filename, $data);
        }

        // Open file to write it
        $fp = fopen($filename, 'w');

        // If file failed to open then no need to proceed further
        if (!$fp)
        {
            return false;
        }

        // Write data into file
        $bytes = fwrite($fp, $data);

        // Close file
        fclose($fp);

        // Return file write status
        return $bytes;
    }

    function getImageUrl($backgroundImage, $backgroundImageDimension)
    {
        // Get background image's name and file extension
        list($backgroundImageName, $backgroundImageExtension) = explode('.', $backgroundImage);

        // Background image path
        $backgroundImage = $this->backgroundImagesCachePath . $backgroundImage;

        // Re-sized background image's path
        $resizedBackgroundImage = $this->backgroundImagesCachePath . $backgroundImageName . '-' . $backgroundImageDimension . '.' . $backgroundImageExtension;

        // If re-sized background image doesn't exist then create it
        if (!is_file($resizedBackgroundImage))
        {
            // Get background image's width and height
            list($backgroundImageWidth, $backgroundImageHeight) = getimagesize($backgroundImage);

            // If background image's width and height is same as re-sized dimension then copy it instead of re-sizing
            if ($backgroundImageHeight == $backgroundImageDimension && $backgroundImageWidth == $backgroundImageDimension)
            {
                copy($backgroundImage, $resizedBackgroundImage);
            }
            else
            {
                // Include needed library
                require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'image.php';

                // Re-size background image
                image_resize($backgroundImage, $backgroundImageDimension, $backgroundImageDimension, true, $backgroundImageDimension);
            }

            // Make re-sized background image world writeable
            chmod($resizedBackgroundImage, 0777);
        }

        // Return re-sized background image's URL
        return str_replace(ABSPATH, get_option('siteurl') . '/', $resizedBackgroundImage);
    }
}

class CalendarArchivesOptions
{
    function field_boxDimension($options)
    {
        // Ouput field to set box dimension
        echo '<input type="text" name="CalendarArchives_options[box_dimension]" id="box_dimension" size="10" value="' . $options['box_dimension'] . '" /><p class="description">You can provide dimension for calendar\'s day box. This dimension will also apply to background images displayed.</p>';
    }

    function field_browseByMonth($options)
    {
        // Ouput field to toggle 'browse by month' flag
        echo '<input type="checkbox" name="CalendarArchives_options[browse_by_month]" id="browse_by_month" ' . checked($options['browse_by_month'], 'on', false) . ' /><p class="description">If enabled, you can browse the calendar by month.</p>';
    }

    function field_cache($options)
    {
        // Ouput field to toggle 'cache' flag
        if ($options['writeable'])
        {
            echo '<input type="checkbox" name="CalendarArchives_options[cache]" id="cache" ' . checked($options['cache'], 'on', false) . ' />';
        }
        else
        {
            echo '<p class="description" style="color: red;">Your plugin directory (or \'cached_pages\' directory, if it exists, in your plugin directory) is not writeable by WordPress. Caching is not possible.</p>';
        }
    }

    function field_dayBackgroundColor($options)
    {
        // Ouput field to set day background color
        echo '<input type="text" class="backgroundColorPreview" id="day_background_color_preview" size="10" value="Preview" DISABLED /> <input type="text" name="CalendarArchives_options[day_background_color]" class="backgroundColorField" id="day_background_color" size="10" value="' . $options['day_background_color'] . '" />';
    }

    function field_dayBoxBackgroundColor($options)
    {
        // Ouput field to set day box background color
        echo '<input type="text" class="backgroundColorPreview" id="day_box_background_color_preview" size="10" value="Preview" DISABLED /> <input type="text" name="CalendarArchives_options[day_box_background_color]" class="backgroundColorField" id="day_box_background_color" size="10" value="' . $options['day_box_background_color'] . '" />';
    }

    function field_featuredImageAsBackground($options)
    {
        // Output fields to select 'use featured image as background' setting
        echo '<label><input type="radio" name="CalendarArchives_options[featured_image_as_background]" id="featured_image_as_background" ' . checked($options['featured_image_as_background'], '', false) . ' value="" /> ' . __('Only when available', 'calendar-archives') . '</label><br />';
        echo '<label><input type="radio" name="CalendarArchives_options[featured_image_as_background]" id="featured_image_as_background_always" ' . checked($options['featured_image_as_background'], 'always', false) . ' value="always" /> ' . __('Always', 'calendar-archives') . '</label><br />';
        echo '<label><input type="radio" name="CalendarArchives_options[featured_image_as_background]" id="featured_image_as_background_no" ' . checked($options['featured_image_as_background'], 'no', false) . ' value="no" /> ' . __('No', 'calendar-archives') . '</label><br />';
    }

    function field_firstDayOfWeek($options)
    {
        // Weekdays
        $weekdays = array
        (
            __('Sunday', 'calendar-archives')
            , __('Monday', 'calendar-archives')
            , __('Tuesday', 'calendar-archives')
            , __('Wednesday', 'calendar-archives')
            , __('Thursday', 'calendar-archives')
            , __('Friday', 'calendar-archives')
            , __('Saturday', 'calendar-archives')
        );

        // Ouput field to set first day of week
        echo '<select name="CalendarArchives_options[first_day_of_week]" id="first_day_of_week">';
        foreach ($weekdays as $value => $label)
        {
            echo '<option value="' . $value . '" ' . selected($options['first_day_of_week'], $value, false) . '>' . $label . '</option>';
        }
        echo '</select>';
    }

    function field_hideNoPostsMonths($options)
    {
        // Ouput field to toggle 'hide no posts months' flag
        echo '<input type="checkbox" name="CalendarArchives_options[hide_no_posts_months]" id="hide_no_posts_months" ' . checked($options['hide_no_posts_months'], 'on', false) . ' /><p class="description">If enabled, months which don\'t have any posts will not be displayed in calendar.</p>';
    }

    function field_layout($options)
    {
        // Ouput field to set layout
        echo '<input type="text" name="CalendarArchives_options[layout]" id="layout" size="10" value="' . $options['layout'] . '" /><p class="description">Calendar Archives currently supports two layouts, and you can buid your own.</p>';
    }

    function field_reverseMonths($options)
    {
        // Ouput field to toggle 'reverse months' flag
        echo '<input type="checkbox" name="CalendarArchives_options[reverse_months]" id="reverse_months" ' . checked($options['reverse_months'], 'on', false) . ' /><p class="description">If enabled, archive months will be displayed in descending order (December through January).</p>';
    }

    function field_showFuturePosts($options)
    {
        // Ouput field to toggle 'show future posts' flag
        echo '<input type="checkbox" name="CalendarArchives_options[show_future_posts]" id="show_future_posts" ' . checked($options['show_future_posts'], 'on', false) . ' /><p class="description">If enabled, posts which have publish date in future will also be displayed in calendar (useful for events).</p>';
    }

    function field_showImages($options)
    {
        // Ouput field to toggle 'show images' flag
        if ($options['writeableForImages'])
        {
            echo '<input type="checkbox" name="CalendarArchives_options[show_images]" id="show_images" ' . checked($options['show_images'], 'on', false) . ' /><p class="description">Show post images. You can disable this to preserve bandwidth.</p><p><a href="options-general.php?page=calendar-archives.php&remove_cached_images=true" onclick="return confirm(\'Do you really want to remove cached images?\');">Remove cached images</a></p>';

            if ($options['remoteImagesDownloadDisabled'])
            {
                echo '<p class="description" style="color: red;">URL file-access (PHP configuration setting \'allow_url_fopen\') is disabled in the server configuration. Hence, the remote images will not get downloaded and will not be displayed.</p>';
            }
        }
        else
        {
            echo '<p class="description" style="color: red;">Your plugin directory (or \'cached_images\' directory, if it exists, in your plugin directory) is not writeable by WordPress. Display of images for posts is not possible.</p>';
        }
    }

    function field_weekdayBackgroundColor($options)
    {
        // Ouput field to set weekday background color
        echo '<input type="text" class="backgroundColorPreview" id="weekday_background_color_preview" size="10" value="Preview" DISABLED /> <input type="text" name="CalendarArchives_options[weekday_background_color]" class="backgroundColorField" id="weekday_background_color" size="10" value="' . $options['weekday_background_color'] . '" />';
    }

    function sectionDescription_background()
    {
        // Background section's description
        echo 'Here you can customize various background colors used in calendar layouts';
    }

    function sectionDescription_options()
    {
    }

    function sectionDescription_usage()
    {
        // Usage section's description
        echo 'Create a new page (having \'no sidebars\' layout) for your calendar archive and insert the code [calendar-archive] in the editor. Load this page and enjoy the view!';
    }
}

// Create plugin object
$CalendarArchives = new CalendarArchives();

// Register activation hook as plugin's activate() method
register_activation_hook(__FILE__, array(&$CalendarArchives, 'activate'));

// Register de-activation hook as plugin's deactivate() method
register_deactivation_hook(__FILE__, array(&$CalendarArchives, 'deactivate'));

// Add filter for plugin's action links
add_filter('plugin_action_links', array(&$CalendarArchives, 'plugin_action_links'), 10, 2);
