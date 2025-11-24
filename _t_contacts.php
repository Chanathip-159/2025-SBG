<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
# Edit zone
############################################
$dest_file = "contacts";																																			// destination name of file
$_SESSION[_SES_CUR_PAGE] = $dest_file;																												// mark destination name to session
require_once("element/_e_check_permission.php");																									// check permission from session name and session destination name

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
			<h1>Contacts</h1>
			<hr>
<?php
	require_once("element/_p_contacts_form.php");
############################################
}
?>