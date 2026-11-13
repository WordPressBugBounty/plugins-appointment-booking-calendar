<?php

if ( ! is_admin() ) {
    echo 'Direct access not allowed.';
    exit;
}

if ( ! defined( 'CP_CALENDAR_ID' ) ) {
    $cal_param = isset( $_GET['cal'] ) ? intval( $_GET['cal'] ) : 1;
    define( 'CP_CALENDAR_ID', $cal_param > 0 ? $cal_param : 1 );
}

global $wpdb;

$plugslug       = 'cpabc_appointments.php';
$message        = '';
$mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME . ' WHERE `' . CPABC_TDEAPP_CONFIG_ID . '` = %d', CP_CALENDAR_ID ) );
$calendar_name  = ( isset( $mycalendarrows[0] ) && ! empty( $mycalendarrows[0]->uname ) ) ? $mycalendarrows[0]->uname : '';
$current_user   = wp_get_current_user();

if ( cpabc_appointment_is_administrator() || ( isset( $mycalendarrows[0] ) && (int) $mycalendarrows[0]->conwer === (int) $current_user->ID ) ) {

    $nonce_un = wp_create_nonce( 'uname_abc_bklist' );
    ?>

<style type="text/css">
    .cpabc-admin-wrap {
        max-width: 1360px;
        margin-top: 20px;
    }
    .cpabc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .cpabc-header h1 {
        margin: 0;
        padding: 0;
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 23px;
        font-weight: 500;
        line-height: 1.3;
    }
    .cpabc-header h1 .dashicons {
        font-size: 26px;
        width: 26px;
        height: 26px;
        color: #2271b1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cpabc-cal-pill {
        font-size: 13px;
        font-weight: 600;
        background: #f0f6fc;
        color: #2271b1;
        border: 1px solid #c5d9ed;
        padding: 2px 10px;
        border-radius: 12px;
    }
    .cpabc-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .wp-core-ui .button.cpabc-btn-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px;
        min-height: 32px;
        padding: 0 12px;
        line-height: 1 !important;
        white-space: nowrap;
        margin: 0 !important;
    }
    .wp-core-ui .button.cpabc-btn-icon .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        line-height: 1 !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    .cpabc-info-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f0f6fc;
        border: 1px solid #c5d9ed;
        border-left: 4px solid #2271b1;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 20px;
        color: #1d2327;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .cpabc-info-banner .dashicons {
        color: #2271b1;
        flex-shrink: 0;
    }
    .cpabc-info-banner a {
        font-weight: 600;
        color: #2271b1;
        text-decoration: none;
    }
    .cpabc-info-banner a:hover {
        text-decoration: underline;
    }
    .cpabc-card {
        background: #ffffff;
        border: 1px solid #c3c4c7;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .cpabc-card-header {
        padding: 14px 24px;
        background: #f6f7f7;
        border-bottom: 1px solid #dcdcde;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .cpabc-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1d2327;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cpabc-card-header h2 .dashicons {
        color: #2271b1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cpabc-card-body {
        padding: 20px 24px;
    }
</style>

<div class="wrap cpabc-admin-wrap">

    <div class="cpabc-header">
        <h1>
            <span class="dashicons dashicons-calendar"></span>
            <?php esc_html_e( 'Calendar Schedule', 'appointment-booking-calendar' ); ?>
            <?php if ( $calendar_name !== '' ) : ?>
                <span class="cpabc-cal-pill"><?php echo esc_html( $calendar_name ); ?> (#<?php echo intval( CP_CALENDAR_ID ); ?>)</span>
            <?php endif; ?>
        </h1>

        <div class="cpabc-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&addbk=1' ) ); ?>" class="button button-primary cpabc-btn-icon">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span><?php esc_html_e( 'Add Booking', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&list=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-clipboard"></span>
                <span><?php esc_html_e( 'Bookings List', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-admin-generic"></span>
                <span><?php esc_html_e( 'Settings', 'appointment-booking-calendar' ); ?></span>
            </a>
            <?php if ( current_user_can( 'manage_options' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&pwizard=1&cal=' . intval( CP_CALENDAR_ID ) ) ); ?>" class="button button-secondary cpabc-btn-icon">
                    <span class="dashicons dashicons-megaphone"></span>
                    <span><?php esc_html_e( 'Publish', 'appointment-booking-calendar' ); ?></span>
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <span><?php esc_html_e( 'Return to the calendars list', 'appointment-booking-calendar' ); ?></span>
            </a>
        </div>
    </div>

    <div class="cpabc-info-banner">
        <span class="dashicons dashicons-info-outline"></span>
        <span>
            <?php
            echo wp_kses(
                sprintf(
                    /* translators: %s: URL to add bookings page in dashboard */
                    __( 'The purpose of this page is to <strong>display the bookings/schedule in a calendar view</strong>. You can add bookings from the public booking form or <a href="%s">add bookings from the dashboard</a> and the bookings will appear in this calendar.', 'appointment-booking-calendar' ),
                    esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&addbk=1' ) )
                ),
                array(
                    'strong' => array(),
                    'a'      => array( 'href' => array() ),
                )
            );
            ?>
        </span>
    </div>

    <div >


        <div>
            <div id="cpabc_printable_contents">
                <script type="text/javascript">
                    var pathCalendar = "<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_calendar_load2=1&id=<?php echo intval( CP_CALENDAR_ID ); ?>&cpabc_action=mvparse";
                    var dc_subjects = "";
                    var dc_locations = "";
                    initMultiViewCal("cal<?php echo intval( CP_CALENDAR_ID ); ?>", <?php echo intval( CP_CALENDAR_ID ); ?>, {
                        viewDay: true,
                        viewWeek: true,
                        viewMonth: true,
                        viewNMonth: true,
                        viewList: false,
                        viewdefault: "week",
                        numberOfMonths: 12,
                        showtooltip: false,
                        tooltipon: 0,
                        shownavigate: false,
                        url: "",
                        target: 0,
                        start_weekday: 0,
                        language: "en-GB",
                        cssStyle: "cupertino",
                        edition: true,
                        btoday: true,
                        bnavigation: true,
                        brefresh: true,
                        bnew: false,
                        path: pathCalendar,
                        userAdd: false,
                        userEdit: false,
                        userDel: false,
                        userEditOwner: true,
                        userDelOwner: false,
                        showtooltipdwm_mouseover: true,
                        userOwner: 0,
                        cellheight: 60,
                        hourswidth: 64,
                        palette: 0,
                        paletteDefault: "2563eb",
                        paletteFull: [
                            // Row 1: Vibrant 400/500 Tones (Slate, Red, Orange, Amber, Green, Teal, Cyan, Blue, Violet, Pink)
                            "94a3b8", "f87171", "fb923c", "fbbf24", "4ade80", "2dd4bf", "38bdf8", "60a5fa", "a78bfa", "f472b6",
                            // Row 2: Standard 500 Tones
                            "64748b", "ef4444", "f97316", "f59e0b", "22c55e", "14b8a6", "06b6d4", "3b82f6", "8b5cf6", "ec4899",
                            // Row 3: Rich 600 Tones (Best default event colors)
                            "475569", "dc2626", "ea580c", "d97706", "16a34a", "0d9488", "0891b2", "2563eb", "7c3aed", "db2777",
                            // Row 4: Deep 700 Tones
                            "334155", "b91c1c", "c2410c", "b45309", "15803d", "0f766e", "0e7490", "1d4ed8", "6d28d9", "be185d",
                            // Row 5: Dark 800 Tones
                            "1e293b", "991b1b", "9a3412", "92400e", "166534", "115e59", "155e75", "1e40af", "5b21b6", "9d174d",
                            // Row 6: Muted / Earthy 900 Tones
                            "0f172a", "7f1d1d", "7c2d12", "78350f", "14532d", "134e4a", "164e63", "1e3a8a", "4c1d95", "831843",
                            // Row 7: Extra Accents (Indigo, Emerald, Rose, Sky, Lime, Fuchsia, Stone, Zinc, Neutral, Slate)
                            "6366f1", "4f46e5", "10b981", "059669", "f43f5e", "e11d48", "0284c7", "65a30d", "c026d3", "52525b"
                        ]
                    });
                </script>

                <div id="multicalendar">
                    <div id="cal<?php echo intval( CP_CALENDAR_ID ); ?>" class="multicalendar"></div>
                </div>

                <div style="clear:both;height:10px"></div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    function do_dexapp_print() {
        var w = window.open();
        if (!w) return;
        w.document.write("<style>.cpnopr{display:none;} table{border:2px solid black;width:100%;} th{border-bottom:2px solid black;text-align:left} td{padding-left:10px;border-bottom:1px solid black;}</style>" + document.getElementById('cpabc_printable_contents').innerHTML);
        w.document.close();
        w.focus();
        w.print();
    }
</script>

<?php } else { ?>
    <div class="wrap cpabc-admin-wrap">
        <div class="notice notice-error">
            <p><?php esc_html_e( "The current user logged in doesn't have enough permissions to edit this calendar. This user can edit only his/her own calendars. Please log in as administrator to get access to all calendars.", 'appointment-booking-calendar' ); ?></p>
        </div>
    </div>
<?php } ?>