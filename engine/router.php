<?php

class RRouter
{

    public $UriString;
    public $Segments = array( );
    public $FolderName;
    public $ClassName;
    public $MethodName;

    public function RRouter( )
    {
        $this->FetchRequestUri( );
        $this->ExplodeSegments( );
        $this->FolderName = $this->Segment( 0 );
        $this->ClassName = $this->Segment( 1 );
        $this->MethodName = $this->Segment( 2 );
        if ( file_exists( APPPATH.$this->FolderName.EXT ) )
        {
            $this->FolderName = "";
            $this->ClassName = $this->Segment( 0 );
            $this->MethodName = $this->Segment( 1 );
        }
        if ( $this->FolderName == "" )
        {
            $this->FolderName = "";
        }
        if ( $this->ClassName == "" )
        {
            $this->ClassName = "main";
        }
        if ( $this->MethodName == "" )
        {
            $this->MethodName = "index";
        }
    }

    public function FetchRequestUri( )
    {
        $this->UriString = $this->ParseServerUri( );
    }

    public function ParseServerUri( )
    {
        if ( is_array( $_GET ) && count( $_GET ) == 1 )
        {
            return key( $_GET );
        }
        $path = isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : getenv( "PATH_INFO" );
        if ( $path != "" && $path != "/" && $path != "/".SCRIPT_PATH )
        {
            return $path;
        }
        $path = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : getenv( "QUERY_STRING" );
        if ( $path != "" && $path != "/" )
        {
            return $path;
        }
        $path = isset( $_SERVER['ORIG_PATH_INFO'] ) ? $_SERVER['ORIG_PATH_INFO'] : getenv( "ORIG_PATH_INFO" );
        if ( $path != "" && $path != "/" && $path != "/".SCRIPT_PATH )
        {
            return $path;
        }
        return "";
    }

    public function ParseRequestUri( )
    {
        if ( !isset( $_SERVER['REQUEST_URI'] ) || $_SERVER['REQUEST_URI'] == "" )
        {
            return "";
        }
        $request_uri = preg_replace( "|/(.*)|", "\\1", str_replace( "\\", "/", $_SERVER['REQUEST_URI'] ) );
        if ( $request_uri == "" || $request_uri == SCRIPT_BASENAME )
        {
            return "";
        }
        $fc_path = SCRIPT_DIRNAME;
        if ( strpos( $request_uri, "?" ) !== FALSE )
        {
            $fc_path .= "?";
        }
        $parsed_uri = explode( "/", $request_uri );
        $i = 0;
        foreach ( explode( "/", $fc_path ) as $segment )
        {
            echo $segment." - ".$parsed_uri[$i]."<br>";
            if ( isset( $parsed_uri[$i] ) && $segment == $parsed_uri[$i] )
            {
                ++$i;
            }
        }
        $parsed_uri = implode( "/", array_slice( $parsed_uri, $i ) );
        if ( $parsed_uri != "" )
        {
            $parsed_uri = "/".$parsed_uri;
        }
        return $parsed_uri;
    }

    public function FilterUri( $str )
    {
        return $str;
    }

    public function ExplodeSegments( )
    {
        foreach ( explode( "/", preg_replace( "|/*(.+?)/*$|", "\\1", $this->UriString ) ) as $val )
        {
            $val = trim( $this->FilterUri( $val ) );
            if ( $val != "" )
            {
                $this->Segments[] = $val;
            }
        }
    }

    public function Segment( $n, $no_result = FALSE )
    {
        return !isset( $this->Segments[$n] ) ? $no_result : $this->Segments[$n];
    }

    public function UriToAssoc( $n )
    {
        if ( count( $this->Segments ) < $n )
        {
            return array( );
        }
        $segments = array_slice( $this->Segments, $n - 1 );
        $i = 0;
        $lastval = "";
        $retval = array( );
        foreach ( $segments as $seg )
        {
            if ( $i % 2 )
            {
                $retval[$lastval] = $seg;
            }
            else
            {
                $retval[$seg] = FALSE;
                $lastval = $seg;
            }
            ++$i;
        }
        return $retval;
    }

    public function Redirect( $uri = "", $method = "location" )
    {
        switch ( $method )
        {
            case "refresh" :
                header( "Refresh:0;url=".base_url( ).$uri );
                break;
        }
        header( "Location: ".base_url( ).$uri );
        exit( );
    }

}

?>
