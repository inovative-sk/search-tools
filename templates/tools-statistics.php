<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
?>

<div class="st-stats-wrap">
    <div class="st-stats">
        <?php echo wp_kses_post($today_html); ?>
    </div>    

    <div class="st-stats">
        <?php echo wp_kses_post($last_7_days_html); ?>
    </div>    

    <div class="st-stats st-stats__bigger">
        <?php echo wp_kses_post($overal_html); ?>
    </div>    

    <div class="st-stats">
        <?php echo wp_kses_post($last_30_days_html); ?>
    </div>    

    <div class="st-stats">
        <?php echo wp_kses_post($this_year_html); ?>
    </div>    
</div>