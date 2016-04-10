<?php

require_once RVXPATH.'model.php';

//=============================================================================
/**
 * Implements a model that logs all chenges to a History table.
 */
class HistoryModel extends RModel
//=============================================================================
{
        const LOGS_SCHEMA = 'staro';

        private $history_table;

//=============================================================================
        public function __construct()
//=============================================================================
        {
                parent::__construct();

                $this->history_table = $this->TableName . 'History';
        }

//=============================================================================
        public function OnBeforeSave($id)
//=============================================================================
        {
                $rvx =& get_engine();

                parent::OnBeforeSave($id);

                if (!$id)
                {
                        // if it's an insert, ignore
                        return;
                }

                $field_names = $this->GetFieldNames();

                $fields_string = implode(', ', $field_names);

                $sql = "SELECT {$fields_string} FROM {$this->TableName}
                        WHERE Id = :Id";
                $params = array('Id' => $id);
                $record = $rvx->Database->QueryRow($sql, $params);

                foreach ($field_names as $fld)
                {
                        $old_val = $record[$fld];
                        $new_val = $this->GetField($fld);

                        // null to empty value is not really a change
                        if ($old_val == null && $new_val == '')
                        {
                                continue;
                        }

                        if ($old_val != $new_val)
                        {
                                $history = $rvx->CreateModel($this->Path, $this->Name . '_history');
                                $history->Load();

                                $history->SetField('ParentId', $id);
                                $history->SetField('Field', $fld);
                                $history->SetField('OldFieldValue', $old_val);
                                $history->SetField('NewFieldValue', $new_val);

                                $history->Save(0);
                        }
                }
        }

//=============================================================================
        /**
         * Returns the names of this model's fields
         *
         * @return array
         */
        private function GetFieldNames()
//=============================================================================
        {
                $field_names = array();
                foreach ($this->Fields as $fld)
                {
                        if ($fld->FieldName == 'CreateUserId' ||
                                $fld->FieldName == 'CreateTime' ||
                                $fld->FieldName == 'UpdateUserId' ||
                                $fld->FieldName == 'UpdateTime')
                        {
                                continue;
                        }

                        $field_names[] = $fld->FieldName;
                }

                return $field_names;
        }
}