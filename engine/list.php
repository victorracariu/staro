<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class RList
{

    public $Controller = NULL;
    public $Title = NULL;
    public $SelectSql = NULL;
    public $Columns = NULL;
    public $FormWidth = NULL;
    public $FormHeight = NULL;
    public $DataArray = array( );
    public $DataCount = 0;
    public $Actions = array( );
    public $HtmlActions = "";
    public $HtmlParams = "{}";
    public $PageSize = 50;

    public function RList( )
    {
        $this->FormWidth = 500;
        $this->FormHeight = 500;
        $this->HtmlActions = "[]";
    }

    public function SetController( $ctrl )
    {
        $this->Controller = $ctrl;
    }

    public function Load( )
    {
        $rvx =& get_engine( );
        $fln = $rvx->Context->GetXmlPath( "list.xml" );
        $this->LoadXml( $fln );
        $this->PageSize = $rvx->Context->GetConfig( "list_page_size", 50 );
    }

    public function LoadXml( $fln )
    {
        $xml = simplexml_load_file( $fln );
        $attrs = $xml->view->attributes( );
        $this->Title = ( string )$attrs['title'];
        $this->FormWidth = ( integer )$attrs->formwidth;
        $this->FormHeight = ( integer )$attrs->formheight;
        $this->SelectSql = ( string )$xml->sql;
        $this->SelectSql = trim( $this->SelectSql );
        if ( $this->FormWidth == "" )
        {
            $this->FormWidth = 500;
        }
        if ( $this->FormHeight == "" )
        {
            $this->FormHeight = 500;
        }
        if ( $this->Title == "" )
        {
            rvx_error( "File list.xml missing <title>" );
        }
        if ( $this->SelectSql == "" )
        {
            rvx_error( "File list.xml missing <sql>" );
        }
        foreach ( $xml->columns->column as $col )
        {
            $x = new RListColumn( );
            $x->LoadXml( $col );
            $this->Columns[] = $x;
        }
        if ( !isset( $xml->actions ) )
        {
            $this->Actions = array( );
        }
        else
        {
            foreach ( $xml->actions->action as $act )
            {
                $a = new StdClass( );
                $attrs = $act->attributes( );
                $a->Caption = ( string )$attrs->caption;
                $a->Url = base_url( ).( string )$attrs->url;
                $a->Caption = rvx_lang( $a->Caption );
                $a->Popup = ( boolean )$attrs->popup;
                $this->Actions[] = $a;
            }
        }
        $rvx =& get_engine( );
        $this->Title = rvx_lang( $this->Title );
    }

    public function GetModelUrl( )
    {
        $rvx =& get_engine( );
        return base_url( ).$rvx->Context->Path."/";
    }

    public function ExtJsRecord( )
    {
        $result = "";
        foreach ( $this->Columns as $col )
        {
            if ( $result != "" )
            {
                $result .= ",";
            }
            $result .= "{name:'".$col->FieldName."'}";
        }
        return $result;
    }

    public function ExtJsModel( )
    {
        $result = "";
        foreach ( $this->Columns as $col )
        {
            if ( $result != "" )
            {
                $result .= ",";
            }
            if ( $col->FieldType == FLD_BOOL )
            {
                $result .= "new Ext.grid.CheckColumn({";
                $result .= "dataIndex:'".$col->FieldName."',";
                $result .= "header:'".$col->Caption."',";
                $result .= "align:'".$col->Align."',";
                $result .= "width:".$col->Width.",";
                $result .= "hidden:".$col->Hidden.",";
                $result .= "sortable:true";
                $result .= "})";
            }
            else
            {
                $result .= "{";
                $result .= "dataIndex:'".$col->FieldName."',";
                $result .= "header:'".$col->Caption."',";
                $result .= "align:'".$col->Align."',";
                $result .= "width:".$col->Width.",";
                $result .= "hidden:".$col->Hidden.",";
                $result .= "sortable:true";
                $result .= "}";
            }
        }
        return $result;
    }

    public function ExtJsSearchColumns( )
    {
        $result = "";
        foreach ( $this->Columns as $col )
        {
            if ( $result != "" )
            {
                $result .= ",";
            }
            $result .= "['".$col->Caption."']";
        }
        return $result;
    }

    public function ExtJsFirstColumn( )
    {
        foreach ( $this->Columns as $col )
        {
            if ( !( strcmp( $col->Hidden, "false" ) == 0 ) )
            {
                continue;
            }
            return $col->Caption;
        }
        return "";
    }

    public function ExtJsFilters( )
    {
        $result = "";
        $comma = "";
        foreach ( $this->Columns as $col )
        {
            if ( rvx_is_number( $col->FieldType ) )
            {
                $type = "numeric";
            }
            else if ( $col->FieldType == FLD_DATE )
            {
                $type = "date";
            }
            else if ( $col->FieldType == FLD_BOOL )
            {
                $type = "boolean";
            }
            else
            {
                $type = "string";
            }
            $result .= $comma."{type:'{$type}', dataIndex:'{$col->FieldName}'}";
            $comma = ",";
        }
        return $result;
    }

    public function FetchData( )
    {
        $rvx =& get_engine( );
        $sql = $this->SelectSql;
        $start = $rvx->Input->Post( "start" );
        $limit = $rvx->Input->Post( "limit" );
        $parentid = $rvx->Input->Post( "parentid" );
        if ( $start == "" )
        {
            $start = 0;
        }
        if ( $limit == "" )
        {
            $limit = 500;
        }
        $sql = $this->FilterSql( $sql );
        $sql = str_replace( ":ParentId", $parentid, $sql );
        $pos_from = strpos( $sql, "FROM" );
        $sql_count = "SELECT COUNT(*) ".trim( substr( $sql, $pos_from, strlen( $sql ) - $pos_from ) );
        $qry_count = $rvx->Database->Retrieve( $sql_count );
        $sql_fetch = $sql." LIMIT ".$start.", ".$limit;
        $qry_fetch = $rvx->Database->Query( $sql_fetch );
        $this->DataCount = $qry_count;
        $this->DataArray = array( );
        foreach ( $qry_fetch->result_array( ) as $rec )
        {
            foreach ( $this->Columns as $col )
            {
                if ( array_key_exists( $col->FieldName, $rec ) )
                {
                    $val = $rec[$col->FieldName];
                }
                else
                {
                    $val = "#!#".$col->FieldName;
                }
                $col->LoadValue( $val );
                $rec[$col->FieldName] = $col->FmtValue;
            }
            $this->DataArray[] = $rec;
        }
        $this->FetchDataSend( );
    }

    public function FilterSql( $sql )
    {
        $rvx =& get_engine( );
        $searchcol = $rvx->Input->Post( "searchcol" );
        $searchval = $rvx->Input->Post( "searchval" );
        $sortfld = $rvx->Input->Post( "sort" );
        $sortdir = $rvx->Input->Post( "dir" );
        $filters = $rvx->Input->Post( "filter" );
        if ( $searchval == "" && $sortfld == "0" && is_array( $filters ) == FALSE )
        {
            return $sql;
        }
        $ana = new RSqlAnalyzer( $sql );
        if ( $searchval != "" )
        {
            $ndx = $this->GetColumnIndex( $searchcol );
            if ( 0 <= $ndx )
            {
                $col = $this->Columns[$ndx];
                $ana->AppendFilter( $col->FieldName, $col->FieldType, $searchval );
            }
        }
        if ( is_array( $filters ) )
        {
            $ana->AppendExtjsFilters( $filters );
        }
        if ( $sortfld != "0" )
        {
            $ana->AppendSorter( $sortfld, $sortdir );
        }
        $sql = $ana->BuildSql( );
        return $sql;
    }

    public function FetchDataSend( )
    {
        $data['total'] = $this->DataCount;
        $data['results'] = $this->DataArray;
        echo rvx_json_encode( $data );
    }

    public function GetColumnIndex( $caption )
    {
        $i = 0;
        while ( $i < count( $this->Columns ) )
        {
            if ( $this->Columns[$i]->Caption == $caption )
            {
                return $i;
            }
            ++$i;
        }
        return 0 - 1;
    }

    public function Render( )
    {
        $rvx =& get_engine( );
        $this->RenderActions( );
        $this->RenderParams( );
        $view = $this;
        $page_title = $this->Title;
        $page_language = $rvx->Language->Code;
        include_once( RVXPATH."list_page.php" );
    }

    public function RenderActions( )
    {
        $this->HtmlActions = "[";
        $comma = "";
        foreach ( $this->Actions as $act )
        {
            $this->HtmlActions .= $comma;
            if ( $act->Caption != "-" )
            {
                if ( $act->Popup )
                {
                    $this->HtmlActions .= "{text:'{$act->Caption}', href:'{$act->Url}', hrefTarget:'_blank'}";
                }
                else
                {
                    $this->HtmlActions .= "{text:'{$act->Caption}', href:'{$act->Url}'}";
                }
            }
            else
            {
                $this->HtmlActions .= "'-'";
            }
            $comma = ",";
        }
        $this->HtmlActions .= "]";
    }

    public function RenderSelect( )
    {
        $rvx =& get_engine( );
        $lookupmode = TRUE;
        $lookupfield = $rvx->Context->GetParam( "lkpfield" );
        $lookuptrigger = $rvx->Context->GetParam( "lkptrigger" );
        $lookupgrid = $rvx->Context->GetParam( "lkpgrid" );
        if ( $lookupgrid == "" )
        {
            $lookupgrid = 0;
        }
        $this->RenderActions( );
        $this->RenderParams( );
        $view = $this;
        $page_title = $this->Title;
        $page_language = $rvx->Language->Code;
        include_once( RVXPATH."list_page.php" );
    }

    public function RenderParams( )
    {
        $rvx =& get_engine( );
        $comma = "";
        $this->HtmlParams = "{";
        foreach ( $rvx->Context->Params as $p => $v )
        {
            $this->HtmlParams .= $comma;
            $this->HtmlParams .= "{$p}:'{$v}'";
            $comma = ",";
        }
        $this->HtmlParams .= "}";
    }

}

include_once( RVXPATH."list_column.php" );
include_once( RVXPATH."sql_analyzer.php" );
?>
