<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ���ͺ������
# Edit zone
############################################
$dest_file = "by_file";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE] = $dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");																									// check permission from session name and session destination name

$sql_user = "SELECT CUSTOMER_Status, CUSTOMER_Parent_Username FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=?";
$sql_parent = "SELECT CUSTOMER_Status, CUSTOMER_Parent_Username FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=?";

$acc_user = querySqlSingleRowEx($sql_user,[$_SESSION[_SES_USR_NAME]]);
$acc_parent = querySqlSingleRowEx($sql_parent,[$acc_user['CUSTOMER_Parent_Username']]);

// echo $_SESSION[_SES_USR_NAME];

if (!$dbh) {
	echo '<h1>Error</h1>';
	echo '<hr>';
	echo '<p>This is an internal error.</p>';
} else {
	echo '<h1>Send by File</h1>';
	echo '<hr>';

	if ($acc_user['CUSTOMER_Status'] == 2 || $acc_parent['CUSTOMER_Status'] == 2) {
		echo 'Your account is blacklisted, please contact administrator.';
	} else {
		require_once("element/_p_drop_file.php");
	}
}

?>