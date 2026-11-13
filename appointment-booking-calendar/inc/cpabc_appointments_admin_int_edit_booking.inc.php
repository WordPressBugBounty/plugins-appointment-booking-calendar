<?php

if ( ! is_admin() ) {
    echo 'Direct access not allowed.';
    exit;
}

if ( ! defined( 'CP_CALENDAR_ID' ) ) {
    define( 'CP_CALENDAR_ID', intval( $_GET['cal'] ) );
}

global $wpdb;

$plugslug       = 'cpabc_appointments.php';
$current_user   = wp_get_current_user();
$mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME . ' WHERE `' . CPABC_TDEAPP_CONFIG_ID . '` = %d', CP_CALENDAR_ID ) );
$calendar_name  = ( isset( $mycalendarrows[0] ) && ! empty( $mycalendarrows[0]->uname ) ) ? $mycalendarrows[0]->uname : '';

function cpabcedit_verify_nonce() {
    if ( isset( $_GET['rsaveedit'] ) && $_GET['rsaveedit'] !== '' ) {
        $nonce = sanitize_text_field( wp_unslash( $_GET['rsaveedit'] ) );
    } else {
        $nonce = isset( $_POST['rsaveedit'] ) ? sanitize_text_field( wp_unslash( $_POST['rsaveedit'] ) ) : '';
    }
    $verify_nonce = wp_verify_nonce( $nonce, 'uname_abc_editlist' );
    if ( ! $verify_nonce ) {
        echo 'Error: Form cannot be authenticated (nonce failed). Please contact our <a href="https://abc.dwbooster.com/contact-us">support service</a> for verification and solution. Thank you.';
        exit;
    }
}

if ( cpabc_appointment_is_administrator() || ( isset( $mycalendarrows[0] ) && (int) $mycalendarrows[0]->conwer === (int) $current_user->ID ) ) {

    $event = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME . ' WHERE id = %d', intval( $_GET['edit'] ) ) );
    $event = $event[0];

    $params = array();
    if ( $event->reference != '' ) {
        $form_data   = json_decode( cpabc_appointment_cleanJSON( cpabc_get_option( 'form_structure', CPABC_APPOINTMENTS_DEFAULT_form_structure ) ) );
        $org_booking = $wpdb->get_results( $wpdb->prepare( 'SELECT buffered_date FROM ' . CPABC_APPOINTMENTS_TABLE_NAME . ' WHERE id = %d', intval( $event->reference ) ) );
        $params      = unserialize( $org_booking[0]->buffered_date );
        unset( $params['QUANTITY'] );
        unset( $params['DATE'] );
        unset( $params['TIME'] );
    } else {
        $params['description'] = $event->description;
    }

    if ( count( $_POST ) > 0 ) {
        cpabcedit_verify_nonce();
        $datatime = sanitize_text_field( wp_unslash( $_POST['datatime'] ) ) . ' ' . sanitize_text_field( wp_unslash( $_POST['datatime_hour'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['datatime_minutes'] ) ) . ':00';
        if ( cpabc_get_option( 'calendar_militarytime', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_MILITARYTIME ) == '0' ) {
            $format = 'g:i A';
        } else {
            $format = 'H:i';
        }
        $dfoption = cpabc_get_option( 'calendar_dateformat', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_DATEFORMAT );
        if ( $dfoption == '0' ) {
            $format = 'm/d/Y ' . $format;
        } elseif ( $dfoption == '2' ) {
            $format = 'd.m.Y ' . $format;
        } else {
            $format = 'd/m/Y ' . $format;
        }

        if ( cpabc_get_option( 'calendar_militarytime', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_MILITARYTIME ) == '0' ) {
            $format_t = 'g:i A';
        } else {
            $format_t = 'H:i';
        }
        if ( cpabc_get_option( 'calendar_dateformat', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_DATEFORMAT ) == '0' ) {
            $format_d = 'm/d/Y ';
        } else {
            $format_d = 'd/m/Y ';
        }

        $params_new             = $params;
        $params_new['DATE']     = date( $format_d, strtotime( sanitize_text_field( wp_unslash( $_POST['datatime'] ) ) ) );
        $params_new['TIME']     = date( $format_t, strtotime( sanitize_text_field( wp_unslash( $_POST['datatime_hour'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['datatime_minutes'] ) ) ) );
        $params_new['QUANTITY'] = intval( $_POST['quantity'] );

        foreach ( $params as $item => $value ) {
            if ( isset( $_POST[ $item ] ) ) {
                $params_new[ $item ] = sanitize_text_field( wp_unslash( $_POST[ $item ] ) );
            }
        }

        $description          = cpabc_get_option( 'uname', '' ) . '<br />' . date( $format, strtotime( $datatime ) ) . '<br />';
        $description_customer = cpabc_get_option( 'uname', '' ) . '<br />' . date( $format, strtotime( $datatime ) ) . '<br />';
        foreach ( $params_new as $item => $value ) {
            if ( $value != '' && $item != 'DATE' && $item != 'TIME' && $item != 'QUANTITY' && $item != 'UTIMEZONE' && $item != 'PRICE' && $item != 'request_timestamp' && $item != 'MAINDATE' ) {
                $name         = cpabc_appointments_get_field_name( $item, $form_data[0] );
                $description .= $name . ': ' . $value . '<br />';
                if ( $name != 'Juniors' && $name != 'IP' && $name != 'PRICE' && $name != 'DATE_CUSTOMER' && $name != 'TIME_CUSTOMER' ) {
                    $description_customer .= $name . ': ' . $value . '<br />';
                }
            }
        }

        if ( $event->reference == '' ) {
            $description = wp_kses_post( wp_unslash( $_POST['description'] ) );
        }

        $data1 = array(
            'datatime'             => $datatime,
            'quantity'             => intval( $_POST['quantity'] ),
            'title'                => sanitize_text_field( wp_unslash( $_POST['title'] ) ),
            'description'          => $description,
            'description_customer' => $description_customer,
            'who_edited'           => $current_user->ID,
        );

        $data2 = array(
            'booked_time_unformatted' => $datatime,
            'booked_time'             => date( $format, strtotime( $datatime ) ),
            'booked_time_customer'    => date( $format, strtotime( $datatime ) ),
            'quantity'                => intval( $_POST['quantity'] ),
            'buffered_date'           => serialize( $params_new ),
        );

        $wpdb->update( CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME, $data1, array( 'id' => intval( $_GET['edit'] ) ) );
        if ( $event->reference != '' ) {
            $wpdb->update( CPABC_APPOINTMENTS_TABLE_NAME, $data2, array( 'id' => intval( $event->reference ) ) );
        }

        echo '<script type="text/javascript">document.location = "admin.php?page=cpabc_appointments.php&cal=' . intval( $_GET['cal'] ) . '&list=1&message=Item+updated&r=" + Math.random();</script>';
        exit;
    }

    $date   = date( 'Y-m-d', strtotime( $event->datatime ) );
    $hour   = intval( date( 'G', strtotime( $event->datatime ) ) );
    $minute = intval( date( 'i', strtotime( $event->datatime ) ) );
    if ( strlen( (string) $minute ) === 2 && substr( (string) $minute, 0, 1 ) === '0' ) {
        $minute = intval( substr( (string) $minute, 1, 1 ) );
    }

    $nonce_un = wp_create_nonce( 'uname_abc_editlist' );
    ?>

<style type="text/css">
    .cpabc-admin-wrap {
        max-width: 980px;
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
        padding: 14px 24px;
        background: #f6f7f7;
        border-bottom: 1px solid #dcdcde;
        display: flex;
        align-items: center;
        justify-content: space-between;
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
    }
    .cpabc-card-body {
        padding: 24px;
    }
    .cpabc-card-footer {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-top: 18px;
        margin-top: 18px;
        border-top: 1px solid #dcdcde;
    }
</style>

<div class="wrap cpabc-admin-wrap">

    <div class="cpabc-header">
        <h1>
            <span class="dashicons dashicons-edit"></span>
            <?php esc_html_e( 'Edit Booking', 'appointment-booking-calendar' ); ?>
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
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug . '&cal=' . intval( CP_CALENDAR_ID ) . '&addbk=1' ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span><?php esc_html_e( 'Add Booking', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <span><?php esc_html_e( 'Return to the calendars list', 'appointment-booking-calendar' ); ?></span>
            </a>
        </div>
    </div>

    <form method="post" name="dexeditfrm" action="">
        <input type="hidden" name="rsaveedit" value="<?php echo esc_attr( $nonce_un ); ?>" />

        <div id="metabox_basic_settings" class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'Appointment Data', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">
                <table class="form-table" role="presentation" style="margin-top:0;">
                    <tr valign="top">
                        <th scope="row"><label for="datatime"><?php esc_html_e( 'Date', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="datatime" id="datatime" class="regular-text" value="<?php echo esc_attr( $date ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Time', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <select name="datatime_hour">
                                <?php for ( $i = 0; $i < 24; $i++ ) : ?>
                                    <option <?php selected( $i, $hour ); ?>><?php echo esc_html( ( $i < 10 ? '0' : '' ) . $i ); ?></option>
                                <?php endfor; ?>
                            </select> :
                            <select name="datatime_minutes">
                                <?php for ( $i = 0; $i < 60; $i += 5 ) : ?>
                                    <option <?php selected( $i, $minute ); ?>><?php echo esc_html( ( $i < 10 ? '0' : '' ) . $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Appointment Title', 'appointment-booking-calendar' ); ?></label></th>
                        <td><input type="text" name="title" class="regular-text" value="<?php echo esc_attr( $event->title ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php esc_html_e( 'Quantity', 'appointment-booking-calendar' ); ?></label></th>
                        <td>
                            <select name="quantity">
                                <?php for ( $i = 1; $i < $event->quantity + 20; $i++ ) : ?>
                                    <option <?php selected( intval( $event->quantity ), $i ); ?>><?php echo intval( $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                    $excluded = explode( ',', get_option( 'CPABC_EXCLUDED_COLUMNS', '' ) );
                    for ( $i = 0; $i < count( $excluded ); $i++ ) {
                        $excluded[ $i ] = strtolower( trim( $excluded[ $i ] ) );
                    }
                    $excluded[] = 'utimezone';
                    $excluded[] = 'couponcode';
                    $excluded[] = 'request_timestamp';
                    foreach ( $params as $item => $value ) {
                        if ( ! is_array( $value ) && ! in_array( strtolower( $item ), $excluded, true ) ) {
                            $name = isset( $form_data[0] ) ? cpabc_appointments_get_field_name( $item, $form_data[0] ) : $item;
                            ?>
                            <tr valign="top">
                                <th scope="row"><label><?php echo esc_html( $name ); ?></label></th>
                                <td>
                                    <?php if ( ! is_array( $value ) && ( strpos( $value, "\n" ) > 0 || strlen( $value ) > 80 ) ) : ?>
                                        <textarea cols="85" rows="4" class="large-text" name="<?php echo esc_attr( $item ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
                                    <?php else : ?>
                                        <input type="text" class="regular-text" name="<?php echo esc_attr( $item ); ?>" value="<?php echo esc_attr( is_array( $value ) ? implode( ',', $value ) : $value ); ?>" />
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>

                <div class="cpabc-card-footer">
                    <button type="submit" name="submit" id="submit" class="button button-primary cpabc-btn-icon">
                        <span class="dashicons dashicons-saved"></span>
                        <span><?php esc_html_e( 'Save Changes', 'appointment-booking-calendar' ); ?></span>
                    </button>
                    <button type="button" class="button button-secondary cpabc-btn-icon" onclick="gobackapp();">
                        <span class="dashicons dashicons-no-alt"></span>
                        <span><?php esc_html_e( 'Cancel', 'appointment-booking-calendar' ); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(function() {
        $j("#datatime").datepicker({
            dateFormat: 'yy-mm-dd'
        });
    });
    function gobackapp() {
        document.location = 'admin.php?page=cpabc_appointments.php&cal=<?php echo intval( $_GET['cal'] ); ?>&list=1&r=' + Math.random();
    }
</script>

<?php } else { ?>
    <div class="wrap cpabc-admin-wrap">
        <div class="notice notice-error">
            <p><?php esc_html_e( "The current user logged in doesn't have enough permissions to edit this item. Please log in as administrator to get full access.", 'appointment-booking-calendar' ); ?></p>
        </div>
    </div>
<?php } ?>