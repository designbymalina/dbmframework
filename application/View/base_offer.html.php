<!DOCTYPE html>
<html lang="<?php Dbm\Classes\TemplateClass::trans('lang'); ?>" class="h-100">
<head>
	<meta charset="<?php Dbm\Classes\TemplateClass::trans('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="author" content="Design by Malina">
    <meta name="robots" content="<?php Dbm\Classes\TemplateClass::trans('meta.robots', $data); ?>">
    <meta name="keywords" content="<?php Dbm\Classes\TemplateClass::trans('meta.keywords', $data); ?>">
	<meta name="description" content="<?php Dbm\Classes\TemplateClass::trans('meta.description', $data); ?>">
	<title><?php Dbm\Classes\TemplateClass::trans('meta.title', $data); ?></title>
	<link href="<?php echo path('images/favicon.ico'); ?>" rel="icon">
	<!-- Stylesheets -->
	<link href="<?php echo path('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
	<link href="<?php echo path('assets/css/font/bootstrap-icons.css'); ?>" rel="stylesheet">
	<!-- Stylesheets Custom -->
	<link href="<?php echo path('assets/css/custom.css'); ?>" rel="stylesheet">
<?php
    // Block Custom Head for the current page in: _include/head_[file_name] (e.g. additional Stylesheet of a given subpage)
     if ($pathHeadInc !== null) {
         include($pathHeadInc);
     }
?>
</head>
<body class="d-flex flex-column h-100">
<?php
    // Header
    include('_include/header_offer.html.php');
    // Block Body Content, view content of the pages; variable $pathViewName is in location: FrameworkClass.php > view()
    include($pathViewName);
    // Footer
    include('_include/footer.html.php');
    // Block Custom Body for the current page in: _include/body_[file_name] (e.g. additional JavaScript of a given subpage)
    if ($pathBodyInc !== null) {
        include($pathBodyInc);
    }
?>
</body>
</html>
