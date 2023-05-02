<?php 


const WEBHOOK_SECRET = 'dd8ad0aaeeed237d35f0d2709cf19afab1d0c1029555fcf6fd0d69ed9f818f723e759298c7f0e5cc6579ede33fc7a4e0';
function verifySignature ($body, $signature) {
    $digest = hash_hmac('sha1', $body, WEBHOOK_SECRET);
    return $signature === $digest ;
}
if (!verifySignature(file_get_contents('php://input'), $_SERVER['HTTP_X_TAWK_SIGNATURE'])) {
    // verification failed
}else{ 
// verification success


$json = file_get_contents('php://input');
//$json = '{"chatId":"198bbee0-ba67-11ed-b336-df4d66de8806","visitor":{"name":"test test","city":"unknown","country":"IN","email":"ajeet.iquincesoft@gmail.com"},"message":{"sender":{"type":"visitor"},"text":"Name (required) : test test\r\nEmail (required) : ajeet.iquincesoft@gmail.com\r\nPhone (numbers only, no special characters) : 8869030234","type":"msg"},"time":"2023-03-04T08:32:53.634Z","event":"chat:start","property":{"id":"63a08a22b0d6371309d529e2","name":"The Shabbat Inc"}}';
$action = json_decode($json, true);
 
//print_r($action['visitor']['name']);
//print_r(@explode(':',$action['message']['text'])[3]);
//die;
$queryUrl = 'https://theshabbat.bitrix24.com/rest/1/vn9oqmdtpbke51i4/crm.contact.add.json';
 $queryData = http_build_query(array(
 'fields' => array(
 "NAME" => $action['visitor']['name'],
 "LAST_NAME" => $action['visitor']['name'],
 "PHONE" => array(array("VALUE" =>@explode(':',$action['message']['text'])[3], "VALUE_TYPE" => "WORK" )),
 "EMAIL" => array(array("VALUE" => $action['visitor']['email'], "VALUE_TYPE" => "WORK" )),
 "COMMENTS" => 'chat',
 "SOURCE_ID" => 'Live chat - Open Channel',

 )
  
 ));
$curl = curl_init();
 curl_setopt_array($curl, array(
 CURLOPT_SSL_VERIFYPEER => 0,
 CURLOPT_POST => 1,
 CURLOPT_HEADER => 0,
 CURLOPT_RETURNTRANSFER => 1,
 CURLOPT_URL => $queryUrl,
 CURLOPT_POSTFIELDS => $queryData,
 ));

 $result = curl_exec($curl);
 curl_close($curl);

  $result = json_decode($result, 1);
 if(@$result['result']){
	$dealqueryData = http_build_query(array(
 'fields' => array(
 "CONTACT_ID" => $result['result'],
 "STAGE_ID" => 'UC_3FV1SY',
 "OPPORTUNITY" => 0,
 "CURRENCY_ID" => 'USD'

 )
 
 )); 
 $dealqueryUrl = 'https://theshabbat.bitrix24.com/rest/1/9qn51z3getlmc0ea/crm.deal.add.json';
 $curl = curl_init();
 curl_setopt_array($curl, array(
 CURLOPT_SSL_VERIFYPEER => 0,
 CURLOPT_POST => 1,
 CURLOPT_HEADER => 0,
 CURLOPT_RETURNTRANSFER => 1,
 CURLOPT_URL => $dealqueryUrl,
 CURLOPT_POSTFIELDS => $dealqueryData,
 ));

 $result = curl_exec($curl);
 curl_close($curl);

  $dresult = json_decode($result, 1);
 }
}
 //print_r($result);