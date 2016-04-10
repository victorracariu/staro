<?php

header('Content-Type: text/html; charset=utf-8');

if ( !isset( $page_title ) )
{
    $page_title = "Content";
}
if ( !isset( $page_language ) )
{
    $page_language = "en";
}
echo "<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n<title>";
echo $page_title;
echo "</title>\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"js/resources/css/ext-all.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"js/ravix.css\">\r\n<link rel=\"icon\" type=\"image/png\" >\r\n</head>\r\n\r\n";
echo "<script type=\"text/javascript\" src=\"js/adapter/ext/ext-base.js\"></script>\r\n";
echo "<script type=\"text/javascript\" src=\"js/ext-all.js\"></script>\r\n";
echo "<script type=\"text/javascript\" src=\"js/ravix.js\"></script> \r\n";
echo "<script type=\"text/javascript\" src=\"js/locale/ravix_";
echo $page_language;
echo ".js\"></script> \r\n\r\n<body>";
?>
