<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    $home_url = home_url();
?>

<div class="seto-dashboard-wrap">
    <ul class="seto-dashboard-tabnav">
        <li><a class="seto-dashboard-tablink is-active" href="#seto_tab_1"><?php esc_html_e('TODAY', 'search-tools'); ?></a></li>
        <li><a class="seto-dashboard-tablink" href="#seto_tab_2"><?php esc_html_e('7 DAYS', 'search-tools'); ?></a></li>
        <li><a class="seto-dashboard-tablink" href="#seto_tab_3"><?php esc_html_e('OVERALL', 'search-tools'); ?></a></li>
    </ul>

    <div class="seto-dashboard-tabcontent-wrap">
        <div id="seto_tab_1" class="seto-dashboard-tabcontent is-active">
            <?php if( $today_html ): ?>
                <table class="seto-table seto-dashboard-table">
                    <thead>
                        <tr>
                            <th class="seto-text-center">#</th>
                            <th><?php esc_html_e("query", "search-tools"); ?></th>
                            <th class="seto-text-center"><?php esc_html_e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo wp_kses_post($today_html); ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="seto-no-results">
                    <?php esc_html_e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="seto_tab_2" class="seto-dashboard-tabcontent">
            <?php if( $last_7_days_html ): ?>
                <table class="seto-table seto-dashboard-table">
                    <thead>
                        <tr>
                            <th class="seto-text-center">#</th>
                            <th><?php esc_html_e("query", "search-tools"); ?></th>
                            <th class="seto-text-center"><?php esc_html_e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo wp_kses_post($last_7_days_html); ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="seto-no-results">
                    <?php esc_html_e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="seto_tab_3" class="seto-dashboard-tabcontent">
            <?php if( $overall_html ): ?>
                <table class="seto-table seto-dashboard-table">
                    <thead>
                        <tr>
                            <th class="seto-text-center">#</th>
                            <th><?php esc_html_e("query", "search-tools"); ?></th>
                            <th class="seto-text-center"><?php esc_html_e("searched", "search-tools"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php echo wp_kses_post($overall_html); ?>        
                    </tbody>
                </table>
            <?php else: ?>
                <div class="seto-no-results">
                    <?php esc_html_e("No searches for this period yet.", "search-tools"); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="seto-text-center">
        <a href='<?php echo esc_url($home_url); ?>/wp-admin/admin.php?page=search-tools-insights'><?php esc_html_e("See more detailed overviews", "search-tools"); ?></a>
    </div>
</div>

<section style="margin:1rem -12px -12px -12px; background: #e7ded0;">
    <h1 style="padding: 1rem; text-align: center; letter-spacing: 0.1rem; font-size: 1.5rem; font-weight: bold; text-shadow: rgba(0,0,0,0.01) 1px 2px 0;">
        <a href="https://www.wpsearchtools.com" target="_blank" style="color: #000; text-decoration: none;"><small>visit</small> WPSEARCHTOOLS<small>.com</small></a>
    </h1>
</section>