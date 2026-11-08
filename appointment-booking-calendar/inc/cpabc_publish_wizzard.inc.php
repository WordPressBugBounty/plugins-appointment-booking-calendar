<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
    echo 'Direct access not allowed.';
    exit;
}

global $wpdb;
$nonce  = wp_create_nonce( 'abc_update_actions_pwizard' );
$cal_id = isset( $_GET['cal'] ) ? intval( $_GET['cal'] ) : 1;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Publish Appointment Booking Calendar', 'appointment-booking-calendar' ); ?></h1>

    <style type="text/css">
        .ahb-buttons-container {
            margin: 1em 1em 1em 0;
            max-width: 850px;
        }
        .ahb-return-link {
            float: right;
            text-decoration: none;
            font-weight: 500;
        }
        .ahb-section-container {
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            margin: 1em 1em 1.5em 0;
            background: #ffffff;
            max-width: 850px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .ahb-section {
            padding: 24px;
            display: none;
        }
        .ahb-section label {
            font-weight: 600;
        }
        .ahb-section-active {
            display: block;
        }
        .ahb-section select,
        .ahb-section input[type="text"] {
            width: 100%;
            max-width: 450px;
        }
        .cpmvcontainer {
            font-size: 15px !important;
        }
        .ahb-shortcode-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px dashed #8c8f94;
            background-color: #fffacd;
            padding: 12px 15px;
            border-radius: 4px;
            margin: 4px 0 8px 0;
            max-width: 450px;
            box-sizing: border-box;
        }
        .ahb-shortcode-box code {
            font-size: 13.5px;
            font-weight: bold;
            flex-grow: 1;
            background: transparent;
            padding: 0;
            color: #1d2327;
        }
        /* Success Card Styles */
        .ahb-success-card {
            background: #ffffff;
            border: 1px solid #dcdcde;
            border-left: 4px solid #00a32a;
            border-radius: 6px;
            padding: 28px 32px;
            margin: 20px 0;
            max-width: 780px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }
        .ahb-success-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0 0 16px 0;
        }
        .ahb-success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #edfaef;
            color: #00a32a;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .ahb-success-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1d2327;
            line-height: 1.3;
        }
        .ahb-url-box {
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 16px 0 24px 0;
            font-size: 14px;
            color: #3c434a;
            word-break: break-all;
        }
        .ahb-url-box a {
            font-weight: 600;
            color: #2271b1;
            text-decoration: none;
            margin-left: 4px;
        }
        .ahb-url-box a:hover {
            text-decoration: underline;
        }
        .ahb-actions-label {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            margin: 0 0 12px 0;
        }
        .ahb-action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            clear: both;
        }
        .ahb-action-buttons .button {
            float: none !important;
            margin: 0 !important;
            padding: 6px 18px !important;
            height: auto !important;
            line-height: 1.6 !important;
            font-size: 14px !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .ahb-draft-notice {
            background: #fcf9e8;
            border: 1px solid #ebd778;
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 13px;
            color: #50575e;
            line-height: 1.5;
            clear: both;
        }
        .ahb-draft-notice strong {
            color: #1d2327;
        }
        /* Helper Note Card */
        .ahb-note-card {
            max-width: 850px;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            background: #ffffff;
            margin: 1em 1em 1em 0;
        }
        .ahb-note-header {
            padding: 12px 20px;
            border-bottom: 1px solid #e6e6e6;
            background: #f6f7f7;
            font-weight: 600;
            font-size: 14px;
            color: #1d2327;
        }
        .ahb-note-body {
            padding: 16px 20px;
            font-size: 13px;
            line-height: 1.6;
            color: #3c434a;
        }
        .ahb-builder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 14px;
        }
        .ahb-builder-box {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 12px;
            background: #fafafa;
        }
        .ahb-builder-box h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #1d2327;
        }
        .ahb-builder-box img {
            max-width: 100%;
            height: auto;
            border: 1px solid #dcdcde;
            border-radius: 3px;
        }
    </style>

    <div class="ahb-buttons-container">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cpabc_appointments.php' ) ); ?>" class="ahb-return-link">
            &larr; <?php esc_html_e( 'Return to the calendars list', 'appointment-booking-calendar' ); ?>
        </a>
        <div class="clear"></div>
    </div>

    <form method="post" action="?page=cpabc_appointments.php&pwizard=1" name="regForm" id="regForm">
        <input name="cpabc_do_action_loaded" type="hidden" value="wizard" />
        <input name="nonce" type="hidden" value="<?php echo esc_attr( $nonce ); ?>" />

        <?php
        if ( cpabc_get_post_param( 'cpabc_do_action_loaded' ) === 'wizard' ) :
            global $cpabc_postURL;
            $published_cal_id = intval( cpabc_get_post_param( 'cpabc_publish_id' ) );
            ?>
            <div class="ahb-success-card">
                <div class="ahb-success-header">
                    <span class="ahb-success-icon">&#10003;</span>
                    <h2><?php esc_html_e( 'Great! Form successfully published', 'appointment-booking-calendar' ); ?></h2>
                </div>

                <div class="ahb-url-box">
                    <?php esc_html_e( 'The booking form was placed into the page:', 'appointment-booking-calendar' ); ?>
                    <a href="<?php echo esc_url( $cpabc_postURL ); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html( $cpabc_postURL ); ?> &#8599;
                    </a>
                </div>

                <p class="ahb-actions-label"><?php esc_html_e( 'Next steps:', 'appointment-booking-calendar' ); ?></p>

                <div class="ahb-action-buttons">
                    <button class="button button-primary" type="button" onclick="window.open('<?php echo esc_js( $cpabc_postURL ); ?>', '_blank');">
                        <?php esc_html_e( 'View the Published Booking Form', 'appointment-booking-calendar' ); ?>
                    </button>
                    <button class="button button-secondary" type="button" onclick="window.location.href='?page=cpabc_appointments.php&cal=<?php echo esc_js( $published_cal_id ); ?>';">
                        <?php esc_html_e( 'Edit booking form settings and availability', 'appointment-booking-calendar' ); ?>
                    </button>
                </div>

                <div class="ahb-draft-notice">
                    <strong><?php esc_html_e( 'Note:', 'appointment-booking-calendar' ); ?></strong>
                    <?php esc_html_e( 'If the calendar was published in a new draft page or post, remember to publish it when ready.', 'appointment-booking-calendar' ); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="ahb-section-container">
                <div class="ahb-section ahb-section-active" data-step="1">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="cpabc_publish_id"><?php esc_html_e( 'Select calendar', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select id="cpabc_publish_id" name="cpabc_publish_id" onchange="ahb_update_shortcode(this.value);">
                                        <?php
                                        $myrows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . CPABC_APPOINTMENTS_CONFIG_TABLE_NAME_NO_PREFIX );
                                        foreach ( $myrows as $item ) {
                                            if ( empty( $cal_id ) ) {
                                                $cal_id = intval( $item->id );
                                            }
                                            echo '<option value="' . esc_attr( $item->id ) . '"' . selected( (int) $item->id, $cal_id, false ) . '>' . esc_html( $item->uname ) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="whereto"><?php esc_html_e( 'Where to publish it?', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select name="whereto" id="whereto" onchange="mvpublish_displayoption(this);">
                                        <option value="0"><?php esc_html_e( 'Into a new page', 'appointment-booking-calendar' ); ?></option>
                                        <option value="1"><?php esc_html_e( 'Into a new post', 'appointment-booking-calendar' ); ?></option>
                                        <option value="2"><?php esc_html_e( 'Into an existing page', 'appointment-booking-calendar' ); ?></option>
                                        <option value="3"><?php esc_html_e( 'Into an existing post', 'appointment-booking-calendar' ); ?></option>
                                        <option value="4" style="color:#999999"><?php esc_html_e( 'Widget in a sidebar, header or footer - upgrade required -', 'appointment-booking-calendar' ); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" id="posttitle_row">
                                <th scope="row">
                                    <label for="posttitle"><?php esc_html_e( 'Page/Post Title', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="posttitle" id="posttitle" value="<?php esc_attr_e( 'Booking Form', 'appointment-booking-calendar' ); ?>" />
                                </td>
                            </tr>

                            <tr valign="top" id="publishstatus_row">
                                <th scope="row">
                                    <label for="publishstatus"><?php esc_html_e( 'Initial Status', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select name="publishstatus" id="publishstatus">
                                        <option value="draft"><?php esc_html_e( 'Draft (Preview first)', 'appointment-booking-calendar' ); ?></option>
                                        <option value="publish"><?php esc_html_e( 'Published immediately', 'appointment-booking-calendar' ); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" id="select_page_row" style="display:none">
                                <th scope="row">
                                    <label for="publishpage"><?php esc_html_e( 'Select existing page', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    wp_dropdown_pages(
                                        array(
                                            'name'              => 'publishpage',
                                            'id'                => 'publishpage',
                                            'show_option_none'  => esc_html__( '— Select a page —', 'appointment-booking-calendar' ),
                                            'option_none_value' => '0',
                                        )
                                    );
                                    ?>
                                </td>
                            </tr>

                            <tr valign="top" id="select_post_row" style="display:none">
                                <th scope="row">
                                    <label for="publishpost"><?php esc_html_e( 'Select existing post', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <select name="publishpost" id="publishpost">
                                        <?php
                                        $posts_list = get_posts(
                                            array(
                                                'numberposts' => 100,
                                                'post_status' => array( 'publish', 'draft' ),
                                            )
                                        );
                                        foreach ( $posts_list as $post_item ) {
                                            echo '<option value="' . esc_attr( $post_item->ID ) . '">' . esc_html( $post_item->post_title ) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label><?php esc_html_e( 'Quick Shortcode', 'appointment-booking-calendar' ); ?></label>
                                </th>
                                <td>
                                    <div class="ahb-shortcode-box">
                                        <code id="ahb_shortcode_text">[CPABC_APPOINTMENT_CALENDAR calendar="<?php echo intval( $cal_id ); ?>"]</code>
                                        <button type="button" class="button button-small" onclick="ahb_copy_shortcode(this);">
                                            <?php esc_html_e( 'Copy', 'appointment-booking-calendar' ); ?>
                                        </button>
                                    </div>
                                    <p class="description">
                                        <?php esc_html_e( 'You can also paste this shortcode directly into any page, post, or builder (Gutenberg / Elementor).', 'appointment-booking-calendar' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <hr />

                    <div class="ahb-buttons-container" style="margin-bottom:0;">
                        <input type="submit" id="subbtnnow" value="<?php esc_attr_e( 'Publish Calendar', 'appointment-booking-calendar' ); ?>" class="button button-primary" style="float:right;" />
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div id="metabox_basic_settings" class="ahb-note-card">
                <div class="ahb-note-header">
                    <span><?php esc_html_e( 'Note: Other Ways to Publish (Gutenberg, Elementor & Classic Editor)', 'appointment-booking-calendar' ); ?></span>
                </div>
                <div class="ahb-note-body">
                    <p style="margin-top:0;">
                        <?php esc_html_e( 'You can also publish the form in a post/page, use the dedicated icon', 'appointment-booking-calendar' ); ?>
                        <img hspace="5" style="vertical-align:middle;" src="<?php echo esc_url( plugins_url( '../images/cpabc_apps.gif', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Insert Appointment Booking Calendar', 'appointment-booking-calendar' ); ?>" />
                        <?php esc_html_e( 'which has been added to your Upload/Insert Menu, just below the title of your Post/Page or under the "+" icon if using the Gutenberg editor.', 'appointment-booking-calendar' ); ?>
                    </p>

                    <div class="ahb-builder-grid">
                        <div class="ahb-builder-box">
                            <h4><?php esc_html_e( 'Using New WordPress Editor (Gutenberg)?', 'appointment-booking-calendar' ); ?></h4>
                            <img src="<?php echo esc_url( plugins_url( '../controllers/help/gutemberg.png', __FILE__ ) ); ?>" alt="Gutenberg Block" />
                        </div>

                        <?php if ( defined( 'ELEMENTOR_PATH' ) ) : ?>
                            <div class="ahb-builder-box">
                                <h4><?php esc_html_e( 'Using Elementor?', 'appointment-booking-calendar' ); ?></h4>
                                <img src="<?php echo esc_url( plugins_url( '../controllers/help/elementor.png', __FILE__ ) ); ?>" alt="Elementor Widget" />
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
function ahb_update_shortcode(calId) {
    var codeEl = document.getElementById('ahb_shortcode_text');
    if (codeEl) {
        codeEl.textContent = '[CPABC_APPOINTMENT_CALENDAR calendar="' + parseInt(calId, 10) + '"]';
    }
}

function ahb_copy_shortcode(btn) {
    var codeEl = document.getElementById('ahb_shortcode_text');
    if (!codeEl) return;

    var text = (codeEl.textContent || codeEl.innerText).trim();

    function showCopiedFeedback() {
        var originalText = btn.getAttribute('data-original') || btn.textContent;
        btn.setAttribute('data-original', originalText);
        btn.textContent = '✓ <?php echo esc_js( __( 'Copied!', 'appointment-booking-calendar' ) ); ?>';
        setTimeout(function() {
            btn.textContent = originalText;
        }, 2000);
    }

    // 1. Modern clipboard API (HTTPS / localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(showCopiedFeedback).catch(function() {
            ahb_fallback_copy(text, showCopiedFeedback);
        });
    } else {
        // 2. Fallback for HTTP and older browsers
        ahb_fallback_copy(text, showCopiedFeedback);
    }
}

function ahb_fallback_copy(text, callback) {
    var tempTextArea = document.createElement('textarea');
    tempTextArea.value = text;
    tempTextArea.style.position = 'fixed';
    tempTextArea.style.top = '0';
    tempTextArea.style.left = '-9999px';
    tempTextArea.setAttribute('readonly', '');

    document.body.appendChild(tempTextArea);
    tempTextArea.focus();
    tempTextArea.select();

    try {
        var successful = document.execCommand('copy');
        if (successful && typeof callback === 'function') {
            callback();
        }
    } catch (err) {
        console.error('Could not copy shortcode:', err);
    }

    document.body.removeChild(tempTextArea);
}

function mvpublish_displayoption(sel) {
    var val = sel.value;
    document.getElementById("posttitle_row").style.display = (val === '0' || val === '1') ? '' : 'none';
    document.getElementById("publishstatus_row").style.display = (val === '0' || val === '1') ? '' : 'none';
    document.getElementById("select_page_row").style.display = (val === '2') ? '' : 'none';
    document.getElementById("select_post_row").style.display = (val === '3') ? '' : 'none';

    if (val === '4') {
        alert('<?php echo esc_js( __( 'Widget option available only in commercial versions. Upgrade required for this option.', 'appointment-booking-calendar' ) ); ?>');
        sel.value = '0';
        mvpublish_displayoption(sel);
    }
}
</script>