function HandleNew()
{
	if(rvxEditMode==true)
	{
		Ext.Rvx.ShowError(rvx_locale.txtEditModeReady);
		return false
	}

var a = Ext.getCmp("ParentId");

if(a)
{
	window.location=rvxModelUrl+"add/parentid/"+a.getValue()
}
else{
	window.location=rvxModelUrl+"add"}
}

function BlockControls(c){
	var a=rvxFormControls;
	for(var b=0;b<a.length;b++)
	{
		Ext.Rvx.BlockEdit(a[b],c)
	}
	rvxEditMode =! c
}

function HandleEdit()
{
	if(rvxEditMode==true)
	{
		Ext.Rvx.ShowError(rvx_locale.txtEditModeReady);
		return false
	}
	rvxForm.el.mask(rvx_locale.txtEdit,"x-mask-loading");
	FormAjaxSubmit(rvxModelUrl+"checkedit/id/"+rvxFormKey,OnEditSuccess,OnSaveFailure)
}

function OnEditSuccess(b,c)
{
	rvxForm.el.unmask();

	try{
		var d=Ext.decode(b.responseText)
	}
	catch(f){
		Ext.Rvx.ShowError(b.responseText);
		return false
	}
	var a=Ext.getCmp("ParentId");

	if(a)
	{
		window.location=rvxModelUrl+"edit/id/"+rvxFormKey+"/parentid/"+a.getValue()
	}else{
		window.location=rvxModelUrl+"edit/id/"+rvxFormKey
	}
}

function HandlePrint(e)
{
	if(rvxEditMode==true)
	{
		Ext.Rvx.ShowError(rvx_locale.txtEditModeReady);
		return false
	}
	if(rvxFormReports.length==0)
	{
		Ext.Rvx.ShowError(rvx_locale.txtCannotPrint);
		return false
	}
	var d=new Ext.data.SimpleStore({fields:["Name"],data:rvxFormReports});
	var c=new Ext.form.ComboBox({store:d,valueField:"Name",displayField:"Name",fieldLabel:rvx_locale.txtReport,name:"Report",id:"Report",value:rvxFormReports[0],width:200,mode:"local",triggerAction:"all",typeAhead:true,emptyText:"",selectOnFocus:true});
	var a=new Ext.FormPanel({id:"printfrm",title:"",frame:true,id:'print',items:c});
	var b=new Ext.Window({id:"printwin",title:rvx_locale.txtPreview,layout:"fit",width:350,height:150,border:true,bodyStyle:"padding:5px 5px 5px 5px",modal:true,items:a});

	c.selectedIndex=0;
	a.addButton({name:"Display",text:rvx_locale.txtDisplay},
				function()
				{
                                        if( rvxConfirmPrint == 1 )
                                        {
                                                var msg = 'Confirm print'

                                                if( rvxControllerClass == 'sale_invoice' )
                                                {
                                                        msg = 'You want to print an invoice from RVX. Please make sure the invoice is not generated in the WMS.';
                                                }

                                                Ext.Msg.confirm('Print?', msg, function(btn, text)
                                                {
                                                        if (btn == 'yes')
                                                        {
                                                                window.open(rvxModelUrl+"printer/id/"+rvxFormKey+"/report/"+(c.selectedIndex+1),"printaction");
                                                                b.close()
                                                        }
                                                });
                                        }else{
                                                window.open(rvxModelUrl+"printer/id/"+rvxFormKey+"/report/"+(c.selectedIndex+1),"printaction");
                                                b.close()
                                        }

				});

	a.addButton({name:"Cancel",text:rvx_locale.txtCancel},function(){b.close()});
	b.show()
}


function FormAjaxSubmit(c,b,e)
{
	var a=rvxFormControls;
	var f=new Array();

	for(var d=0;d<a.length;d++)
	{
		if(a[d].fieldClass==undefined)
		{continue}

		f[a[d].name]=a[d].getRawValue()
	}
	Ext.Ajax.request
	({
		url:c,
		params:f,
		success:b,
		failure:e
	})
}

function HandleSave()
{
	if(rvxEditMode==false)
	{
		Ext.Rvx.ShowError(rvx_locale.txtEditModeFalse);
		return false
	}

	if(!rvxForm.getForm().isValid())
	{
		Ext.Rvx.ShowError(rvx_locale.txtCheckInput);
		return false
	}
	rvxForm.el.mask(rvx_locale.txtSave,"x-mask-loading");

	FormAjaxSubmit(rvxForm.url,OnSaveSuccess,OnSaveFailure)
}

function OnSaveSuccess(b,c)
{
	rvxForm.el.unmask();
	try{
		var h=b.responseText;
		var d=Ext.decode(h)
	}
	catch(f)
	{
		if(h=="")
		{
			return true
		}
		Ext.Rvx.ShowError(h);
		return false
	}
	var a=Ext.getCmp("Id");

	if(a&&d.id)
	{
		a.setValue(d.id);
		rvxFormKey=d.id
	}

	var g=Ext.getCmp("Number");

	if(g&&d.nr)
	{
		g.setValue(d.nr)
	}
	Ext.Rvx.ShowInfo(rvx_locale.txtInfo,rvx_locale.txtSaveComplete);

	if(!rvxSaveHeader)
	{
		BlockControls(true)
	}
	else{
		rvxSaveHeader=false
	}

	if(window.opener&&!window.opener.closed)
	{
		window.opener.TriggerRefresh()
	}
	return true
}

function OnSaveFailure(a,b)
{
	rvxForm.el.unmask();
	Ext.Rvx.ShowError(a.responseText);
	return false
}

function HandleSaveClose()
{
	if(rvxEditMode==true)
	{
		HandleSave()
	}
	else{
		if(window.opener&&!window.opener.closed)
		{
			window.opener.TriggerRefresh()
		}
		window.close()
	}
}

function ConfirmSave(a)
{
	if(a=="no")
	{
		if(window.opener)
		{
			window.close()
		}
		else{
			window.location=rvxModelUrl
		}
	}
	else{
		HandleSaveClose()
	}
}

function HandleClose()
{
	if(window.opener)
	{
		window.close()
	}
	else{
		window.close()
	}
}

function ControlFocus(a)
{
	a.oldvalue=a.getRawValue()
}

function ControlValidate(d)
{
	if(d.oldvalue==d.getRawValue())
	{
		return
	}
	var a=rvxFormControls;
	var c=new Array();

	for(var b=0;b<a.length;b++)
	{
		c[a[b].name]="#"+a[b].getRawValue()
	}
	rvxFocusedCtrl=d.name;
	c.control=d.name;
	c.value=d.getRawValue();
	Ext.Ajax.request({
		url:rvxModelUrl+"ctrlvalidate/",
		params:c,
		success:OnControlSuccess,
		failure:OnControlFailure
	})
}

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

function HandleTrigger(a)
{
	rvxForm.el.unmask();
	if(rvxEditMode==false)
	{
		Ext.Rvx.ShowError(rvx_locale.txtEditModeFalse);
		return false
	}
	Ext.Rvx.PopupWindow(a,800,500);
	return true
}

function ControlValidate2(e)
{
	if(e.value==""){return}

	var b=Ext.getCmp(e.name);

	if(b==false){return}

	if(b.isblur==undefined){return}

	var a=rvxFormControls;

	var d=new Array();

	for(var c=0;c<a.length;c++)
	{
		d[a[c].name]="#"+a[c].getRawValue()
	}
	d.control=e.name;
	d.value=e.value;
	Ext.Ajax.request({
		url:rvxModelUrl+"ctrlvalidate/",
		params:d,
		success:OnControlSuccess,
		failure:OnControlFailure
	})
}

rvxControlValidate=ControlValidate2;


function getSerializedFormElements()
{
	var serializedFormElements = new Array();

	for(var i=0; i < rvxFormControls.length; i++) {
		if(rvxFormControls[i].fieldClass == undefined) {
			continue
		}
		serializedFormElements[rvxFormControls[i].name] = rvxFormControls[i].getRawValue();
	}

	return serializedFormElements;
}


function HandlePost()
{
	var rvxActionPostUrl;
	var serializedFormElements = getSerializedFormElements();
	var date     = Ext.getCmp('Date').value;
	var isPosted = Ext.getCmp('IsPosted').value;

	if(rvxEditMode==true)  {
		Ext.Rvx.ShowError(rvx_locale.txtSaveFirst);
		return false
	}
	rvxForm.el.mask(rvx_locale.txtSave,"x-mask-loading");

	if(date && isPosted)  {
		rvxActionPostUrl = "index.php?admin/util/action_checkpost/id/"+rvxFormKey+"/date/"+date;
		Ext.Ajax.request({
			url     : rvxActionPostUrl,
			method  : 'POST',
			params  : serializedFormElements,
			success : function (response, options) {
					try {
						jsonResponse = Ext.decode(response.responseText);
					} catch (err) {
						return OnSaveFailure(response, options);
					}

					FormAjaxSubmit(rvxModelUrl+"action_post/id/"+rvxFormKey,OnPostSuccess,OnSaveFailure)
				},
			failure : OnSaveFailure
		})
	} else {
		FormAjaxSubmit(rvxModelUrl+"action_post/id/"+rvxFormKey,OnPostSuccess,OnSaveFailure)
	}
}


function HandleUnpost()
{
	var rvxActionPostUrl;
	var serializedFormElements = getSerializedFormElements();
	var date     = Ext.getCmp('Date').value;
	var isPosted = Ext.getCmp('IsPosted').value;

	if(rvxEditMode==true) {
		Ext.Rvx.ShowError(rvx_locale.txtSaveFirst);
		return false
	}
	rvxForm.el.mask(rvx_locale.txtSave,"x-mask-loading");

	if(date && isPosted) {
		rvxActionPostUrl = "index.php?admin/util/action_checkpost/id/"+rvxFormKey+"/date/"+date;
		Ext.Ajax.request({
			url     : rvxActionPostUrl,
			method  : 'POST',
			params  : serializedFormElements,
			success : function (response, options) {
					try {
						jsonResponse = Ext.decode(response.responseText);
					} catch (err) {
						return OnSaveFailure(response, options);
					}

					FormAjaxSubmit(rvxModelUrl+"action_unpost/id/"+rvxFormKey,OnPostSuccess,OnSaveFailure);
				},
			failure : OnSaveFailure
		});
	} else {
		FormAjaxSubmit(rvxModelUrl+"action_unpost/id/"+rvxFormKey,OnPostSuccess,OnSaveFailure)
	}
}


function OnPostSuccess(b,c)
{
	rvxForm.el.unmask();

	try{
		var d=Ext.decode(b.responseText)
	}
	catch(f)
	{
		Ext.Rvx.ShowError(b.responseText);
		return false
	}
	var a=Ext.getCmp("IsPosted");

	if(a)
	{
		a.setValue(1-a.getValue())
	}

	Ext.Rvx.ShowInfo(rvx_locale.txtInfo,"Success");

	if(window.opener&&!window.opener.closed)
	{
		window.opener.TriggerRefresh()
	}
}

function HandleAction(b,a)
{
	if(rvxFormKey==0)
	{
		return Ext.Rvx.ShowError(rvx_locale.txtSaveFirst)
	}
	win="win"+Math.round(Math.random()*100000);

	if(a)
	{
		window.open(b+"/id/"+rvxFormKey,win)
	}
	else{
		window.location=b+"/id/"+rvxFormKey
	}
}

function HandleInfoTime()
{
	if(rvxFormKey==0)
	{
		return Ext.Rvx.ShowError(rvx_locale.txtSaveFirst)
	}
	Ext.Ajax.request({
		url:rvxModelUrl+"audit/id/"+rvxFormKey,
		params:"x=1",
		success:HandleInfoTimeSuccess,
		failure:OnSaveFailure
	})
}

function HandleInfoNote()
{
	win="win"+Math.round(Math.random()*100000);
	window.open(rvxModelUrl+"infonote/id/"+rvxFormKey,win)
}

function HandleInspector()
{
	win="win"+Math.round(Math.random()*100000);
	window.open(rvxModelUrl+"inspector/id/"+rvxFormKey,win)
}

function HandleInfoTimeSuccess(a,b)
{
	Ext.Msg.show({title:rvx_locale.txtInfo,
				  msg:a.responseText,
				  buttons:Ext.Msg.OK,
				  width:400,
				  minWidth:400,
				  icon:Ext.MessageBox.INFO
				  })
}

var rvxActions=new Ext.menu.Menu({items:rvxActionMenus});

var rvxToolbar=new Ext.Toolbar({items:[{text:rvx_locale.txtAdd,icon:"img/new.png",cls:"x-btn-text-icon",handler:HandleNew},
                {text:rvx_locale.txtEdit,icon:"img/edit.png",cls:"x-btn-text-icon",handler:HandleEdit},
                {text:rvx_locale.txtSave,icon:"img/save.png",cls:"x-btn-text-icon",handler:HandleSave},"-",
                //{text:rvx_locale.txtPrint,icon:"img/print.png",cls:"x-btn-text-icon",handler:HandlePrint},
                {text:rvx_locale.txtActions,icon:"img/action.png",cls:"x-btn-text-icon",menu:rvxActions},
                //{text:rvx_locale.txtInfo,icon:"img/time.png",cls:"x-btn-text-icon", menu:[{text:rvx_locale.txtNotes,handler:HandleInfoNote},{text:"Inspector",handler:HandleInspector},{text:rvx_locale.txtInfo,handler:HandleInfoTime}]}
                ]});


var globalKeyMap=new Ext.KeyMap(document);
globalKeyMap.accessKey=function(a,d,c)
{
	var b=function(g,f)
	{
		f.preventDefault();
		d.call(c||this,g,f)
	};
	this.on(a,b,c)
};


/*
globalKeyMap.accessKey({key:"a",alt:true},HandleNew,this);
globalKeyMap.accessKey({key:"e",alt:true},HandleEdit,this);
globalKeyMap.accessKey({key:"s",alt:true},HandleSave,this);
globalKeyMap.accessKey({key:"p",alt:true},HandlePrint,this);
globalKeyMap.accessKey({key:"t",alt:true},function(){rvxToolbar.focus()},this);
*/