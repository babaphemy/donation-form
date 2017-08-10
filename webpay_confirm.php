<?php
/**
 * Created by PhpStorm.
 * User: Babafemi.Adigun
 * Date: 6/9/16
 * Time: 10:26 PM
 * save to db and forward to webpay
 */
define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');

if( isset( $_POST['txnref'] ) || isset ($_REQUEST['txnRef'])){
    global $wpdb;
    //$wpdb->show_errors();
   if( isset($_POST['txnref'])){

        $txnref = $_POST['txnref'];
    // echo($txnref) ;

    }
    if(isset($_REQUEST['txnRef'])){
        $txnref = $_REQUEST['txnRef'];

    }
    $tabl = $wpdb->prefix.isw_tranx;

    $amo = $wpdb->get_var($wpdb->prepare("SELECT amount FROM $tabl WHERE txnref = %s", $txnref));



    $amt = $amo * 100;
    //echo "New $amt";

    $json = dquery($amt,$txnref);
    echo "<div style='padding: 20px; width: 400px; display: block; background: #FAFAFA; margin: 0 auto;'>";


    echo '<p>Response Code : '. $json['ResponseCode'] .'</p>';
    echo '<p>Response Description : '. $json['ResponseDescription'] .'</p>';

    header( "refresh:5;url=http://localhost/wordpress" );
    echo 'You\'ll be redirected in about 5 secs. If not, click <a href="wherever.php">here</a>.';
    echo '</div>';
    //sleep(3000);
    //wp_redirect( home_url() );
    //exit;


    // Echo the title of the first scheduled post

}




    //$ponmo = http_build_query($parami) . "\n";


    function dquery($amt, $ref)
    {
        $pdt = 6205;
        $mac = "D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F";
        $tryhash = $pdt . $ref . $mac;
        $thash = hash('sha512', $tryhash);


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
            updateTranx($json, $ref);
            curl_close($ch);
            return $json;
        }




    }

function updateTranx($tranx, $ref){
    global $wpdb;
    $table = $wpdb->prefix."isw_tranx";
    $wpdb->update($table, array( 'status' => $ref,'resp_code' =>$tranx['ResponseCode'], 'resp_desc' =>$tranx['ResponseDescription'], 'payref' =>$tranx['PaymentReference'] ),"WHERE txnref =".$ref);

}


