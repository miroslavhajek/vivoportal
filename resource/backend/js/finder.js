var multiFinderSearch = false;

$(document).ready(function() {
	//multifinder
	var _mainFinderMultiId = $("form[name='mainFinderMulti']").attr("id");
	if (_mainFinderMultiId)	{
		window.loaded = 1;
		//init multiFinder
		//var entities = getAllEntities(_mainFinderMultiId);
		var getPath = $('#' + _mainFinderMultiId).find("input[name='getPath[getEntities]']").attr("value");
		var pathPart = $('#' + _mainFinderMultiId).find("input[name='input']").attr("value");
		action({
				data: "act="+getPath+"&args[]="+pathPart,
				error: function() {
					console.log("error");
				}, 
				success: function(res, textStatus, jqXHR) {
					console.log("finder data");
					console.log(res.data);
					initMultiFinder(_mainFinderMultiId, res.data.length);
				}
			});

		//$(".inputFinder").show();

		//view bookmarks from finder
		//viewBookmark(_mainFinderMultiId);
	}

	//resize of multifinder
	if (_mainFinderMultiId)	{
		$(window).resize(function() {
			reduceMultifinder(_mainFinderMultiId);
		});
		reduceMultifinder(_mainFinderMultiId);
	}

});	

//zkracovani multifinderu pri malem rozliseni obrazovky - init
function reduceMultifinder(_id) {
	var finderData = new Array();
	var allChildWidth = 0;
	var _children = 0;
	$("#finderMulti_" + _id).children().each(function(i) {
		var child = $(this);
		if (child.hasClass("finderMultiPartDir") || child.hasClass("finderMultiPart")) {
			child.addClass("f-"+i);
			finderData["f-"+i] = new Array();
			finderData["f-"+i]['val'] = child.width();
			if (i > 0) {
				finderData["f-"+i]['sum'] = finderData["f-"+ (i - 1)]['sum'] + child.width();
			} else {
				finderData["f-"+i]['sum'] = child.width();
			}
			allChildWidth += child.width();
			_children++;
		}
	});

	for (var i = _children - 1; i >= 0; i--) {
		if (i == _children - 1) {
			finderData["f-"+i]["sum2"] = finderData["f-"+i]["val"];
		} else {
			finderData["f-"+i]["sum2"] = finderData["f-"+ (i + 1)]["sum2"] + finderData["f-"+i]["val"];
		}
	}

	for (var i = 0; i < _children; i++) {
		showHideMultifinderParts(_id, i, finderData);
	}

	if ($("#header .user-icon").text().indexOf("zstanek") > 0) {
	}
}

//zkracovani multifinderu pri malem rozliseni obrazovky - vykreslovani
function showHideMultifinderParts(_id, _part, childData) {
	var finderWidth = $("#finderMulti_" + _id).width();
	var finderRightPadd = 50;

	if (childData['f-' + _part]['sum2'] >= finderWidth - finderRightPadd) {
		$("#finderMulti_" + _id + " .f-" + _part).hide();
	} else {
		if ($("#finderMulti_" + _id + " .f-" + _part).not(":visible"))
			$("#finderMulti_" + _id + " .f-" + _part).show();
	}
}

//--inicializace multifinderu (vola multifinder)
function initMultiFinder(_id, last) {
	$('#' + _id).find(".inputFinder").hide();
	$("#finderMulti_" + _id).show();
	multiFinder(_id, last);

	$('#' + _id).find("input[name='input']")
		.keyup(function (e) {
			if (e.keyCode == 27) {
				$(this).blur();
			}
		})
		.blur(function() {
			var inputVal = $(this).val();
			if (inputVal.substring(0,1) == "/") {
				multiFinderSearch = false;
			}
			if (!multiFinderSearch) {
				$('#' + _id).find(".inputFinder").hide();
				$("#finderMulti_" + _id).show();
			}
		});

	$("body")
		.keyup(function(e) {
			if (e.keyCode == 27) {
				destroyMultiFinderSubmenus(_id);
			}
		})
		.click(function() {
			destroyMultiFinderSubmenus(_id);
		});
}

//--vraci cestu (cast cesty) v documents (0 -> /, 1 -> /xxx/, atd)
function getPathPart(part, _id) {
	var pathPart = "";
	if (typeof part == "undefined") part = 0;
		var InputField = $("#" + _id).find("input[name='input']").attr("value");
		//alert(InputField);
		//var InputField = (_InputField != "" && _InputField != "/") ? _InputField + "/" : _InputField;
		//alert(InputField);
		var InputFieldParts = new Array();
		InputFieldParts = InputField.split("/");

		pathPart = "/";
		jQuery.each(InputFieldParts, function(i, val) {
			if (i > 0 && i <= part) {
				pathPart += val;
				/*if (i != part) */pathPart += "/";
			}
		});
	return pathPart;
}

//--vykresluje samotny multifinder
function multiFinder(_id, last) {
	var InputField = $("#" + _id).find("input[name='input']").attr("value");
	var pathsField = $("#" + _id).find("input[name='input']").attr("data-titles");
	var siteName = $("#" + _id).find("input[name='site[name]']").attr("value");

	var InputFieldParts = new Array();
	InputFieldParts = InputField.split("/");
	//InputFieldParts.pop();
	/*
	if (InputField.length > 1)
		InputFieldParts[InputFieldParts.length] = "";
	*/
	//console.log(urldecode(pathsField));
	//console.log($.parseJSON(urldecode(pathsField)));

	var pathsFieldParts = new Array();
	//pathsFieldParts = pathsField.split("###");
	//pathsFieldParts = $.parseJSON(urldecode(pathsField));
	pathsFieldParts = window.finder_data[_id];
	//pathsFieldParts.splice(0, 0, {title: "", actionUrl: ""});

	console.log(pathsFieldParts);

	$("#finderMulti_" + _id).html("");
	$("#finderMulti_" + _id).css("cursor", "text");

	//var actionURL = $('#' + _id).find("input[name='actionURL[set]']").attr("value");
	var actionURL = location.pathname;

	//sitename
	$("#finderMulti_" + _id).append("<a class='finderMultiPart' id='"+_id+"_a_" + 0 + "_holder' href='"+pathsFieldParts[0].actionUrl+"'><span>" + siteName + "</span></a>");

	jQuery.each(InputFieldParts, function(i, val) {
		console.log(i);
		console.log(typeof pathsFieldParts[i]);
		if (typeof pathsFieldParts[i] == 'undefined') return;

		var pathTitle = pathsFieldParts[i].title;
		var pathUrl = pathsFieldParts[i].actionUrl;
		
		if (i < InputFieldParts.length - 1) {
			if (i > 0) $("#finderMulti_" + _id).append("<a class='finderMultiPart' id='"+_id+"_a_" + i + "_holder' href='"+pathUrl+"'><span>" + pathTitle + "</span></a>");
			if (last != 0 || i != InputFieldParts.length - 2) $("#finderMulti_" + _id).append("<div class='finderMultiPartDir'><a href='javascript:void(0)' id='"+_id+"_a_" + i + "_entities' class='finderMultiPartDira'><span>&gt;</span></a><div id='"+_id+"_" + i + "_entities' class='finderMultiPartDirEntities'></div></div>");
			$('#'+_id+'_'+i+'_entities').hide();
			$('#'+_id+'_a_'+i+'_entities').click(function(e) {
				e.stopPropagation();
				e.cancelBubble = true;

				return showEntities(i, _id);
			});

			$('#'+_id+'_a_'+i+'_holder').click(function(e) {
				e.stopPropagation();
				e.cancelBubble = true;
			});

			$('#'+_id+'_a_'+i+'_entities').mouseover(function(e) {
				$(this).addClass("finderMultiPartDiraSel");
				$('#'+_id+'_a_'+parseInt(parseInt(i) + 0)+'_holder').addClass("finderMultiPartSel");
			});
			$('#'+_id+'_a_'+i+'_entities').mouseout(function(e) {
				$(this).removeClass("finderMultiPartDiraSel");
				$('#'+_id+'_a_'+parseInt(parseInt(i) + 0)+'_holder').removeClass("finderMultiPartSel");
			});

			$('#'+_id+'_a_'+i+'_holder')
				.attr("title", ((i == 0) ? siteName : pathTitle) + " - " + getPathPart(i, _id))
				.mouseover(function(e) {
					$(this).addClass("finderMultiPartSel");
				})
				.mouseout(function(e) {
					$(this).removeClass("finderMultiPartSel");
				});
		}
	});

	$("#finderMulti_" + _id).append($("#" + _id + " .inputFinderMultiBookmarkTemp").html());

//	if ($("#header .user-icon").text().indexOf("zstanek") > 0) { }

	$("#finderMulti_" + _id + " .inputFinderMultiBookmark").show();
	$("#finderMulti_" + _id + " .inputFinderMultiBookmark a").click(function(e) {
		e.stopPropagation();
		e.cancelBubble = true;
	});

	//view bookmarks from finder
	viewBookmark(_id);

	$("#finderMulti_" + _id).append("<div style='clear: both'></div>");

	var firstSearchResult = 1;

	var origVal = $('#' + _id).find(".inputFinder").find("input[name='input']").val();
	
	var searchTimer0 = null;
	
	$("#finderMulti_" + _id).click(function() {
		$("#finderMulti_" + _id).hide();
		$('#' + _id).find(".inputFinder").show().find("input[name='input']").focus();
		destroyMultiFinderSubmenus(_id);
		var searchPullDownContent = "";
	});

	//input keyup
	$('#' + _id).find(".inputFinder input").keyup(function(e) {
		var inputVal = $(this).val();

		if (jQuery.trim(inputVal) != "" && inputVal.substring(0,1) != "/" && jQuery.inArray(e.keyCode, [37, 38, 39, 40, 13]) == -1) { // 37 left, 38 up, 39 right, 40 down
			multiFinderSearch = false;
			clearTimeout(searchTimer0);
			searchTimer0 = setTimeout(function(){
				var newSearchstr = $('#' + _id).find(".inputFinder").find("input[name='input']").val();
				if (jQuery.trim(newSearchstr) != "" && newSearchstr.substring(0,1) != "/") {
					$('#' + _id).find(".inputFinder .searchBox").removeShadow();
					$('#' + _id).find(".inputFinder .searchBox").remove();
					var act = $('#' + _id).find("input[name='getPath[search]']").attr("value");

					

					action({
						data: "act="+act+"&args[]="+newSearchstr,
						error: function() {
							console.log("error");
						}, 
						success: function(res, textStatus, jqXHR) {
							console.log("finder data");
							console.log(res.data);
							initMultiFinder(_mainFinderMultiId, res.data.length);
						}
					});

					//searchPullDownContent = action(getPath, "renderSearchPulldown", newSearchstr);
					searchPullDownContent = "";
					searchTimer0 = null;
					$('#' + _id).find(".inputFinder").append($(document.createElement("div")).addClass("searchBox"));
					$('#' + _id).find(".inputFinder .searchBox")
						.html(searchPullDownContent)
						.slideDown(200, function() {
							$(this).dropShadow({
								left: 3,
								top: 3,
								blur: 2,
								opacity: .7,
								color: "#8ba9c8",
								swap: false
							});
	
							firstSearchResult = 0;
							searchResultCount = 0;
							searchResultPosition = 0;
							$(this).find("a").each(function() {
								searchResultCount++;
								$(this).attr("id", "pos-" + searchResultCount);
							});
						});
					multiFinderSearch = true;
				}
			}, 500);
		}	
		
		if (inputVal == "" || e.keyCode == 27) {
				$('#' + _id).find(".inputFinder .searchBox").removeShadow();
				$('#' + _id).find(".inputFinder .searchBox").remove();
				firstSearchResult = 1;
		}
		if (inputVal.substring(0,1) != "/" && jQuery.trim(inputVal) != "") {
			switch(e.keyCode) {
				case 38: //up
						e.stopPropagation();
						e.cancelBubble = true;
						e.preventDefault();
						moveSearchResults(_id, -1);
					break;
				case 40: //down
						e.stopPropagation();
						e.cancelBubble = true;
						e.preventDefault();
						moveSearchResults(_id, 1);
					break;
				case 13: //return
						//e.preventDefault();
						if (typeof $('#' + _id + " .inputFinder .searchBox li.active a").attr("href") != "undefined") {
							var redirUrl = $('#' + _id + " .inputFinder .searchBox li.active a").attr("href");
							redirUrl = (redirUrl.substring(redirUrl.length - 1) != "/" && redirUrl.substring(redirUrl.length - 3) != "%2F") ? redirUrl + "/" : redirUrl;
							location.href = redirUrl;
						}
						//return false;
					break;
				case 27: //esc
				break;
				}
			}
			if (inputVal.substring(0,1) == "/") {
				if (e.keyCode == 13) {
					var inputVal_ = inputVal;
					inputVal_ = (inputVal_.substring(inputVal_.length - 1) != "/") ? inputVal_ + "/" : inputVal_;
					console.log(actionURL+"&args[]=" + inputVal_);
					location.href = actionURL+"&args[]=" + inputVal_;
				}
			}

			if (e.keyCode == 27) {
				$(this).val(origVal);
				$(this).blur();
			}
		})
	//input keypress
	.keypress(function(e) {
		var inputVal = $(this).val();
		if (e.keyCode == 13) {
			if (inputVal.substring(0,1) != "/") {
				e.preventDefault();
				return false;
			} else {
				e.preventDefault();
				return false;
			}
		}
	});

	$('#' + _id).submit(function() {
		var inputVal_ = $('#' + _id).find(".inputFinder input").val();
		inputVal_ = (inputVal_.substring(inputVal_.length - 1) != "/") ? inputVal_ + "/" : inputVal_;
		location.href = actionURL+"&args[]=" + inputVal_;
		return false;
	});
}

//--skryti zobrazenych submenu pro jednotlive uzly multifinderu
function destroyMultiFinderSubmenus(_id, part) {
 	if (typeof part != "undefined") { //hide all except part
		$("#finderMulti_" + _id + " .multiFinderEntitiesContainer").each(function() {
			if ($(this).parent().attr("id") != _id + "_" + part + "_entities") {
				$(this).parent().html("").hide();
			}
		});
	} else { //hide all
		$("#finderMulti_" + _id + " .multiFinderEntitiesContainer").each(function() {
			$(this).parent().html("").hide();
		});
	}

	$("#finderMulti_" + _id + " #finderBookmarkSelCont").removeShadow().hide();
	destroySiteChooserSubmenu();
}

//--vykresluje seznam odkazu pro jednotlive "uzly" v ceste
function showEntities(part, _id) {
	if ($('#'+_id+'_'+part+'_entities').css("display") == 'none') {
		var pathAll = $('#' + _id).find("input[name='input']").attr("value");
		//pathAll = (pathAll != "" && pathAll != "/") ? pathAll + "/" : pathAll;
		var pathPart = (typeof part == 'undefined') ? pathAll : getPathPart(part, _id);
		//pathPart = (pathPart != "" && pathPart != "/") ? pathPart + "/" : pathPart;

		//skryti ostatnich submenu
		destroyMultiFinderSubmenus(_id, part);

		$('#'+_id+'_'+part+'_entities').append(
			$(document.createElement("div"))
			.attr("class",  "multiFinderEntitiesContainer")
			.hide()
		);

		var getPath = $('#' + _id).find("input[name='getPath[getEntities]']").attr("value");
		var actionURL = $('#' + _id).find("input[name='actionURL[set]']").attr("value");
		console.log(getPath);
		console.log(pathPart);
		var entities = action({
								data: "act="+getPath+"&args[]="+pathPart,
								error: function() {
									console.log("error");
								}, 
								success: function(res, textStatus, jqXHR) {
									console.log("finder data");
									console.log(res.data);

									for (var i = 0; i < res.data.length; i++) {
									var entity = res.data[i];
									console.log(entity);
									entity.path = entity.path.replace("//", "/");
									var value = entity.path.substring(entity.path.indexOf('/', 1));
									value = value.substring(value.indexOf('/', 1)) + ''; // ve value je cesta k nastaveni do finderu
									var name = entity.path.substring(entity.path.lastIndexOf('/') + 1); // v name je zobrazeny nazev entity
									$('#'+_id+'_'+part+'_entities > div').append($(document.createElement("a"))
														.attr("href", entity.actionUrl)
														//.attr("class", "multiFinderEntitiesHref")
														.addClass("multiFinderEntitiesHref")
														.addClass("multiFinderEntitiesHrefIcon")
														.addClass(entity.folder ? "multiFinderEntitiesHrefFolder" : "")
														.css({"background-image" : "url('"+entity.icon+"')"})
														.text(entity.title ? entity.title : name)
														.attr('title', (entity.title ? entity.title : name) + ' - ' + value + '/')
														.wrapInner($(document.createElement("span")))
														.prepend($(document.createElement("span")).addClass(entity.published ? "published" : "not-published"))
														).hide();
								}

								$('#'+_id+'_'+part+'_entities').show();
								$('#'+_id+'_'+part+'_entities > div').slideDown(200, function() {
									$(this).dropShadow({
										left: 3,
										top: 3,
										blur: 2,
										opacity: .7,
										color: "#8ba9c8",
										swap: false
									});
								});
								$('#'+_id+'_'+part+'_entities').click(function(e) {
									e.stopPropagation();
									e.cancelBubble = true;
								});
								$('#'+_id+'_'+part+'_entities').show();
								
								}
							});
		var entities = {};
		//alert(serialize(entities));
		

		//$('#'+_id+'_'+part+'_entities > div').dropShadow();

		
	} else {
		$('#'+_id+'_'+part+'_entities').html("");
		$('#'+_id+'_'+part+'_entities').hide();
	}
	return false;
}

function getAllEntities(_id) {
	var InputField = $('#' + _id).find("input[name='input']").attr("value");
	//var InputField = (_InputField != "" && _InputField != "/") ? _InputField + "/" : _InputField;
	var getPath = $('#' + _id).find("input[name='getPath[getEntities]']").attr("value");
	//var entities = action(getPath, InputField);
	var entities = {};
	return entities;
}

function viewBookmark(_id) {
	$(".finderBookmarkSela")
		.attr("href", "javascript:void(0)")
		.click(function(e) {
			e.stopPropagation();
			e.cancelBubble = true;
			if ($("#finderBookmarkSelCont").is(":visible")) {
				$("#finderBookmarkSelCont").html("").removeShadow().hide();
			}
			else {
				var getPath = $('#' + _id).find("input[name='finder[output]']").attr("value");
				//bookmarkContent = action(getPath, "viewBookmarks");
				bookmarkContent = "";
				$("#finderBookmarkSelCont").html(bookmarkContent).slideDown(200, function() {
					$(this)
						.click(function(e) {
							e.stopPropagation();
							e.cancelBubble = true;
						})
						.removeShadow();

					$(this).dropShadow({
						left: 3,
						top: 3,
						blur: 2,
						opacity: .7,
						color: "#8ba9c8",
						swap: false
					});
				});
			}
		});
}

function urldecode(url) {
  return decodeURIComponent(url.replace(/\+/g, ' '));
}