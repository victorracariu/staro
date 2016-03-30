<?php

class RFormTab
{

    public $Id;
    public $Name;
    public $Caption;
    public $Columns;

    public function RFormTab( )
    {
        $this->Id = 1;
        $this->Caption = rvx_lang( "Detail" );
        $this->Columns = 1;
        $this->Name = "Tab1";
    }

    public function LoadXml( $ctrl )
    {
        $attrs = $ctrl->attributes( );
        $this->Id = (integer)$attrs['id'];
        $this->Caption = (string)$attrs['caption'];
        $this->Columns = (integer)$attrs['columns'];
        $this->Name = "Tab".$this->Id;
        if ( $this->Id == "" )
        {
            rvx_error( "RFormTab.LoadXml: Id is null" );
        }
        if ( $this->Columns == "" )
        {
            $this->Columns = 1;
        }
        $this->Caption = rvx_lang( $this->Caption );
    }

    public function Render( )
    {
        $html = "var {$this->Name} = new Ext.Panel({";
        $html .= "title:'{$this->Caption}', autoHeight:true";
        if ( 1 < $this->Columns )
        {
            $html .= ", layout:'tableform',layoutConfig:{columns:{$this->Columns}}, formConfig:{layoutConfig:{labelSeparator:''}, bodyStyle:'padding:4 4px', border:false}";
        }
        else
        {
            $html .= ",bodyStyle:'padding:4 4px'";
        }
        $html .= "});\n";
        return $html;
    }

}

?>
