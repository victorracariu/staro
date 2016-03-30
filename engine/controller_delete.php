<?php

include_once RVXPATH . 'fd_country.php';

/**
 * Used to delete entries from any table. It can delete the entry either only
 * from the current country (default) or from all countries (see constructor).
 */
class Controller_Delete
{
    const LOG_DB = 'Logs';
    const COUNTRY_COL = 'LogCountry';
    const DELETE_TIME_COL = 'LogDeleteTime';

    protected $allCountries;
    protected $justLog;
    
    protected $country;

    /**
     * Constructor
     *
     * @param boolean $allCountries If true, deletes entry from all countries,
     * otherwise just from the current country
     * @param boolean $justLog If true, affected lines will just be logged and
     * not really be deleted
     */
    public function __construct($allCountries = false, $justLog = false)
    {
        $this->allCountries = $allCountries;
        $this->justLog = $justLog;

        $this->country = new RFd_Country();
    }

    /**
     * Deletes the record identified by $idColumn and $idValue from the
     * source table and inserts it the Log table. If $this->allCountries is set
     * to true, it gets done for every country, otherwise just for the current
     * country
     *
     * @param string $tableName The table for which the operation is done
     * @param mixed $idValue The column value that identifies the record
     * @param string $idColumn The column name that identifies the record
     * @param string $delete_country Country database to delete from
     */
    public function Delete($tableName, $idValue, $idColumn = 'Id', $delete_country = FALSE)
    {
        $rvx =& get_engine();

        if (!$this->LogTableExists($tableName))
        {
            $this->CreateLogTable($tableName);
        }

        if ($this->allCountries)
        {
            // delete from all countries
            foreach ($this->country->Countries as $country)
            {
                $this->DeleteFromCountry($country, $tableName, $idValue, $idColumn);
            }
        }
        else
        {
            // delete from current country
            $currentCountry = $rvx->Session->UserData['company'];
            if( $delete_country !== FALSE )
            {
                $currentCountry = $delete_country;
            }

            $this->DeleteFromCountry($currentCountry, $tableName, $idValue, $idColumn);
        }
    }

    /**
     * Checks if table exists in log schema
     *
     * @param string $tableName Name of table to check existance of
     * @return boolean True if table exists, false otherwise
     */
    protected function LogTableExists($tableName)
    {
        $rvx =& get_engine();

        $sql = "SHOW TABLES IN " . self::LOG_DB . " LIKE '{$tableName}'";
        $resultSet = $rvx->Database->QueryResult($sql);

        return sizeof($resultSet) > 0;
    }

    /**
     * Creates the given table in the log schema. Adds a 'LogCountry' column in
     * order to log entries from all countries. Also adds a 'LogDeleteTime' column
     * that specifies the time the record was logged (and also deleted).
     *
     * @param string $tableName The name of the table to be created
     * @param string $countryColAfter The country after which the 'LogCountry'
     * column will be created
     */
    protected function CreateLogTable($tableName, $countryColAfter = 'Id')
    {
        $rvx =& get_engine();
        $fullLogTableName = self::LOG_DB . '.' . $tableName;

        $sql = "CREATE TABLE {$fullLogTableName} AS
                SELECT * FROM {$tableName} WHERE 1 = 0";
        $rvx->Database->Execute($sql);

        // add 'LogDeleteTime' column
        $this->AddLogTableColumn($tableName, self::DELETE_TIME_COL, "TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'", $countryColAfter);

        // add 'LogCountry' column
        $this->AddLogTableColumn($tableName, self::COUNTRY_COL, 'VARCHAR(15) NOT NULL', $countryColAfter);
    }

    /**
     * Adds a column to the given table
     *
     * @param string $tableName The table to add a column to
     * @param string $columnName The name of the column to be added
     * @param string $type The type of the column to be added (e.g. VARCHAR(15))
     * @param string $colAfter The column after which to add the new column
     * (if not given or not found, new column will be added at the end)
     */
    protected function AddLogTableColumn($tableName, $columnName, $type, $colAfter = null)
    {
        $rvx =& get_engine();
        $fullLogTableName = self::LOG_DB . '.' . $tableName;

        // look for column $colAfter
        $sql = "SHOW COLUMNS FROM {$tableName}
                FROM " . self::LOG_DB . "
                LIKE '{$colAfter}'";
        $resultSet = $rvx->Database->QueryResult($sql);

        $sql = "ALTER TABLE {$fullLogTableName}
                ADD COLUMN {$columnName} {$type}";
        if (sizeof($resultSet) > 0)
        {
            // if column $colAfter exists, add new column after it
            $sql .= " AFTER {$colAfter}";
        }
        $rvx->Database->Execute($sql);
    }

    /**
     * Deletes the record identified by $idColumn and $idValue from the
     * source table and inserts it in the Log table
     *
     * @param string $country The country for which the operation is done
     * @param string $tableName The table for which the operation is done
     * @param mixed $idValue The column value that identifies the record
     * @param string $idColumn The column name that identifies the record
     */
    protected function DeleteFromCountry($country, $tableName, $idValue, $idColumn)
    {
        $rvx =& get_engine();

        $this->country->ConnectDb($country);
        $rvx->Database->StartTransaction();

        $this->InsertInLog($country, $tableName, $idValue, $idColumn);
        
        if (!$this->justLog)
        {
                $this->DeleteFromSource($tableName, $idValue, $idColumn);
        }

        $rvx->Database->Commit();
    }

    /**
     * Deletes the record identified by $idColumn and $idValue from the
     * source table
     *
     * @param string $tableName The table from which the delete is made
     * @param mixed $idValue The column value that identifies the record
     * @param string $idColumn The column name that identifies the record
     */
    protected function DeleteFromSource($tableName, $idValue, $idColumn)
    {
        $rvx =& get_engine();

        $sql = "DELETE FROM {$tableName} WHERE {$idColumn} = ";
        if (is_string($idValue))
        {
            // if it's string, wrap the value in ''
            $sql .= "'{$idValue}'";
        }
        else
        {
            $sql .= "{$idValue}";
        }

        $rvx->Database->Execute($sql);
    }

    /**
     * Inserts the record identified by $idColumn and $idValue in the Log table
     *
     * @param string $country The country for which the insert is made
     * @param string $tableName The table for which the insert is made
     * @param mixed $idValue The column value that identifies the record
     * @param string $idColumn The column name that identifies the record
     */
    protected function InsertInLog($country, $tableName, $idValue, $idColumn)
    {
        $rvx =& get_engine();

        $sql = $this->BuildLogInsertSql($tableName, $country, $idValue, $idColumn);
        $rvx->Database->Execute($sql);
    }

    /**
     * Builds the insert for the table in the Log db based on the values in the
     * source table
     *
     * @param string $tableName The name of the table being processed
     * @param string $country The country that will be inserted
     * @param mixed $idValue The value of the id column
     * @param string $idColumn The name of the id column
     * @return string The resulting sql insert string
     */
    protected function BuildLogInsertSql($tableName, $country, $idValue, $idColumn)
    {
        $fullLogTableName = self::LOG_DB . '.' . $tableName;

        $logColumns = $this->GetLogTableColumns($tableName);
        $sourceColumns = $this->GetTableColumns($tableName);
        $columns = array_intersect($logColumns, $sourceColumns);
        $columns[] = self::COUNTRY_COL;
        $columns[] = self::DELETE_TIME_COL;

        $sql = "INSERT INTO {$fullLogTableName} (" . implode(', ', $columns) . ")
                SELECT ";

        foreach ($columns as $column)
        {
            // country column does not exist in source table
            if ($column == self::COUNTRY_COL)
            {
                $sql .= "'{$country}' AS " . self::COUNTRY_COL . ", ";
                continue;
            }

            // delete time column does not exist in source table
            if ($column == self::DELETE_TIME_COL)
            {
                $sql .= "NOW() AS " . self::DELETE_TIME_COL . ", ";
                continue;
            }

            $sql .= $column . ", ";
        }
        $sql = substr($sql, 0, strlen($sql) - 2);

        $sql .= " FROM {$tableName} WHERE {$idColumn} = ";
        if (is_string($idValue))
        {
            // if it's string, wrap the value in ''
            $sql .= "'{$idValue}'";
        }
        else
        {
            $sql .= "{$idValue}";
        }

        return $sql;
    }

    /**
     * Get the list of the column names of the given table from the Log db
     *
     * @param string $tableName The table to get the columns of
     * @return array The list of column names
     */
    protected function GetLogTableColumns($tableName)
    {
        $rvx =& get_engine();

        $sql = "SHOW COLUMNS FROM {$tableName}
                FROM " . self::LOG_DB;
        $resultSet = $rvx->Database->QueryResult($sql);

        $columns = array();
        foreach ($resultSet as $row)
        {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    /**
     * Get the list of the column names of the given table
     *
     * @param string $tableName The table to get the columns of
     * @return array The list of column names
     */
    protected function GetTableColumns($tableName)
    {
        $rvx =& get_engine();

        $sql = "SHOW COLUMNS FROM {$tableName}";
        $resultSet = $rvx->Database->QueryResult($sql);

        $columns = array();
        foreach ($resultSet as $row)
        {
            $columns[] = $row['Field'];
        }

        return $columns;
    }
}

/**
 * Usage:
 *
 * - only delete from the current country:
 *
 * $deletor = new Controller_Delete();
 * $deletor->Delete('Item', 15); // default column name is 'Id'
 *
 * - delete from all countries:
 *
 * $deletor = new Controller_Delete(true);
 * $deletor->Delete('Item', 16, 'Id');
 */

?>