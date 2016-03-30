<?php

class RGridEx
{

    public $Name = "Grid";
    public $Model = NULL;
    public $ModelUrl = "";
    public $Title = NULL;
    public $SelectSql = NULL;
    public $Query = NULL;
    public $Columns = NULL;
    public $Html = NULL;
    public $HtmlGrid = NULL;
    public $Width = 800;
    public $Height = 300;
    public $FormWidth = 600;
    public $FormHeight = 600;
    public $ColSpan = 1;
    public $HtmlFields = NULL;
    public $HtmlColumns = NULL;
    public $HtmlPlugins = NULL;
    public $FetchUrl = NULL;
    public $SaveUrl = NULL;

    public function Load( $fln )
    {
        $xml = simplexml_load_file( $fln );
        $attrs = $xml->view->attributes( );
        $this->Title = ( boolean )$attrs['title'];
        $this->FormWidth = ( integer )$attrs->formwidth;
        $this->FormHeight = ( integer )$attrs->formheight;
        $this->SelectSql = ( boolean )$xml->sql;
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

    public function AddColumn( $type, $name, $width, $editor )
    {
        $col = new RListColumn( );
        $col->FieldType = $type;
        $col->FieldName = $name;
        $col->Caption = rvx_lang( $name );
        $col->Width = $width;
        $col->Editor = $editor;
        $col->InitDefaults( );
        if ( $name == "Id" )
        {
            $col->Hidden = "true";
        }
        $this->Columns[] = $col;
        return $col;
    }

    public function SetModel( $model )
    {
        $this->Model = $model;
        $this->ModelUrl = base_url( ).$model;
        $this->FetchUrl = base_url( ).$model."/fetch/";
        $this->SaveUrl = base_url( ).$model."/gridsave/";
    }

    public function Render( )
    {
        $this->Html = "";
        $this->HtmlFields = "";
        $this->HtmlColumns = "";
        $this->HtmlPlugins = "";
        foreach ( $this->Columns as $col )
        {
            if ( $this->HtmlFields != "" )
            {
                $this->HtmlFields .= ",";
            }
            if ( $this->HtmlColumns != "" )
            {
                $this->HtmlColumns .= ",";
            }
            $this->HtmlFields .= "{name:'".$col->FieldName."', type:'string'}";
            $this->HtmlColumns .= $this->RenderColumn( $col );
        }
        $rvx =& get_engine( );
        foreach ( $this->Columns as $col )
        {
            if ( $col->FieldType != FLD_BOOL )
            {
                continue;
            }
            if ( $this->HtmlPlugins != "" )
            {
                $this->HtmlPlugins .= ",";
            }
            $this->Html .= $this->CreateColumnBool( $col );
            $this->HtmlPlugins .= "GridCol_".$col->FieldName;
        }
        $this->Html .= $rvx->LoadView( RVXPATH."gridex_page.php", $this );
        $this->Html .= $this->GridHtml;
        $this->Html .= $this->HtmlGrid;
        return $this->Html;
    }

    public function RenderColumn( $col )
    {
        if ( $col->Editor == false )
        {
            if ( $col->FieldType == FLD_BOOL )
            {
                return $this->RenderColumnBool( $col );
            }
            return $this->RenderColumnCommon( $col )."}";
        }
        if ( $col->FieldType == FLD_COMBO )
        {
            return $this->RenderColumnLookup( $col );
        }
        if ( $col->FieldType == FLD_INTEGER )
        {
            return $this->RenderColumnInteger( $col );
        }
        if ( $col->FieldType == FLD_NUMBER )
        {
            return $this->RenderColumnNumber( $col );
        }
        if ( $col->FieldType == FLD_DATE )
        {
            return $this->RenderColumnDate( $col );
        }
        if ( $col->FieldType == FLD_BOOL )
        {
            return $this->RenderColumnBool( $col );
        }
        return $this->RenderColumnText( $col );
    }

    public function RenderColumnCommon( $col )
    {
        $res = "{";
        $res .= "dataIndex:'".$col->FieldName."',";
        $res .= "header:'".$col->Caption."',";
        $res .= "align:'".$col->Align."',";
        $res .= "width:".$col->Width.",";
        $res .= "hidden:".$col->Hidden;
        return $res;
    }

    public function RenderColumnText( $col )
    {
        $res = $this->RenderColumnCommon( $col );
        $res .= ",editor:new Ext.form.TextField({selectOnFocus:true})";
        $res .= "}";
        return $res;
    }

    public function RenderColumnDate( $col )
    {
        $res = $this->RenderColumnCommon( $col );
        $res .= ",editor:new Ext.form.TextField({selectOnFocus:true})";
        $res .= "}";
        return $res;
    }

    public function RenderColumnInteger( $col )
    {
        $res = $this->RenderColumnCommon( $col );
        $res .= ",editor:new Ext.form.NumberField({selectOnFocus:true, allowBlank:false, decimalSeparator:'.', allowDecimals: false, allowNegative:true, blankText:'0', maxLength:16})";
        $res .= "}";
        return $res;
    }

    public function RenderColumnNumber( $col )
    {
        $res = $this->RenderColumnCommon( $col );
        $res .= ",editor:new Ext.form.NumberField({selectOnFocus:true, allowBlank: false, decimalSeparator: '.', allowDecimals: true, allowNegative: true, blankText: '0.00', maxLength: 16, decimalPrecision: 4})";
        $res .= "}";
        return $res;
    }

    public function RenderColumnLookup( $col )
    {
        $url = base_url( ).$col->LookupModel;
        $url .= "/select";
        $url .= "/lkptrigger/{$col->FieldName}";
        $url .= "/lkpfield/{$col->LookupKey}";
        $url .= "/lkpgrid/1";

        $cbxStore = $col->FieldName."Store";
        $lkpKey = $col->LookupKey;
        $lkpName = $col->LookupName;
        //$html = "function test(){g= Ext.getCmp('Grid'); return g};";
        $html = "var {$cbxStore} = new Ext.data.Store({ ";
        $html .= "proxy: new Ext.data.HttpProxy({url: '".base_url( ).$col->LookupModel."/gridlookup/'}),";
        $html .= "baseParams: {lookupkey:test(), lookupname: this.key},";
       $html .= "reader: new Ext.data.JsonReader( {totalProperty:'total', root:'results', id:'Id'}, [{name:'Id'}, {name:'Name'}] )";
        $html .= "});";
        //$html .= "";
        $this->Html .= $html;

        $res = $this->RenderColumnCommon( $col );
        $res .= ",editor:new Ext.form.ComboBox({store:{$cbxStore}, mode:'remote', minChars:2, typeAhead: true, triggerAction:'all', selectOnFocus:true, triggerClass:'x-combo-list-small', autoLoad: 'false',";
        $res .= "onTrigger2Click: function() {HandleTrigger('{$url}');}})}";

        return $res;
    }

    public function CreateColumnBool( $col )
    {
        $res = "var GridCol_{$col->FieldName} = new Ext.grid.CheckColumn({";
        $res .= "dataIndex:'".$col->FieldName."',";
        $res .= "header:'".$col->Caption."',";
        $res .= "width:".$col->Width.",";
        $res .= "hidden:".$col->Hidden;
        $res .= "});\n";
        return $res;
    }

    public function RenderColumnBool( $col )
    {
        $res = "GridCol_{$col->FieldName}";
        return $res;
    }

    public function FetchData( )
    {
        $rvx =& get_engine( );
        $start = $rvx->Input->Post( "start" );
        $limit = $rvx->Input->Post( "limit" );
        if ( $start == "" )
        {
            $start = 0;
        }
        if ( $limit == "" )
        {
            $limit = $this->PageSize;
        }
        $sql = $this->SelectSql." LIMIT ".$start.", ".$limit;
        $qry = $rvx->Database->Query( $sql );
        $data_count = $qry->num_rows( );
        $data_array = array( );
        foreach ( $qry->result_array( ) as $rec )
        {
            foreach ( $this->Columns as $col )
            {
                $val = $rec[$col->FieldName];
                $col->LoadValue( $val );
                $rec[$col->FieldName] = $col->FmtValue;
            }
            $data_array[] = $rec;
        }
        $data['total'] = $data_count;
        $data['results'] = $data_array;
        echo rvx_json_encode( $data );
    }

}

?>
