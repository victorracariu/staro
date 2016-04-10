<?php

class RFormBox
{

    public $Id;
    public $Tab;
    public $Name;
    public $Caption;
    public $Columns;
    public $ColSpan;
    public $Width;
    public $Height;
    public $GridModel;
    public $GridEdit;
    public $LabelWidth;

    public function RFormBox( )
    {
        $this->Id = 1;
        $this->Tab = 1;
        $this->Name = "Box1";
        $this->Caption = rvx_lang( "Detail" );
        $this->Columns = 1;
        $this->ColSpan = 1;
        $this->Width = 350;
        $this->GridModel = "";
        $this->GridEdit = false;
        $this->LabelWidth = 100;
    }

    public function LoadXml( $ctrl )
    {
        $attrs = $ctrl->attributes( );
        $this->Id = (integer)$attrs['id'];
        $this->Tab = (integer)$attrs['tab'];
        $this->Caption = (string)$attrs['caption'];
        $this->Width = (integer)$attrs['width'];
        $this->Height = (integer)$attrs['height'];
        $this->Columns = (integer)$attrs['columns'];
        $this->ColSpan = (integer)$attrs['colspan'];
        $this->GridModel = (string)$attrs['gridmodel'];
        $this->GridEdit = (boolean)$attrs['gridedit'];
        $this->GridName = (string)$attrs['gridname'];
        $this->LabelWidth = (integer)$attrs['labelwidth'];
        $this->Name = "Box".$this->Id;
        if ( $this->Id == "" )
        {
            rvx_error( "RFormBox.LoadXml: Id is null" );
        }
        if ( $this->Tab == "" )
        {
            $this->Tab = 1;
        }
        if ( $this->Columns == "" )
        {
            $this->Columns = 1;
        }
        if ( $this->ColSpan == "" )
        {
            $this->ColSpan = 1;
        }
        if ( $this->Width == "" )
        {
            $this->Width = 400;
        }
        if ( $this->Height == "" )
        {
            $this->Height = 200;
        }
        if ( $this->LabelWidth == "" )
        {
            $this->LabelWidth = 100;
        }
        if ( $this->GridName == "" )
        {
            $this->GridName = "Grid";
        }
        $this->Caption = rvx_lang( $this->Caption );
    }

    public function Render( )
    {
        if ( $this->GridModel != "" )
        {
            return $this->RenderGrid( );
        }
        $html = "var {$this->Name} = new Ext.form.FieldSet({";
        $html .= "title:'{$this->Caption}', width:{$this->Width}, autoHeight:true, style: 'vertical-align:top', colspan:{$this->ColSpan}, labelWidth:{$this->LabelWidth}";
        if ( $this->Columns == 99 )
        {
            $html .= ", layout:'absolute', height:{$this->Height}, autoHeight:false";
        }
        else if ( 1 < $this->Columns )
        {
            $html .= ", layout:'tableform', layoutConfig:{columns:{$this->Columns}}, formConfig:{bodyStyle:'padding:0 4px', border:false, labelWidth:{$this->LabelWidth}}";
        }
        $html .= "});\n";
        return $html;
    }

    public function RenderGrid( )
    {
        $this->Name = $this->GridName;
        if ( $this->GridEdit )
        {
            include_once( RVXPATH."gridex.php" );
            $grid = new RGridEx( );
        }
        else
        {
            include_once( RVXPATH."grid.php" );
            $grid = new RGrid();
        }
        $rvx =& get_engine( );
        $fln = $rvx->Context->GetXmlPath( "list.xml", $this->GridModel );
        $grid->Load( $fln );
        $grid->SetModel( $this->GridModel );
        $grid->Name = $this->GridName;
        $grid->Title = $this->Caption;
        $grid->Width = $this->Width;
        $grid->Height = $this->Height;
        $grid->ColSpan = $this->ColSpan;
        $grid->Render( );
        return $grid->Html;
    }

}

?>
