<style>
	.btn-info, .btn-secondary, .btn-danger{
	width: 90px !important;
	}
</style>
<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ���ͺ������
# Edit zone
############################################
$dest_file="first";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE]=$dest_file;		

require_once("element/_e_check_permission.php");

$pattern_icons['_ICON_1_'] = "<button title=\"Edit Account\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#addEditCustomerAccModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";
if ($_SESSION[_SES_USR_TYP_ID] <= 2) { // more than _SES_USR_SYS_ADMIN
	$pattern_icons['_ICON_1_'] .= 
	"<button title=\"Delete Account\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#addEditCustomerAccModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";
}

// // -------------------------- logic add parent -----------------------------
// if (strlen($_POST['add_parent_customer_id'])) {

// 	$fields=Array(); $datas=Array();

// 	if ($_POST['add_parent_customer_id']=="_ADD_" && strlen($_POST['CUSTOMER_Parent_Username'])) {
// 		$user_id=strtolower($_POST['CUSTOMER_Parent_Username']);
// 	} else {
// 		$user_id=$_POST['add_parent_customer_id'];
// 	}

// 	if (isset($user_id)) {
		
// 		// check CUSTOMER_Parent_Username and username not same
// 		if ($_POST['CUSTOMER_Parent_Username'] != $_SESSION[_SES_USR_NAME]) 
// 		{

// 			$sql="SELECT COUNT(*) AS Counting FROM SBG.dbo.CUSTOMERS WHERE LOWER(CUSTOMER_Username)=LOWER(?)";
// 			if (querySqlSingleFieldEx($sql,[$user_id]))
// 			{
// 				$sql_check_sub ="SELECT COUNT(*) AS Count_sub FROM SBG.dbo.CUSTOMERS WHERE [CUSTOMER_Parent_Username]='".$_SESSION[_SES_USR_NAME]."'";
// 				$count_sub = querySqlSingleRowEx($sql_check_sub);

// 				// CUSTOMER_AccountUsage from Parent
// 				$sql_accUsage_parent = "SELECT CUSTOMER_AccountUsage
// 																FROM [SBG].[dbo].[CUSTOMERS]
// 																WHERE LOWER(CUSTOMER_Username)='".$_SESSION[_SES_USR_NAME]."'
// 																";
// 				$acc_parent = querySqlSingleRowEx($sql_accUsage_parent);
// 				$accUsage_parent = $acc_parent['CUSTOMER_AccountUsage'];

// 				// CUSTOMER_AccountUsage from SUM SUB AccountUsage
// 				$sql_accUsage_sub = "SELECT SUM(CUSTOMER_AccountUsage) as CUSTOMER_AccountUsage
// 															FROM [SBG].[dbo].[CUSTOMERS]
// 															WHERE LOWER(CUSTOMER_Parent_Username)='".$_SESSION[_SES_USR_NAME]."'
// 															";
// 				$acc_sub = querySqlSingleRowEx($sql_accUsage_sub);
// 				$accUsage_sub = $acc_sub['CUSTOMER_AccountUsage'];

// 				// CUSTOMER_AccountUsage from NEW SUB
// 				$sql_newSubUsage= "SELECT CUSTOMER_AccountUsage as CUSTOMER_AccountUsage_newSub
// 															FROM [SBG].[dbo].[CUSTOMERS]
// 															WHERE LOWER(CUSTOMER_Username)='".$_POST['CUSTOMER_Parent_Username']."'
// 															";
// 				$acc_newSub = querySqlSingleRowEx($sql_newSubUsage);
// 				$accUsage_NewSub = $acc_newSub['CUSTOMER_AccountUsage_newSub'];

// 				$_SESSION['remain_usage'] = $accUsage_parent - $accUsage_sub;
// 				// echo $_SESSION['remain_usage'];
// 				// echo '<br>';
// 				// echo $_POST['CUSTOMER_AccountUsage'];

// 				//check account usage input and update to sub-user table
// 				if($_SESSION['remain_usage'] >= $_POST['CUSTOMER_AccountUsage'])
// 				{
// 					$sql = "UPDATE [SBG].[dbo].[CUSTOMERS]
// 									SET CUSTOMER_AccountUsage=".$_POST['CUSTOMER_AccountUsage']."
// 									WHERE CUSTOMER_Username = '".$user_id."'
// 									";
// 					if (!querySqlEx($sql)) {
// 						$_GET['toast_type']="F";
// 						$_GET['toast_header']="Fail to process";
// 						$_GET['toast_message']="Fail to update account usage";

// 					} else { // success to update account usage
						
// 						if($count_sub['Count_sub'] == 0)
// 						{
// 							if($accUsage_parent >= $accUsage_NewSub)
// 							{
// 								$sql="UPDATE [SBG].[dbo].[CUSTOMERS]
// 											SET [CUSTOMER_Parent_Username]='".$_SESSION[_SES_USR_NAME]."'
// 											WHERE LOWER(CUSTOMER_Username)='".$_POST['CUSTOMER_Parent_Username']."'
// 											";

// 								if (!querySqlEx($sql,$datas)) {
// 									$_GET['toast_type']="F";
// 									$_GET['toast_header']="Fail to process";
// 									$_GET['toast_message']="Fail to add sub-user";
// 								}else{
// 									$_GET['toast_type']="S";
// 									$_GET['toast_header']="Result message";
// 									$_GET['toast_message']="Add sub-user successfully";
// 								}
// 							} else {
// 								$_GET['toast_type']="E";
// 								$_GET['toast_header']="Error message";
// 								$_GET['toast_message']="Remain usage's not enoughh [remain usage: ".$_SESSION['remain_usage'].", sub-user usage: ".$accUsage_NewSub."] ";
// 							}
// 						} else if($accUsage_parent >= $accUsage_sub) {

// 							$sql="UPDATE [SBG].[dbo].[CUSTOMERS]
// 										SET [CUSTOMER_Parent_Username]='".$_SESSION[_SES_USR_NAME]."'
// 										WHERE LOWER(CUSTOMER_Username)='".$_POST['CUSTOMER_Parent_Username']."'
// 										";

// 							if (!querySqlEx($sql,$datas)) {
// 								$_GET['toast_type']="F";
// 								$_GET['toast_header']="Fail to process";
// 								$_GET['toast_message']="Fail to add sub-user";
// 							}else{
// 								$_GET['toast_type']="S";
// 								$_GET['toast_header']="Result message";
// 								$_GET['toast_message']="Add sub-user successfully";
// 							}
// 						}	else {
// 							$_GET['toast_type']="E";
// 							$_GET['toast_header']="Error message";
// 							$_GET['toast_message']="Remain usage's not enough [remain usage: ".$_SESSION['remain_usage'].", sub-user usage: ".$accUsage_NewSub."] ";
// 						}
// 					}
// 				} else {
// 					$_GET['toast_type']="E";
// 					$_GET['toast_header']="Error message";
// 					$_GET['toast_message']="Remain usage's not enough [remain usage: ".$_SESSION['remain_usage'].", your input usage: ".$_POST['CUSTOMER_AccountUsage']."] ";
// 				}
// 			}
// 		} else {
// 			$_GET['toast_type']="F";
// 			$_GET['toast_header']="Fail to process";
// 			$_GET['toast_message']="Fail to add sub-user (Unable to add sub-user with the same name.)";
// 		}
// 	} else {
// 		$_GET['toast_type']="E";
// 		$_GET['toast_header']="Error message";
// 		$_GET['toast_message']="Unknown user account";
// 	}
// }

// ------------------------------ edit sub user ------------------------------------
if (strlen($_POST['add_edit_customer_id'])) {
	$fields=Array(); $datas=Array();
	if ($_SESSION[_SES_USR_TYP_ID]>_ADMIN_L&&$_POST['add_edit_customer_id']==$_SESSION[_SES_USR_NAME]) {
		// not allow any user change username except user that has level under that 2 to 0
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Not allow you to change username";
	}else{
		if ($_POST['add_edit_customer_id']=="_NEW_" && strlen($_POST['CUSTOMER_Username'])) {
			$user_id=strtolower($_POST['CUSTOMER_Username']);
		}else{
			$user_id=$_POST['add_edit_customer_id'];
		}
		if (isset($user_id)) {
			$sql="SELECT COUNT(*) AS Counting FROM SBG.dbo.CUSTOMERS WHERE LOWER(CUSTOMER_Username)=LOWER(?)";
			if (querySqlSingleFieldEx($sql,[$user_id])) {

				// check update usage
				if(isset($_POST['CUSTOMER_AccountUsage']))
				{
					// CUSTOMER_AccountUsage from Parent
					$sql_accUsage_parent = "SELECT CUSTOMER_AccountUsage 
															FROM [SBG].[dbo].[CUSTOMERS] 
															WHERE LOWER(CUSTOMER_Username)='".$_SESSION[_SES_USR_NAME]."'
															";
					$acc_parent = querySqlSingleRowEx($sql_accUsage_parent);
					$accUsage_parent = $acc_parent['CUSTOMER_AccountUsage'];

					$sql = "SELECT SUM(CUSTOMER_AccountUsage) as SUM_NoSubEdit FROM SBG.dbo.CUSTOMERS
									WHERE LOWER(CUSTOMER_Parent_Username)='".$_SESSION[_SES_USR_NAME]."'
									AND CUSTOMER_Username != '".$user_id."'
								 ";
					$acc_sum = querySqlSingleRowEx($sql);
					$sum_no_subedit = $acc_sum['SUM_NoSubEdit'];
					$count_usage_new = $sum_no_subedit + $_POST['CUSTOMER_AccountUsage'];

					if($accUsage_parent < $count_usage_new)
					{
						$_GET['toast_type']="E";
						$_GET['toast_header']="Error message";
						$_GET['toast_message']="Remain usage's not enough [current usage: ".$count_usage_new."/".$accUsage_parent." ]";
					} else {
						$sql="UPDATE SBG.dbo.CUSTOMERS
									SET CUSTOMER_UpdateDt=GETDATE()
									,CUSTOMER_MonthlyUsage=?
									,CUSTOMER_AccountUsage=?
									WHERE LOWER(CUSTOMER_Username)=LOWER(?)";

						$datas[]=$_POST['CUSTOMER_MonthlyUsage'];
						$datas[]=$_POST['CUSTOMER_AccountUsage'];
						$datas[]=$user_id;

						if (querySqlEx($sql,$datas) == false) {
							$_GET['toast_type']="F";
							$_GET['toast_header']="Fail to process";
							$_GET['toast_message']="Fail to update account usage";
						}else{
							if (isset($_POST['CUSTOMER_MonthlyUsage_Apply'])&&$_POST['CUSTOMER_MonthlyUsage_Apply']==1) {
								$sql="UPDATE SBG.dbo.REPORTS SET REPORT_MonthQuota=? WHERE LOWER(REPORT_Username)=LOWER(?) AND REPORT_YearMonth=?";
								querySqlEx($sql,[$_POST['CUSTOMER_MonthlyUsage'],$user_id,date("Ym")]);
							}
			
							$_GET['toast_type']="S";
							$_GET['toast_header']="Result message";
							$_GET['toast_message']="Update success. [current usage: ".$count_usage_new."/".$accUsage_parent." ]";
						}
					}
				}
			}
		}else{
			$_GET['toast_type']="E";
			$_GET['toast_header']="Error message";
			$_GET['toast_message']="Unknown user account";
		}
	}
}
// ------------------------------ edit sub user ------------------------------------

$sql="SELECT * FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=?";
if ($profile=querySqlSingleRowEx($sql,[$_SESSION[_SES_USR_NAME]])) {
?>
			<h1>Profile</h1>
			<hr>
			<div class="col-md-12 justify-content-center">
				<div class="card ml-2 mb-4">
					<div class="card-header bg-primary text-white">
						<div class="row">
							<div class="col">
								<h4><?=$profile['CUSTOMER_Username']?></h4>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6 text-right">Contact</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_Contact']?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Contact Phone No.</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_Telephone']?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">e-Mail</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_Email']?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Delivery Report</div>
							<div class="col-md-6"><?=($profile['CUSTOMER_NeedDr']==1?"Use":"Not use")?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Active Date</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_ActivateDate']?> 00:00:00.000</div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Expire Date</div>
							<div class="col-md-6"><?=date("Y-m-d",strtotime($profile['CUSTOMER_ExpireDate'])-86400)?> 23:59:59.999</div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Monthly Usage Limit</div>
							<div class="col-md-6"><?=($profile['CUSTOMER_MonthlyUsage']==-1?"Unlimited":$profile['CUSTOMER_MonthlyUsage']." SMS")?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Account Usage Limit</div>
							<div class="col-md-6"><?=($profile['CUSTOMER_AccountUsage']==-1?"Unlimited":$profile['CUSTOMER_AccountUsage']." SMS")?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Current Usage</div>
							<div class="col-md-6"><?=(strlen($profile['CUSTOMER_CurrentUsage'])?$profile['CUSTOMER_CurrentUsage']:"0")?> SMS</div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Create Date</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_CreateDt']?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Update Date</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_UpdateDt']?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Status</div>
							<div class="col-md-6"><?=($profile['CUSTOMER_Status']==1?"Active":"Idle")?></div>
						</div>
						<div class="row">
							<div class="col-md-6 text-right">Detail</div>
							<div class="col-md-6"><?=$profile['CUSTOMER_Detail']?></div>
						</div>
						<hr>

						<?php
							$sql_get_parent = "SELECT * FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Parent_Username = '".$_SESSION[_SES_USR_NAME]."' ";
							$result_get_parent = querySqlSingleFieldEx($sql_get_parent);

							if(isset($result_get_parent))
							{
								$sql_from="SBG.dbo.CUSTOMERS";
								$sql_where="CUSTOMER_Parent_Username = '".$_SESSION[_SES_USR_NAME]."'";
								$orderby="CUSTOMER_Username";
								$table_mode=1;// 0=white and gray; 1=color; 2=mix
								$table_font_size="small";
								
								// create table configuration array
								// _TYP_FIELD:: -1=icon and not query; 0=not show; 1=string; 2=date time; 3=number; 4=money; 100=pattern icon or no search; 200=sum/count (num); 201=sum/count (money)
								$fields_info=Array(
									 Array(_SQL_FIELD=>"CUSTOMER_Username" // can use [ISAG].[dbo].[Users].[User_CreateDate] // can use [ISAG].[dbo].[Users].[User_Name] AS Name
										,_LAB_FIELD=>"Username"
										,_TYP_FIELD=>1
										,_SORT_FIELD=>1
										,_SEA_FIELD=>true)
									,Array(_SQL_FIELD=>"CUSTOMER_Telephone"
										,_LAB_FIELD=>"Phone No."
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
									,Array(_SQL_FIELD=>"'_ICON_1_' AS icon1"
										,_LAB_FIELD=>"Editing"
										,_TYP_FIELD=>100
										,_RID_FIELD=>'CUSTOMER_Username' // field id to put in _SID_1_ to _SID_3_ format Service_ID,Provider_ID
										,_ICO_FIELD=>$pattern_icons['_ICON_1_']
										,_SORT_BOC_FIELD=>true)		// hide in sort box
								);
								############################################
								require_once("element/_e_list_items_z1.php");
								require_once("element/_e_list_items_z2.php");		
							} else {
								echo '<div class="text-center">empty Sub-user</div>';
							}
						?>
					</div>
				</div>
			</div>
<?php
}else{
	if ($_SESSION[_SES_USR_TYP_ID]<=10) {?>
			<!-- <h1>This is Admin account.</h1>
			<hr> -->
				<!-- <div class="form-group col-md-12 text-center" style="height: 280px;">
					<canvas id="canvas_main"></canvas>
				</div> -->
				<div class="form-group col-md-12 text-center" style="height: 280px;">
					<canvas id="canvas_main2"></canvas>
				</div>
				<div class="form-group col-md-12 text-center">
					<button class="btn btn-primary" id="refresh" onclick="refresh();">Refresh Now</button> <div id="last_refresh"></div>
				</div>
				<script>
					var refresh_duration = 30000; // 1000 = 1 sec	
					var refresh_smsgw = "monitor.php?mode=smsgw";
					var refresh_smsgw_sbg = "monitor.php?mode=smsgw_sbg";
					
					var barChartMain = {
						labels : [0],
						datasets: [{
							label: 'Traffic',
							backgroundColor: window.chartColors.orange,
							data: [0]
						}]
					};
					var barChartMain2 = {
						labels : [0],
						datasets: [{
							label: 'Traffic',
							backgroundColor: window.chartColors.green,
							data: [0]
						}]
					};
					window.onload = function() {
						// var ctx_main = document.getElementById('canvas_main').getContext('2d');
						// window.barMain = new Chart(ctx_main, {
						// 	type: 'bar',
						// 	data: barChartMain,
						// 	options: {
						// 		maintainAspectRatio: false,
						// 		animation: false,
						// 		title: {
						// 			display: true,
						// 			text: "All SMS Application Gateway (trans per 5 min)",
						// 			fontSize: 30
						// 		},
						// 		legend: {
						// 			// hide dataset label
						// 			display: false
						// 		},
						// 		tooltips: {
						// 			mode: 'index',
						// 			intersect: false
						// 		},
						// 		responsive: true,
						// 		scales: {
						// 			xAxes: [{
						// 				stacked: true,
						// 			}],
						// 			yAxes: [{
						// 				stacked: true
						// 			}]
						// 		}
						// 	}
						// });
						var ctx_main2 = document.getElementById('canvas_main2').getContext('2d');
						window.barMain2 = new Chart(ctx_main2, {
							type: 'bar',
							data: barChartMain2,
							options: {
								maintainAspectRatio: false,
								animation: false,
								title: {
									display: true,
									text: "SMS Bulk Gateway (trans per 5 min)",
									fontSize: 30
								},
								legend: {
									// hide dataset label
									display: false
								},
								tooltips: {
									mode: 'index',
									intersect: false
								},
								responsive: true,
								scales: {
									xAxes: [{
										stacked: true,
									}],
									yAxes: [{
										stacked: true
									}]
								}
							}
						});
						refresh();
					};
					
					function refresh() {
						refresh_http(refresh_smsgw, window.barMain, barChartMain);
						refresh_http(refresh_smsgw_sbg, window.barMain2, barChartMain2);
						var dt = new Date();
						var elem = document.getElementById("last_refresh");
						elem.textContent = dt.toTimeString();
					}

					function refresh_http(path, chart_obj, chart_data) {
						var client = new XMLHttpRequest();
						client.open('GET', path);
						client.onreadystatechange = function() {
							var update_data = client.responseText.split("BREAK");
							chart_data.labels = eval("["+update_data[0]+"]"); // cannot use [ and ] in string inside eval command 
							chart_data.datasets.forEach(function(dataset) {
								dataset.data = eval("["+update_data[1]+"]");
							});
							chart_obj.update();
						}
						client.send();
					}
					
					
					
					setInterval(function() {refresh();}, refresh_duration); 
				</script>
<?php
	}else{
		echo "Error";
	}
}

// require_once("element/_p_dialog_add_parent.php");
require_once("element/_p_dialog_edit_sub.php");

?>