<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
// this page must be UTF-8 Encodeing
// need command "convert2ANSI" befor send SMS 
require_once("element/_0_php_settings.php"); # error memory session setup
require_once("element/_1_defines.php"); #use for define variable such as _CEO,_DEB
require_once("element/_2_var_static.php"); #use for static variable such as month name
require_once("element/_3_functions_base.php"); #use for basic functions such as echoMsg
require_once("element/_4_functions_db.php"); # db connect and db functions
//######################################################################################################
#require_once("/var/php.lib/function.post.receive.php");
#require_once("/var/php.lib/function.post.send.php");
//######################################################################################################

define('_MAIN_DF',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
define('_MENU_ACTIVE',"style='background-color:#7292f0;'");
# handle SQL Injection
foreach ($_GET as $key=>$value) {
	if (!is_array($value)) $_GET[$key]=addslashes(strip_tags(trim($value)));
}
foreach ($_POST as $key=>$value) {
	if (!is_array($value)) $_POST[$key]=addslashes(strip_tags(trim($value)));
}

if (!strlen($_SESSION[_SES_USR_TYP_NAME]) || !strlen($_SESSION[_SES_USR_NAME])) {
	header("Location: login.php"); 
	exit();
}
switch ($_SESSION[_SES_USR_TYP_ID]) {
	case _SES_USR_ROT:
	case _SES_USR_SA:
	case _SES_USR_ADM:
	case _SES_USR_SYS_ADMIN:
	case _SES_USR_MKT:
	case _SES_USR_OPE:
	case _SES_USR_CCC:
		// allow
	break;
	default:
		//header("Location: login.php?toast_type=F&toast_header=Fail to process&toast_message=Invalid username or password"); 
		//exit();
	break;
}

$_get_copy = $_GET;
unset($_get_copy[_DEST_FILE]);
unset($_get_copy['toast_message']);
unset($_get_copy['toast_type']);
unset($_get_copy['toast_header']);
unset($_get_copy['reset_search']);
unset($_get_copy['sort_field']);
unset($_get_copy['sort_desc_field']);
if (count($_get_copy)>0) $_get_url = "&".http_build_query($_get_copy);
if (strlen($_GET[_DEST_FILE]) > 0) {
	$dest_file = trim($_GET[_DEST_FILE]); 
}else{
	$dest_file = "first";
}
if (strlen($_POST[_SEL_PAGE]) > 0) $select_page = trim($_POST[_SEL_PAGE]); else $select_page = 1;
if ($_POST[_MAX_ROWS]>0 && $_POST[_MAX_ROWS]<=_MAX_NUM_ROWS) {
	$max_rows = trim($_POST[_MAX_ROWS]); 
	$_SESSION[_MAX_ROWS] = $max_rows;
}else{
	if ($_SESSION[_MAX_ROWS]>0 && $_SESSION[_MAX_ROWS]<=_MAX_NUM_ROWS) $max_rows = $_SESSION[_MAX_ROWS]; else $max_rows = 50;
}

if (strlen($_GET[_SORT_FIELD]) > 0) {
	$sort_field = trim($_GET[_SORT_FIELD]);
	$_SESSION[$dest_file._SORT_FIELD] = $sort_field;
}else{
	if (strlen($_SESSION[$dest_file._SORT_FIELD])>0) $sort_field = $_SESSION[$dest_file._SORT_FIELD]; 
}
if (strlen($_GET[_SORT_DESC_FIELD]) > 0) {
	$sort_desc_field = trim($_GET[_SORT_DESC_FIELD]);
	$_SESSION[$dest_file._SORT_DESC_FIELD] = $sort_desc_field;
}else{
	if (strlen($_SESSION[$dest_file._SORT_DESC_FIELD])>0) $sort_desc_field = $_SESSION[$dest_file._SORT_DESC_FIELD]; 
}
if ($_POST[_POST_BYPASS] > 0) {
	if (strlen($_SESSION[_POST_BYPASS])) {
		if ($_GET['reset_search'] != 1) {
			$_POST = unserialize($_SESSION[_POST_BYPASS]); # replace $_POST
		}
	}
}

# save old post
$_post_bypass = $_POST;
$_post_bypass[_POST_BYPASS] = null;
$_post_bypass['cur_password'] = null;
$_post_bypass['new_password'] = null;
$_post_bypass['newnew_password'] = null;

$_SESSION[_POST_BYPASS] = serialize($_post_bypass);

?>
<!DOCTYPE html>
<html>
	<head>
<?php
require_once("element/_5_config_header_meta_css_script.php"); # set html meta,js,css,script
?>
		<title>SBG</title>
	</head>
<script>
function gotoPage(file,max) {
	var f = document.getElementById("change_page_form");
	if (max > 0) f.max_rows.value = max;
	if (file.length > 0) f.action = "<?=_MAIN_DF;?>"+file; else f.action = "<?=_MAIN_DF;?>";
	f.submit();
}
function gotoPageWithLastParam(file,page,max) {
	var f = document.getElementById("change_page_form");
	if (page > 0) f.select_page.value = page; else if (page == -1) f.select_page.value = <?php echo $select_page;?>; else f.select_page.value = 1;
	if (max > 0) f.max_rows.value = max;
	if (file.length > 0) f.action = "<?=_MAIN_DF;?>"+file+"<?=$_get_url;?>"; else f.action = "<?=_MAIN_DF;?>"+"<?=$_get_url;?>";

	var save_draft_of_provinces_list = document.getElementById("save_draft_of_provinces_list");
	if (save_draft_of_provinces_list != 'undefined' && save_draft_of_provinces_list != null) {
		localStorage.setItem('save_draft_of_provinces_list',save_draft_of_provinces_list.innerHTML);
	 }

	f.submit();
}
</script>
<form action="/"  method="post" name="change_page_form" id="change_page_form" >
	<input type="hidden" name="select_page" value="<?php echo $select_page;?>">
	<input type="hidden" name="max_rows" value="-1">
	<input type="hidden" name="bypass" value="1">
</form>
	<body  id="page-top">
		<div id="wrapper"><!-- Page Wrapper -->
			<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar"><!-- Sidebar (Left) -->
				<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo _MAIN_DF;?>"><!-- Sidebar - Brand -->
					<div class="sidebar-brand-text mx-3">SBG</div>
				</a>
				<!-- admin only -->
				<?php
				if ($_SESSION[_SES_USR_TYP_ID]<10) {
				?>
				<hr class="sidebar-divider my-0"><!-- Divider -->
				<li class="nav-item"><!-- manage Customer -->
					<a class="nav-link" href="javascript:gotoPage('manage_cus&reset_search=1',-1);" <?php menuPermission($dest_file,"manage_cus");?>>
						<i class="fa fa-fw fa-globe-asia"></i><span>Customers Management</span>
					</a>
				</li>
				<?php
				}
				?>
				<hr class="sidebar-divider my-0"><!-- Divider - ->
				<li class="nav-item"><!-- by DB - ->
					<a class="nav-link" href="javascript:gotoPage('by_contacts&reset_search=1',-1);" <?php menuPermission($dest_file,"by_contacts");?>>
						<i class="fa fa-fw fa-globe-asia"></i><span>Send by Contacts</span>
					</a>
				</li>
				<li class="nav-item"><!-- manage DB - ->
					<a class="nav-link" href="javascript:gotoPage('contacts&reset_search=1',-1);" <?php menuPermission($dest_file,"contacts");?>>
						<i class="fa fa-fw fa-globe-asia"></i><span>Contacts</span>
					</a>
				</li>
				-->
				<li class="nav-item"><!-- File -->
					<a class="nav-link" href="javascript:gotoPage('by_file&reset_search=1',-1,);" <?php menuPermission($dest_file,"by_file");?>>
						<i class="fa fa-fw fa-book"></i><span>Send by File</span>
					</a>
				</li>
				<!--
				<li class="nav-item"><!-- Scheduled - ->
					<a class="nav-link" href="javascript:gotoPage('scheduled&reset_search=1',-1,);" <?php menuPermission($dest_file,"scheduled");?>>
						<i class="fa fa-fw fa-th-list"></i><span>Scheduled Queuing</span>
					</a>
				</li>
				
				<li class="nav-item"><!-- Scheduled Report - ->
					<a class="nav-link" href="javascript:gotoPage('scheduled_report&reset_search=1',-1,);" <?php menuPermission($dest_file,"scheduled_report");?>>
						<i class="fa fa-fw fa-file-alt"></i><span>Scheduled Report</span>
					</a>
				</li>
				-->
				<li class="nav-item"><!-- Report -->
					<a class="nav-link" href="javascript:gotoPage('report&reset_search=1',-1,);" <?php menuPermission($dest_file,"report");?>>
						<i class="fa fa-fw fa-file-alt"></i><span>SMS Report</span>
					</a>
				</li>
				<hr class="sidebar-divider d-none d-md-block"><!-- Last Divider -->
				<div class="text-center d-none d-md-inline"><!-- Sidebar Toggler (Sidebar) -->
					<button class="rounded-circle border-0" id="sidebarToggle"></button>
				</div>
			</ul><!-- End of Sidebar -->
<script type="text/javascript">(function () {if (localStorage.getItem('sidebar-toggle-collapsed') == '1') {$(".sidebar").toggleClass("toggled");}})();	</script>
			<div id="content-wrapper" class="d-flex flex-column"><!-- Content Wrapper --><!-- cover level 1 -->
				<div id="content"><!-- cover level 2 -->
					<nav class="navbar navbar-expand navbar-light bg-primary topbar mb-4 static-top shadow"><!-- Topbar -->
						<!--<div class="d-none d-md-inline-block mr-auto ml-md-3 my-2 my-md-0 w-100"></div>--><!-- fill space -->
						<div class="d-none d-md-inline-block mr-auto ml-md-3 my-2 my-md-0"></div>
						<div class="justify-content-right">
						<ul class="navbar-nav mr-auto ml-md-auto"><!-- Topbar Navbar -->
							<li class="nav-item dropdown"><!-- Nav Item - User Information -->
								<a class="nav-link dropdown-toggle text-right" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<?=$_SESSION[_SES_USR_DIS_NAME]." (".$_SESSION[_SES_USR_NAME].")";?><BR/>[<?=$_SESSION[_SES_USR_TYP_NAME];?>]
								</a>
								<div class="dropdown-menu dropdown-menu-right border-0 shadow animated--grow-in" aria-labelledby="userDropdown"><!-- Dropdown - User Information -->
									<div class="dropdown-header">Personal</div>
									<a class="dropdown-item" href="#" data-toggle="modal" data-target="#editPasswordModal">
										<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Change password
									</a>
									<a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
										<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout
									</a>
									<div class="dropdown-divider"></div>
									<div class="dropdown-header">Data per page</div>
									<a href="javascript:gotoPage('<?php echo $dest_file;?>',10);" class="dropdown-item">
										10 <?php if($max_rows==10) echo "<i class=\"fa fa-sm fa-fw fa-check-circle mr-2 text-gray-400\"></i>" ?>
									</a>
									<a href="javascript:gotoPage('<?php echo $dest_file;?>',50);" class="dropdown-item">
										50 <?php if($max_rows==50) echo "<i class=\"fa fa-sm fa-fw fa-check-circle mr-2 text-gray-400\"></i>" ?>
									</a>
									<a href="javascript:gotoPage('<?php echo $dest_file;?>',100);" class="dropdown-item">
										100 <?php if($max_rows==100) echo "<i class=\"fa fa-sm fa-fw fa-check-circle mr-2 text-gray-400\"></i>" ?>
									</a>
									<a href="javascript:gotoPage('<?php echo $dest_file;?>',200);" class="dropdown-item">
										200 <?php if($max_rows==200) echo "<i class=\"fa fa-sm fa-fw fa-check-circle mr-2 text-gray-400\"></i>" ?>
									</a>
								</div>
							</li>
						</ul><!-- End of Topbar Navbar -->
						</div>
						<button id="sidebarToggleTop" class="btn btn-link d-md-none bg-white"><!-- Sidebar Toggle (Topbar) -->
							<i class="fa fa-bars"></i>
						</button>
					</nav><!-- End of Topbar -->
					<div class="container-fluid"> <!-- main content -->
<?php
if (file_exists(getRealNamePage($_SESSION[_SES_USR_TYP_NAME],$dest_file))) {
	require_once(getRealNamePage($_SESSION[_SES_USR_TYP_NAME],$dest_file));
}else{
	require_once("_t_error.php");
}
?>
					</div><!-- end main content -->
				</div><!-- end cover level 2 -->
<?php require_once("element/_e_footer_copyright.php");?>
			</div><!-- End of Content Wrapper --><!-- end cover level 1 -->
		</div><!-- End of Page Wrapper -->
		<!-- Scroll to Top Button-->
		<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
<?php #require_once("element/_e_dialog_edit_profile.php");?>
<?php require_once("element/_e_dialog_edit_password.php");?>
<?php require_once("element/_e_dialog_logout.php");?>
<?php require_once("element/_e_toast.php");?>
<?php require_once("element/_6_config_footer_script.php");?>
	</body>
</html>

<script type="text/javascript">
$(document).ready(function() {
	$('.toast').toast();
	if ($(window).width() <= 768) {
		if (localStorage.getItem('sidebar-toggle-collapsed') != '1') {
			$(".sidebar").toggleClass("toggled");
			localStorage.setItem('sidebar-toggle-collapsed','1');
		}
	}
	<?php if (strlen($_GET['toast_message'])) { ?>
		document.getElementById("informModalHeader").className = 'modal-header font-weight-bold' + modalHeaderColor("<?=$_GET['toast_type'];?>");
		document.getElementById("informModalHeaderText").innerHTML = "<?=$_GET['toast_header'];?>";
		//document.getElementById("informModalBody").className = 'modal-body';
		document.getElementById("informModalBodyText").innerHTML = "<?=$_GET['toast_message'];?>";
		$("#informModal").modal("show");
		//$('#informModal').on('show.bs.modal',function(e) {
			var newURL = removeParamFromUrl("toast_message",location.href);
			newURL = removeParamFromUrl("toast_header",newURL);
			newURL = removeParamFromUrl("toast_type",newURL);
			window.history.pushState('object',document.title,newURL);
		//});
	<?php }?>
});

(function ($) {
	$('#sidebarToggle').click(function(e) {
		e.preventDefault();
		if (localStorage.getItem('sidebar-toggle-collapsed')=='1') {
			localStorage.setItem('sidebar-toggle-collapsed','');
		 } else {
			localStorage.setItem('sidebar-toggle-collapsed','1');
		 }
	 });
})(jQuery);
function modalHeaderColor(type) {
	var toast_color = "";
	switch (type) {
		case 1: case "N": case "green-blue": toast_color=" text-white bg-info";break; // notify
		case 2: case "F": case "gray": toast_color=" text-white bg-secondary";break; // fail or not allow
		case 3: case "S": case "green": toast_color=" text-white bg-success";break; // success
		case 4: case "E": case "red": toast_color=" text-white bg-danger";break; // error
		case 5: case "W": case "yello": toast_color=" text-dark bg-warning";break; // warning
		case 6: case "blue": $toast_color=" text-white bg-primary";break; // notify
	}
	return toast_color;
}

function removeParamFromUrl(key,sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i,1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}
</script>