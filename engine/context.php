<?php

class RContext
{

    public $Module;
    public $Model;
    public $Action;
    public $Params;
    public $Path;
    public $Company;
    public $Username;
    public $Password;
    public $Language;
    public $UserGroup;
    public $UserSuper = false;
    public $UserId;
    public $FirmaId;
    public $Configs;

    public function Init( )
    {
    }

    public function Load( )
    {
        $rvx =& get_engine( );
        $this->Module = $rvx->Router->Segment( 0 );
        $this->Model = $rvx->Router->Segment( 1 );
        $this->Action = $rvx->Router->Segment( 2 );
        $this->Params = $rvx->Router->UriToAssoc( 4 );
        $this->Path = $this->Module."/".$this->Model;
        $this->Company = $rvx->Session->GetUserData( "company" );
        $this->Username = $rvx->Session->GetUserData( "username" );
        $this->Password = $rvx->Session->GetUserData( "password" );
        $this->Language = $rvx->Session->GetUserData( "language" );
        $this->UserId = $rvx->Session->GetUserData( "userid" );
        $this->LoadUser( );
        $this->Save( );
        $rvx->Language->Code = substr( $this->Language, 0, 2 );
        rvx_log( "[URL] ".$this->Username."@".$rvx->Input->IpAddress( )." ".$rvx->Router->UriString );
        $this->LoadConfig( );
        $this->FirmaId = defined( "RVX_PORTAL_DB" ) ? $this->UserId : 1;
    }

    public function Reset( )
    {
        $data['company'] = "";
        $data['username'] = "";
        $data['password'] = "";
        $data['language'] = "";
        $data['userid'] = "";
        $rvx =& get_engine( );
        $rvx->Session->SetUserData( $data );
        $rvx->Session->SessionDestroy( );
    }

    public function LoadUser( )
    {
        if ( $this->UserId != "" )
        {
            return true;
        }
        $rvx =& get_engine( );
        $sql = "SELECT Id FROM User WHERE Username=:Username";
        $qry = $rvx->Database->Query( $sql, array( "Username" => $this->Username ) );
        $row = $qry->row_array( );
        if ( count( $row ) == 0 )
        {
            $this->UserId = 1;
        }
        else
        {
            $this->UserId = $row['Id'];
        }
    }


    public function Save( )
    {
        $data['userid'] = $this->UserId;
        $rvx =& get_engine( );
        $rvx->Session->SetUserData( $data );
    }

    public function GetParam( $index, $mandatory = false )
    {
        if ( !isset( $this->Params[$index] ) )
        {
            if ( $mandatory )
            {
                return rvx_error( "Input parameter is missing: %s", $index );
            }
            return FALSE;
        }
        return $this->Params[$index];
    }

    public function SetParam( $index, $value )
    {
        $this->Params[$index] = $value;
    }

    public function LoadConfig( )
    {
        $rvx =& get_engine( );
        $sql = "SELECT Code, Value, 0 AS Special FROM Config UNION SELECT Code, Value, 1 AS Special FROM UserConfig WHERE GroupId = ".$this->UserGroup;
        $rvx->Log->Enabled = false;
        $qry = $rvx->Database->Query( $sql );
        $rvx->Log->Enabled = true;
        foreach ( $qry->result_array( ) as $row )
        {
            $this->Configs[$row['Code']] = $row['Value'];
        }
    }

    public function GetConfig( $key, $default = "" )
    {
        if ( array_key_exists( $key, $this->Configs ) )
        {
            return $this->Configs[$key];
        }
        return $default;
    }

    public function GetLogoUrl( )
    {
        $logo = "pub/logo_".str_replace( " ", "_", strtolower( $this->Company ) ).".jpg";
        if ( file_exists( $logo ) )
        {
            return $logo;
        }
    }

    public function IsAdmin( )
    {
        return $this->UserGroup == USER_ADMINISTRATOR;
    }

    public function GetXmlPath( $filename, $path = "" )
    {
        $dir = $path ? $path : $this->Path;
        $fln = APXPATH.$dir."/".$filename;
        if ( file_exists( $fln ) )
        {
            return $fln;
        }
        $dir = $path ? $path : $this->Path;
        $fln = APPPATH.$dir."/".$filename;
        return $fln;
    }

}

?>
