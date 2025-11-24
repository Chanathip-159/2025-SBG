<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# à¸—à¸”à¸ªà¸­à¸šà¸ à¸²à¸©à¸²à¹„à¸—à¸¢
# Edit zone
############################################
$dest_file = "sum_report";																																			// destination name of file
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
// $search_bar = "element/_p_search_bar_summary.php"; // "_e_search_bar.php"

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

// if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;
// if (strlen($_POST[_USR_SUM])) $user_sumrpt = $_POST[_USR_SUM]; else $user_sumrpt = null;
// if (strlen($_POST[_SENDER_SUM])) $sender_sumrpt = $_POST[_SENDER_SUM]; else $sender_sumrpt = null;

switch ($_SESSION[_SES_USR_TYP_ID]) {
	case _SES_USR_ROT:
	case _SES_USR_ADM:
	case _SES_USR_SYS_ADMIN:
		// do nothing
	break;
	default:
		$check_creater = "AND SUMRPT_Username = '".$_SESSION[_SES_USR_NAME]."'";
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
	$sql_from = "[SBG].[dbo].[SUM_REPORTS] JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = SUM_REPORTS.SUMRPT_Username ";
	$sql_where = "SUMRPT_Date $sql_range_time $check_creater $searchByForm";
	// $orderby = "charge_account,originate_number asc";
	$table_mode = 2;// 0=white and gray; 1=color; 2=mix
} else {
	$sql_from = "[SBG].[dbo].[SUM_REPORTS] JOIN [SBG].[dbo].[CUSTOMERS] ON CUSTOMERS.CUSTOMER_Username = SUM_REPORTS.SUMRPT_Username ";
	$sql_where="(CUSTOMERS.CUSTOMER_Parent_Username = '".$_SESSION[_SES_USR_NAME]."' OR CUSTOMERS.CUSTOMER_Username = '".$_SESSION[_SES_USR_NAME]."' ) AND SUMRPT_Date $sql_range_time $searchByForm";

	$table_mode = 2;// 0=white and gray; 1=color; 2=mix
}

// echo $_SESSION[_SES_USR_TYP_ID];

$custom_headers_table = '';

$save_enable = true;
$table_define = "";

	// _TYP_FIELD:: -1 = icon and not query; 0 = not show; 1 = string; 2 = date time; 3 = number; 4 = money; 100 = pattern icon or no search; 200 = sum/count (num); 201 = sum/count (money)
	$fields_info = Array(
		Array(_SQL_FIELD=>"SUMRPT_Username"
			,_LAB_FIELD=>"Username"
			,_TYP_FIELD=>1
			,_SEA_FIELD=>true) // default sort by: 1 = sort ase,2 = desc
		,Array(_SQL_FIELD=>"SUMRPT_Sender"
			,_LAB_FIELD=>"Sender"
			,_TYP_FIELD=>1
			,_SEA_FIELD=>true)
    ,Array(_SQL_FIELD=>"SUMRPT_Count"
			,_LAB_FIELD=>"SMS Count"
			,_TYP_FIELD=>3
			,_SEA_FIELD=>true)
			,Array(_SQL_FIELD=>"SUMRPT_Deliver_code"
			,_LAB_FIELD=>"Deliver code"
			,_TYP_FIELD=>1
			,_SEA_FIELD=>true)
			,Array(_SQL_FIELD=>"SUMRPT_Date"
			,_LAB_FIELD=>"Date"
			,_TYP_FIELD=>1
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
			<h1>Summary Report</h1>
			<hr>
			<div class="col-md-12 text-right mb-2">Latest information starting from : 2022-11-01 (Y-M-D)</div>
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
	
		if( i == 3 ) {
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

		select1.onchange = function() {
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
		};

		select2.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
		};

		select3.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
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