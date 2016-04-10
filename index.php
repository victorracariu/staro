<?php

include_once( 'config.php' );

define( 'APPVER', 'Contento' );
define( 'DBVER', '20' );

// define paths and directories
define( 'EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION) );
define( 'SCRIPT_DIRNAME', pathinfo(__FILE__, PATHINFO_DIRNAME) );
define( 'SCRIPT_BASENAME', pathinfo(__FILE__, PATHINFO_BASENAME));
define( 'SCRIPT_PATH', __FILE__ );
define( 'APPROOT', SCRIPT_DIRNAME );
define( 'APPPATH', APPROOT.'/app/' );
define( 'APXPATH', APPROOT.'/apx/' );
define( 'RVXPATH', APPROOT.'/engine/' );
define( 'BASEPATH', APPROOT.'/engine/' );//for ci
define( 'DATABASES_FILE', APPROOT.'/databases.xml' );
define( 'DEBUG_MODE', false );

// php setttings
ini_set('memory_limit', '-1');
set_time_limit(0);
//error_reporting( E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

// set datetime
date_default_timezone_set('UTC');

// hook php errors in our custom function
set_error_handler( 'rvx_exception_handler' );

// include common functions
require(RVXPATH.'common.php');
require(RVXPATH.'engine.php');

// create ENGINE
$rvx =& get_engine();
$rvx->Init();

// read params
$folder = $rvx->Router->FolderName;
$class  = $rvx->Router->ClassName;
$method = $rvx->Router->MethodName;

// create the controller
$CTRL = $rvx->CreateController( $folder, $class );

// check if controller method is defined
if( !method_exists( $CTRL, $method ) )
{
	rvx_error( 'Controller method not found: %s', $method );
	exit;
}

// blood check
if( $folder )
{
//	if( ! $rvx->Security->CheckRight( SECURITY_ACCESS ) )
//		return rvx_error( MSG_ACCESS_DENIED );
}

// proceed to action

call_user_func_array( array(&$CTRL, $method), array() );

// clean up
$rvx->Done();

?>