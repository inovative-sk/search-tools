<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
?>

<h3 class="st-text-center"><strong><?php _e("SEARCHES LAST 90 DAYS", "search-tools"); ?>:</strong></h3>

<script>						
    const search_insights_graph_days = <?php echo json_encode($days); ?>;
    const search_insights_graph_data = <?php echo json_encode($day_counts); ?>;
    const search_insights_graph_label = "<?php _e("Count", "search-tools"); ?>";
</script>

<div id="st_chart"></div>