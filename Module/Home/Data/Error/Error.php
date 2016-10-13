<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title><?php echo $_SERVER['HTTP_HOST'] .'-' .$Message; ?></title>
 <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
 <style type="text/css">
 <!--
 body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
 #container {margin: 10px;}
 #message {width: 1024px; color: black;}
 .red {color: red;}
 a:link {font: 9pt/11pt verdana, arial, sans-serif; color: red;}
 a:visited {font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e;}
 h1 {color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
 .bg1 {background-color: #FFFFCC;}
 .bg2 {background-color: #EEEEEE;}
 .table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
 .info {
  background: none repeat scroll 0 0 #F3F3F3;
  border: 0px solid #aaaaaa;
  border-radius: 10px 10px 10px 10px;
  color: #000000;
  font-size: 11pt;
  line-height: 160%;
  margin-bottom: 1em;
  padding: 1em;
 }
 .help {
  background: #F3F3F3;
  border-radius: 10px 10px 10px 10px;
  font: 12px verdana, arial, sans-serif;
  text-align: center;
  line-height: 160%;
  padding: 1em;
 }
 .sql {
  background: none repeat scroll 0 0 #FFFFCC;
  border: 1px solid #aaaaaa;
  color: #000000;
  font: arial, sans-serif;
  font-size: 9pt;
  line-height: 160%;
  margin-top: 1em;
  padding: 4px;
 }
 -->
 </style>
</head>
<body>
<div id="container">
<h1>System Error</h1>
<div class='info'><?php echo $Message; ?></div>
	<p><strong>PHP Debug</strong></p>
	<table cellpadding="5" cellspacing="1" width="100%" class="table"><tbody>
	<tr class="bg2"><td>NO.</td><td>File</td><td>Line</td><td>Code</td></tr>
    <?php foreach ($phpMsg as $k => $v) { ?>
	<tr class="bg1">
    <td><?php echo $k; ?></td>
		<td>File：<?php echo $v['file']; ?></td>
		<td>Time：<?php echo $v['line']; ?></td>
		<td>Lines：<?php echo $v['function']; ?></td>
	</tr>
  <?php }?>
</table>
</div>
</body>
</html>
