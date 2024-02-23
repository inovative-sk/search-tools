<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    $home_url = home_url();
?>

<div class="st-dashboard-wrap">
    <ul class="st-dashboard-tabnav">
        <li><a class="st-dashboard-tablink is-active" href="#st_tab_1"><?php _e('TODAY', 'search-tools'); ?></a></li>
        <li><a class="st-dashboard-tablink" href="#st_tab_2"><?php _e('7 DAYS', 'search-tools'); ?></a></li>
        <li><a class="st-dashboard-tablink" href="#st_tab_3"><?php _e('OVERALL', 'search-tools'); ?></a></li>
    </ul>

    <div class="st-dashboard-tabcontent-wrap">
        <div id="st_tab_1" class="st-dashboard-tabcontent is-active">
            <?php if( $today_html ): ?>
                <table class="st-table st-dashboard-table">
                    <thead>
                        <tr>
                            <th class="st-text-center">#</th>
                            <th><?php _e("query", "search-tools"); ?></th>
                            <th class="st-text-center"><?php _e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo $today_html; ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="st-no-results">
                    <?php _e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="st_tab_2" class="st-dashboard-tabcontent">
            <?php if( $last_7_days_html ): ?>
                <table class="st-table st-dashboard-table">
                    <thead>
                        <tr>
                            <th class="st-text-center">#</th>
                            <th><?php _e("query", "search-tools"); ?></th>
                            <th class="st-text-center"><?php _e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo $last_7_days_html; ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="st-no-results">
                    <?php _e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="st_tab_3" class="st-dashboard-tabcontent">
            <?php if( $overall_html ): ?>
                <table class="st-table st-dashboard-table">
                    <thead>
                        <tr>
                            <th class="st-text-center">#</th>
                            <th><?php _e("query", "search-tools"); ?></th>
                            <th class="st-text-center"><?php _e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo $overall_html; ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="st-no-results">
                    <?php _e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="st-text-center">
        <a href='<?php echo $home_url; ?>/wp-admin/admin.php?page=search-tools-insights'><?php _e("See more detailed overviews", "search-tools"); ?></a>
    </div>
</div>

<section style="margin:1rem -12px -12px -12px; background: #e7ded0;">
    <h1 style="padding: 1rem; text-align: center; letter-spacing: 0.1rem; font-size: 2rem; font-weight: bold; text-shadow: rgba(0,0,0,0.01) 1px 2px 0;">
        <a href="https://www.wpsearchtools.com" target="_blank" style="color: #000; text-decoration: none;">WP SEARCH TOOLS</a>
    </h1>
</section>