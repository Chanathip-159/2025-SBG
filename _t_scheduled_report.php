<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
# Edit zone
############################################
$dest_file = "scheduled_report";																																			// destination name of file
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


if (strlen($_POST['del_scheduled_id'])) {
	$sql="DELETE FROM [SMS].[dbo].[SBG_SCHEDULED_pend] WHERE [scheduled_delivery_id] = ?";
	querySqlEx($sql, [$_POST['del_scheduled_id']]);
	$_GET['toast_type'] = "S";
	$_GET['toast_header'] = "Result message";
	$_GET['toast_message'] = "Delete scheduled ".$_POST['del_scheduled_id']." successfully";
}


// start setup year to search bar with only exist data
#$sql_year_list = "SELECT DISTINCT YEAR(SerialNo_ExecDatetime) as yr FROM [ISAG].[dbo].[SerialNumbers]";
#if ($stmt = querySqlEx($sql_year_list)) {
#	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
#		$year_list[] = $row['yr'];
#	}
#}
// end setup year to search bar with only exist data

# select search bar file
#$search_bar = "_p_search_bar_sbg_fix_month.php"; // "_e_search_bar.php"

// start set year by get last 5 year
for($i=date("Y")-5; $i<=date("Y"); $i++) {
	$year_list[] = $i;
}
// end set year by get last 5 year

// set $year_list=null use for hide search bar
#$year_list=null;

if ($year_list!==null) {
	// setup date range for search if $year_list is ok
	if (strlen($_POST[_STA_YEAR]) > 0) $start_year = $_POST[_STA_YEAR]; else $start_year=date('Y');
	if (strlen($_POST[_STA_MONTH]) > 0) $start_month = $_POST[_STA_MONTH]; else $start_month=date('m');
	if (strlen($_POST[_STA_DAY]) > 0) $start_day = checkRealLastDay($start_year, $start_month, $_POST[_STA_DAY]); else $start_day=1;
	if (strlen($_POST[_END_YEAR]) > 0) $end_year = $_POST[_END_YEAR]; else if (strlen($_POST[_STA_YEAR]) > 0) $end_year=$start_year; else $end_year=date('Y');
	if (strlen($_POST[_END_MONTH]) > 0) $end_month = $_POST[_END_MONTH]; else if (strlen($_POST[_STA_MONTH]) > 0) $end_month=$start_month; else $end_month=date('m');
	if (strlen($_POST[_END_DAY]) > 0) $end_day = checkRealLastDay($end_year, $end_month, $_POST[_END_DAY]); else $end_day=date('t');

	// swarp start and stop
	$start_dt_search = genDateYmd($start_year, $start_month, $start_day);
	$end_dt_search = genDateYmd($end_year, $end_month, $end_day);
	if (strtotime($start_dt_search." 00:00:00.000") > strtotime($end_dt_search." 00:00:00.000")) {
		echo "$start_dt_search $end_dt_search";
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
	
	$start_dt_search = genDateYmd($start_year, $start_month, $start_day);
	$end_dt_search = genDateYmd($end_year, $end_month, $end_day);

	$sql_range_time = "BETWEEN '$start_dt_search 00:00:00.000' AND '$end_dt_search 23:59:59.999'";
}

if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;



$pattern_icons['_ICON_1_'] = "<a href=\"#DeleteId\" title=\"Del\" data-toggle=\"modal\" id=\"_SID_1_\" data-target=\"#delSchModal\"><i class=\"fa fa-fw fa-trash\"></i></a>";

switch ($_SESSION[_SES_USR_TYP_ID]) {
	case _SES_USR_ROT:
	case _SES_USR_ADM:
	case _SES_USR_SYS_ADMIN:
		$check_creator = "";
	break;
	default:
		$check_creator = "AND SMSSCHCDR_Creator = '".$_SESSION[_SES_USR_NAME]."'";
	break;
}

$sql_from = "SMS.dbo.ALLSMSSCHCDR";
$sql_where = "SMSSCHCDR_SendDt $sql_range_time $check_creator";

$orderby = "SMSSCHCDR_SendDt DESC";
$table_mode = 2;// 0=white and gray; 1=color; 2=mix

$custom_headers_table = '';

$save_enable = true;
$table_define = "";

// create table configuration array
// _TYP_FIELD:: -1 = icon and not query; 0 = not show; 1 = string; 2 = date time; 3 = number; 4 = money; 100 = pattern icon or no search; 200 = sum/count (num); 201 = sum/count (money)
$fields_info = Array(
	  Array(_SQL_FIELD => "SMSSCHCDR_ScheduledId"
		, _LAB_FIELD=>"Scheduled ID"
		, _TYP_FIELD=>1
		, _COM_FIELD1=> 1
		, _COM_FIELD2=> 2)
	, Array(_SQL_FIELD => "SMSSCHCDR_CreateDt"
		, _LAB_FIELD=>"Create Datetime"
		, _TYP_FIELD=>0
		, _COM_TYP_FIELD=>2
		, _SEA_FIELD=>true)
	, Array(_SQL_FIELD => "SMSSCHCDR_SendDt"
		, _LAB_FIELD=>"Sent Datetime"
		, _TYP_FIELD=>0
		, _COM_TYP_FIELD=>2
		, _SEA_FIELD=>true
		, _SORT_FIELD=>2) // default sort by: 1 = sort ase, 2 = desc
	, Array(_SQL_FIELD => "SMSSCHCDR_Sender"
		, _LAB_FIELD=>"Sender"
		, _TYP_FIELD=>1
		, _SEA_FIELD=>true)
	, Array(_SQL_FIELD => "SMSSCHCDR_ShortMessage"
		, _LAB_FIELD=>"SMS"
		, _TYP_FIELD=>1
		, _SEA_FIELD=>true)
	, Array(_SQL_FIELD => "SMSSCHCDR_Creator"
		, _LAB_FIELD=>"Creator"
		, _TYP_FIELD=>1
		, _SEA_FIELD=>true)
	, Array(_SQL_FIELD => "SMSSCHCDR_NumOfSms"
		, _LAB_FIELD=>"SMS"
		, _TYP_FIELD=>3)
	, Array(_SQL_FIELD => "SMSSCHCDR_TotalPhoneNum"
		, _LAB_FIELD=>"Phone"
		, _TYP_FIELD=>3)
	, Array(_SQL_FIELD => "SMSSCHCDR_TotalSuccessPhoneNum"
		, _LAB_FIELD=>"Success"
		, _TYP_FIELD=>3)
);
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
			<h1>Scheduled queuing</h1>
			<ol class="breadcrumb"><!-- Breadcrumbs-->
				<li class="breadcrumb-item active"><i class="fa fa-money-bill-wave fa-fw"></i> Details</li>
			</ol>
			<hr>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_del_scheduled.php");
}
?>