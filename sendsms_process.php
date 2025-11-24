<?php
	try {
		require_once ('nusoap.php');
		
		// $endpoint = 'https://10.99.210.48:18310/SendSmsService/services/SendSms';
		// $endpoint = 'https://10.99.214.44:18310/SendSmsService/services/SendSms';
		$endpoint = 'http://10.100.141.195/service/SMSWebServiceEngine.php';
		
		$times = date('YmdHis');
		// $pass = 'USSDWeb'.'Ussd@123'.$times;
		// $pass_md5 = md5($pass);
		
		$msg = '<?xml version="1.0" encoding="UTF-8" ?>
						<Envelope>
						<Header/>
						<Body>
						<sendSMS>
						<user>adminalert</user>
						<pass>catcdma2000</pass>
						<from>TestSMS</from>
						<target>66910600460</target>
						<mess>test 1 22:40 2024-02-05 ทดสอบข้อความสั้น</mess>
						<lang>T</lang>
						</sendSMS>
						</Body>
						</Envelope>
					';

					$msg = '<soapenv:Envelope
					xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
					xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1"
					xmlns:loc="http://www.csapi.org/schema/parlayx/sms/send/v2_2/local">
					<soapenv:Header>
					<v2:RequestSOAPHeader>
					<v2:spId>USSDWeb</v2:spId>
					<v2:spPassword>'.$pass_md5.'</v2:spPassword>
					<v2:serviceId>35000001000001</v2:serviceId>
					<v2:timeStamp>'.$times.'</v2:timeStamp>
					</v2:RequestSOAPHeader>
					</soapenv:Header>
					<soapenv:Body>
					<loc:sendSms>
					<loc:addresses>'.$msisdn_sms.'</loc:addresses>
					<loc:senderName>myByCAT</loc:senderName>
					<loc:message>'.$msg_sms.'</loc:message>
					</loc:sendSms>
					</soapenv:Body>
					</soapenv:Envelope>
					';


	
		$ws_client = new nusoap_client($endpoint, false);
			
		//$ws_client->setUseCURL(false);
		
		$ws_client->soap_defencoding = 'UTF-8';
		$ws_client->decode_utf8 = false;
		
		$ws_result = $ws_client->send($msg,$endpoint);
		
		$req = $ws_client->request;
		$res = $ws_client->response;	
		
		//echo '<br>req: ' . $req;
		//echo '<br>res: ' . $res;


		// Check for a fault
		if ($ws_client->fault) {
			//http_response_code(500);
			header("Status: 500");
			//echo '<h2>Fault</h2><pre>';
			// print_r($result);
			//echo '</pre>';
		} else {
			// Check for errors
			$err = $ws_client->getError();
			if ($err) {
				//http_response_code(500);
				header("Status: 500");
				// Display the error
				//echo '<h2>Error</h2><pre>' . $err . '</pre>';
				echo $err;
			} else {
				// Display the result
				//echo '<h2>Result</h2><pre>';
				// print_r($ws_result);
				//echo '</pre>';
			}
		}
		
	}
	catch (exception $e) {
		//http_response_code(500);
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' . $e->getMessage();
	}
?>