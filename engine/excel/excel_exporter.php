<?php

class Excel_Exporter
{

    public function WriteLabel( $Row, $Col, $Value )
    {
        $L = strlen( $Value );
        echo pack( "v*", 516, 8 + $L, $Row, $Col, 0, $L );
        echo $Value;
    }

    public function WriteNumber( $Row, $Col, $Value )
    {
        $L = strlen( $Value );
        echo pack( "vvvvv", 515, 14, $Row, $Col, 0 );
        echo pack( "d", $Value );
    }

    public function ExportXls( $fln, $sql, $qry = array() )
    {
        $rvx =& get_engine( );

        if( empty($qry) )
            $qry = $rvx->Database->QueryResult( $sql );

        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Content-Type: application/force-download" );
        header( "Content-Type: application/octet-stream" );
        header( "Content-Type: application/download" );
        header( "Content-Disposition: attachment;filename=\"".$fln."\"" );
        header( "Content-Transfer-Encoding: binary" );
        echo pack( "vvvvvv", 2057, 8, 0, 16, 0, 0 );
        $col = 0;
        $row = 0;
        if ( count( $qry ) == 0 )
        {
            $this->WriteLabel( 0, 0, "EMPTY" );
            echo pack( "vv", 10, 0 );
            return false;
        }
        $qrow = $qry[0];
        foreach ( $qrow as $fld => $val )
        {
            $this->WriteLabel( 0, $col, $fld );
            ++$col;
        }
        $col = 0;
        $row = 1;
        foreach ( $qry as $qrow )
        {
            foreach ( $qrow as $fld => $val )
            {
                if ( is_numeric( $val ) )
                {
                    $this->WriteNumber( $row, $col, $val );
                }
                else
                {
                    $this->WriteLabel( $row, $col, $val );
                }
                ++$col;
            }
            $col = 0;
            ++$row;
        }
        echo pack( "vv", 10, 0 );
    }

}

?>
