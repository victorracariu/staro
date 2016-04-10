<?php

echo "\r\nfunction #Grid#_HandleImportExcel() \r\n{\r\n\twindow.location=#Grid#_ModelUrl+\"/import_excel/parentid/\"+rvxFormKey}\r\n";
echo "\r\n\r\nfunction #Grid#_HandleAdd() \r\n{\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tif( rvxFormKey == 0 ) {\r\n\t\tExt.Rvx.ShowError( rvx_locale.txt#Grid#SaveHeader );\r\n\t\treturn false;\r\n\t}\r\n\tExt.Rvx.PopupWindow( #Grid#_ModelUrl + '/add/parentid/' + rvxFormKey, #Grid#_Form";
echo "Width, #Grid#_FormHeight );\r\n}\r\n\r\nfunction #Grid#_HandleOpen() \r\n{\t\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\tif (selected) {\t\r\n\t\tExt.Rvx.PopupWindow( #Grid#_ModelUrl + '/view/parentid/' + rvxFormKey + '/id/' + selected.get('Id'), #Grid#_FormWidth, #Grid#_FormHeight );\r\n\t} else {\r\n\t\tExt.MessageBox.alert( rvx_locale.txtWarning, rvx_locale.txt";
echo "SelectRecord );\r\n\t}\r\n}\r\n\r\nfunction #Grid#_HandleDelete() \r\n{\t\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\tif (selected) {\t\r\n\t\tExt.MessageBox.confirm( rvx_locale.txtConfirm, rvx_locale.txtDeleteConfirm, #Grid#_ProcessDelete);\r\n\t} else {\r\n\t\tExt.MessageBox.alert( rvx_locale.txtWarning, rvx_locale.txtSelectRecord );\r\n\t}\r\n};\r\n\r\nfunction #Grid#_Proc";
echo "essDelete(btn)\r\n{\r\n\tif( btn == 'no' ) {\r\n\t\treturn false;\r\n\t};\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\t#Grid#.el.mask(rvx_locale.txtDelete, 'x-mask-loading');\r\n\tExt.Ajax.request({\r\n\t\turl: #Grid#_ModelUrl + '/delete/',\r\n\t\tparams: { task: \"delete\", id: selected.get('Id'), key: 'id'\t},\r\n\t\tfailure: function(response,options) {\r\n\t\t\t#Grid#.el.unmask();\r\n\t\t\tExt.Rvx.ShowError( response";
echo ".responseText );\r\n\t\t},                                      \r\n\t\tsuccess: function(response,options) {\r\n\t\t\t#Grid#.el.unmask();\r\n\t\t\ttry {\r\n\t\t\t\tExt.decode( response.responseText );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.ShowError( response.responseText );\r\n\t\t\t\treturn;\r\n\t\t\t}\r\n\t\t\t#Grid#.getStore().reload();\r\n\t\t}                                   \r\n\t});\r\n};\r\n\r\nfunction #Grid#_KeyDown( e )\r\n{\r\n\tvar keyPressed = e";
echo ".getKey();\r\n\tif( keyPressed == 45 ) {\r\n\t\t#Grid#_HandleAdd();\r\n\t}\r\n\telse if( keyPressed == 46 ) {\r\n\t\t#Grid#_HandleDelete();\r\n\t}\r\n}\r\n\r\nfunction #Grid#_HandleExportExcel() \r\n{\r\n\twindow.location = #Grid#_ModelUrl + '/export_excel/parentid/' + rvxFormKey;\r\n} \r\n\r\n#Grid#_ModelUrl   = '";

echo $grid->ModelUrl;
echo "';\n#Grid#_Width      = ";
echo $grid->Width;
echo ";\n#Grid#_Height     = ";
echo $grid->Height;
echo ";\n#Grid#_FormWidth  = ";
echo $grid->FormWidth;
echo ";\n#Grid#_FormHeight = ";
echo $grid->FormHeight;
echo ";\n#Grid#_ColSpan    = ";
echo $grid->ColSpan;
echo ";\nvar #Grid#_DataRecord = new Ext.data.Record.create([";
echo $grid->HtmlFields;
echo "]);\nvar #Grid#_DataReader = new Ext.data.JsonReader({root:'results', totalProperty:'total', id:'id'}, #Grid#_DataRecord);
         \nvar #Grid#_DataStore  = new Ext.data.Store({proxy: new Ext.data.HttpProxy({url:#Grid#_ModelUrl+'/fetch/', method:'POST'}),baseParams:{parentid:rvxFormKey}, reader:#Grid#_DataReader});
         \nvar #Grid#_ColModel   = new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(), ";
echo $grid->HtmlColumns;
echo "]);";
echo "\nvar rvxFilters = new Ext.ux.grid.GridFilters({filters:[ ";
echo $grid->HtmlFilters;
echo "]});";
echo "\nvar #Grid#_Toolbar    = new Ext.Toolbar({items:[{text: rvx_locale.txtSaveHeader, icon: 'img/save.png', cls: 'x-btn-text-icon', handler: Grid_HandleSaveHeader },'-',{text: rvx_locale.txtAddLine, icon: 'img/new.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleAdd },{text: rvx_locale.txtOpenLine, icon: 'img/edit.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleOpen }";
echo ",{text: rvx_locale.txtDelLine, icon: 'img/delete.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleDelete },'-',{text: 'Export', icon: 'img/page_save.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleExportExcel },{text: 'Import', icon: 'img/page_excel.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleImportExcel }]});
        var #Grid#_SelModel = new Ext.grid.RowSelectionModel({singleSelect:true});";
echo "#Grid# = new Ext.grid.GridPanel({ id:'#Grid#', title:'', height: #Grid#_Height, width: #Grid#_Width, colModel:#Grid#_ColModel, store:#Grid#_DataStore, tbar:#Grid#_Toolbar, selModel:#Grid#_SelModel, colspan:#Grid#_ColSpan,plugins:[rvxFilters";
echo $grid->HtmlPlugins;
echo "]});
#Grid#.on('keydown', #Grid#_KeyDown, this, true);
#Grid#.addListener( 'rowdblclick', #Grid#_HandleOpen );

// pointer to datastore to be refreshed after line update
rvxTriggerStore = #Grid#_DataStore;

// hack grid refresh for update total controls
function #Grid#_ReadFunction(response)
{
	OnControlSuccess(response);
	var json = response.responseText;
	var o = eval(\"(\"+json+\")\");
	if(!o) {
		throw {message: \"JsonReader.read: Json object not found\"};
	}
	return #Grid#_DataReader.readRecords(o);
}
#Grid#_DataReader.read = #Grid#_ReadFunction;


// retrieve the grid data only if we have a head
if( rvxFormKey>0 ) {
	#Grid#_DataStore.load({params: {start:0, limit:1000}});
}


";
?>
