<?php

class REngine
{
    private static $Managers = array( );
    public $ErrorMessage = "";
    public $engine;

    public function REngine( )
    {
    }

    public function Init( )
    {
        $this->LoadClass( "Log", "RLog", RVXPATH."log.php" );
        $this->LoadClass( "Router", "RRouter", RVXPATH."router.php" );
        $this->LoadClass( "Input", "RInput", RVXPATH."input.php" );
        $this->LoadClass( "Session", "RSession", RVXPATH."session.php" );
        $this->LoadClass( "Language", "RLanguage", RVXPATH."language.php" );
        $this->LoadClass( "Exception", "RException", RVXPATH."exception.php" );
        $this->LoadClass( "Security", "RSecurity", RVXPATH."security.php" );
        $this->LoadClass( "Context", "RContext", RVXPATH."context.php" );
        $this->LoadClass( "Database", "RDatabase", RVXPATH."database.php" );
        $this->LoadClass( "Mask", "RMask", RVXPATH."mask.php" );
    }

    public function Done( )
    {
        $this->Database->Disconnect( );
        rvx_log( "[URL] ========================================" );
    }

    public function &LoadClass( $member, $class, $filename, $instantiate = TRUE )
    {
        if ( isset( $this->Managers[$member] ) )
        {
            return $this->Managers[$member];
        }
        if ( !file_exists( $filename ) )
        {
            rvx_error( "Engine cannot load class. File not found: %s", $filename );
        }
        require_once( $filename );
        if ( $instantiate == FALSE )
        {
            $this->Managers[$member] = TRUE;
            return $this->Managers[$member];
        }
        $this->Managers[$member] =& new $class( );
        $this->$member = $this->Managers[$member];
        return $this->Managers[$member];
    }

    public function &LoadManager( $member )
    {
        $class = "R".$member;
        $filename = strtolower( RVXPATH.$member.".php" );
        return $this->LoadClass( $member, $class, $filename );
    }

    public function LoadView( $filename, $view )
    {
        $grid = $view;
        ob_start( );
        include( $filename );
        $contents = ob_get_contents( );
        ob_end_clean( );
        return $contents;
        rvx_error( "File not found: %s", $filename );
    }

    public function CreateController( $folder, $class )
    {
        $folder .= "/";
        if ( !file_exists( APXPATH.$folder.$class.EXT ) )
        {
            if ( !file_exists( APPPATH.$folder.$class.EXT ) )
            {
                return rvx_error( "Controller file not found: %s", $folder.$class );
            }
            include_once( APPPATH.$folder.$class.EXT );
        }
        else
        {
            include_once( APXPATH.$folder.$class.EXT );
        }
        $class_name = $class;
        if ( class_exists( $class."X" ) )
        {
            $class_name .= "X";
        }
        if ( !class_exists( $class ) )
        {
            rvx_error( "Controller class not found: %s", $class );
        }
        $ctrl = new $class_name( );
        $ctrl->Name = $class;
        $ctrl->Path = $folder;
        return $ctrl;
    }

    public function CreateModel( $folder, $class )
    {
        $ctrl = $this->CreateController( $folder, $class );
        return $ctrl->CreateModel( );
    }

}

function &get_engine( )
{
    global $rvx;
    if ( !is_object( $rvx ) )
    {
        $rvx = new REngine( );
    }

    return $rvx;
}

?>
