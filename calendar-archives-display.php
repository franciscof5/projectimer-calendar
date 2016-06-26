<?php
// Get current page's URL
$pageUrl = get_page_link(get_the_ID());

// Links for calendar years
$yearsLinks = array();

// Loop through list of years to display link for each year
foreach ($years as $yearDetails)
{
    $queryArguments = array('calendar_year' => $yearDetails->year);
    if ($category) : $queryArguments['category'] = $category; endif;
    $yearsLinks[] = '<a href="' . add_query_arg($queryArguments, $pageUrl) . '">' . $yearDetails->year . '</a>';
}

// Output links for calendar years
printf(__('Ver anos anteriores: %s', 'calendar-archives'), implode(' ', $yearsLinks));

// Current month and year
$currentMonth = date('n');
$currentYear = date('Y');

// First day of week to use
$firstDayOfWeek = (int)$options['first_day_of_week'];

// Setting flag 'hide no posts months'
$hideNoPostsMonths = (bool)$options['hide_no_posts_months'];

// Setting flag 'reverse months'
$reverseMonths = (bool)$options['reverse_months'];

// Initialize needed flag
$showFutureMonths = false;

// Setting flag 'show future posts'
$showFuturePosts = (bool)$options['show_future_posts'];

// Weekdays
$weekdays = array
(
    __('Domingo', 'calendar-archives')
    , __('Segunda', 'calendar-archives')
    , __('Terça-feira', 'calendar-archives')
    , __('Quarta-feira', 'calendar-archives')
    , __('Quinta-feira', 'calendar-archives')
    , __('Sexta-feira', 'calendar-archives')
    , __('Sábado', 'calendar-archives')
);

// Loop through months to display calendar with posts
for ($month = ($reverseMonths ? 12 : 1); ($reverseMonths ? 0 < $month : 12 >= $month); ($reverseMonths ? $month-- : $month++))
{
    /**
     * Move to next month if any of the following matches
     * - If 'hide no posts months' setting flag is ON and there are no posts for current month in current year
     * - If 'hide no posts months' and 'show future posts' setting flags are OFF and current month-year is in future
     */
    if ($hideNoPostsMonths)
    {
        if (!isset($postsPerDay[$month]))
        {
            continue;
        }
    }
    else if (!$hideNoPostsMonths && $currentYear <= $year && $currentMonth < $month)
    {
        if (isset($postsPerDay[$month]))
        {
            $showFutureMonths = true;
        }

        if (!$showFuturePosts || !$showFutureMonths)
        {
            continue;
        }
    }

    // Time for first day of current month/year
    $timeForFirstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);

    // Include needed layout
    include('calendar-layout-' . $layout . '.php');
}

// Include needed javascript only when 'browse by month' setting is ON
if ((bool)$options['browse_by_month'])
{
?>
<script src="<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js" type="text/javascript"></script>
<script src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/calendar-archives/calendar-archives-display.js" type="text/javascript"></script>
<?php
}
