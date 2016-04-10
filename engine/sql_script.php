<?php

class RSqlScript
{

    public $VerboseSql = true;
    public $VerboseErr = true;

    public function ExecuteFile( $sqls )
    {
        if ( !( $buffer = file_get_contents( $sqlfile ) ) )
        {
            return 0 - 1;
        }
        return $this->ExecuteScript( $buffer );
    }

    public function ExecuteScript( $script, &$res )
    {
        $rvx =& get_engine( );
        $res = "";
        $ok = true;
        $queries = $this->SplitSql( $script );
        foreach ( $queries as $sql )
        {
            $sql = trim( $sql );
            if ( $sql != "" && $sql[0] != "#" && $sql != ";" )
            {
                if ( $this->VerboseSql )
                {
                    rvx_box_msg( $sql );
                }
                $res .= $sql."\n";
                $err = $rvx->Database->ExecuteSafe( $sql );
                if ( $err != "" )
                {
                    if ( $this->VerboseErr )
                    {
                        rvx_box_error( $err );
                    }
                    $res .= "### ERROR ###\n".$err."\n";
                    $ok = false;
                }
            }
        }
        return $ok;
    }

    public function SplitSql( $sql )
    {
        $sql = trim( $sql );
        $sql = preg_replace( "/\n\\#[^\n]*/", "", "\n".$sql );
        $buffer = array( );
        $ret = array( );
        $in_string = false;
        $i = 0;
        while ( $i < strlen( $sql ) )
        {
            if ( $sql[$i] == ";" && !$in_string )
            {
                $ret[] = substr( $sql, 0, $i );
                $sql = substr( $sql, $i + 1 );
                $i = 0;
            }
            if ( $in_string && $sql[$i] == $in_string && $buffer[1] != "\\" )
            {
                $in_string = false;
            }
            else if ( !$in_string && ( $sql[$i] == "\"" || $sql[$i] == "'" ) && ( !isset( $buffer[0] ) || $buffer[0] != "\\" ) )
            {
                $in_string = $sql[$i];
            }
            if ( isset( $buffer[1] ) )
            {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
            ++$i;
        }
        if ( !empty( $sql ) )
        {
            $ret[] = $sql;
        }
        return $ret;
    }

}

?>