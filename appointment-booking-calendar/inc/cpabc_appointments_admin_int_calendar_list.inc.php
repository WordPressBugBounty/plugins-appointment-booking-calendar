<?php

if ( ! defined( 'ABSPATH' ) || ! is_admin() ) {
    echo 'Direct access not allowed.';
    exit;
}

$current_user = wp_get_current_user();
global $wpdb;

$message      = '';
$message_type = 'success';

if ( cpabc_appointment_is_administrator() && isset( $_GET['u'] ) && $_GET['u'] !== '' ) {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'uname_abc' ) ) {
        $message      = __( 'Access verification error. Cannot update settings.', 'appointment-booking-calendar' );
        $message_type = 'error';
    } else {
        $owner_id = isset( $_GET['owner'] ) ? intval( $_GET['owner'] ) : 0;
        $cal_name = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '';
        $cal_id   = intval( $_GET['u'] );

        $wpdb->query(
            $wpdb->prepare(
                'UPDATE `' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME . '` SET conwer=%d, `' . CPABC_TDEAPP_CONFIG_USER . '`=%s WHERE `' . CPABC_TDEAPP_CONFIG_ID . '`=%d',
                $owner_id,
                $cal_name,
                $cal_id
            )
        );
        $message = __( 'Calendar item updated successfully.', 'appointment-booking-calendar' );
    }
} elseif ( cpabc_appointment_is_administrator() && isset( $_GET['ac'] ) && $_GET['ac'] === 'st' ) {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'uname_abc' ) ) {
        $message      = __( 'Access verification error. Cannot update settings.', 'appointment-booking-calendar' );
        $message_type = 'error';
    } else {
        $ict = isset( $_GET['ict'] ) ? sanitize_text_field( wp_unslash( $_GET['ict'] ) ) : '';
        $ics = isset( $_GET['ics'] ) ? sanitize_text_field( wp_unslash( $_GET['ics'] ) ) : '';
        $col = isset( $_GET['col'] ) ? sanitize_text_field( wp_unslash( $_GET['col'] ) ) : '';
        $scr = isset( $_GET['scr'] ) && intval( $_GET['scr'] ) === 1 ? '1' : '2';
        $chs = isset( $_GET['chs'] ) ? sanitize_text_field( wp_unslash( $_GET['chs'] ) ) : '';

        update_option( 'CPABC_CAL_TIME_ZONE_MODIFY_SET', $ict );
        update_option( 'CPABC_CAL_TIME_SLOT_SIZE_SET', $ics );
        update_option( 'CPABC_EXCLUDED_COLUMNS', $col );
        update_option( 'CPABC_APPOINTMENTS_LOAD_SCRIPTS', $scr );
        update_option( 'CPABC_APPOINTMENTS_DEFAULT_USE_EDITOR', '1' );

        if ( $chs !== '' ) {
            $target_charset = str_replace( '`', '``', $chs );
            $tables         = array(
                $wpdb->prefix . CPABC_APPOINTMENTS_TABLE_NAME_NO_PREFIX,
                $wpdb->prefix . CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME_NO_PREFIX,
                $wpdb->prefix . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME_NO_PREFIX,
                $wpdb->prefix . CPABC_APPOINTMENTS_DISCOUNT_CODES_TABLE_NAME_NO_PREFIX,
            );
            foreach ( $tables as $tab ) {
                $myrows = $wpdb->get_results( "DESCRIBE {$tab}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                foreach ( $myrows as $item ) {
                    $name = $item->Field;
                    $type = $item->Type;
                    if ( preg_match( '/^varchar\((\d+)\)$/i', $type ) || ! strcasecmp( $type, 'CHAR' ) || ! strcasecmp( $type, 'TEXT' ) || ! strcasecmp( $type, 'MEDIUMTEXT' ) ) {
                        $wpdb->query( "ALTER TABLE {$tab} CHANGE {$name} {$name} {$type} COLLATE `{$target_charset}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    }
                }
            }
        }
        $message = __( 'Troubleshoot settings updated.', 'appointment-booking-calendar' );
    }
}

$nonce_un = wp_create_nonce( 'uname_abc' );
?>

<style>
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
        margin-bottom: 20px;
    }
    .cpabc-header h1 {
        margin: 0;
        padding: 0;
        display: inline-flex;
        align-items: center;
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
    .cpabc-header-links {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .cpabc-card {
        background: #fff;
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
        color: #50575e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cpabc-card-body {
        padding: 20px;
    }
    .cpabc-card-body.no-padding {
        padding: 0;
    }

    /* Calendar Table */
    .cpabc-table-wrap {
        overflow-x: auto;
    }
    .cpabc-cal-table {
        border: none !important;
        margin: 0 !important;
        table-layout: auto;
        width: 100%;
    }
    .cpabc-cal-table thead th {
        font-weight: 600;
        color: #1d2327;
        padding: 12px 14px !important;
        white-space: nowrap;
        border-bottom: 1px solid #c3c4c7;
    }
    .cpabc-cal-table tbody td {
        vertical-align: middle !important;
        padding: 12px 14px !important;
    }
    .cpabc-cal-table input[type="text"],
    .cpabc-cal-table select {
        width: 100%;
        min-width: 140px;
        max-width: 190px;
        height: 32px;
        line-height: 1;
        margin: 0;
    }
    .cpabc-id-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        background: #f0f0f1;
        border-radius: 4px;
        font-weight: 600;
        color: #3c434a;
    }

    /* Fixed Button & Dashicon Alignment */
    .cpabc-actions-group {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
    .wp-core-ui .button.cpabc-btn-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px;
        min-height: 32px;
        padding: 0 10px;
        line-height: 1 !important;
        white-space: nowrap;
        margin: 0 !important;
    }
    .wp-core-ui .button.button-small.cpabc-btn-icon {
        min-height: 28px;
        padding: 0 8px;
        font-size: 12px;
    }
    .wp-core-ui .button.cpabc-btn-icon .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        position: static;
    }

    /* Shortcode Copy Box */
    .cpabc-shortcode-box {
        display: inline-flex;
        align-items: center;
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        padding: 2px 3px 2px 8px;
        gap: 6px;
        height: 32px;
        box-sizing: border-box;
    }
    .cpabc-shortcode-box code {
        background: transparent;
        padding: 0;
        font-size: 11.5px;
        color: #1d2327;
        white-space: nowrap;
    }
    .cpabc-copy-btn {
        cursor: pointer;
        border: none;
        background: #fff;
        border: 1px solid #dcdcde;
        width: 24px;
        height: 24px;
        padding: 0;
        border-radius: 3px;
        color: #2271b1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }
    .cpabc-copy-btn:hover {
        background: #2271b1;
        border-color: #2271b1;
        color: #fff;
    }
    .cpabc-copy-btn .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Upgrade Notice Banner */
    .cpabc-upgrade-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #f0f6fc;
        border: 1px solid #c5d9ed;
        border-radius: 6px;
        padding: 14px 20px;
        margin-bottom: 24px;
    }
    .cpabc-upgrade-banner-text {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #1d2327;
        font-size: 14px;
    }
    .cpabc-upgrade-banner-text .dashicons {
        color: #2271b1;
    }

    /* Add-ons Grid */
    .cpabc-addons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }
    .cpabc-addon-card {
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 12px 14px;
        background: #fafafa;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        opacity: 0.85;
    }
    .cpabc-addon-card input[type="checkbox"] {
        margin-top: 3px;
    }
    .cpabc-addon-title {
        font-weight: 600;
        color: #3c434a;
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
    }
    .cpabc-addon-desc {
        color: #646970;
        font-size: 12px;
        line-height: 1.4;
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
</style>

<div class="wrap cpabc-admin-wrap">

    <div class="cpabc-header">
        <h1>
            <span class="dashicons dashicons-calendar-alt"></span>
            <?php esc_html_e( 'Appointment Booking Calendar', 'appointment-booking-calendar' ); ?>
        </h1>
        <div class="cpabc-header-links">
            <a href="https://abc.dwbooster.com/support?ref=callist" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-book"></span>
                <?php esc_html_e( 'Documentation', 'appointment-booking-calendar' ); ?>
            </a>
            <a href="https://wordpress.org/support/plugin/appointment-booking-calendar#new-post" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-sos"></span>
                <?php esc_html_e( 'Support', 'appointment-booking-calendar' ); ?>
            </a>
        </div>
    </div>

    <?php if ( $message ) : ?>
        <div id="setting-error-settings_updated" class="notice notice-<?php echo esc_attr( $message_type ); ?> is-dismissible">
            <p><strong><?php echo esc_html( $message ); ?></strong></p>
        </div>
    <?php endif; ?>

    <script type="text/javascript">
        function cp_updateItem(id) {
            var calname = document.getElementById("calname_" + id).value;
            var ownerEl = document.getElementById("calowner_" + id);
            var owner = ownerEl ? ownerEl.options[ownerEl.options.selectedIndex].value : 0;
            if (owner === '') owner = 0;
            document.location = 'admin.php?page=cpabc_appointments.php&_wpnonce=<?php echo esc_js( $nonce_un ); ?>&u=' + id + '&r=' + Math.random() + '&owner=' + owner + '&name=' + encodeURIComponent(calname);
        }

        function cp_manageSettings(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&cal=' + id + '&r=' + Math.random();
        }

        function cp_BookingsList(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&cal=' + id + '&list=1&r=' + Math.random();
        }

        function cp_calendarschedule(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&cal=' + id + '&calschedule=1&r=' + Math.random();
        }

        function cp_publish(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&pwizard=1&cal=' + id + '&r=' + Math.random();
        }

        function cp_addbk(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&cal=' + id + '&addbk=1&r=' + Math.random();
        }

        function cp_updateConfig() {
            if (confirm('<?php echo esc_js( __( 'Are you sure that you want to update these settings?', 'appointment-booking-calendar' ) ); ?>')) {
                var scr = document.getElementById("ccscriptload").value;
                var chs = document.getElementById("cccharsets").value;
                var ccf = document.getElementById("ccformrender").value;
                var ict = document.getElementById("icaltimediff").value;
                var ics = document.getElementById("icaltimeslotsize").value;
                var col = document.getElementById("excludecolumns").value;
                document.location = 'admin.php?page=cpabc_appointments.php&_wpnonce=<?php echo esc_js( $nonce_un ); ?>&ac=st&scr=' + encodeURIComponent(scr) + '&chs=' + encodeURIComponent(chs) + '&ccf=' + encodeURIComponent(ccf) + '&ict=' + encodeURIComponent(ict) + '&ics=' + encodeURIComponent(ics) + '&col=' + encodeURIComponent(col) + '&r=' + Math.random();
            }
        }

        function cp_editArea(id) {
            document.location = 'admin.php?page=cpabc_appointments.php&edit=1&cal=1&item=' + id + '&r=' + Math.random();
        }

        function cpabc_copyText(text, btnEl) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text);
            } else {
                var tempInput = document.createElement("textarea");
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
            }
            var icon = btnEl.querySelector('.dashicons');
            if (icon) {
                var origClass = icon.className;
                icon.className = 'dashicons dashicons-yes';
                setTimeout(function() {
                    icon.className = origClass;
                }, 1500);
            }
        }
    </script>

    <!-- Calendar List Section -->
    <div id="metabox_calendar_list" class="cpabc-card">
        <div class="cpabc-card-header">
            <h2>
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e( 'Calendar List / Items List', 'appointment-booking-calendar' ); ?>
            </h2>
        </div>
        <div class="cpabc-card-body no-padding">
            <div class="cpabc-table-wrap">
                <table class="wp-list-table widefat striped cpabc-cal-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><?php esc_html_e( 'ID', 'appointment-booking-calendar' ); ?></th>
                            <th style="width: 170px;"><?php esc_html_e( 'Calendar Name', 'appointment-booking-calendar' ); ?></th>
                            <th style="width: 150px;"><?php esc_html_e( 'Owner', 'appointment-booking-calendar' ); ?></th>
                            <th><?php esc_html_e( 'Actions & Management', 'appointment-booking-calendar' ); ?></th>
                            <th style="width: 110px;"><?php esc_html_e( 'iCal Feed', 'appointment-booking-calendar' ); ?></th>
                            <th style="width: 240px;"><?php esc_html_e( 'Shortcode', 'appointment-booking-calendar' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $users  = $wpdb->get_results( "SELECT user_login, ID FROM {$wpdb->users} ORDER BY ID DESC" );
                    $myrows = $wpdb->get_results( 'SELECT * FROM ' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME );

                    foreach ( $myrows as $item ) :
                        if ( ! cpabc_appointment_is_administrator() && (int) $current_user->ID !== (int) $item->conwer ) {
                            continue;
                        }
                        $ical_url  = add_query_arg(
                            array(
                                'cpabc_app' => 'calfeed',
                                'id'        => $item->id,
                                'verify'    => cpabc_appointments_get_ical_verify( $item->id ),
                            ),
                            get_site_url( false )
                        );
                        $shortcode = '[CPABC_APPOINTMENT_CALENDAR calendar="' . intval( $item->id ) . '"]';
                        ?>
                        <tr>
                            <td>
                                <span class="cpabc-id-badge"><?php echo esc_html( $item->id ); ?></span>
                            </td>
                            <td>
                                <input type="text"
                                    <?php echo ! cpabc_appointment_is_administrator() ? 'readonly' : ''; ?>
                                    name="calname_<?php echo esc_attr( $item->id ); ?>"
                                    id="calname_<?php echo esc_attr( $item->id ); ?>"
                                    value="<?php echo esc_attr( $item->uname ); ?>"
                                    class="regular-text" />
                            </td>
                            <td>
                                <?php if ( cpabc_appointment_is_administrator() ) : ?>
                                    <select name="calowner_<?php echo esc_attr( $item->id ); ?>" id="calowner_<?php echo esc_attr( $item->id ); ?>">
                                        <option value="0" <?php selected( empty( $item->conwer ) ); ?>><?php esc_html_e( '— No Owner —', 'appointment-booking-calendar' ); ?></option>
                                        <?php foreach ( $users as $user ) : ?>
                                            <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( (string) $user->ID, (string) $item->conwer ); ?>>
                                                <?php echo esc_html( $user->user_login ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else : ?>
                                    <strong><?php echo esc_html( $current_user->user_login ); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cpabc-actions-group">
                                    <?php if ( cpabc_appointment_is_administrator() ) : ?>
                                        <button type="button" class="button button-secondary cpabc-btn-icon" name="calupdate_<?php echo esc_attr( $item->id ); ?>" onclick="cp_updateItem(<?php echo esc_attr( $item->id ); ?>);">
                                            <span class="dashicons dashicons-yes"></span>
                                            <span><?php esc_html_e( 'Update', 'appointment-booking-calendar' ); ?></span>
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" class="button button-primary cpabc-btn-icon" name="calmanage_<?php echo esc_attr( $item->id ); ?>" onclick="cp_manageSettings(<?php echo esc_attr( $item->id ); ?>);">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <span><?php esc_html_e( 'Settings', 'appointment-booking-calendar' ); ?></span>
                                    </button>

                                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                                        <button type="button" class="button button-primary cpabc-btn-icon" name="calpublish_<?php echo esc_attr( $item->id ); ?>" onclick="cp_publish(<?php echo esc_attr( $item->id ); ?>);">
                                            <span class="dashicons dashicons-megaphone"></span>
                                            <span><?php esc_html_e( 'Publish', 'appointment-booking-calendar' ); ?></span>
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" class="button button-secondary cpabc-btn-icon" name="calbookings_<?php echo esc_attr( $item->id ); ?>" onclick="cp_BookingsList(<?php echo esc_attr( $item->id ); ?>);">
                                        <span class="dashicons dashicons-clipboard"></span>
                                        <span><?php esc_html_e( 'Bookings', 'appointment-booking-calendar' ); ?></span>
                                    </button>

                                    <button type="button" class="button button-secondary cpabc-btn-icon" name="calschedule_<?php echo esc_attr( $item->id ); ?>" onclick="cp_calendarschedule(<?php echo esc_attr( $item->id ); ?>);">
                                        <span class="dashicons dashicons-calendar"></span>
                                        <span><?php esc_html_e( 'Schedule', 'appointment-booking-calendar' ); ?></span>
                                    </button>

                                    <button type="button" class="button button-secondary cpabc-btn-icon" name="caladdbk_<?php echo esc_attr( $item->id ); ?>" onclick="cp_addbk(<?php echo esc_attr( $item->id ); ?>);">
                                        <span class="dashicons dashicons-plus-alt2"></span>
                                        <span><?php esc_html_e( 'Add Booking', 'appointment-booking-calendar' ); ?></span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="cpabc-actions-group" style="flex-wrap: nowrap;">
                                    <a href="<?php echo esc_url( $ical_url ); ?>" class="button button-small cpabc-btn-icon" title="<?php esc_attr_e( 'Download / Subscribe iCal Feed', 'appointment-booking-calendar' ); ?>">
                                        <span class="dashicons dashicons-rss"></span>
                                        <span><?php esc_html_e( 'iCal', 'appointment-booking-calendar' ); ?></span>
                                    </a>
                                    <button type="button" class="button button-small cpabc-btn-icon" onclick="cpabc_copyText('<?php echo esc_js( $ical_url ); ?>', this);" title="<?php esc_attr_e( 'Copy iCal URL', 'appointment-booking-calendar' ); ?>">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="cpabc-shortcode-box">
                                    <code><?php echo esc_html( $shortcode ); ?></code>
                                    <button type="button" class="cpabc-copy-btn" onclick="cpabc_copyText('<?php echo esc_js( $shortcode ); ?>', this);" title="<?php esc_attr_e( 'Copy Shortcode', 'appointment-booking-calendar' ); ?>">
                                        <span class="dashicons dashicons-clipboard"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ( cpabc_appointment_is_administrator() ) : ?>

        <!-- Multi-Calendar Upgrade Banner -->
        <div id="metabox_new_calendar" class="cpabc-upgrade-banner">
            <div class="cpabc-upgrade-banner-text">
                <span class="dashicons dashicons-info-outline"></span>
                <span>
                    <strong><?php esc_html_e( 'New Calendar / Item:', 'appointment-booking-calendar' ); ?></strong>
                    <?php esc_html_e( 'This free version supports one booking calendar.', 'appointment-booking-calendar' ); ?>
                </span>
            </div>
            <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer" class="button button-primary">
                <?php esc_html_e( 'Check Upgrade Options', 'appointment-booking-calendar' ); ?>
            </a>
        </div>

        <!-- Form Builder Settings & Troubleshoot Area -->
        <div id="metabox_troubleshoot_settings" class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e( 'Form Builder Settings & Troubleshoot Area', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <div class="notice notice-warning inline" style="margin: 0 0 16px 0;">
                    <p>
                        <strong><?php esc_html_e( 'Important!', 'appointment-booking-calendar' ); ?></strong>
                        <?php
                        echo wp_kses(
                            __( 'Use this area <strong>only</strong> if you want to activate the form builder or if you are experiencing conflicts with third-party plugins, theme scripts, or character encoding.', 'appointment-booking-calendar' ),
                            array( 'strong' => array() )
                        );
                        ?>
                    </p>
                </div>

                <form name="updatesettings" method="post" onsubmit="return false;">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="ccformrender"><?php esc_html_e( 'Form rendering', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select id="ccformrender" name="ccformrender" class="regular-text">
                                        <option value="1" selected><?php esc_html_e( 'Use classic predefined form', 'appointment-booking-calendar' ); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php
                                        echo wp_kses(
                                            sprintf(
                                                /* translators: %s: URL to commercial versions */
                                                __( 'The <strong>Visual Form Builder</strong> is available in the <a href="%s" target="_blank" rel="noopener noreferrer">commercial versions</a>. To edit the form in this basic version, manually edit the file <code>cpabc_scheduler.inc.php</code>.', 'appointment-booking-calendar' ),
                                                esc_url( 'https://abc.dwbooster.com/download' )
                                            ),
                                            array(
                                                'strong' => array(),
                                                'a'      => array(
                                                    'href'   => array(),
                                                    'target' => array(),
                                                    'rel'    => array(),
                                                ),
                                                'code'   => array(),
                                            )
                                        );
                                        ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="ccscriptload"><?php esc_html_e( 'Script load method', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <?php $script_load_opt = get_option( 'CPABC_APPOINTMENTS_LOAD_SCRIPTS', ( CPABC_APPOINTMENTS_DEFAULT_DEFER_SCRIPTS_LOADING ? '1' : '0' ) ); ?>
                                    <select id="ccscriptload" name="ccscriptload" class="regular-text">
                                        <option value="1" <?php selected( $script_load_opt, '1' ); ?>><?php esc_html_e( 'Classic (Recommended)', 'appointment-booking-calendar' ); ?></option>
                                        <option value="2" <?php selected( $script_load_opt !== '1' ); ?>><?php esc_html_e( 'Direct', 'appointment-booking-calendar' ); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Change the script load method if the booking form does not appear on the public website.', 'appointment-booking-calendar' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="cccharsets"><?php esc_html_e( 'Character encoding', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select id="cccharsets" name="cccharsets" class="regular-text">
                                        <option value=""><?php esc_html_e( 'Keep current charset (Recommended)', 'appointment-booking-calendar' ); ?></option>
                                        <option value="utf8_general_ci">UTF-8 (try this first)</option>
                                        <option value="latin1_swedish_ci">latin1_swedish_ci</option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Update the charset if you experience issues displaying special or non-Latin characters. After updating, re-enter any affected special characters.', 'appointment-booking-calendar' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="icaltimediff"><?php esc_html_e( 'iCal timezone difference vs server time', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <?php $current_tz_mod = trim( get_option( 'CPABC_CAL_TIME_ZONE_MODIFY_SET', ' +2 hours' ) ); ?>
                                    <select id="icaltimediff" name="icaltimediff" class="regular-text">
                                        <?php for ( $i = -23; $i < 24; $i++ ) : ?>
                                            <?php
                                            $text        = ' ' . ( $i < 0 ? '' : '+' ) . $i . ' hours';
                                            $trimmed_txt = trim( $text );
                                            $is_selected = ( $current_tz_mod === $trimmed_txt || $current_tz_mod === substr( $trimmed_txt, 1 ) );
                                            ?>
                                            <option value="<?php echo esc_attr( $text ); ?>" <?php selected( $is_selected ); ?>><?php echo esc_html( $text ); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Adjust this offset to match your desired timezone relative to the server time. Current server time:', 'appointment-booking-calendar' ); ?>
                                        <code><?php echo esc_html( date( 'Y-m-d H:i' ) ); ?></code>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="icaltimeslotsize"><?php esc_html_e( 'iCal timeslot size', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" min="1" step="1" class="small-text" name="icaltimeslotsize" id="icaltimeslotsize" value="<?php echo esc_attr( get_option( 'CPABC_CAL_TIME_SLOT_SIZE_SET', '30' ) ); ?>" />
                                    <span><?php esc_html_e( 'minutes', 'appointment-booking-calendar' ); ?></span>
                                    <p class="description">
                                        <?php esc_html_e( 'Duration of each appointment slot in the exported iCal file.', 'appointment-booking-calendar' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="excludecolumns"><?php esc_html_e( 'Exclude columns from CSV export', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="regular-text" name="excludecolumns" id="excludecolumns" placeholder="e.g. Calendar ID, UTIMEZONE" value="<?php echo esc_attr( get_option( 'CPABC_EXCLUDED_COLUMNS', '' ) ); ?>" />
                                    <p class="description">
                                        <?php esc_html_e( 'Comma-separated names of columns to exclude from exported CSV files.', 'appointment-booking-calendar' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit" style="margin-bottom: 0; padding-bottom: 0;">
                        <button type="button" onclick="cp_updateConfig();" name="gobtn" class="button button-primary cpabc-btn-icon">
                            <span class="dashicons dashicons-saved"></span>
                            <?php esc_html_e( 'Update Troubleshoot Settings', 'appointment-booking-calendar' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Customization Area -->
        <div id="metabox_basic_settings_custom" class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-art"></span>
                    <?php esc_html_e( 'Customization Area', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <p style="margin-top: 0;">
                    <?php esc_html_e( 'Use this area to add custom CSS styles or custom scripts. These styles and scripts are preserved safely across plugin updates.', 'appointment-booking-calendar' ); ?>
                </p>
                <button type="button" onclick="cp_editArea('css');" name="gobtn3" class="button button-secondary cpabc-btn-icon">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e( 'Add Custom Styles', 'appointment-booking-calendar' ); ?>
                </button>
            </div>
        </div>

        <!-- Add-ons Area -->
        <?php
        $cpabc_addons_list = array(
            array(
                'id'    => 'addon-appLimit-20180607',
                'title' => 'Limit the number of appointments per user',
                'desc'  => 'Adds support for limiting the number of appointments per user.',
            ),
            array(
                'id'    => 'addon-AuthNetSIM-20160910',
                'title' => 'Authorize.net Server Integration Method',
                'desc'  => 'Adds support for Authorize.net Server Integration Method payments.',
            ),
            array(
                'id'    => 'addon-clickatell-20170403',
                'title' => 'Clickatell',
                'desc'  => 'Allows sending SMS notification messages via Clickatell after submitting the form.',
            ),
            array(
                'id'    => 'addon-abcgooglecalapi-20190815',
                'title' => 'Google Calendar API',
                'desc'  => 'Adds support for two-way Google Calendar API integration.',
            ),
            array(
                'id'    => 'addon-iCalImport-20180607',
                'title' => 'iCal Automatic Import',
                'desc'  => 'Adds support for importing iCal files from external websites and services.',
            ),
            array(
                'id'    => 'addon-mailchimp-20170504',
                'title' => 'MailChimp',
                'desc'  => 'Creates MailChimp list members with the submitted booking information.',
            ),
            array(
                'id'    => 'addon-idealmollie-20160616',
                'title' => 'iDEAL Mollie',
                'desc'  => 'Adds support for iDEAL via Mollie payments.',
            ),
            array(
                'id'    => 'addon-paymentTimeout-20220114',
                'title' => 'Payment Timeout',
                'desc'  => 'Cancels unpaid appointments after a timeout and/or deletes cancelled items.',
            ),
            array(
                'id'    => 'addon-paytm-20160706',
                'title' => 'PayTM Payment Gateway',
                'desc'  => 'Adds support for PayTM payments.',
            ),
            array(
                'id'    => 'addon-PWGiftCard-20210523',
                'title' => 'PW WooCommerce Gift Cards Integration',
                'desc'  => 'Integrates booking payments with PW WooCommerce Gift Cards.',
            ),
            array(
                'id'    => 'addon-Razorpay-20210312',
                'title' => 'Razorpay Payment Gateway',
                'desc'  => 'Adds support for Razorpay payments (SCA and Classic).',
            ),
            array(
                'id'    => 'addon-recaptcha-20160616',
                'title' => 'reCAPTCHA',
                'desc'  => 'Protects booking forms with Google reCAPTCHA.',
            ),
            array(
                'id'    => 'addon-sabtpv-20160616',
                'title' => 'RedSys TPV',
                'desc'  => 'Adds support for RedSys TPV payments.',
            ),
            array(
                'id'    => 'addon-SagePay-20160706',
                'title' => 'SagePay Payment Gateway',
                'desc'  => 'Adds support for SagePay payments.',
            ),
            array(
                'id'    => 'addon-SagePayments-20160706',
                'title' => 'SagePayments Payment Gateway',
                'desc'  => 'Adds support for SagePayments payments.',
            ),
            array(
                'id'    => 'addon-salesforce-20160616',
                'title' => 'SalesForce',
                'desc'  => 'Creates SalesForce leads with the submitted information.',
            ),
            array(
                'id'    => 'addon-signature-20171025',
                'title' => 'Signature Fields',
                'desc'  => 'Allows replacing form fields with digital Signature fields.',
            ),
            array(
                'id'    => 'addon-Skrill-20170903',
                'title' => 'Skrill Payments Integration',
                'desc'  => 'Adds support for Skrill payments.',
            ),
            array(
                'id'    => 'addon-SquareCheckout-20200927',
                'title' => 'Square Checkout',
                'desc'  => 'Adds support for Square Checkout payments via Square hosted page.',
            ),
            array(
                'id'    => 'addon-stripe-20201230',
                'title' => 'Stripe',
                'desc'  => 'Adds support for Stripe payments (SCA and Classic).',
            ),
            array(
                'id'    => 'addon-summaryDisplay-20180607',
                'title' => 'Summary Display',
                'desc'  => 'Provides a shortcode to display a booking summary on the Thank You page.',
            ),
            array(
                'id'    => 'addon-idealtargetpay-20160616',
                'title' => 'iDEAL TargetPay',
                'desc'  => 'Adds support for iDEAL via TargetPay payments.',
            ),
            array(
                'id'    => 'addon-TwilioSMS-20180301',
                'title' => 'Twilio SMS Notifications',
                'desc'  => 'Adds support for Twilio SMS notifications for bookings and reminders.',
            ),
            array(
                'id'    => 'addon-webhook-20160616',
                'title' => 'WebHook & Zapier',
                'desc'  => 'Sends submitted information to a webhook URL and integrates with Zapier.',
            ),
            array(
                'id'    => 'addon-whiteList-20180607',
                'title' => 'Email Whitelist',
                'desc'  => 'Limits appointments to allowed whitelisted email addresses.',
            ),
            array(
                'id'    => 'addon-woocommerce-20160616',
                'title' => 'WooCommerce',
                'desc'  => 'Integrates booking forms with WooCommerce products and checkout.',
            ),
            array(
                'id'    => 'addon-WorldPay-20160706',
                'title' => 'WorldPay Payment Gateway',
                'desc'  => 'Adds support for WorldPay payments.',
            ),
            array(
                'id'    => 'addon-Zoom-20200509',
                'title' => 'Zoom.us Meetings Integration',
                'desc'  => 'Automatically creates a Zoom.us meeting for the booked time.',
            ),
        );
        ?>
        <div id="metabox_addons_settings" class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <?php esc_html_e( 'Add-ons Area', 'appointment-booking-calendar' ); ?>
                    <span class="cpabc-badge-pro"><?php esc_html_e( 'Platinum Version Only', 'appointment-booking-calendar' ); ?></span>
                </h2>
                <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer" class="button button-secondary button-small">
                    <?php esc_html_e( 'Check Upgrade Options', 'appointment-booking-calendar' ); ?>
                </a>
            </div>
            <div class="cpabc-card-body">
                <div class="cpabc-addons-grid">
                    <?php foreach ( $cpabc_addons_list as $addon ) : ?>
                        <label for="<?php echo esc_attr( $addon['id'] ); ?>" class="cpabc-addon-card">
                            <input type="checkbox" disabled id="<?php echo esc_attr( $addon['id'] ); ?>" name="cpabc_addons" value="<?php echo esc_attr( $addon['id'] ); ?>" />
                            <div>
                                <span class="cpabc-addon-title"><?php echo esc_html( $addon['title'] ); ?></span>
                                <span class="cpabc-addon-desc"><?php echo esc_html( $addon['desc'] ); ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 20px;">
                    <a href="https://abc.dwbooster.com/download" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                        <?php esc_html_e( 'Upgrade to Activate Add-ons', 'appointment-booking-calendar' ); ?>
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>