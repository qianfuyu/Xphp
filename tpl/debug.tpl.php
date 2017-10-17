<html>
<head>
	<title>Xphp异常错误处理模板</title>
	<style type="text/css">
		* {
			margin:0px;padding:0px;
		}
		body{
			margin:20px;
		}
		#debug{
			width:880px;border:solid 1px #dcdcdc;margin-top:20px;padding:10px;
		}
		fieldset{
			padding:10px;
			font-size:16px;
		}
		legend{
			padding:5px;
		}
		p{
			backgroud-color:#666;
			font-size:12px;
			color:#fff;
			margin-top:10px;
			padding:3px;
		}
	</style>
</head>
<body>	
<div id='debug'>
	<h2>DEBUG</h2>
	<fieldset>
		<legend>被载入文件</legend>
		<?php echo $e['message']; ?>
	</fieldset>
	<?php if(isset($e['info'])) {?>
	<fieldset>
		<legend>TRACE</legend>
		<?php echo $e['info']; ?>
	</fieldset>
	<?php } ?>
</div>
</body>
</html>