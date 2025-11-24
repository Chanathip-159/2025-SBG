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
$_SESSION[_SES_CUR_PAGE]=$dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");

if (strlen($_POST[_TXT_SEARCH])) $text_search = $_POST[_TXT_SEARCH]; else $text_search = null;

$sql_from="SBG.dbo.KEYWORD";
$sql_where="";
$orderby="KEYWORD_Name";
$table_mode=2;// 0=white and gray; 1=color; 2=mix
$table_font_size="small";

$save_enable = true;

$search_bar = "element/_e_search_bar_keyword.php";

// create table configuration array
// _TYP_FIELD:: -1=icon and not query; 0=not show; 1=string; 2=date time; 3=number; 4=money; 100=pattern icon or no search; 200=sum/count (num); 201=sum/count (money)
$fields_info=Array(
  Array(_SQL_FIELD=>"KEYWORD_Name" // can use [ISAG].[dbo].[Users].[User_CreateDate] // can use [ISAG].[dbo].[Users].[User_Name] AS Name
   ,_LAB_FIELD=>"Name"
   ,_TYP_FIELD=>1
   ,_SORT_FIELD=>1
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
    <h1>Keyword list</h1> <hr>
    <div class="container-fluid mb-2">
      <?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar_keyword.php");?>
    </div>
    <br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
}
?>
