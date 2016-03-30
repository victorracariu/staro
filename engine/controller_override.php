<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariusbocean
 * Date: 22/08/14
 * Time: 11:43
 * To change this template use File | Settings | File Templates.
 */
include_once( RVXPATH.'controller.php' );


class RController_Override extends RController {

    function __construct()
    {
        parent::__construct();
    }

//=============================================================================
    function Export_Excel($xml_file = 'list.xml')
//=============================================================================
    {
        $rvx = &get_engine();
        $id = $rvx->Context->GetParam( 'parentid', true );
        include_once( RVXPATH."excel/excel_exporter.php" );

        if($xml_file <> null)
        {
            $folder = $rvx->Router->FolderName;
            $subfolder = $rvx->Router->ClassName;
            $path = APXPATH.$folder.'/'.$subfolder.'/'.$xml_file;

            $xml_content = simplexml_load_file($path);
            $sql = $xml_content->sql;
            $sql = str_replace(':ParentId',$id,$sql);

            $xls = new Excel_Exporter();
            $xls->ExportXls($subfolder.'_'.$id.'.xls', $sql);

        }else{
            parent::Export_Excel();
        }



    }

}