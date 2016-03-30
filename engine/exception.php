<?php

class RException
{

    public $action;
    public $severity;
    public $message;
    public $filename;
    public $line;
    public $ob_level;
    public $levels = array( 'E_ERROR' => 1, 'E_WARNING' => 2, 'E_PARSE' => 3, 'E_NOTICE' => 4, 'E_CORE_ERROR' => 5, 'E_CORE_WARNING' => 6, 'E_COMPILE_ERROR' => 7, 'E_COMPILE_WARNING' => 8, 'E_USER_ERROR' => 9, 'E_USER_WARNING' => 10, 'E_USER_NOTICE' => 11, 'E_STRICT' => 12 );

    public function RException( )
    {
        $this->ob_level = ob_get_level( );
    }

    public function Show404( $page = "" )
    {
        $heading = "404 Page Not Found";
        $message = "The page you requested was not found.";
        echo $this->ShowError( $heading, $message, "error_404" );
        exit( );
    }

    public function ShowError( $heading, $message, $template = "error_general" )
    {
        $message = implode( "<br><br>", !is_array( $message ) ? array( $message ) : $message );
        if ( $this->ob_level + 1 < ob_get_level( ) )
        {
            ob_end_flush( );
        }
        ob_start( );
        include( RVXPATH."errors/".$template.EXT );
        $buffer = ob_get_contents( );
        ob_end_clean( );
        return $buffer;
    }

    public function ShowPhpError( $severity, $message, $filepath, $line )
    {
        $severity = !isset( $this->levels[$severity] ) ? $severity : $this->levels[$severity];
        $filepath = str_replace( "\\", "/", $filepath );
        if ( FALSE !== strpos( $filepath, "/" ) )
        {
            $x = explode( "/", $filepath );
            $filepath = $x[count( $x ) - 2]."/".end( $x );
        }
        if ( $this->ob_level + 1 < ob_get_level( ) )
        {
            ob_end_flush( );
        }
        ob_start( );
        include( RVXPATH."errors/error_php".EXT );
        $buffer = ob_get_contents( );
        ob_end_clean( );
        echo $buffer;
    }

    public function PrintDebugCallstack( )
    {
        echo "<code>";
        $call_stack = debug_backtrace( );
        $i = count( $call_stack ) - 1;
        $j = 1;
        while ( 0 < $i )
        {
            $call = $call_stack[$i];
            if ( !isset( $call['file'] ) )
            {
                $call['file'] = "index.php";
            }
            if ( !isset( $call['line'] ) )
            {
                $call['line'] = "0";
            }
            if ( !isset( $call['class'] ) )
            {
                $call['class'] = "Kernel";
            }
            echo "<b>{$j}. ".$call['class'].".".$call['function']."()</b> - File: ".$call['file']." [Line: <b>".$call['line']."</b>]<br>";
            --$i;
            ++$j;
        }
        echo "</code>";
    }

}

?>
