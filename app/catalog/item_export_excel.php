<?php

include_once( RVXPATH."excel/excel_exporter.php" );
include_once( RVXPATH."controller.php" );

class Item_Export_Excel  extends RController
{
//=============================================================================
	function Item_Export_Excel()
//=============================================================================
	{
		parent::RController();
	}

//=============================================================================
	function Index()
//=============================================================================
	{
		$rvx =& get_engine();

		include_once( RVXPATH.'dialog.php' );
		$dlg = new RDialog();
		$dlg->Init( 'Item Export', base_url().'catalog/item_export_excel/execute' );
		$dlg->AddButton( BTN_SUBMIT );
        $dlg->AddButton( BTN_CANCEL );
		$dlg->Render();
	}

//=============================================================================
	function Execute()
//=============================================================================
	{
		$rvx =& get_engine();

		$filename = 'Item.xls';

		$sql = "SELECT A.Code, ISC.Id Subcategory, A.EAN, IB.Name Brand, A.Name ProductName, IV.Name AttributeName, IV.Value AttributeValue
                FROM Item A, ItemSubcategory ISC, ItemBrand IB, ItemAttribute IA, ItemValue IV
                WHERE A.SubcategoryId = ISC.Id
                    AND A.BrandId = IB.Id
                    AND A.Id = IV.ItemId
                    AND IA.Id = IV.AttributeId
                    AND (IA.IsMandatory = 1 OR IA.IsFilter = 1)
                GROUP BY A.Id, IV.Name";

		$xls = new Excel_Exporter();

		$xls->ExportXls($filename, $sql);
	}

}