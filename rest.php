<?php

define( 'WC_LOG_DIR_PP', WC_LOG_DIR . 'pp/' );

add_action( 'rest_api_init', function () {
    register_rest_route( 'woo/v1', '/emt_callBack', array(
      'methods' => 'POST',
      'callback' => 'amw_update_func',
    ) );
 
} );
/* 
 --- Old function --- changed September 27, 2022 at 12:08pm GMT0.
  function amw_update_func(WP_REST_Request $request ){
    $desc = '';
    $order_id = $request['order_id'];
    if(empty($order_id)){
        $order_id = $request['txid'];   
    }
    $order_status = $request['status'];
    if(!empty($request['description'])){
        $desc = $request['description'];
    }
    $response = [];    
    if(empty($order_id) && $order_status == 1){
        return new WP_Error( 'error', 'Invalid Request', array( 'status' => 404 ) );
    } 
    $order = wc_get_order($order_id);
    if(empty($order)){
        error_log( print_r( 'not correct order_id '.$order_id, true ) );
        return new WP_Error( 'error', 'Invalid order id', array( 'status' => 404 ) );
    }
    $order->set_status('processing',$desc);
    $order->save();
    $total = $order->get_formatted_order_total();
    setEmail($order_id,$total);
    $response['status'] = 'success';
    $response['description'] = $desc;
    $res = new WP_REST_Response($response);
    $res->set_status(200);
    return $res;
}

  */
  
  
  // --- New function --- changed September 27, 2022 at 12:08pm GMT0.
function amw_update_func(WP_REST_Request $request ){
		$queryParams = $request->get_json_params();
		$log = new WC_Logger();
		$log->log( 'Calllback Logger', 'request:'.wc_print_r($queryParams,true)); //log data received from callback. this line can be commented

     
		$hash_received = $queryParams['hash'];
		$order_id_to_check = (int)$queryParams['txid'];
		$action = $queryParams['action'];
		$order_status = $queryParams['status'];

        $idAccount_to_check = $queryParams['idaccount'];
		
		if ($idAccount_to_check == 1027){
		    $idMethod = 113;
		    $idAccount = 1027;
		    $SecretKey = 'h7MhP9GeaB6R';
		    $file_name = date('Y-m-d').'-vp1258-rest.log';
		} else{
		    $idAccount_to_check = 1023;
		    $idMethod = 104;
		    $idAccount = 1023;
		    $SecretKey = 'GraAxBXbUdlb';
		    $file_name = date('Y-m-d').'-vp5353-rest.log';
		}

        //return new WP_Error( 'error', 'Invalid Request', array( 'status' => 404, 'idaccount' => $idAccount_to_check));
		
		$desc = '';
		if(!empty($queryParams['description'])){
			$desc = $queryParams['description'];
		}
        // check if has order ID in callback
		if(empty($order_id_to_check) && $order_status == 1){
			return new WP_Error( 'error', 'Invalid Request', array( 'status' => 404 ));
		}

		$order = wc_get_order( $order_id_to_check );
        // check if given ID exist 
		if(empty($order)){
			error_log( print_r( 'not correct order_id '.$order_id_to_check, true ) );
			return new WP_Error( 'error', 'Invalid order id', array( 'status' => 404 ) );
		}
		
		
        /*
		// staging mode
		$SecretKey = 'j3BWHjad5JBG';
		$idAccount = 1017;
		$idMethod = 102;
		// production mode
		$SecretKey = 'GraAxBXbUdlb';
		$idAccount = 1023;
		$idMethod = 104;
		
		*/
		
		$order_id = $order->get_id();
		$clientClientId = $order->get_user_id();
		$order_number = $order->get_order_number();
		$amount = number_format((float)$order->get_total(), 2, '.', '');
        // create data for HASH check
		$data_local = [
			"action"=> $action,
			"idaccount" => $idAccount,
			"idmethod" => $idMethod,
			"txid" => strval($order_number),
			"clientid" => $clientClientId,
			"amount" => floatval(preg_replace("/\\.?0+$/", "", $amount)),
			"description" => $desc,
			"status" => $order_status
		];
		$json_data_local = json_encode($data_local);
		//$log->log( 'Data to check', 'Data String: '.$json_data_local); //log data before hash create. this line can be commented
		// create HASH for check
		$hash_local = hash('sha256', $json_data_local.$SecretKey);
        // Hash check
		if( !hash_equals($hash_local, $hash_received) ){
		    $log->log( 'Invalid HASH:', 'HASHs not equal', $hash_local);
			return new WP_Error( 'error', 'Invalid HASH', array( 'status' => 404, 'hash' => $hash_local ));
		}

		$response = [];
        // Order status check
		if ( $order->has_status('completed') || $order->has_status('processing')) {
		   
			error_log( print_r( 'No need to do changes for order #' . $order_id_to_check, true ) );
			return;
		}
       
		$order->set_status('processing',$desc);
		/**
		 * Added this due to metorik not syncing up correctly with woocommerce
		 * Updates the last modified time for both order.
		 */
		$order->set_date_modified( time() );
		/*******/
		$order->save();
		//$total = $order->get_formatted_order_total();
		$currency = get_woocommerce_currency_symbol();
		if($currency=='&#36;'){
		    $currency = '$';	    
		}
		$total = $currency.' '.$order->get_total();
		$time = $order->get_date_modified();
		setEmail($order_id, $total, $idAccount_to_check);
        wc_log_pp($file_name, $order_id, $total,'processing', $time);
		$response['status'] = 'success';
		$response['description'] = $desc;
		$res = new WP_REST_Response($response);
		$res->set_status(200);
		return $res;
	}

function amw_update_func_2(WP_REST_Request $request ){
		$queryParams = $request->get_json_params();
		$log = new WC_Logger();
		$log->log( 'Test callback staging_2', 'request:'.wc_print_r($queryParams,true));

		$hash_received = $queryParams['hash'];
		$order_id_to_check = (int)$queryParams['txid'];
		$action = $queryParams['action'];
		$order_status = $queryParams['status'];
		
		$desc = '';
		if(!empty($queryParams['description'])){
			$desc = $queryParams['description'];
		}

		if(empty($order_id_to_check) && $order_status == 1){
			return new WP_Error( 'error', 'Invalid Request', array( 'status' => 404 ) );
		}

		$order = wc_get_order( $order_id_to_check );

		if(empty($order)){
			error_log( print_r( 'not correct order_id '.$order_id_to_check, true ) );
			return new WP_Error( 'error', 'Invalid order id', array( 'status' => 404 ) );
		}

		// staging mode
		$SecretKey = 'j3BWHjad5JBG';
		$idAccount = 1017;
		$idMethod = 102;
		// production mode
		$SecretKey = 'h7MhP9GeaB6R';
        $idAccount = 1027;
        $idMethod = 113;
		$order_id = $order->get_id();
		$clientClientId = $order->get_user_id();
		$order_number = $order->get_order_number();
		$amount = number_format((float)$order->get_total(), 2, '.', '');

		$data_local = [
			"action"=> $action,
			"idaccount" => $idAccount,
			"idmethod" => $idMethod,
			"txid" => strval($order_number),
			"clientid" => $clientClientId,
			"amount" => $amount,
			"description" => $desc,
			"status" => $order_status
		];
		$json_data_local = json_encode($data_local);
		$log->log( 'Data to check 2', 'Data String: '.$json_data_local);
		$hash_local = hash('sha256', $json_data_local.$SecretKey);

		if( !hash_equals($hash_local, $hash_received) ){
		    $log->log( 'Invalid HASH 2:', 'HASHs not equal');
			return new WP_Error( 'error', 'Invalid HASH', array( 'status' => 404 ) );
		}

		$response = [];

		if ( $order->has_status('completed') || $order->has_status('processing')) {
			error_log( print_r( 'No need to do changes for order #' . $order_id_to_check, true ) );
			return;
		}

		$order->set_status('processing',$desc);
		/**
		 * Added this due to metorik not syncing up correctly with woocommerce
		 * Updates the last modified time for order.
		 */
		$order->set_date_modified( time() );
		/*******/
		$order->save();
        $total = $order->get_formatted_order_total();
		setEmail($order_id,$total, $idAccount_to_check);
 
		$response['status'] = 'success';
		$response['description'] = $desc;
		$res = new WP_REST_Response($response);
		$res->set_status(200);
		return $res;
	}

function setEmail($order_id,$total,$account){
    
     if($account == 1023){
        $email = "vp5353@directexpay.com";
    } 
    
    $date = date('Y-m-d H:i:s');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $to = [get_option( 'admin_email' )];
    $subject = "Order #$order_id has been approved - $email";
    

    $message = "Order #$order_id has been approved. Changing order status from on-hold to processing $date - $total. Payment Email: $email";
    wp_mail( $to, $subject, $message, $headers );
}

function wc_log_pp($file_name, $order_id, $price, $order_status, $time){
    
    if( $order_status=='processing'){
        $message = 'Order #%s has been approved. Changing order status from on-hold to processing %s - %s';
        $text =  sprintf($message, $order_id,$time,$price);
    }else{
        $message = 'Order #%s has been create. Order status %s %s - %s';
        $text =  sprintf($message, $order_id, $order_status, $time,$price);
    }
    
    file_put_contents(WC_LOG_DIR_PP.$file_name, PHP_EOL . $text, FILE_APPEND);
}
 