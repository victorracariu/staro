<?php

include_once( RVXPATH."dialog.php" );
class InfoNote extends RController
{

    public function Run( $ctrl )
    {
        $rvx =& get_engine( );
        $rec_id = $rvx->Context->GetParam( "id", true );
        $action = $rvx->Context->GetParam( "action" );
        $notes = $rvx->Input->Post( "Notes" );
        $model = $ctrl->CreateModel( );
        $model->Load( );
        $form = $ctrl->CreateForm( );
        $form->Load( );
        if ( $action == "submit" )
        {
            $sql = "UPDATE ".$model->TableName." SET Notes=:Notes WHERE Id=".$rec_id;
            $rvx->Database->Execute( $sql, array( "Notes" => $notes ) );
            echo "<script type=\"text/javascript\">window.close();</script>";
            return true;
        }
        $sql = "SELECT Notes FROM ".$model->TableName." WHERE Id=".$rec_id;
        $txt = $rvx->Database->Retrieve( $sql );
        $url = base_url( ).$rvx->Context->Path."/infonote/id/".$rec_id;
        $title = rvx_lang( "Notes" )." - ".$form->Title." [".$rec_id."]";
        $dlg = new RDialog( );
        $dlg->Init( $title, $url );
        $memo = $dlg->AddControl( CTRL_HTML, "Notes", $txt, false );
        $memo->HideLabel = true;
        $memo->Height = 300;
        $memo->Width = 600;
        $dlg->AddButton( BTN_SUBMIT );
        $dlg->Render( );
    }

}

?>
