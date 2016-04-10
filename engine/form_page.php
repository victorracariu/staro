<?php

include( "header.php" );
echo '
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>';

echo '
<script type="text/javascript" src="js/menu/EditableItem.js"></script>
<script type="text/javascript" src="js/menu/RangeMenu.js"></script>
<script type="text/javascript" src="js/grid/GridFilters.js"></script>
<script type="text/javascript" src="js/grid/filter/Filter.js"></script>
<script type="text/javascript" src="js/grid/filter/StringFilter.js"></script>
<script type="text/javascript" src="js/grid/filter/DateFilter.js"></script>
<script type="text/javascript" src="js/grid/filter/ListFilter.js"></script>
<script type="text/javascript" src="js/grid/filter/NumericFilter.js"></script>
<script type="text/javascript" src="js/grid/filter/BooleanFilter.js"></script>';
echo "\r\n";
echo "<script type=\"text/javascript\" src=\"js/xcheckbox.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nvar baseurl = '";
echo base_url( );
echo "';\r\nvar rvxForm;\r\nvar rvxFormKey   = ";
echo $view->ModelKey;
echo ";\r\nvar rvxFormTitle = '";
echo $view->Title;
echo "';\r\nvar rvxModelUrl  = '";
echo $view->ModelUrl;
echo "';\r\nvar rvxConfirmPrint = '";
echo $view->ConfirmPrint;
echo "';\r\nvar rvxControllerClass = '";
echo $view->ControllerClass;
echo "';\r\nvar rvxEditMode  = ";
echo $view->EditMode ? "true" : "false";
echo ";\r\nvar rvxPostMode  = ";
echo $view->PostMode ? "true" : "false";
echo ";\r\nvar rvxSaveHeader = false;\r\nvar rvxActionMenus = ";
echo $view->HtmlActions;
echo ";\r\nvar rvxFormReports = ";
echo $view->HtmlReports;
echo ";\r\nvar rvxToolbar;\r\nvar rvxFormControls;\r\nvar rvxFocusedCtrl;\r\n</script>\r\n\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/rvx_form.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/rvx_grid.js\"></script>\r\n\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nExt.onReady(function(){\r\nExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\r\nExt.QuickTips.init();\r\n\r\nvar rvxFormReader = new Ext.data.JsonReader();\r\nrvxFormReader.read = Ext.Rvx.CheckJson;\r\n\r\n";
echo $view->HtmlControls;
echo "\t\r\nvar rvxTabs = new Ext.TabPanel({\r\n    id: 'rvxTabs',\r\n\tactiveTab: 0,\r\n\tlayoutOnTabChange: true,\r\n\titems:[ ";
echo $view->TabsNames;
echo " ]\r\n});\t\r\n\r\nrvxFormControls = [";
echo $view->ControlNames;
echo "];\r\n\r\nrvxForm = new Ext.FormPanel({\r\n\tid: 'rvxForm',\r\n\ttitle: rvxFormTitle,\r\n\turl: rvxModelUrl + 'save/',\r\n\ttbar: rvxToolbar,\r\n\terrorReader: rvxFormReader,\r\n\tframe: false,\r\n\tborder: false,\r\n\tautoScroll: true,\r\n\tlabelWidth: 100,\r\n\titems: [rvxTabs]\r\n});\r\n\r\nrvxForm.addButton('OK', HandleSaveClose);\r\nrvxForm.addButton(rvx_locale.txtCancel, HandleClose);\r\nif( Ext.getCmp('ParentId') )\r\n{\r\n\trvxForm.addBu";
echo "tton(rvx_locale.txtAdd, HandleNew);\r\n}\r\nif( rvxPostMode ) \r\n{\r\n\trvxForm.addButton(rvx_locale.txtPost, HandlePost);\r\n\trvxForm.addButton(rvx_locale.txtUnpost, HandleUnpost);\r\n}\r\n";
$view->RenderButtons( );
echo "rvxForm.render('rvxform');\r\n\r\n// add resizable capabilities to grid\r\n\r\nif( typeof(Grid) != 'undefined' ) \r\n{\r\n\tif( Grid.rendered ) \r\n\t{\r\n\t\tvar Grid_Resizable = new Ext.Resizable( 'Grid', {pinned:true, handles:'se'} );\r\n\t\tGrid_Resizable.on('resize', Grid.syncSize, Grid );\r\n\t}\r\n}\r\n\r\n\r\n";
echo $view->FocusCtrl;
echo ".focus();\r\n\r\n}); //extjs\r\n\r\n
        // refresh grid after updating a record\r\nvar rvxTriggerStore;\r\nvar rvxControlValidate;\r\n\r\nfunction TriggerRefresh() \r\n{\r\n\trvxTriggerStore.baseParams.parentid = rvxFormKey;\r\n\trvxTriggerStore.reload();\r\n}\r\nfunction TriggerValidate(o)\r\n{\r\n\trvxControlValidate(o);\r\n}\r\n\r\n</script>\r\n</head>\r\n<body>\r\n<div id='rvxform'></div>\r\n</body>\r\n</html>";
?>
