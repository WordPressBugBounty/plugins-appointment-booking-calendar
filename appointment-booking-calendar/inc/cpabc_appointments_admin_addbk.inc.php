<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_admin() && ! defined( 'CPABC_CALENDAR_ON_PUBLIC_WEBSITE' ) ) {
    echo 'Direct access not allowed.';
    exit;
}

$plugslug = 'cpabc_appointments.php';

if ( ! defined( 'CP_CALENDAR_ID' ) ) {
    $cal_param = isset( $_GET['cal'] ) ? intval( $_GET['cal'] ) : 1;
    define( 'CP_CALENDAR_ID', $cal_param > 0 ? $cal_param : 1 );
}

global $wpdb;

$calendar_name = '';
$cal_row       = $wpdb->get_row(
    $wpdb->prepare(
        'SELECT `' . CPABC_TDEAPP_CONFIG_USER . '` AS uname FROM `' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME . '` WHERE `' . CPABC_TDEAPP_CONFIG_ID . '` = %d',
        CP_CALENDAR_ID
    )
);
if ( $cal_row && ! empty( $cal_row->uname ) ) {
    $calendar_name = $cal_row->uname;
}

$is_booking_added = ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cpabc_appointments_post'] ) );
?>

<style type="text/css">
    .cpabc-addbk-wrap {
        max-width: 1080px;
        margin-top: 20px;
    }
    .cpabc-addbk-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .cpabc-addbk-header h1 {
        margin: 0;
        padding: 0;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 23px;
        font-weight: 500;
        line-height: 1.3;
    }
    .cpabc-addbk-header h1 .dashicons {
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
    .cpabc-addbk-card {
        background: #ffffff;
        border: 1px solid #c3c4c7;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .cpabc-addbk-card-header {
        padding: 14px 24px;
        background: #f6f7f7;
        border-bottom: 1px solid #dcdcde;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cpabc-addbk-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1d2327;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cpabc-addbk-card-body {
        padding: 24px;
    }
    .cpabc-success-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #edfaef;
        border: 1px solid #b8e6bf;
        border-left: 4px solid #00a32a;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }
    .cpabc-success-banner-text {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #1d2327;
        font-weight: 500;
    }
    .cpabc-success-banner-text .dashicons {
        color: #00a32a;
    }
</style>

<div class="wrap cpabc-addbk-wrap">

    <div class="cpabc-addbk-header">
        <h1>
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php esc_html_e( 'Add Booking', 'appointment-booking-calendar' ); ?>
            <?php if ( $calendar_name !== '' ) : ?>
                <span class="cpabc-cal-pill"><?php echo esc_html( $calendar_name ); ?> (#<?php echo intval( CP_CALENDAR_ID ); ?>)</span>
            <?php endif; ?>
        </h1>

        <div class="cpabc-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&list=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-clipboard"></span>
                <span><?php esc_html_e( 'Bookings List', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&calschedule=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-calendar"></span>
                <span><?php esc_html_e( 'Schedule', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <span><?php esc_html_e( 'Return to the calendars list', 'appointment-booking-calendar' ); ?></span>
            </a>
        </div>
    </div>

    <?php if ( $is_booking_added ) : ?>
        <div id="setting-error-settings_updated" class="cpabc-success-banner">
            <div class="cpabc-success-banner-text">
                <span class="dashicons dashicons-yes-alt"></span>
                <span><?php esc_html_e( 'Booking added successfully! It is now available in the bookings list.', 'appointment-booking-calendar' ); ?></span>
            </div>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&list=1' ) ); ?>" class="button button-primary cpabc-btn-icon">
                <span class="dashicons dashicons-visibility"></span>
                <span><?php esc_html_e( 'View Bookings List', 'appointment-booking-calendar' ); ?></span>
            </a>
        </div>
    <?php endif; ?>

    <div class="cpabc-info-banner">
        <span class="dashicons dashicons-info-outline"></span>
        <span>
            <?php esc_html_e( 'This page is for adding bookings from the administration area. The captcha and payment process are disabled in order to allow the website manager to easily add bookings.', 'appointment-booking-calendar' ); ?>
        </span>
    </div>

    <div class="cpabc-addbk-card">
        <div class="cpabc-addbk-card-header">
            <h2>
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php esc_html_e( 'Select Date, Time & Customer Details', 'appointment-booking-calendar' ); ?>
            </h2>
        </div>
        <div class="cpabc-addbk-card-body">
            <?php echo cpabc_appointments_filter_content( array( 'calendar' => CP_CALENDAR_ID ) ); ?>
        </div>
    </div>

</div>