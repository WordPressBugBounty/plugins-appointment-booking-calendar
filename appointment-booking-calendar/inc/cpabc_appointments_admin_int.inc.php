<?php

if ( ! is_admin() && ! defined( 'CPABC_CALENDAR_ON_PUBLIC_WEBSITE' ) ) {
    echo 'Direct access not allowed.';
    exit;
}

if ( ! defined( 'CP_CALENDAR_ID' ) ) {
    define( 'CP_CALENDAR_ID', 1 );
}

global $wpdb;

$plugslug       = 'cpabc_appointments.php';
$mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME . ' WHERE `' . CPABC_TDEAPP_CONFIG_ID . '`=%d', CP_CALENDAR_ID ) );
$calendar_name  = ( isset( $mycalendarrows[0] ) && ! empty( $mycalendarrows[0]->uname ) ) ? $mycalendarrows[0]->uname : '';
$current_user   = wp_get_current_user();

if ( cpabc_appointment_is_administrator() || ( isset( $mycalendarrows[0] ) && (int) $mycalendarrows[0]->conwer === (int) $current_user->ID ) ) {

    $request_costs = explode( ';', cpabc_get_option( 'request_cost', CPABC_APPOINTMENTS_DEFAULT_COST ) );
    if ( ! count( $request_costs ) ) {
        $request_costs[0] = CPABC_APPOINTMENTS_DEFAULT_COST;
    }

    $request_costs_exploded = "'" . str_replace( "'", "\'", $request_costs[0] ) . "'";
    for ( $k = 1; $k < 100; $k++ ) {
        if ( isset( $request_costs[ $k ] ) ) {
            $request_costs_exploded .= ",'" . str_replace( "'", "\'", $request_costs[ $k ] ) . "'";
        } else {
            $request_costs_exploded .= ",'" . str_replace( "'", "\'", floatval( $request_costs[0] ) * ( $k + 1 ) ) . "'";
        }
    }

    $nonce_un     = wp_create_nonce( 'uname_abc' );
    $nonce_udpate = wp_create_nonce( 'cp_abc_update2' );
    ?>

<style type="text/css">
    .cpabc-admin-wrap {
        max-width: 1200px;
        margin-top: 20px;
    }
    .cpabc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
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
    .cpabc-card {
        background: #ffffff;
        border: 1px solid #c3c4c7;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .cpabc-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        padding: 14px 20px;
        border-bottom: 1px solid #dcdcde;
        background: #f6f7f7;
    }
    .cpabc-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1d2327;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        line-height: 1.4;
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
    .cpabc-badge-pro {
        background: #dba617;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .cpabc-slots-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
    }
    .cpabc-slot-box {
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #1d2327;
    }
    .cpabc-slot-box input[type="text"] {
        margin-top: 4px;
        width: 65px !important;
    }
    .cpabc-save-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #ffffff;
        border: 1px solid #c3c4c7;
        border-radius: 6px;
        padding: 16px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
</style>

<div class="wrap cpabc-admin-wrap">

    <div class="cpabc-header">
        <h1>
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e( 'Manage Calendar Settings & Availability', 'appointment-booking-calendar' ); ?>
            <?php if ( $calendar_name !== '' ) : ?>
                <span class="cpabc-cal-pill"><?php echo esc_html( $calendar_name ); ?> (#<?php echo intval( CP_CALENDAR_ID ); ?>)</span>
            <?php endif; ?>
        </h1>

        <?php if ( ! defined( 'CPABC_CALENDAR_ON_PUBLIC_WEBSITE' ) ) : ?>
            <div class="cpabc-header-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&addbk=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <span><?php esc_html_e( 'Add Booking', 'appointment-booking-calendar' ); ?></span>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&list=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                    <span class="dashicons dashicons-clipboard"></span>
                    <span><?php esc_html_e( 'Bookings List', 'appointment-booking-calendar' ); ?></span>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&calschedule=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                    <span class="dashicons dashicons-calendar"></span>
                    <span><?php esc_html_e( 'Schedule', 'appointment-booking-calendar' ); ?></span>
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
        <?php endif; ?>
    </div>

    <?php if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cpabc_appointments_post_options'] ) ) : ?>
        <div id="setting-error-settings_updated" class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e( 'Settings saved.', 'appointment-booking-calendar' ); ?></strong></p>
        </div>
    <?php endif; ?>

    <form method="post" name="dexconfigofrm" action="">
        <input name="cpabc_appointments_post_options" type="hidden" value="1" />
        <input name="cpabc_item" type="hidden" value="<?php echo intval( CP_CALENDAR_ID ); ?>" />
        <input name="_wpnonce" type="hidden" value="<?php echo esc_attr( $nonce_un ); ?>" />
        <input name="cpabc_appointments_control_field" type="hidden" value="&quot;" />
        <input type="hidden" name="quantity_field" value="<?php echo esc_attr( cpabc_get_option( 'quantity_field', '1' ) ); ?>" />

        <input type="hidden" name="form_structure" id="form_structure" size="180" value="<?php echo esc_attr( str_replace( "\r", '', str_replace( "\n", '', esc_attr( cpabc_appointment_cleanJSON( cpabc_get_option( 'form_structure', CPABC_APPOINTMENTS_DEFAULT_form_structure ) ) ) ) ) ); ?>" />
        <input type="hidden" name="vs_text_is_required" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_is_required', CPABC_APPOINTMENTS_DEFAULT_vs_text_is_required ) ); ?>" />
        <input type="hidden" name="vs_text_is_email" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_is_email', CPABC_APPOINTMENTS_DEFAULT_vs_text_is_email ) ); ?>" />
        <input type="hidden" name="cv_text_enter_valid_captcha" value="<?php echo esc_attr( cpabc_get_option( 'cv_text_enter_valid_captcha', CPABC_TDEAPP_DEFAULT_dexcv_text_enter_valid_captcha ) ); ?>" />
        <input type="hidden" name="vs_text_datemmddyyyy" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_datemmddyyyy', CPABC_APPOINTMENTS_DEFAULT_vs_text_datemmddyyyy ) ); ?>" />
        <input type="hidden" name="vs_text_dateddmmyyyy" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_dateddmmyyyy', CPABC_APPOINTMENTS_DEFAULT_vs_text_dateddmmyyyy ) ); ?>" />
        <input type="hidden" name="vs_text_number" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_number', CPABC_APPOINTMENTS_DEFAULT_vs_text_number ) ); ?>" />
        <input type="hidden" name="vs_text_digits" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_digits', CPABC_APPOINTMENTS_DEFAULT_vs_text_digits ) ); ?>" />
        <input type="hidden" name="vs_text_max" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_max', CPABC_APPOINTMENTS_DEFAULT_vs_text_max ) ); ?>" />
        <input type="hidden" name="vs_text_min" value="<?php echo esc_attr( cpabc_get_option( 'vs_text_min', CPABC_APPOINTMENTS_DEFAULT_vs_text_min ) ); ?>" />

        <!-- Calendar Configuration / Administration -->
        <div id="metabox_basic_settings" class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'Calendar Configuration / Administration', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <script>
                    var pathCalendar = "<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>";
                    var cpabc_global_start_weekday = '<?php echo esc_js( cpabc_get_option( 'calendar_weekday', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_WEEKDAY ) ); ?>';
                    var cp_abc_update_nonce = '<?php echo esc_html( $nonce_udpate ); ?>';
                </script>
                <script>initAppCalendar("cal<?php echo intval( CP_CALENDAR_ID ); ?>","3","1","<?php echo esc_js( CPABC_TDEAPP_DEFAULT_CALENDAR_LANGUAGE ); ?>",{m1:"Please, select your appointment."});</script>

                <div style="padding:10px"><div id="caladmin"><div id="cal<?php echo intval( CP_CALENDAR_ID ); ?>Container"></div></div></div>
                <div style="clear:both;height:20px"></div>
                <div id="demo" class="yui-navset" style="padding-left:10px;width:690px;max-width:100%;"></div>
                <div style="clear:both;height:10px"></div>
            </div>
        </div>

        <!-- Public Calendar Settings -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Public Calendar Settings', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Calendar language', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_language', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_LANGUAGE ); ?>
                            <select name="calendar_language" class="regular-text">
                                <option value="-" <?php selected( $value, '-' ); ?>>- Auto detect -</option>
                                <option value="AR" <?php selected( $value, 'AR' ); ?>>Arabic</option>
                                <option value="BG" <?php selected( $value, 'BG' ); ?>>Bulgarian</option>
                                <option value="CZ" <?php selected( $value, 'CZ' ); ?>>Czech</option>
                                <option value="DE" <?php selected( $value, 'DE' ); ?>>German</option>
                                <option value="DA" <?php selected( $value, 'DA' ); ?>>Danish</option>
                                <option value="DU" <?php selected( $value, 'DU' ); ?>>Dutch</option>
                                <option value="EN" <?php selected( $value, 'EN' ); ?>>English</option>
                                <option value="FI" <?php selected( $value, 'FI' ); ?>>Finnish</option>
                                <option value="FR" <?php selected( $value, 'FR' ); ?>>French</option>
                                <option value="GR" <?php selected( $value, 'GR' ); ?>>Greek</option>
                                <option value="HE" <?php selected( $value, 'HE' ); ?>>Hebrew</option>
                                <option value="HU" <?php selected( $value, 'HU' ); ?>>Hungarian</option>
                                <option value="IT" <?php selected( $value, 'IT' ); ?>>Italian</option>
                                <option value="JP" <?php selected( $value, 'JP' ); ?>>Japanese</option>
                                <option value="KR" <?php selected( $value, 'KR' ); ?>>Korean</option>
                                <option value="NW" <?php selected( $value, 'NW' ); ?>>Norwegian</option>
                                <option value="PL" <?php selected( $value, 'PL' ); ?>>Polish</option>
                                <option value="PT" <?php selected( $value, 'PT' ); ?>>Portuguese</option>
                                <option value="RU" <?php selected( $value, 'RU' ); ?>>Russian</option>
                                <option value="SP" <?php selected( $value, 'SP' ); ?>>Spanish</option>
                                <option value="CA" <?php selected( $value, 'CA' ); ?>>Spanish - Catalan</option>
                                <option value="SK" <?php selected( $value, 'SK' ); ?>>Slovak</option>
                                <option value="SE" <?php selected( $value, 'SE' ); ?>>Swedish</option>
                                <option value="TH" <?php selected( $value, 'TH' ); ?>>Thai</option>
                                <option value="TR" <?php selected( $value, 'TR' ); ?>>Turkish</option>
                                <option value="VI" <?php selected( $value, 'VI' ); ?>>Vietnamese</option>
                            </select>
                            <p class="description">
                                * Note that this "Calendar language" setting will affect the calendar area, but the rest of the form texts are translated in the MO/PO files located in the "<em>appointment-booking-calendar/languages</em>" folder. The <em>WP_LANG</em> constant into your config.php file must match the suffix of the MO/PO file to apply.
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Calendar visual theme', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_theme', 'friendly/' ); ?>
                            <select name="calendar_theme" class="regular-text">
                                <option value="friendly/" <?php selected( $value, 'friendly/' ); ?>>Default - Friendly</option>
                                <option value="modern/" <?php selected( $value, 'modern/' ); ?>>Modern</option>
                                <option value="modern2/" <?php selected( $value, 'modern2/' ); ?>>Modern II</option>
                                <option value="rounded/" <?php selected( $value, 'rounded/' ); ?>>Rounded</option>
                                <option value="" <?php selected( $value, '' ); ?>>Classic</option>
                                <option value="light/" <?php selected( $value, 'light/' ); ?>>Light</option>
                                <option value="blue/" <?php selected( $value, 'blue/' ); ?>>Blue</option>
                            </select>
                            <p class="description">
                                * This will modify the calendar appearance in the public website. For other appearance modifications <a target="_blank" rel="noopener noreferrer" href="https://abc.dwbooster.com/faq">check the FAQ</a>.
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Date format', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_dateformat', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_DATEFORMAT ); ?>
                            <select name="calendar_dateformat" class="regular-text">
                                <option value="0" <?php selected( $value, '0' ); ?>>mm/dd/yyyy</option>
                                <option value="1" <?php selected( $value, '1' ); ?>>dd/mm/yyyy</option>
                                <option value="2" <?php selected( $value, '2' ); ?>>dd.mm.yyyy</option>
                                <option value="3" <?php selected( $value, '3' ); ?>>Complete #1: Ex: Saturday 9th January</option>
                                <option value="4" <?php selected( $value, '4' ); ?>>Complete #2: Ex: Saturday 9th January, 2016</option>
                                <option value="5" <?php selected( $value, '5' ); ?>>yyyy-mm-dd</option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Calendar Pages', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_pages', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_PAGES ); ?>
                            <select name="calendar_pages">
                                <?php for ( $i = 1; $i <= 12; $i++ ) : ?>
                                    <option value="<?php echo intval( $i ); ?>" <?php selected( (string) $value, (string) $i ); ?>><?php echo intval( $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Military time (24 hours or AM/PM)', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_militarytime', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_MILITARYTIME ); ?>
                            <select name="calendar_militarytime">
                                <option value="0" <?php selected( $value, '0' ); ?>>No</option>
                                <option value="1" <?php selected( $value, '1' ); ?>>Yes</option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Start weekday', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_weekday', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_WEEKDAY ); ?>
                            <select name="calendar_weekday">
                                <option value="0" <?php selected( $value, '0' ); ?>>Sunday</option>
                                <option value="1" <?php selected( $value, '1' ); ?>>Monday</option>
                                <option value="2" <?php selected( $value, '2' ); ?>>Tuesday</option>
                                <option value="3" <?php selected( $value, '3' ); ?>>Wednesday</option>
                                <option value="4" <?php selected( $value, '4' ); ?>>Thursday</option>
                                <option value="5" <?php selected( $value, '5' ); ?>>Friday</option>
                                <option value="6" <?php selected( $value, '6' ); ?>>Saturday</option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="calendar_mindate"><?php esc_html_e( 'Minimum available date', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="text" onchange="abc_checkdatemin()" id="calendar_mindate" name="calendar_mindate" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'calendar_mindate', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_MINDATE ) ); ?>" />
                            <p class="description">Examples: <?php echo esc_html( date( 'Y-m-d' ) ); ?>, today, today +3 days, now +30 hours</p>
                            <div id="abcmindateval" style="font-weight:bold; margin-top:4px;"></div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="calendar_maxdate"><?php esc_html_e( 'Maximum available date', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="text" onchange="abc_checkdatemax()" id="calendar_maxdate" name="calendar_maxdate" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'calendar_maxdate', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_MAXDATE ) ); ?>" />
                            <p class="description">Examples: <?php echo esc_html( date( 'Y-m-d', strtotime( '+6 months' ) ) ); ?>, today, today +3 days</p>
                            <div id="abcmaxdateval" style="font-weight:bold; margin-top:4px;"></div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Open calendar in this initial month/year', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $value = cpabc_get_option( 'calendar_startmonth', '0' ); ?>
                            <select name="calendar_startmonth">
                                <option value="0" <?php selected( $value, '0' ); ?>>Current month</option>
                                <option value="1" <?php selected( $value, '1' ); ?>>January</option>
                                <option value="2" <?php selected( $value, '2' ); ?>>February</option>
                                <option value="3" <?php selected( $value, '3' ); ?>>March</option>
                                <option value="4" <?php selected( $value, '4' ); ?>>April</option>
                                <option value="5" <?php selected( $value, '5' ); ?>>May</option>
                                <option value="6" <?php selected( $value, '6' ); ?>>June</option>
                                <option value="7" <?php selected( $value, '7' ); ?>>July</option>
                                <option value="8" <?php selected( $value, '8' ); ?>>August</option>
                                <option value="9" <?php selected( $value, '9' ); ?>>September</option>
                                <option value="10" <?php selected( $value, '10' ); ?>>October</option>
                                <option value="11" <?php selected( $value, '11' ); ?>>November</option>
                                <option value="12" <?php selected( $value, '12' ); ?>>December</option>
                            </select>
                            /
                            <?php $value = cpabc_get_option( 'calendar_startyear', '0' ); ?>
                            <select name="calendar_startyear">
                                <option value="0" <?php selected( $value, '0' ); ?>>Current year</option>
                                <?php for ( $y = date( 'Y' ) - 3; $y < date( 'Y' ) + 30; $y++ ) : ?>
                                    <option value="<?php echo intval( $y ); ?>" <?php selected( (string) $value, (string) intval( $y ) ); ?>><?php echo intval( $y ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Minimum slots to be selected', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php
                            $option = @intval( cpabc_get_option( 'min_slots', '1' ) );
                            if ( $option == '' ) {
                                $option = 1;
                            }
                            ?>
                            <select name="min_slots">
                                <?php for ( $k = 1; $k <= 22; $k++ ) : ?>
                                    <option value="<?php echo intval( $k ); ?>" <?php selected( $option, $k ); ?>><?php echo intval( $k ); ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="description">This is the minimum number of slots that the customer must select in the booking form.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Maximum slots to be selected', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php
                            $option = @intval( cpabc_get_option( 'max_slots', '1' ) );
                            if ( $option == '' ) {
                                $option = 1;
                            }
                            ?>
                            <select name="max_slots" onchange="cpabc_updatemaxslots();">
                                <?php for ( $k = 1; $k <= 26; $k++ ) : ?>
                                    <option value="<?php echo intval( $k ); ?>" <?php selected( $option, $k ); ?>><?php echo intval( $k ); ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="description">This is the maximum number of slots that the customer can select in the booking form.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Close floating panel after selecting a time-slot?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php
                            $option = cpabc_get_option( 'close_fpanel', 'yes' );
                            if ( $option == '' ) {
                                $option = 'yes';
                            }
                            ?>
                            <select name="close_fpanel">
                                <option value="yes" <?php selected( $option, 'yes' ); ?>>Yes</option>
                                <option value="no" <?php selected( $option, 'no' ); ?>>No</option>
                            </select>
                            <p class="description">Default: "Yes". Set to "No" in the case the user have to select various slots in the same date. The price should be set for each total number of slots below (request cost setting).</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-button"></span>
                    <?php esc_html_e( 'Submit Button', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Submit button label (text):', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $label = cpabc_get_option( 'vs_text_submitbtn', 'Continue' ); ?>
                            <input type="text" name="vs_text_submitbtn" class="regular-text" value="<?php echo esc_attr( $label == '' ? 'Continue' : $label ); ?>" />
                            <p class="description">
                                The class="<?php echo ( get_option( 'CPABC_APPOINTMENTS_DEFAULT_USE_EDITOR', '1' ) != '1' ) ? 'pbSubmit' : 'cp_subbtn'; ?>" can be used to modify the button styles. The styles can be applied into the <a href="?page=cpabc_appointments.php#metabox_basic_settings_custom">CSS Customization Area</a>. For further modifications, the submit button is located at the end of the file "<?php echo ( get_option( 'CPABC_APPOINTMENTS_DEFAULT_USE_EDITOR', '1' ) != '1' ) ? 'cpabc_internal.inc.php' : 'cpabc_scheduler.inc.php'; ?>".
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- PayPal Payment Configuration -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-cart"></span>
                    <?php esc_html_e( 'PayPal Payment Configuration', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Enable PayPal Payments?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="checkbox" readonly disabled name="enable_paypal" value="1" checked />
                            <span class="description">The feature for working without PayPal is implemented/available in the <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer">commercial versions</a>.</span>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'PayPal email', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="email" name="paypal_email" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'paypal_email', $current_user->user_email ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Request cost for each number of time slots', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <div id="cpabcslots" class="cpabc-slots-grid">
                                <div id="cpabccost1" class="cpabc-slot-box">
                                    1 slot:<br />
                                    <input type="text" name="request_cost_1" value="<?php echo esc_attr( cpabc_get_option( 'request_cost', CPABC_APPOINTMENTS_DEFAULT_COST ) ); ?>" />
                                </div>
                            </div>
                            <p class="description">Note: Each box should contain the TOTAL cost for the N slots related. Ex: 1 slot for 25, 2 slots for 50, 3 slots for 75. Keep only the price number without currency symbols.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'PayPal product name', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="paypal_product_name" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'paypal_product_name', CPABC_APPOINTMENTS_DEFAULT_PRODUCT_NAME ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Currency', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $currency = strtoupper( esc_attr( cpabc_get_option( 'currency', CPABC_APPOINTMENTS_DEFAULT_CURRENCY ) ) ); ?>
                            <select name="currency" onchange="javascript:cpExplainCurrency(this);">
                                <option value="USD" <?php selected( $currency == 'USD' || $currency == '' ); ?>>USD - U.S. Dollar</option>
                                <option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR - Euro</option>
                                <option value="GBP" <?php selected( $currency, 'GBP' ); ?>>GBP - Pound Sterling</option>
                                <option value="USD"> - </option>
                                <option value="ARS" <?php selected( $currency, 'ARS' ); ?>>ARS - Argentine peso</option>
                                <option value="AUD" <?php selected( $currency, 'AUD' ); ?>>AUD - Australian Dollar</option>
                                <option value="BRL" <?php selected( $currency, 'BRL' ); ?>>BRL - Brazilian Real</option>
                                <option value="CAD" <?php selected( $currency, 'CAD' ); ?>>CAD - Canadian Dollar</option>
                                <option value="CZK" <?php selected( $currency, 'CZK' ); ?>>CZK - Czech Koruna</option>
                                <option value="DKK" <?php selected( $currency, 'DKK' ); ?>>DKK - Danish Krone</option>
                                <option value="HKD" <?php selected( $currency, 'HKD' ); ?>>HKD - Hong Kong Dollar</option>
                                <option value="HUF" <?php selected( $currency, 'HUF' ); ?>>HUF - Hungarian Forint</option>
                                <option value="ILS" <?php selected( $currency, 'ILS' ); ?>>ILS - Israeli New Sheqel</option>
                                <option value="INR" <?php selected( $currency, 'INR' ); ?>>INR - Indian Rupee</option>
                                <option value="JPY" <?php selected( $currency, 'JPY' ); ?>>JPY - Japanese Yen</option>
                                <option value="MYR" <?php selected( $currency, 'MYR' ); ?>>MYR - Malaysian Ringgit</option>
                                <option value="MXN" <?php selected( $currency, 'MXN' ); ?>>MXN - Mexican Peso</option>
                                <option value="NOK" <?php selected( $currency, 'NOK' ); ?>>NOK - Norwegian Krone</option>
                                <option value="NZD" <?php selected( $currency, 'NZD' ); ?>>NZD - New Zealand Dollar</option>
                                <option value="PHP" <?php selected( $currency, 'PHP' ); ?>>PHP - Philippine Peso</option>
                                <option value="PLN" <?php selected( $currency, 'PLN' ); ?>>PLN - Polish Zloty</option>
                                <option value="RUB" <?php selected( $currency, 'RUB' ); ?>>RUB - Russian Ruble</option>
                                <option value="SGD" <?php selected( $currency, 'SGD' ); ?>>SGD - Singapore Dollar</option>
                                <option value="SEK" <?php selected( $currency, 'SEK' ); ?>>SEK - Swedish Krona</option>
                                <option value="CHF" <?php selected( $currency, 'CHF' ); ?>>CHF - Swiss Franc</option>
                                <option value="TWD" <?php selected( $currency, 'TWD' ); ?>>TWD - Taiwan New Dollar</option>
                                <option value="THB" <?php selected( $currency, 'THB' ); ?>>THB - Thai Baht</option>
                                <option value="USD" <?php selected( $currency, 'nocurrency' ); ?>> - Other Currency? -</option>
                            </select>
                            <script type="text/javascript">
                                function cpExplainCurrency(fld) {
                                    var sel = fld.options[fld.options.selectedIndex].text;
                                    if (sel == '- Other Currency? -')
                                        document.getElementById("cpexplaincurr").style.display = '';
                                }
                            </script>
                            <div id="cpexplaincurr" style="display:none;padding:15px;margin-top:8px;background-color:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;">
                                <p style="margin-top:0;">The currencies listed in this dropdown are the <a href="https://developer.paypal.com/docs/classic/api/currency_codes/#paypal" target="_blank" rel="noopener noreferrer">currencies supported by PayPal</a> to accept payments.</p>
                                <p>The commercial versions of the plugin support all currencies since PayPal is optional in those versions and some distributions also support integration with other payment gateways.</p>
                                <p style="margin-bottom:0;">If you need further information or solution about this currency setting you can <a href="https://abc.dwbooster.com/contact-us" target="_blank" rel="noopener noreferrer">contact our support service</a>.</p>
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'URL to return after successful payment', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="url_ok" class="large-text" value="<?php echo esc_attr( cpabc_get_option( 'url_ok', CPABC_APPOINTMENTS_DEFAULT_OK_URL ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'URL to return after an incomplete or cancelled payment', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="url_cancel" class="large-text" value="<?php echo esc_attr( cpabc_get_option( 'url_cancel', CPABC_APPOINTMENTS_DEFAULT_CANCEL_URL ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'PayPal language', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="paypal_language" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'paypal_language', CPABC_APPOINTMENTS_DEFAULT_PAYPAL_LANGUAGE ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Request address at PayPal', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <select name="request_address">
                                <option value="0" <?php selected( cpabc_get_option( 'request_address', '0' ) != '1' ); ?>><?php esc_html_e( 'No', 'appointment-booking-calendar' ); ?></option>
                                <option value="1" <?php selected( cpabc_get_option( 'request_address', '0' ) == '1' ); ?>><?php esc_html_e( 'Yes', 'appointment-booking-calendar' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'PayPal Mode', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <select name="paypal_mode">
                                <option value="production" <?php selected( cpabc_get_option( 'paypal_mode', 'production' ) != 'sandbox' ); ?>>Production - real payments processed</option>
                                <option value="sandbox" <?php selected( cpabc_get_option( 'paypal_mode', 'production' ) == 'sandbox' ); ?>>SandBox - PayPal testing sandbox area</option>
                            </select>
                            <p class="description">* Note that if you are testing it in a <strong>localhost</strong> site the PayPal IPN notification won't reach to your website and the appointment won't be processed.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Discount Codes', 'appointment-booking-calendar' ); ?></label></th>
                        <td><span class="description">The -discount codes- feature is available in the <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer">commercial versions</a>.</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notification Settings for Administrators -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e( 'Notification Settings for Administrators', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Notification "from" email', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="email" name="notification_from_email" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'notification_from_email', _cpabc_appointments_get_default_from_email() ) ); ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Send notification to email', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="text" name="notification_destination_email" class="regular-text" value="<?php echo esc_attr( cpabc_get_option( 'notification_destination_email', _cpabc_appointments_get_default_paypal_email() ) ); ?>" />
                            <p class="description">Note: Comma separated list for adding more than one email address.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email subject notification to admin', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="email_subject_notification_to_admin" class="large-text" value="<?php echo esc_attr( cpabc_get_option( 'email_subject_notification_to_admin', CPABC_APPOINTMENTS_DEFAULT_SUBJECT_NOTIFICATION_EMAIL ) ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email format?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $option = cpabc_get_option( 'nadmin_emailformat', CPABC_APPOINTMENTS_DEFAULT_email_format ); ?>
                            <select name="nadmin_emailformat">
                                <option value="text" <?php selected( $option != 'html' ); ?>>Plain Text (default)</option>
                                <option value="html" <?php selected( $option == 'html' ); ?>>HTML (use html in the textarea below)</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email notification to admin', 'appointment-booking-calendar' ); ?></label></th>
                        <td><textarea cols="70" rows="5" class="large-text" name="email_notification_to_admin"><?php echo esc_textarea( cpabc_get_option( 'email_notification_to_admin', CPABC_APPOINTMENTS_DEFAULT_NOTIFICATION_EMAIL ) ); ?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Email Copy to User / Auto-reply -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-email-alt"></span>
                    <?php esc_html_e( 'Email Copy to User / Auto-reply', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <?php if ( get_option( 'CPABC_APPOINTMENTS_DEFAULT_USE_EDITOR', '1' ) != '1' ) : ?>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e( 'Email field on the form', 'appointment-booking-calendar' ); ?></label></th>
                            <td><select id="cu_user_email_field" name="cu_user_email_field" def="<?php echo esc_attr( cpabc_get_option( 'cu_user_email_field', CPABC_APPOINTMENTS_DEFAULT_cu_user_email_field ) ); ?>"></select></td>
                        </tr>
                    <?php endif; ?>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email subject confirmation to user', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="email_subject_confirmation_to_user" class="large-text" value="<?php echo esc_attr( cpabc_get_option( 'email_subject_confirmation_to_user', CPABC_APPOINTMENTS_DEFAULT_SUBJECT_CONFIRMATION_EMAIL ) ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email format?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $option = cpabc_get_option( 'nuser_emailformat', CPABC_APPOINTMENTS_DEFAULT_email_format ); ?>
                            <select name="nuser_emailformat">
                                <option value="text" <?php selected( $option != 'html' ); ?>>Plain Text (default)</option>
                                <option value="html" <?php selected( $option == 'html' ); ?>>HTML (use html in the textarea below)</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email confirmation to user', 'appointment-booking-calendar' ); ?></label></th>
                        <td><textarea cols="70" rows="5" class="large-text" name="email_confirmation_to_user"><?php echo esc_textarea( cpabc_get_option( 'email_confirmation_to_user', CPABC_APPOINTMENTS_DEFAULT_CONFIRMATION_EMAIL ) ); ?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Captcha Verification -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-shield"></span>
                    <?php esc_html_e( 'Captcha Verification', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Use Captcha Verification?', 'appointment-booking-calendar' ); ?></label></th>
                        <td colspan="5">
                            <?php $option = cpabc_get_option( 'dexcv_enable_captcha', CPABC_TDEAPP_DEFAULT_dexcv_enable_captcha ); ?>
                            <select name="dexcv_enable_captcha">
                                <option value="true" <?php selected( $option, 'true' ); ?>>Yes</option>
                                <option value="false" <?php selected( $option, 'false' ); ?>>No</option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label>Width:</label></th>
                        <td><input type="number" name="dexcv_width" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_width', CPABC_TDEAPP_DEFAULT_dexcv_width ) ); ?>" onblur="generateCaptcha();" /></td>
                        <th scope="row"><label>Height:</label></th>
                        <td><input type="number" name="dexcv_height" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_height', CPABC_TDEAPP_DEFAULT_dexcv_height ) ); ?>" onblur="generateCaptcha();" /></td>
                        <th scope="row"><label>Chars:</label></th>
                        <td><input type="number" name="dexcv_chars" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_chars', CPABC_TDEAPP_DEFAULT_dexcv_chars ) ); ?>" onblur="generateCaptcha();" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label>Min font size:</label></th>
                        <td><input type="number" name="dexcv_min_font_size" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_min_font_size', CPABC_TDEAPP_DEFAULT_dexcv_min_font_size ) ); ?>" onblur="generateCaptcha();" /></td>
                        <th scope="row"><label>Max font size:</label></th>
                        <td><input type="number" name="dexcv_max_font_size" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_max_font_size', CPABC_TDEAPP_DEFAULT_dexcv_max_font_size ) ); ?>" onblur="generateCaptcha();" /></td>
                        <td colspan="2">
                            <strong>Preview:</strong><br /><br />
                            <img src="<?php echo esc_attr( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=captcha&inAdmin=1" id="captchaimg" alt="security code" border="0" style="border:1px solid #dcdcde;border-radius:4px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label>Noise:</label></th>
                        <td><input type="number" name="dexcv_noise" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_noise', CPABC_TDEAPP_DEFAULT_dexcv_noise ) ); ?>" onblur="generateCaptcha();" /></td>
                        <th scope="row"><label>Noise Length:</label></th>
                        <td colspan="3"><input type="number" name="dexcv_noise_length" class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'dexcv_noise_length', CPABC_TDEAPP_DEFAULT_dexcv_noise_length ) ); ?>" onblur="generateCaptcha();" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label>Background:</label></th>
                        <td><input type="color" name="dexcv_background" value="#<?php echo esc_attr( cpabc_get_option( 'dexcv_background', CPABC_TDEAPP_DEFAULT_dexcv_background ) ); ?>" onblur="generateCaptcha();" /></td>
                        <th scope="row"><label>Border:</label></th>
                        <td colspan="3"><input type="color" name="dexcv_border" value="#<?php echo esc_attr( cpabc_get_option( 'dexcv_border', CPABC_TDEAPP_DEFAULT_dexcv_border ) ); ?>" onblur="generateCaptcha();" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label>Font:</label></th>
                        <td colspan="5">
                            <select name="dexcv_font" onchange="generateCaptcha();">
                                <option value="font1" <?php selected( 'font1', cpabc_get_option( 'dexcv_font', CPABC_TDEAPP_DEFAULT_dexcv_font ) ); ?>>Font 1</option>
                                <option value="font2" <?php selected( 'font2', cpabc_get_option( 'dexcv_font', CPABC_TDEAPP_DEFAULT_dexcv_font ) ); ?>>Font 2</option>
                                <option value="font3" <?php selected( 'font3', cpabc_get_option( 'dexcv_font', CPABC_TDEAPP_DEFAULT_dexcv_font ) ); ?>>Font 3</option>
                                <option value="font4" <?php selected( 'font4', cpabc_get_option( 'dexcv_font', CPABC_TDEAPP_DEFAULT_dexcv_font ) ); ?>>Font 4</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Email Reminder Settings -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e( 'Email Reminder Settings', 'appointment-booking-calendar' ); ?>
                    <span class="cpabc-badge-pro"><?php esc_html_e( 'Commercial Versions', 'appointment-booking-calendar' ); ?></span>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Enable e-mail reminder?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="checkbox" name="enable_reminder" disabled readonly value="1" <?php checked( (bool) cpabc_get_option( 'enable_reminder', CPABC_APPOINTMENTS_DEFAULT_ENABLE_REMINDER ) ); ?> />
                            <span class="description">* This feature is available in the <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer">commercial versions</a>.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Send reminder:', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <input type="number" name="reminder_hours" disabled readonly class="small-text" value="<?php echo esc_attr( cpabc_get_option( 'reminder_hours', CPABC_APPOINTMENTS_DEFAULT_REMINDER_HOURS ) ); ?>" /> hours before the appointment
                            <p class="description">Note: Hours date based in server time. Server time now is <code><?php echo esc_html( date( 'Y-m-d H:i' ) ); ?></code></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Reminder email subject', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="reminder_subject" disabled readonly class="large-text" value="<?php echo esc_attr( cpabc_get_option( 'reminder_subject', CPABC_APPOINTMENTS_DEFAULT_REMINDER_SUBJECT ) ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Email format?', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <?php $option = cpabc_get_option( 'nremind_emailformat', CPABC_APPOINTMENTS_DEFAULT_email_format ); ?>
                            <select name="nremind_emailformat">
                                <option value="text" <?php selected( $option != 'html' ); ?>>Plain Text (default)</option>
                                <option value="html" <?php selected( $option == 'html' ); ?>>HTML (use html in the textarea below)</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Reminder email message', 'appointment-booking-calendar' ); ?></label></th>
                        <td><textarea cols="70" rows="3" disabled readonly class="large-text" name="reminder_content"><?php echo esc_textarea( cpabc_get_option( 'reminder_content', CPABC_APPOINTMENTS_DEFAULT_REMINDER_CONTENT ) ); ?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Custom Settings -->
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-forms"></span>
                    <?php esc_html_e( 'Custom Settings', 'appointment-booking-calendar' ); ?>
                    <span class="cpabc-badge-pro"><?php esc_html_e( 'Commercial Versions', 'appointment-booking-calendar' ); ?></span>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row">
                            Options (drop-down select, one item per line with format: <span style="color:#d63638">price | title</span>)<br /><br />
                            <strong>Sample Format:</strong><br />
                            <code><?php echo str_replace( "\n", '<br />', esc_html( CPABC_APPOINTMENTS_DEFAULT_EXPLAIN_CP_CAL_CHECKBOXES ) ); ?></code>
                        </th>
                        <td>
                            <span class="description">This feature is available in the <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer">commercial versions</a>.</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if ( ! defined( 'CPABC_CALENDAR_ON_PUBLIC_WEBSITE' ) ) : ?>
            <div class="cpabc-card">
                <div class="cpabc-card-header">
                    <h2>
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php esc_html_e( 'Note on Publishing', 'appointment-booking-calendar' ); ?>
                    </h2>
                </div>
                <div class="cpabc-card-body">
                    To insert the calendar booking form in a post/page, use the dedicated icon
                    <?php print '<img hspace="5" style="vertical-align:middle;" src="' . esc_attr( plugins_url( '../images/cpabc_apps.gif', __FILE__ ) ) . '" alt="' . esc_attr( __( 'Insert Appointment Booking Calendar', 'appointment-booking-calendar' ) ) . '" />'; ?>
                    which has been added to your Upload/Insert Menu, just below the title of your Post/Page, or use the <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&pwizard=1&cal=' . intval( CP_CALENDAR_ID ) ) ); ?>">Publish Wizard</a>.
                </div>
            </div>
        <?php endif; ?>

        <!-- Save Changes Bar -->
        <div class="cpabc-save-bar">
            <button type="submit" name="submit" id="submit" class="button button-primary cpabc-btn-icon">
                <span class="dashicons dashicons-saved"></span>
                <span><?php esc_html_e( 'Save Changes', 'appointment-booking-calendar' ); ?></span>
            </button>

            <?php if ( ! defined( 'CPABC_CALENDAR_ON_PUBLIC_WEBSITE' ) ) : ?>
                <div class="cpabc-header-actions">
                    <a href="https://abc.dwbooster.com/support?go=doc" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                        <span class="dashicons dashicons-book"></span>
                        <span><?php esc_html_e( 'Documentation', 'appointment-booking-calendar' ); ?></span>
                    </a>
                    <a href="https://wordpress.org/support/plugin/appointment-booking-calendar#new-post" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                        <span class="dashicons dashicons-sos"></span>
                        <span><?php esc_html_e( 'Support', 'appointment-booking-calendar' ); ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </form>
</div>

<script type="text/javascript">
    function generateCaptcha() {
        var d = new Date();
        var f = document.dexconfigofrm;
        var cv_background = f.dexcv_background.value.replace('#', '');
        var cv_border = f.dexcv_border.value.replace('#', '');
        var qs = "?width=" + f.dexcv_width.value;
        qs += "&height=" + f.dexcv_height.value;
        qs += "&letter_count=" + f.dexcv_chars.value;
        qs += "&min_size=" + f.dexcv_min_font_size.value;
        qs += "&max_size=" + f.dexcv_max_font_size.value;
        qs += "&noise=" + f.dexcv_noise.value;
        qs += "&noiselength=" + f.dexcv_noise_length.value;
        qs += "&bcolor=" + cv_background;
        qs += "&border=" + cv_border;
        qs += "&font=" + f.dexcv_font.options[f.dexcv_font.selectedIndex].value;
        qs += "&rand=" + d;

        document.getElementById("captchaimg").src = "<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=captcha&inAdmin=1&" + qs;
    }

    generateCaptcha();

    var $j = jQuery.noConflict();
    $j(function() {
        $j("#cpabc_dc_expires").datepicker({
            dateFormat: 'yy-mm-dd'
        });
    });
    $j('#cpabc_nocodes_availmsg').load('<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=cpabc_loadcoupons&inAdmin=1&cpabc_item=<?php echo intval( CP_CALENDAR_ID ); ?>');
    $j('#cpabc_dc_subccode').click(function() {
        var code = $j('#cpabc_dc_code').val();
        var discount = $j('#cpabc_dc_discount').val();
        var expires = $j('#cpabc_dc_expires').val();
        if (code == '') { alert('Please enter a code'); return; }
        if (parseInt(discount) + "" != discount) { alert('Please numeric discount percent'); return; }
        if (expires == '') { alert('Please enter an expiration date for the code'); return; }
        var params = '&add=1&expires=' + encodeURIComponent(expires) + '&discount=' + encodeURIComponent(discount) + '&code=' + encodeURIComponent(code);
        $j('#cpabc_nocodes_availmsg').load('<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=cpabc_loadcoupons&inAdmin=1&cpabc_item=<?php echo intval( CP_CALENDAR_ID ); ?>' + params);
        $j('#cpabc_dc_code').val();
    });

    function abc_checkdatemin() {
        $j('#abcmindateval').load('<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=cpabc_loadmindate&code=' + encodeURIComponent(document.getElementById("calendar_mindate").value));
    }

    function abc_checkdatemax() {
        $j('#abcmaxdateval').load('<?php echo esc_js( cpabc_appointment_get_site_url( true ) ); ?>/?cpabc_app=cpabc_loadmaxdate&code=' + encodeURIComponent(document.getElementById("calendar_maxdate").value) + '&code2=' + encodeURIComponent(document.getElementById("calendar_mindate").value));
    }
    abc_checkdatemax();
    abc_checkdatemin();

    function cpabc_updatemaxslots() {
        try {
            var default_request_cost = new Array(<?php echo str_replace( "\'", "'", esc_js( $request_costs_exploded ) ); ?>);
            var f = document.dexconfigofrm;
            var slots = f.max_slots.options[f.max_slots.selectedIndex].value;
            var buffer = "";
            for (var i = 1; i <= slots; i++) {
                buffer += '<div id="cpabccost' + i + '" class="cpabc-slot-box">' + i + ' slot' + (i > 1 ? 's' : '') + ':<br />' +
                          '<input type="text" name="request_cost_' + i + '" value="' + default_request_cost[i - 1] + '" /></div>';
            }
            document.getElementById("cpabcslots").innerHTML = buffer;
        } catch (e) {}
    }
    cpabc_updatemaxslots();
</script>

<?php } else { ?>
    <div class="wrap cpabc-admin-wrap">
        <div class="notice notice-error">
            <p><?php esc_html_e( "The current user logged in doesn't have enough permissions to edit this calendar. This user can edit only his/her own calendars. Please log in as administrator to get access to all calendars.", 'appointment-booking-calendar' ); ?></p>
        </div>
    </div>
<?php } ?>