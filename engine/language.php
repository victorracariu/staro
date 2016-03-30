<?php

class RLanguage
{

    public $Directory = "locale/";
    public $Code = "en";
    public $Languages = array( );
    public $LoadFiles = array( );

    public function Load( $modulename, $filename = "" )
    {
        if ( $filename == "" )
        {
            $filename = $modulename;
        }
        $fn = APXPATH."/".$modulename."/_locale/".$filename."_".$this->Code.".php";
        if ( !file_exists( $fn ) )
        {
            $fn = APPPATH."/".$modulename."/_locale/".$filename."_".$this->Code.".php";
        }
        if ( !file_exists( $fn ) )
        {
            return false;
        }
        include_once( $fn );
        if ( !isset( $lang ) )
        {
            return false;
        }
        $this->LoadFiles[] = $fn;
        $this->Languages = array_merge( $this->Languages, $lang );
        unset( $lang );
        return true;
    }

    public function Translate( $s )
    {
        if ( isset( $this->Languages[$s] ) )
        {
            return $this->Languages[$s];
        }
        return $s;
    }

}

?>
