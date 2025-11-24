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
$dest_file="sender_list";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE]=$dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");	


$pattern_icons['_ICON_1_'] = "<button title=\"Edit Sender\" class='btn btn-info' id=\"_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class='fa fa-edit'></i>&nbsp;Edit</button><br>";
$pattern_icons['_ICON_1_'] .= "<button title=\"Delete Sender\" class='btn btn-danger' id=\"del-_SID_1_\" data-toggle=\"modal\" data-target=\"#EditChangeDelModal\" ><i class=\"fa fa-fw fa-trash\"></i>&nbsp;Del</button><br>";

$sql_from="SBG.dbo.SENDERS";
$sql_where="SENDER_Username = '".$_SESSION[_SES_USR_NAME]."' ";
$orderby="SENDER_Sender";
$table_mode=2;// 0=white and gray; 1=color; 2=mix
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
		,_TYP_FIELD=>1
    ,_SEA_FIELD=>true)
	,Array(_SQL_FIELD=>"CATEGORY_Id"
		,_LAB_FIELD=>"Category"
		,_TYP_FIELD=>1
		,_SEA_FIELD=>true)
    ,Array(_SQL_FIELD=>"SENDER_CreateDt"
		,_LAB_FIELD=>"Create DateTime"
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
    <h1>Sender list</h1>  <br>
    [Status] 1 = Active, 2 = Blacklist, 3 = Suspend <br>
    [Category] 1 = Public Relations, 2 = Normal, 3 = Important, 4 = Very Important
    <br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
}

?>
