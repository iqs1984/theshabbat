<?php 

$json = file_get_contents('php://input');
$action = json_decode($json, true);
if($action['payment_option']){
	$paid="Yes";
	//$stage_id='FINAL_INVOICE';
	$stage_id='EXECUTING';
}else{
	$paid="No";
	$stage_id='EXECUTING';
}

if(count($action)>0){
$queryUrl = 'https://theshabbat.bitrix24.com/rest/1/vn9oqmdtpbke51i4/crm.contact.add.json';
if($action['formid']==12){
	$stage_id='UC_HE6Z8R';
	$queryData = http_build_query(array(
 'fields' => array(
 "NAME" => $action['fieldname26'],
 "LAST_NAME" => $action['fieldname26'],
 "PHONE" => array(array("VALUE" =>$action['fieldname27'], "VALUE_TYPE" => "WORK" )),
 "EMAIL" => array(array("VALUE" => $action['fieldname133'], "VALUE_TYPE" => "WORK" )),
 "ADDRESS" => $action['fieldname138'].','.$action['fieldname135'].','.$action['fieldname139'].','.$action['fieldname140'],
 "UF_CRM_1676374990" => $action['fieldname138'].','.$action['fieldname135'].','.$action['fieldname139'].','.$action['fieldname140'], // Full ADDRESS
 "UF_CRM_1676375140" => $action['fieldname135'], //city
 "UF_CRM_1676375154" => $action['fieldname139'], //state
 "UF_CRM_1676375170" => $action['fieldname140'], //Postal Code
 "OPENED" => 'Y',
 "EXPORT" => 'Y',
 "COMMENTS" => $action['fieldname24'],
 "SOURCE_ID" => 'UC_7GD3FF',
 //"UF_CRM_1674718367" => $action['fieldname26'],
 //for first package
 "UF_CRM_1679034311" => $action['fieldname47'], // Adults (April 4-9)
 "UF_CRM_1679034366" => $action['fieldname161'], // Kids 4-12 (April 4-9)
 "UF_CRM_1675246373" => $action['fieldname48'], // Teen (April 4-9)
 "UF_CRM_1677131876" => $action['fieldname141'], // Single Occupancy (April 4-9)
  
  
 "UF_CRM_1674718846" => $action['fieldname54'], // Rooms Included
 "UF_CRM_1675246652" => $action['fieldname51'], // Extra Rooms
 "UF_CRM_1675246681" => $action['fieldname162'], // Suit Upgrade
 "UF_CRM_1674718880" => $action['fieldname154'], // Cost (After Discounts and Taxes, Gratuity, and Fees)
 "UF_CRM_1677134138" => $action['fieldname158'], // Referral Code
 "UF_CRM_1677134249" => $action['fieldname153'], // Cost of Package
 "UF_CRM_1677134319" => $action['fieldname147'], // Taxes & Gratuity
 "UF_CRM_1675246721" => $action['fieldname154'], //Total Cost of Package
 "UF_CRM_1677134609" => $paid, //Payment Status
 //"UF_CRM_1674718942" => 'question',
 //"UF_CRM_1674719487" => 'paid',

 )
 
 ));
	
}elseif($action['formid']==13){
	$stage_id='UC_ZC4UPN';
		$queryData = http_build_query(array(
 'fields' => array(
 "NAME" => $action['fieldname26'],
 "LAST_NAME" => $action['fieldname26'],
 "PHONE" => array(array("VALUE" =>$action['fieldname27'], "VALUE_TYPE" => "WORK" )),
 "EMAIL" => array(array("VALUE" => $action['fieldname133'], "VALUE_TYPE" => "WORK" )),
 "OPENED" => 'Y',
 "EXPORT" => 'Y',
 "SOURCE_ID" => '7',
 //for first package
 "UF_CRM_1680067308" => $action['fieldname47'], //Adults April 9 EXCURSION
 "UF_CRM_1680067377" => $action['fieldname52'], //Teens 13+ April 9 EXCURSION
 "UF_CRM_1680067406" => $action['fieldname48'], //Kids <12 April 9 EXCURSION
 //for second package
 "UF_CRM_1680067850" => $action['fieldname120'], //Adults April 10 EXCURSION
 "UF_CRM_1680067877" => $action['fieldname121'], //Teens 13+ April 10 EXCURSION
 "UF_CRM_1680067908" => $action['fieldname122'], //Kids <12 April 10 EXCURSION
 //for third package
 "UF_CRM_1680067965" => $action['fieldname124'], //Adults April 11 EXCURSION
 "UF_CRM_1680067993" => $action['fieldname125'], //Teens 13+ April 11 EXCURSION
 "UF_CRM_1680068014" => $action['fieldname126'], //Kids <12 April 11 EXCURSION
 
 
 "UF_CRM_1680068075" => $action['fieldname169'], // I would like to speak about :
 "UF_CRM_1680068091" => $action['fieldname176'], // I would like to lein Torah on :
 "UF_CRM_1680068108" => $action['fieldname175'], // I would like an aliyah on :
 "UF_CRM_1680068129" => $action['fieldname174'], // I would like to work with kids :
  
  
 "UF_CRM_1680068147" => $action['fieldname178'], // I would like to volunteer :
 "UF_CRM_1680068166" => $action['fieldname180'], // I have dietary restrictions / requests :
 "UF_CRM_1680068181" => $action['fieldname184'], // I have a birthday in my party / group :
 "UF_CRM_1680068197" => $action['fieldname183'], // I have a special needs child :
 )
 )
 
 );
$action['fieldname154']=0;	
}elseif($action['formid']==14){
	$stage_id='UC_WFDPSA';
		$queryData = http_build_query(array(
 'fields' => array(
 "NAME" => $action['fieldname26'],
 "LAST_NAME" => $action['fieldname26'],
 "PHONE" => array(array("VALUE" =>$action['fieldname188'], "VALUE_TYPE" => "WORK" )),
 "EMAIL" => array(array("VALUE" => $action['fieldname133'], "VALUE_TYPE" => "WORK" )),
 "OPENED" => 'Y',
 "EXPORT" => 'Y',
 "SOURCE_ID" => '8',
 "COMMENTS" => $action['fieldname187'],
 "UF_CRM_1676374990" => $action['fieldname189'],
 )
 )
 
 );
$action['fieldname154']=$action['fieldname186'];	
}else{
 $queryData = http_build_query(array(
 'fields' => array(
 "NAME" => $action['fieldname26'],
 "LAST_NAME" => $action['fieldname26'],
 "PHONE" => array(array("VALUE" =>$action['fieldname27'], "VALUE_TYPE" => "WORK" )),
 "EMAIL" => array(array("VALUE" => $action['fieldname133'], "VALUE_TYPE" => "WORK" )),
 "ADDRESS" => $action['fieldname138'].','.$action['fieldname135'].','.$action['fieldname139'].','.$action['fieldname140'],
 "UF_CRM_1676374990" => $action['fieldname138'].','.$action['fieldname135'].','.$action['fieldname139'].','.$action['fieldname140'], // Full ADDRESS
 "UF_CRM_1676375140" => $action['fieldname135'], //city
 "UF_CRM_1676375154" => $action['fieldname139'], //state
 "UF_CRM_1676375170" => $action['fieldname140'], //Postal Code
 "OPENED" => 'Y',
 "EXPORT" => 'Y',
 "COMMENTS" => $action['fieldname24'],
 "SOURCE_ID" => 'UC_7GD3FF', 
 //"UF_CRM_1674718367" => $action['fieldname26'],
 //for first package
 "UF_CRM_1675246336" => $action['fieldname47'], // Adults (April 4-9)
 "UF_CRM_1675246355" => $action['fieldname52'], // Kids 4-12 (April 4-9)
 "UF_CRM_1675246373" => $action['fieldname48'], // Teen (April 4-9)
 "UF_CRM_1677131876" => $action['fieldname141'], // Single Occupancy (April 4-9)
  //for second package
 "UF_CRM_1675246519" => $action['fieldname120'], // Adults (April 10-14)
 "UF_CRM_1675246543" => $action['fieldname121'], // Kids 4-12 (April 10-14)
 "UF_CRM_1675246559" => $action['fieldname122'], // Teen (April 10-14)
 "UF_CRM_1677132048" => $action['fieldname142'], // Single Occupancy (April 10-14)
 //for third package
 "UF_CRM_1675246575" => $action['fieldname124'], // Adults (April 4-14)
 "UF_CRM_1675246594" => $action['fieldname125'], // Kids 4-12 (April 4-14)
 "UF_CRM_1675246627" => $action['fieldname126'], // Teen (April 4-14)
 "UF_CRM_1677132661" => $action['fieldname143'], // Single Occupancy (April 4-14)
 //for fourth package
 "UF_CRM_1677132737" => $action['fieldname128'], // Adults (April 4-16)
 "UF_CRM_1677132835" => $action['fieldname129'], // Kids 4-12 (April 4-16)
 "UF_CRM_1677133437" => $action['fieldname130'], // Teen (April 4-16)
 "UF_CRM_1677133490" => $action['fieldname144'], // Single Occupancy (April 4-16)
  
 "UF_CRM_1674718846" => $action['fieldname54'], // Rooms Included
 "UF_CRM_1675246652" => $action['fieldname51'], // Extra Rooms
 "UF_CRM_1675246698" => $action['fieldname104'], // Private Seder
 "UF_CRM_1674718880" => $action['fieldname154'], // Cost (After Discounts and Taxes, Gratuity, and Fees)
 "UF_CRM_1677134138" => $action['fieldname158'], // Referral Code
 "UF_CRM_1677134249" => $action['fieldname153'], // Cost of Package
 "UF_CRM_1677134319" => $action['fieldname147'], // Taxes & Gratuity
 "UF_CRM_1675246721" => $action['fieldname154'], //Total Cost of Package
 "UF_CRM_1677134609" => $paid, //Payment Status
 //"UF_CRM_1674718942" => 'question',
 //"UF_CRM_1674719487" => 'paid',

 )
 
 ));
}
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
}
 if(@$result['result']){
	$dealqueryData = http_build_query(array(
 'fields' => array(
 "CONTACT_ID" => $result['result'],
 "STAGE_ID" => $stage_id,
 "OPPORTUNITY" => $action['fieldname154'],
 "CURRENCY_ID" => 'USD',

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