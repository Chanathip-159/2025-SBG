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
					,SENDER_Username = '".$_POST['SENDER_Username']."'
					,SENDER_Status='".$_POST['SENDER_Status']."'
					,CATEGORY_Id='".$_POST['CATEGORY_Id']."'
					,PRIORITY_flag='".$_POST['PRIORITY_flag']."'
					WHERE SENDER_Sender ='".$_POST['add_edit_sender_id']."'
					AND SENDER_Username = 'SENDER_Username'
					";
	
	// if (querySqlEx($sql) == false) {
	// 	// if not admin but try to change username
	// 	// return error
	// 	$_GET['toast_type']="F";
	// 	$_GET['toast_header']="Fail to process";
	// 	$_GET['toast_message']="Fail to update sender";
	// } else {
	// 	$_GET['toast_type']="S";
	// 	$_GET['toast_header']="Result message";
	// 	$_GET['toast_message']="Update sender successfully";
	// }
}

if($_POST['del_item']) {
	$sql = "DELETE FROM SBG.dbo.SENDERS 
					WHERE SENDER_Sender ='".$_POST['del_item']."' 
					AND SENDER_Username = '".$_POST['SENDER_Username']."'
					";

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
	$str_sql_Insert = "";
	$str_sql_notInsert = "";
	$i = 0;
	$j = count($split);

	foreach($split as $split_data) {
		$pattern_pipe = "/[|]/";
		$split_pipe = preg_split($pattern_pipe, $split_data);

		$array_sql[$i] = $split_pipe;
		$i++;
	}

	// $sql = "SELECT SENDER_Username, SENDER_Sender FROM SBG.dbo.SENDERS";
	// $result = querySqlEx($sql);

	// while ($row=$result->fetch(PDO::FETCH_ASSOC)) 
	// {
		// for($i=0; $i<$j; $i++)
		// {
		// 	echo $array_sql[$i][0];
		// 	$sql_check = "SELECT SENDER_Username, SENDER_Sender FROM SBG.dbo.SENDERS 
		// 					WHERE SENDER_Username IN (".$array_sql[$i][0].");
		// 					";
		// 	echo $sql_check;
		// }

  // $result = mysqli_query($db,$sql);
	// 	for($i=0; $i<$j; $i++)
	// 	{
	// 		if($row['SENDER_Username'] == $array_sql[$i][0] && $row['SENDER_Sender'] == $array_sql[$i][1])
	// 		{
	// 			echo $array_sql[$i][0] . '|' . $array_sql[$i][1] . '<br>';

	// 			// $str_sql_notInsert .= "(
	// 			// 	'".$array_sql[$i][0]."',
	// 			// 	'".$array_sql[$i][1]."',
	// 			// 	".$array_sql[$i][2].",
	// 			// 	".$array_sql[$i][3].",
	// 			// 	".$array_sql[$i][4]."
	// 			// ),";
	// 		}
	// 	}
	// }
	// echo $str_sql_notInsert;

	for($i=0; $i<$j; $i++)
	{
		$str_sql .= "(
			'".$array_sql[$i][0]."',
			'".$array_sql[$i][1]."',
			'".$create_dt."',
			'".$update_dt."',
			".$array_sql[$i][2].",
			".$array_sql[$i][3].",
			".$array_sql[$i][4]."
		),";

			// $insert_data[]="('".$array_sql[$i][0]."','".$array_sql[$i][1]."','".$array_sql[$i][2]."','".$array_sql[$i][3]."')";
	}
	if (isset($str_sql))
	{
		$sql = "INSERT INTO SBG.dbo.SENDERS (SENDER_Username, SENDER_Sender, SENDER_CreateDt, SENDER_UpdateDt, SENDER_Status, CATEGORY_Id, PRIORITY_flag)VALUES".substr($str_sql, 0, -1);
		// $stmt=insertSqlEx($sql);
		echo $sql;
	}
	// if ($stmt == false) {
	// 	$_GET['toast_type']="F";
	// 	$_GET['toast_header']="Fail to process";
	// 	$_GET['toast_message']="Fail to upload sender";
	// }else{
	// 	$_GET['toast_type']="S";
	// 	$_GET['toast_header']="Result message";
	// 	$_GET['toast_message']="Upload sender successfully";
	// }
} else {
	//
}

$year_list=null;

if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

$pattern_icons['_ICON_1_'] = "<button title=\"Edit Sender\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";
$pattern_icons['_ICON_1_'] .= "<button title=\"Delete Sender\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";

$sql_from="SBG.dbo.SENDERS";
$sql_where="";
$orderby="SENDER_Sender";
$table_mode=1;// 0=white and gray; 1=color; 2=mix
$table_font_size="small";

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
			</form>
			<hr>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_add_edit_sender.php");
}

?>
