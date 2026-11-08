<?php

if ( !is_admin() )
{
    echo 'Direct access not allowed.';
    exit;
}

if (!defined('CP_CALENDAR_ID'))
    define ('CP_CALENDAR_ID', 1);

global $wpdb;

$current_user = wp_get_current_user();

$format = cpabc_appointments_getDateFormat();

$message = "";

$records_per_page = 50;                                                                                  

function cpabc_bklist_verify_nonce() {
    if (isset($_GET['rsave']) && $_GET['rsave'] != '')
        $nonce = sanitize_text_field($_GET['rsave']);
    elseif ( isset( $_POST['rsave'] ) )
        $nonce = sanitize_text_field( wp_unslash( $_POST['rsave'] ) );
    else
        $nonce = '';
    $verify_nonce = wp_verify_nonce( $nonce, 'uname_abc_bklist');
    if (!$verify_nonce)
    {
        echo 'Error: Form cannot be authenticated (nonce failed). Please contact our <a href="https://abc.dwbooster.com/contact-us">support service</a> for verification and solution. Thank you.';
        exit;
    } 
}

if (isset($_GET['delmark']) && $_GET['delmark'] != '')
{
    cpabc_bklist_verify_nonce();
    for ($i=0; $i<=$records_per_page; $i++)
    if (isset($_GET['c'.$i]) && $_GET['c'.$i] != '')   
        $wpdb->query( $wpdb->prepare('DELETE FROM `'.CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME.'` WHERE id=%d', $_GET['c'.$i])  );       
    $message = "Marked items deleted";
}
else if (isset($_GET['ld']) && $_GET['ld'] != '')
{
    cpabc_bklist_verify_nonce();
    $wpdb->query( $wpdb->prepare('DELETE FROM `'.CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME.'` WHERE id=%d', $_GET['ld']) );       
    $message = "Item deleted";
} 
else if (isset($_GET['del']) && $_GET['del'] == 'all')
{        
    cpabc_bklist_verify_nonce();
    $wpdb->query( $wpdb->prepare( 'DELETE FROM `'.CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME.'` WHERE appointment_calendar_id=%d', CP_CALENDAR_ID ) );           
    $message = "All items deleted";
}
else if (isset($_GET['paid']) && intval($_GET['paid']))
{
	cpabc_bklist_verify_nonce();
    $item = $wpdb->get_row("SELECT * FROM ".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME." WHERE id=".intval($_GET['paid']));
    $itemdetails = $wpdb->get_row("SELECT * FROM ".CPABC_APPOINTMENTS_TABLE_NAME." WHERE id=".intval($item->reference));    
    $data = unserialize($itemdetails->buffered_date);
    if (intval($_GET['paidac']))
    {
        $data["payment_type"] = 'Manually updated';
    }
    else
    {
        unset($data["payment_type"]);
        unset($data["txnid"]);
    }
    //print_r($data);
    do_action( 'cpabc_update_paid_status', intval($item->reference), (intval($_GET['paidac'])?true:false) );
    $wpdb->query("UPDATE ".CPABC_APPOINTMENTS_TABLE_NAME." set buffered_date='".esc_sql(serialize($data))."' WHERE id=".intval($item->reference));   
    $message = "Paid status updated.";
}
else if (isset($_GET['cancel']) && $_GET['cancel'] != '')
{    
    cpabc_bklist_verify_nonce();
    $cancelc = intval($_GET['cancel']);
    $wpdb->query("UPDATE `".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME."` SET who_cancelled='".esc_sql($current_user->ID)."',is_cancelled='1',cancelled_reason='".esc_sql($_GET["reason"])."' WHERE id=".intval($cancelc));
    $message = "Item cancelled";
    // send email to customer         
    $item = $wpdb->get_row("SELECT * FROM ".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME." WHERE id=".intval($cancelc)); 
    
    $app_source = $wpdb->get_row("SELECT * FROM ".CPABC_APPOINTMENTS_TABLE_NAME." WHERE id=".intval($item->reference)); 
    $params = unserialize($app_source->buffered_date);
    if ('html' == cpabc_get_option('cncustomer_emailformat')) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
    $email_content = str_replace('%INFORMATION%',str_replace('<br />',"\n",$item->description_customer), cpabc_get_option('cemail_notification_to_customer') );   
    $params['ITEMNUMBER'] = $cancelc;
    foreach ($params as $itemr => $value)
    {
        $email_content = str_replace('<%'.$itemr.'%>',(is_array($value)?(implode(", ",$value)):($value)),$email_content);
        $email_content = str_replace('%'.$itemr.'%',(is_array($value)?(implode(", ",$value)):($value)),$email_content);
    }
    $email_content = str_replace("%CALENDAR%", cpabc_get_option('uname'), $email_content);
    $email_content = str_replace("%cancelreason%", sanitize_text_field($_GET["reason"]), $email_content);
    
    //wp_mail($item->title, cpabc_get_option('cemail_subject_notification_to_customer'), $email_content,
    //        "From: \"".cpabc_get_option('notification_from_email')."\" <".cpabc_get_option('notification_from_email').">\r\n".
    //        $content_type.
    //        "X-Mailer: PHP/" . phpversion());    
}
else if (isset($_GET['nocancel']) && $_GET['nocancel'] != '')
{    
    cpabc_bklist_verify_nonce();
    $wpdb->query("UPDATE `".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME."` SET who_edited='".esc_sql($current_user->ID)."',is_cancelled='0' WHERE id=".intval($_GET['nocancel']));           
    $message = "Item un-cancelled";
}
else if (isset($_GET['resend']) && $_GET['resend'] != '')
{    
    cpabc_bklist_verify_nonce();
    $events = $wpdb->get_results( "SELECT * FROM ".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME." WHERE id=".intval($_GET['resend']) );
    $events = $wpdb->get_results( "SELECT * FROM ".CPABC_APPOINTMENTS_TABLE_NAME." WHERE id=".intval($events[0]->reference) );    
    define('CPABC_IS_RESEND', true);
    cpabc_process_ready_to_go_appointment($events[0]->id,'',true);
    $message = "Item re-sent";
}



$mycalendarrows = $wpdb->get_results( 'SELECT * FROM '.CPABC_APPOINTMENTS_CONFIG_TABLE_NAME .' WHERE `'.CPABC_TDEAPP_CONFIG_ID.'`='.CP_CALENDAR_ID);

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cpabc_appointments_post_options'] ) )
    echo "<div id='setting-error-settings_updated' class='updated settings-error'> <p><strong>Settings saved.</strong></p></div>";

$current_user = wp_get_current_user();

if ( cpabc_appointment_is_administrator() || ( isset( $mycalendarrows[0] ) && $mycalendarrows[0]->conwer == $current_user->ID ) ) {

$current_page = intval(cpabc_get_get_param("p"));
if (!$current_page) $current_page = 1;

$cond = '';
if (cpabc_get_get_param("search") != '') 
{
    $search_text = sanitize_text_field($_GET["search"]);
    $cond .= " AND (title like '%".esc_sql($search_text)."%' OR description LIKE '%".esc_sql($search_text)."%')";
}
if (cpabc_get_get_param("dfrom") != '') $cond .= " AND (datatime >= '".esc_sql(sanitize_text_field($_GET["dfrom"]))."')";
if (cpabc_get_get_param("dto") != '') $cond .= " AND (datatime <= '".esc_sql(sanitize_text_field($_GET["dto"]))." 23:59:59')";


if (!empty($_GET["added_by"]) && $_GET["added_by"] != '') $cond .= " AND (who_added >= '".esc_sql(sanitize_text_field($_GET["added_by"]))."')";
if (!empty($_GET["edited_by"]) && $_GET["edited_by"] != '') $cond .= " AND (who_edited >= '".esc_sql(sanitize_text_field($_GET["edited_by"]))."')";
if (!empty($_GET["cancelled_by"]) && $_GET["cancelled_by"] != '') $cond .= " AND (is_cancelled='1' AND who_cancelled >= '".esc_sql(sanitize_text_field($_GET["cancelled_by"]))."')";
if (!empty($_GET["cstatus"]) && $_GET["cstatus"] == 'cancelled') $cond .= " AND (is_cancelled='1')";
if (!empty($_GET["cstatus"]) && $_GET["cstatus"] == 'approved') $cond .= " AND (is_cancelled<>'1')";


$orderby = empty($_GET["orderby"]) ? 'datatime DESC' : sanitize_text_field($_GET["orderby"]);
if ($orderby != 'id DESC' && $orderby != 'id ASC' && $orderby != 'datatime DESC' && $orderby != 'datatime ASC')
	$orderby = 'datatime DESC';

$orderby_parts = explode( ' ', $orderby );
$orderby_column = ( 'id' === $orderby_parts[0] ) ? CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME . '.id' : CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME . '.datatime';
$orderby_direction = ( isset( $orderby_parts[1] ) && 'ASC' === $orderby_parts[1] ) ? 'ASC' : 'DESC';
$events = $wpdb->get_results( "SELECT ".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME.".*,".CPABC_APPOINTMENTS_TABLE_NAME.".buffered_date,".CPABC_APPOINTMENTS_TABLE_NAME.".time FROM ".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME." LEFT JOIN  ".CPABC_APPOINTMENTS_TABLE_NAME." ON  ".CPABC_APPOINTMENTS_TABLE_NAME.".id=".CPABC_APPOINTMENTS_CALENDARS_TABLE_NAME.".reference WHERE appointment_calendar_id=".intval(CP_CALENDAR_ID).$cond." ORDER BY ".$orderby_column." ".$orderby_direction );
$total_items = count( $events );
$total_pages = (int) ceil( $total_items / $records_per_page );
if ( $total_pages > 0 && $current_page > $total_pages ) {
    $current_page = $total_pages;
}

$users_arr = array();
$users_arr['id-1'] = 'CUSTOMER';
$users = $wpdb->get_results( "SELECT user_login,ID FROM ".$wpdb->users." ORDER BY ID DESC" );                                                                     
foreach ($users as $user)
    $users_arr["id".$user->ID] = $user;
	
if ($message) echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>".esc_html($message)."</strong></p></div>";

$nonce_un = wp_create_nonce( 'uname_abc_bklist' );

$pagination_args = array(
    'page'         => 'cpabc_appointments.php',
    'cal'          => CP_CALENDAR_ID,
    'list'         => 1,
    'dfrom'        => sanitize_text_field( cpabc_get_get_param( 'dfrom' ) ),
    'dto'          => sanitize_text_field( cpabc_get_get_param( 'dto' ) ),
    'search'       => sanitize_text_field( cpabc_get_get_param( 'search' ) ),
    'added_by'     => sanitize_text_field( cpabc_get_get_param( 'added_by' ) ),
    'edited_by'    => sanitize_text_field( cpabc_get_get_param( 'edited_by' ) ),
    'cancelled_by' => sanitize_text_field( cpabc_get_get_param( 'cancelled_by' ) ),
    'cstatus'      => sanitize_text_field( cpabc_get_get_param( 'cstatus' ) ),
    'orderby'      => $orderby,
);
$base_url = add_query_arg( $pagination_args, admin_url( 'admin.php' ) );

?>
<script type="text/javascript">
    function cp_deleteMessageItem(id) {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to delete this item?', 'appointment-booking-calendar' ) ); ?>')) {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&ld=' + encodeURIComponent(id) + '&r=' + Math.random();
        }
    }

    function do_dexapp_deleteall() {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to delete ALL bookings for this calendar?', 'appointment-booking-calendar' ) ); ?>') &&
            confirm('<?php echo esc_js( __( 'Please note that this action cannot be undone. Are you sure you want to delete ALL bookings?', 'appointment-booking-calendar' ) ); ?>')) {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&del=all&r=' + Math.random();
        }
    }

    function cp_paidMessageItem(id, ac) {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to change this paid status?', 'appointment-booking-calendar' ) ); ?>')) {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&paid=' + encodeURIComponent(id) + '&paidac=' + encodeURIComponent(ac) + '&r=' + Math.random();
        }
    }

    function cp_editItem(id, cal) {
        document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=' + encodeURIComponent(cal) + '&edit=' + encodeURIComponent(id) + '&r=' + Math.random();
    }

    function cp_uncancelItem(id) {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to un-cancel this item?', 'appointment-booking-calendar' ) ); ?>')) {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&nocancel=' + encodeURIComponent(id) + '&r=' + Math.random();
        }
    }

    function cp_resendItem(id) {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to resend this email?', 'appointment-booking-calendar' ) ); ?>')) {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&resend=' + encodeURIComponent(id) + '&r=' + Math.random();
        }
    }

    function cp_cancelItem(id) {
        var reason = prompt('<?php echo esc_js( __( 'Please enter cancellation reason:', 'appointment-booking-calendar' ) ); ?>');
        if (reason !== null && reason.trim() !== '') {
            document.location = 'admin.php?page=cpabc_appointments.php&rsave=<?php echo esc_js( $nonce_un ); ?>&cal=<?php echo intval( CP_CALENDAR_ID ); ?>&list=1&cancel=' + encodeURIComponent(id) + '&reason=' + encodeURIComponent(reason) + '&r=' + Math.random();
        }
    }

    function cp_toggleDesc(id, btn) {
        var $content = jQuery('#cpabc_desc_' + id);
        if ($content.hasClass('cpabc-desc-collapsed')) {
            $content.removeClass('cpabc-desc-collapsed');
            jQuery(btn).text('<?php echo esc_js( __( 'Show less', 'appointment-booking-calendar' ) ); ?>');
        } else {
            $content.addClass('cpabc-desc-collapsed');
            jQuery(btn).text('<?php echo esc_js( __( 'Show more', 'appointment-booking-calendar' ) ); ?>');
        }
    }

    function cp_markall() {
        var control = document.getElementById('cpcontrolck');
        if (!control) return;
        jQuery('#dex_table_form input[type="checkbox"][name^="c"]').prop('checked', control.checked);
    }

    function do_dexapp_print() {
        var printWindow = window.open('', '_blank');
        if (!printWindow) return;
        printWindow.document.write('<!doctype html><html><head><title><?php echo esc_js( __( 'Bookings List', 'appointment-booking-calendar' ) ); ?></title><style>body{font-family:sans-serif;font-size:12px} .ahbnoprint,.cpnopr,.check-column{display:none!important} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top} th{background:#f4f4f4}.cpabc-desc-content{max-height:none!important;display:block!important;overflow:visible!important}</style></head><body>' + document.getElementById('cpabc_printable_contents').innerHTML + '</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function do_dexapp_deletemarked() {
        if (confirm('<?php echo esc_js( __( 'Are you sure that you want to delete the marked items?', 'appointment-booking-calendar' ) ); ?>')) {
            document.dex_table_form.submit();
        }
    }

    var $j = jQuery.noConflict();
    $j(function() {
        $j('#dfrom, #dto').datepicker({ dateFormat: 'yy-mm-dd' });

        // Also collapse descriptions that wrap beyond 15 visual lines without explicit line breaks.
        $j('.cpabc-desc-content').each(function() {
            var lineHeight = parseFloat($j(this).css('line-height')) || 18.2;
            if (!$j(this).hasClass('cpabc-desc-collapsed') && this.scrollHeight > Math.ceil(lineHeight * 15.5)) {
                var id = this.id.replace('cpabc_desc_', '');
                $j(this).addClass('cpabc-desc-collapsed');
                $j(this).after('<a href="javascript:void(0);" class="cpabc-show-more-btn cpnopr" onclick="cp_toggleDesc(' + id + ', this);"><?php echo esc_js( __( 'Show more', 'appointment-booking-calendar' ) ); ?></a>');
            }
        });
    });
</script>

<style>
    .cpabc-orders-list tr.cpabc_cancelled td,
    .cpabc-orders-list tr.cpabc_cancelled th {
        color: #d63638 !important;
        background-color: #fcf0f1;
    }
    .cpabc-orders-list th.cpabc-col-compact,
    .cpabc-orders-list td.cpabc-col-compact {
        padding-left: 3px !important;
        padding-right: 3px !important;
        text-align: center;
        font-size: 12px;
        white-space: nowrap;
    }
    .cpabc-orders-list td.cpabc-col-compact .dashicons {
        font-size: 17px;
        width: 17px;
        height: 17px;
        vertical-align: middle;
    }
    .cpabc-desc-content {
        line-height: 1.4em;
        overflow-wrap: anywhere;
    }
    .cpabc-desc-content.cpabc-desc-collapsed {
        display: -webkit-box;
        -webkit-line-clamp: 15;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-height: 21em;
    }
    .cpabc-show-more-btn {
        display: inline-block;
        margin-top: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }
    .cpabc-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
    .cpabc-filter-bar input[type="search"] { min-width: 180px; }
    .cpabc-filter-bar input[type="text"] { max-width: 150px; }
    .ahb-wp-pagination {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        margin-top: 3px;
        color: #3c434a;
        font-size: 13px;
    }
    .ahb-wp-pagination .displaying-num { margin-right: 7px; }
    .ahb-wp-pagination .pagination-links { display: flex; align-items: center; }
    .ahb-wp-pagination .pagination-links .button {
        margin: 0 2px;
        padding: 0 4px;
        min-width: 30px;
        min-height: 30px;
        line-height: 28px;
        text-align: center;
        display: inline-block;
        box-sizing: border-box;
        border: 1px solid #8c8f94;
        border-radius: 3px;
        background: #f6f7f7;
        color: #2271b1;
        text-decoration: none;
        font-weight: normal;
    }
    .ahb-wp-pagination .pagination-links .button.disabled {
        border-color: #dcdcde;
        color: #a7aaad;
        background: #f6f7f7;
        cursor: default;
    }
    .ahb-wp-pagination .pagination-links .button:hover:not(.disabled) {
        background: #f0f0f1;
        border-color: #0a4b78;
        color: #0a4b78;
    }
    .ahb-wp-pagination .paging-input { margin: 0 4px; display: inline-flex; align-items: center; }
    .ahb-wp-pagination .paging-input input {
        width: 40px !important;
        margin: 0 4px;
        text-align: center;
        border: 1px solid #8c8f94;
        border-radius: 3px;
        line-height: 2;
        min-height: 30px;
        padding: 0 4px;
        color: #2c3338;
    }
    .cpabc-booking-meta {
        margin-top: 8px;
        padding-top: 5px;
        border-top: 1px dotted #ccc;
        color: #777;
        font-size: 11px;
        line-height: 1.5;
    }
    .cpabc-payment-meta { margin-top: 7px; padding-top: 6px; border-top: 1px solid #e2e4e7; }
    .abcpaid { font-weight: 600; color: #008a20; }
    .abcunpaid { color: #8a2424; }
    .abcsubdate { color: #646970; }
    .cpabc-orders-list .row-actions { margin-top: 2px; }
    .cpabc-orders-list td { overflow-wrap: anywhere; }
    @media screen and (max-width: 782px) {
        .cpabc-filter-bar { align-items: stretch; }
        .cpabc-filter-bar select,
        .cpabc-filter-bar input[type="search"],
        .cpabc-filter-bar input[type="text"] { max-width: none; width: 100%; }
        .ahb-wp-pagination { justify-content: flex-start; margin-bottom: 10px; }
    }
</style>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Appointment Booking Calendar - Bookings List', 'appointment-booking-calendar' ); ?>
        <?php if ( isset( $mycalendarrows[0] ) ) : ?> - <?php echo esc_html( $mycalendarrows[0]->uname ); ?><?php endif; ?>
    </h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cpabc_appointments.php' ) ); ?>" class="page-title-action">&larr; <?php esc_html_e( 'Back to items list', 'appointment-booking-calendar' ); ?></a>
    <hr class="wp-header-end">

    <?php if ( isset( $mycalendarrows[0] ) ) : ?>
        <p class="description" style="margin:8px 0 14px;">
            <?php esc_html_e( 'This booking list applies only to:', 'appointment-booking-calendar' ); ?>
            <strong><?php echo esc_html( $mycalendarrows[0]->uname ); ?></strong>
        </p>
    <?php endif; ?>

    <form action="admin.php" method="get" style="margin-bottom:10px;">
        <input type="hidden" name="rsave" value="<?php echo esc_attr( $nonce_un ); ?>" />
        <input type="hidden" name="page" value="cpabc_appointments.php" />
        <input type="hidden" name="cal" value="<?php echo intval( CP_CALENDAR_ID ); ?>" />
        <input type="hidden" name="list" value="1" />

        <div class="alignleft actions cpabc-filter-bar">
            <input autocomplete="off" type="search" name="search" placeholder="<?php esc_attr_e( 'Search for...', 'appointment-booking-calendar' ); ?>" value="<?php echo esc_attr( cpabc_get_get_param( 'search' ) ); ?>" />
            <input autocomplete="off" type="text" id="dfrom" name="dfrom" placeholder="<?php esc_attr_e( 'From', 'appointment-booking-calendar' ); ?>" value="<?php echo esc_attr( cpabc_get_get_param( 'dfrom' ) ); ?>" size="10" />
            <input autocomplete="off" type="text" id="dto" name="dto" placeholder="<?php esc_attr_e( 'To', 'appointment-booking-calendar' ); ?>" value="<?php echo esc_attr( cpabc_get_get_param( 'dto' ) ); ?>" size="10" />

            <?php if ( cpabc_appointment_is_administrator() ) : ?>
                <select name="added_by" aria-label="<?php esc_attr_e( 'Added by', 'appointment-booking-calendar' ); ?>">
                    <option value=""><?php esc_html_e( 'Added by: all', 'appointment-booking-calendar' ); ?></option>
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( cpabc_get_get_param( 'added_by' ), $user->ID ); ?>><?php echo esc_html( $user->user_login ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="edited_by" aria-label="<?php esc_attr_e( 'Edited by', 'appointment-booking-calendar' ); ?>">
                    <option value=""><?php esc_html_e( 'Edited by: all', 'appointment-booking-calendar' ); ?></option>
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( cpabc_get_get_param( 'edited_by' ), $user->ID ); ?>><?php echo esc_html( $user->user_login ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="cancelled_by" aria-label="<?php esc_attr_e( 'Cancelled by', 'appointment-booking-calendar' ); ?>">
                    <option value=""><?php esc_html_e( 'Cancelled by: all', 'appointment-booking-calendar' ); ?></option>
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( cpabc_get_get_param( 'cancelled_by' ), $user->ID ); ?>><?php echo esc_html( $user->user_login ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="cstatus" aria-label="<?php esc_attr_e( 'Booking status', 'appointment-booking-calendar' ); ?>">
                    <option value=""><?php esc_html_e( 'Status: all', 'appointment-booking-calendar' ); ?></option>
                    <option value="approved" <?php selected( cpabc_get_get_param( 'cstatus' ), 'approved' ); ?>><?php esc_html_e( 'Active', 'appointment-booking-calendar' ); ?></option>
                    <option value="cancelled" <?php selected( cpabc_get_get_param( 'cstatus' ), 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'appointment-booking-calendar' ); ?></option>
                </select>
            <?php endif; ?>

            <select name="orderby" aria-label="<?php esc_attr_e( 'Sort bookings', 'appointment-booking-calendar' ); ?>">
                <option value="id DESC" <?php selected( $orderby, 'id DESC' ); ?>><?php esc_html_e( 'Submission time - desc', 'appointment-booking-calendar' ); ?></option>
                <option value="id ASC" <?php selected( $orderby, 'id ASC' ); ?>><?php esc_html_e( 'Submission time - asc', 'appointment-booking-calendar' ); ?></option>
                <option value="datatime DESC" <?php selected( $orderby, 'datatime DESC' ); ?>><?php esc_html_e( 'Appointment time - desc', 'appointment-booking-calendar' ); ?></option>
                <option value="datatime ASC" <?php selected( $orderby, 'datatime ASC' ); ?>><?php esc_html_e( 'Appointment time - asc', 'appointment-booking-calendar' ); ?></option>
            </select>
            <input type="submit" name="ds" value="<?php esc_attr_e( 'Filter', 'appointment-booking-calendar' ); ?>" class="button" />
            <input type="submit" name="cpabc_appointments_csv" value="<?php esc_attr_e( 'Export to CSV', 'appointment-booking-calendar' ); ?>" class="button" />
        </div>
    </form>
    <div class="clear"></div>

    <div class="tablenav top" style="height:auto;padding:6px 0 10px;">
        <div class="tablenav-pages ahb-wp-pagination<?php if ( $total_pages <= 1 ) echo ' one-page'; ?>">
            <span class="displaying-num">
                <?php echo esc_html( sprintf( _n( '%s item', '%s items', $total_items, 'appointment-booking-calendar' ), number_format_i18n( $total_items ) ) ); ?>
            </span>
            <?php if ( $total_pages > 0 ) : ?>
                <span class="pagination-links">
                    <?php if ( $current_page <= 1 ) : ?>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                    <?php else : ?>
                        <a class="first-page button" href="<?php echo esc_url( add_query_arg( 'p', 1, $base_url ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'First page', 'appointment-booking-calendar' ); ?></span><span aria-hidden="true">&laquo;</span></a>
                        <a class="prev-page button" href="<?php echo esc_url( add_query_arg( 'p', $current_page - 1, $base_url ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Previous page', 'appointment-booking-calendar' ); ?></span><span aria-hidden="true">&lsaquo;</span></a>
                    <?php endif; ?>

                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text"><?php esc_html_e( 'Current Page', 'appointment-booking-calendar' ); ?></label>
                        <input class="current-page" id="current-page-selector" type="text" value="<?php echo esc_attr( $current_page ); ?>" size="<?php echo esc_attr( strlen( (string) $total_pages ) ); ?>" aria-describedby="table-paging" onkeydown="if(event.keyCode===13){window.location.href='<?php echo esc_url_raw( $base_url ); ?>&p='+encodeURIComponent(this.value);return false;}" />
                        <span class="tablenav-paging-text"> <?php esc_html_e( 'of', 'appointment-booking-calendar' ); ?> <span class="total-pages"><?php echo esc_html( number_format_i18n( $total_pages ) ); ?></span></span>
                    </span>

                    <?php if ( $current_page >= $total_pages ) : ?>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                    <?php else : ?>
                        <a class="next-page button" href="<?php echo esc_url( add_query_arg( 'p', $current_page + 1, $base_url ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Next page', 'appointment-booking-calendar' ); ?></span><span aria-hidden="true">&rsaquo;</span></a>
                        <a class="last-page button" href="<?php echo esc_url( add_query_arg( 'p', $total_pages, $base_url ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Last page', 'appointment-booking-calendar' ); ?></span><span aria-hidden="true">&raquo;</span></a>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="clear"></div>

    <div id="cpabc_printable_contents">
        <form name="dex_table_form" id="dex_table_form" action="admin.php" method="get">
            <input type="hidden" name="page" value="cpabc_appointments.php" />
            <input type="hidden" name="cal" value="<?php echo intval( CP_CALENDAR_ID ); ?>" />
            <input type="hidden" name="list" value="1" />
            <input type="hidden" name="rsave" value="<?php echo esc_attr( $nonce_un ); ?>" />
            <input type="hidden" name="delmark" value="1" />

            <table class="wp-list-table widefat fixed striped cpabc-orders-list">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column cpnopr">
                            <input type="checkbox" name="cpcontrolck" id="cpcontrolck" value="" onclick="cp_markall();" />
                        </td>
                        <th scope="col" class="manage-column" style="width:65px;"><?php esc_html_e( 'ID#', 'appointment-booking-calendar' ); ?></th>
                        <?php if ( 'id DESC' === $orderby || 'id ASC' === $orderby ) : ?>
                            <th scope="col" class="manage-column" style="width:125px;"><?php esc_html_e( 'Submission Time', 'appointment-booking-calendar' ); ?></th>
                        <?php endif; ?>
                        <th scope="col" class="manage-column" style="width:135px;"><?php esc_html_e( 'Date', 'appointment-booking-calendar' ); ?></th>
                        <th scope="col" class="manage-column" style="width:160px;"><?php esc_html_e( 'Title', 'appointment-booking-calendar' ); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e( 'Description', 'appointment-booking-calendar' ); ?></th>
                        <th scope="col" class="manage-column cpabc-col-compact" style="width:48px;" title="<?php esc_attr_e( 'Quantity', 'appointment-booking-calendar' ); ?>"><?php esc_html_e( 'Qty', 'appointment-booking-calendar' ); ?></th>
                        <th scope="col" class="manage-column cpnopr" style="width:205px;"><?php esc_html_e( 'Options', 'appointment-booking-calendar' ); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php
                    $offset = ( $current_page - 1 ) * $records_per_page;
                    for ( $i = $offset; $i < min( $offset + $records_per_page, $total_items ); $i++ ) :
                        $event = $events[ $i ];
                        $is_cancelled = ( '1' === (string) $event->is_cancelled );
                        $desc_raw = str_replace( array( "\r\n", "\r" ), "\n", (string) $event->description );
                        $desc_lines = explode( "\n", $desc_raw );
                        $is_long_desc = ( count( $desc_lines ) > 15 || strlen( $desc_raw ) > 900 );
                        $params = maybe_unserialize( $event->buffered_date );
                        $params = is_array( $params ) ? $params : array();
                        $is_paid = ( array_key_exists( 'txnid', $params ) || ( isset( $params['payment_type'] ) && ( 'PayPal' !== $params['payment_type'] || isset( $params['txnid'] ) ) ) );
                        ?>
                        <tr class="<?php echo $is_cancelled ? 'cpabc_cancelled ' : ''; ?>author-self status-draft format-default iedit" valign="top">
                            <th scope="row" class="check-column cpnopr">
                                <input type="checkbox" name="c<?php echo intval( $i - $offset ); ?>" value="<?php echo intval( $event->id ); ?>" />
                            </th>
                            <td><?php echo esc_html( $event->reference ); ?></td>
                            <?php if ( 'id DESC' === $orderby || 'id ASC' === $orderby ) : ?>
                                <td><?php echo isset( $event->time ) ? esc_html( date( $format, strtotime( $event->time ) ) ) : ''; ?></td>
                            <?php endif; ?>
                            <td><?php echo esc_html( substr( $event->datatime, 0, 16 ) ); ?></td>
                            <td><?php echo esc_html( $event->title ); ?></td>
                            <td>
                                <div id="cpabc_desc_<?php echo intval( $event->id ); ?>" class="cpabc-desc-content<?php echo $is_long_desc ? ' cpabc-desc-collapsed' : ''; ?>">
                                    <?php echo wp_kses_post( str_replace( "\n", '<br />', $desc_raw ) ); ?>
                                </div>
                                <?php if ( $is_long_desc ) : ?>
                                    <a href="javascript:void(0);" class="cpabc-show-more-btn cpnopr" onclick="cp_toggleDesc(<?php echo intval( $event->id ); ?>, this); return false;"><?php esc_html_e( 'Show more', 'appointment-booking-calendar' ); ?></a>
                                <?php endif; ?>
                            </td>
                            <td class="cpabc-col-compact"><?php echo esc_html( $event->quantity ); ?></td>
                            <td class="cpnopr">
                                <div class="row-actions visible">
                                    <span class="edit"><a href="javascript:void(0);" onclick="cp_editItem(<?php echo intval( $event->id ); ?>, <?php echo intval( $event->appointment_calendar_id ); ?>); return false;"><?php esc_html_e( 'Edit', 'appointment-booking-calendar' ); ?></a> | </span>
                                    <span class="inline">
                                        <?php if ( $is_cancelled ) : ?>
                                            <a href="javascript:void(0);" onclick="cp_uncancelItem(<?php echo intval( $event->id ); ?>); return false;"><?php esc_html_e( 'Un-Cancel', 'appointment-booking-calendar' ); ?></a> |
                                        <?php else : ?>
                                            <a href="javascript:void(0);" onclick="cp_cancelItem(<?php echo intval( $event->id ); ?>); return false;"><?php esc_html_e( 'Cancel', 'appointment-booking-calendar' ); ?></a> |
                                        <?php endif; ?>
                                    </span>
                                    <span class="inline"><a href="javascript:void(0);" onclick="cp_resendItem(<?php echo intval( $event->id ); ?>); return false;"><?php esc_html_e( 'Resend Email', 'appointment-booking-calendar' ); ?></a><?php if ( cpabc_appointment_is_administrator() ) : ?> | <?php endif; ?></span>
                                    <?php if ( cpabc_appointment_is_administrator() ) : ?>
                                        <span class="trash"><a href="javascript:void(0);" class="submitdelete" onclick="cp_deleteMessageItem(<?php echo intval( $event->id ); ?>); return false;"><?php esc_html_e( 'Delete', 'appointment-booking-calendar' ); ?></a></span>
                                    <?php endif; ?>
                                </div>

                                <div class="cpabc-booking-meta">
                                    <?php if ( isset( $users_arr[ 'id' . $event->who_added ] ) && is_object( $users_arr[ 'id' . $event->who_added ] ) ) : ?>
                                        <div><em><?php esc_html_e( 'Added by:', 'appointment-booking-calendar' ); ?> <strong><?php echo esc_html( $users_arr[ 'id' . $event->who_added ]->user_login ); ?></strong></em></div>
                                    <?php endif; ?>
                                    <?php if ( isset( $users_arr[ 'id' . $event->who_edited ] ) && is_object( $users_arr[ 'id' . $event->who_edited ] ) ) : ?>
                                        <div><em><?php esc_html_e( 'Edited by:', 'appointment-booking-calendar' ); ?> <strong><?php echo esc_html( $users_arr[ 'id' . $event->who_edited ]->user_login ); ?></strong></em></div>
                                    <?php endif; ?>
                                    <?php if ( isset( $users_arr[ 'id' . $event->who_cancelled ] ) && $is_cancelled ) : ?>
                                        <div><em><?php esc_html_e( 'Cancelled by:', 'appointment-booking-calendar' ); ?>
                                            <strong><?php echo esc_html( is_object( $users_arr[ 'id' . $event->who_cancelled ] ) ? $users_arr[ 'id' . $event->who_cancelled ]->user_login : $users_arr[ 'id' . $event->who_cancelled ] ); ?></strong>
                                        </em></div>
                                    <?php endif; ?>
                                    <?php if ( '' !== (string) $event->cancelled_reason && $is_cancelled ) : ?>
                                        <div><em><?php esc_html_e( 'Cancelled reason:', 'appointment-booking-calendar' ); ?> <strong><?php echo esc_html( $event->cancelled_reason ); ?></strong></em></div>
                                    <?php endif; ?>
                                    <?php if ( 'id DESC' !== $orderby && 'id ASC' !== $orderby ) : ?>
                                        <div><em><?php esc_html_e( 'Submission date:', 'appointment-booking-calendar' ); ?> <strong><?php echo isset( $event->time ) ? esc_html( date( $format, strtotime( $event->time ) ) ) : ''; ?></strong></em></div>
                                    <?php endif; ?>

                                    <div class="cpabc-payment-meta">
                                        <?php if ( $is_paid ) : ?>
                                            <div class="abcpaid">
                                                <?php esc_html_e( 'Paid.', 'appointment-booking-calendar' ); ?>
                                                <?php if ( isset( $params['payment_type'] ) && ( 'PayPal' !== $params['payment_type'] || isset( $params['txnid'] ) ) ) : ?><?php echo esc_html( $params['payment_type'] ); ?>.<?php endif; ?>
                                                <?php if ( isset( $params['txnid'] ) ) : ?><?php esc_html_e( 'Payment ID', 'appointment-booking-calendar' ); ?>: <?php echo esc_html( $params['txnid'] ? $params['txnid'] : '---' ); ?><?php endif; ?>
                                            </div>
                                            <p style="margin:5px 0 0;"><button type="button" class="button button-small" name="calpaide_<?php echo intval( $event->id ); ?>" onclick="cp_paidMessageItem(<?php echo intval( $event->id ); ?>, 0);"><?php esc_html_e( 'Mark as unpaid', 'appointment-booking-calendar' ); ?></button></p>
                                        <?php else : ?>
                                            <div class="abcunpaid"><?php esc_html_e( 'Payment: not confirmed so far', 'appointment-booking-calendar' ); ?></div>
                                            <p style="margin:5px 0 0;"><button type="button" class="button button-small" name="calpaide_<?php echo intval( $event->id ); ?>" onclick="cp_paidMessageItem(<?php echo intval( $event->id ); ?>, 1); return false;"><?php esc_html_e( 'Mark as paid', 'appointment-booking-calendar' ); ?></button></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    <?php if ( 0 === $total_items ) : ?>
                        <tr><td colspan="<?php echo ( 'id DESC' === $orderby || 'id ASC' === $orderby ) ? 8 : 7; ?>"><?php esc_html_e( 'No bookings found.', 'appointment-booking-calendar' ); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <div class="ahbnoprint tablenav bottom" style="margin-top:15px;">
        <div class="alignleft actions">
            <input class="button" type="button" name="pbutton" value="<?php esc_attr_e( 'Delete marked items', 'appointment-booking-calendar' ); ?>" onclick="do_dexapp_deletemarked();" />
        </div>
        <div class="alignright actions">
            <input class="button" type="button" name="pbutton" value="<?php esc_attr_e( 'Print List', 'appointment-booking-calendar' ); ?>" onclick="do_dexapp_print();" style="margin-right:10px;" />
            <input class="button button-link-delete" type="button" name="pbutton" value="<?php esc_attr_e( 'Delete All Bookings', 'appointment-booking-calendar' ); ?>" onclick="do_dexapp_deleteall();" />
        </div>
        <div class="clear"></div>
    </div>
</div>

<?php } else { ?>
  <br />
  The current user logged in doesn't have enough permissions to edit this calendar. This user can edit only his/her own calendars. Please log in as administrator to get access to all calendars.

<?php } ?>











