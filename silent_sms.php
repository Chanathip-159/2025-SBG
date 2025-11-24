<?php
  require_once("element/_0_php_settings.php"); # error memory session setup
  require_once("element/_4_functions_db.php"); # db connect and db functions
  require_once(__DIR__.'/../php.lib/api.sms.send.direct.php');
  require_once(__DIR__.'/api/bulk.api.engine.php');

  define('_APP_ID', 3); // [ADMINAPPLEVEL_AppId] = [ADMINAPPinfo_Id] = 3 = SBG
  $php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];

  $times = date('Y-m-d H:i:s');

  $msisdn = ["66910600460", "66619817484", "66635616555", "66906180967", "66830131477"];

  for ($i = 0; $i < count($msisdn); $i++) {
    $send_data = Array();
    $send_data['sms_client_ip'] = "10.100.141.163";
    $send_data['sms_type']="submit"; 
    $send_data['sms_service_type'] = "SBG";
    $send_data['sms_charge_account'] = "admin";
    $send_data['sms_sender'] = "TestSMS";
    $send_data['sms_receiver'] = $msisdn[$i];
    $send_data['sms_langauge'] = "T";
    $send_data['sms_message'] = "TestSMS Account SBG : ทดสอบส่งข้อมูลสั้น \n".$times;

    $send_test_result = sendSmsToDb($send_data);
  }
?>