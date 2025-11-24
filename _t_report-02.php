<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# à¸—à¸”à¸ªà¸­à¸šà¸ à¸²à¸©à¸²à¹„à¸—à¸¢
# Edit zone
############################################
$dest_file = "report";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE] = $dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");																									// check permission from session name and session destination name

// start setup custom POST array to use only this page
if (strlen($_GET['Sid'])) {
	$_SESSION[$dest_file._SES_SERVI_ID] = $_GET['Sid'];
	$service_id = $_GET['Sid'];
}else{
	if (strlen($_SESSION[$dest_file._SES_SERVI_ID])) $service_id = $_SESSION[$dest_file._SES_SERVI_ID];
}
if (strlen($_GET['Mode'])) {
	$_SESSION[$dest_file._SES_MODE_TYP] = $_GET['Mode'];
	$mode_type = $_GET['Mode'];
}else{
	if (strlen($_SESSION[$dest_file._SES_MODE_TYP])) $mode_type = $_SESSION[$dest_file._SES_MODE_TYP];
}
// end setup custom POST array to use only this page

// start setup year to search bar with only exist data
#$sql_year_list = "SELECT DISTINCT YEAR(SerialNo_ExecDatetime) as yr FROM [ISAG].[dbo].[SerialNumbers]";
#if ($stmt = querySqlEx($sql_year_list)) {
#	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
#		$year_list[] = $row['yr'];
#	}
#}
// end setup year to search bar with only exist data

# select search bar file

// start set year by get last 5 year
for($i=date("Y")-5; $i<=date("Y"); $i++) {
	$year_list[] = $i;
}
// end set year by get last 5 year

// set $year_list=null use for hide search bar
#$year_list=null;
if ($year_list!==null) {
	// setup date range for search if $year_list is ok

	if (strlen($_POST[_STA_YEAR]) > 0) $start_year = $_POST[_STA_YEAR]; else $start_year = date('Y');
	if (strlen($_POST[_STA_MONTH]) > 0) $start_month = $_POST[_STA_MONTH]; else $start_month = date('m');
	if (strlen($_POST[_STA_DAY]) > 0) $start_day = $_POST[_STA_DAY]; else $start_day = date('d', strtotime('-7 days'));

	if (strlen($_POST[_END_YEAR]) > 0) $end_year = $_POST[_END_YEAR]; else $end_year = date('Y');
	if (strlen($_POST[_END_MONTH]) > 0) $end_month = $_POST[_END_MONTH]; else $end_month = date('m');
	if (strlen($_POST[_END_DAY]) > 0) $end_day = $_POST[_END_DAY]; else $end_day = date('d');

	// if (strlen($_POST[_STA_YEAR]) > 0) $start_year = $_POST[_STA_YEAR]; else $start_year=date('Y');
	// if (strlen($_POST[_STA_MONTH]) > 0) $start_month = $_POST[_STA_MONTH]; else $start_month=date('m');
	// if (strlen($_POST[_STA_DAY]) > 0) $start_day = checkRealLastDay($start_year,$start_month,$_POST[_STA_DAY]); else $start_day=1;
	// if (strlen($_POST[_END_YEAR]) > 0) $end_year = $_POST[_END_YEAR]; else if (strlen($_POST[_STA_YEAR]) > 0) $end_year=$start_year; else $end_year=date('Y');
	// if (strlen($_POST[_END_MONTH]) > 0) $end_month = $_POST[_END_MONTH]; else if (strlen($_POST[_STA_MONTH]) > 0) $end_month=$start_month; else $end_month=date('m');
	// if (strlen($_POST[_END_DAY]) > 0) $end_day = checkRealLastDay($end_year,$end_month,$_POST[_END_DAY]); else $end_day=date('t');

	// swarp start and stop
	$start_dt_search = genDateYmd($start_year,$start_month,$start_day);
	$end_dt_search = genDateYmd($end_year,$end_month,$end_day);

	if($start_dt_search > $end_dt_search)
	{
		$start_month = date('m', strtotime('-1 months'));
		$start_dt_search = genDateYmd($start_year,$start_month,$start_day);
	}

	// echo $start_dt_search;
	// echo '<br>';
	// echo $end_dt_search;
	// echo '<br>';

	if (strtotime($start_dt_search." 00:00:00.000")>strtotime($end_dt_search." 00:00:00.000")) {
		// echo "$start_dt_search $end_dt_search";
		$_POST[_STA_YEAR] = $end_year;
		$_POST[_STA_MONTH] = $end_month;
		$_POST[_STA_DAY] = $end_day;
		$_POST[_END_YEAR] = $start_year;
		$_POST[_END_MONTH] = $start_month;
		$_POST[_END_DAY] = $start_day;
		
		$start_year = $_POST[_STA_YEAR];
		$start_month = $_POST[_STA_MONTH];
		$start_day = $_POST[_STA_DAY];
		$end_year = $_POST[_END_YEAR];
		$end_month = $_POST[_END_MONTH];
		$end_day = $_POST[_END_DAY];
	}
	
	$start_dt_search = genDateYmd($start_year,$start_month,$start_day);
	$end_dt_search = genDateYmd($end_year,$end_month,$end_day);

	$sql_range_time = "BETWEEN '$start_dt_search 00:00:00.000' AND '$end_dt_search 23:59:59.999'";
}
if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

switch ($_SESSION[_SES_USR_TYP_ID]) {
	case _SES_USR_ROT:
	case _SES_USR_ADM:
	case _SES_USR_SYS_ADMIN:
		// do nothing
	break;
	default:
		$check_creater = "AND charge_account = '".$_SESSION[_SES_USR_NAME]."'";
	break;
}

// Dynamic search form process ----------------------------
$srchString_multi = "";

$byValue1 = 'LIKE'; // contain
$byValue2 = 'NOT LIKE'; // not contain
$byValue3 = '=';
$byValue4 = '!=';
$byValue5 = '>';
$byValue6 = '<';
$byValue7 = '>=';
$byValue8 = '<=';

if ($_POST['title_1']) {
	if (strlen($_POST['title_1'])) $title_1 = $_POST['title_1']; else $title = null;
	if (strlen($_POST['text_1'])) $text_1 = $_POST['text_1']; else $text_1 = null;
	if (strlen($_POST['selectBy_1'])) $selectBy_1 = $_POST['selectBy_1']; else $selectBy_1 = null;

	if ($_POST['selectBy_1'] == "1") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue1, $_POST['selectBy_1']);
	} else if ($_POST['selectBy_1'] == "2") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue2, $_POST['selectBy_1']);
	} else if ($_POST['selectBy_1'] == "3") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue3, $_POST['selectBy_1']);
	}	else if ($_POST['selectBy_1'] == "4") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue4, $_POST['selectBy_1']);
	} else if ($_POST['selectBy_1'] == "5") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue5, $_POST['selectBy_1']);
	} else if ($_POST['selectBy_1'] == "6") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue6, $_POST['selectBy_1']);
	}	else if ($_POST['selectBy_1'] == "7") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue7, $_POST['selectBy_1']);
	} else if ($_POST['selectBy_1'] == "8") {
		$convSelectBy_1 = str_replace($_POST['selectBy_1'], $byValue8, $_POST['selectBy_1']);
	}
	
	for ($i = 2; $i<=6; $i++) {
		$title = 'title_' . $i;
		$text = 'text_' . $i;
		$selectBy = 'selectBy_' . $i;
		$selectAndOr = 'selectAndOr_' . $i;

		if ($_POST[$selectBy] == "1") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue1, $_POST[$selectBy]);
		} else if ($_POST[$selectBy] == "2") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue2, $_POST[$selectBy]);
		} else if ($_POST[$selectBy] == "3") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue3, $_POST[$selectBy]);
		}	else if ($_POST[$selectBy] == "4") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue4, $_POST[$selectBy]);
		} else if ($_POST[$selectBy] == "5") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue5, $_POST[$selectBy]);
		} else if ($_POST[$selectBy] == "6") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue6, $_POST[$selectBy]);
		}	else if ($_POST[$selectBy] == "7") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue7, $_POST[$selectBy]);
		} else if ($_POST[$selectBy] == "8") {
			$convSelectBy = str_replace($_POST[$selectBy], $byValue8, $_POST[$selectBy]);
		}

		if($_POST[$selectBy] == "1" || $_POST[$selectBy] == "2") {
			$srchString_multi .= $_POST[$selectAndOr]." ".$_POST[$title]." ".$convSelectBy." '%".$_POST[$text]."%' ";
		}	else if (	$_POST[$selectBy] == "3" || 
								$_POST[$selectBy] == "4" ||
								$_POST[$selectBy] == "5" ||
								$_POST[$selectBy] == "6" ||
								$_POST[$selectBy] == "7" ||
								$_POST[$selectBy] == "8" ) {
			// $srchString_multi = $_POST[$checkAndOr]." ".$_POST[$title].$_POST[$selectBy].$_POST[$text];
			$srchString_multi .= $_POST[$selectAndOr]." ".$_POST[$title]." ".$convSelectBy." '".$_POST[$text]."' ";
		}
	}

	if($_POST['selectBy_1'] == "1" || $_POST['selectBy_1'] == "2") {
		$searchByForm = "AND " . $_POST['title_1']." ".$convSelectBy_1." '%".$_POST['text_1']."%' ".$srchString_multi." ";
	} else {
		$searchByForm = "AND " . $_POST['title_1']." ".$convSelectBy_1." '".$_POST['text_1']."' ".$srchString_multi." ";

	}
}

if($_POST['start_date'] && $_POST['end_date']) {
	$st = date_create($_POST['start_date']);
	$ed = date_create($_POST['end_date']);

	$convert_dateStart = date_format($st,"Y-m-d H:i");
	$convert_dateEnd = date_format($ed,"Y-m-d H:i");
	
	$sql_range_time = "BETWEEN '".$convert_dateStart."' AND '".$convert_dateEnd."'";
}
// Dynamic search form process ----------------------------

if ($_SESSION[_SES_USR_TYP_ID]<10) {
	// $sql_from = "[SMS].[dbo].[ALLSMSCDR] JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = ALLSMSCDR.charge_account ";
	$sql_from = "[SMS].[dbo].[ALLSMSCDR] ";
	$sql_where = "incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $check_creater $searchByForm";
	$orderby = "ALLSMSCDR.transaction_id desc";
	$table_mode = 2;// 0=white and gray; 1=color; 2=mix
} else {
	$sql_from = "[SMS].[dbo].[ALLSMSCDR] JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = ALLSMSCDR.charge_account ";
	$sql_where="(CUSTOMERS.CUSTOMER_Parent_Username =  '".$_SESSION[_SES_USR_NAME]."' OR CUSTOMERS.CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' OR ALLSMSCDR.username_sub = '".$_SESSION[_SES_USR_NAME]."') AND incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $searchByForm";
	// $sql_where="(CUSTOMERS.CUSTOMER_Parent_Username =  '".$_SESSION[_SES_USR_NAME]."' OR CUSTOMERS.CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' ) AND incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $searchByForm";
	// $sql_where = "incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $check_creater";
	$orderby = "ALLSMSCDR.transaction_id desc";
	$table_mode = 2;// 0=white and gray; 1=color; 2=mix
}

// $sql = "SELECT deliver_code FROM [SMS].[dbo].[ALLSMSCDR] 
// JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = ALLSMSCDR.charge_account 
// WHERE (CUSTOMERS.CUSTOMER_Parent_Username = '".$_SESSION[_SES_USR_NAME]."' OR CUSTOMERS.CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' ) 
// AND incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $check_creater ";

// // echo $sql;
// $stmt_sql=querySqlEx($sql);
// $row_dvCode=$stmt_sql->fetch(PDO::FETCH_ASSOC);

// echo $_SESSION[_SES_USR_TYP_ID];

$custom_headers_table = '';

$save_enable = true;
$table_define = "";

// $sql_getUser = "SELECT CUSTOMER_NeedDr FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' ";
$sql_getUser = "SELECT CUSTOMER_NeedDr FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' ";
$stmt=querySqlEx($sql_getUser);
$row=$stmt->fetch(PDO::FETCH_ASSOC);

// echo 'Dr = '.$row['CUSTOMER_NeedDr'];

if ($_SESSION[_SES_USR_TYP_ID]<10){
	// get Deliver code, Deliver message, Deliver DT
	// _TYP_FIELD:: -1 = icon and not query; 0 = not show; 1 = string; 2 = date time; 3 = number; 4 = money; 100 = pattern icon or no search; 200 = sum/count (num); 201 = sum/count (money)
	$fields_info = Array(
		Array(_SQL_FIELD=>"transaction_id"
			,_LAB_FIELD=>"Transaction ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=> 1
			,_COM_FIELD2=> 2
			,_COM_FIELD3=> 3
			,_SEA_FIELD=>true
			,_SORT_FIELD=>2) // default sort by: 1 = sort ase,2 = desc
			,Array(_SQL_FIELD=>"username_sub"
			,_LAB_FIELD=>"Username"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"command"
			,_LAB_FIELD=>"Command"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sms_type"
			,_LAB_FIELD=>"SMS type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"session_id"
			,_LAB_FIELD=>"Session ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>5
			,_COM_FIELD2=>6
			,_COM_FIELD3=>7
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"service_type"
			,_LAB_FIELD=>"Service type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"originate_number"
			,_LAB_FIELD=>"Originate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"IIF(charge_account = username_sub, null,charge_account) AS charge_account"
		// ,Array(_SQL_FIELD=>"charge_account"
			,_LAB_FIELD=>"Charge account"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"priority"
			,_LAB_FIELD=>"Priority"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>9
			,_COM_FIELD2=>10
			,_COM_FIELD3=>11
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"scheduled"
			,_LAB_FIELD=>"Scheduled"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"validity"
			,_LAB_FIELD=>"Validity"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"language_code"
			,_LAB_FIELD=>"Language code"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"message"
			,_LAB_FIELD=>"Message"
			,_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------

		// --------------------------------
		//,Array(_SQL_FIELD=>"IIF(deliver_code != 0 OR deliver_code is NULL, 999,0) AS deliver_code"
		,Array(_SQL_FIELD=>"deliver_code"
			,_LAB_FIELD=>"Deliver code"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>14
			,_COM_FIELD2=>15
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"deliver_message"
			,_LAB_FIELD=>"Deliver message"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"deliver_datetime"
			,_LAB_FIELD=>"Deliver DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"error_code"
			,_LAB_FIELD=>"Error code"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>17
			,_COM_FIELD2=>18
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"incoming_datetime"
			,_LAB_FIELD=>"Incoming DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"responding_datetime"
			,_LAB_FIELD=>"responding DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"request_ip"
			,_LAB_FIELD=>"Request IP"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>20
			,_COM_FIELD2=>21
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sm_id"
			,_LAB_FIELD=>"SM ID"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"terminate_number"
			,_LAB_FIELD=>"Terminate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
	);
}  else if($row['CUSTOMER_NeedDr'] == 1) {
	$fields_info = Array(
		Array(_SQL_FIELD=>"transaction_id"
			,_LAB_FIELD=>"Transaction ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=> 1
			,_COM_FIELD2=> 2
			,_COM_FIELD3=> 3
			,_SEA_FIELD=>true
			,_SORT_FIELD=>2) // default sort by: 1 = sort ase,2 = desc
		,Array(_SQL_FIELD=>"username_sub"
			,_LAB_FIELD=>"Username"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"command"
			,_LAB_FIELD=>"Command"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sms_type"
			,_LAB_FIELD=>"SMS type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"session_id"
			,_LAB_FIELD=>"Session ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>5
			,_COM_FIELD2=>6
			,_COM_FIELD3=>7
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"service_type"
			,_LAB_FIELD=>"Service type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"originate_number"
			,_LAB_FIELD=>"Originate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"IIF(charge_account = username_sub, null,charge_account) AS charge_account"
		// ,Array(_SQL_FIELD=>"charge_account"
			,_LAB_FIELD=>"Charge account"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"priority"
			,_LAB_FIELD=>"Priority"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>9
			,_COM_FIELD2=>10
			,_COM_FIELD3=>11
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"scheduled"
			,_LAB_FIELD=>"Scheduled"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"validity"
			,_LAB_FIELD=>"Validity"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"language_code"
			,_LAB_FIELD=>"Language code"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		// ,Array(_SQL_FIELD=>"message"
		// 	,_LAB_FIELD=>"Message"
		// 	,_TYP_FIELD=>1
		// 	,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"IIF(deliver_code != 0 OR deliver_code is NULL, 999,0) AS deliver_code"
		// ,Array(_SQL_FIELD=>"deliver_code"
			,_LAB_FIELD=>"Deliver code"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>13
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"deliver_datetime"
			,_LAB_FIELD=>"Deliver DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"error_code"
			,_LAB_FIELD=>"Error code"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>15
			,_COM_FIELD2=>16
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"incoming_datetime"
			,_LAB_FIELD=>"Incoming DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"responding_datetime"
			,_LAB_FIELD=>"responding DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"request_ip"
			,_LAB_FIELD=>"Request IP"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>18
			,_COM_FIELD2=>19
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sm_id"
			,_LAB_FIELD=>"SM ID"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"terminate_number"
			,_LAB_FIELD=>"Terminate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
	);
}  else if($row['CUSTOMER_NeedDr'] == 0) {
	// not Deliver code, Deliver message, Deliver DT
	$fields_info = Array(
		Array(_SQL_FIELD=>"transaction_id"
			,_LAB_FIELD=>"Transaction ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=> 1
			,_COM_FIELD2=> 2
			,_COM_FIELD3=> 3
			,_SEA_FIELD=>true
			,_SORT_FIELD=>2) // default sort by: 1 = sort ase,2 = desc
		,Array(_SQL_FIELD=>"username_sub"
			,_LAB_FIELD=>"Username"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"command"
			,_LAB_FIELD=>"Command"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sms_type"
			,_LAB_FIELD=>"SMS type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"session_id"
			,_LAB_FIELD=>"Session ID"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>5
			,_COM_FIELD2=>6
			,_COM_FIELD3=>7
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"service_type"
			,_LAB_FIELD=>"Service type"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"originate_number"
			,_LAB_FIELD=>"Originate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"IIF(charge_account = username_sub, null,charge_account) AS charge_account"
		// ,Array(_SQL_FIELD=>"charge_account"
			,_LAB_FIELD=>"Charge account"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"priority"
			,_LAB_FIELD=>"Priority"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>9
			,_COM_FIELD2=>10
			,_COM_FIELD3=>11
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"scheduled"
			,_LAB_FIELD=>"Scheduled"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"validity"
			,_LAB_FIELD=>"Validity"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"language_code"
			,_LAB_FIELD=>"Language code"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		// ,Array(_SQL_FIELD=>"message"
		// 	,_LAB_FIELD=>"Message"
		// 	,_TYP_FIELD=>1
		// 	,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"error_code"
			,_LAB_FIELD=>"Error code"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>13
			,_COM_FIELD2=>14
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"incoming_datetime"
			,_LAB_FIELD=>"Incoming DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"responding_datetime"
			,_LAB_FIELD=>"responding DT"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		// --------------------------------
		,Array(_SQL_FIELD=>"request_ip"
			,_LAB_FIELD=>"Request IP"
			,_TYP_FIELD=>1
			,_COM_FIELD1=>16
			,_COM_FIELD2=>17
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sm_id"
			,_LAB_FIELD=>"SM ID"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"terminate_number"
			,_LAB_FIELD=>"Terminate number"
			,_COM_TYP_FIELD=>1
			,_SEA_FIELD=>true)
	);
}

############################################
require_once("element/_e_list_items_z1.php");
if (!$dbh) {
?>
			<!-- Page Content -->
			<h1>Error</h1>
			<hr>
			<p>This is an internal error.</p>
<?php
}else{
############################################
?>
			<h1>SMS Report</h1>
			<div class="form-row justify-content-center text-center">
			<?php
			$stmt=querySqlEx("SELECT TOP 6 REPORT_YearMonth,REPORT_MonthUsage,REPORT_MonthQuota FROM SBG.dbo.REPORTS WHERE REPORT_Username=? ORDER BY REPORT_YearMonth DESC",[$_SESSION[_SES_USR_NAME]]);
			while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) $months[]=$row;
			if (isset($months)&&is_array($months)) {
				foreach($months AS $month){
			?>
				<div class="col-md-4">
					<div class="form-group input-group mb-2">
						<input class="form-control text-right" value="Month : <?=substr($month['REPORT_YearMonth'],0,4)."-".substr($month['REPORT_YearMonth'],-2)?>" disabled/>
						<input class="form-control" value="<?=number_format($month['REPORT_MonthUsage'],0,"",",");?> SMS / <?=($month['REPORT_MonthQuota']==-1?'Unlimited':$month['REPORT_MonthQuota']." SMS")?>" disabled/>
					</div>
				</div>
			<?php
				}
			}
			?>
			</div>
			<hr>

			<div class="container-fluid mb-2">
				<?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar_dnm.php");?>
			</div>
			<br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
}
?>
<script type='text/javascript'>	
	var i = 1;
	const x = "<?php echo $counterState; ?>";
	var b = parseInt(x);

	$(document).on('click', '#add_field', function() {
	
		if( i == 5 ) {
			return false;
		} else {
			if( x === '') {
				i +=1;
			} else {
				i = b+=1;
			}
		}
		// i = document.getElementById('counter').value;
		// i = parseInt(i)+=1;
		// document.getElementById('counter').value = i;

		$('#formClassDM').append("<div id='dm_formSearch' class='dm_formSearch' id='myid_"+ i +"'><select class='form-control' style='background-color: #85929E;color: #FFFFFF' name='selectAndOr_"+ i +"' id='selectAndOr_"+ i +"'><?php foreach( $selectAndOr_values as $names => $display) { ?><option value='<?php echo $names; ?>' ><?php echo $display; ?></option><?php } ?></select><div class='form-group'><select class='form-control' name ='title_"+i+"' id='select_"+ i +"' ><option disabled selected value> - Select title for search - </option><?php foreach( $values as $names => $display) { ?><option value='<?php echo $names; ?>' ><?php echo $display; ?></option><?php } ?></select>&nbsp;<select class='form-control' name='selectBy_"+ i +"'><?php foreach( $selectBy_values as $names => $display) { ?><option value='<?php echo $names; ?>' ><?php echo $display; ?></option><?php } ?></select>&nbsp;<input class='form-control' type='text' name='text_"+ i +"' ></div></div>");        

		// dynamic selected
		var select1 = select = document.getElementById('select_1');
		var select2 = select = document.getElementById('select_2');
		var select3 = select = document.getElementById('select_3');
		var select4 = select = document.getElementById('select_4');
		var select5 = select = document.getElementById('select_5');
		var select6 = select = document.getElementById('select_6');
		var select7 = select = document.getElementById('select_7');
		var select8 = select = document.getElementById('select_8');

		select1.onchange = function() {
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select2.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select3.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select4.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select5.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select6.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select7.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select8, this.selectedIndex);
		};

		select8.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
			preventDupes.call(this, select5, this.selectedIndex);
			preventDupes.call(this, select6, this.selectedIndex);
			preventDupes.call(this, select7, this.selectedIndex);
		};
		// dynamic selected

		function preventDupes(select, index) {
			var options = select.options,
				len = options.length;

			if (index === select.selectedIndex) {
				if (select.options[index].text != '- Select title for search -') {
					alert('You\'ve already selected the item "' + select.options[index].text + '".\n\nPlease choose another.');
					this.selectedIndex = 0;
				}
			}
		}
	});

	$(document).on('click', '#del_field', function() {
		i-=1;
    $(".dm_formSearch:last").remove();
		if(i < 1){
			i = 1;
		}
  });
</script>
<BR/>
<div class="form-row justify-content-center text-center">Note</div>
<div class="form-row justify-content-center text-center">
	<div class="col-md-4 text-left">
		Error Code detail<BR/>
		0 = No error<BR/>
		97 = Invalid time format<BR/>
		98 = Invalid expire format<BR/>
		1041 = Maximum submission exceeded<BR/>
		1042 = Over quota limit<BR/>
		1078 = Invalid destination<BR/>
		1375 = SLA service level agreement in UAG<BR/>
	</div>
	<div class="col-md-4 text-left">
		Deliver Code detail<BR/>
		0 = [DELIVERED] Success/Message is delivered to destination<BR/>
		161 = [EXPIRED] Message validity period has expired<BR/>
		162 = [DELETED] Message has been deleted<BR/>
		163 = [UNDELIV] Message is undeliverable<BR/>
		164 = [ACCEPTD] Message is in accepted state<BR/>
		165 = [UNKNOWN] Message is in invalid state<BR/>
		166 = [REJECTD] Message is in a rejected state<BR/>
		167 = [ENROUTE] The message is in enroute state<BR/>
	</div>
</div>