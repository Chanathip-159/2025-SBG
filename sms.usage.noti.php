<?php
  require_once("element/_0_php_settings.php"); # error memory session setup
  require_once("element/_4_functions_db.php"); # db connect and db functions
  require_once(__DIR__.'/../php.lib/api.sms.send.direct.php');
  require_once(__DIR__.'/api/bulk.api.engine.php');
 

  // defining function
  function cal_percentage($num_amount, $num_total) {
    $count1 = $num_amount / $num_total;
    $count2 = $count1 * 100;
    $count = number_format($count2, 0);
    return $count;
  }

  $arr_conditions1 = [];
  $arr_conditions1_2 = [];
  $arr_conditions2 = [];
  $arr_conditions2_2 = [];
  $arr_conditions3 = [];
  $str_udt = [];

  $dateCheck = Date('Y-m-d', strtotime('-30 days'));
  $monthlyCheck = Date('Y-m');

  $sql = "SELECT CUSTOMER_Username, CUSTOMER_NeedDr, CUSTOMER_ExpireDate, CUSTOMER_AccountUsage, CUSTOMER_CurrentUsage, CUSTOMER_MonthlyUsage, CUSTOMER_Telephone, CUSTOMERS_RemainUsage FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Telephone IS NOT NULL";
  $sql_monthly = "SELECT SUMRPT_Username, SUM(SUMRPT_Count) AS SUMUsage FROM [SBG].[dbo].[SUM_REPORTS] WHERE SUMRPT_Date LIKE '$monthlyCheck%' GROUP BY SUMRPT_Username";

  if (($stmt = querySqlEx($sql)) == false) {
    //
  } else {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $username = $row['CUSTOMER_Username'];
      $NeedDR = $row['CUSTOMER_NeedDr'];
      $accUsage = $row['CUSTOMER_AccountUsage'];
      $curUsage = $row['CUSTOMER_CurrentUsage'];
      $remainUsage = $row['CUSTOMERS_RemainUsage'];
      $monthlyUsage = $row['CUSTOMER_MonthlyUsage'];
      $tel = $row['CUSTOMER_Telephone'];

      // [1] Check expire date conditions ===================
      $expireDate = date_create($row['CUSTOMER_ExpireDate']);
      $checkDate = date_create(Date('Y-m-d'));
      $result = date_diff($checkDate, $expireDate);
      $date_diff = $result->format("%a");
      // $date_diff = $result->format("Total number of days: %a.");

      if ($expireDate > $checkDate && $date_diff == 30) {
        $arr_conditions1[] = [
          'username' => $username,
          'tel' => $tel,
          'checkdate' => $checkDate,
          'exp' => $row['CUSTOMER_ExpireDate'],
          'diff' => $date_diff
        ];
      } else if ($expireDate > $checkDate && $date_diff == 5) {
        $arr_conditions1_2[] = [
          'username' => $username,
          'tel' => $tel,
          'checkdate' => $checkDate,
          'exp' => $row['CUSTOMER_ExpireDate'],
          'diff' => $date_diff
        ];
      }   
      // [1] Check expire date conditions ===================
      
      // [2] Check account usage conditions ===================
      if($accUsage == -1 || $curUsage == 0) {
        // CUSTOMER_Telephone
      } else {
        $cal = cal_percentage($remainUsage, $accUsage).'%<br/>'; 
        
        if ($cal == 95) { // remain usage = 5%
          $arr_conditions2[] = [
            'username' => $username,
            'tel' => $tel
          ];
        }

        if ($cal == 97) { // remain usage = 3%
          $arr_conditions2_2[] = [
            'username' => $username,
            'tel' => $tel
          ];
        }
      }
      // [2] Check account usage conditions ===================

      // [3] Check monthly usage conditions ===================
      $sqlFrom = " FROM [SBG].[dbo].[SUM_REPORTS] 
                   JOIN [SBG].[dbo].[CUSTOMERS] ON SUM_REPORTS.SUMRPT_Username = CUSTOMERS.CUSTOMER_Username
                   WHERE SUMRPT_Date LIKE '$monthlyCheck%' AND SUMRPT_Username = '".$row['CUSTOMER_Username']."'
                   GROUP BY SUMRPT_Username";

      if ($NeedDR == '0') { // NO DR
        $dr = 'NO DR';
        $sqlMonthlyCheckDR = "SELECT SUMRPT_Username, SUM(SUMRPT_Count) AS SUMUsage" . $sqlFrom;

      } else if ($NeedDR == '1') { // USE DR
        $dr = 'USE DR';
        $sqlMonthlyCheckDR = "SELECT SUMRPT_Username, SUM(SUMRPT_Success) AS SUMUsage" . $sqlFrom;
      }
      // echo $sqlMonthlyCheckDR;
      if($monthlyUsage != -1) {
        if (($stmt_checkDR = querySqlEx($sqlMonthlyCheckDR)) == false) {
          //
        } else {
          while ($row = $stmt_checkDR->fetch(PDO::FETCH_ASSOC)) {
            $monthly_SUMUsage = $row['SUMUsage'];
            $cal = cal_percentage($monthly_SUMUsage, $monthlyUsage).'%<br/>';

            if ($cal >= 95) { // remain usage <= 5%
              $arr_conditions3[] = [
                'username' => $username,
                'tel' => $tel
              ];
            }
          }
        }
      } else {
        //
      }
      // [3] Check monthly usage conditions ===================
    }
  }

  echo "<pre>";
  print_r($arr_conditions1);
  echo "</pre>";
  echo "<pre>";
  print_r($arr_conditions1_2);
  echo "</pre>";
  echo "<pre>";
  print_r($arr_conditions2);
  echo "</pre>";
  echo "<pre>";
  print_r($arr_conditions2_2);
  echo "</pre>";
  echo "<pre>";
  print_r($arr_conditions3);
  echo "</pre>";

  foreach($arr_conditions1 as $row) {
    // echo $row['username'];
    // echo $row['tel'];

    try {
      error_reporting(E_ERROR);
      
      $msisdn = substr($row['tel'], 1);        
      $msisdn_sms = '66'. $msisdn;

      $msg_header = "my SMS Bulk";

      $msg_sms = "เรียนผู้ใช้บริการ my SMS Bulk (".$row['username'].")

บัญชีของท่านจะหมดอายุการใช้งานในวันที่ (".$row['exp'].") กรุณาติดต่อเจ้าหน้าที่ฝ่ายขายเพื่อยืนยันการใช้งาน";

      // $msg_db = "noti:1|desc:<30days|msisdn:$msisdn_sms|user:".$row['username']." ";

      $send_data = Array();
      $send_data['sms_client_ip'] = getClientIp();
      $send_data['sms_type']="submit"; 
      $send_data['sms_service_type'] = "SBG";
      $send_data['sms_charge_account'] = "admin";
      $send_data['sms_sender'] = "my SMS Bulk";
      $send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
      $send_data['sms_langauge'] = "T";
      $send_data['sms_message'] = $msg_sms;
  
      // $send_test_result = sendSmsToDb($send_data);
    }
    catch (exception $e) {
      header("Status: 500");
      header("Content-Type: text/plain" );
      // echo 'Message: ' .$e->getMessage();
    }
  }

  foreach($arr_conditions1_2 as $row) {
    // echo $row['username'];
    // echo $row['tel'];

    try {
      error_reporting(E_ERROR);
      
      $msisdn = substr($row['tel'], 1);        
      $msisdn_sms = '66'. $msisdn;

      $msg_header = "my SMS Bulk";

      $msg_sms = "เรียนผู้ใช้บริการ my SMS Bulk (".$row['username'].")

บัญชีของท่านจะหมดอายุการใช้งานในอีก 5 วัน (".$row['exp'].") กรุณาติดต่อเจ้าหน้าที่ฝ่ายขายเพื่อยืนยันการใช้งาน";

      // $msg_db = "noti:1|desc:<30days|msisdn:$msisdn_sms|user:".$row['username']." ";

      $send_data = Array();
      $send_data['sms_client_ip'] = getClientIp();
      $send_data['sms_type']="submit"; 
      $send_data['sms_service_type'] = "SBG";
      $send_data['sms_charge_account'] = "admin";
      $send_data['sms_sender'] = "my SMS Bulk";
      $send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
      $send_data['sms_langauge'] = "T";
      $send_data['sms_message'] = $msg_sms;
  
      // $send_test_result = sendSmsToDb($send_data);
    }
    catch (exception $e) {
      header("Status: 500");
      header("Content-Type: text/plain" );
      // echo 'Message: ' .$e->getMessage();
    }
  }

  foreach($arr_conditions2 as $row) {
    // echo $row['username'];
    // echo $row['tel'];
    try {
      error_reporting(E_ERROR);
      
      $msisdn = substr($row['tel'], 1);        
      $msisdn_sms = '66'. $msisdn;

      $msg_header = "my SMS Bulk";

      $msg_sms = "เรียนผู้ใช้บริการ my SMS Bulk (".$row['username'].")

ขณะนี้จำนวนการใช้งาน SMS เหลือต่ำกว่า 5% กรุณาติดต่อเจ้าหน้าที่ฝ่ายขายเพื่อยืนยันการใช้งาน";

      // $msg_db = "noti:2|desc:<5%usage|msisdn:$msisdn_sms|user:".$row['username']." ";

      $send_data = Array();
      $send_data['sms_client_ip'] = getClientIp();
      $send_data['sms_type']="submit"; 
      $send_data['sms_service_type'] = "SBG";
      $send_data['sms_charge_account'] = "admin";
      $send_data['sms_sender'] = "my SMS Bulk";
      $send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
      $send_data['sms_langauge'] = "T";
      $send_data['sms_message'] = $msg_sms;
  
      // $send_test_result = sendSmsToDb($send_data);
    }
    catch (exception $e) {
      header("Status: 500");
      header("Content-Type: text/plain" );
      // echo 'Message: ' .$e->getMessage();
    }
  }

  foreach($arr_conditions2_2 as $row) {
    // echo $row['username'];
    // echo $row['tel'];
    try {
      error_reporting(E_ERROR);
      
      $msisdn = substr($row['tel'], 1);        
      $msisdn_sms = '66'. $msisdn;

      $msg_header = "my SMS Bulk";

      $msg_sms = "เรียนผู้ใช้บริการ my SMS Bulk (".$row['username'].")

ขณะนี้จำนวนการใช้งาน SMS เหลือต่ำกว่า 3% กรุณาติดต่อเจ้าหน้าที่ฝ่ายขายเพื่อยืนยันการใช้งาน";

      // $msg_db = "noti:2|desc:<5%usage|msisdn:$msisdn_sms|user:".$row['username']." ";

      $send_data = Array();
      $send_data['sms_client_ip'] = getClientIp();
      $send_data['sms_type']="submit"; 
      $send_data['sms_service_type'] = "SBG";
      $send_data['sms_charge_account'] = "admin";
      $send_data['sms_sender'] = "my SMS Bulk";
      $send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
      $send_data['sms_langauge'] = "T";
      $send_data['sms_message'] = $msg_sms;
  
      // $send_test_result = sendSmsToDb($send_data);
    }
    catch (exception $e) {
      header("Status: 500");
      header("Content-Type: text/plain" );
      // echo 'Message: ' .$e->getMessage();
    }
  }

  foreach($arr_conditions3 as $row) {
    // echo $row['username'];
    // echo $row['tel'];
    try {
      error_reporting(E_ERROR);
      
      $msisdn = substr($row['tel'], 1);        
      $msisdn_sms = '66'. $msisdn;

      $msg_header = "my SMS Bulk";

  //     $msg_sms = "เรียน คุณ ".$row['username']."
  // <br />
  // ขณะนี้ Account ของท่านเหลือโควต้าการใช้งาน sms ประจำเดือนต่ำกว่า 5% กรุณาติดต่อผู้ดูแลระบบเพื่อขยายโควต้าการใช้งาน
  // <br />";

      $msg_sms = "เรียนผู้ใช้บริการ my SMS Bulk (".$row['username'].")

ขณะนี้จำนวนการใช้งาน SMS ประจำเดือนเหลือต่ำกว่า 5% กรุณาติดต่อเจ้าหน้าที่ฝ่ายขายเพื่อยืนยันการใช้งาน";

      // $msg_db = "noti:3|desc:<5%montly|msisdn:$msisdn_sms|user:".$row['username']." ";

      $send_data = Array();
      $send_data['sms_client_ip'] = getClientIp();
      $send_data['sms_type']="submit"; 
      $send_data['sms_service_type'] = "SBG";
      $send_data['sms_charge_account'] = "admin";
      $send_data['sms_sender'] = "my SMS Bulk";
      $send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
      $send_data['sms_langauge'] = "T";
      $send_data['sms_message'] = $msg_sms;
  
      // $send_test_result = sendSmsToDb($send_data);
    }
    catch (exception $e) {
      header("Status: 500");
      header("Content-Type: text/plain" );
      // echo 'Message: ' .$e->getMessage();
    }
  }

  // echo "<pre>";
  // print_r($send_test_result);
  // echo "</pre>";
?>