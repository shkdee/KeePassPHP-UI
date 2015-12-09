function $cel(n)
{
	return $(document.createElement(n));
}

function $get(id)
{
	return $(document.getElementById(id));
}

function raiseError(body) {
	var modal = $get("modal_error");
	modal.find(".modal-body > pre").text(body);
	modal.modal("show");
}

function InputWatcher(input)
{
	this._event = input.attr("type") == "checkbox" ? "change" : "input";
	this.input = input;
	var that = this;
	input.on(this._event, function() {
		if(that._watched)
		{
			var test = that.makeTest();
			that._ok = test.result;
			that.displayError(test);
			if(that._ok)
				that._watched = false;
		} else
			that._ok = false;
	});
}

InputWatcher.prototype = {
	_ok: false,
	_watched: false,

	makeTest: function()
	{
		return { result: !!this.input.val(), error: errorMessages.empty };
	},

	isValid: function()
	{
		if(this._ok)
			return true;
		this._watched = true;
		this.input.triggerHandler(this._event);
		return this._ok;
	},

	val: function()
	{
		return this.input.val();
	},

	displayError: function(test)
	{
		if(test.result)
			hideErrors(this.input);
		else
			showError(this.input, test.error);
	}
};

function hideErrors(input)
{
	var row = input.parent();
	var form_group = row.parent();
	if(form_group.hasClass("has-error"))
	{
		form_group.removeClass("has-error");
		row.children(".help-block").remove();
	}
	return;
}

function showError(input, error)
{
	var row = input.parent();
	var form_group = row.parent();
	if(form_group.hasClass("has-error"))
		row.children(".help-block").remove();
	else
		form_group.addClass("has-error");
	if(error)
		row.append($cel("span").addClass("help-block").text(error));
}

function selectThis()
{
	$(this).select();
}


$(function()
{
	var urlParts = location.href.split("#");
	if(urlParts.length > 1)
		if(urlParts[1] == "see" || urlParts[1] == "open" || urlParts[1] == "about" || urlParts[1] == "add")
			$('ul.nav.nav-tabs a[href="#' + urlParts[1] + '"]').tab("show");

	var ajaxQueryString = (typeof forceLang !== "undefined" && forceLang) ?
		"?l=" + forceLang : "";

	$get("dbid").focus();
	$get("use_pwd_in_key").on("click", function() {
		if (!$(this).is(":checked"))
			$get("open_other_pwd").focus();
	});
	$get("add_use_pwd_in_key").on("click", function() {
		if (!$(this).is(":checked"))
			$get("add_other_pwd").focus();
	});

	$get("open_more").on("show.bs.collapse", function() {
		$get("open_more_a").addClass("dropup");
	})
		.on("hidden.bs.collapse", function() {
			$get("open_more_a").removeClass("dropup");
		});

	$get("add_more").on("show.bs.collapse", function() {
		$get("add_more_a").addClass("dropup");
	})
		.on("hidden.bs.collapse", function() {
			$get("add_more_a").removeClass("dropup");
		});

	if(typeof formErrors !== "undefined" && formErrors) {
		for(var input in formErrors) {
			showError($get(input), errorMessages[formErrors[input]]);
		}
	}

	if(typeof debugTrace !== "undefined") {
		$get("debugtrace").removeClass("hide").text("Debug trace:\n" + debugTrace);
	}

	var addDbidWatcher = new InputWatcher($get("add_dbid"));
	var addKdbxfileWatcher = new InputWatcher($get("add_kdbx_file"));
	var addMainPwdWatcher = new InputWatcher($get("add_main_pwd"));
	var addUsePwdInKey = $get("add_use_pwd_in_key");
	var addOtherPwd = $get("add_other_pwd");
	var addOtherKeyfile = $get("add_other_keyfile");

	$get("form_add").on("submit", function()
	{
		var ok = true;
		if (!addUsePwdInKey.is(":checked") && !addOtherPwd.val() && !addOtherKeyfile.val())
		{
			$get("add_more").collapse('show');
			showError(addUsePwdInKey);        
			showError(addOtherPwd, errorMessages.nootherkey);
			showError(addOtherKeyfile, errorMessages.nootherkey);
			ok = false;
		}
		else
		{
			hideErrors(addUsePwdInKey);
			hideErrors(addOtherPwd);
			hideErrors(addOtherKeyfile);
		}

		ok = addDbidWatcher.isValid() && ok;
		ok = addKdbxfileWatcher.isValid() && ok;
		ok = addMainPwdWatcher.isValid() && ok;

		if(!ok)
			return false;
	});

	var dbidWatcher = new InputWatcher($get("dbid"));
	var mainPwdWatcher = new InputWatcher($get("main_pwd"));
	var otherPwd = $get("open_other_pwd");
	var usePwdInKey = $get("use_pwd_in_key");

	function loadPassword() {
		var btn = $(this);
		btn.button("loading");
		var uuid = btn.data("uuid");
		var container = btn.parent();

		var dbidval = dbidWatcher.val();
		var mainPwdval = mainPwdWatcher.val();
		var otherPwdval = otherPwd.val();
		var usePwdInKeyVal = usePwdInKey.is(':checked');
		if (!dbidval || !mainPwdval || (!otherPwdval && !usePwdInKeyVal)) {
			btn.button('reset');
			$("open_tab_a").tab('show');
			$get("form_open").submit();
			return;
		}

		$.ajax({
			data: {
				dbid: dbidval,
				main_pwd: mainPwdval,
				use_pwd_in_key: usePwdInKeyVal,
				open_other_pwd: otherPwdval,
				uuid: uuid
			},
			method: 'POST',
			dataType: 'json',
			url: 'ajaxopen.php' + ajaxQueryString,
			timeout: 20000
		})
			.fail(function(jqxhr, status, error) {
				/*$get("see_results").empty().addClass("hide");
				$get("see_alert").show();
				btn.button('reset');*/
				raiseError(error);
			})
			.done(function(answer) {
				$get("debugtrace").empty().addClass("hide");
				var status = answer.status;
				if(status == 1 /* Success */)
					container.html(answer.result)
						.find(".selectOnFocus").on("focus", selectThis);
				else if(status == 5 /* Password not found */)
					container.html(answer.result);
				else if(answer.result)
					raiseError(answer.result);
				if(answer.debug)
					$get("debugtrace").removeClass("hide").text("Debug trace:\n" + answer.debug);
				btn.button('reset');
			});
	}

	$get("form_open").on("submit", function()
	{
		var button = $(this).find('button[type="submit"]');
		button.button('loading');

		var ok = true;
		if(!otherPwd.val() && !usePwdInKey.is(":checked"))
		{
			$get("open_more").collapse('show');
			showError(otherPwd, errorMessages.nootherkey);
			showError(usePwdInKey);
			ok = false;
		}
		else
		{
			hideErrors(otherPwd);
			hideErrors(usePwdInKey);
		}

		ok = dbidWatcher.isValid() && ok;
		ok = mainPwdWatcher.isValid() && ok;

		if(ok)
		{
			$.ajax({
				data: {
					dbid: dbidWatcher.val(),
					main_pwd: mainPwdWatcher.val(),
					use_pwd_in_key: usePwdInKey.is(":checked"),
					open_other_pwd: otherPwd.val()
				},
				method: 'POST',
				dataType: 'json',
				url: 'ajaxopen.php' + ajaxQueryString,
				timeout: 20000
			})
				.fail(function(jqxhr, status, error) {
					$get("see_results").empty().addClass("hide");
					$get("see_alert").show();
					button.button('reset');
					raiseError(error);
				})
				.done(function(answer) {
					$get("debugtrace").empty().addClass("hide");
					var status = answer.status;
					if(status == 1 /* Success */) {
						hideErrors(otherPwd);
						hideErrors(usePwdInKey);
						$get("see_alert").hide();
						var results = $get("see_results").removeClass("hide")
							.html(answer.result);
						results.find(".selectOnFocus").on("focus", selectThis);
						results.find(".passwordLoader").on("click", loadPassword);
						$get("see_tab_li").removeClass("disabled").children("a").tab("show");
					} else {
						$get("see_results").empty().addClass("hide");
						$get("see_alert").show();
						if(status === 0 /* fail */)
							raiseError(answer.result);
						else if(status == 2 /* bad password */)
							showError(mainPwdWatcher.input, errorMessages.badpwd);
						else if(status == 3 /* no such id */)
							showError(dbidWatcher.input, errorMessages.nosuchid);
						else if(status == 4 /* something is empty */)
						{
							var element = document.getElementById(answer.result);
							if(element)
								showError($(element), errorMessages.empty);
						}
						if(answer.debug)
							$get("debugtrace").removeClass("hide").text("Debug trace:\n" + answer.debug);
					}
					button.button('reset');
				});
		}
		else
			button.button('reset');
		return false;
	});

	$get("btn_clean_all").on("click", function() {
		$get("see_results").empty().addClass("hide");
		$get("see_alert").show();
		$get("see_tab_li").addClass("disabled");
		$get("debugtrace").empty().hide();
		$get("form_open").get(0).reset();
		$get("form_add").get(0).reset();
		return false;
	});
});