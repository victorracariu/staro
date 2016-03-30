<?php
include_once( RVXPATH.'excel/excel_reader2.php' );
include_once( RVXPATH.'controller.php' );
include_once( RVXPATH.'dialog.php' );
include_once( RVXPATH.'header.php' );

class Item_Import_Excel extends RController
{
    var $ImportedItems;

    function Item_Import_Excel()
    {
        parent::RController();
        $rvx =& get_engine();
    }

	function Index()
	{
		$rvx =& get_engine();

		$dlg = new RDialog();
		$dlg->Init( 'Import items', base_url().'catalog/item_import_excel/execute' );
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

            rvx_table_begin('Import Items', array('Code', 'Error'));

            for ($row_index = 2; $row_index <= $sheet['numRows']; $row_index++)
            {
                    $display = array('Code' => '', 'Error' => '');

                    $row = $sheet['cells'][$row_index];

                    $color = COLOR_WHITE;

                    $item = array();

                    $item = $this->TreatExcelRow($row, $excel_columns);

                    $this->CompleteRow($row, $excel_columns);

                    if( $success )
                    {
                        $item_controller = $rvx->CreateController('catalog', 'item');
                        $display['Error'] = $item_controller->SaveItem($item);
                        if($display['Error']) $color = COLOR_RED;

                        $display['Code'] = $item['Code'];
                    }

                    rvx_table_row($display, $color);
            }

            rvx_table_end();
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

            return $item;
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