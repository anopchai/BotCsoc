<?php 
include("nusoap.php"); 
	/*Get Data From POST Http Request*/
	$datas = file_get_contents('php://input');
	/*Decode Json From LINE Data Body*/
	$deCode = json_decode($datas,true);

	$replyToken = $deCode['events'][0]['replyToken'];
	$userId = $deCode['events'][0]['source']['userId'];
	$text = $deCode['events'][0]['message']['text'];


	$messages = [];
	$messages['replyToken'] = $replyToken;
	
	
 $check_text = substr($text, 0, 1);
   if($check_text == "#"){

	
	      $circuit = substr($text,1);

	$client = new nusoap_client("http://1.179.246.37:8082/api_csoc_02/server_solarwinds_nodes.php?wsdl",true);
	$data = json_decode($client->call('circuitStatus', array('circuit' => $circuit)),true); 

	
$thai_day_arr=array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");
$thai_month_arr=array(
    "0"=>"",
    "1"=>"มกราคม",
    "2"=>"กุมภาพันธ์",
    "3"=>"มีนาคม",
    "4"=>"เมษายน",
    "5"=>"พฤษภาคม",
    "6"=>"มิถุนายน", 
    "7"=>"กรกฎาคม",
    "8"=>"สิงหาคม",
    "9"=>"กันยายน",
    "10"=>"ตุลาคม",
    "11"=>"พฤศจิกายน",
    "12"=>"ธันวาคม"                 
);

function thai_date($time){
    global $thai_day_arr,$thai_month_arr;
    $thai_date_return="วัน".$thai_day_arr[date("w",$time)];
    $thai_date_return.= "ที่ ".date("j",$time);
    $thai_date_return.=" ".$thai_month_arr[date("n",$time)];
    $thai_date_return.= " ".(date("Yํ",$time)+543);
    $thai_date_return.= "  ".date("H:i",$time)." น.";
    return $thai_date_return;
}
	
	foreach($data['result'] as $value){
	
		$circuit = $value['circuit'];
		$IP_ADDRESS = $value['IP_ADDRESS'];
		$status = $value['status'];
		$Service_Request = $value['Service_Request'];
		$DEPARTMENT = $value['DEPARTMENT'];
		$ADDRESS = $value['ADDRESS'];
		$DISTRICT = $value['DISTRICT'];
		$CITY = $value['CITY'];
		$PROVINCE = $value['PROVINCE'];
		$Location_Circuit = $value['Location_Circuit'];
		$Device_Type = $value['Device_Type'];
		$datetime=strtotime($value['EventTime']['date']);
		$datetime1 = thai_date($datetime);
		
		if($status == 1){
            $repair_status = " 🟢 Online ";
        }else{
            $repair_status = " 🔴 Offline ";
        }
            $messages['messages'][] = getFormatTextMessage("🔎 ผลการค้นหา  🔍\n เลขวงจร : " . $circuit . "\n SR Name : " . $Service_Request . "\n รหัสหมู่บ้าน : " . $DEPARTMENT . "\n ชื่อหมู่บ้าน :  " . $ADDRESS . "\n ตำบล :  " . $DISTRICT . "\n อำเภอ :  " . $CITY . "\n จังหวัด :  " . $PROVINCE . "\n IP : " . $IP_ADDRESS . "\n ประเภท  :  " . $Device_Type . "\n รหัสตู้  :  " . $Location_Circuit . "\n สถานะ  :  $repair_status \n ⏰ : " . $datetime1);   
        }
        
		
}

	$encodeJson = json_encode($messages);

	$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
  	$LINEDatas['token'] = "CKEudX8H/OqoOmEeOVsPDqjoYrclAQ5JsLKavgMJtnxO1xTmrC+toIu4yYRyDLWQzx4eP0fvqDN7cpGzjlpmx3lFYgMniulNMkO21OtDf2u5Jbh/CRtsmx5RWnqPuBX4RpuGPlnFMdx4Iw77QvIT8gdB04t89/1O/w1cDnyilFU=";

  	$results = sentMessage($encodeJson,$LINEDatas);

	/*Return HTTP Request 200*/
	http_response_code(200);

	function getFormatTextMessage($text)
	{
		$datas = [];
		$datas['type'] = 'text';
		$datas['text'] = $text;

		return $datas;
	}
	
	function sentMessage($encodeJson,$datas)
	{
		$datasReturn = [];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $datas['url'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encodeJson,
		  CURLOPT_HTTPHEADER => array(
		    "authorization: Bearer ".$datas['token'],
		    "cache-control: no-cache",
		    "content-type: application/json; charset=UTF-8",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		    $datasReturn['result'] = 'E';
		    $datasReturn['message'] = $err;
		} else {
		    if($response == "{}"){
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		    }else{
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		    }
		}

		return $datasReturn;
	}
?>
