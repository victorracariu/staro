<?php

class RDatabase
{

    public $db;
    public $TransactionSqls = array( );
    public $TransactionParams = array( );
    public $TransactionDepth = 0;

    public function RDatabase( )
    {
    }

    public function Connect( $params )
    {
        include_once( RVXPATH."database/db.php" );
        $this->db =& DB( $params );
        return $this->db->conn_id;
    }

    public function Disconnect( )
    {
        if ( isset( $this->Database->db ) )
        {
            $this->Database->db->close( );
        }
    }

    public function Query( $sql, $params = array( ) )
    {
        foreach ( $params as $p => $v )
        {
            $param_name = ":".$p;
            $param_value = $this->db->escape( $v );
            $sql = str_replace( $param_name, $param_value, $sql );
        }
        $qry = $this->db->query( trim( $sql ) );
        return $qry;
    }

    public function Retrieve( $sql, $params = array( ) )
    {
        $qry = $this->Query( $sql, $params );
        if ( !$qry )
        {
            return NULL;
        }
        if ( $qry->num_rows( ) == 0 )
        {
            return NULL;
        }
        $row = array_values( $qry->row_array( ) );
        if ( count( $row ) == 0 )
        {
            return NULL;
        }
        return $row[0];
    }

    public function QueryResult( $sql, $params = array( ) )
    {
        $qry = $this->Query( $sql, $params );
        if ( !$qry )
        {
            return NULL;
        }
        return $qry->result_array( );
    }

    public function QueryRow( $sql, $params = array( ) )
    {
        $qry = $this->Query( $sql, $params );
        if ( $qry )
        {
            return $qry->row_array( );
        }
    }

    public function Execute( $sql, $params = array( ) )
    {
        $qry = $this->Query( $sql, $params );
        return $this->db->affected_rows( );
    }

    public function ExecuteSafe( $sql, $params = array( ) )
    {
        $this->db->db_debug = false;
        $this->db->query( trim( $sql ), FALSE, FALSE );
        $this->db->db_debug = true;
        return $this->db->_error_message( );
    }

    public function GenerateId( $tablename )
    {
        $prm['Name'] = $tablename;
        $vid = $this->Retrieve( "SELECT NextKey FROM Generator WHERE Name=:Name", $prm );
        if ( $vid != NULL )
        {
            $this->Execute( "UPDATE Generator SET NextKey=NextKey+1 WHERE Name=:Name", $prm );
            return $vid;
        }
        $prm['MaxGen'] = 1 + rvx_safenr( $this->Retrieve( "SELECT MAX(Id) FROM Generator" ) );
        $prm['MaxKey'] = 1 + rvx_safenr( $this->Retrieve( "SELECT MAX(Id) FROM ".$tablename ) );
        $this->Execute( "INSERT INTO Generator(Id,Name,NextKey) VALUES(:MaxGen,:Name,:MaxKey+1)", $prm );
        return $prm['MaxKey'];
    }

    public function GenerateNr( $model, $inc = 0, $location = 0 )
    {
        $rvx =& get_engine( );
        if ( $location == 0 )
        {
            $location = $rvx->Context->Location;
        }
        $sql = "SELECT Id, GenPrefix, GenValue, GenDigits FROM GeneratorNr WHERE Model=:Model AND (LocationId=:LocId OR LocationId=0) ORDER BY LocationId";
        $qry = $rvx->Database->QueryRow( $sql, array( "Model" => $model, "LocId" => $location ) );
        if ( count( $qry ) == 0 )
        {
            $this->Insert( "GeneratorNr", array( "Model" => $model, "LocationId" => 0, "GenValue" => 2 ) );
            return 1;
        }

        $len = $qry['GenDigits'] - strlen( $res );
        $i = 0;
        while ( $i < $len )
        {
            $res = "0".$res;
            ++$i;
        }
        $res = $qry['GenPrefix'].$res;
        if ( $inc != 0 )
        {
            $sql = "UPDATE GeneratorNr SET GenValue=GenValue+".$inc." WHERE Id=".$qry['Id'];
            $rvx->Database->Execute( $sql );
        }
        return $res;
    }

    public function LastInsertId( )
    {
        return $this->db->insert_id();
    }

    public function Insert( $tablename, $fields )
    {
        foreach ( $fields as $fld => $val )
        {
            $this->db->set( $fld, $val );
        }
        $this->db->insert( $tablename );
    }

    public function Update( $tablename, $fields, $keyname, $keyvalue )
    {
        foreach ( $fields as $fld => $val )
        {
            $this->db->set( $fld, $val );
        }
        $this->db->where( $keyname, $keyvalue );
        $this->db->update( $tablename );
    }

    public function Delete( $tablename, $keyname, $keyvalue )
    {
        $this->db->where( $keyname, $keyvalue );
        $this->db->delete( $tablename );
    }

    
    public function StartTransaction( )
    {
        if ( 1 < $this->TransactionDepth )
        {
            return true;
        }
        $this->db->trans_begin( );
    }

    public function Commit( )
    {
        if ( $this->TransactionDepth == 0 )
        {
        }
        else
        {
            if ( 0 < $this->TransactionDepth )
            {
            }
            else
            {
                $this->db->trans_commit( );
                $this->TransactionDepth = 0;
            }
        }
    }

    public function Rollback( )
    {
        if ( $this->TransactionDepth == 0 )
        {
        }
        else
        {
            if ( isset( $this->db ) )
            {
                $this->db->trans_rollback( );
            }
            $this->TransactionDepth = 0;
        }
    }

}

?>
