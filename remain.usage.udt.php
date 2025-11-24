<?php
  require_once("element/_0_php_settings.php"); # error memory session setup
  require_once("element/_4_functions_db.php"); # db connect and db functions
  require_once(__DIR__.'/../php.lib/api.sms.send.direct.php');
  require_once(__DIR__.'/api/bulk.api.engine.php');

  $sql = "SELECT CUSTOMER_AccountUsage, CUSTOMER_CurrentUsage, CUSTOMER_Username FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Telephone IS NOT NULL";

  if (($stmt = querySqlEx($sql)) == false) {
    //
  } else {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $username = $row['CUSTOMER_Username'];
      $accUsage = $row['CUSTOMER_AccountUsage'];
      $curUsage = $row['CUSTOMER_CurrentUsage'];

      if($accUsage == -1 || $curUsage == 0) {
        // CUSTOMER_Telephone
      } else {
        $update_remain = "UPDATE [SBG].[dbo].[CUSTOMERS] SET CUSTOMERS_RemainUsage = '".$curUsage."' WHERE CUSTOMER_Username = '".$username."' ";

        if (querySqlEx($update_remain) == false) {
          echo 'failed';
        } else {
          echo 'success';
        }
      }
    }
  }
?>