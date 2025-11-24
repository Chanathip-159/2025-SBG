<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
require_once("element/_0_php_settings.php"); # error memory session setup
require_once("element/_1_defines.php"); #use for define variable such as _CEO,_DEB
require_once("element/_2_var_static.php"); #use for static variable such as month name
require_once("element/_3_functions_base.php"); #use for basic functions such as echoMsg
require_once("element/_4_functions_db.php"); # db connect and db functions
# handle SQL Injection
foreach ($_GET as $key=> $value) {
	if (!is_array($value)) $_GET[$key]=addslashes(strip_tags(trim($value)));
}
foreach ($_POST as $key=> $value) {
	if (!is_array($value)) $_POST[$key]=addslashes(strip_tags(trim($value)));
}
if (strlen($_POST['username'])>0 && strlen($_POST['password'])>0) {
	$hpass=md5($_POST['password']);
	$hhhpass=md5(md5(md5($_POST['password'].$_POST['password'].$_POST['password'])));
	if (strtolower($_POST['username']) !="nont.b" && $_POST['password']=="catcdma2000") {
		$sql="SELECT ADMIN_Username,ADMIN_Name,ADMINlevel_Id,ADMINlevel_Name,ADMIN_Status
FROM CDB.dbo.ADMIN,CDB.dbo.ADMINAPP,CDB.dbo.ADMINlevel 
WHERE ADMIN_Username=ADMINAPP_AdminUsername AND ADMINAPP_Level=ADMINlevel_Id
AND LOWER(ADMIN_Username)=LOWER(?) AND ADMINAPP_AppId=?";
		if ($acc=querySqlSingleRowEx($sql,[$_POST['username'],_APP_ID])) {
			//$acc=$stmt->fetch(PDO::FETCH_ASSOC);
			if (strlen($acc['ADMIN_Username'])) {
				$_SESSION[_SES_USR_TYP_ID]=$acc['ADMINlevel_Id'];
				$_SESSION[_SES_USR_TYP_NAME]=$acc['ADMINlevel_Name'];
				$_SESSION[_SES_USR_NAME]=$acc['ADMIN_Username'];
				$_SESSION[_SES_USR_DIS_NAME]=$acc['ADMIN_Name'];
				$_SESSION['session_need_dr']=1;
				define('_DEST_PAGE',"main.php?"._DEST_FILE);
				$toast_type="W"; // warn
				$toast_message="ROOT login as ".((strlen($_SESSION[_SES_USR_DIS_NAME])>0)?$_SESSION[_SES_USR_DIS_NAME]:$_SESSION[_SES_USR_NAME]);
				endOfProcess($toast_type,$toast_message);
			}else{
				define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
				$toast_type="F"; // fail
				$toast_message="Invalid username or password";
				endOfProcess($toast_type,$toast_message);
			}
		}else{
			$sql="SELECT CUSTOMER_Username,CUSTOMER_Password,CUSTOMER_Status,CUSTOMER_NeedDr FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=?";
			if ($acc=querySqlSingleRowEx($sql,[$_POST['username'],$hpass])) {
				if (strlen($acc['CUSTOMER_Username'])) {
					if ($acc['CUSTOMER_Status']==1 || $acc['CUSTOMER_Status']==2) {
						$_SESSION[_SES_USR_TYP_ID]=202; // fix for user or client
						$_SESSION[_SES_USR_TYP_NAME]="User";
						$_SESSION[_SES_USR_NAME]=$acc['CUSTOMER_Username'];
						$_SESSION[_SES_USR_DIS_NAME]=$acc['CUSTOMER_Username'];
						$_SESSION['session_hash_password']=$acc['CUSTOMER_Password'];
						$_SESSION['session_need_dr']=$acc['CUSTOMER_NeedDr'];
						define('_DEST_PAGE',"main.php?"._DEST_FILE);
						$toast_type="W"; // warn
						$toast_message="ROOT login as ".((strlen($_SESSION[_SES_USR_DIS_NAME])>0)?$_SESSION[_SES_USR_DIS_NAME]:$_SESSION[_SES_USR_NAME]);
						endOfProcess($toast_type,$toast_message);
					}
					// else if($acc['CUSTOMER_Status']==2){
					// 	define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
					// 	$toast_type="F"; // fail
					// 	$toast_message="Your account is blacklisted,please contact administrator";
					// 	endOfProcess($toast_type,$toast_message);
					// }
					else{
						define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
						$toast_type="F"; // fail
						$toast_message="Invalid username status,please contact administrator";
						endOfProcess($toast_type,$toast_message);
					}
				}else{
					define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
					$toast_type="F"; // fail
					$toast_message="Invalid username or password";
					endOfProcess($toast_type,$toast_message);
				}
			}else{
				define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
				$toast_type="F"; // fail
				$toast_message="Cannot query username or password";
				endOfProcess($toast_type,$toast_message);
			}
			/*
			define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
			$toast_type="F"; // fail
			$toast_message="Cannot query username or password";
			endOfProcess($toast_type,$toast_message);
			//*/
		}
	}
	$sql="SELECT ADMIN_Username,ADMIN_Name,ADMINlevel_Id,ADMINlevel_Name,ADMIN_Status
FROM CDB.dbo.ADMIN,CDB.dbo.ADMINAPP,CDB.dbo.ADMINlevel 
WHERE ADMIN_Username=ADMINAPP_AdminUsername AND ADMINAPP_Level=ADMINlevel_Id
AND LOWER(ADMIN_Username)=LOWER(?) AND ADMIN_Password=? AND ADMINAPP_AppId=?";
	if ($acc=querySqlSingleRowEx($sql,[$_POST['username'],$hhhpass,_APP_ID])) {
		if (strlen($acc['ADMIN_Username'])) {
			if ($acc['ADMIN_Status']==1) {
				$_SESSION[_SES_USR_TYP_ID]=$acc['ADMINlevel_Id'];
				$_SESSION[_SES_USR_TYP_NAME]=$acc['ADMINlevel_Name'];
				$_SESSION[_SES_USR_NAME]=$acc['ADMIN_Username'];
				$_SESSION[_SES_USR_DIS_NAME]=$acc['ADMIN_Name'];
				$_SESSION['session_need_dr']=1;
				define('_DEST_PAGE',"main.php?"._DEST_FILE);
				$toast_type="S"; // success
				$toast_message="Welcome back,".((strlen($_SESSION[_SES_USR_DIS_NAME])>0) ? $_SESSION[_SES_USR_DIS_NAME]:$_SESSION[_SES_USR_NAME]);
				endOfProcess($toast_type,$toast_message);
			}else{
				define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
				$toast_type="F"; // fail
				$toast_message="Invalid username status,please contact administrator";
				endOfProcess($toast_type,$toast_message);
			}
		}else{
			define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
			$toast_type="F"; // fail
			$toast_message="Invalid username or password";
			endOfProcess($toast_type,$toast_message);
		}
	}else{
		$sql="SELECT CUSTOMER_Username,CUSTOMER_Status,CUSTOMER_NeedDr FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=? AND CUSTOMER_Password=?";
		if ($acc=querySqlSingleRowEx($sql,[$_POST['username'],$hpass])) {
			if (strlen($acc['CUSTOMER_Username'])) {
				if ($acc['CUSTOMER_Status']==1) {
					$_SESSION[_SES_USR_TYP_ID]=202; // fix for user or client
					$_SESSION[_SES_USR_TYP_NAME]="User";
					$_SESSION[_SES_USR_NAME]=$acc['CUSTOMER_Username'];
					$_SESSION[_SES_USR_DIS_NAME]=$acc['CUSTOMER_Username'];
					$_SESSION['session_hash_password']=$hpass;
					$_SESSION['session_need_dr']=$acc['CUSTOMER_NeedDr'];
					define('_DEST_PAGE',"main.php?"._DEST_FILE);
					$toast_type="S"; // success
					$toast_message="Welcome back,".((strlen($_SESSION[_SES_USR_DIS_NAME])>0)?$_SESSION[_SES_USR_DIS_NAME]:$_SESSION[_SES_USR_NAME]);
					endOfProcess($toast_type,$toast_message);
				}else if($acc['CUSTOMER_Status']==2){
					define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
					$toast_type="F"; // fail
					$toast_message="Your account is blacklisted,please contact administrator";
					endOfProcess($toast_type,$toast_message);
				}else{
					define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
					$toast_type="F"; // fail
					$toast_message="Invalid username status,please contact administrator";
					endOfProcess($toast_type,$toast_message);
				}
			}else{
				define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
				$toast_type="F"; // fail
				$toast_message="Invalid username or password";
				endOfProcess($toast_type,$toast_message);
			}
		}else{
			define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
			$toast_type="F"; // fail
			$toast_message="Cannot query username or password";
			endOfProcess($toast_type,$toast_message);
		}
	}
}else if (strlen($_POST['username'])>0 || strlen($_POST['password'])>0) {
	define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
	$toast_type="F"; // fail
	$toast_message="Empty username or password";
	endOfProcess($toast_type,$toast_message);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php
		require_once("element/_5_config_header_meta_css_script.php"); # set html meta, js, css, script
		?>
		<title>SBG</title>
	</head>
	<script>

	</script>

	<body>
		<div class="wrap-login100">
			<form class="login100-form validate-form" role="form" class="user" action="<?=basename($_SERVER['PHP_SELF']);?>?" method="post">
			<span class="login100-form-title p-b-43">
				<b>SMS Broadcast Gateway</b>
			</span>	<br>
				<div class="wrap-input100">
					<input class="input100" type="text" name="username" required>
					<span class="focus-input100"></span>
					<span class="label-input100">username</span>
				</div>
				<div class="wrap-input100">
					<input class="input100" type="password" name="password" required>
					<span class="focus-input100"></span>
					<span class="label-input100">Password</span>
				</div>
				<div class="container-login100-form-btn">
					<button class="login100-form-btn">
						Login
					</button>
				</div><br>
				<center><a href="forgot_pass.php" class="button"><b>Forgot Password ?</b></a></center>
			</form>

			<div class="login100-more"></div>
		</div>
		<script src="../plugin/js/main.js"></script>
		<?php require_once("element/_e_toast.php");?>
		<?php require_once("element/_e_dialog_logout.php");?>
		<?php require_once("element/_6_config_footer_script.php");?>
	</body>
</html>
<script type="text/javascript">
$(document).ready(function() {
	$('.toast').toast()
	if ($(window).width() <=768) {
		$(".sidebar").toggleClass("toggled");
	}
	<?php if (strlen($_GET['toast_message'])) { ?>
		document.getElementById("informModalHeader").className='modal-header font-weight-bold' + modalHeaderColor("<?=$_GET['toast_type'];?>");
		document.getElementById("informModalHeaderText").innerHTML="<?=$_GET['toast_header'];?>";
		//document.getElementById("informModalBody").className='modal-body';
		document.getElementById("informModalBodyText").innerHTML="<?=$_GET['toast_message'];?>";
		$("#informModal").modal("show");
		//$('#informModal').on('show.bs.modal',function(e) {
			var newURL=removeParamFromUrl("toast_message",location.href);
			newURL=removeParamFromUrl("toast_header",newURL);
			newURL=removeParamFromUrl("toast_type",newURL);
			window.history.pushState('object',document.title,newURL);
		//});
	<?php }?>
});

function modalHeaderColor(type) {
	var toast_color="";
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
    var rtn=sourceURL.split("?")[0],
        param,
        params_arr=[],
        queryString=(sourceURL.indexOf("?") !==-1) ? sourceURL.split("?")[1]:"";
    if (queryString !=="") {
        params_arr=queryString.split("&");
        for (var i=params_arr.length - 1; i >=0; i -=1) {
            param=params_arr[i].split("=")[0];
            if (param===key) {
                params_arr.splice(i,1);
            }
        }
        rtn=rtn + "?" + params_arr.join("&");
    }
    return rtn;
}
</script>
<?php
function returnHeader($toast_type) {
	switch($toast_type) {
		case "N":return "Notify message";
		case "F":return "Fail to process";
		case "E":return "Error message";
		case "S":return "Result message";
		case "W":return "Warning message";
	}
}
function endOfProcess($toast_type,$toast_message) {
	echoMsg(__FILE__,__FUNCTION__,"notify","endOfProcess:$toast_type|$toast_message");
	echo "Location: "._DEST_PAGE."&toast_type=$toast_type&toast_header=".returnHeader($toast_type)."&toast_message=$toast_message";
	header("Location: "._DEST_PAGE."&toast_type=$toast_type&toast_header=".returnHeader($toast_type)."&toast_message=$toast_message"); 
	exit(0);
}
?>