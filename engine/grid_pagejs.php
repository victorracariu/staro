<?php
/*********************/
/*                   */
/*  Dezend for PHP5  */
/*         NWS       */
/*      Nulled.WS    */
/*                   */
/*********************/

echo "var #Grid#;\r\nvar #Grid#_ModelUrl;\r\nvar #Grid#_Width;\r\nvar #Grid#_Height;\r\nvar #Grid#_FormWidth;\r\nvar #Grid#_FormHeight;\r\nvar #Grid#_ColSpan;\r\n\r\nfunction #Grid#_HandleAdd() \r\n{\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tif( rvxFormKey == 0 ) {\r\n\t\tExt.Rvx.ShowError( rvx_locale.txt#Grid#SaveHeader );\r\n\t\treturn false;\r\n\t}\r\n\tExt.Rvx.PopupWindow( #Grid#_ModelUrl + '/add/parentid/' + rvxFormKey, #Grid#_Form";
echo "Width, #Grid#_FormHeight );\r\n}\r\n\r\nfunction #Grid#_HandleOpen() \r\n{\t\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\tif (selected) {\t\r\n\t\tExt.Rvx.PopupWindow( #Grid#_ModelUrl + '/view/parentid/' + rvxFormKey + '/id/' + selected.get('Id'), #Grid#_FormWidth, #Grid#_FormHeight );\r\n\t} else {\r\n\t\tExt.MessageBox.alert( rvx_locale.txtWarning, rvx_locale.txt";
echo "SelectRecord );\r\n\t}\r\n}\r\n\r\nfunction #Grid#_HandleDelete() \r\n{\t\r\n\trvxTriggerStore = #Grid#_DataStore;\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\tif (selected) {\t\r\n\t\tExt.MessageBox.confirm( rvx_locale.txtConfirm, rvx_locale.txtDeleteConfirm, #Grid#_ProcessDelete);\r\n\t} else {\r\n\t\tExt.MessageBox.alert( rvx_locale.txtWarning, rvx_locale.txtSelectRecord );\r\n\t}\r\n};\r\n\r\nfunction #Grid#_Proc";
echo "essDelete(btn)\r\n{\r\n\tif( btn == 'no' ) {\r\n\t\treturn false;\r\n\t};\r\n\tvar selected = #Grid#.getSelectionModel().getSelected();\r\n\t#Grid#.el.mask(rvx_locale.txtDelete, 'x-mask-loading');\r\n\tExt.Ajax.request({\r\n\t\turl: #Grid#_ModelUrl + '/delete/',\r\n\t\tparams: { task: \"delete\", id: selected.get('Id'), key: 'id'\t},\r\n\t\tfailure: function(response,options) {\r\n\t\t\t#Grid#.el.unmask();\r\n\t\t\tExt.Rvx.ShowError( response";
echo ".responseText );\r\n\t\t},                                      \r\n\t\tsuccess: function(response,options) {\r\n\t\t\t#Grid#.el.unmask();\r\n\t\t\ttry {\r\n\t\t\t\tExt.decode( response.responseText );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.ShowError( response.responseText );\r\n\t\t\t\treturn;\r\n\t\t\t}\r\n\t\t\t#Grid#.getStore().reload();\r\n\t\t}                                   \r\n\t});\r\n};\r\n\r\nfunction #Grid#_KeyDown( e )\r\n{\r\n\tvar keyPressed = e";
echo ".getKey();\r\n\tif( keyPressed == 45 ) {\r\n\t\t#Grid#_HandleAdd();\r\n\t}\r\n\telse if( keyPressed == 46 ) {\r\n\t\t#Grid#_HandleDelete();\r\n\t}\r\n}\r\n\r\nfunction #Grid#_HandleExportExcel() \r\n{\r\n\twindow.location = #Grid#_ModelUrl + '/export_excel/parentid/' + rvxFormKey;\r\n} \r\n\r\n#Grid#_ModelUrl   = '";
echo $grid->ModelUrl;
echo "';\r\n#Grid#_Width      = ";
echo $grid->Width;
echo ";\r\n#Grid#_Height     = ";
echo $grid->Height;
echo ";\r\n#Grid#_FormWidth  = ";
echo $grid->FormWidth;
echo ";\r\n#Grid#_FormHeight = ";
echo $grid->FormHeight;
echo ";\r\n#Grid#_ColSpan    = ";
echo $grid->ColSpan;
echo ";\r\n\t\t\r\nvar #Grid#_DataRecord = new Ext.data.Record.create([";
echo $grid->HtmlFields;
echo "]);\r\nvar #Grid#_DataReader = new Ext.data.JsonReader({root:'results', totalProperty:'total', id:'id'}, #Grid#_DataRecord);\r\nvar #Grid#_DataStore  = new Ext.data.Store({proxy: new Ext.data.HttpProxy({url:#Grid#_ModelUrl+'/fetch/', method:'POST'}),baseParams:{parentid:rvxFormKey}, reader:#Grid#_DataReader});\r\nvar #Grid#_ColModel   = new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(), ";
echo $grid->HtmlColumns;
echo "]);\r\nvar #Grid#_Toolbar    = new Ext.Toolbar({items:[\r\n\t\t{text: rvx_locale.txtAddLine, icon: 'img/new.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleAdd },\r\n\t\t{text: rvx_locale.txtOpenLine, icon: 'img/edit.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleOpen },\r\n\t\t{text: rvx_locale.txtDelLine, icon: 'img/delete.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleDelete },'-',\r\n\t\t{text: 'E";
echo "xport', icon: 'img/page_save.png', cls: 'x-btn-text-icon', handler: #Grid#_HandleExportExcel },\r\n]});\r\nvar #Grid#_SelModel = new Ext.grid.RowSelectionModel({singleSelect:true});\r\n\t\t\r\n#Grid# = new Ext.grid.GridPanel({ id:'#Grid#', title:'', height: #Grid#_Height, width: #Grid#_Width, \r\n\tcolModel:#Grid#_ColModel, \r\n\tstore:#Grid#_DataStore, \r\n\ttbar:#Grid#_Toolbar, \r\n\tselModel:#Grid#_SelModel, \r\n\tcols";
echo "pan:#Grid#_ColSpan,\r\n\tplugins:[";
echo $grid->HtmlPlugins;
echo "]\r\n});\r\n#Grid#.on('keydown', #Grid#_KeyDown, this, true);\r\n#Grid#.addListener( 'rowdblclick', #Grid#_HandleOpen );\r\n\r\n// pointer to datastore to be refreshed after line update\r\n//rvxTriggerStore = #Grid#_DataStore;\r\n\r\n// hack #Grid# refresh for update total controls \r\nfunction #Grid#_ReadFunction(response) \r\n{\t\r\n\tOnControlSuccess(response);\r\n\tvar json = response.responseText;\r\n\tvar o = eval(\"(\"+js";
echo "on+\")\");\r\n\tif(!o) {\r\n\t\tthrow {message: \"JsonReader.read: Json object not found\"};\r\n\t}\r\n\treturn #Grid#_DataReader.readRecords(o);\r\n}\r\n#Grid#_DataReader.read = #Grid#_ReadFunction;\r\n\r\n// retrieve the #Grid# data only if we have a head\r\nif( rvxFormKey>0 ) {\r\n\t#Grid#_DataStore.load({params: {start:0, limit:1000}});\r\n}\r\n\r\n\r\n";
?>
