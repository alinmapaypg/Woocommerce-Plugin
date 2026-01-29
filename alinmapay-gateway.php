<?php

$bd=ABSPATH.'wp-content/plugins/'.dirname( plugin_basename( __FILE__ ) );
set_include_path($bd.'/AlinmaPay_Payment'.PATH_SEPARATOR.get_include_path());
require_once($bd."/woocommerceLib/woocommercelib.php");
require_once dirname(__FILE__).'/config.php';


class AlinmaPay_Payment extends WC_Payment_Gateway {
   public $domain;
   
    public $gateway_server;
  public $merchant_id;
  public $merchant_key; 
  public $terminalId;
    public $transaction_method;
    public $channel;
    public $redirect_page_id;
    public $liveurl;
    public $msg;
  public $url;
  public $userData;
   
  // Constructor method
  public function __construct() {
    $this->id  = 'alinmapay_payment';
  
    $this ->method_title = __('AlinmaPay', 'alinmapay_payment');
    $this->method_description = __('Accept payments through  AlinmaPay Gateway', 'AlinmaPay');
    $this -> icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/images/AlinmaPayDefault.png';
       $this->domain = 'alinmapay_payment';
    // Other initialization code goes here
    
    $this->init_form_fields();
    $this->init_settings();
  
      $this -> title = $this -> settings['title'];
      $this -> description = $this -> settings['description'];
      $this -> merchant_id = isset($this -> settings['merchant_id']);
      $this -> merchant_key = $this -> settings['merchant_key'];
      $this -> terminalId = isset($this -> settings['terminalId']);
      $this -> url = $this -> settings['url'];
      $this -> gateway_server = isset($this -> settings['gateway_server']);
      $this -> transaction_method = isset($this -> settings['transaction_method']);
      $this -> channel = $this -> settings['channel'];
      $this -> redirect_page_id = isset($this -> settings['redirect_page_id']);
      $this -> liveurl = 'http://www.abc.com';
      $this -> msg['message'] = "";
      $this -> msg['class'] = "";
    $this -> userData = $this -> settings['userData'];

      
       //new for stc pay
      //add_action( 'woocommerce_checkout_create_order', array( $this, 'save_order_payment_type_meta_data' ), 10, 1 );
      add_action('init', array(&$this, 'check_alinmapay_payment_response'));
      //update for woocommerce >2.0
      add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_alinmapay_payment_response' ) );

      add_action('valid-alinmapay_payment-request', array(&$this, 'SUCCESS'));
      if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
      } else {
        add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
      }
    add_action('woocommerce_receipt_alinmapay_payment', array(&$this, 'receipt_page'));
    add_action('woocommerce_thankyou_alinmapay_payment',array(&$this, 'thankyou_page'));
    add_action('woocommerce_checkout_update_order_meta', 'save_transaction_type_meta');
    add_action( 'wp_enqueue_scripts', 'enqueue_custom_payment_styles' );
    // register_activation_hook(__FILE__, array($this, 'check_woocommerce_version_on_activation'));
     
    

  }
   
  
  /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     **/
    public function admin_options(){
      echo '<h3>'.__('AlinmaPay Gateway', 'alinmapay_payment').'</h3>';
      echo '<p>'.__('AlinmaPay Plugin  is most popular payment gateway for online shopping in KSA').'</p>';
      echo '<table class="form-table">';
      $this -> generate_settings_html();
      echo '</table>';

    }
    /**
     *  There are no payment fields for alinmapay_payment, but we want to show the description if set.
     **/
   //new for stc pay
   // function payment_fields(){
   //    if($this -> description)
   //  {  
   //  echo wpautop(wptexturize($this -> description));
      
   //  echo '<style>#transaction_type_field label.radio { display:inline-block; margin:0 .8em 0 .4em}</style>';

   //          $option_keys = array_keys($this->options);

   //          alinmapay_form_field( 'transaction_type', array(
   //              'type'          => 'radio',
   //              'class'         => array('transaction_type form-row-wide'),
   //              'label'         => __('Payment Type', $this->domain),
   //              'options'       => $this->options,
   //          ), reset( $option_keys ) );
   //  }
    // }
     function payment_fields(){
      if($this -> description) echo wpautop(wptexturize($this -> description));
    }
    
    
  //new for stc pay
   /**
         * Save the chosen payment type as order meta data.
         *
         * @param object $order
         * @param array $data
         */
        // public function save_order_payment_type_meta_data( $order) {
        //     if ( isset($_POST['transaction_type']) )
        //         $order->update_meta_data('_transaction_type', esc_attr($_POST['transaction_type']) );
        // }

    
  public function init_form_fields() {
    
    $this->form_fields = array(
      'enabled' => array(
        'title'   => __('Enable/Disable', 'AlinmaPay'),
        'type'    => 'checkbox',
        'label'   => __('Enable My Custom Gateway', 'alinmapay_payment'),
        'default' => 'yes',
      ),
      'title' => array(
            'title' => __('Title:', 'AlinmaPay'),
            'type'=> 'text',
            'description' => __('This controls the title which the user sees during checkout.', 'alinmapay_payment'),
            'default' => __('AlinmaPay', 'AlinmaPay')),
          'description' => array(
            'title' => __('Description:', 'alinmapay_payment'),
            'type' => 'textarea',
            'description' => __('This controls the description which the user sees during checkout.', 'alinmapay_payment'),
            'default' => __('Pay securely by Credit or Debit card or net banking through AlinmaPay Secure Servers.', 'AlinmaPay')),      
      'password' => array(
            'title' => __('Password', 'alinmapay_payment'),
            'type' => 'text',
            'description' =>  __('Password.', 'alinmapay_payment')
            ),      
      'merchant_key' => array(
            'title' => __('Merchant Key', 'alinmapay_payment'),
            'type' => 'text',
            'description' =>  __('Merchant Key.', 'alinmapay_payment')
            ),      
      'aggregator_id' => array(
            'title' => __('Terminal ID', 'alinmapay_payment'),
            'type' => 'text',
            'description' =>  __('Terminal ID.', 'alinmapay_payment')
            ),
        'transaction_method' => array(

            'title' => __('Transaction Type', 'alinmapay_payment'),

            'type' => 'select',

             // 'options' => array("1"=>"Purchase","4"=>"Authorization","NETBANK"=>"NETBANK","UPI"=>"UPI","WALLET"=>"WALLET"),
            'options' => array("1"=>"Purchase","4"=>"Authorization"),

            'description' => __('Transaction Type','woocom_plugin')

            ),
          // 'channel_id' => array(
          //   'title'       => __('Channel ID', 'vegaah_payment'),
          //   'type'        => 'text',
          //   'description' => __('Enter Channel ID if Transaction Method is NetBank.', 'vegaah_payment'),
          //   'default'     => '',
        // ),
        /*  'gateway_server' => array(
            'title' => __('Gateway Server', 'alinmapay_payment'),
            'type' => 'select',
            'options' => array("0"=>"Select","sandbox"=>"Sandbox","live"=>"Live"),
            'description' => __('alinmapay_payment Gateway module as activated by alinmapay_paymentPay.','alinmapay_payment')
            ),*/
      'url' => array(
            'title' => __('Request URL', 'alinmapay_payment'),
            'type' => 'text',
            'description' =>  __('Request URL.', 'alinmapay_payment')
            ),
          
          'channel' => array(
            'title' => __('Channel', 'alinmapay_payment'),
            'type' => 'select',
            'options' => array("0"=>"Select","WEB"=>"Web","MOBILE"=>"Mobile"),
            'description' => __('Channel.','alinmapay_payment')
            ),
        //    'pip_selection' => array(
        //     'title'       => __('Payment Instrument Priority', 'vegaah_payment'),
        //     'type'        => 'pip_order', // custom renderer
        //     'description' => __('Select and order instruments', 'vegaah_payment'),
        // ),
    'userData' => array(
            'title' => __('userData', 'alinmapay_payment'),
            'type' => 'text',
            'description' =>  __('metaData.', 'alinmapay_payment'),
       'default' => __('{"entry":"entry1","receiptUrl":"https://www.google.com"}')
            )

        
    );
  }

/*public function generate_pip_order_html($key, $data) {
    $value = $this->get_option($key, '');
    $options = ['CCI','UPI','WALLET','CHALLAN','NETBANK'];

    ob_start(); ?>
    <div>
        <span id="selectedDisplay"><?php echo esc_html($value ?: 'Select Options'); ?></span>
        <input type="hidden" id="selectedItemsInput" name="<?php echo esc_attr($this->get_field_key($key)); ?>" value="<?php echo esc_attr($value); ?>" />
        <button type="button" onclick="showPopup()">Choose</button>
    </div>

    <div id="overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:#000; opacity:0.5; z-index:9998;"></div>
    <div id="popup" style="display:none; position:fixed; top:20%; left:35%; background:#fff; border:1px solid #ccc; padding:20px; z-index:9999;">
        <h3>Select Options</h3>
        <?php foreach ($options as $opt): ?>
            <label><input type="checkbox" value="<?php echo $opt; ?>" onclick="updateSelection(this)"> <?php echo $opt; ?></label><br>
        <?php endforeach; ?>

        <div style="margin-top:10px; text-align:right;">
            <button type="button" onclick="confirmSelection()">OK</button>
            <button type="button" onclick="hidePopup()">Cancel</button>
        </div>
    </div>
<?php
        return $this->form_table_row($key, $data, ob_get_clean());
}
 private function form_table_row($key, $data, $field_html) {
        return sprintf(
            '<tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="%s">%s</label>
                </th>
                <td class="forminp">%s<p class="description">%s</p></td>
            </tr>',
            esc_attr($this->get_field_key($key)),
            esc_html($data['title']),
            $field_html,
            esc_html($data['description'])
        );
    }*/
function receipt_page($order){
      echo '<p>'.__('Thank you for your order, please click the button below to pay with alinmapay_payment.', 'alinmapay_payment').'</p>';
      echo $this -> generate_alinmapay_payment_form($order);
    }
    function thankyou_page($order){

      //echo '<p>'.__('Thank you for your order.', 'woocom_plugin').'</p>';

      //echo $this -> generate_woocom_plugin_form($order);

    }
  
  // Process the payment
  // public function process_payment($order_id) {
  //   $order = wc_get_order($order_id);
  //   $payment_type = $order->get_meta('_transaction_type');
  //   //echo $payment_type;die();
  //     return array('result' => 'success', 
  //       'redirect' => add_query_arg(
  //         'order-pay',
  //         $order->get_id(),
  //         $order->get_checkout_payment_url(true),
  //         add_query_arg('key', $order->order_key, get_permalink(alinmapay_get_page_id('pay'))
  //       )
  //       )
  //     );
   
  // }
 
    public function process_payment($order_id) {
    $order = wc_get_order($order_id);

    if ($order->has_status('failed')) {
        $order->update_status('pending', __('Retrying payment. Order status changed to pending.', 'alinmapay_payment'));
    }

    return array(
        'result' => 'success',
        'redirect' => add_query_arg(
            array(
                'order-pay' => $order->get_id(),
                'key'       => $order->get_order_key(),
            ),
            get_permalink(wc_get_page_id('pay')) // or use 'pay' if that’s what you intended
        )
    );
}

   public function result($order,$result)
    {
      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        $link = "https"; 
      else
        $link = "http"; 
  
      // Here append the common URL characters. 
      $link .= "://"; 
  
      // Append the host(domain name, ip) to the URL. 
      $link .= $_SERVER['HTTP_HOST']; 
  
// Append the requested resource location to the URL 
 $link .= $_SERVER['REQUEST_URI']; 
  $link = preg_split( "/(\?|!)/", $link );  
    echo '
    <html>
<head>
<style>
.text-danger strong {
        color: #9f181c;
    }
    .receipt-main {
      background: #ffffff none repeat scroll 0 0;
      border-bottom: 12px solid #333333;
      border-top: 12px solid #9f181c;
      margin-top: 50px;
      margin-bottom: 50px;
      padding: 30px 30px !important;
      position: relative;
      box-shadow: 0 1px 21px #acacac;
      color: #333333;
      font-family: open sans;
    }
    .receipt-main p {
      color: #333333;
      font-family: open sans;
      line-height: 1.42857;
    }
    .receipt-footer h1 {
      font-size: 15px;
      font-weight: 400 !important;
      margin: 0 !important;
    }
    .receipt-main::after {
      background: #414143 none repeat scroll 0 0;
      content: "";
      height: 5px;
      left: 0;
      position: absolute;
      right: 0;
      top: -13px;
    }
    .receipt-main thead {
      background: #414143 none repeat scroll 0 0;
    }
    .receipt-main thead th {
      color:#fff;
    }
    .receipt-right h5 {
      font-size: 16px;
      font-weight: bold;
      margin: 0 0 7px 0;
    }
    .receipt-right p {
      font-size: 12px;
      margin: 0px;
    }
    .receipt-right p i {
      text-align: center;
      width: 18px;
    }
    .receipt-main td {
      padding: 9px 20px !important;
    }
    .receipt-main th {
      padding: 13px 20px !important;
    }
    .receipt-main td {
      font-size: 13px;
      font-weight: initial !important;
    }
    .receipt-main td p:last-child {
      margin: 0;
      padding: 0;
    } 
    .receipt-main td h2 {
      font-size: 20px;
      font-weight: 900;
      margin: 0;
      text-transform: uppercase;
    }
    .receipt-header-mid .receipt-left h1 {
      font-weight: 100;
      margin: 34px 0 0;
      text-align: right;
      text-transform: uppercase;
    }
    .receipt-header-mid {
      
      overflow: hidden;
    }
    
    #container {
      background-color: #dcdcdc;
    }
</style>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->
</head>
<body>';
if($result!='Fraud'){
echo '
<div class="container">
  <div class="row">
    
        <div class="receipt-main col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                
      <div class="row">
        <div class="receipt-header receipt-header-mid">';
        if($result=='UnSuccessful'){
        echo '<div style="background-color: red;" id="unsuccess"><h2 align="center">Your Transaction is '.$result.'</h2></div>';
        }
        else
        {
        echo '<div style="background-color: green;" id="success"><h2 align="center">Your Transaction is '.$result.'</h2></div>';
        }
        echo'
        
          <div class="col-xs-8 col-sm-8 col-md-8 text-left">
            <div class="receipt-right">
              <p><b>Email :</b> '.$order -> get_billing_email().'</p>
              <p><b>Address :</b> '.$order -> get_billing_address_1().'</p>
            </div>
          </div>
          <div class="col-xs-4 col-sm-4 col-md-4">
            <div class="receipt-left">
              <h1>Receipt</h1>
            </div>
          </div>
        </div>
            </div>
      
            <div>
      ';
      foreach ($order->get_items() as $key => $lineItem) {
            
  echo '
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-md-9">'.$lineItem['name'].'</td>
                            <td class="col-md-3"><i class="fa fa-inr"></i> '.$lineItem['total'].'</td>
                        </tr>
                       
                     
                        <tr>
                            <td class="text-right">
                            <p>
                                <strong>Total Amount: </strong>
                            </p>
              </td>
                            <td>
                            <p>
                                <strong><i class="fa fa-inr"></i> '.$order -> get_total().' '. get_alinmapay_currency().'/-</strong>
                            </p> 
              </td>
                        </tr>
                   
                    </tbody>
                </table>';
        }
        echo '
            </div>
      
      <div class="row">
        <div class="receipt-header receipt-header-mid receipt-footer">
          <div class="col-xs-8 col-sm-8 col-md-8 text-left">
            <div class="receipt-right">
              <p><b>Date :</b> '.date("Y-m-d").'</p>
            </div>
          </div>
          <div><a href="'.$link[0].'"><h2 align="center">Back To Home</h2></a></div>
          <div class="col-xs-4 col-sm-4 col-md-4">
            <div class="receipt-left">
              
            </div>
          </div>
        </div>
            </div>
      
        </div>    
  </div>
</div>';
}else{
    echo '
    <div class="container">
  <div class="row">
    
        <div class="receipt-main col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                
      <div class="row">
        <div class="receipt-header receipt-header-mid">
          <div style="background-color: red;"><h2 align="center">Thank you for shopping with us. This is fraud Transaction. Data is tempered. Please contact with administrator...</h2></div>
        </div>
        </div>
        </div>
        </div>
        </div>
    ';
    }
echo '
</body>
</html>
    ';
    
    die;
  }
      /**
     * Check for valid alinmapay_payment server callback
     **/  
    function decryptData($encryptedResponse, $merKey) {
      // Convert the hexadecimal key to binary
      $binaryKey = hex2bin($merKey);

      // Decode the base64 encoded encrypted response
      $decodedData = base64_decode($encryptedResponse);

      // Decrypt the data using AES-256-ECB cipher
      $decryptedData = openssl_decrypt($decodedData, 'AES-256-ECB', $binaryKey, OPENSSL_RAW_DATA);

      // Check if decryption failed
      if ($decryptedData === false) {
          return "Decryption failed";
      }

      return $decryptedData;
  }   
    function check_alinmapay_payment_response(){
  //echo"hii";die;
    global $woocommerce;
    $jsonData = file_get_contents("php://input");

    //echo "Received JSON data: " . $jsonData;die();
    parse_str($jsonData, $parsedData);

    unset($parsedData['termId']);
    if(isset($parsedData['data'])) {
    $dataValue = $parsedData['data'];

    // Decode the extracted data
    if (is_array($dataValue)) {
     $decodedData = urldecode(reset($dataValue)); // Use the first element if it's an array
    } else {
        $decodedData = urldecode($dataValue); // Safe to decode
    }
    $decodedData = str_replace(' ', '+', $decodedData);
    $encryptedResponse = $decodedData;
      
      
    $merKey = $this->merchant_key;

    //echo $merKey;die();
    try {

     $decryptedData = $this->decryptData($encryptedResponse, $merKey);
     error_log('Decrypted Response: ' . $decryptedData);
    //echo "Decrypted and decoded data: " . $decryptedData;die();

    } catch (Exception $e) {
      echo "Error: " . $e->getMessage();die();
    }
         
    $data = json_decode($decryptedData, true);

    //Access the "transactionId" field
         
    $transactionId = $data['transactionId'];
    $respamount = $data['amountDetails']['amount'];
    $orderid =$data['orderDetails']['orderId'];
    $responsehash=$data['signature'];
    $responsecode=$data['responseCode'];
    //echo $responsehash;die();
    $result = $data['result'];
    $respcurrency=$data['currency'];
    
    // Use the received POST data as needed
        
    if($transactionId !== NULL)

    {

      $order = new WC_Order($orderid);

      $orderStatus=$order->get_status();

      $transauthorised = false;

      $merchant_key=$this->merchant_key;
     

    if($orderStatus=='pending')

    {

      if($result=="SUCCESS" )
    {       
      $order    = new WC_Order($orderid);        
      $transauthorised = true;
      $this -> msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
      $this -> msg['class'] = 'woocommerce';
      $this ->msg['type']="info";
                
      $order -> payment_complete();
      $woocommerce -> cart -> empty_cart();
      $order -> update_status('completed');
      $order -> add_order_note('Payment Gateway has processed the payment. Ref Number: '.$orderid);
       
      $order_received_url = $order->get_checkout_order_received_url();
      wp_redirect($order_received_url);
      //echo $this -> result($order,"Successful");
      exit;  
 
    }
      else

    {

        if ($result == 'FAILURE') {

        $order->update_status('failed');
        $order->add_order_note('Transaction failed.');
        $order->add_order_note($message);

        $order = wc_get_order($orderid);
        

        $unique_key = 'alinmapay_error_' . md5($orderid . time());
        set_transient($unique_key, 'Thank you for shopping with us. However, the transaction has been Failed.', 60);

        // Redirect with just a key (not message)
        wp_redirect( add_query_arg('alinmapay_msg_key', $unique_key, wc_get_checkout_url()) );
        exit;

   
}


            }

    }

    else

    {
    
       
    $message = "Thank you for shopping with us. However, the transaction has been Failed.";
    
    $order->update_status('failed');
    $order->add_order_note('Transaction failed.');
    $order->add_order_note($message);

    // Use WooCommerce default pages or fallback
      $unique_key = 'alinmapay_error_' . md5($orderid . time());
        set_transient($unique_key, 'Thank you for shopping with us. However, the transaction has been Failed.', 60);

        // Redirect with just a key (not message)
        wp_redirect( add_query_arg('alinmapay_msg_key', $unique_key, wc_get_checkout_url()) );
        exit;

    }

            

    $message = "Thank you for shopping with us. However, the transaction has been Failed.";
    
    $order->update_status('failed');
    $order->add_order_note('Transaction failed.');
    $order->add_order_note($message);

    // Use WooCommerce default pages or fallback
      $unique_key = 'alinmapay_error_' . md5($orderid . time());
        set_transient($unique_key, 'Thank you for shopping with us. However, the transaction has been Failed.', 60);

        // Redirect with just a key (not message)
        wp_redirect( add_query_arg('alinmapay_msg_key', $unique_key, wc_get_checkout_url()) );
        exit;

    }
  }

    }
    function getOS() { 
  global $user_agent;
  $user_agent = $_SERVER['HTTP_USER_AGENT'];


    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
                          '/windows nt 10/i'      =>  'Windows 10',
                          '/windows nt 6.3/i'     =>  'Windows 8.1',
                          '/windows nt 6.2/i'     =>  'Windows 8',
                          '/windows nt 6.1/i'     =>  'Windows 7',
                          '/windows nt 6.0/i'     =>  'Windows Vista',
                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                          '/windows nt 5.1/i'     =>  'Windows XP',
                          '/windows xp/i'         =>  'Windows XP',
                          '/windows nt 5.0/i'     =>  'Windows 2000',
                          '/windows me/i'         =>  'Windows ME',
                          '/win98/i'              =>  'Windows 98',
                          '/win95/i'              =>  'Windows 95',
                          '/win16/i'              =>  'Windows 3.11',
                          '/macintosh|mac os x/i' =>  'Mac OS X',
                          '/mac_powerpc/i'        =>  'Mac OS 9',
                          '/linux/i'              =>  'Linux',
                          '/ubuntu/i'             =>  'Ubuntu',
                          '/iphone/i'             =>  'iPhone',
                          '/ipod/i'               =>  'iPod',
                          '/ipad/i'               =>  'iPad',
                          '/android/i'            =>  'Android',
                          '/blackberry/i'         =>  'BlackBerry',
                          '/webos/i'              =>  'Mobile'
                    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}
    function getDeviceType($userAgent) {
    if (strpos($userAgent, 'iPad') !== false) {
        return 'iPad';
    } elseif (strpos($userAgent, 'iPhone') !== false) {
        return 'iPhone';
    } elseif (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
        return 'Mobile';
    } elseif (strpos($userAgent, 'Macintosh') !== false && strpos($userAgent, 'Safari') !== false) {
    return 'Mac';
    }
    else {
        return 'Desktop';
    }
}

    function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

    
  /**
     * Generate woocommerce_payment button link
     **/    
    public function generate_alinmapay_payment_form($order_id)
  {
    
     
      global $woocommerce;
      $order = new WC_Order($order_id);
      $stcFlag= get_post_meta($order->get_id(), '_transaction_type', true ) ;

      $redirect_url = ($this -> redirect_page_id=="" || $this -> redirect_page_id==0)?get_site_url() . "/":get_permalink($this -> redirect_page_id);
     
      //For wooCoomerce 2.0
      $redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );
      $order_id = $order_id.''.date("ymds").rand();
      //echo $redirect_url;die();
      //do we have a phone number?
      //get currency  

      $id=$order->get_id();
      $address = $order -> get_billing_address_1();
      if ($order ->get_billing_address_2() != "")
        $address = $address.' '.$order -> get_billing_address_2();

  
 
      $currencycode = get_woocommerce_currency();    
      $merchantTxnId = $order_id;
      $orderAmount = $order -> get_total();     
      //$action = customgatewayLib::getCpUrl($this->gateway_server); 
      
    
      $success_url =  $redirect_url;
      $failure_url =  $redirect_url;
      
      $host= gethostname();
      $ip = gethostbyname($host);
      
      $terminalId= $this -> settings['aggregator_id'];
      $password=$this -> settings['password'];
      
      $txn_details= "".$id."|".$terminalId."|".$password."|".$this->merchant_key."|".$orderAmount."|".$currencycode."";
      $requestHash  = woocommerceLib::_Hashcreation($txn_details);
      
      $url= $this -> settings['url'];
      $userData = $this -> settings['userData'];

      $Transaction_type=$this ->settings['transaction_method'];
    
      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        $plugin_root_url = "https"; 
      else
        $plugin_root_url = "http"; 
    
        // Here append the common URL characters. 
        $plugin_root_url .= "://"; 
          
        // Append the host(domain name, ip) to the URL. 
        $plugin_root_url .= $_SERVER['HTTP_HOST']; 
          
        $plugin_root_url .= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        //print_r($plugin_root_url ."?wc-api=woocommerce_payment");die();
        //  $plugin_root_url = preg_split( "/(\?|!)/", $plugin_root_url );
            $pluginName = 'Woocommerce_Hosted';
      $pluginVersion = '3.0.3';
      $pluginPlatform = 'Desktop';
      // $deviceModeltest = $_SERVER['HTTP_USER_AGENT'];
      // $deviceModel = substr($deviceModeltest,13,-75);
      // $devicePlatform = $this->getBrowser();
      // $deviceOSVersion = $this->getOS();
      $ua=$this-> getBrowser();
      $str=$ua['userAgent'];
      //echo($str);
      $pos1 = strpos($str, '(')+1;
  
      $pos2 = strpos($str, ')')-$pos1;
      $part = substr($str, $pos1, $pos2);
      $parts = explode(" ", $part);
      $devicePlatform= $ua['name'] . " " . $ua['version'];

      $userAgent = $_SERVER['HTTP_USER_AGENT']; 

      $deviceType = $this-> getDeviceType($userAgent);
      if ($deviceType === 'iPad') {
        $version = preg_replace("/(.+)(iPhone|iPad|iPod)(.+)OS[\s|\_](\d+)\_?(\d+)?[\_]?(\d+)?.+/i", "$4.$5", $str);

        //echo($version);
        $deviceOSVersion = $version;
        $deviceModel= "ipad" ;
      } else if ($deviceType === 'iPhone') {
          $version = preg_match("/OS ((\d+_?){2,3})/i", $str, $matches);
          $iosversion=str_replace("_",".",$matches[1]);
          //print_r($iosversion);
          $deviceOSVersion = $iosversion;
          $deviceModel= $parts[2].' '.$parts[4];

    } else if ($deviceType === 'Mobile') {
          $deviceOSVersion = $parts[1].' '.$parts[2];
          $deviceModel= $parts[3].' '.$parts[4];

  }elseif($deviceType === 'Mac'){
      $deviceInfo = $this-> getDeviceInfo($userAgent);
      $deviceType === 'Mac';
      $devicePlatform = $deviceInfo['browser'];
      $deviceOSVersion = $deviceInfo['macOSVersion'];
      $deviceModel = $deviceInfo['macDevice'];

  }else{
    $deviceType  = 'Desktop';
    $deviceModeltest = $_SERVER['HTTP_USER_AGENT'];
    $deviceModel = substr($deviceModeltest,13,-75);

    $deviceOSVersion = $this-> getOS();
  }
  /*$pip_selection = $this->get_option('pip_selection'); // e.g. "WALLET,CHALLAN"
  $pipArray = $pip_selection ? explode(",", $pip_selection) : [];

  $customisePaymentInstruments = [];
  $orderopt = 1;

  foreach ($pipArray as $pip) {
      $customisePaymentInstruments[] = [
          "paymentMethod" => trim($pip),
          "order"         => strval($orderopt++)
      ];
  }
  $paymentInstr =null;
  $transaction_method = $Transaction_type; // get dropdown value from checkout/admin form
  //echo $transaction_method ;die();
  if ($transaction_method === "NETBANK") {
      $Transaction_type = "1"; // API expects "1" for NETBANK
         $paymentInstr = [
            "paymentMethod" => "NETBANK",
            "channelId"     => $channelID 
          ];
  }
  elseif ($transaction_method === "UPI") {
      $Transaction_type= "1";
     $paymentInstr = [
            "paymentMethod" => "UPI",
            "channelId"     => $channelID 
        ];
  }
  elseif ($transaction_method === "WALLET") {
     $Transaction_type = "1";
       $paymentInstr = [
            "paymentMethod" => "WALLET",
             "channelId"     => $channelID 
        ];
  }*/

      $fields = array(
      'terminalId' => $terminalId,
      'password'=> $password,
      'signature' => $requestHash,
      'paymentType' => $Transaction_type,
      'amount' =>$orderAmount,
      'currency' => $currencycode,
      'order' => array(
          'orderId' =>  $id,
          'description' => "", 
          ),
      'customer' => array(
          'customerEmail'=>$order -> get_billing_email(),
          'billingAddressStreet'=> "",
          'billingAddressCity'=>"",
          'billingAddressState'=>"",
          'billingAddressPostalCode'=>"",
          'billingAddressCountry'=> $order -> get_billing_country()
        ),
      //'customisePaymentInstruments'=>$customisePaymentInstruments,   
      'merchantIp' =>$ip,
      'additionalDetails' => array(
        'userData' => $userData
                
      ),
      //'paymentInstrument'=>$paymentInstr,
      "deviceInfo" => json_encode([
              'pluginName' => $pluginName,
              'pluginVersion' => $pluginVersion, 
              'deviceType' => $deviceType,
              'deviceModel' => $deviceModel,
              'deviceOSVersion' => $deviceOSVersion,  
              'clientPlatform' => $devicePlatform,
            ])

            );
    $fields_string = json_encode($fields,JSON_UNESCAPED_SLASHES);
  error_log('Request URL: ' . $url);
  error_log('Request Body:' . $fields_string);
  
    //echo "<pre>";
  //echo "request Json:- ".$fields_string;die;
    
    
    $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
              'Content-Length: ' . strlen($fields_string))
            );
              curl_setopt($ch, CURLOPT_TIMEOUT, 5);
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

              //execute post
        $result = curl_exec($ch);
        
        //print_r ($result);die;
              //close connection
        curl_close($ch);
        error_log('Response Body : ' . $result);   
                  
                  $urldecode=(json_decode($result,true));
          if(empty($result)){

           $message = "Thank you for shopping with us. However, the transaction has been Failed.";
    
            $order->update_status('failed');
            $order->add_order_note('Transaction failed.');
            $order->add_order_note($message);

            $order = wc_get_order($id);
            
             $unique_key = 'alinmapay_error_' . md5($id . time());
            set_transient($unique_key, 'Thank you for shopping with us. However, the transaction has been Failed.', 60);

            // Redirect with just a key (not message)
            wp_redirect( add_query_arg('alinmapay_msg_key', $unique_key, wc_get_checkout_url()) );
            exit;

          }else{
            if($urldecode['responseCode'] =='001' &&  isset($urldecode['paymentLink']['linkUrl'])){
            $linkUrl= $urldecode['paymentLink']['linkUrl'];
            $url=$linkUrl.$urldecode['transactionId'];
                 
            //echo $url;die; 
              
            if($urldecode['transactionId'] != NULL)
            {echo '
              <html>
              <form name="myform" method="POST" action="'.$url.'">
              <h1>Transaction is processing......</h1>
              </form>
              <script type="text/javascript">document.myform.submit();
              </script>
              </html>';die;
            } else{
              echo "<b>Something went wrong!!!!</b>"; 
              }
            }
                    else{
           $message = "Thank you for shopping with us. However, the transaction has been Failed.";
    
            $order->update_status('failed');
            $order->add_order_note('Transaction failed.');
            $order->add_order_note($message);

            $order = wc_get_order($id);
            

            $unique_key = 'alinmapay_error_' . md5($id . time());
            set_transient($unique_key, 'Thank you for shopping with us. However, the transaction has been Failed.', 60);

            // Redirect with just a key (not message)
            wp_redirect( add_query_arg('alinmapay_msg_key', $unique_key, wc_get_checkout_url()) );
            exit;
                    }
                    
          }
                
          
                
                  
                 }
   function get_pages($title = false, $indent = true) {
      $wp_pages = get_pages('sort_column=menu_order');
      $page_list = array();
      if ($title) $page_list[] = $title;
      foreach ($wp_pages as $page) {
        $prefix = '';
        // show indented child pages?
        if ($indent) {
          $has_parent = $page->post_parent;
          while($has_parent) {
            $prefix .=  ' - ';
            $next_page = get_page($has_parent);
            $has_parent = $next_page->post_parent;
          }
        }
        // add to page list array array
        $page_list[$page->ID] = $prefix . $page->post_title;
      }
      return $page_list;
    }

  }

  /**
   * Add the Gateway to WooCommerce
   **/
  function enqueue_custom_payment_styles() {
    wp_enqueue_style( 'custom-payment-method-css', plugins_url( 'css/style.css', __FILE__ ) );
}


  add_filter( 'woocommerce_get_checkout_order_url', 'custom_checkout_order_url', 10, 2 );

function custom_checkout_order_url( $order_url, $order ) {
    // Get the order ID
    $order_id = $order->get_id();

    // Modify the URL to use order-pay instead of order
    $order_url = add_query_arg( 'order-pay', $order_id, $order_url );

    return $order_url;
}
  

  function woocommerce_add_alinmapay_payment_gateway($methods) {
    $methods[] = 'AlinmaPay_Payment';
    return $methods;
  }
add_filter('woocommerce_payment_gateways', 'woocommerce_add_alinmapay_payment_gateway' );

    function save_transaction_type_meta($order_id) {
    if (isset($_POST['transaction_type'])) {
        update_post_meta($order_id, '_transaction_type', sanitize_text_field($_POST['transaction_type']));
    }
}

add_action('woocommerce_before_checkout_form', 'alinmapay_display_failure_notice', 5);

function alinmapay_display_failure_notice() {
    if (isset($_GET['alinmapay_msg_key'])) {
        $msg_key = sanitize_text_field($_GET['alinmapay_msg_key']);
        $msg = get_transient($msg_key);

        error_log("HOOK TRIGGERED: {$msg_key} = {$msg}"); // Debug check

        if (!empty($msg)) {
            wc_add_notice($msg, 'error');
            delete_transient($msg_key); // Clean up
        }
    }
}
add_action('template_redirect', function() {
    if (is_checkout() && isset($_GET['alinmapay_msg_key'])) {
        $msg = get_transient(sanitize_text_field($_GET['alinmapay_msg_key']));
        if (!empty($msg)) {
            wc_add_notice($msg, 'error');
            delete_transient($_GET['alinmapay_msg_key']);
        }
    }
});
/*add_action('admin_footer', function () {
    $screen = get_current_screen();
    if ($screen->id === 'woocommerce_page_wc-settings') { ?>
        <script>
            let selectedItems = [];

            function showPopup() {
                const input = document.getElementById("selectedItemsInput");
                selectedItems = input.value ? input.value.split(",") : [];

                // Reset checkboxes
                document.querySelectorAll("#popup input[type=checkbox]").forEach(cb => {
                    cb.checked = selectedItems.includes(cb.value);
                });

                document.getElementById("popup").style.display = "block";
                document.getElementById("overlay").style.display = "block";
            }

            function hidePopup() {
                document.getElementById("popup").style.display = "none";
                document.getElementById("overlay").style.display = "none";
            }

            function updateSelection(checkbox) {
                const value = checkbox.value;
                if (checkbox.checked) {
                    if (!selectedItems.includes(value)) {
                        selectedItems.push(value);
                    }
                } else {
                    selectedItems = selectedItems.filter(v => v !== value);
                }
            }

            function confirmSelection() {
                const display = document.getElementById("selectedDisplay");
                const input = document.getElementById("selectedItemsInput");
                const selectedText = selectedItems.join(",");
                display.textContent = selectedText || "Select Options";
                input.value = selectedText;
                hidePopup();
            }
           

        </script>
    <?php }
});*/















 
  

?>