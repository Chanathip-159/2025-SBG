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
$dest_file="keyword";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE]=$dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");	

if($_POST['add_edit_sender_id']) {
	$sql = "UPDATE SBG.dbo.KEYWORD
					SET KEYWORD_Name='".$_POST['KEYWORD_Name']."'
					,KEYWORD_Description='".$_POST['KEYWORD_Description']."'
					WHERE KEYWORD_Name ='".$_POST['add_edit_sender_id']."'
					AND KEYWORD_Username ='".$_SESSION[_SES_USR_NAME]."'
					";
					// echo $sql;
	
	if (querySqlEx($sql) == false) {
		// if not admin but try to change username
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to update keyword";
	} else {
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']="Update keyword successfully";
	}
}

if($_POST['del_item']) {
	$sql = "DELETE FROM SBG.dbo.KEYWORD WHERE KEYWORD_Name ='".$_POST['del_item']."' ";

	if (querySqlEx($sql) == false) {
		// if not admin but try to change username
		// return error
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to Delete keyword";
	} else {
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']="Delete keyword successfully";
	}
}


if($_POST['submit_add_keyword']) {
	$current_date = date("Ymd");
	$create_dt = date("Y-m-d h:i:s.000");
	$update_dt = date("Y-m-d h:i:s.000");

	$keyword = $_POST['keyword'];
	$desc = $_POST['desc'];

	$sql_chk_keyword = "SELECT count(KEYWORD_Name) as count FROM SBG.dbo.KEYWORD WHERE KEYWORD_Name = '".$keyword."' ";

	$count_keyword = querySqlSingleRowEx($sql_chk_keyword);

	if($count_keyword['count'] > 0) {
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="This keyword already exists.";
	} else {
		$sql = "INSERT INTO SBG.dbo.KEYWORD (KEYWORD_Name, KEYWORD_Description, KEYWORD_CreateDt, KEYWORD_UpdateDt, KEYWORD_Username)
						VALUES ('".$keyword."', '".$desc."', '".$create_dt."', '".$update_dt."', '".$_SESSION[_SES_USR_NAME]."')
					";
		// echo $sql_insert;

		if (querySqlEx($sql) == false) {
			// if not admin but try to change username
			// return error
			$_GET['toast_type']="F";
			$_GET['toast_header']="Fail to process";
			$_GET['toast_message']="Fail to upload keyword";
		} else {
			$_GET['toast_type']="S";
			$_GET['toast_header']="Result message";
			$_GET['toast_message']="Upload keyword successfully.";
		}
	}
}

$search_bar = "element/_e_search_bar_keyword.php"; // "_e_search_bar.php"

if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

// start set year by get last 5 year
for($i=date("Y")-5; $i<=date("Y"); $i++) {
	$year_list[] = $i;
}

$year_list=null;

$pattern_icons['_ICON_1_'] = "<button title=\"Edit Sender\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";
$pattern_icons['_ICON_1_'] .= "<button title=\"Delete Sender\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";

$sql_from="SBG.dbo.KEYWORD";
$sql_where="";
// $orderby="";
$table_mode=1;// 0=white and gray; 1=color; 2=mix
$table_font_size="small";

$save_enable = true;

// conditions for pattern_icon on Action field.
if($_SESSION[_SES_USR_NAME] != 'admin') { // user
	$ico_conditions = "IIF(KEYWORD_Username = '".$_SESSION[_SES_USR_NAME]."', '_ICON_1_', '*Not allow to edit') AS icon1";
} else { // admin 
	$ico_conditions = "IIF(KEYWORD_Username != '', '_ICON_1_', '*Not allow to edit') AS icon1";
}

// create table configuration array
// _TYP_FIELD:: -1=icon and not query; 0=not show; 1=string; 2=date time; 3=number; 4=money; 100=pattern icon or no search; 200=sum/count (num); 201=sum/count (money)
$fields_info=Array(
	 Array(_SQL_FIELD=>"KEYWORD_Name" // can use [ISAG].[dbo].[Users].[User_CreateDate] // can use [ISAG].[dbo].[Users].[User_Name] AS Name
		,_LAB_FIELD=>"Name"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"KEYWORD_Description"
		,_LAB_FIELD=>"Description"
		,_TYP_FIELD=>1)
	,Array(_SQL_FIELD=>"KEYWORD_CreateDt"
		,_LAB_FIELD=>"Create DT"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"KEYWORD_UpdateDt"
		,_LAB_FIELD=>"Update DT"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"KEYWORD_Username"
		,_LAB_FIELD=>"Username"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
	// ,Array(_SQL_FIELD=>"'_ICON_1_' AS icon1"
	,Array(_SQL_FIELD=>$ico_conditions
		,_LAB_FIELD=>"Action"
		,_TYP_FIELD=>100
		,_RID_FIELD=>'KEYWORD_Name' // field id to put in _SID_1_ to _SID_3_ format Service_ID,Provider_ID
		,_ICO_FIELD=>$pattern_icons['_ICON_1_']
		,_SORT_FIELD=>2) // default sort by: 1 = sort ase, 2 = desc)
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
} else {
############################################
?>
	<h1>Keywords Management</h1>	<hr>
	<h5><i class="fa fa-cog"></i>&nbsp;Add new keyword</h5>	<br>
	<form enctype="multipart/form-data" role="form" name="form_add_sender" action="<?=_MAIN_DF;?>keyword" method="POST">
		<div class="form-row align-items-center">
			<div class="form-group">
				<input type="text" id="keyword" name="keyword" value="" class="form-control" placeholder="Keyword text" size="20" required>
			</div>&nbsp;
			<div class="form-group">
				<input type="text" id="desc" name="desc" value="" class="form-control" placeholder="Description" size="30">
			</div>&nbsp;
			<div class="form-group">
			<input type="submit" id="submit_add_keyword" name="submit_add_keyword" class="btn btn-primary text-white" value="submit">
			</div>
		</div>
	</form>
	<hr>
	<div class="container-fluid mb-2">
      <?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar.php");?>
    </div>

<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_add_edit_keyword.php");
	// require_once("_t_keyword_list.php");
}
?>
	<hr>
