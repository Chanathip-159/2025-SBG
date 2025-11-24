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
$dest_file="blacklist";																																			// destination name of file
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
					";
	
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


if($_POST['submit_add_blacklist'])
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

	$str_sql_err1 = "";
	$_SESSION['str_err1_msg'] = "";

	$str_sql_err2 = "";
	$_SESSION['str_err2_msg'] = "";

	$i = 0;
	$j = count($split);

	foreach($split as $split_data) {
		$pattern_pipe = "/[|]/";
		$split_pipe = preg_split($pattern_pipe, $split_data);

		$array_sql[$i] = $split_pipe;
		$i++;
	}

	for($i=0; $i<$j; $i++)
	{
		$sql_chk_sender = "SELECT count(SENDER_Sender) as count_sender 
											FROM SBG.dbo.SENDERS 
											WHERE SENDER_Sender = '".$array_sql[$i][0]."'
											";

		$sql_chk_status = "SELECT SENDER_Sender as sender, SENDER_Status as status
											FROM SBG.dbo.SENDERS 
											WHERE SENDER_Sender = '".$array_sql[$i][0]."'
											AND SENDER_Status = '2'
											";

		$stmt_chk_sender = querySqlEx($sql_chk_sender);
		$stmt_chk_status = querySqlEx($sql_chk_status);

		while ($row_chk_sender = $stmt_chk_sender->fetch(PDO::FETCH_ASSOC))
		{
			if($row_chk_sender['count_sender'] > 0)
			{
				if( $stmt_chk_status->fetch(PDO::FETCH_ASSOC) )
				{
					$msg_err2 = 'ไม่สามารถปรับ Status ได้ เนื่องจากมีข้อมูล sender, status นี้อยู่แล้ว ';
					$_SESSION['str_err2_msg'] .= $array_sql[$i][0].'<br>';

				} else {
					$msg_result = 'เพิ่มได้';
					$str_sql .= "'".$array_sql[$i][0]."',";
					$_SESSION['str_msg'] .= $array_sql[$i][0].'<br>';
				}
			} else {
				$msg_err1 = 'ไม่สามารถปรับ Status ได้ เนื่องจากไม่มี sender อยู่ในระบบ ';
				$_SESSION['str_err1_msg'] .= $array_sql[$i][0].'<br>';
			}
		}
	}

	if (isset($str_sql))
	{
		$sql = "UPDATE SBG.dbo.SENDERS SET SENDER_Status = 2 WHERE SENDER_Sender IN (".substr($str_sql, 0, -1).')';
		// $stmt=insertSqlEx($sql,$insert_data);
		// echo $sql;
	}
	if ($sql) # change $sql to $stmt
	{
		$_GET['toast_type']="S";
		$_GET['toast_header']="Result message";
		$_GET['toast_message']= "Adjust sender successfully.";
		
		// $_GET['toast_message']= "Upload sender successfully. <br> => ".$str_msg." <br> Can not upload sender because user account does not exist. <br> => ".$str_err2_msg." <br> Can not upload sender because already exist. <br> => ".$str_err1_msg." ";
	?>
	<script>window.open("./element/_p_senderBlacklist_detail.php", '_blank');	</script>
	<?php
	} else {
		$_GET['toast_type']="F";
		$_GET['toast_header']="Fail to process";
		$_GET['toast_message']="Fail to adjust sender";
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

// $search_bar = "element/_e_search_bar_sender.php"; // "_e_search_bar.php"

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

$sql_from="SBG.dbo.SENDERS";
$sql_where="SENDER_Status = '2' $searchByForm";
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
				Blacklist Management
			</h1>
			<hr>

			<div class="container-fluid mb-2">
				<?php if (strlen($search_bar)>0) require_once($search_bar); else require_once("element/_e_search_bar_dnm.php");?>
			</div>
			<br>
<?php
############################################
	require_once("element/_e_list_items_z2.php");
	require_once("element/_p_dialog_add_edit_blacklist.php");
}

?>

<script type='text/javascript'>	
	var i = 1;
	const x = "<?php echo $counterState; ?>";
	var b = parseInt(x);

	$(document).on('click', '#add_field', function() {
	
		if( i == 2 ) {
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

		select1.onchange = function() {
			preventDupes.call(this, select2, this.selectedIndex);
		};

		select2.onchange = function() {
			preventDupes.call(this, select1, this.selectedIndex);
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