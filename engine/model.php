<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class RModel
{

    public $Controller = NULL;
    public $Name = NULL;
    public $Path = NULL;
    public $TableName = NULL;
    public $TableKey = "Id";
    public $Fields = array( );
    public $Relations = array( );
    public $RelationTbl = "";
    public $Children = array( );
    public $ParentTable = "";
    public $ParentId = 0;
    public $RecordId = 0;
    public $InsertMode = FALSE;
    public $AuditMode = TRUE;
    public $PostMode = FALSE;
    public $ValidateFlag = TRUE;

    public function RModel( )
    {
    }

    public function SetController( $ctrl )
    {
        $this->Controller = $ctrl;
        $this->Name = $this->Controller->Name;
        $this->Path = $this->Controller->Path;
    }

    public function AddField( $field_name, $field_type )
    {
        $f = new RModelField( $this );
        $f->FieldName = $field_name;
        $f->FieldType = $field_type;
        $f->InitDefaults( );
        $this->Fields[$field_name] = $f;
    }

    public function Load( )
    {
        $rvx =& get_engine( );
        $fln = $rvx->Context->GetXmlPath( "model.xml", $this->Path.$this->Name );
        $this->LoadXml( $fln );
    }

    public function LoadXml( $fln )
    {
        if ( !file_exists( $fln ) )
        {
            return rvx_error( "File not found: %s", $fln );
        }
        $xml = simplexml_load_file( $fln );
        $attrs = $xml->model->attributes( );
        $this->TableName = ( string )$attrs->tablename;
        $this->TableKey = ( string )$attrs->tablekey;
        if ( $this->TableName == "" )
        {
            rvx_error( "model.xml: Attribute [tablename] is missing" );
        }
        if ( $this->TableKey == "" )
        {
            $this->TableKey = "Id";
        }
        foreach ( $xml->fields->field as $fld )
        {
            $x = new RModelField( $this );
            $x->LoadXml( $fld );
            $this->Fields[$x->FieldName] = $x;
        }
        if ( isset( $xml->relations ) )
        {
            foreach ( $xml->relations->relation as $rel )
            {
                $x = new RModelRelation( );
                $x->LoadXml( $rel );
                $this->Relations[] = $x;
            }
        }
        if ( isset( $xml->children ) )
        {
            foreach ( $xml->children->child as $child )
            {
                $x = new RModelRelation( );
                $x->LoadXml( $child );
                $this->Children[] = $x;
            }
        }
        $this->AddAuditFields( );
    }

    public function Open( $id )
    {
        $this->RecordId = $id;
        $rvx =& get_engine( );
        $sql = "SELECT * FROM {$this->TableName} WHERE {$this->TableKey}=:{$this->TableKey}";
        $qry = $rvx->Database->Query( $sql, array(
            $this->TableKey => $id
        ) );
        if ( $qry->num_rows( ) == 0 )
        {
            rvx_error( "Record does not exist: %s", $id );
            return FALSE;
        }
        $row = $qry->row_array( );
        foreach ( $this->Fields as $fld )
        {
            if ( !array_key_exists( $fld->FieldName, $row ) )
            {
                rvx_error( "Field does not exist: %s", $this->TableName.".".$fld->FieldName );
                return FALSE;
            }
            $fld->LoadValue( $row[$fld->FieldName] );
        }
        return TRUE;
    }

    public function Insert( )
    {
        foreach ( $this->Fields as $fld )
        {
            $fld->InitDefaults( );
        }
    }

    public function Edit( $id )
    {
        if ( !$this->ValidateDate( $id ) )
        {
            return FALSE;
        }
        return TRUE;
    }

    public function Post( $id )
    {
        $rvx =& get_engine( );
        if ( $id != 0 )
        {
            $this->Open( $id );
        }
        foreach ( $this->Fields as $fld )
        {
            $value = $rvx->Input->Post( $fld->FieldName );
            if ( $value !== FALSE )
            {
                $fld->SaveValue( $value );
            }
        }
    }

    public function Save( $id )
    {
        $this->InsertMode = $id == 0;
        if ( $id == 0 )
        {
            $this->SaveInsert( $id );
        }
        else
        {
            $this->SaveUpdate( $id );
        }
    }

    public function SaveInsert( $id )
    {
        $rvx =& get_engine( );
        //$f['CreateUserId'] = $rvx->Context->UserId;
        //$f['CreateTime'] = date( "Y-m-d H:i:s" );
        $this->SetFields( $f, TRUE );
        if ( $this->ValidateFlag )
        {
            $this->Validate( );
        }
        //$rvx->Database->StartTransaction( );
        $this->OnBeforeSave( $id );
        $fields = array( );
        foreach ( $this->Fields as $fld )
        {
            if ( $fld->FieldName == $this->TableKey )
            {
                continue;
            }
            $fields[$fld->FieldName] = $fld->NewValue;
        }
        $rvx->Database->Insert( $this->TableName, $fields );
        $id = $rvx->Database->LastInsertId( );
        $this->SetField( $this->TableKey, $id );
        $this->OnAfterSave( $id );
        //$rvx->Database->Commit();
        $rvx->Log->LogHuman( "INSERT", $this->GetDescriptionString( ) );
    }

    public function SaveUpdate( $id )
    {
        $rvx =& get_engine( );
        $f['UpdateUserId'] = $rvx->Context->UserId;
        $f['UpdateTime'] = date( "Y-m-d H:i:s" );
        $this->SetFields( $f, TRUE );
        if ( $this->ValidateFlag )
        {
            $this->Validate( );
        }
        //$rvx->Database->StartTransaction( );
        $this->OnBeforeSave( $id );
        $fields = array( );
        foreach ( $this->Fields as $fld )
        {
            if ( $fld->FieldName == $this->TableKey )
            {
                continue;
            }
            $fields[$fld->FieldName] = $fld->NewValue;
        }
        $rvx->Database->Update( $this->TableName, $fields, $this->TableKey, $id );
        $this->OnAfterSave( $id );
        //$rvx->Database->Commit();
        $rvx->Log->LogHuman( "UPDATE", $this->GetDescriptionString( ) );
    }

    public function Delete( $id )
    {
        $rvx =& get_engine( );
        if ( $this->CheckRelations( $id ) )
        {
            return rvx_error( "Record is used and cannot be deleted: %s", $this->RelationTbl );
        }
        if ( !$this->ValidateDate( $id ) )
        {
            return FALSE;
        }
        //$rvx->Database->StartTransaction( );
        $this->OnBeforeDelete( $id );
        $this->DeleteChildren( $id );
        $rvx->Database->Delete( $this->TableName, $this->TableKey, $id );
        $this->OnAfterDelete( $id );
        //$rvx->Database->Commit( );
        $rvx->Log->LogHuman( "DELETE", "Id=".$id );
    }

    public function DeleteChildren( $parentid )
    {
        $rvx =& get_engine( );
        foreach ( $this->Children as $child )
        {
            $rvx->Database->Execute( "DELETE FROM {$child->ForeignTbl} WHERE {$child->ForeignKey}={$parentid}" );
        }
    }

    public function RenderCombo( )
    {
        $rvx =& get_engine( );
        $searchpart = $rvx->Input->Post( "query" );
        $lookupkey = $rvx->Input->Post( "lookupkey" );
        $lookupname = $rvx->Input->Post( "lookupname" );
        $searchpart = strtoupper( $searchpart );
        $sql = "SELECT {$lookupkey} AS Id, {$lookupname} AS Name FROM {$this->TableName} WHERE UPPER({$lookupname}) LIKE '{$searchpart}%' ORDER BY {$lookupname} LIMIT 0, 50";
        $qry = $rvx->Database->Query( $sql );
        $data = rvx_json_encode( $qry->result_array( ) );
        $cb = isset( $_GET['callback'] ) ? $_GET['callback'] : "";
        echo $cb."({\"total\":\"".$qry->num_rows( )."\",\"results\":".$data."})";
    }

    public function Validate( )
    {
        if ( !$this->ValidateDate( 0 ) )
        {
            return FALSE;
        }
        foreach ( $this->Fields as $fld )
        {
            $fld->Validate( );
        }
    }

    public function SetField( $fld, $val )
    {
        if ( !array_key_exists( $fld, $this->Fields ) )
        {
            rvx_error( "Field does not exist: %s", $fld );
        }
        $this->Fields[$fld]->SetValue( $val );
    }

    public function GetField( $fld, $safe_mode = FALSE )
    {
        if ( !array_key_exists( $fld, $this->Fields ) )
        {
            if ( $safe_mode )
            {
                return "";
            }
            return rvx_error( "Field does not exist: %s", $fld );
        }
        return $this->Fields[$fld]->GetValue( );
    }

    public function SetFields( $f, $safe_mode = FALSE )
    {
        foreach ( $f as $fld => $val )
        {
            if ( $safe_mode && !array_key_exists( $fld, $this->Fields ) )
            {
                continue;
            }
            $this->SetField( $fld, $val );
        }
    }

    public function GetFields( $only_modified = FALSE )
    {
        $f = array( );
        foreach ( $this->Fields as $fld )
        {
            if ( $only_modified && $fld->IsModified == FALSE )
            {
                continue;
            }
            $f[$fld->FieldName] = $fld->NewValue;
        }
        return $f;
    }

    public function SaveField( $fld, $val )
    {
        if ( !isset( $this->Fields[$fld] ) )
        {
            rvx_error( "Field does not exist: %s", $fld );
        }
        $this->Fields[$fld]->SaveValue( $val );
    }

    public function CheckRelations( $id )
    {
        $rvx =& get_engine( );
        foreach ( $this->Relations as $rel )
        {
            $sql = "SELECT COUNT(*) FROM ".$rel->ForeignTbl." WHERE ".$rel->ForeignKey."=:Id";
            $key = $id;
            if ( $rel->FieldKey != "Id" )
            {
                $key = $rvx->Database->Retrieve( "SELECT ".$rel->FieldKey." FROM ".$this->TableName." WHERE Id=".$id );
            }
            $res = $rvx->Database->Retrieve( $sql, array(
                "Id" => $key
            ) );
            if ( !( 0 < $res ) )
            {
                continue;
            }
            $this->RelationTbl = $rel->ForeignTbl;
            return TRUE;
        }
        $this->RelationTbl = "";
        return FALSE;
    }

    public function CheckPosted( $id )
    {
        $rvx =& get_engine( );
        if ( $this->ParentTable == "" )
        {
            $sql = "SELECT IsPosted FROM ".$this->TableName." WHERE Id=".$id;
        }
        else if ( 0 < $id )
        {
            $sql = "SELECT F.IsPosted FROM ".$this->ParentTable." F, ".$this->TableName." G ";
            $sql .= "WHERE F.Id = G.ParentId AND G.Id = ".$id;
        }
        else
        {
            $sql = "SELECT F.IsPosted FROM ".$this->ParentTable." F WHERE Id = ".$this->ParentId;
        }
        $res = $rvx->Database->Retrieve( $sql );
        if ( $res == 1 )
        {
            return rvx_error( "Document is already posted" );
        }
    }

    public function AddAuditFields( )
    {
        if ( !$this->AuditMode )
        {
            return;
        }
        /*$this->AddField( "CreateUserId", FLD_INTEGER );
        $this->AddField( "UpdateUserId", FLD_INTEGER );
        $this->AddField( "CreateTime", FLD_STRING );
        $this->AddField( "UpdateTime", FLD_STRING );*/
    }

    public function RenderAudit( $id )
    {
        $rvx =& get_engine( );
        $sql = "SELECT * FROM ".$this->TableName." WHERE Id=:Id";
        $row = $rvx->Database->QueryRow( $sql, array(
            "Id" => $id
        ) );
        $s1 = "";
        $s2 = "";
        if ( array_key_exists( "CreateUserId", $row ) )
        {
            if ( $row['CreateUserId'] )
            {
                $s1 = $rvx->Database->Retrieve( "SELECT Username FROM User WHERE Id=".$row['CreateUserId'] );
            }
            $s1 = rvx_lang( "Created" ).": <b>".$s1."</b> [".$row['CreateTime']."]";
        }
        if ( array_key_exists( "UpdateUserId", $row ) )
        {
            if ( $row['UpdateUserId'] )
            {
                $s2 = $rvx->Database->Retrieve( "SELECT Username FROM User WHERE Id=".$row['UpdateUserId'] );
            }
            $s2 = rvx_lang( "Updated" ).": <b>".$s2."</b> [".$row['UpdateTime']."]";
        }
        echo "<code>".$s1."<br><br>".$s2."</code>";
    }

    public function ValidateDate( $id )
    {
        $rvx =& get_engine( );

        if ( $fld == "" )
        {
            return TRUE;
        }
        if ( $id != 0 )
        {
            $date = $rvx->Database->Retrieve( "SELECT ".$fld." FROM ".$this->TableName." WHERE Id=".$id );
        }
        else
        {
            $date = $this->GetField( $fld );
        }
        return TRUE;
    }

    public function OnBeforeSave( $id )
    {
    }

    public function OnAfterSave( $id )
    {
    }

    public function OnBeforeDelete( $id )
    {
    }

    public function OnAfterDelete( $id )
    {
    }

    public function CheckFieldsChanged( $flds )
    {
        if ( $this->InsertMode )
        {
            return FALSE;
        }
        foreach ( $flds as $f )
        {
            if ( !( $this->Fields[$f]->OldValue != $this->Fields[$f]->NewValue ) )
            {
                continue;
            }
            return TRUE;
        }
        return FALSE;
    }

    public function GenerateNumber( $number_field )
    {
        $rvx =& get_engine( );
        $nr = $this->GetField( $number_field );
        $lc = $this->GetField( "LocationId", TRUE );
        if ( $nr != FLD_AUTO )
        {
            return FALSE;
        }
        $nr = $rvx->Database->GenerateNr( $this->Name, 0 + 1, $lc );
        $this->SetField( $number_field, $nr );
    }

    public function GetDescriptionString( )
    {
        $msg = "";
        foreach ( $this->Fields as $fld )
        {
            if ( !$fld->FmtValue )
            {
                continue;
            }
            if ( $msg )
            {
                $msg .= " ";
            }
            $val = substr( $fld->FmtValue, 0, 50 );
            $val = str_replace( "\r", "", $val );
            $val = str_replace( "\t", "", $val );
            $val = str_replace( "\n", " ", $val );
            $msg .= $fld->FieldName."=".$val;
        }
        return $msg;
    }

}

include_once( RVXPATH."model_field.php" );
?>
