<style>
	.btn-info, .btn-secondary, .btn-danger{
	width: 90px !important;
	}
	.fa-plus-circle {
      color: #3366CC;
    }
</style>
<?php
date_default_timezone_set("Asia/Bangkok");
require_once(__DIR__."/../php.lib/function.basic.php");
require_once(__DIR__."/../php.lib/function.basic.db.php");
require_once("element/_1_defines.php"); #use for define variable such as _CEO,_DEB
require_once("element/_2_var_static.php"); #use for static variable such as month name
require_once("element/_3_functions_base.php"); #use for basic functions such as echoMsg
require_once("element/_4_functions_db.php"); # db connect and db functions

$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
# Edit zone
############################################
$dest_file="sender";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE]=$dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");	

$datas=Array();

if($_POST['add_edit_sender_id'])
{
	$sql = "UPDATE SBG.dbo.SENDERS
					SET SENDER_Sender='".$_POST['SENDER_Sender']."'
					,SENDER_Status='".$_POST['SENDER_Status']."'
					,CATEGORY_Id='".$_POST['CATEGORY_Id']."'
					,PRIORITY_flag='".$_POST['PRIORITY_flag']."'
					WHERE SENDER_Sender ='".$_POST['add_edit_sender_id']."'
					AND SENDER_Username ='".$_POST['SENDER_Username']."'
					";
					// echo $sql;
	
	if (querySqlEx($sql) == false) {
		// if not admin but try to change username
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to update sender";
	} else {
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']="Update sender successfully";
	}
}

if($_POST['del_item']) {
	$sql = "DELETE FROM SBG.dbo.SENDERS WHERE SENDER_Sender ='".$_POST['del_item']."' ";

	if (querySqlEx($sql) == false) {
		// if not admin but try to change username
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to Delete sender";
	} else {
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']="Delete sender successfully";
	}
}


if($_POST['submit_add_sender'])
{
	$current_date = date("Ymd");
	$create_dt = date("Y-m-d h:i:s.000");
	$update_dt = date("Y-m-d h:i:s.000");

	$file_name = $_FILES['fileToUpload']['tmp_name']; 

	$get_content = file_get_contents($file_name);

	$pattern_enter = "/[\n]/";
	$split = preg_split($pattern_enter, $get_content);
	// array_shift($split);

	$str_sql = "";
	$_SESSION['str_msg'] = "";
	// $str_msg = "";

	$str_sql_err1 = "";
	$_SESSION['str_err1_msg'] = "";

	$str_sql_err2 = "";
	$_SESSION['str_err2_msg'] = "";
	// $str_err2_msg = "";

	$i = 0;
	$j = count($split);
	// echo $j;

	foreach($split as $split_data) {
		$pattern_pipe = "/[|]/";
		$split_pipe = preg_split($pattern_pipe, $split_data);

		$array_sql[$i] = $split_pipe;
		$i++;
	}

	for($i=0; $i<$j; $i++)
	{
		$sql_chk_user = "SELECT count(CUSTOMER_Username) as count
										 FROM SBG.dbo.CUSTOMERS 
										 WHERE CUSTOMER_Username = '".$array_sql[$i][0]."' 
										";

		$sql_chk_user_sender = "SELECT SENDER_Username as username, SENDER_Sender as sender 
														FROM SBG.dbo.SENDERS 
														WHERE SENDER_Username = '".$array_sql[$i][0]."' 
														AND SENDER_Sender = '".$array_sql[$i][1]."';
													 ";
		
		$sql_chk_sender = "SELECT count(SENDER_Sender) as count_sender, SENDER_Sender as sender, SENDER_Status, CATEGORY_ID as category, PRIORITY_Flag as priority 
											 FROM SBG.dbo.SENDERS 
											 WHERE SENDER_Sender = '".$array_sql[$i][1]."'
											";

		$stmt_chk_user = querySqlEx($sql_chk_user);
		$stmt_chk_user_sender = querySqlEx($sql_chk_user_sender);
		$stmt_chk_sender = querySqlEx($sql_chk_sender);

		while ($row_chk_use = $stmt_chk_user->fetch(PDO::FETCH_ASSOC))
		{
			// check isset username on SBG.dbo.CUSTOMERS 
			if($row_chk_use['count'] > 0)
			{
				if( $stmt_chk_user_sender->fetch(PDO::FETCH_ASSOC) )
				{
					// check username and sender already on SBG.dbo.SENDERS
					if($row_chk_user_sender['username'] = $array_sql[$i][0] && $row_chk_user_sender['sender'] = $array_sql[$i][1])
					{
						$msg_err2 = 'ไม่สามารถเพิ่ม sender ดังต่อไปนี้ได้ เนื่องจากมีข้อมูลอยู่แล้ว';
						$str_sql_err2 .= "(
							'".$array_sql[$i][0]."',
							'".$array_sql[$i][1]."',
							".$array_sql[$i][2].",
							".$array_sql[$i][3].",
							".$array_sql[$i][4]."
						),";

						$_SESSION['str_err2_msg'] .= $array_sql[$i][0].'|'.
															$array_sql[$i][1].'|'.
															$array_sql[$i][2].'|'.
															$array_sql[$i][3].'|'.
															$array_sql[$i][4].'<br>';
					}
				} else {
					$msg_result = 'เพิ่มได้';
					$str_sql .= "(
						'".$array_sql[$i][0]."',
						'".$array_sql[$i][1]."',
						'".$create_dt."',
						'".$update_dt."',
						".$array_sql[$i][2].",
						".$array_sql[$i][3].",
						".$array_sql[$i][4]."
					),";

					$_SESSION['str_msg'] .= $array_sql[$i][0].'|'.
												$array_sql[$i][1].'|'.
												$array_sql[$i][2].'|'.
												$array_sql[$i][3].'|'.
												$array_sql[$i][4].'<br>';
				}
			} else {
				$msg_err1 = 'ไม่สามารถเพิ่ม sender ดังต่อไปนี้ได้ เนื่องจากไม่มี username อยู่ในระบบ ';
				$str_sql_err1 .= "(
					'".$array_sql[$i][0]."',
					'".$array_sql[$i][1]."',
					".$array_sql[$i][2].",
					".$array_sql[$i][3].",
					".$array_sql[$i][4]."
				),";

				$_SESSION['str_err1_msg'] .= $array_sql[$i][0].'|'.
													$array_sql[$i][1].'|'.
													$array_sql[$i][2].'|'.
													$array_sql[$i][3].'|'.
													$array_sql[$i][4].'<br>';
			}
		}
		// $insert_data[]="('".$array_sql[$i][0]."','".$array_sql[$i][1]."','".$array_sql[$i][2]."','".$array_sql[$i][3]."')";
	}

	// if(isset($msg_err1) || isset($msg_err2))
	// {
	// 	echo $msg_err1;
	// 	echo '<br>';
	// 	echo $str_sql_err1;
	// 	echo '<br>';
	// 	echo '------------------------';
	// 	echo '<br>';
	// 	echo $msg_err2;
	// 	echo '<br>';
	// 	echo $str_sql_err2;
	// }
	// echo '<br>';
	// echo '------------------------';
	// echo '<br>';
	// if(isset($msg_result)) 
	// {
	// 	echo $msg_result;
	// 	echo '<br>';
	// 	echo $str_sql;
	// 	echo '<br><br>';
	// }

	if (isset($str_sql))
	{
		$sql = "INSERT INTO SBG.dbo.SENDERS (SENDER_Username, SENDER_Sender, SENDER_CreateDt, SENDER_UpdateDt, SENDER_Status, CATEGORY_Id, PRIORITY_flag)VALUES".substr($str_sql, 0, -1);
		// $stmt=insertSqlEx($sql);
		// echo $sql;
	}

	if ($sql) # change $sql to $stmt
	{
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']= "Upload sender successfully.";
		
		// $_GET['toast_message']= "Upload sender successfully. <br> => ".$str_msg." <br> Can not upload sender because user account does not exist. <br> => ".$str_err2_msg." <br> Can not upload sender because already exist. <br> => ".$str_err1_msg." ";
	?>
	<script>window.open("./element/_p_senderUpload_detail.php", '_blank');	</script>
	<?php
	} else {
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to upload sender";
	}
}

$search_bar = "element/_e_search_bar_sender.php"; // "_e_search_bar.php"

// start set year by get last 5 year
for($i=date("Y")-5; $i<=date("Y"); $i++) {
	$year_list[] = $i;
}

$year_list=null;

if (strlen($_POST[_USERNAME])) $username = $_POST[_USERNAME]; else $username = null;
if (strlen($_POST[_SENDER])) $sender = $_POST[_SENDER]; else $sender = null;
if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

$pattern_icons['_ICON_1_'] = "<button title=\"Edit Sender\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";
$pattern_icons['_ICON_1_'] .= "<button title=\"Delete Sender\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";

$sql_from="SBG.dbo.SENDERS";
$sql_where="SENDER_Status = '1'";
$orderby="SENDER_Sender";
$table_mode=1;// 0=white and gray; 1=color; 2=mix
$table_font_size="small";

$save_enable = true;

// create table configuration array
// _TYP_FIELD:: -1=icon and not query; 0=not show; 1=string; 2=date time; 3=number; 4=money; 100=pattern icon or no search; 200=sum/count (num); 201=sum/count (money)
$fields_info=Array(
	 Array(_SQL_FIELD=>"SENDER_Sender" // can use [ISAG].[dbo].[Users].[User_CreateDate] // can use [ISAG].[dbo].[Users].[User_Name] AS Name
		,_LAB_FIELD=>"Sender"
		,_TYP_FIELD=>1
		,_SORT_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"SENDER_Status"
		,_LAB_FIELD=>"Status"
		,_TYP_FIELD=>3)
	,Array(_SQL_FIELD=>"SENDER_CreateDt"
		,_LAB_FIELD=>"Create DT"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"SENDER_UpdateDt"
		,_LAB_FIELD=>"Update DT"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"SENDER_Username"
		,_LAB_FIELD=>"Username"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"'_ICON_1_' AS icon1"
		,_LAB_FIELD=>"Action"
		,_TYP_FIELD=>100
		,_RID_FIELD=>'SENDER_Sender' // field id to put in _SID_1_ to _SID_3_ format Service_ID,Provider_ID
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
				Sender Management
			</h1>
			<hr>
			<h5><i class="fa fa-cog"></i>&nbsp;Add or Upload Sender</h5>
			<br>
			<form enctype="multipart/form-data" role="form" name="form_add_sender" action="<?=_MAIN_DF;?>sender" method="POST">
				<div class="row col-md-4 mb-3 custom-file">
					<input type="file" name="fileToUpload" id="fileToUpload" required>
				</div><br>
				<input type="submit" id="submit_add_sender" name="submit_add_sender" class="btn btn-primary text-white" value="submit">
			</form><br>
			<img src="./img/ex_sender_pattern.png" width='500' height='350'>
			<hr>

			<div class="container-fluid mb-2">
				<?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar_sender.php");?>
			</div>
			<br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_add_edit_sender.php");
}

?>
