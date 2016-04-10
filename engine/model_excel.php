<?php

class RModelExcel
{

    public function ExportXls_WriteLabel( $Row, $Col, $Value )
    {
        $L = strlen( $Value );
        echo pack( "v*", 516, 8 + $L, $Row, $Col, 0, $L );
        echo $Value;
    }

    public function ExportXls_WriteNumber( $Row, $Col, $Value )
    {
        $L = strlen( $Value );
        echo pack( "vvvvv", 515, 14, $Row, $Col, 0 );
        echo pack( "d", $Value );
    }

    public function ExportXls( $ctrl )
    {
        $model = $ctrl->CreateModel( );
        $model->Load( );
        $rvx =& get_engine( );
        $pid = $rvx->Context->GetParam( "parentid", true );
        $sql = "SELECT * FROM ".$model->TableName." WHERE ParentId=".$pid;
        $fln = $model->Name."_".$pid.".xls";
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
        $qrow = $qry[0];
        foreach ( $qrow as $fld => $val )
        {
            $this->ExportXls_WriteLabel( 0, $col, $fld );
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
                    $this->ExportXls_WriteNumber( $row, $col, $val );
                }
                else
                {
                    $this->ExportXls_WriteLabel( $row, $col, $val );
                }
                ++$col;
            }
            $col = 0;
            ++$row;
        }
        echo pack( "vv", 10, 0 );
    }

    public function ImportXls( $ctrl )
    {
        $rvx =& get_engine( );
        $pid = $rvx->Context->GetParam( "parentid" );
        if ( !$_FILES )
        {
            include_once( RVXPATH."dialog.php" );
            $dlg = new RDialog( );
            $dlg->Init( "Import Excel", "index.php?".$rvx->Router->UriString );
            $dlg->AddControl( CTRL_UPLOAD, "ExcelFile", "", false );
            $dlg->AddControl( CTRL_HIDDEN, "ParentId", $pid, false );
            $dlg->AddButton( BTN_SUBMIT );
            $dlg->Render( );
        }
        else
        {
            $pid = $rvx->Input->Post( "ParentId" );
            include_once( RVXPATH."excel/excel_reader2.php" );
            $fln = rvx_upload_file( "ExcelFile" );
            $excel = new Spreadsheet_Excel_Reader( );
            $excel->read( SCRIPT_DIRNAME."/".$fln );
            $sheet = $excel->sheets[0];
            $model = $ctrl->CreateModel( );
            $model->Load( );
            $lines = array( );
            $items_not_found = "";
            $row = 2;
            while ( $row <= $sheet['numRows'] )
            {
                $ry = $sheet['cells'][1];
                $rx = $sheet['cells'][$row];
                $line = array( );
                $i = 1;
                while ( $i <= count( $ry ) )
                {
                    $fld = $ry[$i];
                    if ( array_key_exists( $i, $rx ) )
                    {
                        $val = $rx[$i];
                    }
                    else
                    {
                        $val = "";
                    }
                    if ( $fld == "Id" || $fld == "ParentId" )
                    {
                        continue;
                    }
                    if ( $fld == "ItemCode" )
                    {
                        $line['ItemId'] = $rvx->Database->Retrieve( "SELECT Id FROM Item WHERE Code='{$val}'" );
                        if ( !$line['ItemId'] )
                        {
                            $items_not_found .= "<br>".$val;
                        }
                    }
                    $line[$fld] = $val;
                    ++$i;
                }
                $lines[] = $line;
                ++$row;
            }
            if ( $items_not_found )
            {
                return rvx_error( "Item codes not found: %s", $items_not_found );
            }
            foreach ( $lines as $line )
            {
                $model->ValidateFlag = false;
                $model->ParentId = $pid;
                $model->Insert( );
                $model->SetFields( $line, true );
                $model->Save( 0 );
            }
            $url = $rvx->Router->FolderName."/".$rvx->Router->ClassName;
            $url = str_replace( "_line", "", $url );
            $rvx->Router->Redirect( $url."/edit/id/".$pid );
        }
    }

}

?>
