<?php

class RConnectionInfo
{

    public $Name = NULL;
    public $Hostname = NULL;
    public $Database = NULL;
    public $Username = NULL;
    public $Password = NULL;
    public $Language = NULL;
    public $Deleted = false;

}

class RConnection
{

    public $Connections = NULL;

    public function RConnection( )
    {
        $rvx =& get_engine( );
        $this->LoadConnections( );
        $this->Language = $rvx->Input->Cookie( "rvx_language" );
        if ( $this->Language == "" )
        {
            $this->Language = "english";
        }
    }

    public function Index( )
    {
        $rvx =& get_engine( );
        $page_title = "Connections";
        $page_language = substr( $this->Language, 0, 2 );
        $page_companylist = $this->GetCompanyList( );
        if ( $page_companylist && !defined( "RVX_CREATE_DB" ) )
        {
            $rvx->Router->Redirect( __FILE__ );
        }
        include_once( RVXPATH."connection_page.php" );
    }

    public function Submit( )
    {
        $rvx =& get_engine( );
        $btn = $rvx->Input->Post( "Submit" );
        set_time_limit( 0 );
        $con = new RConnectionInfo( );
        $con->Name = $rvx->Input->Post( "Connection", true );
        $con->Hostname = $rvx->Input->Post( "Hostname", true );
        $con->Database = $rvx->Input->Post( "Database", true );
        $con->Username = $rvx->Input->Post( "Username", true );
        $con->Password = $rvx->Input->Post( "Password" );
        if ( $btn == "Create" )
        {
            if ( 0 <= $this->GetConnectionIndex( $con->Name ) )
            {
                return rvx_error( "Connection already in list" );
            }
            if ( $this->CreateDatabase( $con ) )
            {
                $this->Connections[] = $con;
                $this->SaveConnections( );
            }
        }
        else if ( $btn == "Select" )
        {
            if ( 0 <= $this->GetConnectionIndex( $con->Name ) )
            {
                return rvx_error( "Connection already in list" );
            }
            $this->Connections[] = $con;
            $this->SaveConnections( );
            $this->Index( );
        }
        else if ( $btn == "Remove" )
        {
            $ndx = $this->GetConnectionIndex( $con->Name );
            if ( $ndx < 0 )
            {
                return rvx_error( "Connection does not exist" );
            }
            $this->Connections[$ndx]->Deleted = true;
            $this->SaveConnections( );
            $this->Index( );
        }
    }

    public function GetCompanyList( )
    {
        $res = "";
        if ( isset( $this->Connections ) )
        {
            foreach ( $this->Connections as $con )
            {
                if ( $con->Deleted )
                {
                    continue;
                }
                if ( $res != "" )
                {
                    $res .= ", ";
                }
                $res .= "[\"".( boolean )$con->Name."\"]";
            }
        }
        return $res;
    }

    public function LoadConnections( )
    {
        $xml = simplexml_load_file( DATABASES_FILE );
        foreach ( $xml->database as $db )
        {
            $attrs = $db->attributes( );
            $con = new RConnectionInfo( );
            $con->Name = ( boolean )$attrs['name'];
            $con->Hostname = ( boolean )$attrs['host'];
            $con->Database = ( boolean )$attrs['database'];
            $con->Username = ( boolean )$attrs['user'];
            $con->Password = ( boolean )$attrs['pass'];
            $this->Connections[] = $con;
        }
    }

    public function SaveConnections( )
    {
        $str = "<databases>\n";
        foreach ( $this->Connections as $con )
        {
            if ( $con->Deleted )
            {
                continue;
            }
            $str .= "\t<database name=\"{$con->Name}\" driver=\"mysql\" host=\"{$con->Hostname}\" database=\"{$con->Database}\" user=\"{$con->Username}\" pass=\"{$con->Password}\" />";
            $str .= "\n";
        }
        $str .= "</databases>";
        $xml = fopen( DATABASES_FILE, "w" );
        fwrite( $xml, $str );
        fclose( $xml );
    }

    public function GetConnectionIndex( $name )
    {
        $i = 0;
        while ( $i < count( $this->Connections ) )
        {
            if ( $this->Connections[$i]->Name == $name )
            {
                return $i;
            }
            ++$i;
        }
        return 0 - 1;
    }

    public function CreateDatabase( $con )
    {
        $rvx =& get_engine( );
        $params['hostname'] = $con->Hostname;
        $params['username'] = $con->Username;
        $params['password'] = $con->Password;
        $params['dbdriver'] = "mysql";
        $params['pconnect'] = TRUE;
        $params['db_debug'] = TRUE;
        $params['cache_on'] = FALSE;
        $params['cachedir'] = "";
        $params['char_set'] = "utf8";
        $params['dbcollat'] = "utf8_general_ci";
        rvx_box_begin( "Create Database" );
        rvx_box_msg( "Connecting to MySQL server: ".$con->Hostname );
        if ( !$rvx->Database->Connect( $params ) )
        {
            return rvx_box_error( "Cannot connect to MySQL server" );
        }
        rvx_box_msg( "Select database: ".$con->Database );
        $rvx->Database->db->database = $con->Database;
        if ( !$rvx->Database->db->db_select( ) )
        {
            rvx_box_msg( "Creating database: ".$con->Database );
            if ( !$rvx->Database->Query( "CREATE DATABASE IF NOT EXISTS ".$con->Database." DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci" ) )
            {
                return rvx_box_error( "Cannot create database" );
            }
            if ( !$rvx->Database->db->db_select( ) )
            {
                return rvx_box_error( "Cannot select database" );
            }
        }
        include_once( RVXPATH."sql_script.php" );
        $script = new RSqlScript( );
        $script->VerboseSql = false;
        $script->VerboseErr = true;
        $filename = "sql/createdb.txt";
        if ( !file_exists( $filename ) )
        {
            return rvx_box_error( "File not found: ".$filename );
        }
        $scripts_file = file_get_contents( $filename );
        $scripts_list = explode( "\n", $scripts_file );
        $scripts_number = count( $scripts_list );
        $i = 0;
        while ( $i < $scripts_number )
        {
            if ( strlen( $scripts_list[$i] ) == 0 )
            {
                continue;
            }
            $filename = "sql/".trim( $scripts_list[$i] ).".sql";
            if ( !file_exists( $filename ) )
            {
                rvx_box_error( "File not found: ".$filename );
                continue;
            }
            rvx_box_msg( ( $i + 1 )."/".$scripts_number." Running script file ".$filename );
            $script_sql = file_get_contents( $filename );
            if ( !$script->ExecuteScript( $script_sql, $err ) )
            {
                $err = str_replace( "\n\n", "<br>", $err );
                $err = str_replace( "### ERROR ###", "", $err );
                rvx_box_error( $err );
                return false;
            }
            ++$i;
        }
        rvx_box_success( "login" );
        return true;
    }

}

include_once( RVXPATH."controller.php" );
?>
