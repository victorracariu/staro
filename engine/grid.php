<?php

class RGrid
{

    public $Name = "Grid";
    public $Model;
    public $ModelUrl;
    public $Title;
    public $SelectSql;
    public $Query;
    public $Columns;
    public $Html;
    public $Width;
    public $Height;
    public $FormWidth;
    public $FormHeight;
    public $ColSpan;
    public $HtmlFields;
    public $HtmlColumns;
    public $HtmlPlugins;
    public $HtmlFilters;

    public function Load( $fln )
    {
        $xml = simplexml_load_file( $fln );
        $attrs = $xml->view->attributes( );
        $this->Title = (string)$attrs->title;
        $this->FormWidth = (string)$attrs->formwidth;
        $this->FormHeight = (string)$attrs->formheight;
        $this->SelectSql = (string)$xml->sql;
        if ( $this->FormWidth == "" )
        {
            $this->FormWidth = 600;
        }
        if ( $this->FormHeight == "" )
        {
            $this->FormHeight = 600;
        }
        foreach ( $xml->columns->column as $col )
        {
            $x = new RListColumn( );
            $x->LoadXml( $col );
            $this->Columns[] = $x;
        }
    }

    public function SetModel( $model )
    {
        $this->Model = $model;
        $this->ModelUrl = base_url( ).$model;
    }

    public function Render( )
    {
        $this->Title = "";
        $this->ModelUrl = base_url( ).$this->Model;
        $this->Html = "";
        $this->HtmlFields = "";
        $this->HtmlColumns = "";
        $this->HtmlPlugins = "";
        $c = '';

        $this->RenderColumnFilter();

        $grid_html = '';

        foreach ( $this->Columns as $col )
        {
                if( $col->FieldType == FLD_BOOL )
                {
                        $grid_html .= $this->CreateColumnBool($col);
                }
        }

        foreach ( $this->Columns as $col )
        {
            $HtmlFields .= $c.$this->RenderColumnField($col);
            $HtmlColumns .= $c.$this->RenderColumn($col);
            $c = ',';

            $this->HtmlFields = $HtmlFields;
            $this->HtmlColumns = $HtmlColumns;
        }

        $rvx =& get_engine();

        $grid_html .= $rvx->LoadView( RVXPATH."grid_page.php", $this );
        $grid_html = str_replace( "#Grid#", $this->Name, $grid_html );

        $this->Html .= $grid_html;
    }

    public function RenderColumn( $col )
    {
        if ( $col->FieldType == FLD_BOOL )
        {
            return $this->RenderColumnBool( $col );
        }
        return $this->RenderColumnCommon( $col )."}";
    }

    public function RenderColumnCommon( $col )
    {
        $res = "{";
        $res .= "dataIndex:'".$col->FieldName."',";
        $res .= "header:'".$col->Caption."',";
        $res .= "align:'".$col->Align."',";
        $res .= "width:".$col->Width.",";
        $res .= "sortable:true,";
        $res .= "hidden:".$col->Hidden;
        return $res;
    }

    public function CreateColumnBool( $col )
    {
        $res = "var #Grid#Col_{$col->FieldName} = new Ext.grid.CheckColumn({";
        $res .= "dataIndex:'".$col->FieldName."',";
        $res .= "header:'".$col->Caption."',";
        $res .= "width:".$col->Width.",";
        $res .= "hidden:".$col->Hidden;
        $res .= "});\n";
        return $res;
    }

    public function RenderColumnBool( $col )
    {
        $res = "#Grid#Col_{$col->FieldName}";
        return $res;
    }

    public function RenderColumnField( $col )
    {
        $res = "{name:'".$col->FieldName."'}";
        return $res;
    }

    public function RenderColumnFilter()
    {
            $list = new RList();
            $list->Columns = $this->Columns;
            $this->HtmlFilters = $list->ExtJsFilters();
    }

}

?>
