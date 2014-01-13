<? include "include.php" ?>
<!doctype html>
<html>
<head>

	<meta charset="utf-8">

	<link href="scss/precedents.scss" rel="stylesheet">
	<link href="scss/layout.scss" rel="stylesheet">
	<link href="scss/custom.scss" rel="stylesheet">
	<link href='http://fonts.googleapis.com/css?family=Lato:100,300,900' rel='stylesheet' type='text/css'>

	<link href="library/codemirror/lib/codemirror.css" rel="stylesheet">
	<!-- <link href="library/codemirror/theme/mbo.css" rel="stylesheet"> -->
	<link href="library/codemirror/theme/neat.css" rel="stylesheet">

</head>
<body>
	
	<iframe src="/"></iframe>

	<div id="overlay"></div>
	<div class="form">
		<form action="#" method="post">
			Loading...
		</form>
	</div>	

	<!-- Scripts -->
	<script>
		var admin_folder = "<?= $admin_folder ?>";
		var content_folder = "<?= $typset_settings->content_folder ?>";
	</script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script src="scripts/codemirror-compressed.js"></script>
	<script src="library/codemirror/codemirror-placeholder.js"></script>
	<script src="scripts/upload.js"></script>	
	<script src="scripts/functions.js"></script>
	
</body>
</html>