<?php
if ( ! defined( 'CPABC_AUTH_INCLUDE' ) ) {
    echo 'Direct access not allowed.';
    exit;
}

$nonce_un      = wp_create_nonce( 'cpabc_bkcontrol' );
$custom_styles = sanitize_textarea_field( base64_decode( get_option( 'CP_ABC_CSS', '' ) ) );
if ( $custom_styles !== '' ) {
    echo '<style type="text/css">' . $custom_styles . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( ! is_admin() ) {
    $full_url = get_permalink();

    // Parse the URL to get the path
    $path = parse_url( $full_url, PHP_URL_PATH );
    if ( $path == '' ) {
        $path = $full_url;
    }

    // Add a random GET parameter to the path to prevent cache
    $cache_buster = wp_rand( 100000, 999999 );
    $path        .= ( strpos( $path, '?' ) === false ? '?' : '&' ) . 'abcnocache=' . $cache_buster;
} else {
    $path = '';
}

$cal_pages = is_admin() ? 3 : intval( cpabc_get_option( 'calendar_pages', CPABC_APPOINTMENTS_DEFAULT_CALENDAR_PAGES ) );
if ( $cal_pages < 1 ) {
    $cal_pages = 1;
}
?>
<style type="text/css">
/* Core Overlay (dims the background) */
.ui-widget-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100;
}

/* Main Dialog Container */
.ui-dialog {
    position: absolute;
    top: 0;
    left: 0;
    padding: .2em;
    outline: 0;
    background: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 101;
}

/* Dialog Header */
.ui-dialog-titlebar {
    padding: .4em 1em;
    position: relative;
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
    font-weight: bold;
}

/* Close Button Position */
.ui-dialog-titlebar-close {
    position: absolute;
    right: .3em;
    top: 50%;
    width: 20px;
    margin: -10px 0 0 0;
    padding: 1px;
    height: 20px;
    cursor: pointer;
}

/* Content Area */
.ui-dialog-content {
    position: relative;
    border: 0;
    padding: .5em 1em;
    background: none;
    overflow: auto;
}

/* Button Pane at bottom */
.ui-dialog-buttonpane {
    text-align: left;
    border-width: 1px 0 0 0;
    background-image: none;
    margin-top: .5em;
    padding: .3em 1em .5em .4em;
    border-top: 1px solid #eee;
}

/* Modern Scoped Form Styles */
.cpabc-booking-form-wrap {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    color: #1d2327;
    <?php if ( $cal_pages === 1 ) : ?>
    max-width: 650px;
    <?php endif; ?>
}
.cpabc-booking-form-wrap .abc_selectdate {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #1d2327;
}
.cpabc-booking-form-wrap .appContainer {
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.cpabc-booking-form-wrap .cpabc-cal-selector {
    margin-bottom: 16px;
}
.cpabc-booking-form-wrap .cpabc-cal-selector label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 14px;
}
.cpabc-booking-form-wrap .cpabc-cal-selector select,
.cpabc-booking-form-wrap #abc_capacity {
    min-width: 200px;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
}

/* Administrator Options Panel */
.cpabc-admin-options {
    border: 1px solid #dcdcde;
    border-left: 4px solid #2271b1;
    border-radius: 6px;
    background: #f8fafc;
    padding: 16px 20px;
    margin: 20px 0;
    max-width: 680px;
    box-sizing: border-box;
}
.cpabc-admin-options legend {
    font-weight: 600;
    font-size: 14px;
    color: #1d2327;
    padding: 0 8px;
    background: #ffffff;
    border: 1px solid #dcdcde;
    border-radius: 4px;
}
.cpabc-admin-check-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    font-size: 13.5px;
    cursor: pointer;
    margin-bottom: 10px;
}
.cpabc-admin-check-label input[type="checkbox"] {
    margin: 0;
}
#repeat {
    background: #ffffff;
    border: 1px solid #dcdcde;
    border-radius: 5px;
    padding: 14px 16px;
    margin-top: 8px;
}
#repeat > div,
#repeatOptions > div {
    padding-top: 6px;
    padding-bottom: 6px;
}
#repeat label {
    font-weight: 600;
    margin-right: 6px;
    font-size: 13px;
}
#repeat select,
#repeat input[type="text"],
#end_until_input {
    padding: 4px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 13px;
}
#repeat select {
    min-width: 90px;
    width: auto;
    padding-right: 28px;
}
#repeat #interval,
#repeat #end_after {
    min-width: 65px;
    width: auto;
    padding-right: 24px;
}
#bydayweek input[type="checkbox"] {
    margin: 0 3px 0 8px;
    vertical-align: middle;
}
#bydaymonth input[type="radio"] {
    margin: 0 3px 0 8px;
    vertical-align: middle;
}
#repeatList {
    margin-top: 8px;
    padding: 8px 12px;
    background: #f6f7f7;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    max-height: 160px;
    overflow-y: auto;
    font-size: 12.5px;
}
#repeatList div {
    padding-top: 2px;
    padding-bottom: 2px;
}
.cpabc-admin-note {
    margin: 10px 0 0 0;
    font-size: 12.5px;
    color: #646970;
    font-style: italic;
}

/* Customer Details Form Fields */
.cpabc-customer-fields {
    margin-top: 20px;
    max-width: 650px;
}
.cpabc-fields-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.cpabc-field-group {
    margin-bottom: 16px;
}
.cpabc-fields-grid .cpabc-field-group {
    margin-bottom: 0;
}
.cpabc-field-group label {
    display: block;
    font-weight: 600;
    font-size: 13.5px;
    margin-bottom: 6px;
    color: #1d2327;
}
.cpabc-field-group input[type="text"],
.cpabc-field-group input[type="email"],
.cpabc-field-group textarea,
#hdcaptcha {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.5;
    box-sizing: border-box;
    background-color: #fff;
    color: #1d2327;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.cpabc-field-group input[type="text"]:focus,
.cpabc-field-group input[type="email"]:focus,
.cpabc-field-group textarea:focus,
#hdcaptcha:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}
.cpabc-field-group textarea {
    min-height: 90px;
    resize: vertical;
}

/* Captcha Section */
.cpabc-captcha-box {
    background: #f8fafc;
    border: 1px solid #dcdcde;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 20px;
    max-width: 340px;
    box-sizing: border-box;
}
.cpabc-captcha-image-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.cpabc-captcha-image-row img {
    display: block;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    background: #fff;
    max-width: 100%;
    height: auto;
}
.cpabc-captcha-refresh {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    padding: 0;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    background: #ffffff;
    color: #2271b1;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.15s ease;
}
.cpabc-captcha-refresh:hover {
    background: #f0f6fc;
    border-color: #2271b1;
    color: #135e96;
}
.cpabc-captcha-refresh svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

/* Submit Button */
.cpabc-booking-form-wrap .cp_subbtn.pbSubmit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #2271b1;
    color: #ffffff;
    border: 1px solid #2271b1;
    border-radius: 4px;
    padding: 9px 22px;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.cpabc-booking-form-wrap .cp_subbtn.pbSubmit:hover {
    background: #135e96;
    border-color: #135e96;
}
/* Captcha Inline Error Styles */
#hdcaptcha.cpabc-input-error {
    border-color: #d63638 !important;
    box-shadow: 0 0 0 1px #d63638 !important;
    background-color: #fcf0f1;
}
.cpabc-captcha-error {
    display: none;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 8px 10px;
    background: #fcf0f1;
    border: 1px solid #f0b8b8;
    border-left: 3px solid #d63638;
    border-radius: 4px;
    color: #b32d2e;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
}
.cpabc-captcha-error.is-visible {
    display: flex;
    animation: cpabcShake 0.35s ease-in-out;
}
.cpabc-captcha-error svg {
    width: 16px;
    height: 16px;
    fill: #d63638;
    flex-shrink: 0;
}
@keyframes cpabcShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    50% { transform: translateX(4px); }
    75% { transform: translateX(-4px); }
}
/* Date & Time Inline Error Styles */
.cpabc-datetime-error {
    display: none;
    align-items: center;
    gap: 8px;
    margin: 10px 0 14px 0;
    padding: 10px 14px;
    background: #fcf0f1;
    border: 1px solid #f0b8b8;
    border-left: 4px solid #d63638;
    border-radius: 4px;
    color: #b32d2e;
    font-size: 13.5px;
    font-weight: 500;
    line-height: 1.4;
    max-width: 650px;
    box-sizing: border-box;
}
.cpabc-datetime-error.is-visible {
    display: flex;
    animation: cpabcShake 0.35s ease-in-out;
}
.cpabc-datetime-error svg {
    width: 18px;
    height: 18px;
    fill: #d63638;
    flex-shrink: 0;
}
.cpabc-cal-error-highlight .appContainer2 {
    outline: 2px solid #d63638 !important;
    outline-offset: 2px;
    border-radius: 6px;
    transition: outline 0.15s ease;
}
</style>

<script type="text/javascript">
function cpabc_refreshCaptcha() {
    var img = document.getElementById('captchaimg');
    if (img) {
        var baseSrc = img.src.replace(/&r=\d+/g, '');
        img.src = baseSrc + '&r=' + Math.floor((Math.random() * 99999) + 1);
    }
    cpabc_clearCaptchaError();
}

function cpabc_showDateTimeError(msg) {
    var errEl = document.getElementById('cpabc_datetime_error');
    var textEl = document.getElementById('cpabc_datetime_error_text');
    var calArea = document.getElementById('calarea_' + cpabc_current_calendar_item);

    if (calArea) {
        calArea.classList.add('cpabc-cal-error-highlight');
    }
    if (errEl && textEl) {
        textEl.textContent = msg;
        errEl.classList.remove('is-visible');
        void errEl.offsetWidth; // Restart shake animation
        errEl.classList.add('is-visible');
        if (calArea && typeof calArea.scrollIntoView === 'function') {
            calArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } else {
        alert(msg);
    }
}

function cpabc_clearDateTimeError() {
    var errEl = document.getElementById('cpabc_datetime_error');
    var calArea = document.getElementById('calarea_' + cpabc_current_calendar_item);
    if (calArea) {
        calArea.classList.remove('cpabc-cal-error-highlight');
    }
    if (errEl) {
        errEl.classList.remove('is-visible');
    }
}

function cpabc_showCaptchaError(msg) {
    var errEl = document.getElementById('hdcaptcha_error');
    var inputEl = document.getElementById('hdcaptcha');
    var textEl = document.getElementById('hdcaptcha_error_text');
    if (inputEl) {
        inputEl.classList.add('cpabc-input-error');
        inputEl.focus();
    }
    if (errEl && textEl) {
        textEl.textContent = msg;
        errEl.classList.remove('is-visible');
        void errEl.offsetWidth; // Restart shake animation
        errEl.classList.add('is-visible');
    } else {
        alert(msg);
    }
}

function cpabc_clearCaptchaError() {
    var errEl = document.getElementById('hdcaptcha_error');
    var inputEl = document.getElementById('hdcaptcha');
    if (inputEl) {
        inputEl.classList.remove('cpabc-input-error');
    }
    if (errEl) {
        errEl.classList.remove('is-visible');
    }
}
</script>

<form class="cpp_form no-prefetch cpabc-booking-form-wrap" id="cp_abcform_pform" name="FormEdit" action="<?php echo esc_attr( $path ); ?>" method="post" onsubmit="return doValidate(this);">
    <input name="_wpnonce" type="hidden" value="<?php echo esc_attr( $nonce_un ); ?>" />
    <input name="cpabc_appointments_post" type="hidden" value="1" />
    <input name="cpabc_appointments_utime" type="hidden" value="" />

    <?php echo $quant_buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <div class="cpabc-cal-selector" <?php if ( count( $myrows ) < 2 ) echo 'style="display:none"'; ?>>
        <label for="cpabc_item"><?php esc_html_e( 'Calendar', 'appointment-booking-calendar' ); ?>:</label>
        <select name="cpabc_item" id="cpabc_item" onchange="cpabc_updateItem()"><?php echo $calendar_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></select>
    </div>

    <div class="abc_selectdate fields"><?php esc_html_e( 'Select date and time', 'appointment-booking-calendar' ); ?>:</div>

    <?php
    if ( isset( $_GET['fl_builder'] ) ) {
        echo '<div style="border:1px dotted black;background-color:#ffffbb;padding:7px;">NOTE: <strong>The Appointment Calendar will be rendered here</strong>. Calendar is disabled while this visual editor is in use. Will appear again after closing edition.</div>';
    }
    foreach ( $myrows as $item ) {
        $atlang = cpabc_auto_language( $item->calendar_language );
        echo '<div id="calarea_' . intval( $item->id ) . '" style="display:none' . ( is_rtl() ? ';float:right;' : '' ) . '">'
            . '<input name="selDaycal' . intval( $item->id ) . '" type="hidden" id="selDaycal' . intval( $item->id ) . '" />'
            . '<input name="selMonthcal' . intval( $item->id ) . '" type="hidden" id="selMonthcal' . intval( $item->id ) . '" />'
            . '<input name="selYearcal' . intval( $item->id ) . '" type="hidden" id="selYearcal' . intval( $item->id ) . '" />'
            . '<input name="selHourcal' . intval( $item->id ) . '" type="hidden" id="selHourcal' . intval( $item->id ) . '" />'
            . '<input name="selMinutecal' . intval( $item->id ) . '" type="hidden" id="selMinutecal' . intval( $item->id ) . '" />'
            . '<div class="appContainer"><div style="z-index:1000;" class="appContainer2"><div id="cal' . intval( $item->id ) . 'Container"></div></div></div>'
            . '<div style="clear:both;"></div>'
            . '<div id="listcal' . intval( $item->id ) . '" class="listcal"></div>'
            . '</div>';
    }
    ?>
    <div class="cpabc-datetime-error" id="cpabc_datetime_error" role="alert" aria-live="polite">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
        </svg>
        <span id="cpabc_datetime_error_text"></span>
    </div>

    <?php if ( is_admin() && ! defined( 'ABC_ELEMENTOR_EDIT_MODE' ) && cpabc_get_get_param( 'action' ) != 'edit' ) : ?>
        <fieldset class="cpabc-admin-options">
            <legend><?php esc_html_e( 'Administrator options', 'appointment-booking-calendar' ); ?></legend>

            <label class="cpabc-admin-check-label">
                <input type="checkbox" name="sendemails_admin" value="1" vt="1" checked />
                <span><?php esc_html_e( 'Send notification emails for this booking', 'appointment-booking-calendar' ); ?></span>
            </label>

            <div id="repeat" style="display:none">
                <div>
                    <label id="rl1" for="freq"><?php esc_html_e( 'Repeats:', 'appointment-booking-calendar' ); ?></label>
                    <select id="freq" name="freq">
                        <option id="opt0" value="10"><?php esc_html_e( 'No', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt0" value="0"><?php esc_html_e( 'Daily', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt1" value="1"><?php esc_html_e( 'Every weekday (Monday to Friday)', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt2" value="2"><?php esc_html_e( 'Every Monday, Wednesday, and Friday', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt3" value="3"><?php esc_html_e( 'Every Tuesday, and Thursday', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt4" value="4"><?php esc_html_e( 'Weekly', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt5" value="5"><?php esc_html_e( 'Monthly', 'appointment-booking-calendar' ); ?></option>
                        <option id="opt6" value="6"><?php esc_html_e( 'Yearly', 'appointment-booking-calendar' ); ?></option>
                    </select>
                </div>

                <div id="repeatOptions" style="display:none">
                    <div id="intervaldiv">
                        <label id="rl2" for="interval"><?php esc_html_e( 'Repeat every:', 'appointment-booking-calendar' ); ?></label>
                        <select id="interval"></select> <span id="interval_label"><?php esc_html_e( 'weeks', 'appointment-booking-calendar' ); ?></span>
                    </div>

                    <div id="bydayweek">
                        <label id="rl3"><?php esc_html_e( 'Repeat on:', 'appointment-booking-calendar' ); ?></label>
                        <input id="bydaySU" class="bydayw" name="SU" type="checkbox"><span id="chk0">SU</span>
                        <input id="bydayMO" class="bydayw" name="MO" type="checkbox"><span id="chk1">MO</span>
                        <input id="bydayTU" class="bydayw" name="TU" type="checkbox"><span id="chk2">TU</span>
                        <input id="bydayWE" class="bydayw" name="WE" type="checkbox"><span id="chk3">WE</span>
                        <input id="bydayTH" class="bydayw" name="TH" type="checkbox"><span id="chk4">TH</span>
                        <input id="bydayFR" class="bydayw" name="FR" type="checkbox"><span id="chk5">FR</span>
                        <input id="bydaySA" class="bydayw" name="SA" type="checkbox"><span id="chk6">SA</span>
                    </div>

                    <div id="bydaymonth">
                        <label id="rl4"><?php esc_html_e( 'Repeat by:', 'appointment-booking-calendar' ); ?></label>
                        <input id="byday_m" class="bydaym" name="bydaym" type="radio" value="1" checked="checked"> <span id="bydaymonth1"><?php esc_html_e( 'day of the month', 'appointment-booking-calendar' ); ?></span>
                        <input id="byday_w" class="bydaym" name="bydaym" type="radio" value="2"> <span id="bydaymonth2"><?php esc_html_e( 'day of the week', 'appointment-booking-calendar' ); ?></span>
                    </div>

                    <div class="clear"></div>

                    <div>
                        <div class="fl">
                            <div>
                                <?php esc_html_e( 'Ends', 'appointment-booking-calendar' ); ?>
                                <input style="display:none" id="end_count" name="end" checked="" title="Ends after a number of occurrences" type="radio">
                                <span id="end21"><?php esc_html_e( 'after', 'appointment-booking-calendar' ); ?></span>
                                <select id="end_after" onchange="document.getElementById('end_count').checked=true;"></select>
                                <span id="end22"><?php esc_html_e( 'occurrences', 'appointment-booking-calendar' ); ?></span>
                                <?php esc_html_e( 'or', 'appointment-booking-calendar' ); ?>
                                <input style="display:none" id="end_until" name="end" title="Ends on a specified date" type="radio">
                                <span id="end3"><?php esc_html_e( 'on', 'appointment-booking-calendar' ); ?></span>
                                <input size="10" id="end_until_input" value="" onchange="document.getElementById('end_until').checked=true;">
                            </div>
                        </div>
                    </div>

                    <div class="clear"></div>

                    <div style="display:none">
                        <label id="rl7"><?php esc_html_e( 'Summary:', 'appointment-booking-calendar' ); ?></label>
                        <span id="summary"></span>
                    </div>

                    <div>
                        <label id="rl5"><?php esc_html_e( 'Starts on', 'appointment-booking-calendar' ); ?></label>
                        <label id="starts" style="font-weight:bold;"></label>
                        <label id="rl5"><?php esc_html_e( ' and repeated on:', 'appointment-booking-calendar' ); ?></label>
                    </div>
                </div>

                <input type="hidden" id="format" value="FREQ=DAILY" size="55" />
                <div id="repeatList"></div>
            </div>

            <p class="cpabc-admin-note">
                * <?php esc_html_e( 'Note: Repeat / recurrent options are displayed after selecting a time-slot in the calendar.', 'appointment-booking-calendar' ); ?>
            </p>
        </fieldset>
    <?php endif; ?>

    <div style="clear:both;"></div>

    <div class="cpabc-customer-fields">
        <div class="cpabc-fields-grid">
            <div class="cpabc-field-group">
                <label for="cpabc_field_name"><?php esc_html_e( 'Your name', 'appointment-booking-calendar' ); ?> *</label>
                <input type="text" id="cpabc_field_name" name="name" required="required" value="" />
            </div>

            <div class="cpabc-field-group">
                <label for="cpabc_field_email"><?php esc_html_e( 'Your email', 'appointment-booking-calendar' ); ?> *</label>
                <input type="email" id="cpabc_field_email" name="email" required="required" value="" />
            </div>

            <div class="cpabc-field-group">
                <label for="cpabc_field_phone"><?php esc_html_e( 'Your phone number', 'appointment-booking-calendar' ); ?> *</label>
                <input type="text" id="cpabc_field_phone" name="phone" required="required" value="" />
            </div>
        </div>

        <div class="cpabc-field-group">
            <label for="cpabc_field_question"><?php esc_html_e( 'Comments/Questions', 'appointment-booking-calendar' ); ?></label>
            <textarea id="cpabc_field_question" name="question"></textarea>
        </div>

        <?php if ( ! is_admin() && cpabc_get_option( 'dexcv_enable_captcha', CPABC_TDEAPP_DEFAULT_dexcv_enable_captcha ) != 'false' ) : ?>
            <div class="cpabc-captcha-box cpabc-field-group">
                <label><?php esc_html_e( 'Please enter the security code:', 'appointment-booking-calendar' ); ?></label>
                <div class="cpabc-captcha-image-row">
                    <img src="<?php echo esc_url( cpabc_appointment_get_site_url() . '/?cpabc_app=captcha' . cpabc_get_captcha_params() ); ?>" id="captchaimg" alt="security code" border="0" class="skip-lazy" />
                    <button type="button" class="cpabc-captcha-refresh" onclick="cpabc_refreshCaptcha();" title="<?php esc_attr_e( 'Refresh security code', 'appointment-booking-calendar' ); ?>" aria-label="<?php esc_attr_e( 'Refresh security code', 'appointment-booking-calendar' ); ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M17.65 6.35A7.958 7.958 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                    </button>
                </div>

                <label for="hdcaptcha"><?php esc_html_e( 'Security Code:', 'appointment-booking-calendar' ); ?> *</label>
                <div class="dfield">
                    <input type="text" size="20" name="hdcaptcha" required="required" id="hdcaptcha" value="" oninput="cpabc_clearCaptchaError();" />
                    <div class="cpabc-captcha-error" id="hdcaptcha_error" role="alert" aria-live="polite">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <span id="hdcaptcha_error_text"></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" name="subbtn" class="cp_subbtn pbSubmit">
            <?php echo esc_html( __( $button_label, 'appointment-booking-calendar' ) ); ?>
        </button>
    </div>
</form>