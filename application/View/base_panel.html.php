<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Design by Malina">
    <meta name="robots" content="noindex,nofollow">
	<title><?php Dbm\Classes\TemplateClass::trans('meta.title', $data); ?></title>
	<link href="<?php echo path('images/favicon.ico'); ?>" rel="icon">
	<!-- Stylesheets -->
	<link href="<?php echo path('admin/vendor/fontawesome-free/css/all.min.css'); ?>"  type="text/css" rel="stylesheet">
    <link href="<?php echo path('admin/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
    <!-- TODO! Add to project or delete: <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">-->
	<!-- Stylesheets Custom -->
	<link href="<?php echo path('admin/css/custom.css'); ?>" rel="stylesheet">
<?php
    // Block Custom Head for the current page in: _include/head_[file_name] in controller > method > $this->view(file_name)
    if ($pathHeadInc !== null) {
        include($pathHeadInc);
    }
?>
</head>
<body class="bg-gradient-primary" id="page-top">
<?php
	// View content of the pages; variable $pathViewName in location: FrameworkClass.php > view()
	include($pathViewName);
?>
	<!-- JavaScript Body -->
	<script src="<?php echo path('admin/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo path('admin/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo path('admin/js/sb-admin-2.min.js'); ?>"></script>
<?php
	// Block Custom Body for the current page in: _include/body_[file_name] in controller > method > $this->view(file_name)
    if ($pathBodyInc !== null) {
	    include($pathBodyInc);
	}
?>
</body>
</html>
