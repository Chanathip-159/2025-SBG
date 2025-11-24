<?php
// this page must be UTF-8 Encodeing
// need command "convert2ANSI" befor send SMS 
// this file receive content from www/html/api/api.ussd.directtrans.php
// verify by ussd code in message
#error_reporting(E_ALL & ~E_NOTICE);
ini_set('memory_limit',"1024M");
error_reporting(1);
ini_set('display_errors',1);
//######################################################################################################
define('_CEO',false); // echo or silent on web page
define('_DEB',false);
define('_FLAG_LOG_DB',_DEB);
define('_LOGN',"/var/php.isag.log/monitor.php");

define('_APP_ID',2); // [ADMINAPPLEVEL_AppId]=[ADMINAPPinfo_Id]=2=ISAG
#define('_SERVICE_ID',"0000001"); // default service id
define('_SES_USR_NAME',"session_user_name");
define('_SES_USR_TYP_ID',"session_user_type_id");

require_once('/var/php.lib/function.basic.php');
require_once('/var/php.lib/function.basic.db.php');
#=================================

# check db connection
if (!$dbh) {
	$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
	echoMsgConsole(__FILE__,__FUNCTION__,"app","dbh error",255,true,true,false);
	sleep(9999);
	exit();
}

$break = "BREAK";
$mode = $_GET['mode'];

$init = strtotime(date("Y-m-d H:".(roundToQuarterHour(time())).":00"));

define('_PRE', 300); // period

$labels = "";
$datas = "";
switch ($mode) {
	case "smsgw":
		$sql = "SELECT 
    DATEADD(MINUTE, (DATEDIFF(MINUTE, '20000101', incoming_datetime) / 5)*5, '20000101') AS 'title',
    count(*) AS 'counter'
FROM SMS.dbo.ALLSMSCDR
WHERE incoming_datetime BETWEEN '2020-05-15 00:00:00' AND '2020-05-16 00:00:00'-- > GETDATE()-1
GROUP BY
    DATEADD(MINUTE, (DATEDIFF(MINUTE, '20000101', incoming_datetime) / 5)*5, '20000101')
ORDER BY title ASC";
	break;
	case "smsgw_sbg":
		$sql = "SELECT 
    DATEADD(MINUTE, (DATEDIFF(MINUTE, '20000101', incoming_datetime) / 5)*5, '20000101') AS 'title',
    count(*) AS 'counter'
FROM SMS.dbo.ALLSMSCDR
WHERE incoming_datetime>GETDATE()-1 AND service_type='BUK'
GROUP BY
    DATEADD(MINUTE, (DATEDIFF(MINUTE, '20000101', incoming_datetime) / 5)*5, '20000101')
ORDER BY title ASC";
	break;
}


if ($stmt=querySqlEx($sql)) {
	while ($record=$stmt->fetch(PDO::FETCH_ASSOC)) {
		$labels .= "'".date("H:i", strtotime($record['title']))."',";
		$datas .= $record['counter'].",";
	}
}

echo substr($labels, 0, -1).$break.substr($datas, 0, -1);
//*/
function roundToQuarterHour($time) {
    $minutes = date('i', $time);
    return $minutes - ($minutes % 5);
}
?>