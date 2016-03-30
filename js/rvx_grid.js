var Grid;
var Grid_ModelUrl;
var Grid_Width;
var Grid_Height;
var Grid_FormWidth;
var Grid_FormHeight;
var Grid_ColSpan;


function OnControlSuccess(a,b)
{
	rvxForm.el.unmask();
	try{
		var h=a.responseText;
		var d=Ext.decode(h)
	}
	catch(g){
		if(h=="")
		{
			return true
		}
		Ext.Rvx.ShowError(h);
		return true
	}
	if(!d.success&&d.message&&d.control)
	{
		var f=Ext.getCmp(d.control);
		if(f)
		{
			f.focus(true);
			f.markInvalid(d.message)
		}
		return false
	}
	if(d.controls&&d.captions)
	{
		for(var c=0;c<d.controls.length;c++)
		{
			var f=Ext.getCmp(d.controls[c]);
			if(f)
			{
				f.setValue(d.captions[c]);
				f.value=d.captions[c];
				f.oldvalue=d.captions[c]
			}
		}
	}
	return true
}

function OnControlFailure(a,b)
{
	Ext.Rvx.ShowError(a.responseText)
}


function Grid_HandleSaveHeader(){
        rvxSaveHeader=true;
        HandleSave()
}

function Grid_HandleAdd(){
        if(rvxFormKey==0){
                Ext.Rvx.ShowError(rvx_locale.txtGridSaveHeader);
                return false
        }
        if(rvxEditMode==false){
                Ext.Rvx.ShowError(rvx_locale.txtEditModeFalse);
                return false
        }
        Ext.Rvx.PopupWindow(Grid_ModelUrl+"/add/parentid/"+rvxFormKey,Grid_FormWidth,Grid_FormHeight)}

function Grid_HandleOpen(){var a=Grid.getSelectionModel().getSelected();if(a){Ext.Rvx.PopupWindow(Grid_ModelUrl+"/view/parentid/"+rvxFormKey+"/id/"+a.get("Id"),Grid_FormWidth,Grid_FormHeight)}else{Ext.MessageBox.alert(rvx_locale.txtWarning,rvx_locale.txtSelectRecord)}}
function Grid_HandleDelete(){if(rvxEditMode==false){Ext.Rvx.ShowError(rvx_locale.txtEditModeFalse);return false}var a=Grid.getSelectionModel().getSelected();if(a){Ext.MessageBox.confirm(rvx_locale.txtConfirm,rvx_locale.txtDeleteConfirm,Grid_ProcessDelete)}else{Ext.MessageBox.alert(rvx_locale.txtWarning,rvx_locale.txtSelectRecord)}}
function Grid_ProcessDelete(a){if(a=="no"){return false}var b=Grid.getSelectionModel().getSelected();Grid.el.mask(rvx_locale.txtDelete,"x-mask-loading");Ext.Ajax.request({url:Grid_ModelUrl+"/delete/",params:{task:"delete",id:b.get("Id"),key:"id"},failure:function(c,d){Grid.el.unmask();Ext.Rvx.ShowError(c.responseText)},success:function(c,d){Grid.el.unmask();try{Ext.decode(c.responseText)}catch(f){Ext.Rvx.ShowError(c.responseText);return}Grid.getStore().reload()}})}
function Grid_ValidateEdit(a){
        var b=Grid.getStore().getAt(a.row);
        if(!b){Ext.Rvx.ShowError(rvx_locale.txtSelectRecord);
                return
        }
        Grid.el.mask(rvx_locale.txtSave,"x-mask-loading");
        Ext.Ajax.request({
                url:Grid_ModelUrl+"/gridsave",
                params:{gridkey:b.data.Id,gridrow:a.row,gridfield:a.field,gridvalue:a.value,parentid:Ext.getCmp("Id").getValue()},
                failure:function(c,d){Grid.el.unmask();Ext.Rvx.ShowError(c.responseText);return false},success:function(c,d){Grid.el.unmask();var g={};try{g=Ext.decode(c.responseText)}catch(h){Ext.Rvx.ShowError(c.responseText);return false}var j=Grid.getStore().getAt(h.row);j.commit();if(g.fields&&g.values){var j=Grid.getStore().getAt(g.row);for(var f=0;f<g.fields.length;f++){j.set(g.fields[f],g.values[f])}j.commit()}ControlSuccess(c,d)}})}

function test(a){Ext.getCmp('Grid').getStore().getAt(a.row).b.data.Id};

function Grid_Edit(a){
        var b=Grid.getStore().getAt(a.row);
        if(!b){Ext.Rvx.ShowError(rvx_locale.txtSelectRecord);
                return
        }
        Ext.Ajax.request({
                url:Grid_ModelUrl+"/gridlookup",
                params:{lookupname:"Name", lookupkey:"Id", query:b.data.Id, gridkey:b.data.Id,gridrow:a.row,gridfield:a.field,gridvalue:a.value,parentid:Ext.getCmp("Id").getValue()},
                failure:function(c,d){
                        Ext.Rvx.ShowError(c.responseText);return false
                },
                success:function(c,d){
                        var g={};
                        try{
                                g=Ext.decode(c.responseText);
                        }catch(h){
                                Ext.Rvx.ShowError(c.responseText);
                                return false
                        }

                        var j=Grid.getStore().getAt(a.row);

                        if(g.fields&&g.values){
                                var j=Grid.getStore().getAt(g.row);
                                for(var f=0;f<g.fields.length;f++){
                                        j.set(g.fields[f],g.values[f])
                                }
                                j.commit()}
                        OnControlSuccess(c,d)}
        })}
function Grid_KeyDown(b){var a=b.getKey();if(a==45){Grid_HandleAdd()}else{if(a==46){Grid_HandleDelete()}}}function Grid_HandleExportExcel(){window.location=Grid_ModelUrl+"/export_excel/parentid/"+rvxFormKey}function Grid_HandleImportExcel(){window.location=Grid_ModelUrl+"/import_excel/parentid/"+rvxFormKey};