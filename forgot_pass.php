<?php
	$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
	require_once("element/_0_php_settings.php"); # error memory session setup
	require_once("element/_1_defines.php"); #use for define variable such as _CEO, _DEB
	require_once("element/_2_var_static.php"); #use for static variable such as month name
	require_once("element/_3_functions_base.php"); #use for basic functions such as echoMsg
	require_once("element/_4_functions_db.php"); # db connect and db functions

	// require_once(__DIR__.'/../php.lib/api.sms.send.direct.php');
	// require_once(__DIR__.'/api/bulk.api.engine.php');

	foreach ($_GET as $key=> $value) {
		if (!is_array($value)) $_GET[$key]=addslashes(strip_tags(trim($value)));
	}
	foreach ($_POST as $key=> $value) {
		if (!is_array($value)) $_POST[$key]=addslashes(strip_tags(trim($value)));
	}

	$n=6;
	function getName($n) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
		
			for ($i = 0; $i < $n; $i++) {
					$index = rand(0, strlen($characters) - 1);
					$randomString .= $characters[$index];
			}
		
			return $randomString;
	}
	$new_pass = getName($n);	

	if(strlen($_POST['username']) > 0) {
		// echo 'user = '.$_POST['username'];
		$sql="SELECT CUSTOMER_Username,CUSTOMER_Telephone,CUSTOMER_Status FROM SBG.dbo.CUSTOMERS WHERE CUSTOMER_Username=?";

		if ($acc=querySqlSingleRowEx($sql,[$_POST['username']])) {
			$username = $acc['CUSTOMER_Username'];
			$tel = $acc['CUSTOMER_Telephone'];
			$status = $acc['CUSTOMER_Status'];

			if ($status == 1) {
				$sql_update = "UPDATE SBG.dbo.CUSTOMERS SET CUSTOMER_UpdateDt=GETDATE(),CUSTOMER_Password=? WHERE LOWER(CUSTOMER_Username)=LOWER(?)";

				if (!querySqlEx($sql_update,[md5($new_pass),$username])) {
				// return error
					// echo 'fail update';
					$_GET['toast_type']="F";
					$_GET['toast_header']="Fail to process";
					$_GET['toast_message']="Fail to update or insert user account";
				} else {
					// echo 'success update';
					try {
						error_reporting(E_ERROR);
						
						$msisdn = substr($tel, 1);        
						$msisdn_sms = '66'. $msisdn;
		
// $msg_sms = 'เรียนผู้ใข้งานระบบ SBG : '.$username.'

// รหัสผ่านใหม่สำหรับเข้าใช้งานระบบ SBG ของคุณถูกส่งไปยังเบอร์ติดต่อ '.$tel.' เรียบร้อยแล้ว กรุณาตรวจสอบข้อความ

// Username: '.$username.'
// Password: '.$new_pass.' ';

$msg_sms = 'เรียน คุณ '.$username.'

รายละเอียด Account สำหรับลงชื่อเข้าใช้งานระบบ SBG ของท่านคือ

Username: '.$username.'
New password: '.$new_pass.' ';

		
						$send_data = Array();
						$send_data['sms_client_ip'] = getClientIp();
						$send_data['sms_type']="submit"; 
						$send_data['sms_service_type'] = "SBG";
						$send_data['sms_charge_account'] = "admin";
						$send_data['sms_sender'] = "SBG";
						$send_data['sms_receiver'] = $msisdn_sms; // destination ex: 66864601421
						$send_data['sms_langauge'] = "T";
						$send_data['sms_message'] = $msg_sms;
		
						// $send_test_result = sendSmsToDb($send_data);
						
						$_GET['toast_type']="S";
						$_GET['toast_header']="Result message";
						$_GET['toast_message']="Update or insert user account successfully";
						// $toast_message="Request new password successfully".$msisdn_sms.','.$msg_sms.','.$username.','.$new_pass;
					}
					catch (exception $e) {
						header("Status: 500");
						header("Content-Type: text/plain" );
						echo 'Message: ' .$e->getMessage();
					}

					define('_DEST_PAGE',"login.php?"._DEST_FILE);
					$toast_type="S"; // success
					// $toast_message="success";
					// $toast_message="Request new password successfully. ".$tel.', '.$msg_sms.', '.$username.', '.$new_pass;
					// $toast_message="Request new password successfully. ".'[Username='.$username.';Tel='.$msisdn_sms.']';
					$toast_message="Request new password successful and send to $msisdn_sms ".'[Username = '.$username.']';
					endOfProcess($toast_type,$toast_message);
				}
			} else if ($status == 2) {
				define('_DEST_PAGE',"login.php?"._DEST_FILE);
				// define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
				$toast_type="F"; // fail
				$toast_message="Your account is blacklisted, please contact administrator";
				endOfProcess($toast_type,$toast_message);
			}
		} else {
			define('_DEST_PAGE',"login.php?"._DEST_FILE);
			// define('_DEST_PAGE',basename($_SERVER['PHP_SELF'])."?"._DEST_FILE."=");
			$toast_type="F"; // fail
			$toast_message="This account was not found.";
			endOfProcess($toast_type,$toast_message);
		}
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
	<body>
		<div class="wrap-login100">
      <form class="login100-form validate-form" role="form" name="forgot_pass" class="user" action="<?=basename($_SERVER['PHP_SELF']);?>?" method="post">
				<span class="login100-form-title p-b-43">
					<b>FORGOT PASSWORD</b>
				</span>	<br>
				<div class="wrap-input100">
					<input class="input100" type="text" name="username" id="username" required>
					<span class="focus-input100"></span>
					<span class="label-input100">Username</span>
				</div><br>
				<div class="container-login100-form-btn">
					<button class="login100-form-btn">
					Request NEW PASSWORD
					</button>
				</div>
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
	if ($(window).width() <= 768) {
		$(".sidebar").toggleClass("toggled");
	}
	<?php if (strlen($_GET['toast_message'])) { ?>
		document.getElementById("informModalHeader").className = 'modal-header font-weight-bold' + modalHeaderColor("<?=$_GET['toast_type'];?>");
		document.getElementById("informModalHeaderText").innerHTML = "<?=$_GET['toast_header'];?>";
		//document.getElementById("informModalBody").className = 'modal-body';
		document.getElementById("informModalBodyText").innerHTML = "<?=$_GET['toast_message'];?>";
		$('#informModal').modal("show");
		//$('#informModal').on('show.bs.modal', function(e) {
			var newURL = removeParamFromUrl("toast_message", location.href);
			newURL = removeParamFromUrl("toast_header", newURL);
			newURL = removeParamFromUrl("toast_type", newURL);
			window.history.pushState('object', document.title, newURL);
		//});
	<?php }?>
});

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
function removeParamFromUrl(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
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
function endOfProcess($toast_type, $toast_message) {
	echoMsg(__FILE__, __FUNCTION__, "notify", "endOfProcess:$toast_type|$toast_message");
	header("Location: "._DEST_PAGE."&toast_type=$toast_type&toast_header=".returnHeader($toast_type)."&toast_message=$toast_message"); 
	exit();
}
?>