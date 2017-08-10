<?php
/**
 * Plugin Name: Interswitch Form
 * Plugin URI: http://myeverlasting.net
 * Description: Interswitch custom payment Form
 * Version: 1.0
 * Author: Babafemi Adigun
 * Date: 6/9/16
 * Time: 12:13 PM
 */
define('WP_USE_THEMES', false);


add_shortcode( 'isw_form' , 'html_form_code' );
register_activation_hook(__FILE__,'iswform_install');
add_action( 'admin_post_confirm_pay', 'prefix_confirm_pay' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'my_plugin_action_links' );
function my_plugin_action_links( $links ) {
    $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=iswform.php') ) .'">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_action('admin_menu', 'iswform_admin_menu');
add_action('admin_init', 'iswform_admin_init');
function iswform_admin_init() {

    // Register settings
    register_setting('iswform_options', 'iswform_options');
}
function iswform_admin_menu() {

    // Add a new submenu under settings
    add_options_page('PLUGIN-NAME', 'PLUGIN-NAME', 'manage_options', 'iswform', 'iswform_options_page');

    // Add a new submenu under tools
    add_management_page('PLUGIN-NAME', 'PLUGIN-NAME', 'manage_options', 'iswform-tool', 'iswform_management_page' );

    // Add top-level menu item at position 3, with a icon
    add_menu_page('Example', 'Example', 'manage_options', 'iswform-example', null, 'dashicons-palmtree', 3);
    // Add a sub menu item
    add_submenu_page('mt-example', 'Sub example', 'Sub example', 'manage_options', 'mt-example', 'iswform_page_example');

}



function register_newpage(){
    add_menu_page( 'Confirm', '', 'read', 'iswform/webpay_confirm.php', '', '', 6 );
}
function iswform_install(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table = $wpdb->prefix . 'isw_tranx';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
  sno int(11) NOT NULL AUTO_INCREMENT,
  txnref varchar(255),
  custid varchar(255),
  amount varchar(255),
  ramount varchar(255),
  email varchar(255),
  userid varchar(255),
  token varchar(500),
  gsm varchar(255),
  name varchar(255),
  membership_type varchar(255),
  payment_type varchar(255),
  status varchar(255),
  payref varchar(255),
  resp_code varchar(255),
  resp_desc varchar(255),
  txn_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  purpose varchar(255),
  PRIMARY KEY (sno)
) $charset_collate ;";
    //require_once( ABSPATH . 'wp_admin/includes/upgrade.php');
    $wpdb->query($sql);

}



function html_form_code()
{

    wp_head();
    echo "<form action='".get_admin_url()."admin-post.php' method='post'>";
    echo '<p>';
    echo 'Your Name <br />';
    echo '<input type="hidden" name="action" value="confirm_pay">';
    echo '<input type="text" name="cf-name" id="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . (isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '') . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Your Email <br />';
    echo '<input type="email" name="cf-email" value="' . (isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '') . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Your Mobile  <br />';
    echo '<input type="text" name="cf-mobile" value="' . (isset($_POST["cf-mobile"]) ? esc_attr($_POST["cf-mobile"]) : '') . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Purpose <br />';
    echo '<input type="text" name="cf-purpose" value="' . (isset($_POST["cf-purpose"]) ? esc_attr($_POST["cf-purpose"]) : '') . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Amount (required) <br />';
    echo '<input type="number" name="cf-amount" pattern="\d*" value="' . (isset($_POST["cf-amount"]) ? esc_attr($_POST["cf-amount"]) : '') . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Your Message (required) <br />';
    echo '<textarea rows="10" cols="35" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
    echo '</p>';
    echo '<p><input type="submit" name="isw-submitted" value="Submit"/></p>';
    echo '</form>';


}





function prefix_confirm_pay()
{
    global $wpdb;


    // if the submit button is clicked, send the email
    // if( isset( $_POST['cf-name'] ) ) {


    // sanitize form values
    $name = sanitize_text_field($_POST["cf-name"]);
    $email = sanitize_email($_POST["cf-email"]);
    $mobi = sanitize_text_field($_POST["cf-mobile"]);
    $pur = sanitize_text_field($_POST["cf-purpose"]);
    $amount = sanitize_text_field($_POST["cf-amount"]);
    $message = esc_textarea($_POST["cf-message"]);
    $amt1 = $amount * 100;
    $curr = 566;
    $pdid = 6205;
    $pitem = 101;
    $stat = "PENDING";
    $ref = uniqid();
    $rurl  = plugins_url().'/iswform/webpay_confirm.php';
    //$rurl = get_site_url();
    $mac = "D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F";
    $tohash = $ref.$pdid.$pitem.$amt1.$rurl.$mac;
    $dhash =  hash('sha512',$tohash);

    $table = $wpdb->prefix."isw_tranx";
    $wpdb->insert($table, array(
        'name' =>$name,
        'txnref' => $ref,
        'amount' => $amount,
        'email' => $email,
        'gsm' => $mobi,
        'purpose' => $pur,
        'payment_type' => $message,
        'status' => $stat
    ));

    echo "<div style='padding: 20px; width: 400px; display: block; background: #FAFAFA; margin: 0 auto;'>";
    echo '<h1>';
    echo 'Hello' ." ". $name. ' <br />';
    echo '</h1>';
    echo '<p>';
    echo 'You are donating :   <br />';
    echo $amount;
    echo '</p>';
    echo '<p>';
    echo 'Your Reference is : <br />';
    echo $ref;
    echo '</p>';

    echo "<form action='https://stageserv.interswitchng.com/test_paydirect/pay' method='post'>";
    echo '<p>';
    echo '<input type="hidden" name="action" value="query_pay">';
    echo '<input type="hidden" name="product_id" value="'.$pdid. '" />';
    echo '<input type="hidden" name="pay_item_id" value="'.$pitem.'" />';
    echo '<input type="hidden" name="currency" value="'.$curr.'" />';
    echo '<input type="hidden" name="amount" value="' . $amt1. '" />';
    echo '<p>';
    echo '<input type="hidden" name="txn_ref" value="' . $ref . '" size="40" readonly="readonly"';
    echo '</p>';
    echo '<input type="hidden" name="site_redirect_url" value="'.$rurl.'" />';
    echo '<input type="hidden" name="hash" value="'.$dhash.'" />';
    echo '<input type="hidden" name="cust_name" value="'. $name. '" />';
    echo '<input type="hidden" name="cust_id" value="'. $email. '" />';
    echo '</p>';
    echo '<p><input type="submit" name="isw-submitted" value="Submit"/></p>';
    echo '</form>';
    echo '</div>';





    // get the blog administrator's email address
    //$to = get_option('admin_email');

    //$headers = "From: $name <$email>" . "\r\n";

    // If email has been process for sending, display a success message
    //if (wp_mail($to, $subject, $message, $headers)) {

    //} else {
    //  echo 'An unexpected error occurred';
    //}
    //  }
}

/* class PostListener {

    private $valid = false;

    public function __construct(array $postdata) {
        $this->valid = $this->validatePostData($postdata);
    }

    public function __invoke() {
        if ($this->valid) {
            // do whatever you need to do
            global $wpdb;
            $txnref = $_POST['txnref'];
            $savedtranx = $wpdb->get_var("SELECT amount FROM $wpdb->wp_isw_tranx WHERE txnref = $txnref");
            $amt = $savedtranx * 100;
            $json = dquery($amt,$txnref);

            echo '<p>Response Code : '. $json['ResponseCode'] .'</p>';
            echo '<p>Response Description : '. $json['ResponseDescription'] .'</p>';
            exit();
        }
    }


    function dquery($amt, $ref)
    {
        $thash = queryHash($ref);
        $parami = array(
            "productid" => $GLOBALS['pdtid'],
            "transactionreference" => $ref,
            "amount" => $amt,
        );
        $pdt = $GLOBALS['pdtid'];
        //$ponmo = http_build_query($parami) . "\n";

        $query_url = 'https://stageserv.interswitchng.com/test_paydirect/api/v1/gettransaction.json';
        //$query_url = 'https://webpay.interswitchng.com/paydirect/api/v1/gettransaction.json';
        $url = "$query_url?productid=$pdt&transactionreference=$ref&amount=$amt";

        //note the variables appended to the url as get values for these parameters
        $headers = array(
            "GET /HTTP/1.1",
            "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
            "Accept-Language: en-us,en;q=0.5",
            "Keep-Alive: 300",
            "Connection: keep-alive",
            "Hash: $thash ");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, false);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        } else {
            $json = json_decode($data, TRUE);
            curl_close($ch);
            return $json;
        }




    }
    function queryHash($refi)
    {
        $tryhash = $GLOBALS['pdtid'] . $refi . $GLOBALS['mackey'];
        $dhash = hash('sha512', $tryhash);
        return $dhash;
    }
    private function validatePostData(array $postdata) {
        // check here the $_POST data, e.g. if the post data actually comes
        // from the api, autentication and so on
    }
}  */


//transactions log

add_action('admin_menu', 'isw_tranactions_log');

function isw_tranactions_log() {

    add_menu_page('Webpay Requery', 'Webpay Requery', 'administrator', 'isw_logs', 'webpay_logs');

}

function webpay_logs(){

    echo '<table border="1" width="90%">';
    echo '<tr>';
    echo '<th>S/NO</th>';
    echo '<th>TXNREF</th>';
    echo '<th>PAYMENT</th>';
    echo '<th>STATUS</th>';
    echo '<th>AMOUNT</th>';
    echo '<th>RESPONSE</th>';
    echo '<th>ACTION</th>';
    echo '</tr>';

    global $wpdb;
    $result = $wpdb->get_results( "SELECT * FROM wp_isw_tranx");
    foreach ( $result as $print ) {
        echo '<tr>';
        echo '<td>'.$print->sno.' </td>';
        echo '<td>'.$print->txnref.' </td>';
        echo '<td>'.$print->purpose.' </td>';
        echo '<td>'.$print->status.' </td>';
        echo '<td>'.$print->amount.' </td>';
        echo '<td>'.$print->resp_code.' </td>';
        echo '<td><a href="'.plugins_url().'/iswform/webpay_confirm.php?txnref='.$print->txnref.'"><button>Requery</button></a> </td>';
        echo '</tr>';
    }





}

function iswform_options_page() {

    // Must check that the user has the required capability
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Now display the settings editing screen
    ?>
    <div class="wrap">

        <h2>PLUGIN-NAME <?php _e('Settings') ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('iswform_options'); ?>
            <?php do_settings_sections('iswform'); ?>
            <?php
            // get option 'text_string' value from the database
            $options = get_option( 'iswform_options' ); ?>
            <h3>Interswitch Webpay Settings</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Product ID</th>
                    <td><input type="text" name="iswform_options[username]" value="<?php echo $options['username']; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Pay Item ID</th>
                    <td><input type="password" name="iswform_options[password]" value="<?php echo $options['password']; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Currency</th>
                    <td><input type="password" name="iswform_options[password]" value="<?php echo $options['password']; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Mac Key</th>
                    <td><input type="password" name="iswform_options[password]" value="<?php echo $options['password']; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Pay Item ID</th>
                    <td><input type="password" name="iswform_options[password]" value="<?php echo $options['password']; ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

<?php
}




