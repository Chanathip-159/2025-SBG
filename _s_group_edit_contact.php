<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
set_time_limit(60);													// 1 minute timeout
$dest_file = $_POST[_DEST_FILE];								// destination name of file
$_SESSION[_SES_CUR_PAGE] = $dest_file;					// mark destination name to session
?>
Please wait...
<?php
define('_DEST_PAGE', _MAIN_DF.$dest_file);

# _s_account_add_account.php
$verify = validatePhoneNumber($_POST['msisdn']);
$msisdn = convert2ThaiPhoneNumber($_POST['msisdn']);
if (!$msisdn || !$verify) {
	$toast_type = "F"; // fail
	$toast_message = "[".$_POST['msisdn']."] is incorrect format";
	endOfProcess($toast_type, $toast_message);
}

if (strlen($_POST['ACCPAIR_Parent'])>0) {
	$verify = validatePhoneNumber($_POST['ACCPAIR_Parent']);
	$parent = convert2ThaiPhoneNumber($_POST['ACCPAIR_Parent']);
	if (!$parent || !$verify) {
		$toast_type = "F"; // fail
		$toast_message = "[".$_POST['ACCPAIR_Parent']."] is incorrect format";
		endOfProcess($toast_type, $toast_message);
	}
}

# define variable
$levels = Array();
$statuss = Array();
$sql = "SELECT [ACClevel_Id],[ACClevel_Name] FROM [ITOP].[dbo].[ACClevel]";
if ($stmt = querySqlEx($sql)) {
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $levels [$row['ACClevel_Id']] = true;
}else{
	$toast_type = "E"; // error
	$toast_message = "Internal error, cannont connect database";
	endOfProcess($toast_type, $toast_message);
}
$sql = "SELECT [ACCstatus_Id],[ACCstatus_Name] FROM [ITOP].[dbo].[ACCstatus]";
if ($stmt = querySqlEx($sql)) {
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $statuss[$row['ACCstatus_Id']] = true;
}
$sex = Array(false,true,true,true); // 0=false, 1=true, 2= true, 3=true

# verify text
if (!$levels[$_POST['ACC_Level']]) {
	$toast_type = "F"; // fail
	$toast_message = $_POST['ACC_Level']." is not a type format";
	endOfProcess($toast_type, $toast_message);
}
if (!$statuss[$_POST['ACC_Status']]) {
	$toast_type = "F"; // fail
	$toast_message = $_POST['ACC_Level']." is not a status format";
	endOfProcess($toast_type, $toast_message);
}
if (!$sex[(int) $_POST['ACC_Sex']]) {
	$_POST['ACC_Sex'] = 3; // default
}

$sql = "SELECT [ACC_Msisdn] FROM [ITOP].[dbo].[ACC] WHERE [ACC_Msisdn] = ?";
if ($update_msisdn = querySqlSingleFieldEx($sql, [$msisdn])) {
	if (strlen($update_msisdn)>0) {
		if ($_POST['ACC_Status'] == 1) $approve_sql = ", [ACC_ApproveBy] = '".$_SESSION[_SES_USR_NAME]."', [ACC_ApproveDt] = GETDATE()";
		$sql = "UPDATE [ITOP].[dbo].[ACC] SET [ACC_Level] = ?, [ACC_Status] = ?, [ACC_Sex] = ?
, [ACC_Name] = ?, [ACC_Surname] = ?, [ACC_ContactPhone] = ?, [ACC_Email] = ?, [ACC_Description] = ?
, [ACC_UpdateBy] = '".$_SESSION[_SES_USR_NAME]."', [ACC_UpdateDt] = GETDATE()$approve_sql
WHERE [ACC_Msisdn] = '$msisdn'";
		$param = Array($_POST['ACC_Level'], $_POST['ACC_Status'], $_POST['ACC_Sex'], $_POST['ACC_Name'], $_POST['ACC_Surname'], $_POST['ACC_ContactPhone'], $_POST['ACC_Email'],$_POST['ACC_Description']);
		if (!querySqlEx($sql,$param)) {
			$toast_type = "E"; // error
			$toast_message = "Cannot update";
			endOfProcess($toast_type, $toast_message);
		}
		if (strlen($parent)>0) {
			$sql = "SELECT [ACCPAIR_Parent] FROM [ITOP].[dbo].[ACCPAIR] WHERE [ACCPAIR_Child] = ?";
			$old_parent = querySqlSingleFieldEx($sql, [$msisdn]);
			if (strlen($old_parent)>0) {
				#update
				$sql = "UPDATE [ITOP].[dbo].[ACCPAIR] SET [ACCPAIR_Parent] = '$parent' WHERE [ACCPAIR_Child] = '$msisdn'";
				if ($stmt = querySqlEx($sql)) {
					$toast_type = "S"; // success
					$toast_message = "Update account and pair successfully";
					endOfProcess($toast_type, $toast_message);
				}else{
					$toast_type = "E"; // error
					$toast_message = "Internal error, cannont update pair";
					endOfProcess($toast_type, $toast_message);
				}
			}else{
				# insert
				$sql = "INSERT INTO [ITOP].[dbo].[ACCPAIR] ([ACCPAIR_Parent], [ACCPAIR_Child]) VALUES ('$parent','$msisdn')";
				if (querySqlEx($sql)) {
					$toast_type = "S"; // success
					$toast_message = "Update and Insert pair successfully".$msisdns_not_exist_message;
					endOfProcess($toast_type, $toast_message);
				}else{
					$toast_type = "W"; // warn
					$toast_message = "Update account successfully, insert pair fail";
					endOfProcess($toast_type, $toast_message);
				}
			}
		}else{
			# no parent, if have in pair, delete it
			$sql = "SELECT [ACCPAIR_Parent] FROM [ITOP].[dbo].[ACCPAIR] WHERE [ACCPAIR_Child] = ?";
			$old_parent = querySqlSingleFieldEx($sql, [$msisdn]);
			if (strlen($old_parent)>0) {
				$sql = "DELETE FROM [ITOP].[dbo].[ACCPAIR] WHERE [ACCPAIR_Child] = '$msisdn'";
				if (querySqlEx($sql)) {
					$toast_type = "S"; // success
					$toast_message = "Update account and un-pair successfully";
					endOfProcess($toast_type, $toast_message);
				}else{
					$toast_type = "E"; // error
					$toast_message = "Internal error, cannot un-pair";
					endOfProcess($toast_type, $toast_message);
				}
			}else{
				$toast_type = "S"; // success
				$toast_message = "Update successfully";
				endOfProcess($toast_type, $toast_message);
			}
		}
	}else{
		$toast_type = "F"; // fail
		$toast_message = "$msisdn not found";
		endOfProcess($toast_type, $toast_message);
	}
}else{
	$toast_type = "E"; // error
	$toast_message = "Internal error, cannont connect database";
	endOfProcess($toast_type, $toast_message);
}

function returnHeader($toast_type) {
	switch($toast_type) {
		case "N":return "Notify message";
		case "F":return "Fail to process";
		case "E":return "Error message";
		case "S":return "Result message";
		case "W":return "Warning message";
	}
}
function endOfProcess($toast_type, $toast_message) {
	echoMsg(__FILE__, __FUNCTION__, "notify", "endOfProcess:$toast_type|$toast_message");
	header("Location: "._DEST_PAGE."&toast_type=$toast_type&toast_header=".returnHeader($toast_type)."&toast_message=$toast_message"); 
	exit();
}
?>