<?php
$php_root_file=$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_FILENAME'];
# ทดสอบภาษาไทย
set_time_limit(120);													// 1 minute timeout
$dest_file = $_GET['bf'];											// destination name of file
if (strlen($_GET['bw'])>0) $back_url = http_build_query(serialize($_GET['bw'])); else $back_url = null;				// destination name of file
?>
			<h1>Generate CSV - <?=$dest_file?></h1>
			<hr>
			Processing...
<?php
define('_DEST_PAGE', _MAIN_DF."$dest_file&$back_url");
define('_CLS_CACHE', 2); // 2 day
$total_data = $_SESSION[_SES_SQL_CUR_QRY_ROW];
$sql = "SELECT ".$_SESSION[_SES_SQL_CUR_QRY_SEL]." ORDER BY ".$_SESSION[_SES_SQL_CUR_QRY_ODR];
// clear session
$_SESSION[_SES_SQL_CUR_QRY_ROW] = 0;
$_SESSION[_SES_SQL_CUR_QRY_SEL] = null;
$_SESSION[_SES_SQL_CUR_QRY_ODR] = null;

if ($total_data == 0) {
	$toast_type = "N"; // notify
	$toast_message = "Empty data";
	endOfProcess($toast_type, $toast_message);
}
if ($total_data > 500) $log_mode = true;
if ($total_data > 50000) {
	echo "<h2>THIS IS A VERY LARGE FILE PLEASE WAIT...</h2>";
	flush(); ob_flush(); ob_end_clean();
}
$csv_name = date("ymdH-").strtolower(randomChar(6))."-$dest_file.csv";
$csv_download_name = date("Ymd-His")."-$dest_file.csv";
$fp = fopen("export/$csv_name", 'w');
fputcsv($fp, Array("Header: page: $dest_file; total $total_data rows"));
if ($log_mode) {
	$current_data = 0;
	$current_time = time();
	echo "Clear cache<br />\n";
	flush(); ob_flush(); ob_end_clean();
}
$dir = "export/";
foreach(scandir("export/") AS $file) {
	if ($file != ".." && $file != ".") {
		$file_name = explode("-", $file);
		if (((int) date('ymdH', strtotime("-2day")))>((int) $file_name[0])) {
			unlink($dir.$file);
		}
	}
}
$old_percent = "";
$current_line = 0;
$echo_period = 500;
if ($stmt = executeSqlWOMsg($dbh, $sql)) {
	$field_name_write = true;
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		unset($row['icon1']);unset($row['icon2']);unset($row['icon3']);unset($row['icon4']);unset($row['icon5']);
		
		if ($field_name_write) {
			$keys = array_keys($row);
			fputcsv($fp, array_map('trim',$keys));
			$field_name_write = false;
		}
		
		fputcsv($fp, array_map('trim',$row));
		if ($log_mode) {
			$current_percent = floor((100*$current_data)/$total_data);
			$current_percent_line = floor($current_percent/10);
			if (!$echo_period) {
				if ($current_percent_line > $current_line) echo "<br />\n";
				if ($current_percent != $old_percent) {
					echo $current_percent . "% ";
					$old_percent=$current_percent;
				}else {
					echo ".";
				}
				flush(); ob_flush(); ob_end_clean();
				$current_time = time();
				$current_line = $current_percent_line;
				$echo_period = 500;
			}
			$current_data++;
			$echo_period--;
		}
	}
	echo "100%<br />\n<hr>";
	flush(); ob_flush(); ob_end_clean();
}
fputcsv($fp, Array("Footer: total $total_data rows"));
fclose($fp);

# send info to next page
$toast_type = "S"; // success
$toast_message = "Generate CSV successfully, please wait until downloading is complete";
$redirect = _DEST_PAGE."&toast_type=$toast_type&toast_header=".returnHeader($toast_type)."&toast_message=$toast_message";
?>
			<div class="row">
				<div class="col-md-12 text-center">
					<h1><a href="export/<?=$csv_name?>" download="<?=$csv_download_name?>" onclick="downloadAndRedirect()"><button class="btn btn-lg btn-success" type="button" title="SAVE">&nbsp;<i class="fa fa-2x fa-fw fa-save text-white fa-fw"></i></a></h1>
				</div>
			</div>
<script type="text/javascript">
function downloadAndRedirect() {
	setTimeout(function () { window.location.replace("<?=$redirect?>");}, 2000);
}
</script>
<?php
function executeSqlWOMsg($dbh, $sql, $param=null) {
	echoMsg(__FILE__, __FUNCTION__, "database", "sql :: ".$sql, 0, false);
	$stmt = false;
	try {
		$stmt = @$dbh->prepare($sql);
		if ($param == null) $stmt->execute(); else $stmt->execute($param);
	} catch (PDOException $e) {
		echoMsg(__FILE__, __FUNCTION__, "database", "executeSql failed: ".$e->getMessage()."\r\n$sql\r\n".print_r($param, true), 255, false); // last true mean alway echo to admin
	}
	return $stmt;
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