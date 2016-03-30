<?php

include( "header.php" );
echo "<s";
echo "cript type=\"text/javascript\">\r\n\tvar baseurl = '";
echo base_url( );
echo "';\r\n\tvar rvxCompanyList = [ ";
echo $page_companylist;
echo " ];\r\n</script>\r\n\t\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nExt.onReady(function(){\r\n\r\nExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\r\nExt.QuickTips.init();\t\r\n\t\r\nvar rvxCompanyStore = new Ext.data.SimpleStore({\r\n\tfields: ['name'],\r\n\tdata : rvxCompanyList\r\n});\r\n";
if ( $page_companylist )
{
    echo "var rvxCompany = new Ext.form.ComboBox({\r\n\tstore: rvxCompanyStore,\r\n\tfieldLabel: rvx_locale.txtCompany,\r\n\tname: 'Connection',\t\r\n\tdisplayField: 'name',\r\n\tmode: 'local',\r\n\ttriggerAction: 'all',\r\n\temptyText: '',\r\n\ttypeAhead: true,\r\n\tselectOnFocus: true,\r\n\tallowBlank: false,\r\n\twidth: 270,\r\n\tvalue: ''\r\n});\r\n";
}
else
{
    echo "var rvxCompany = new Ext.form.TextField({\r\n\tfieldLabel: rvx_locale.txtCompany, \r\n\tname: 'Connection', \r\n\twidth: 270, \r\n\tvalue: 'Company'\r\n});\r\n";
}
echo "\t\r\n\t\r\n\r\nvar rvxForm = new Ext.FormPanel({\r\n\ttitle: rvx_locale.txtConnections,\r\n\turl: baseurl+'connect/submit',\r\n\tbodyStyle:'padding:5px 5px 0',\r\n\tdefaultType: 'textfield',\r\n\tlabelWidth: 150,\r\n\twidth: 500,\t\r\n\tframe: true,\r\n\titems: [ rvxCompany,\r\n\t\t{ fieldLabel: rvx_locale.txtDbHostname, name: 'Hostname', width: 270, value: 'localhost' },\r\n\t\t{ fieldLabel: rvx_locale.txtDbUsername, name: 'Username', ";
echo "width: 270, value: 'root' },\r\n\t\t{ fieldLabel: rvx_locale.txtDbPassword, name: 'Password', width: 270, inputType: 'password' },\r\n\t\t{ fieldLabel: rvx_locale.txtDbDatabase, name: 'Database', width: 270, value: 'company' }\r\n\t]\r\n});\r\nvar btnSubmit = new Ext.form.Hidden({id:'Submit', name:'Submit', value:''})\r\nrvxForm.add( btnSubmit );\r\n\r\nfunction HandleSubmit( btn )\r\n{\r\n\tbtnSubmit.setValue( btn.name );";
echo "\r\n\trvxForm.getForm().getEl().dom.action = rvxForm.url;\r\n\trvxForm.getForm().getEl().dom.submit();\r\n};\r\n\r\nrvxForm.addButton({name:'Create', text:rvx_locale.txtDbCreate}, HandleSubmit );\r\n";
if ( $page_companylist )
{
    echo "rvxForm.addButton({name:'Select', text:rvx_locale.txtDbSelect}, HandleSubmit );\r\nrvxForm.addButton({name:'Remove', text:rvx_locale.txtDbRemove}, HandleSubmit );\r\n";
}
echo "rvxForm.render('divForm');\t\r\n\r\nExt.get( 'divContainer').center();\r\nExt.get( 'divContainer').setTop(100);\r\n\r\n\r\n});\r\n</script> \r\n<div id=\"divContainer\" style=\"width: 400px;\">\r\n<div id=\"divForm\"></div>\r\n</div>\r\n</body>\r\n";
?>
