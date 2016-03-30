<?php
include_once( RVXPATH.'excel/excel_reader2.php' );
include_once( RVXPATH.'controller.php' );
include_once( RVXPATH.'dialog.php' );
include_once( RVXPATH.'header.php' );

class Item_Category_Import_Excel extends RController
{
    var $ImportedItems;

    function Item_Category_Import_Excel()
    {
        parent::RController();
        $rvx =& get_engine();
    }

	function Index()
	{
		$rvx =& get_engine();

		$dlg = new RDialog();
		$dlg->Init( 'Import items', base_url().'catalog/item_category_import_excel/execute' );
		$dlg->AddControl( CTRL_UPLOAD, 'ExcelFile', '', false );
		$dlg->AddButton( BTN_SUBMIT );
		$dlg->Render();
	}

    function Execute()
    {
            $rvx =& get_engine();

            $success = true;

            set_time_limit(0);
            $filename = rvx_upload_file('ExcelFile');

            $excel = new Spreadsheet_Excel_Reader();
            $excel->read(SCRIPT_DIRNAME.'/'.$filename);
            $sheet = $excel->sheets[0];

            $excel_columns = $this->GetExcelColumns($sheet);
            $excel_columns[] = 'Errors';

            rvx_table_begin('Import Items', $excel_columns);

            for ($row_index = 2; $row_index <= $sheet['numRows']; $row_index++)
            {
                    $row = $sheet['cells'][$row_index];

                    $color = COLOR_WHITE;

                    try
                    {
                            $this->TreatExcelRow($row, $excel_columns);
                    }
                    catch (Exception $ex)
                    {
                            $color = COLOR_RED;
                            $errorColumnIndex = $this->GetExcelColumnIndex('Errors', $excel_columns);
                            $row[$errorColumnIndex] = $ex->getMessage(); // error must be last
                            $success = false;
                    }

                    $this->CompleteRow($row, $excel_columns);

                    rvx_table_row($row, $color);
            }

            rvx_table_end();

            if( $success )
            {
                $item = $rvx->CreateController('catalog', 'item_category');
                $item->SaveCategory($this->ImportedItems);
            }
    }

//=============================================================================
    function GetExcelColumns($excel)
//=============================================================================
    {
            $res = array();

            $header = $excel['cells'][1];
            foreach($header as $col)
            {
                    $res[] = $col;
            }

            return $res;
    }

//=============================================================================
    function TreatExcelRow($row, $excel_columns)
//=============================================================================
    {
            $item = array();

            foreach($excel_columns as $column_name)
            {
                $column_index = array_search($column_name, $excel_columns) + 1;
                if (isset($row[$column_index]))
                {
                        $item[$column_name] = trim($row[$column_index]);
                }
                else
                {
                        $item[$column_name] = '';
                }
            }

            $this->ImportedItems[] = $item;
    }

//=============================================================================
    function GetExcelColumnValue($row, $column_name, $excel_columns)
//=============================================================================
    {
            $column_index = $this->GetExcelColumnIndex($column_name, $excel_columns);
            if (isset($row[$column_index]))
            {
                    return trim($row[$column_index]);
            }
            else
            {
                    return '';
            }
    }

//=============================================================================
    function GetExcelColumnIndex($column_name, $excel_columns)
//=============================================================================
    {
            if (!in_array($column_name, $excel_columns))
            {
                    throw new Exception("Column '{$column_name}' does not exist");
            }

            return array_search($column_name, $excel_columns) + 1;
    }

//=============================================================================
        function CompleteRow(&$row, $excel_columns)
//=============================================================================
        {
                foreach ($excel_columns as $col)
                {
                        $key = $this->GetExcelColumnIndex($col, $excel_columns);

                        if (!array_key_exists($key, $row))
                        {
                                $row[$key] = '';
                        }
                }
        }

}