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
$search_bar = "_p_search_bar_sbg_fix_month.php"; // "_e_search_bar.php"

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
	if (strlen($_POST[_STA_DAY]) > 0) $start_day = checkRealLastDay($start_year,$start_month,$_POST[_STA_DAY]); else $start_day=1;
	if (strlen($_POST[_END_YEAR]) > 0) $end_year = $_POST[_END_YEAR]; else if (strlen($_POST[_STA_YEAR]) > 0) $end_year=$start_year; else $end_year=date('Y');
	if (strlen($_POST[_END_MONTH]) > 0) $end_month = $_POST[_END_MONTH]; else if (strlen($_POST[_STA_MONTH]) > 0) $end_month=$start_month; else $end_month=date('m');
	if (strlen($_POST[_END_DAY]) > 0) $end_day = checkRealLastDay($end_year,$end_month,$_POST[_END_DAY]); else $end_day=date('t');

	// swarp start and stop
	$start_dt_search = genDateYmd($start_year,$start_month,$start_day);
	$end_dt_search = genDateYmd($end_year,$end_month,$end_day);
	if (strtotime($start_dt_search." 00:00:00.000")>strtotime($end_dt_search." 00:00:00.000")) {
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
$sql_from = "SMS.dbo.SBG_pend JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = SBG_pend.charge_account ";
$sql_where = "incoming_datetime $sql_range_time AND service_type = '"._SMS_SERVICE_TYPE."' $check_creater";
$orderby = "CUSTOMERS.CUSTOMER_Username asc";
$table_mode = 2;// 0=white and gray; 1=color; 2=mix

$custom_headers_table = '';

$save_enable = true;
$table_define = "";


// create table configuration array
// _TYP_FIELD:: -1 = icon and not query; 0 = not show; 1 = string; 2 = date time; 3 = number; 4 = money; 100 = pattern icon or no search; 200 = sum/count (num); 201 = sum/count (money)
$fields_info = Array(
	Array(_SQL_FIELD=>"charge_account"
		,_LAB_FIELD=>"Username"
		,_TYP_FIELD=>1
		,_COM_FIELD1=> 1
		,_COM_FIELD2=> 2
		,_COM_FIELD3=> 3
		,_SEA_FIELD=>true
		,_SORT_FIELD=>2) // default sort by: 1 = sort ase,2 = desc
	,Array(_SQL_FIELD=>"transaction_id"
		,_LAB_FIELD=>"Transaction ID"
		,_TYP_FIELD=>0
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"command"
		,_LAB_FIELD=>"Command"
		,_TYP_FIELD=>0
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"command_id"
		,_LAB_FIELD=>"Command ID"
		,_TYP_FIELD=>0
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// --------------------------------
	,Array(_SQL_FIELD=>"incoming_datetime"
		,_LAB_FIELD=>"Income DT"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>5
		,_COM_FIELD2=>6
		,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"request_ip"
		,_LAB_FIELD=>"IP"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
		,Array(_SQL_FIELD=>"sequence_number"
		,_LAB_FIELD=>"Sequence Number"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// --------------------------------
	,Array(_SQL_FIELD=>"service_type"
		,_LAB_FIELD=>"Service Type"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>8
		,_COM_FIELD2=>9
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"esm_class"
		,_LAB_FIELD=>"ESM Class"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"priority_flag"
		,_LAB_FIELD=>"Priority Flag"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// --------------------------------
	,Array(_SQL_FIELD=>"source_addr"
		,_LAB_FIELD=>"Source Addr"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>11
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"destination_addr"
		,_LAB_FIELD=>"Destination Addr"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// --------------------------------
	,Array(_SQL_FIELD=>"schedule_delivery_time"
		,_LAB_FIELD=>"Schedule"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>13
		,_COM_FIELD2=>14
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"validity_period"
		,_LAB_FIELD=>"Validity"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"registered_delivery"
		,_LAB_FIELD=>"Register"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// --------------------------------
	,Array(_SQL_FIELD=>"data_coding"
		,_LAB_FIELD=>"Data Code"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>16
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"long_message_id"
		,_LAB_FIELD=>"Long Message"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
			// --------------------------------
	,Array(_SQL_FIELD=>"current_message"
		,_LAB_FIELD=>"Current"
		,_TYP_FIELD=>1
		,_COM_FIELD1=>18
		,_COM_FIELD2=>19
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"summary_message"
		,_LAB_FIELD=>"Summary"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"short_message"
		,_LAB_FIELD=>"Short"
		,_COM_TYP_FIELD=>1
		,_SEA_FIELD=>true)

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
			<h1>SMS Report (Admin)</h1>
			<hr>
			<div class="col-md-12 text-center mb-2">Monthly Usage</div>
			<div class="form-row justify-content-center text-center">
			<?php
			$stmt=querySqlEx("SELECT TOP 6 REPORT_YearMonth,REPORT_MonthUsage,REPORT_MonthQuota FROM SBG.dbo.REPORTS WHERE REPORT_Username=? ORDER BY REPORT_YearMonth DESC",[$_SESSION[_SES_USR_NAME]]);
			while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) $months[]=$row;
			if (isset($months)&&is_array($months)) {
				foreach($months AS $month){
			?>
				<div class="col-md-4">
					<div class="form-group input-group mb-2">
						<input class="form-control text-right" value="Month : <?=substr($month['REPORT_YearMonth'],0,4)."-".substr($month['REPORT_YearMonth'],-2)?>" />
						<input class="form-control" value="<?=number_format($month['REPORT_MonthUsage'],0,"",",");?> SMS / <?=($month['REPORT_MonthQuota']==-1?'Unlimited':$month['REPORT_MonthQuota']." SMS")?>" />
					</div>
				</div>
			<?php
				}
			}
			?>
			</div>
			<hr>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
}
?>
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