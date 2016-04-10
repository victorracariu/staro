<?php

class RSqlAnalyzer
{

    public $SqlBlock = NULL;
    public $SqlSelect = NULL;
    public $SqlFrom = NULL;
    public $SqlWhere = NULL;
    public $SqlOrder = NULL;
    public $SqlGroup = NULL;
    public $FieldNames = NULL;
    public $FieldAlias = NULL;
    public $FixString = false;
    public $Filters = array( );
    public $Sorters = array( );

    public function RSqlAnalyzer( $sql )
    {
        if ( $sql == "" )
        {
            return;
        }
        $this->SqlBlock = $sql;
        $this->BreakParts( $sql );
        $this->BreakFields( $sql );
    }

    public function BreakParts( $sql )
    {
        $sql_len = strlen( $sql );
        $pos_select = strpos( $sql, "SELECT" );
        $pos_from = strpos( $sql, "FROM" );
        $pos_where = strpos( $sql, "WHERE" );
        $pos_order = strpos( $sql, "ORDER BY" );
        if ( $pos_order == FALSE )
        {
            $pos_order = strpos( $sql, "GROUP BY" );
        }
        if ( $pos_select === FALSE )
        {
            rvx_error( "Bad systax in SQL statement (no SELECT)" );
        }
        if ( $pos_from === FALSE )
        {
            rvx_error( "Bad systax in SQL statement (no FROM)." );
        }
        if ( $pos_order === FALSE )
        {
            $pos_order = $sql_len;
        }
        if ( $pos_where === FALSE )
        {
            $pos_where = $pos_order;
        }
        $this->SqlSelect = trim( substr( $sql, $pos_select, $pos_from - $pos_select ) );
        $this->SqlFrom = trim( substr( $sql, $pos_from, $pos_where - $pos_from ) );
        $this->SqlWhere = trim( substr( $sql, $pos_where, $pos_order - $pos_where ) );
        $this->SqlOrder = trim( substr( $sql, $pos_order, $sql_len - $pos_order ) );
    }

    public function BreakFields( )
    {
        $len = strlen( $this->SqlSelect );
        $fix = strlen( "SELECT" );
        $sel = trim( substr( $this->SqlSelect, $fix, $len - $fix ) );
        $pieces = explode( ",", $sel );
        foreach ( $pieces as $piece )
        {
            $piece = trim( $piece );
            $alias = $piece;
            $fname = $piece;
            $pas = strrpos( $piece, "AS" );
            $spc = strrpos( $piece, " " );
            if ( 0 < $pas )
            {
                $fname = substr( $piece, 0, $pas );
                $alias = substr( $piece, $spc, strlen( $piece ) - $spc );
            }
            else if ( 0 < $spc )
            {
                $fname = substr( $piece, 0, $spc );
                $alias = substr( $piece, $spc, strlen( $piece ) - $spc );
            }
            $this->FieldNames[] = trim( $fname );
            $this->FieldAlias[] = trim( $alias );
        }
    }

    public function AppendFilter( $fldname, $fldtype, $fldvalue )
    {
        $this->Filters[] = $this->GetFilterExpression( $fldname, $fldtype, $fldvalue );
    }

    public function AppendSorter( $sortfld, $sortord )
    {
        $realfld = $this->ExtractRealField( $sortfld );
        $this->Sorters[] = $realfld." ".$sortord;
    }

    public function BuildSql( )
    {
        $sql = $this->SqlSelect." ".$this->SqlFrom." ".$this->SqlWhere;
        if ( $this->SqlWhere == "" )
        {
            $operator = " WHERE ";
        }
        else
        {
            $operator = " AND ";
        }
        foreach ( $this->Filters as $filter )
        {
            $sql .= $operator.$filter;
            $operator = " AND ";
        }
        $operator = " ORDER BY ";
        foreach ( $this->Sorters as $sorter )
        {
            $sql .= $operator.$sorter;
            $operator = ", ";
        }
        if ( count( $this->Sorters ) == 0 )
        {
            $sql .= " ".$this->SqlOrder;
        }
        return $sql;
    }

    public function GetFilterExpression( $fldname, $fldtype, $fldvalue )
    {
        $rvx =& get_engine( );
        $real_field = $this->ExtractRealField( $fldname );
        $real_operator = $this->ExtractOperator( $fldvalue );
        $real_value = substr( $fldvalue, strlen( $real_operator ) );
        $real_value = $rvx->Mask->UnformatField( $real_value, $fldtype );
        if ( $real_operator == "" )
        {
            $real_operator = "=";
        }
        if ( $real_field == "" )
        {
            $real_field = $fldname;
        }
        if ( $fldtype == FLD_STRING )
        {
            return $this->GetFilterString( $real_field, $real_operator, $real_value );
        }
        if ( $fldtype == FLD_DATE )
        {
            return $this->GetFilterDate( $real_field, $real_operator, $real_value );
        }
        return $this->GetFilterNumber( $real_field, $real_operator, $real_value );
    }

    public function GetFilterString( $fldname, $operator, $fldvalue )
    {
        if ( $this->FixString )
        {
            return $fldname." LIKE '".$fldvalue."'";
        }
        return $fldname." LIKE '".$fldvalue."%'";
    }

    public function GetFilterDate( $fldname, $operator, $fldvalue )
    {
        return $fldname.$operator."'".$fldvalue."'";
    }

    public function GetFilterNumber( $fldname, $operator, $fldvalue )
    {
        return $fldname.$operator.$fldvalue;
    }

    public function ExtractRealField( $fldname )
    {
        if ( $this->SqlSelect == "" )
        {
            return $fldname;
        }
        $i = 0;
        while ( $i < count( $this->FieldNames ) )
        {
            $pieces = explode( ".", $this->FieldAlias[$i] );
            if ( count( $pieces ) == 2 )
            {
                $fld = strtoupper( $pieces[1] );
            }
            else
            {
                $fld = strtoupper( $pieces[0] );
            }
            if ( $fld == strtoupper( $fldname ) )
            {
                return $this->FieldNames[$i];
            }
            ++$i;
        }
        return false;
    }

    public function ExtractOperator( $fldvalue )
    {
        $res = "";
        $i = 0;
        while ( $i < strlen( $fldvalue ) )
        {
            if ( $fldvalue[$i] == "=" || $fldvalue[$i] == ">" || $fldvalue[$i] == "<" )
            {
                $res .= $fldvalue[$i];
            }
            else
            {
                return $res;
            }
            ++$i;
        }
        return "";
    }

    public function AppendExtjsFilters( $filters )
    {
        $qs = "";
        $opand = "";
        $i = 0;
        while ( $i < count( $filters ) )
        {
            $dtype = $filters[$i]['data']['type'];
            $value = $filters[$i]['data']['value'];
            $field = $filters[$i]['field'];
            $field = $this->ExtractRealField( $field );
            switch ( $dtype )
            {
            case "string" :
                $qs .= $opand.$field." LIKE '%".$value."%'";
                break;
            case "list" :
                do
                {
                    if ( !strstr( $value, "," ) )
                    {
                        break;
                    }
                    else
                    {
                        $fi = explode( ",", $value );
                        $q = 0;
                        while ( $q < count( $fi ) )
                        {
                            $fi[$q] = "'".$fi[$q]."'";
                            ++$q;
                        }
                        $value = implode( ",", $fi );
                        $qs .= $opand.$field." IN (".$value.")";
                    }
                } while ( 0 );
                $qs .= $opand.$field." = '".$value."'";
                break;
            case "boolean" :
                do
                {
                    if ( !( $value == "true" ) )
                    {
                        break;
                    }
                    else
                    {
                        $qs .= $opand.$field."=1";
                    }
                } while ( 0 );
                $qs .= $opand.$field."=0";
                break;
            case "numeric" :
                switch ( $filters[$i]['data']['comparison'] )
                {
                case "ne" :
                    $qs .= $opand.$field." != ".$value;
                    break;
                case "eq" :
                    $qs .= $opand.$field." = ".$value;
                    break;
                case "lt" :
                    $qs .= $opand.$field." < ".$value;
                    break;
                case "gt" :
                    $qs .= $opand.$field." > ".$value;
                }
                break;
            case "date" :
                switch ( $filters[$i]['data']['comparison'] )
                {
                case "ne" :
                    $qs .= $opand.$field." != '".date( "Y-m-d", strtotime( $value ) )."'";
                    break;
                case "eq" :
                    $qs .= $opand.$field." = '".date( "Y-m-d", strtotime( $value ) )."'";
                    break;
                case "lt" :
                    $qs .= $opand.$field." < '".date( "Y-m-d", strtotime( $value ) )."'";
                    break;
                case "gt" :
                    $qs .= $opand.$field." > '".date( "Y-m-d", strtotime( $value ) )."'";
                }
            }
            $opand = " AND ";
            ++$i;
        }
        $this->Filters[] = $qs;
    }

}

?>
