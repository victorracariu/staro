<?php

class RController
{

    public $Name;
    public $Path;
    public $ListClass = "RList";
    public $ModelClass = "RModel";
    public $FormClass = "RForm";
    public $List = null;
    public $Form = null;
    public $Model = null;
    public $TableName = "";

    public function RController( )
    {
        $rvx =& get_engine( );
        if ( $rvx->Security->IsConnected )
        {
            return true;
        }
        $rvx->Security->Login( );
        $rvx->Context->Load( );
        $rvx->Language->Load( "system", "general" );
        $rvx->Language->Load( $rvx->Context->Module );
    }

    public function CheckRight( $right )
    {
        $rvx =& get_engine( );
        $res = $rvx->Security->CheckRight( $right );
        if ( !$res )
        {
            rvx_error( MSG_ACCESS_DENIED );
        }
        return $res;
    }

    public function &CreateList( )
    {
        $this->List = new $this->ListClass( );
        $this->List->SetController( $this );
        return $this->List;
    }

    public function &CreateModel( )
    {
        $this->Model = new $this->ModelClass( );
        $this->Model->SetController( $this );
        return $this->Model;
    }

    public function &CreateForm( )
    {
        $this->Form = new $this->FormClass( );
        return $this->Form;
    }

    public function Index( )
    {
        if ( !$this->CheckRight( SECURITY_ACCESS ) )
        {
        }
        else
        {
            $view =& $this->CreateList( );
            $view->Load( );
            $view->Render( );
        }
    }

    public function Fetch( )
    {
        $view =& $this->CreateList( );
        $view->Load( );
        $view->FetchData( );
    }

    public function Add( )
    {
        if ( !$this->CheckRight( SECURITY_INSERT ) )
        {
        }
        else
        {
            $model =& $this->CreateModel( );
            $model->Load( );
            $model->Insert( );
            $form =& $this->CreateForm( );
            $form->Load( );
            $form->SetModel( $model, true );
            $form->Render( );
        }
    }

    public function View( )
    {
        if ( !$this->CheckRight( SECURITY_ACCESS ) )
        {
        }
        else
        {
            $rvx =& get_engine( );
            $id = $rvx->Context->GetParam( "id" );
            $model =& $this->CreateModel( );
            $model->Load( );
            $model->Open( $id );
            $form =& $this->CreateForm( );
            $form->Load( );
            $form->SetModel( $model, false );
            $form->Render( );
            $rvx->Log->LogHuman( "VIEW", $model->GetDescriptionString( ) );
        }
    }

    public function ViewLink( )
    {
        if ( !$this->CheckRight( SECURITY_ACCESS ) )
        {
        }
        else
        {
            $rvx =& get_engine( );
            $id = $rvx->Context->GetParam( "id" );
            $model =& $this->CreateModel( );
            $model->Load( );
            $fld = $rvx->Context->GetParam( "fld", true );
            $val = $rvx->Context->GetParam( "val", true );
            $vak = str_replace( "_", " ", $val );
            $sql = "SELECT Id FROM ".$model->TableName." WHERE {$fld}='{$val}' OR {$fld}='{$vak}'";
            $id = $rvx->Database->Retrieve( $sql );
            $model->Open( $id );
            $form =& $this->CreateForm( );
            $form->Load( );
            $form->SetModel( $model, false );
            $form->Render( );
            $rvx->Log->LogHuman( "VIEW", $model->GetDescriptionString( ) );
        }
    }

    public function Edit( )
    {
        if ( !$this->CheckRight( SECURITY_UPDATE ) )
        {
        }
        else
        {
            $rvx =& get_engine( );
            $id = $rvx->Context->GetParam( "id" );
            $model =& $this->CreateModel( );
            $model->Load( );
            $model->Open( $id );
            $form =& $this->CreateForm( );
            $form->Load( );
            if ( $model->Edit( $id ) )
            {
                $form->SetModel( $model, true );
                $form->Render( true );
            }
        }
    }

    public function CheckEdit( )
    {
        if ( !$this->CheckRight( SECURITY_UPDATE ) )
        {
        }
        else
        {
            $rvx =& get_engine( );
            $id = $rvx->Context->GetParam( "id" );
            $model =& $this->CreateModel( );
            $model->Load( );
            if ( $model->Edit( $id ) )
            {
                rvx_json_success( );
            }
        }
    }

    public function Save( )
    {
        $rvx =& get_engine( );
        $id = $rvx->Input->Post( "Id" );
        $rg = $id == 0 ? SECURITY_INSERT : SECURITY_UPDATE;
        if ( !$this->CheckRight( $rg ) )
        {
        }
        else
        {
            $model =& $this->CreateModel( );
            $model->Load( );
            $model->Post( $id );
            $model->Save( $id );
            $id = $model->GetField( "Id" );
            $nr = $model->GetField( "Number", true );
            echo "{success: true, id: {$id}, nr: '{$nr}'}";
        }
    }

    public function Delete( )
    {
        if ( !$this->CheckRight( SECURITY_DELETE ) )
        {
        }
        else
        {
            $rvx =& get_engine( );
            $id = $rvx->Input->Post( "id" );
            $model =& $this->CreateModel( );
            $model->Load( );
            $model->Delete( $id );
            echo "{success: true, id: {$id}}";
        }
    }

    public function Select( )
    {
        $view =& $this->CreateList( );
        $view->Load( );
        $view->RenderSelect( );
    }

    public function Combo( )
    {
        $model =& $this->CreateModel( );
        $model->Load( );
        $model->RenderCombo( );
    }

    public function CtrlValidate( )
    {
    }

    public function Printer( )
    {
        include( RVXPATH."qr_manager.php" );
        $qrm = new RQrManager( );
        $qrm->Execute( );
    }

    public function Audit( )
    {
        $rvx =& get_engine( );
        $id = $rvx->Context->GetParam( "id", true );
        $model =& $this->CreateModel( );
        $model->Load( );
        $model->RenderAudit( $id );
    }

    public function CheckPosted( $id, $posted )
    {
        $posted = $posted ? 1 : 0;
        $rvx =& get_engine( );
        $res = $rvx->Database->Retrieve( "SELECT IsPosted FROM ".$this->TableName." WHERE Id=".$id );
        if ( $res == $posted )
        {
            return true;
        }
        if ( $res )
        {
            return rvx_error( "Document is already posted" );
        }
        return rvx_error( "Document is not posted yet" );
    }

    public function SetPosted( $id, $posted )
    {
        $posted = $posted ? 1 : 0;
        $rvx =& get_engine( );
        $rvx->Database->Query( "UPDATE ".$this->TableName." SET IsPosted=".$posted." WHERE Id=".$id );
    }

    public function Export_Excel( )
    {
        include_once( RVXPATH."model_excel.php" );
        $excel = new RModelExcel( );
        $excel->ExportXls( $this );
    }

    public function Import_Excel( )
    {
        include_once( RVXPATH."model_excel.php" );
        $excel = new RModelExcel( );
        $excel->ImportXls( $this );
    }

    public function RedirectError( $error )
    {
        $rvx =& get_engine( );
        $rvx->ErrorMessage = "<b>".rvx_lang( "Error" )."</b>: ".rvx_lang( $error );
        $this->Index( );
    }

    public function InfoNote( )
    {
        include_once( RVXPATH."infonote.php" );
        $x = new InfoNote( );
        $x->Run( $this );
    }

    public function Inspector( )
    {
        $rvx =& get_engine( );
        if ( !$rvx->Context->IsAdmin( ) )
        {
            return rvx_error( MSG_ACCESS_DENIED );
        }
        $id = $rvx->Context->GetParam( "id", true );
        $model =& $this->CreateModel( );
        $model->Load( );
        $qry = $rvx->Database->QueryRow( "SELECT * FROM ".$model->TableName." WHERE Id=".$id );
        rvx_box_begin( $model->TableName." [ID:".$id."]" );
        rvx_table_begin( "", array( "Field", "Value" ) );
        foreach ( $qry as $fld => $val )
        {
            rvx_table_row( array( $fld, $val ) );
        }
        rvx_table_end( );
    }

}

include_once( RVXPATH."defines.php" );
include_once( RVXPATH."list.php" );
include_once( RVXPATH."model.php" );
include_once( RVXPATH."form.php" );
?>
