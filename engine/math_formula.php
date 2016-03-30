<?php

class RMath_Formula
{

    public $Formula = NULL;
    public $Variables = NULL;
    public $MasterObject = NULL;
    public $MasterGetVar = NULL;

    public function Evaluate( $formula )
    {
        $this->Formula = $formula;
        if ( $formula == "" )
        {
            return 0;
        }
        $exp = "";
        $var = "";
        $i = 0;
        while ( $i < strlen( $formula ) )
        {
            $ch = $formula[$i];
            if ( ctype_alpha( $ch ) || $ch == "_" )
            {
                $var .= $ch;
            }
            else if ( is_numeric( $ch ) && $var != "" )
            {
                $var .= $ch;
            }
            else if ( $var != "" )
            {
                $val = $this->GetVariable( $var );
                $exp .= $val;
                $exp .= $ch;
                $var = "";
            }
            else
            {
                $exp .= $ch;
            }
            ++$i;
        }
        if ( $var != "" )
        {
            $exp .= $this->GetVariable( $var );
        }
        if ( $exp == "" )
        {
            return 0;
        }
        eval( "\$res = {$exp};" );
        return $res;
    }

    public function SetVariable( $name, $value )
    {
        $this->Variables[$name] = $value;
    }

    public function ResetVariables( )
    {
        $this->Variables = array( );
    }

    public function GetVariable( $name )
    {
        if ( $this->MasterObject && $this->MasterGetVar )
        {
            $object = $this->MasterObject;
            $function = $this->MasterGetVar;
            return $object->$function( $name );
        }
        if ( !array_key_exists( $name, $this->Variables ) )
        {
            return rvx_error( "Variable does not exist in formula: %s", $name );
        }
        return $this->Variables[$name];
    }

}

?>
