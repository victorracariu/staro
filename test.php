<?php

include_once( RVXPATH.'controller.php' );

//=============================================================================
class Test extends RController
//=============================================================================
{
//=============================================================================
	function Index()
//=============================================================================
	{
		$rvx =& get_engine();

		set_time_limit(0);


		$sql = "SELECT Description FROM Item WHERE Id = 1";
		echo $rvx->Database->QueryResult($sql);
    }
}
?>

