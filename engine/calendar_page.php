<?php

include_once( RVXPATH."header.php" );
echo "  \r";
echo "<s";
echo "cript type=\"text/javascript\"> \rExt.onReady(function(){\rExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\rExt.QuickTips.init();\t\r\rvar rvxGridUrl   = '";
echo $this->GridUrl;
echo "';\rvar rvxGridTitle = '";
echo $this->GridTitle;
echo "';\rvar rvxGridWeek  = ";
echo $this->Week;
echo ";\rvar rvxGrid;\rvar rvxRecord  = Ext.data.Record.create([";
echo $grid_record;
echo "]);\t\rvar rvxReader  = new Ext.data.JsonReader({root:'results', totalProperty:'total',id:'id'}, rvxRecord);\rvar rvxModel   = new Ext.grid.ColumnModel([ ";
echo $grid_model;
echo "]);\rvar rvxParams  = ";
echo $this->HtmlParams;
echo ";\r\rfunction HandleAdd()\r{\r\tvar cell = rvxGrid.selModel.getSelectedCell();\r\tif( !cell )\r\t{\r\t\tExt.Rvx.ShowError( 'Please select a cell' ); \r\t\treturn false;\r\t}\r\t\r\tvar fld = rvxModel.getDataIndex(cell[1]);\r\tvar rec = rvxGrid.getStore().getAt(cell[0]);\r\tvar val = rec.get(fld);\r\tif( val ) {\r\t\tExt.Rvx.ShowError( 'Cell is already filled' );\r\t\treturn false;\r\t}\r\t\r\tvar day = rvxModel.getColumnHeader(cell[1])";
echo ";\r\tvar font = day.indexOf( 'font' );\r\tif( font > 0 ) {\r\t\tExt.Rvx.ShowError( 'Cell is blocked' );\r\t\treturn false;\r\t}\r\t\r\tvar spc = day.indexOf( ' ' );\r\tvar date = day.substr( 0, spc );\r\tvar hour = rec.get('Hour');\r\tvar rnd = Math.floor(Math.random()*1000);\r\twindow.open( rvxGridUrl+'add/date/'+date+'/time/'+hour, 'blank'+rnd );\r}\r\rfunction HandleDelete()\r{\r\tvar cell = rvxGrid.selModel.getSelectedCell";
echo "();\r\tif( !cell ){\r\t\tExt.Rvx.ShowError( 'Please select a cell' ); \r\t\treturn false;\r\t}\r\tvar fld = rvxModel.getDataIndex(cell[1]);\r\tvar rec = rvxGrid.getStore().getAt(cell[0]);\r\tvar val = rec.get(fld);\r\tif( val == '' ) {\r\t\treturn false;\r\t}\r\t\r\tvar x1 = val.indexOf( 'id/' );\r\tvar x2 = val.indexOf( '\">' );\r\tvar id = val.substr( x1+3, x2-x1-3 );\r\t\r\tExt.Ajax.request({\r\t\turl: rvxGridUrl+'delete/',\r\t\tparams";
echo ": {id:id, key:'id'},\r\t\tfailure: function(response,options) {\r\t\t\trvxGrid.el.unmask();\r\t\t\tExt.Rvx.ShowError( response.responseText );\r\t\t},                                      \r\t\tsuccess: function(response,options) {\r\t\t\trvxGrid.el.unmask();\r\t\t\trvxTriggerStore.reload();\r\t\t}\r\t});\r}\r\rfunction HandleOpen()\r{\r\talert( 'Please select a cell' );\r}\r\rfunction HandleToday()\r{\r\twindow.location = rvxGridUrl;\r}\r\r";
echo "function HandlePrevMonth()\r{\r\trvxGridWeek = rvxGridWeek - 1;\r\twindow.location = rvxGridUrl+'index/week/'+rvxGridWeek;\r}\r\rfunction HandleNextMonth()\r{\r\trvxGridWeek = rvxGridWeek+1;\r\twindow.location = rvxGridUrl+'index/week/'+rvxGridWeek;\r}\r\rvar rvxToolbar = new Ext.Toolbar({\r\titems: [{\r\t\ttext: rvx_locale.txtAdd,\r\t\ticon: 'img/new.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler : HandleAdd\r\t},{\r\t\ttext: rvx";
echo "_locale.txtOpen,\r\t\ticon: 'img/edit.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler : HandleOpen\r\t},{\r\t\ttext: rvx_locale.txtDelete,\r\t\ticon: 'img/delete.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler: HandleDelete\r\t}, '-', {\r\t\ttext: 'Previous Week',\r\t\ticon: 'img/previous.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler : HandlePrevMonth\r\t}, {\r\t\ttext: 'This Week',\r\t\ticon: 'img/calendar.png',\r\t\tcls: 'x-btn-text-icon',\r";
echo "\t\thandler : HandleToday\r\t},{\r\t\ttext: 'Next Week',\r\t\ticon: 'img/next.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler: HandleNextMonth\r\t}, '-', {\r\t\ttext: 'Select Doctor',\r\t\ticon: 'img/action.png',\r\t\tcls: 'x-btn-text-icon',\r\t\thandler: function() { \r\t\t\twindow.location = 'index.php?oftalmix/ofta_calendar/selectdoctor';\r\t\t}\r\t}]\r});\r\r\rvar rvxStore  = new Ext.data.GroupingStore({\r\tproxy: new Ext.data.HttpProxy(";
echo "{url:rvxGridUrl+'fetch/', method: 'POST'}),\r\treader: rvxReader,\r\tbaseParams: rvxParams\r});\r\rvar rvxGrid = new Ext.grid.EditorGridPanel({\r\ttitle: rvxGridTitle,\r\tregion: 'center',\r\tcolModel: rvxModel,\r\tstore: rvxStore,\r\ttbar: rvxToolbar,\r\tborder: false,\r\tframe: false,\r\tloadMask: true\r});\r\rrvxGrid.addListener( 'rowdblclick', HandleAdd);\r\rvar\trvxPanel = new Ext.Viewport({\r\tlayout:'border',\r\tborder:fal";
echo "se,\r\titems:[rvxGrid]\r});\r\rrvxStore.load();\rrvxTriggerStore = rvxStore;\r});\r\r\r// refresh grid after updating a record\rvar rvxTriggerStore;\rfunction TriggerRefresh() \r{\r\trvxTriggerStore.reload();\r}\r</script>\r\r</script> \r \r<body> \r</body> \r</html>";
?>
