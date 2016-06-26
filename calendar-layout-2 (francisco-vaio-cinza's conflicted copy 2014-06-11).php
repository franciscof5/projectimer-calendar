<style type="text/css">
ul.calendar li.day .author-ranking {
    display: none;
    overflow-y:auto; 
    max-height: 80px;
    background: #FFF;
    /*border-bottom-left-radius: 5px;
    border-bottom-right-radius: 5px; */
}

ul.calendar li.day .author-ranking li {
    height: 15px;
}
ul.calendar li.day .day-footer li:hover {
    background-color: #CAC5A7 !important;
}
ul.calendar li.day .author-ranking span {
    overflow: hidden;
    white-space: nowrap;
    float:left;
}
ul.calendar li.day .author-ranking span.aut_pos {
    width: 10%;
}
ul.calendar li.day .author-ranking span.aut_nome {
    width: 30%;

}
ul.calendar li.day .author-ranking span.aut_barra {
    width: 40%;
    
}
ul.calendar li.day .author-ranking span.aut_total {
    width: 10%;
    float:right;
    padding-right: 5px;
}

/*
ul.calendar li.day .day-header:hover .author-ranking {
    display: block;
}
/*

ul.calendar li.day:hover .author-ranking {
    display: block;
    overflow-y:auto; 
    height: 80px;
}

ul.calendar li.day div.day-header:hover 
    ul.calendar li.day .author-ranking {
        display: none;
    }
ul.calendar li.day div.day-footer:hover 

    /ul.calendar li.day .author-ranking {
    ul.calendar li.day:nth-child(0) {/
    .author-ranking {
        background-color: #09d;
        display: none !important;
    }
*/
ul.calendar li.day {
    overflow: hidden !important;
}
ul.calendar li.day div.day-footer {
    overflow: hidden;
    height: 130px;
    width:100%;
}
ul.calendar li.day div.day-footer {
    border-top:1px dotted #333;
}
ul.calendar li.day div.day-footer:hover {
    overflow-y: auto !important;
}

ul.calendar li.day ul li {
    white-space: nowrap;
}
ul.calendar li.day ul li:hover {
    white-space: normal;
}
/*for a span above*/
.show-hour {
    float: right;
    font-size: 10px;
}
/**/
ul.weekdays li {
    background-color: #063;
    color: #FFF !important;
}
</style>
<script type="text/javascript">
    if(window.jQuery) {
        /*jQuery("ul.calendar li.day").find('.day-header').mouseover(function(){
            jQuery(this).next().css('display', "block");
        })
        jQuery("ul.calendar li.day").find('.author-ranking').mouseleave(function(){
            jQuery(this).css('display', "none");
        })
        jQuery("ul.calendar li.day").find('.author-ranking').focusout(function(){
            jQuery(this).css('display', "none");
        })
        jQuery("ul.calendar li.day").find('.day-header').mouseleave(function(){
            jQuery(this).find('.author-ranking').css('display', "none");
        })

        jQuery("ul.calendar li.day").find('.day-footer').mouseenter(function(){
            jQuery(this).parent().find('.author-ranking').css('display', "none");
        })
        */
        /*todo: paiting the 3 firts bars colors to GOLD SILVER and BRONZE*/
        jQuery("ul.calendar li.day .author-ranking ul").each(function(i) {
            /*jQuery("li").each(function(i) { });*/
            console.log("ul:".i);
            jQuery("li").each(function(i) {
                console.log("li:".i);
            });
        });
        /* For the effect of hide and show the ranking when mouses passes over day header */
        jQuery("ul.calendar li.day").find('.day-caption').mouseenter(function(){
            //jQuery(this).find('.author-ranking').show();
            jQuery(this).find('.author-ranking').css('display', "block");
            //console.log("mouseenter day caption");
        })
        jQuery("ul.calendar li.day").find('.author-ranking').mouseleave(function(){
            //jQuery(this).hide();
            jQuery(this).css('display', "none");
            //console.log("saiu author ranking");
        })
        jQuery("ul.calendar li.day").mouseleave(function(){
            jQuery(this).find('.author-ranking').show();
            //jQuery(this).find('.author-ranking').css('display', "none");
        })
    }
</script>
<div class="calendar-container">
    <h1 class="month-year-caption"><?php echo date('F', $timeForFirstDayOfMonth); ?> <?php echo $year; ?></h1>
    <ul class="weekdays">
<?php
    // Loop for seven times to output weekday names
    for ($counter = 0, $i = $firstDayOfWeek; 7 > $counter; $counter++, $i++)
    {
?>
        <li><?php echo $weekdays[$i]; ?></li>
<?php
        // If counter reached to 6, set it to -1
        if (6 == $i)
        {
            $i = -1;
        }
    }
?>
    </ul><br class="clear" />
    <ul class="calendar">
<?php
    // Total number of days in current month/year
    $totalDaysInMonth = date('t', $timeForFirstDayOfMonth);

    // Weekday for first day of current month/year
    $weekdayForFirstDayOfMonth = date('w', $timeForFirstDayOfMonth);

    // If 'first day of week' is not equal to weekday for first day of month then proceed further to output empty TDs
    if ($firstDayOfWeek != $weekdayForFirstDayOfMonth)
    {
        // Calculate total empty days
        $totalEmptyDays = ($weekdayForFirstDayOfMonth - $firstDayOfWeek);

        // If first day of week is greater than weekday for first day of month then add 7 days to total empty days
        if ($firstDayOfWeek > $weekdayForFirstDayOfMonth)
        {
            $totalEmptyDays += 7;
        }

        // Loop for 'total empty days' to output empty LIs if first day of current month/year doesn't start on 'first day of week'
        for ($i = 0; $i < $totalEmptyDays; $i++)
        {
?>
        <li class="empty">&nbsp;</li>
<?php
        }
    }

    // Loop for total number of days in current month/year to output calendar with posts
    for ($day = 1; $day <= $totalDaysInMonth; $day++)
    {
        // If new week started then close current UL and start new one
        if (1 < $day && $firstDayOfWeek == date('w', mktime(0, 0, 0, $month, $day, $year)))
        {
?>
    </ul><br class="clear" />
    <ul class="calendar">
<?php
        }

        // Initialize variable used to store background image
        $backgroundImage = false;

        // If background image set for current day in current month/year then use it
        if (isset($backgroundImages[$month][$day]) && false !== $backgroundImages[$month][$day])
        {
            $backgroundImage = $backgroundImages[$month][$day];
        }
?>
        <li class="day"<?php echo ($backgroundImage ? ' style="background-image: url(' . $this->getImageUrl($backgroundImage, $boxDimension) . ');"' : ''); ?>>
<?php
        // If background image set for current day in current month/year then display that day in black/white
        if ($backgroundImage)
        {
?>
            <div class="blackDay"><?php echo $day; ?></div>
            <div class="whiteDay"><?php echo $day; ?></div><br class="clear" />
<?php
        }
        // If background image is not set for current day in current month/year then display that day simply
        $array_usuarios_dos_posts = array();
        
        ?>
        <div class="day-header">
            <div class="day-caption">
                <?php echo $day; ?>
                <?php if (isset($postsPerDay[$month][$day])) { ?>
               
                    <?php 
                    //echo "Dia ";
                    
                    
                    foreach ($postsPerDay[$month][$day] as $key => $index) {
                        //echo $posts[$index]->post_author;
                        $authorname = get_the_author_meta('display_name', $posts[$index]->post_author);
                        
                            array_push($array_usuarios_dos_posts[]=$authorname);

                    }
                    $authors_sum_of_posts = array_count_values($array_usuarios_dos_posts);
                    arsort($authors_sum_of_posts);
                    //echo $authors_sum_of_posts=>0."<hr />";
                    $top_author = array_shift(array_keys($authors_sum_of_posts));
                    $top_author_value = (array_shift(array_values($authors_sum_of_posts))/2);
                    //var_dump($top_author_value);
                    //var_dump(($top_author));
                    if($top_author=="") 
                    $top_author = "anônimo"; 
                    else
                    $top_author = substr($top_author, 0,10);
                    //var_dump(count($postsPerDay[$month][$day]));
                    $total_hours_of_day = (count($postsPerDay[$month][$day])/2);
                    //echo " top:";

                    //echo $day;
                    echo ' <a style="text-align:center">'.$top_author."</a> ";
                    echo "<span class='show-hour'>";
                    
                    //echo $top_author_value."h ";
                    //echo " |";
                    echo " total ".$total_hours_of_day."h";
                    echo "</span>";
                    ?>

                    <div class="author-ranking">
                    <ul>
                        <?php
                        $index=0;
                        foreach ($authors_sum_of_posts as $key => $value) {
                            if($index==0)
                            $valuemax = $value;
                            #console_log();

                            $index++;
                            if($key=="") 
                            $key = "anônimo"; 
                            ?>
                            <li width="100%">
                                <span class="aut_pos">
                                    <?php echo $index; ?>
                                </span>
                                <span class="aut_nome">
                                    <?php echo substr($key,0,9); ?>
                                </span>
                                <span class="aut_barra">
                                    <div style="
                                    width:<?php echo (($value/$valuemax)*100); ?>%;
                                    border-radius:3px;
                                    background-color: #DDD;
                                    height:10px;
                                    margin-top:5px;
                                    float:none;
                                    ">&nbsp;
                                    </div>
                                    <?php #echo (($value/$valuemax)*40);#echo $value; ?>
                                </span>
                                <span class="aut_total">
                                    <?php echo ($value/2); ?>h
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                
                <?php } ?>
            </div>
        </div>

        <?php
        // If any post(s) for current day in current month/year then display it/them
        if (isset($postsPerDay[$month][$day]))
        {
?>
            <div class="day-footer">
            <ul<?php echo ($backgroundImage ? ' class="invisible"' : ''); ?>>
<?php
            // Loop through post(s) for current day in current month/year to display it/them
            //var_dump($postsPerDay[$month][$day]);die;
            foreach ($postsPerDay[$month][$day] as $key => $index)
            {
?>
                <li>
                    
                    
                    <?php //echo the_time( "G:i",$posts[$index]->ID); 
                    //var_dump($posts[$index]);die;
                        echo substr($posts[$index]->post_date, 11,5) ;
                    ?>
                    <a href="#">
                        <?php  
                        //echo $posts[$index]->post_author;
                        echo get_the_author_meta('display_name', $posts[$index]->post_author);
                        //$post = get_post( $posts[$index]->ID) ); 
                        // echo $post->post_author; ); 
                        ?> 
                    </a>
                    <a href="<?php echo get_permalink($posts[$index]->ID); ?>" style="color:#333;">
                        <?php echo $posts[$index]->post_title; ?>
                        <?php #the_author(); ?>

                    </a>
                    <!--a title="Ver calendario de " href="<?php bloginfo(url); ?>/calendar/?id=<?php echo $posts[$index]->post_author; ?></a>"-->
                    
                </li>
                
<?php
            }
?>
            </ul>
            </div>
<?php
        }
?>
        </li>
<?php
    }

    // Weekday for last day of current month/year
    $weekdayForLastDayOfMonth = date('w', mktime(0, 0, 0, $month, $totalDaysInMonth, $year));

    // Calculate total empty days
    $totalEmptyDays = ($firstDayOfWeek - $weekdayForLastDayOfMonth - 1);

    // If first day of week is less than or equals to weekday for last day of month then add 7 days to total empty days
    if ($firstDayOfWeek <= $weekdayForLastDayOfMonth)
    {
        $totalEmptyDays += 7;
    }

    // Loop for 'total empty days' to output empty TDs if last day of current month/year doesn't end on 'first day of week'
    for ($i = 0; $i < $totalEmptyDays; $i++)
    {
?>
        <li class="empty">&nbsp;</li>
<?php
    }
?>
    </ul><br class="clear" /><br class="clear" />
</div>