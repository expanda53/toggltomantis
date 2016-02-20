
$.ajaxSetup({ cache: false });
function ajaxCall( func, d,asyn,fn) {
  var res;
  console.debug('ajax:'+func+' data:'+JSON.stringify(d));
  $.ajax({
        type: "POST",
		url: "index.php/" + func, 
		
        data: d,
		async: asyn,
        dataType: "json",
        success: function(data) {
			//console.debug('ajax success:'+fn+' data:'+JSON.stringify(data));
			res=data; 
			if (fn) { 
				f=fn.split(".");
				if (f.length>1) {
					var myFunc = window[f[0]][f[1]];
				}
				else {
					var myFunc = window[fn];
				}
				//alert(fn+' '+typeof myFunc);
				if(typeof myFunc === 'function') {
					//console.debug('ajax success,start func:'+fn+' data:'+JSON.stringify(data));
					myFunc(data);
				}
			  
			}
        },
        error: function(data) {
            console.debug('ajax error:'+func+' data:'+JSON.stringify(data));
			alert('ajax error:'+func+' data:'+JSON.stringify(data));
			res='ERROR';
        }
  });

  return res;
}

function showMain(){
	panelName='main';
	$.get( "views/"+panelName+".tpl", function( data ) { 
			tpl = data; 
			$('#divcontent').html(tpl);		
			$('#divcontent').show();
			$('#togglDate').datepicker({'dateFormat':"yy-mm-dd"});
			$('#divmantisuj').addClass('tabactive');
			$('#divmantisregi').addClass('tabinactive');

			$('#togglUsers').hide();
			$('#togglProjects').hide();
			$('#mantisUsers').hide();
			$('#mantisPartners').hide();
			$('#mantisMonths').hide();
			fn='togglUsers';
			ajaxCall(fn,{},true, fn);
			fn='togglProjects';
			ajaxCall(fn,{},true, fn);
			
			fn='mantisUsers';
			ajaxCall(fn,{},true, fn);
			fn='mantisPartners';
			ajaxCall(fn,{},true, fn);
			fn='mantisMonths';
			ajaxCall(fn,{},true, fn);
			
			$('#togglfilter').click(function(){
				fn='togglTasks';
				uid = $('#togglUsers').val();
				pid = $('#togglProjects').val();
				until = $('#togglDate').val();
				ajaxCall(fn,{'userId':uid,'projectId':pid,'until':until},true, fn);
			});
			$('#divmantisuj').click(function(){
				$.get( "views/mantis_uj.tpl", function( data ) { 
					tpl = data; 
					$('.divtabcontent').html(tpl);
					$('#divmantisuj').addClass('tabactive');
					$('#divmantisregi').addClass('tabinactive');
					$('#divmantisregi').removeClass('tabactive');
					$('#divmantisuj').removeClass('tabinactive');
					$('#divmantiskulon').click(function(){
						$('#spanDateNeeded').hide();
						$('#divmantiskulon').addClass('tabactive');
						$('#divmantiskulon').removeClass('tabinactive');
						$('#divmantisegybe').addClass('tabinactive');
						$('#divmantisegybe').removeClass('tabactive');
						$('#divsubcontent2').hide();
						$('#divsubcontent1').show();
					});
					$('#divmantisegybe').click(function(){
						$('#spanDateNeeded').show();
						$('#divmantisegybe').addClass('tabactive');
						$('#divmantisegybe').removeClass('tabinactive');
						$('#divmantiskulon').addClass('tabinactive');
						$('#divmantiskulon').removeClass('tabactive');
						$('#divsubcontent1').hide();
						$('#divsubcontent2').show();
					});				
					$('#bkulonstart').click(function(){
						mantis_newbug();
					});
					$('#begybestart').click(function(){
						mantis_newbug_with_note();
					});
					$('#divmantiskulon').trigger('click');
				});
			
				
			});
			
			$('#divmantisregi').click(function(){
				$.get( "views/mantis_old.tpl", function( data ) { 
					tpl = data; 
					$('.divtabcontent').html(tpl);
					$('#spanDateNeeded').show();
					$('#divmantisregi').addClass('tabactive');
					$('#divmantisuj').addClass('tabinactive');
					$('#divmantisuj').removeClass('tabactive');
					$('#divmantisregi').removeClass('tabinactive');
					$('#bmantisquery').click(function(){
						mantisQuery();
					});					
				});
			
			});
			$('#divmantisuj').trigger('click');

	})
}

function mantis_newbug (){
			
				fn='Insert';
				uid = $('#mantisUsers').val();
				pid = $('#mantisPartners').val();
				month = $('#mantisMonths').val();

				$('#divtogglfilterresult input:checkbox:checked').each(function(){
					taskid = $(this).attr('taskid');
					durationms = $(this).attr('durationms');
					start = $(this).attr('start');
					taskdesc = $('#desc'+taskid).html();
					togglPar = {'taskId':taskid};
					mantisPar = {'uid':uid,'pid':pid,'desc':taskdesc,'durms':durationms,'start':start,'month':month,'note':''};
					ajaxCall(fn,{'togglPar':togglPar,'mantisPar':mantisPar},true, fn);
				});

				
			
}

function mantis_newbug_with_note (){
			
				fn='InsertWithNote';
				uid = $('#mantisUsers').val();
				pid = $('#mantisPartners').val();
				month = $('#mantisMonths').val();
				mantisDesc = $('#mantisDesc').val();
				start="";
				durationms = 0;
				note = "";
				taskIds ="";
				noteToggl="";
				$('#divtogglfilterresult input:checkbox:checked').each(function(){
					taskid = $(this).attr('taskid');
					curdur = $(this).attr('durationms');
					durationms = durationms + parseInt(curdur);
					s = $(this).attr('start');
					if (start=="") start = s;
					//note += s+": "+$('#desc'+taskid).html()+" ("+mstoHM(curdur)+")\n";
					if ($('#cbDateNeeded').prop('checked')) {
						note += s+": "+$('#desc'+taskid).html()+"\n";
					}
					else {
						note += $('#desc'+taskid).html()+"\n";
					}

					noteToggl += $('#desc'+taskid).html()+"\n";
					taskIds += taskid+ "\n";
				});
				mantisPar = {'uid':uid,'pid':pid,'desc':mantisDesc,'durms':durationms,'start':start,'month':month,'note':note};
				togglPar = {'taskIds':taskIds,'togglDesc':noteToggl};
				ajaxCall(fn,{'togglPar':togglPar,'mantisPar':mantisPar},true, fn);
}
function mantisUpdate(mantisId,mantisHM){
				fn='UpdateWithNote';
				uid = $('#mantisUsers').val();
				pid = $('#mantisPartners').val();
				month = $('#mantisMonths').val();				
				start="";
				durationms = 0;
				note = "";
				taskIds ="";
				noteToggl="";
				$('#divtogglfilterresult input:checkbox:checked').each(function(){
					taskid = $(this).attr('taskid');
					curdur = $(this).attr('durationms');
					durationms = durationms + parseInt(curdur);
					s = $(this).attr('start');
					if (start=="") start = s;
					//note += s+": "+$('#desc'+taskid).html()+" ("+mstoHM(curdur)+")\n";
					if ($('#cbDateNeeded').prop('checked')) {
						note += s+": "+$('#desc'+taskid).html()+"\n";
					}
					else {
						note += $('#desc'+taskid).html()+"\n";
					}
					noteToggl += $('#desc'+taskid).html()+"\n";
					taskIds += taskid+ "\n";
				});
				mantisPar = {'uid':uid,'pid':pid,'id':mantisId,'mantishm':mantisHM,'durms':durationms,'start':start,'month':month,'note':note};
				togglPar = {'taskIds':taskIds,'togglDesc':noteToggl};
				ajaxCall(fn,{'togglPar':togglPar,'mantisPar':mantisPar},true, fn);
	
}
function Insert(result){
	/*alert(JSON.stringify(result));*/
	$( "#togglfilter" ).trigger( "click" );
}
function InsertWithNote(result){
	/*alert(JSON.stringify(result));*/
	$( "#togglfilter" ).trigger( "click" );
}
function UpdateWithNote(result){
	/*alert(JSON.stringify(result));*/
	$( "#togglfilter" ).trigger( "click" );
}


function togglUsers(result){
	r=result;
	selectStr = "";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		selectStr += "<option value='"+res.id+"'>"+res.fullname+"</option>";
		//alert(JSON.stringify(res));
	}
	$('#togglUsers').append(selectStr);
	sortSelect('togglUsers');
	$('#togglUsers').show();
	
}

function togglProjects(result){
	r=result;
	selectStr="";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		selectStr += "<option value='"+res.id+"'>"+res.name+"</option>";
		//alert(JSON.stringify(res));
	}
	$('#togglProjects').append(selectStr);
	sortSelect('togglProjects');
	$('#togglProjects').show();
	
	$('#togglProjects').bind('change',function () {
		togglPId =  $(this).val();
		fn='projectAssignCheck';
		ajaxCall(fn,{'togglPId':togglPId,'mantisPId':-1},true, fn+'Toggl');
	})
	$('#togglProjects').trigger('change');
	
	
}
function mstoHM(ms) {
		hours = Math.trunc(ms / 1000 / 3600) ;
		minutes = Math.trunc((ms - (hours * 1000 * 3600)) / 1000 / 60);
		hours = ("00" + hours).slice(-2);
		minutes = ("00" + minutes).slice(-2);
		return hours+':'+minutes;

}
function sortSelect(id){
	var my_options = $("#"+id+" option");
	//var selected = $("#togglProjects").val(); /* preserving original selection, step 1 */

	my_options.sort(function(a,b) {
		if (a.text > b.text) return 1;
		else if (a.text < b.text) return -1;
		else return 0
	})

	$("#"+id).empty().append( my_options );
	//$("#togglProjects").val(selected); /* preserving original selection, step 2 */
	
}
function togglTasks(result){
	r=result;
	selectStr="";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		durstr = mstoHM(res.dur);
		selectStr += "<div class='toggltask' ttaskid='"+res.id+"'>";
		selectStr += "<input class=togglcb start='"+res.start.substring(0,10)+"' durationms='"+res.dur+"' taskid='"+res.id+"' type=checkbox id=cb"+res.id+">";
		selectStr += "<span>"+res.start.substring(0,10)+": </span>";
		selectStr += "<span id=desc"+res.id+">"+res.description+"</span>";
		selectStr += "<span> ("+durstr+")</span>";
		selectStr += "</div>";
		//alert(JSON.stringify(res));
	}
	$('#divtogglfilterresult').html(selectStr);
	$('.toggltask').click(function(){
		id = $(this).attr('ttaskid');
		$('#cb'+id).prop("checked", !$('#cb'+id).prop("checked"));
	});	
	$('.togglcb').click(function(event){
		event.stopPropagation();
		//event.preventDefault();
	});	
	
}

function mantisPartners(result){
	r=result;
	selectStr = "";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		selectStr += "<option value='"+res.id+"'>"+res.name+"</option>";
		//alert(JSON.stringify(res));
	}
	$('#mantisPartners').append(selectStr);
	$('#mantisPartners').show();
	$('#mantisPartners').bind('change',function () {
		togglPId =  $('#togglProjects').val();
		mantisPId = $(this).val();
		fn='projectAssignCheck';
		ajaxCall(fn,{'togglPId':togglPId,'mantisPId':mantisPId},true, fn);
	})
}

function projectAssignCheck (result) {
	if (result=='') {
		var r = confirm("Toggl és Mantis partner összerendelés. Folytatod?");
		if (r == true) {
			togglPId =  $('#togglProjects').val();
			mantisPId = $('#mantisPartners').val();
			fn='projectAssign';
			ajaxCall(fn,{'togglPId':togglPId,'mantisPId':mantisPId},true, fn);
		};
		
	}
}
function projectAssignCheckToggl (result) {
	if (result!='') {
			$('#mantisPartners').val(result[0].rcount);
	}
}

function projectAssign(result) {
	//alert(JSON.stringify(result));
}

function mantisUsers(result){
	r=result;
	selectStr = "";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		selectStr += "<option value='"+res.id+"'>"+res.username+"</option>";
		//alert(JSON.stringify(res));
	}
	$('#mantisUsers').append(selectStr);
	$('#mantisUsers').show();
}
function mantisMonths(result){
	r=result;
	selectStr = "";
	for (var i = 0;i < r.length;i++){
		res = r[i];
		selectStr += "<option value='"+res.version+"'>"+res.version+"</option>";
		//alert(JSON.stringify(res));
	}
	$('#mantisMonths').append(selectStr);
	$('#mantisMonths').show();

}

function mantisQuery(){
	uid = $('#mantisUsers').val();
	pid = $('#mantisPartners').val();
	fn = 'mantisQueryResult';
	ajaxCall(fn,{'uid':uid,'pid':pid},true, fn);
	
}
function mantisQueryResult(result){
	selectStr="";
	for (var i = 0;i < result.length;i++){
		res = result[i];
		selectStr += "<div class='mantistask' mantishm='"+res.platform+"' mantisid="+res.id+">";
		selectStr += "<span>["+res.id+"] "+res.last_updated+" : "+res.summary;
		if (res.fixed_in_version!="") selectStr += " ("+res.fixed_in_version+") ";
		selectStr +=" </span>"
		selectStr += "</div>";
	}
	$('#divmantisresult').html(selectStr);
	$('.mantistask').click(function(){
		mantisId = $(this).attr('mantisid');
		mantisHM = $(this).attr('mantishm');
		mantisDesc = $(this).text();
		var r = confirm("felírás ide: "+mantisDesc+". Folytatod?");
		if (r == true) {
			mantisUpdate(mantisId,mantisHM);
		};
		
	});

}

$(document).ready(function () {
	showMain();
})