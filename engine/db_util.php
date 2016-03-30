<?php

include_once( RVXPATH . 'fd_country.php' );

//=============================================================================
class Db_Util
//=============================================================================
{
	const DB_RVX = 1;
	const DB_PZK = 2;
	const DB_BI = 3;

	private $_db = null;

//=============================================================================
	public function __construct( $target_server, $force_slave = FALSE ) 
//=============================================================================
	{
		$rvx =& get_engine();
		$rvx->Context->Load();

		if ( !$force_slave && ($target_server == self::DB_RVX && $rvx->Context->GetConfig('rvx_slave_enabled') == 0) )
		{
			$this->_db = $rvx->Database;
		}elseif ( $target_server == self::DB_RVX )
		{
			$this->_db = $this->CreateRVXSlaveConnection();
		}elseif ( $target_server == self::DB_PZK )
		{
			$this->_db = $this->CreatePZKSlaveConnection();
		}elseif ( $target_server == self::DB_BI )
		{
			$this->_db = $this->CreateBISlaveConnection();
		}else{
			return rvx_error ("Invalid DB Type requested: {$target_server}. Valid options are: " . self::DB_RVX . " and " . self::DB_PZK);
		}
	}

//=============================================================================
	public function GetDbConn()
//=============================================================================
	{
		if ( is_null ($this->_db) )
		{
			return rvx_error ("Db object is null. Invalid Initialization of Db_Util class.");
		}
		return $this->_db;
	}

//=============================================================================
	function CreateRVXSlaveConnection()
//=============================================================================
	{
		$cn = new RFd_Country();

                $slave_db = new RDatabase();

                $params = $cn->DbParams['FDRO'];
                $params['hostname'] = '2-rvxdb.ros.fd.corp';

                $params['dbdriver'] = 'mysql';
                $params['dbprefix'] = '';
                $params['pconnect'] = TRUE;
                $params['db_debug'] = TRUE;
                $params['cache_on'] = FALSE;
                $params['cachedir'] = '';
                $params['char_set'] = 'utf8';
                $params['dbcollat'] = 'utf8_general_ci';

                $slave_db->Connect( $params );

                return $slave_db;
	}

//=============================================================================
	function CreatePZKSlaveConnection()
//=============================================================================
	{
		$rvx =& get_engine();
		$cn = new RFd_Country();

                $slave_db = new RDatabase();

                $params = $cn->DbParams['FDRO'];
                $params['hostname'] = 'sql-2.ros.fd.corp';
                $params['username'] = $rvx->Context->GetConfig('pizokel_slave_username');
                $params['password'] = PZK_SLAVE_PASSWORD;
                $params['database'] = 'pizokel_product';

                $params['dbdriver'] = 'mysql';
                $params['dbprefix'] = '';
                $params['pconnect'] = TRUE;
                $params['db_debug'] = TRUE;
                $params['cache_on'] = FALSE;
                $params['cachedir'] = '';
                $params['char_set'] = 'utf8';
                $params['dbcollat'] = 'utf8_general_ci';

                $slave_db->Connect( $params );

                return $slave_db;
	}

//=============================================================================
	function CreateBISlaveConnection()
//=============================================================================
	{
		$rvx =& get_engine();
		$cn = new RFd_Country();

                $slave_db = new RDatabase();

                $params = $cn->DbParams['FDRO'];
                $params['hostname'] = $rvx->Context->GetConfig('bi_slave_host');
                $params['username'] = $rvx->Context->GetConfig('bi_slave_username');
                $params['password'] = BI_SLAVE_PASSWORD;
                $params['database'] = 'Mart';

                $params['dbdriver'] = 'mysql';
                $params['dbprefix'] = '';
                $params['pconnect'] = TRUE;
                $params['db_debug'] = TRUE;
                $params['cache_on'] = FALSE;
                $params['cachedir'] = '';
                $params['char_set'] = 'utf8';
                $params['dbcollat'] = 'utf8_general_ci';

                $slave_db->Connect( $params );

                return $slave_db;
	}
}