<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
?>

<div class="st-stats-wrap">
    <div class="st-stats">
        <?php echo $today_html; ?>
    </div>    

    <div class="st-stats">
        <?php echo $last_7_days_html ?>
    </div>    

    <div class="st-stats st-stats__bigger">
        <?php echo $overal_html; ?>
    </div>    

    <div class="st-stats">
        <?php echo $last_30_days_html; ?>
    </div>    

    <div class="st-stats">
        <?php echo $this_year_html; ?>
    </div>    
</div>