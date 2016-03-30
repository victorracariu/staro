<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

header( "HTTP/1.1 404 Not Found" );
echo "<html>\n<head>\n<title>404 Page Not Found</title>\n</head>\n<body>\n<h1>";
echo $heading;
echo "</h1>\n<code>";
echo $message;
echo "<code>\n</body>\n</html>";
?>
