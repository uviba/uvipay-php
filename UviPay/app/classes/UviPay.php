<?php

namespace Uviba;

class UviPay
{
  private static $privateKey;
  private static $apiDefError;
  
  public static function setPrivateKey($privateKey)
  {
    if (strlen($privateKey) < 20)
    throw new Exception('Correct private key is required to initialize UviPay client');
    
    self::$privateKey = $privateKey;
    self::$apiDefError = (object) array
    (
      'status'=>false,
      'error'=>array(
          'message' => "Sorry, some error happend",
          'code'    => "server_response",
          'type'    => "request"
      ),
    );
    /*
  self::$apiDefError = (object) array
    (
      'message' => "Sorry, some error happend",
      'code'    => "server_response",
      'type'    => "request"
    );
    */
  }

  public static function setApiKey($privateKey)
  {
    return self::setPrivateKey($privateKey);
  }

  
  /**
  * Send request to uviba api server
  * @param path Url path to send request
  * @param data Request parameters
  */
  public static function request($path, $data = array())
  {
    try
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"https://api.uviba.com/pay/v1/{$path}");
      curl_setopt($ch, CURLOPT_USERPWD, self::$privateKey);  
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close ($ch);
      //var_dump($response);
      $data = json_decode($response);
      $json = new ResponseObject;
      $json->set($data);

      return isset($json->status) ? $json : self::$apiDefError;
    }
    catch (Exception $e)
    {
      return self::$apiDefError;
    }
  }
  
  /**
  * @deprecated deprecated since v1.0. Use UviPay::request as alternative method
  */
  public static function APIRequest($url, $data = array())
  {
    return self::request($url, $data);
  }
  
  /**
  * Charge customer's credit card
  * @param data Request parameters
  */
  public static function charge($data = array())
  {
    if(!isset($data['token'])){
 
      if(isset($_GET['UvibaToken'])){
        $data['token']=$_GET['UvibaToken'];
      }else if(isset($_POST['UvibaToken'])){
        $data['token']=$_POST['UvibaToken'];
      }else if(isset($_GET['token'])){
        $data['token']=$_GET['token'];
      }else if(isset($_POST['token'])){
        $data['token']=$_POST['token'];
      }else{
        //not defined we will send error message
      }
    }
    return self::request('/charges', $data);
  }
  
  /**
  * Refund pervious charge
  * @param data Request parameters
  */
  public static function refund($charge_id,$data = array())
  { 
    if(is_array($charge_id)){
      //so it is params
      $data=array_merge($charge_id,$data);
    }else{
      $data['charge_id']=$charge_id;
    }
    return self::request('/refunds', $data);
  }
  
  /**
  * Get your current balance
  * @param data Request parameters
  */
  public static function get_balance($data = array())
  {
    return self::request('/balance', $data);
  }
  
  /**
  * Cancel running subscribtion
  * @param data Request parameters
  */
  public static function cancel_subscription($sub_id, $data = array())
  {
    return self::request("/subscriptions/{$sub_id}?action=delete", $data);
  }
  
  /**
  * Send payment to specified email & account
  * @param data Request parameters
  */
  public static function send_payment($amount,$data = array())
  {
    if(is_array($amount)){
      //so it is params
      $data=array_merge($amount,$data);
    }else{
      $data['amount']=$amount;
    }
    return self::request('/transfers?action=send_payment', $data);
  }

  /**
  * create link and put money in it.
  * @param data Request parameters
  */
  public static function create_paylink($amount,$data = array())
  {
    if(is_array($amount)){
      //so it is params
      $data=array_merge($amount,$data);
    }else{
      $data['amount']=$amount;
    }
    return self::request('/transfers?action=create_paylink', $data);
  }
  
  /**
  * Reverse sent payment
  * @param data Request parameters
  */
  public static function reverse_payment($data = array())
  {
    return self::request('/transfers?action=take_payment_back', $data);
  }

  /**
  * Verify sent webhook request
  * @param req_id Request id that sent to the webserver on webhook request
  * @param data Request parameters
  */  
  public static function verify_webhook($req_id, $data = array())
  {
    return self::request("/webhooks/?action=verify&request_id={$req_id}", is_array($data) ? $data : array('verify_for' => $data));   
  }
}