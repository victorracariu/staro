<?php

class RInput
{

    public $IpAddress = FALSE;
    public $UserAgent = FALSE;
    public $AllowGetArray = FALSE;

    public function RInput( )
    {
        $this->SanitizeGlobals( );
    }

    public function SanitizeGlobals( )
    {
        $protected = array( "_SERVER", "_GET", "_POST", "_FILES", "_REQUEST", "_SESSION", "_ENV", "GLOBALS", "HTTP_RAW_POST_DATA", "system_folder", "application_folder", "BM", "EXT", "CFG", "URI", "RTR", "OUT", "IN" );
        foreach ( array( $_GET, $_POST, $_COOKIE, $_SERVER, $_FILES, $_ENV, isset( $_SESSION ) && is_array( $_SESSION ) ? $_SESSION : array( ) ) as $global )
        {
            if ( !is_array( $global ) )
            {
                if ( !in_array( $global, $protected ) )
                {
                    unset( $GLOBALS[$global] );
                }
            }
            else
            {
                foreach ( $global as $key => $val )
                {
                    if ( !in_array( $key, $protected ) )
                    {
                        unset( $GLOBALS[$key] );
                    }
                    if ( is_array( $val ) )
                    {
                        foreach ( $val as $k => $v )
                        {
                            if ( !in_array( $k, $protected ) )
                            {
                                unset( $GLOBALS[$k] );
                            }
                        }
                    }
                }
            }
        }
        if ( $this->AllowGetArray == FALSE )
        {
            $_GET = array( );
        }
        else if ( is_array( $_GET ) && 0 < count( $_GET ) )
        {
            foreach ( $_GET as $key => $val )
            {
                $_GET[$this->CleanInputKeys( $key )] = $this->CleanInputData( $val );
            }
        }
        if ( is_array( $_POST ) && 0 < count( $_POST ) )
        {
            foreach ( $_POST as $key => $val )
            {
                $_POST[$this->CleanInputKeys( $key )] = $this->CleanInputData( $val );
            }
        }
        if ( is_array( $_COOKIE ) && 0 < count( $_COOKIE ) )
        {
            foreach ( $_COOKIE as $key => $val )
            {
                $_COOKIE[$this->CleanInputKeys( $key )] = $this->CleanInputData( $val );
            }
        }
    }

    public function CleanInputData( $str )
    {
        if ( is_array( $str ) )
        {
            $new_array = array( );
            foreach ( $str as $key => $val )
            {
                $new_array[$this->CleanInputKeys( $key )] = $this->CleanInputData( $val );
            }
            return $new_array;
        }
        if ( get_magic_quotes_gpc( ) )
        {
            $str = stripslashes( $str );
        }
        return preg_replace( "/\r\n|\r|\n/", "\n", $str );
    }

    public function CleanInputKeys( $str )
    {
        if ( !preg_match( "/^[a-z0-9:_\\/-]+$/i", $str ) )
        {
            exit( "Disallowed Key Characters." );
        }
        return $str;
    }

    public function Get( $index = "", $mandatory = false )
    {
        if ( !isset( $_GET[$index] ) )
        {
            if ( $mandatory )
            {
                return rvx_error( "Input parameter is missing: %s", $index );
            }
            return FALSE;
        }
        return $_GET[$index];
    }

    public function Post( $index = "", $mandatory = false )
    {
        $res = FALSE;
        if ( isset( $_POST[$index] ) )
        {
            $res = $_POST[$index];
        }
        if ( !$res && $mandatory )
        {
            return rvx_error( "Input parameter is missing: %s", $index );
        }
        return $res;
    }

    public function Cookie( $index = "" )
    {
        if ( !isset( $_COOKIE[$index] ) )
        {
            return FALSE;
        }
        return $_COOKIE[$index];
    }

    public function Server( $index = "" )
    {
        if ( !isset( $_SERVER[$index] ) )
        {
            return FALSE;
        }
        return $_SERVER[$index];
    }

    public function IpAddress( )
    {
        if ( $this->IpAddress !== FALSE )
        {
            return $this->IpAddress;
        }
        if ( $this->server( "REMOTE_ADDR" ) && $this->server( "HTTP_CLIENT_IP" ) )
        {
            $this->IpAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if ( $this->server( "REMOTE_ADDR" ) )
        {
            $this->IpAddress = $_SERVER['REMOTE_ADDR'];
        }
        else if ( $this->server( "HTTP_CLIENT_IP" ) )
        {
            $this->IpAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if ( $this->server( "HTTP_X_FORWARDED_FOR" ) )
        {
            $this->IpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if ( $this->IpAddress === FALSE )
        {
            $this->IpAddress = "0.0.0.0";
            return $this->IpAddress;
        }
        if ( strstr( $this->IpAddress, "," ) )
        {
            $x = explode( ",", $this->IpAddress );
            $this->IpAddress = end( $x );
        }
        if ( !$this->ValidateIp( $this->IpAddress ) )
        {
            $this->IpAddress = "0.0.0.0";
        }
        return $this->IpAddress;
    }

    public function ValidateIp( $ip )
    {
        $ip_segments = explode( ".", $ip );
        if ( count( $ip_segments ) != 4 )
        {
            return FALSE;
        }
        if ( substr( $ip_segments[0], 0, 1 ) == "0" )
        {
            return FALSE;
        }
        foreach ( $ip_segments as $segment )
        {
            if ( preg_match( "/[^0-9]/", $segment ) || 255 < $segment || 3 < strlen( $segment ) )
            {
                return FALSE;
                break;
            }
        }
        return TRUE;
    }

    public function UserAgent( )
    {
        if ( $this->UserAgent !== FALSE )
        {
            return $this->UserAgent;
        }
        $this->UserAgent = !isset( $_SERVER['HTTP_USER_AGENT'] ) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        return $this->UserAgent;
    }

    public function PostJson( $param )
    {
        $res = $this->Post( $param );
        if ( $res != "" )
        {
            $res[0] = " ";
            $res = trim( $res );
        }
        return $res;
    }

    public function PostDate( $index = "", $mandatory = false )
    {
        $rvx =& get_engine( );
        $res = $this->Post( $index, $mandatory );
        return $rvx->Mask->UnformatDate( $res );
    }

}

?>
