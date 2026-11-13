<?php

if ( ! is_admin() ) {
    echo 'Direct access not allowed.';
    exit;
}

global $wpdb;

$cpid     = 'CP_ABC';
$plugslug = 'cpabc_appointments.php';

if ( isset( $_GET['item'] ) && 'js' === $_GET['item'] ) {
    $saved_contents = base64_decode( get_option( $cpid . '_JS', '' ) );
} elseif ( isset( $_GET['item'] ) && 'css' === $_GET['item'] ) {
    $saved_contents = base64_decode( get_option( $cpid . '_CSS', '' ) );
}
?>
<script>
jQuery(function() {
    <?php
    if ( function_exists( 'wp_enqueue_code_editor' ) ) {
        $settings_js  = wp_enqueue_code_editor( array( 'type' => 'application/javascript' ) );
        $settings_css = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );

        if ( ! ( false === $settings_js && false === $settings_css ) ) {
            if ( isset( $_GET['item'] ) && 'js' === $_GET['item'] ) {
                print sprintf( 'if (document.getElementById("editionarea")) {wp.codeEditor.initialize( "editionarea", %s );}', wp_json_encode( $settings_js ) );
            } else {
                print sprintf( 'if (document.getElementById("editionarea")) {wp.codeEditor.initialize( "editionarea", %s );}', wp_json_encode( $settings_css ) );
            }
        }
    }
    ?>
});

function cpabc_copySampleCss(btnEl) {
    var codeBlock = btnEl.closest('.cpabc-sample-item').querySelector('.cpabc-sample-code');
    if (!codeBlock) return;
    var text = (codeBlock.textContent || codeBlock.innerText).trim();
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
    var label = btnEl.querySelector('.cpabc-copy-label');
    if (label) {
        var orig = label.textContent;
        label.textContent = '<?php echo esc_js( __( 'Copied!', 'appointment-booking-calendar' ) ); ?>';
        setTimeout(function() {
            label.textContent = orig;
        }, 1500);
    }
}
</script>

<style>
    .cpabc-admin-wrap {
        max-width: 1080px;
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
    .cpabc-sample-item {
        border: 1px solid #dcdcde;
        border-radius: 6px;
        margin-bottom: 18px;
        overflow: hidden;
    }
    .cpabc-sample-item:last-child {
        margin-bottom: 0;
    }
    .cpabc-sample-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: #f6f7f7;
        border-bottom: 1px solid #dcdcde;
        font-weight: 600;
        font-size: 13.5px;
        color: #1d2327;
    }
    .cpabc-sample-code {
        background: #fcfcfc;
        border-left: 3px solid #2271b1;
        color: #2c3338;
        font-family: Consolas, Monaco, monospace;
        font-size: 13px;
        line-height: 1.6;
        padding: 14px 18px;
        margin: 0;
        overflow-x: auto;
        white-space: pre-wrap;
    }
</style>

<div class="wrap cpabc-admin-wrap">

    <div class="cpabc-header">
        <h1>
            <span class="dashicons dashicons-art"></span>
            <?php esc_html_e( 'Customization / Edit Page', 'appointment-booking-calendar' ); ?>
        </h1>

        <div class="cpabc-header-actions">
            <a href="https://abc.dwbooster.com/faq?page=faq#design" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-book"></span>
                <span><?php esc_html_e( 'Design FAQ', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="https://abc.dwbooster.com/contact-us" target="_blank" rel="noopener noreferrer" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-sos"></span>
                <span><?php esc_html_e( 'Support', 'appointment-booking-calendar' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugslug ) ); ?>" class="button button-secondary cpabc-btn-icon">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <span><?php esc_html_e( 'Return to the calendars list', 'appointment-booking-calendar' ); ?></span>
            </a>
        </div>
    </div>

    <?php if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST[ $cpid . '_post_edition' ] ) ) : ?>
        <div id="setting-error-settings_updated" class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e( 'Settings saved.', 'appointment-booking-calendar' ); ?></strong></p>
        </div>
    <?php endif; ?>

    <div class="cpabc-info-banner">
        <span class="dashicons dashicons-info-outline"></span>
        <span>
            <?php esc_html_e( 'Note: This section has been modified to improve security. Please add your custom CSS in your theme (Appearance > Customize > Additional CSS).', 'appointment-booking-calendar' ); ?>
            <a href="https://abc.dwbooster.com/contact-us" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact us for support and assistance', 'appointment-booking-calendar' ); ?></a>.
        </span>
    </div>

    <?php if ( isset( $_GET['item'] ) && 'css' === $_GET['item'] ) : ?>
        <div class="cpabc-card">
            <div class="cpabc-card-header">
                <h2>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e( 'Sample Styles', 'appointment-booking-calendar' ); ?>
                </h2>
            </div>
            <div class="cpabc-card-body">

                <div class="cpabc-sample-item">
                    <div class="cpabc-sample-header">
                        <span><?php esc_html_e( 'Center the calendar in the page:', 'appointment-booking-calendar' ); ?></span>
                        <button type="button" class="button button-small cpabc-btn-icon" onclick="cpabc_copySampleCss(this);">
                            <span class="dashicons dashicons-clipboard"></span>
                            <span class="cpabc-copy-label"><?php esc_html_e( 'Copy CSS', 'appointment-booking-calendar' ); ?></span>
                        </button>
                    </div>
                    <pre class="cpabc-sample-code">.appContainer { text-align: center; }
.appContainer2 { margin-left: auto; margin-right: auto; width: 200px; }</pre>
                </div>

                <div class="cpabc-sample-item">
                    <div class="cpabc-sample-header">
                        <span><?php esc_html_e( "Change the calendar's width and height:", 'appointment-booking-calendar' ); ?></span>
                        <button type="button" class="button button-small cpabc-btn-icon" onclick="cpabc_copySampleCss(this);">
                            <span class="dashicons dashicons-clipboard"></span>
                            <span class="cpabc-copy-label"><?php esc_html_e( 'Copy CSS', 'appointment-booking-calendar' ); ?></span>
                        </button>
                    </div>
                    <pre class="cpabc-sample-code">.yui-calendar td.calcell, #cp_abcform_pform .yui-calendar td.calcell {
    padding-top: 10px;
    padding-bottom: 10px;
    border: 1px solid #E0E0E0;
    text-align: center;
    vertical-align: top;
}</pre>
                </div>

                <div class="cpabc-sample-item">
                    <div class="cpabc-sample-header">
                        <span><?php esc_html_e( 'Change the background color of the selected date:', 'appointment-booking-calendar' ); ?></span>
                        <button type="button" class="button button-small cpabc-btn-icon" onclick="cpabc_copySampleCss(this);">
                            <span class="dashicons dashicons-clipboard"></span>
                            <span class="cpabc-copy-label"><?php esc_html_e( 'Copy CSS', 'appointment-booking-calendar' ); ?></span>
                        </button>
                    </div>
                    <pre class="cpabc-sample-code">.yui-calendar td.calcell.reserveddate { background-color: #B6EA59; }</pre>
                </div>

                <div class="cpabc-sample-item">
                    <div class="cpabc-sample-header">
                        <span><?php esc_html_e( 'Make the calendar 100% width:', 'appointment-booking-calendar' ); ?></span>
                        <button type="button" class="button button-small cpabc-btn-icon" onclick="cpabc_copySampleCss(this);">
                            <span class="dashicons dashicons-clipboard"></span>
                            <span class="cpabc-copy-label"><?php esc_html_e( 'Copy CSS', 'appointment-booking-calendar' ); ?></span>
                        </button>
                    </div>
                    <pre class="cpabc-sample-code">.yui-calendar td.calcell, #cp_abcform_pform .yui-calendar td.calcell {
    border: 1px solid #E0E0E0;
    text-align: center;
    vertical-align: top;
}
.yui-calendar .calheader, #cp_abcform_pform .yui-calendar .calheader {
    width: 100%;
}
.yui-calendar, #cp_abcform_pform .yui-calendar {
    width: 100%;
    table-layout: fixed;
}</pre>
                </div>

                <div class="cpabc-sample-item">
                    <div class="cpabc-sample-header">
                        <span><?php esc_html_e( 'Other styles:', 'appointment-booking-calendar' ); ?></span>
                    </div>
                    <div style="padding: 14px 18px; font-size: 13.5px;">
                        <?php esc_html_e( 'For other styles check the design section in the FAQ:', 'appointment-booking-calendar' ); ?>
                        <a href="https://abc.dwbooster.com/faq?page=faq#design" target="_blank" rel="noopener noreferrer">https://abc.dwbooster.com/faq?page=faq#design</a>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

</div>