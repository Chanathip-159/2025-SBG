<style>
	.btn-info, .btn-secondary, .btn-danger{
	width: 90px !important;
	}
	.fa-plus-circle {
      color: #3366CC;
    }
</style>
<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# à¸—à¸”à¸ªà¸­à¸šà¸ à¸²à¸©à¸²à¹„à¸—à¸¢
# Edit zone
############################################
$dest_file="manage_cus";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE]=$dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");						
require_once('../php.lib/api.sms.send.direct.php');
require_once('../sbg/api/bulk.api.engine.php');

$n=6;
function getName($n) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
	
		for ($i = 0; $i < $n; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
		}
	
		return $randomString;
}
$new_pass = getName($n);																			// check permission from session name and session destination name
//print_r($_POST);
// for delete msisdn
if (strlen($_POST['del_item'])) {
	$del_item=$_POST['del_item'];
	$sql="DELETE FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=? DELETE FROM SBG.dbo.GROUPS WHERE GROUP_Username=?";
	if (querySqlEx($sql,[$del_item,$del_item])) {
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']="Delete $del_item successfully";
	}else{
		$_GET['toast_type']="E";
		$_GET['toast_header']="Error";
		$_GET['toast_message']="Can not delete $del_item";
	}
} elseif (strlen($_POST['reset_item'])) {
	$reset_item=$_POST['reset_item'];
	$sql="UPDATE SBG.dbo.CUSTOMERS SET CUSTOMER_UpdateDt=GETDATE(),CUSTOMER_Password=? WHERE LOWER(CUSTOMER_Username)=LOWER(?)";
	if (!querySqlEx($sql,[md5($new_pass),$reset_item])) {
		// if not admin but try to change username
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to update or insert user account";
	}else{

		$sql_get_data = "SELECT * FROM SBG.dbo.CUSTOMERS WHERE LOWER([CUSTOMER_Username])=LOWER(?)";
		$stmt=querySqlEx($sql_get_data,[$reset_item]);

		while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
			try {
				error_reporting(E_ERROR);
				
				$msisdn = substr($row['CUSTOMER_Telephone'], 1);        
				$msisdn_sms = '66'. $msisdn;

				$msg_sms = 'เรียน คุณ '.$row['CUSTOMER_Username'].'

รายละเอียด Account สำหรับลงชื่อเข้าใช้งานระบบ SBG ของท่านคือ

Username: '.$row['CUSTOMER_Username'].'
New password: '.$new_pass.' ';

				$send_data = Array();
				$send_data['sms_client_ip'] = getClientIp();
				$send_data['sms_type']="submit"; 
				$send_data['sms_service_type'] = "SBG";
				$send_data['sms_charge_account'] = "admin";
				$send_data['sms_sender'] = "SBG";
				$send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
				$send_data['sms_langauge'] = "T";
				$send_data['sms_message'] = $msg_sms;

				$send_test_result = sendSmsToDb($send_data);

				$_GET['toast_type']="S";
				$_GET['toast_header']="Result message";
				$_GET['toast_message']="Update password of $reset_item to $new_pass successfully";
			}
			catch (exception $e) {
				header("Status: 500");
				header("Content-Type: text/plain" );
				echo 'Message: ' .$e->getMessage();
			}
		}
	}
} else if (strlen($_POST['add_edit_customer_id'])) {
	$fields=Array(); $datas=Array();
	if ($_SESSION[_SES_USR_TYP_ID]>_ADMIN_L&&$_POST['add_edit_customer_id']==$_SESSION[_SES_USR_NAME]) {
		// not allow any user change username except user that has level under that 2 to 0
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Not allow you to change username";
	} else {
		if ($_POST['add_edit_customer_id']=="_NEW_" && strlen($_POST['CUSTOMER_Username'])) {
			$user_Insert=strtolower($_POST['CUSTOMER_Username']);
			$user_id = $user_Insert;
		} else {
			$user_edit=$_POST['add_edit_customer_id'];
			$user_id = $user_edit;
		}

		if(isset($user_Insert) || isset($user_edit)) {
			if($user_Insert != '') {
				$sql_insert="INSERT INTO SBG.dbo.CUSTOMERS
				(CUSTOMER_Password
				,CUSTOMER_Department
				,CUSTOMER_Category
				,CUSTOMER_Contact
				,CUSTOMER_Detail
				,CUSTOMER_Telephone
				,CUSTOMER_Email
				,CUSTOMER_ActivateDate
				,CUSTOMER_ExpireDate
				,CUSTOMER_MonthlyUsage
				,CUSTOMER_AccountUsage
				,CUSTOMER_NeedDr
				,CUSTOMER_Status
				,CUSTOMER_AdminComment
				,CUSTOMER_CreateDt
				,CUSTOMER_UpdateDt
				,CUSTOMER_Username
				,CUSTOMER_CurrentUsage
				,CUSTOMER_CurrentUsageByDR
				,CUSTOMER_Parent_Username) VALUES ('".md5($new_pass)."',?,?,?,?,?,?,?,?,?,?,?,?,?,GETDATE(),GETDATE(),LOWER(?),0,0,null)";
				//,CUSTOMER_Parent_Username) VALUES ('".md5($new_pass)."',?,?,?,?,?,?,?,?,?,?,?,?,?,GETDATE(),GETDATE(),LOWER(?),0,0,null)";
				
				$datas[]=$_POST['CUSTOMER_Department'];
				$datas[]=$_POST['CUSTOMER_Category'];
				$datas[]=$_POST['CUSTOMER_Contact'];
				$datas[]=$_POST['CUSTOMER_Detail'];
				$datas[]=$_POST['CUSTOMER_Telephone'];
				$datas[]=$_POST['CUSTOMER_Email'];
				$datas[]=$_POST['CUSTOMER_ActivateDate_year']."-".$_POST['CUSTOMER_ActivateDate_month']."-".$_POST['CUSTOMER_ActivateDate_day']." 00:00:00";
				$datas[]=date("Y-m-d 00:00:00",strtotime($_POST['CUSTOMER_ExpireDate_year']."-".$_POST['CUSTOMER_ExpireDate_month']."-".$_POST['CUSTOMER_ExpireDate_day']." +1day"));
				$datas[]=$_POST['CUSTOMER_MonthlyUsage'];
				$datas[]=$_POST['CUSTOMER_AccountUsage'];
				$datas[]=$_POST['CUSTOMER_NeedDr'];
				$datas[]=$_POST['CUSTOMER_Status'];
				$datas[]=($_SESSION[_SES_USR_TYP_ID]<=_ADMIN_L?$_POST['CUSTOMER_AdminComment']:NULL);
				$datas[]=$user_id;

print_r($sql_insert);
echo '<br>';
print_r($datas);

				if (querySqlEx($sql_insert,$datas) == false) {
					$_GET['toast_type']="F";
					$_GET['toast_header']="Fail to process";
					$_GET['toast_message']="Fail to update or insert user account";
				} else {
					if (isset($_POST['CUSTOMER_MonthlyUsage_Apply'])&&$_POST['CUSTOMER_MonthlyUsage_Apply']==1) {
						$sql="UPDATE SBG.dbo.REPORTS SET REPORT_MonthQuota=? WHERE LOWER(REPORT_Username)=LOWER(?) AND REPORT_YearMonth=?";
						querySqlEx($sql,[$_POST['CUSTOMER_MonthlyUsage'],$user_id,date("Ym")]);
					}

					try {
						error_reporting(E_ERROR);
						
						$msisdn = substr($_POST['CUSTOMER_Telephone'], 1);        
						$msisdn_sms = '66'. $msisdn;
		
$msg_sms = 'เรียน คุณ '.$_POST['CUSTOMER_Username'].'

รายละเอียด Account สำหรับลงชื่อเข้าใช้งานระบบ SBG ของท่านคือ

Username: '.$_POST['CUSTOMER_Username'].'
Password: '.$new_pass.' ';

$send_data = Array();
$send_data['sms_client_ip'] = getClientIp();
$send_data['sms_type']="submit"; 
$send_data['sms_service_type'] = "SBG";
$send_data['sms_charge_account'] = "admin";
$send_data['sms_sender'] = "SBG";
$send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
$send_data['sms_langauge'] = "T";
$send_data['sms_message'] = $msg_sms;

$send_test_result = sendSmsToDb($send_data);
		
						$_GET['toast_type']="S";
						$_GET['toast_header']="Result message";
						$_GET['toast_message']="Update or insert user account successfully";
					}
					catch (exception $e) {
						header("Status: 500");
						header("Content-Type: text/plain" );
						echo 'Message: ' .$e->getMessage();
					}
				}
			} else if ($user_edit != '') {
				$sql="SELECT COUNT(*) AS Counting FROM SBG.dbo.CUSTOMERS WHERE LOWER(CUSTOMER_Username)=LOWER(?)";
				if (querySqlSingleFieldEx($sql,[$user_id])) {
					$sql_update="UPDATE SBG.dbo.CUSTOMERS
												SET CUSTOMER_UpdateDt=GETDATE()
														,CUSTOMER_Department=?
														,CUSTOMER_Category=?
														,CUSTOMER_Contact=?
														,CUSTOMER_Detail=?
														,CUSTOMER_Telephone=?
														,CUSTOMER_Email=?
														,CUSTOMER_ActivateDate=?
														,CUSTOMER_ExpireDate=?
														,CUSTOMER_MonthlyUsage=?
														,CUSTOMER_AccountUsage=?
														,CUSTOMER_NeedDr=?
														,CUSTOMER_Status=?
														,CUSTOMER_AdminComment=?
														,CUSTOMER_UrlDelivery=?
												WHERE LOWER(CUSTOMER_Username)=LOWER(?)";

					$datas[]=$_POST['CUSTOMER_Department'];
					$datas[]=$_POST['CUSTOMER_Category'];
					$datas[]=$_POST['CUSTOMER_Contact'];
					$datas[]=$_POST['CUSTOMER_Detail'];
					$datas[]=$_POST['CUSTOMER_Telephone'];
					$datas[]=$_POST['CUSTOMER_Email'];
					$datas[]=$_POST['CUSTOMER_ActivateDate_year']."-".$_POST['CUSTOMER_ActivateDate_month']."-".$_POST['CUSTOMER_ActivateDate_day']." 00:00:00";
					$datas[]=date("Y-m-d 00:00:00",strtotime($_POST['CUSTOMER_ExpireDate_year']."-".$_POST['CUSTOMER_ExpireDate_month']."-".$_POST['CUSTOMER_ExpireDate_day']." +1day"));
					$datas[]=$_POST['CUSTOMER_MonthlyUsage'];
					$datas[]=$_POST['CUSTOMER_AccountUsage'];
					$datas[]=$_POST['CUSTOMER_NeedDr'];
					$datas[]=$_POST['CUSTOMER_Status'];
					$datas[]=($_SESSION[_SES_USR_TYP_ID]<=_ADMIN_L?$_POST['CUSTOMER_AdminComment']:NULL);
					$datas[]=$_POST['CUSTOMER_UrlDelivery'];
					$datas[]=$user_id;

				}
print_r($sql_update);
echo '<br>';
print_r($datas);
				if (querySqlEx($sql_update,$datas) == false) {
					$_GET['toast_type']="F";
					$_GET['toast_header']="Fail to process";
					$_GET['toast_message']="Fail to update or insert user account";
				} else {
					$_GET['toast_type']="S";
					$_GET['toast_header']="Result message";
					$_GET['toast_message']="Update or insert user account successfully";
				}
			}
		}	else {
			$_GET['toast_type']="E";
			$_GET['toast_header']="Error message";
			$_GET['toast_message']="Unknown user account";
		}
	}
	/// clear post
	unset($_POST['add_edit_customer_id']);
	unset($_POST['CUSTOMER_Password']);
	unset($_POST['CUSTOMER_Department']);
	unset($_POST['CUSTOMER_Category']);
	unset($_POST['CUSTOMER_Contact']);
	unset($_POST['CUSTOMER_Detail']);
	unset($_POST['CUSTOMER_Telephone']);
	unset($_POST['CUSTOMER_Email']);
	unset($_POST['CUSTOMER_ActivateDate']);
	unset($_POST['CUSTOMER_ExpireDate']);
	unset($_POST['CUSTOMER_MonthlyUsage']);
	unset($_POST['CUSTOMER_AccountUsage']);
	unset($_POST['CUSTOMER_NeedDr']);
	unset($_POST['CUSTOMER_Status']);
	unset($_POST['CUSTOMER_AdminComment']);
	unset($_POST['CUSTOMER_CreateDt']);
	unset($_POST['CUSTOMER_UpdateDt']);
	unset($_POST['CUSTOMER_Username']);
	unset($_POST['CUSTOMER_UrlDelivery']);
}
// start setup custom POST array to use only this page
#
// end setup custom POST array to use only this page

// start setup year to search bar with only exist data
#$sql_year_list="SELECT DISTINCT YEAR(SerialNo_ExecDatetime) as yr FROM [ISAG].[dbo].[SerialNumbers]";
#if ($stmt=querySqlEx($sql_year_list)) {
#	while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
#		$year_list[]=$row['yr'];
#	}
#}
// end setup year to search bar with only exist data

// start set year by get last 5 year
#for($i=date("Y")-5; $i<=date("Y"); $i++) {
#	$year_list[]=$i;
#}
// end set year by get last 5 year

# select search bar file
# $search_bar="_e_search_bar.php"; // "_e_search_bar.php"

// set $year_list=null use for hide search bar
$year_list=null;

if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

$pattern_icons['_ICON_1_'] = 
// "<a href=\"#\" title=\"Edit Account\" data-toggle=\"modal\" id=\"_SID_1_\" data-target=\"#addEditCustomerAccModal\"><i class=\"fa fa-fw fa-edit\"></i></a>";

"<button title=\"Edit Account\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#addEditCustomerAccModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";

$pattern_icons['_ICON_1_'] .= 
// "<a href=\"#\" title=\"Reset password\" data-toggle=\"modal\" id=\"reset-_SID_1_\" data-target=\"#addEditCustomerAccModal\"><i class=\"fa fa-fw fa-key\"></i></a>";

"<button title=\"Reset password\" class='btn btn-secondary' id=\"reset-_SID_1_\" data-toggle=\"modal\" data-target=\"#addEditCustomerAccModal\" ><i class='fas fa-redo-alt'></i>&nbsp;Reset</button><br>";

if ($_SESSION[_SES_USR_TYP_ID] <= 2) { // more than _SES_USR_SYS_ADMIN
	$pattern_icons['_ICON_1_'] .= 
	// "<a href=\"#\" title=\"Delete Account\" data-toggle=\"modal\" id=\"del-_SID_1_\" data-target=\"#addEditCustomerAccModal\"><i class=\"fa fa-fw fa-trash\"></i></a>";
	"<button title=\"Delete Account\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#addEditCustomerAccModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";
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

$sql_from="SBG.dbo.CUSTOMERS";
$sql_where="CUSTOMER_Username != '' $searchByForm";
$orderby="CUSTOMER_Username";
$table_mode=1;// 0=white and gray; 1=color; 2=mix
$table_font_size="small";

$save_enable = true;

// create table configuration array
// _TYP_FIELD:: -1=icon and not query; 0=not show; 1=string; 2=date time; 3=number; 4=money; 100=pattern icon or no search; 200=sum/count (num); 201=sum/count (money)
$fields_info=Array(
	 Array(_SQL_FIELD=>"CUSTOMER_Username" // can use [ISAG].[dbo].[Users].[User_CreateDate] // can use [ISAG].[dbo].[Users].[User_Name] AS Name
		,_LAB_FIELD=>"Username"
		,_TYP_FIELD=>1
		,_SORT_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_CreateDt"
		,_LAB_FIELD=>"Create datetime"
		,_TYP_FIELD=>2)
	,Array(_SQL_FIELD=>"CUSTOMER_Department"
		,_LAB_FIELD=>"Department"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_Telephone"
		,_LAB_FIELD=>"Phone No."
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_Email"
		,_LAB_FIELD=>"e-Mail"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_ActivateDate"
		,_LAB_FIELD=>"Activate"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"DATEADD(day,-1,CAST(CUSTOMER_ExpireDate AS date)) AS exdt"
		,_LAB_FIELD=>"Expire"
		,_TYP_FIELD=>2
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_MonthlyUsage"
		,_LAB_FIELD=>"Monthly Usage"
		,_TYP_FIELD=>3
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_AccountUsage"
		,_LAB_FIELD=>"Account Usage"
		,_TYP_FIELD=>3
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_CurrentUsage"
		,_LAB_FIELD=>"Current Usage"
		,_TYP_FIELD=>3
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CUSTOMER_Detail"
		,_LAB_FIELD=>"Detail"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"'_ICON_1_' AS icon1"
		,_LAB_FIELD=>"Editing"
		,_TYP_FIELD=>100
		,_RID_FIELD=>'CUSTOMER_Username' // field id to put in _SID_1_ to _SID_3_ format Service_ID,Provider_ID
		,_ICO_FIELD=>$pattern_icons['_ICON_1_']
		,_SORT_BOC_FIELD=>true)		// hide in sort box
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
			<h1>
				Customer Account
				<a href="#" data-toggle="modal" id="_NEW_" data-target="#addEditCustomerAccModal"><i class="fa fa-fw fa-plus-circle" style="font-size:30px"></i></a>
			</h1>
			<hr>
			
			<div class="container-fluid mb-2">
				<?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar_dnm.php");?>
			</div>
			<br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_add_edit_customer.php");
}
?>
<script type='text/javascript'>	
	var i = 1;
	const x = "<?php echo $counterState; ?>";
	var b = parseInt(x);

	$(document).on('click', '#add_field', function() {
	
		if( i == 4 ) {
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

		select1.onchange = function() {
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
		};

		select2.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
		};

		select3.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select4, this.selectedIndex);
		};

		select4.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
			preventDupes.call(this, select2, this.selectedIndex);
			preventDupes.call(this, select3, this.selectedIndex);
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


