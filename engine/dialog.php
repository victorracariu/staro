<?php

class RDialog
{

    public $Title = NULL;
    public $ActionUrl = NULL;
    public $Width = NULL;
    public $Height = NULL;
    public $Columns = NULL;
    public $Controls = NULL;
    public $ControlNames = NULL;
    public $ControlHtml = NULL;
    public $SpecialHtml = NULL;
    public $Buttons = NULL;
    public $Grid = NULL;
    public $MsgText = NULL;
    public $RefreshOpener = 0;
    public $FocusCtrl = NULL;

    public function RDialog( )
    {
        $this->Title = "Dialog";
        $this->ActionUrl = "";
        $this->Width = 500;
        $this->Height = 500;
        $this->Columns = 1;
        $this->Controls = array( );
        $this->ControlNames = array( );
        $this->ControlHtml = "";
        $this->SpecialHtml = "";
        $this->Buttons = array( );
        $this->Grid = NULL;
    }

    public function Init( $title, $action_url )
    {
        $this->Title = rvx_lang( $title );
        $this->ActionUrl = $action_url;
    }

    public function LoadXml( $fln )
    {
        $xml = simplexml_load_file( $fln );
        $attrs = $xml->form->attributes( );
        $this->Title = ( string )$attrs->title;
        foreach ( $xml->controls->control as $ctrl )
        {
            $x = new RFormControl( );
            $x->LoadXml( $ctrl );
            $this->Controls[] = $x;
        }
        $this->Title = rvx_lang( $this->Title );
    }

    public function AddControl( $type, $name, $value, $readonly )
    {
        $ctrl = new RFormControl( );
        $ctrl->Type = $type;
        $ctrl->Name = $name;
        $ctrl->Caption = rvx_lang( $name );
        $ctrl->Value = $value;
        $ctrl->ReadOnly = $readonly;
        $ctrl->Value = str_replace( "'", "\\'", $ctrl->Value );
        $this->Controls[] = $ctrl;
        return $ctrl;
    }

    public function AddGrid( $grid )
    {
        $this->Grid = $grid;
    }

    public function AddLookup( $type, $name, $lookup_model, $lookup_table, $lookup_name, $value = "" )
    {
        $ctrl = new RFormControl( );
        $ctrl->Type = $type;
        $ctrl->Name = $name;
        $ctrl->Value = $value;
        $ctrl->Caption = rvx_lang( $name );
        $ctrl->LookupModel = $lookup_model;
        $ctrl->LookupTable = $lookup_table;
        $ctrl->LookupName = $lookup_name;
        $ctrl->LookupKey = "Id";
        $this->Controls[] = $ctrl;
        return $ctrl;
    }

    public function AddButton( $name, $handler = "HandleSubmit" )
    {
        if ( $name == BTN_CANCEL )
        {
            $handler = "HandleCancel";
        }
        if ( $name == BTN_CLOSE )
        {
            $handler = "HandleClose";
        }
        $btn = new RFormControl( );
        $btn->Name = $name;
        $btn->Caption = rvx_lang( $name );
        $btn->Handler = $handler;
        $this->Buttons[] = $btn;
    }

    public function Render( )
    {
        $rvx =& get_engine( );
        $this->ControlHtml = "";
        $this->ControlNames = "";
        $comma = "";
        foreach ( $this->Controls as $ctrl )
        {
            $this->ControlHtml .= $ctrl->Render( );
            $this->ControlHtml .= "rvxBox.add( {$ctrl->Name} );\n";
            $this->ControlNames .= $comma.$ctrl->Name;
            $comma = ",";
            if ( $this->FocusCtrl == "" && !$ctrl->ReadOnly && $ctrl->Type != CTRL_HIDDEN )
            {
                $this->FocusCtrl = $ctrl->Name;
            }
        }
        if ( isset( $this->Grid ) )
        {
            $this->ControlHtml .= $this->Grid->Render( );
            $this->ControlHtml .= "rvxDialog.add( 'Grid' );\n";
        }
        foreach ( $this->Buttons as $ctrl )
        {
            $this->ControlHtml .= "rvxDialog.addButton( {name:'{$ctrl->Name}', text:'{$ctrl->Caption}'}, {$ctrl->Handler} );\n";
        }
        $this->RefreshOpener = $rvx->Input->Post( "RefreshOpener" ) ? 1 : 0;
        $view = $this;
        $page_title = $view->Title;
        $page_language = $rvx->Language->Code;
        include_once( RVXPATH."dialog_page.php" );
    }

}

include_once( RVXPATH."form_control.php" );
?>
