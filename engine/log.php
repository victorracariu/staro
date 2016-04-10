<?php

class RLog
{

    public $LogPath = "log/";
    public $LogName = "";
    public $DateFmt = "Y-m-d H:i:s";
    public $Enabled = false;
    
    public function __construct() {
            $this->LogPath = APPROOT . "/log/";
    }

    public function Start( $name )
    {
        $this->LogName = $name;
        $this->Enabled = true;
    }

    public function Stop( )
    {
        $this->Enabled = false;
    }

    public function Write( $msg )
    {
        $rvx =& get_engine( );
        if ( $this->Enabled === false )
        {
            return false;
        }
        if ( $this->LogName == "" )
        {
            return false;
        }
        $msg = str_replace( "\r", "", $msg );
        $msg = str_replace( "\t", "", $msg );
        $msg = str_replace( "\n", " ", $msg );
        $msg = trim( $msg );
        $message = date( $this->DateFmt )." ".$msg."\r\n";
        if ( strlen( $msg ) == 0 )
        {
            $message = "\r\n";
        }
        $fn = $this->LogPath.$this->LogName."-".date( "Y-m-d" )."-".$rvx->Context->Username.".php";
        $fe = file_exists( $fn );
        if ( !( $fp = @fopen( @$fn, "a" ) ) )
        {
            rvx_error( "Cannot create log file: [%s]", $fn );
            return false;
        }
        if ( !$fe )
        {
            $message = "<?php die( 'Access denied!' ); ?>\n".$message;
        }
        $message .= "\r\n";
        flock( $fp, LOCK_EX );
        fwrite( $fp, $message );
        flock( $fp, LOCK_UN );
        fclose( $fp );
        return true;
    }

    public function LogHuman( $action, $text )
    {
        $rvx =& get_engine( );
        $msg = date( $this->DateFmt );
        $msg .= "\t".$rvx->Context->Username;
        $msg .= "\t".$rvx->Context->Module."/".$rvx->Context->Model;
        $msg .= "\t".$action;
        $msg .= "\t".$text;
        $msg .= "\r\n";
        $fn = $this->LogPath.$this->LogName."-".date( "Y-m-d" ).".php";
        $fe = file_exists( $fn );
        if ( !( $fp = @fopen( @$fn, "a" ) ) )
        {
            rvx_error( "Cannot create log file: [%s]", $fn );
            return false;
        }
        if ( !$fe )
        {
            $msg = "<?php die( 'Access denied!' ); ?>\n".$msg;
        }
        flock( $fp, LOCK_EX );
        fwrite( $fp, $msg );
        flock( $fp, LOCK_UN );
        fclose( $fp );
        return true;
    }

}

?>
