<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @version $Id$
 * @package phpMyAdmin
 */
$strCalendar = isset($strCalendar) ? $strCalendar : null;
$month = isset($month) ? $month : null;
$day_of_week = isset($day_of_week) ? $day_of_week : null;
$strTime = isset($strTime) ? $strTime : null;
$strGo = isset($strGo) ? $strGo : null;
/**
 *
 */
require_once './libraries/common.inc.php';
require_once './libraries/header_http.inc.php';
$page_title = $strCalendar;
require './libraries/header_meta_style.inc.php';
$GLOBALS['js_include'][] = 'common.js';
$GLOBALS['js_include'][] = 'tbl_change.js';
require './libraries/header_scripts.inc.php';
?>
<script type="text/javascript">
//<![CDATA[
var month_names = new Array("<?php echo implode('","', $month); ?>");
var day_names = new Array("<?php echo implode('","', $day_of_week); ?>");
var submit_text = "<?php echo $strGo . ' (' . $strTime . ')'; ?>";
//]]>
</script>
<?php echo '</head>' ?>
<body onload="initCalendar();">
<div id="calendar_data"></div>
<div id="clock_data"></div>
</body>
<?php echo '</html>' ?>
