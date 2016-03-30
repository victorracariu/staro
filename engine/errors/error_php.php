<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "<body>\n";
echo "<s";
echo "tyle>\nbody {\n\tfont-family: Monaco, Verdana, Sans-serif;\n\tfont-size:13px;\n\t\n}\ncode {\n\tfont-family: Monaco, Verdana, Sans-serif;\n\tfont-size: 12px;\n\tbackground-color: #f9f9f9;\n\tborder: 1px solid black;\n\tdisplay: block;\n\tmargin: 4px 0 4px 0;\n\tpadding: 12px 12px 12px 12px;\n}\n</style>\n<code>\n<font color=\"red\"><b>PHP Error</b></font><br>\nMessage:  ";
echo $message;
echo "<br>\nSeverity: ";
echo $severity;
echo "<br>\nFilename: ";
echo $filepath;
echo "<br>\nLine Number: ";
echo $line;
echo "<br>\n</code>\n</body>";
?>
