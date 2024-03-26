<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
?>

<?php if( !empty($day_counts) ): ?>

<h3 class="seto-text-center"><strong><?php esc_html_e("SEARCHES LAST 90 DAYS", "search-tools"); ?>:</strong></h3>

<script>						
    const search_insights_graph_days = <?php echo wp_json_encode($days); ?>;
    const search_insights_graph_data = <?php echo wp_json_encode($day_counts); ?>;
    const search_insights_graph_label = "<?php esc_html_e("Count", "search-tools"); ?>";
</script>

<div id="seto_chart"></div>

<?php else: ?>
    
    <h2><?php esc_html_e("No searches collected yet. Do one by yourself or wait for visitors data.", "search-tools"); ?></h2>
    
<?php endif; ?>