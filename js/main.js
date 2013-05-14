$(function(){
	$("#dbid").focus();
	$("#usePwdForCK").click(function(){
		if(!$(this).is(':checked'))
			$("#openPwd1").focus();
	});
	$("#addUsePwdForCK").click(function(){
		if(!$(this).is(':checked'))
			$("#addPwd1").focus();
	});
	$(".withToggleableChevron").click(function(){
		$(this).children('i')
			.toggleClass('icon-chevron-up')
			.toggleClass('icon-chevron-down');
	});
});

$(function(){
	var sp = location.href.split("#");
	if(sp.length > 1)
		if(sp[1] == "see" || sp[1] == "open" || sp[1] == "about" || sp[1] == "add")
			$('#mainTab a[href="#'+sp[1]+'"]').tab("show");
});

function raiseError(body){
	var modal = $("#modalError");
	modal.find(".modal-body > pre").html(body);
	modal.modal("show");
}

function formChecker(state){
	this.state = state;
	this.isError = false;
	this.manageStyles = function(field, param){
		var par = field.parent().parent();
		var found = false;
		$.each(par.attr("class").split(/\s+/), function(i, e){
			if(e == param[1]) found = true
			else par.toggleClass(e, e == "control-group");
		});
		if(!found && param[1] != null)
			par.addClass(param[1]);
		found = false;
		field.siblings('.help-inline').each(function(i){
			var e = $(this);
			if(e.attr("ident") != param[0])
				e.hide("slow", "linear", function() { $(this).remove(); });
			else
				found = true;
		});
		if(!found && param[0] != null){
			var nn = $('#errorMessages > [ident="'+param[0]+'"]').clone();
			field.after(nn);
			nn.show("fast", "linear");
		}
	}
	this.setStyle = function(){
		var isFocus = false;
		for(var k in this.state)
			if(this.state[k] == null || this.state[k] == "")
				this.manageStyles($(k), [null, null]);
			else {
				this.manageStyles($(k), this.state[k]);
				if(!isFocus){
					$(k).focus();
					isFocus = true;
				}
			}
	}
	this.setIfEmpty = function(k, v){
		if(this.state[k] == null && v != null){
			state[k] = v;
			this.isError = true;
		}
	}
}

$(function(){
	$("#formAdd").submit(function(){
		var checker = new formChecker({"#addDbid":null, "#addKdbxFile":null, "#addMainPwd":null, "#addPwd1":null, "#addUsePwdForCK":null, "#addFile1":null});
		if($("#addDbid").val() == "")
			checker.setIfEmpty("#addDbid", ["empty", "warning"]);
		if($("#addKdbxFile").val() == "")
			checker.setIfEmpty("#addKdbxFile", ["empty", "warning"]);
		if($("#addMainPwd").val() == "")
			checker.setIfEmpty("#addMainPwd", ["empty", "warning"]);
		if($("#addPwd1").val() == "" && !$("#addUsePwdForCK").is(':checked')
			&& $("#addFile1").val() == ""){
			$("#addmore").collapse('show');
			checker.setIfEmpty("#addPwd1", ["nootherkey", "error"]);
			checker.setIfEmpty("#addUsePwdForCK", ["", "error"]);
			checker.setIfEmpty("#addFile1", ["nootherkey", "error"]);
		}
		if(checker.isError)
		{
			checker.setStyle();
			return false;
		}
	})
});

$(function(){
	$("#formOpen").submit(function(){
		var button = $(this).find('button[type="submit"]');
		button.button('loading');
		var checker = new formChecker({"#dbid":null, "#mainPwd":null, "#openPwd1":null, "#usePwdForCK":null});
		var dbidval = $("#dbid").val();
		if(dbidval == "")
			checker.setIfEmpty("#dbid", ["empty", "warning"]);
		var mainPwdval = $("#mainPwd").val();
		if(mainPwdval == "")
			checker.setIfEmpty("#mainPwd", ["empty", "warning"]);
		var otherPwdval = 	$("#openPwd1").val();
		var usePwdForCKval = $("#usePwdForCK").is(':checked');
		if(otherPwdval == "" && !usePwdForCKval){
			$("#openmore").collapse('show');
			checker.setIfEmpty("#openPwd1", ["nootherkey", "error"]);
			checker.setIfEmpty("#usePwdForCK", ["", "error"]);
		}
		if(checker.isError){
			checker.setStyle();
			button.button('reset');
			return false;
		}
		var data = "dbid="+dbidval+"&mainPwd="+mainPwdval+"&usePwdForCK="+
			(usePwdForCKval ? "1" : "0")+"&openPwd1="+otherPwdval;
		$.post("ajaxopen.php", data, function(response, status){
			if(status == "success"){
				if(response[0] == "1") {
					checker.setStyle();
					$("#seeAlert").hide();
					$("#seeResult").show().html(response[1])
						.find(".selectOnFocus").focus(function(){ $(this).select();	});
					$("#mainTab li:eq(2)").removeClass("disabled").children("a").tab("show");
				} else {
					$("#seeResult").empty().hide();
					$("#seeAlert").show();
					if(response[0] == "2")
						checker.setIfEmpty("#mainPwd", ["badpwd", "error"]);
					else if(response[0] == "3")
						checker.setIfEmpty("#dbid", ["nosuchid", "error"]);
					else if(response[0] == "4")
					{
						if(("#" + response[1]) in checker.state)
							checker.setIfEmpty("#" + response[1], ["empty", "warning"]);
					}
					else if(response[0] == "0")
						raiseError(response[1] + "\n" + response[2]);
					checker.setStyle();
				}
				if(response[2] != "")
					$("#debugtrace").show().html("Debug trace:\n"+response[2]);
				else
					$("#debugtrace").empty().hide();
			}
			button.button('reset');
		}, 'json');
		return false;
	})
});


function cleanAll(){
	$("#seeResult").empty().hide();
	$("#seeAlert").show();
	$("#debugtrace").empty().hide();
	$("#formOpen").get(0).reset();
	$("#formAdd").get(0).reset();
	return false;
}

function loadPassword(uuid){
	var container = $("#pwd_" + uuid);
	container.children('button').button('loading');
	var dbidval = $("#dbid").val();
	var mainPwdval = $("#mainPwd").val();
	var otherPwdval = 	$("#openPwd1").val();
	var usePwdForCKval = $("#usePwdForCK").is(':checked');
	if(dbidval == "" || mainPwdval == "" || (otherPwdval == "" && !usePwdForCKval))
	{
		container.children('button').button('reset');
		$("#mainTab li:eq(0) a").tab('show');
		$("#formOpen").submit();
		return;
	}
	var data = "dbid="+dbidval+"&mainPwd="+mainPwdval+"&usePwdForCK="+
		(usePwdForCKval ? "1" : "0")+"&openPwd1="+otherPwdval+"&uuid="+uuid;
	$.post("ajaxopen.php?r=p", data, function(response, status){
		if(status == "success"){
			if(response[0] == "1")
				container.html(response[1])
					.find(".selectOnFocus")
					.focus(function(){ $(this).select(); });
			else if(response[0] == "5")
				container.html(response[1]);
			else if(response[1] != "")
				raiseError(response[1] + "\n" + response[2]);
			if(response[2] != "")
				$("#debugtrace").show().html("Debug trace:\n"+response[2]);
			else
				$("#debugtrace").empty().hide();
		}
		container.children('button').button('reset');
	}, 'json');
}