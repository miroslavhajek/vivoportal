var action_param = "act",
        showHideHintsFlag = true,
        ie7, ie8, ie6, opera;

//onload
$(document).ready(function() {
    $("#loader").hide();
    $("#wrapper").show();

    $(".explorer_content_with_tree").css({'margin-right': parseInt($.cookie("treeMenuWidth")) + 7});
    $(".explorer_tree").css({"width": parseInt($.cookie("treeMenuWidth"))});


    //show/hide treeMenu
    if ($.cookie("hideTree") == 1) {
        $(".explorer_content_with_tree").css("margin-right", "7px");
        $(".explorer_tree").css({"width": "0px"});
        $(".explorer_tree_hitcher").addClass("explorer_tree_hitcher_right_closed").removeClass("explorer_tree_hitcher_right_open");
        $(".explorer_tree_hitcher_cont").addClass("explorer_tree_hitcher_right_closed").removeClass("explorer_tree_hitcher_right_open");
    } else {
        $(".explorer_tree_hitcher").removeClass("explorer_tree_hitcher_right_closed").addClass("explorer_tree_hitcher_right_open");
        $(".explorer_tree_hitcher_cont").removeClass("explorer_tree_hitcher_right_closed").addClass("explorer_tree_hitcher_right_open");
        //$(".treeViewCont").show();
    }

    //system messages - error, warning, info
    $(".alert-info").slideDown("slow", function() {checkHeight();}).find("a.alert-close").attr("href", "javascript: void(0)").click(function() {$(this).parents(".alert").slideUp("fast", function() {$(this).remove(); checkHeight();});});
    $(".alert-success").slideDown("slow", function() {checkHeight();}).find("a.alert-close").attr("href", "javascript: void(0)").click(function() {$(this).parents(".alert").slideUp("fast", function() {$(this).remove(); checkHeight();});});
    $(".alert-warning").slideDown("slow", function() {checkHeight();}).find("a.alert-close").attr("href", "javascript: void(0)").click(function() {$(this).parents(".alert").slideUp("fast", function() {$(this).remove(); checkHeight();});});
    $(".alert-error").slideDown("slow", function() {checkHeight();}).find("a.alert-close").attr("href", "javascript: void(0)").click(function() {$(this).parents(".alert").slideUp("fast", function() {$(this).remove(); checkHeight();});});
       


    //init treemenu
    initTreeMenu('#treeMenu');

    $(".explorer_tree_hitcher_cont").draggable({
        axis: 'x',
        zIndex: 1000000,
        cursor: 'move',
        snap: true,
        snapTolerance: 40,
        start: function(event, ui) {
            $(".explorer_tree_hitcher_cont").css({"left": "", "opacity": "0.6"});
            $("iframe").css("visibility", "hidden");
        },
        drag: function(event, ui) {
            $(".explorer_tree_hitcher_cont").css({"left": ""});
        },
        stop: function(event, ui) {
            off = ui.offset;
            console.log(off);
            var mwidth = $(window).width() - off.left;
            $(".explorer_content_with_tree").css({'margin-right': (mwidth < 0) ? 7 : mwidth});
            $(".explorer_tree").css({"width": (mwidth - 7 < 0) ? 0 : mwidth - 7});
            $(".explorer_tree_hitcher_cont").css({"left": ""});

            //$(".footer_status").html(mwidth);
            console.log(mwidth);
            if (mwidth - 7 <= 0) {

                $.cookie("hideTree", 1, {expires: 365, path: '/'});
                $(".explorer_tree_hitcher").addClass("explorer_tree_hitcher_right_closed").removeClass("explorer_tree_hitcher_right_open");
                $(".explorer_tree_hitcher_cont").addClass("explorer_tree_hitcher_right_closed").removeClass("explorer_tree_hitcher_right_open");
                checkHeight();
            } else {
                $.cookie("hideTree", null, {expires: 365, path: '/'});
                $(".explorer_tree_hitcher").removeClass("explorer_tree_hitcher_right_closed").addClass("explorer_tree_hitcher_right_open");
                $(".explorer_tree_hitcher_cont").removeClass("explorer_tree_hitcher_right_closed").addClass("explorer_tree_hitcher_right_open");
                checkHeight();
            }
            $.cookie("treeMenuWidth", ((mwidth - 7 < 0) ? defMenuWidth : mwidth - 7), {expires: 365, path: '/'});
            //openTree(parent.location.pathname);
            setFakeDialogPathWidth();
            checkMultiTabs();
            makeTableHeaderFixed();
            $("iframe").css("visibility", "visible");
        }
    });

    $('input[type=checkbox]').iCheck({
        checkboxClass: 'icheckbox_minimal',
        increaseArea: '20%' // optional
    });

    $('input[type=radio]').iCheck({
        radioClass: 'iradio_minimal',
        increaseArea: '20%' // optional
    });

    $('select').select2({
        minimumResultsForSearch: "250",
        width:"100%"
    });
    var contWidth = $('.select2-container').width();
    //console.log(contWidth);

    /* $('.checkbox.hover').hover(function(){
     var $this = $(this);
     var neco = $this.next();
     alert(neco);
     });*/

    $.ajaxSetup({
        url: window.location.pathname,
        type: "POST"
    });

    //alert(window.location.pathname);

    jQuery(document)
            .ajaxStart(function() {
        $("#loader").show();
    })
            .ajaxSuccess(function() {
        $("#loader").hide();
    })
            .ajaxError(function(event, jqxhr, settings, exception) {
        $("#loader").hide();
        alert("Ajax error ...\n" + exception);
    });

    ribbonInit();

    checkHeight();
    $(window).resize(function() {
        checkHeight();
    });

    startclock();

    //init treemenu
    initTreeMenu('#treeMenu');

    $(window).resize(function() {
        setFakeDialogPathWidth();
    });
    setFakeDialogPathWidth();

    //manager logout window
    $(window).resize(function() {
        shadowWindow("#logoutDialog", ".manager_content");
    });
    shadowWindow("#logoutDialog", ".manager_content");

    $('.browsersTrigger').click(function() {
        $(this).toggleClass('open');
        $('.saveButtons.browsers').toggle();
        shadowWindow("#logoutDialog", ".manager_content", false);
    });

    initTableInputs(".form-horizontal");

    //hint box (help)
    hintBox();

    //choose site ... menu
    siteMenu();

});

function setFakeDialogPathWidth() {
    if ($("#fakePathHolder").is(":visible")) {
        var firstItemOnRightSide = $("#firstRDialogItem").offset();
        var fakePath = $("#fakePathHolder .fakePath").offset();
        var newFakePathWidth = firstItemOnRightSide.left - fakePath.left - 30;
        $("#fakePathHolder .fakePath").css({"width": newFakePathWidth + "px"});
    }
}

function checkHeight() {

    var window_height = $(parent.window).height();
    var window_width = $(parent.window).width();
    var tabs_height = $("#tabs").height();
    tabs_height = (tabs_height === null || typeof tabs_height == 'undefined') ? 0 : tabs_height;
    var ribbon_height = $(".ribbon-holder").height();
    ribbon_height = (ribbon_height === null) ? 0 : ribbon_height;
    tabs_height = ribbon_height ? ribbon_height : tabs_height;
    var tabs_multi_iframe_height = $("#tabs-multi-iframe").height();
    var dialog_height = $(".dialogHeader").height();
    var finder_height = $("#finder").height() > 0 ? $("#finder").height() + 2 : 0;
    var footer_height = $("#footer").height();
    var default_view = window_height - $("#header").height() - footer_height - finder_height - 3;
    var message_height = $(".alerts").height();
    var h1_height = ($("h1.dialogTitle").height() > 0) ? $("h1.dialogTitle").height() + 1 : 0;
    var manager_panel_width = $(".manager_panel").width();
    message_height = (message_height === null || typeof message_height == 'undefined') ? 0 : message_height + 2;
    var button_bar = $('#buttons-bar').length ? $('#buttons-bar').height() : 0;


    $("body").css({"overflow-y": "hidden"});
    $(".explorer_panel").css({"height": default_view - message_height, "overflow-y": "hidden", "overflow-x": "hidden"});
    $(".manager_panel").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 4});

    if (location.pathname == "/system/manager/content-manager/") {
        var body = window_height - $("#header").height() - message_height - h1_height - dialog_height - $("#footer").height() - 3;

        $(".explorer_tree_hitcher").add(".explorer_tree_hitcher_cont").css({"height": body});
        $(".explorer_tree").css({"height": body, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    else {
        $(".explorer_tree_hitcher").add(".explorer_tree_hitcher_cont").css({"height": default_view - message_height});
        $(".explorer_tree").css({"height": default_view - message_height, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    if ($(".manager_panel").width() === null || 0) {//logon screen
        $(".manager_content").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 4, "margin-left": "0px"});
    } else {
        if (location.pathname == "/system/manager/" && ie7) {
            $(".manager_content").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 3});
        }
    }

    if (dialog_height > 0) {//page with dialog options
        if (location.pathname != "/system/Editors/browser/" || (location.pathname == "/system/Editors/browser/" && parent.window.location.pathname != "/system/Editors/editor/"))
            $(".tabMainContent").css({"height": default_view - tabs_height - dialog_height - message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden", "zoom": "1"});
    } else { //page without dialog options
        $(".tabContent").css({"height": default_view - tabs_height - message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden"});
        $(".tabMainContent").css({"height": default_view - tabs_height - message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    ;

    //replicator - osetreni zobrazeni exploreru s iframy pri editaci
    var parent_tabcontent_height = null;
    if (window != parent.window) {
        if (parent_tabcontent_height = parent.window.$(".tabContent").height() && location.pathname != "/system/Editors/browser/") {
            $(".tabMainContent").css({"height": parent_tabcontent_height - tabs_multi_iframe_height - dialog_height - message_height, "overflow-y": "auto", "overflow-x": "hidden", "zoom": "1"});
        }
    }

    //replicator - zobrazeni prehledu replikaci
    if ($("#replication").length) {
        var tab_content_height = $("#replication").parents(".tabMainContent").height();
        $(".tabMainContent").css("overflow-y", "hidden");
        $("#replication").css({
            "width": window_width - manager_panel_width,
            "height": tab_content_height,
            "overflow": "auto"
        });
    }

    //osetreni zobrazeni tabu zabezpeceni - roztahnuti na vysku
    if ($(".leftCell").length && $(".rightCell").length && $(".leftCell .secureDisplay:visible").length) {
        $(".leftCell .secureDisplay, .rightCell .cellContent")
                .css("height", window_height - $(".leftCell .secureDisplay:visible").offset().top - footer_height - 6); //6 - td padding (top + bottom)
    }

    //resize iframe
    $(".viewFrame, .vivoiframe").css({"height": default_view - tabs_height - dialog_height - message_height, "overflow-y": "auto", "overflow-x": "hidden", "border": "0px"});
    $(".vivoiframe").parents(".tabContent").css({"overflow-y": "hidden"});

    //manager help
    $(".manager_content > iframe").css({"width": "100%", "height": window_height - $("#header").height() - $("#footer").height() - 3});

    //site
    $(".site").css({"height": window_height - $("#header").height() - $("#footer").height() - finder_height - message_height - 3});

    //checkInputWithIconWidth();
}

//--soupatko pro zalozky v multidocumentu - init
function checkMultiTabs() {
    if ($("#tabs-multi").is(":visible")) {
        var li_width = new Array();
        var whole_width = new Number;
        var curDocument = $("#entity_uuid").val(); //uuid dokumentu
        var curPosition = ($.cookie("multiTabPosition") === null) ? 0 : $.cookie("multiTabPosition");
        var curPosotionParams = new Array();
        if (curPosition != 0) {
            curPosotionParams = curPosition.split(":");
        }
        var multiTabWidth = $("#tabs-multi").outerWidth(false);
        $("#tabs-multi li").each(function(i) {
            li_width.push($(this).outerWidth(true));
            whole_width += parseInt($(this).outerWidth(true));
        });

        whole_width = whole_width + 4; // tabsHolder-multi left padding

        $("#tabs-multi .tabsHolder-multi")
                .css({"width": whole_width + "px"});

        if (!$("#tabs-multi .rightScroller").length && multiTabWidth < whole_width) {
            $("#tabs-multi").addClass("tabs-multi");
            $("#tabs-multi .tabsHolder-multi")
                    .before($(document.createElement("div")).addClass("leftScroller"))
                    .before($(document.createElement("div")).addClass("rightScroller"));
        }

        if ($("#tabs-multi").hasClass("tabs-multi") && multiTabWidth >= whole_width) {
            $("#tabs-multi").removeClass("tabs-multi");
        }

        multiTabWidth = $("#tabs-multi").outerWidth(false);


        $("#tabs-multi .rightScroller").css({"left": ($("#tabs-multi").outerWidth(false) + $("#tabs-multi .rightScroller").width()) + "px"});


        if (multiTabWidth < whole_width) {
            $("#tabs-multi .rightScroller").addClass("rightScrollerActive");
        }

        if (parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left")) < 0) {
            $("#tabs-multi .leftScroller").addClass("leftScrollerActive");
        }

        if ($("#tabs-multi .rightScroller").hasClass("rightScrollerActive")) {
            $("#tabs-multi .rightScroller").bind('click', function() {
                multiTabMoveRight(whole_width);
            });
        }

        if ($("#tabs-multi .leftScroller").hasClass("leftScrollerActive")) {
            $("#tabs-multi .leftScroller").bind('click', function() {
                multiTabMoveLeft(whole_width);
            });
        }

        if (curPosotionParams[0] == curDocument && curPosotionParams[1] != 0) {
            $("#tabs-multi .tabsHolder-multi").css({"margin-left": curPosotionParams[1] + "px"});
        }
        if (curPosotionParams[0] != curDocument) {
            $.cookie("multiTabPosition", null, {expires: 365, path: '/'});
        }
    }
}
//--soupatko pro zalozky v multidocumentu - doprava
function multiTabMoveRight(whole_width) {
    var multiTabWidth = $("#tabs-multi").outerWidth(false);
    var curDocument = $("#entity_uuid").val(); //uuid dokumentu
    var mLeft = parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left")) - 300;
    var maxMLeft = multiTabWidth - whole_width;
    mLeft = (mLeft < maxMLeft) ? maxMLeft : mLeft;

    $("#tabs-multi .tabsHolder-multi")
       .animate(
            {"margin-left": mLeft + "px"},200,
            function() {
                if (parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left")) < 0) {
                    $("#tabs-multi .leftScroller").addClass("leftScrollerActive");
                    $("#tabs-multi .leftScroller").unbind('click');
                    $("#tabs-multi .leftScroller").bind('click', function() {
                        multiTabMoveLeft(whole_width);
                    });
                }
                if (multiTabWidth >= whole_width + parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left"))) {
                    $("#tabs-multi .rightScroller").removeClass("rightScrollerActive");
                    $("#tabs-multi .rightScroller").unbind('click');
                }
                $.cookie("multiTabPosition", curDocument + ":" + mLeft, {expires: 365, path: '/'});
            });
}

//--soupatko pro zalozky v multidocumentu - doleva
function multiTabMoveLeft(whole_width) {
    var multiTabWidth = $("#tabs-multi").outerWidth(false);
    var curDocument = $("#entity_uuid").val(); //uuid dokumentu
    var mLeft = parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left")) + 300;
    mLeft = (mLeft > 0) ? 0 : mLeft;
    $("#tabs-multi .tabsHolder-multi")
            .animate(
            {"margin-left": mLeft + "px"},
    200,
            function() {
                if (parseInt($("#tabs-multi .tabsHolder-multi").css("margin-left")) >= 0) {
                    $("#tabs-multi .leftScroller").removeClass("leftScrollerActive");
                    $("#tabs-multi .leftScroller").unbind('click');
                }
                if (multiTabWidth < whole_width) {
                    $("#tabs-multi .rightScroller").addClass("rightScrollerActive");
                    $("#tabs-multi .rightScroller").unbind('click');
                    $("#tabs-multi .rightScroller").bind('click', function() {
                        multiTabMoveRight(whole_width);
                    });
                }
                $.cookie("multiTabPosition", curDocument + ":" + mLeft, {expires: 365, path: '/'});
            })
}

function makeTableHeaderFixed() {
    if (ie7)
        return;
    var table_selector = "table.vivo-table-fixedheader";
    var table = $(table_selector);

    if (!table.length)
        return;

    var first_tr = table.find('tr:eq(0)');
    cloneTableColumns = [];
    var is_cloned = $('#tableClone').length;
    clone = (typeof clone == 'undefined') ? true : clone;
    var first_tr_clone;
    var clone_table;
    if (!is_cloned) { //clone
        first_tr_clone = first_tr.clone(false);
        // zobrazime klon
        table.before("<div id='tableClone'><table class='tableGrid'><thead>" + first_tr_clone.html() + "</thead></table></div>");
    }
    clone_table = $('#tableClone');
    clone_table
            .css({"width": table.parent().width()})
            .find("table").css({"width": table.width()});

    var td_count = first_tr.find('th').length;

    //zjisteni aktualni sirky sloupcu
    for (i = 0; i < td_count; i++) {
        cloneTableColumns.push(table.find("tr:eq(0) th:eq(" + i + ") div").width());
    }

    //nastaveni sirky sloupcu pro #tableClone dle aktualni sirky sloupcu tabulky
    var th_width;
    for (i = 0; i < td_count; i++) {
        th_width = cloneTableColumns[i];
        clone_table.find('th:eq(' + i + ')')
                .css({
            'width': th_width + "px"
        })
                .removeAttr("width");
    }
    $(window).resize(function() {
        makeTableHeaderFixed();
    });
}

$.fn.dropShadow = function() {
    console.log("TODO shadows ...");
    return $(this);
}

$.fn.removeShadow = function() {
    console.log("TODO shadows ...");
    return $(this);
}

function prepareActionData(act) {
    var data = action_param + '=' + act;
    for (var i = 1; i < arguments.length; i++)
        data += '&args[]=' + encodeURIComponent(arguments[i]);
    return data;
}

function action(act) {

    //console.log("TODO action ...");
    //return false;

    if (typeof act === 'function' && typeof jQuery === 'function') {//first argument is callback 
        //jQuery.post( url [, data ] [, success(data, textStatus, jqXHR) ] [, dataType ] )
        jQuery.post(window.location.pathname, {async: 1, act: arguments[1], args: jQuery.makeArray(arguments).slice(2)}, act, 'json');
        return;
    }

    if (typeof act === 'string' && typeof jQuery === 'function') {
        var data = prepareActionData(act, arguments);
    }

    var beforeSend = function() {
    },
            error = function() {
    },
            success = function() {
    },
            complete = function() {
    },
            return_complete = true;

    if (typeof act === 'object' && typeof jQuery === 'function') {
        if (typeof act.beforeSend === "function") {
            beforeSend = act.beforeSend;
        }
        if (typeof act.error === "function") {
            error = act.error;
        }
        if (typeof act.success === "function") {
            success = act.success;
        }
        if (typeof act.complete === "function") {
            complete = act.complete;
        }
        return_complete = false;
        var data = act.data || "";
        console.log(data);
    }

    var result;

    $.ajax({
        url: act.url || window.location.pathname,
        data: data,
        type: act.type || "GET",
        beforeSend: function(jqXHR, settings) {
            beforeSend(jqXHR, settings);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            error(jqXHR, textStatus, errorThrown);
        },
        success: function(res, textStatus, jqXHR) {
            success(res, textStatus, jqXHR);
            result = res;
        },
        complete: function(jqXHR, textStatus) {
            //complete(jqXHR, textStatus);
            //console.log(jqXHR.responseText)
            //eval('result = ' + jqXHR.responseText);
        }
    })

    if (return_complete) {
        return result;
    }
}
/*
//--build layout
function checkHeight() {
    var window_height = $(parent.window).height();
    var window_width = $(parent.window).width();
    var tabs_height = $("#tabs").outerHeight();
    tabs_height = (tabs_height === null || typeof tabs_height == 'undefined') ? 0 : tabs_height;
    var ribbon_height = $(".ribbon-holder").outerHeight();
    ribbon_height = (ribbon_height === null) ? 0 : ribbon_height;
    tabs_height = ribbon_height ? ribbon_height : tabs_height;
    var tabs_multi_iframe_height = $("#tabs-multi-iframe").outerHeight();
    var dialog_height = $(".dialogHeader").outerHeight();
    var finder_height = $("#finder").outerHeight() > 0 ? $("#finder").outerHeight() + 0 : 0;
    var footer_height = $("#footer").outerHeight();
    var default_view = window_height - $("#header").outerHeight() - footer_height - finder_height - 0;
    var message_height = $(".main_message").outerHeight();
    var h1_height = ($("h1.dialogTitle").outerHeight() > 0) ? $("h1.dialogTitle").outerHeight() + 0 : 0;
    var manager_panel_width = $(".manager_panel").width();
    message_height = (message_height === null || typeof message_height == 'undefined') ? 0 : message_height + 0;
    var button_bar = $('#buttons-bar').length ? $('#buttons-bar').outerHeight() : 0;


    $("body").css({"overflow-y": "hidden"});
    $(".explorer_panel").css({"height": default_view - message_height, "overflow-y": "hidden", "overflow-x": "hidden"});
    $(".manager_panel").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 4});

    if (location.pathname == "/system/manager/content-manager/") {
        var body = window_height - $("#header").height() - message_height - h1_height - dialog_height - $("#footer").height() - 3;

        $(".explorer_tree_hitcher").add(".explorer_tree_hitcher_cont").css({"height": body});
        $(".explorer_tree").css({"height": body, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    else {
        $(".explorer_tree_hitcher").add(".explorer_tree_hitcher_cont").css({"height": default_view - message_height});
        $(".explorer_tree").css({"height": default_view - message_height, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    if ($(".manager_panel").width() === null || 0) {//logon screen
        $(".manager_content").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 4, "margin-left": "0px"});
    } else {
        if (location.pathname == "/system/manager/" && ie7) {
            $(".manager_content").css({"height": window_height - message_height - $("#header").height() - $("#footer").height() - 3});
        }
    }

    if (dialog_height > 0) {//page with dialog options
        console.log(default_view + " " + tabs_height + " " + dialog_height + " " + message_height + " " + h1_height + " " + button_bar);
        if (location.pathname != "/system/Editors/browser/" || (location.pathname == "/system/Editors/browser/" && parent.window.location.pathname != "/system/Editors/editor/"))
            $(".tabMainContent").css({"height": default_view - tabs_height -  message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden", "zoom": "1"});
    } else { //page without dialog options
        console.log("b");
        $(".tabContent").css({"height": default_view - tabs_height - message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden"});
        $(".tabMainContent").css({"height": default_view - tabs_height - message_height - h1_height - button_bar, "overflow-y": "auto", "overflow-x": "hidden"});
    }
    ;

    //replicator - osetreni zobrazeni exploreru s iframy pri editaci
    var parent_tabcontent_height = null;
    if (window != parent.window) {
        if (parent_tabcontent_height = parent.window.$(".tabContent").height() && location.pathname != "/system/Editors/browser/") {
            $(".tabMainContent").css({"height": parent_tabcontent_height - tabs_multi_iframe_height - dialog_height - message_height, "overflow-y": "auto", "overflow-x": "hidden", "zoom": "1"});
        }
    }

    //replicator - zobrazeni prehledu replikaci
    if ($("#replication").length) {
        var tab_content_height = $("#replication").parents(".tabMainContent").height();
        $(".tabMainContent").css("overflow-y", "hidden");
        $("#replication").css({
            "width": window_width - manager_panel_width,
            "height": tab_content_height,
            "overflow": "auto"
        });
    }

    //osetreni zobrazeni tabu zabezpeceni - roztahnuti na vysku
    if ($(".leftCell").length && $(".rightCell").length && $(".leftCell .secureDisplay:visible").length) {
        $(".leftCell .secureDisplay, .rightCell .cellContent")
                .css("height", window_height - $(".leftCell .secureDisplay:visible").offset().top - footer_height - 6); //6 - td padding (top + bottom)
    }

    //resize iframe
    $(".viewFrame, .vivoiframe").css({"height": default_view - tabs_height - dialog_height - message_height - 4, "overflow-y": "auto", "overflow-x": "hidden", "border": "0px"});
    $(".vivoiframe").parents(".tabContent").css({"overflow-y": "hidden"});

    //manager help
    $(".manager_content > iframe").css({"width": "100%", "height": window_height - $("#header").height() - $("#footer").height() - 3});

    //site
    $(".site").css({"height": window_height - $("#header").height() - $("#footer").height() - finder_height - message_height - 3});

    //checkInputWithIconWidth();
}
*/



//clock depending on server side time
function startclock() {
    var server_time = new Date($("#current_time_year").text(), $("#current_time_month").text(), $("#current_time_day").text(), $("#current_time_hours").text(), $("#current_time_minutes").text(), $("#current_time_seconds").text());
    //alert(server_time);
    var server_hours = server_time.getHours();
    var server_mins = server_time.getMinutes();
    var server_secs = server_time.getSeconds();
    server_secs++;
    if (server_secs == 60) {
        server_mins++;
        server_secs = 0;
    }
    if (server_mins == 60) {
        server_hours++;
        server_mins = 0;
    }
    if (server_hours == 24) {
        server_hours = 00;
    }
    if (server_mins < 10)
        server_mins = "0" + server_mins;
    if (server_secs < 10)
        server_secs = "0" + server_secs;
    //$("#current_time").html(server_hours+":"+server_mins+":"+server_secs);
    $("#current_time_hours").text(server_hours);
    $("#current_time_minutes").text(server_mins);
    $("#current_time_seconds").text(server_secs);
    setTimeout('startclock()', 1000);
}

//skryti submenu pro siteChooser
function destroySiteChooserSubmenu() {
    $("#siteChooser ul").removeShadow().hide();
}

function initTreeMenu(elm) {
    /* zruseno orezavani delky slova a doplneni tri tecek v treemenu
     if (!ie7)
     $(elm).find("a").each(function() {
     var treeLinkCont = $(this).find("span");
     if (treeLinkCont.height() > treeMenuLineHeight) {
     $(this).attr("title", treeLinkCont.text() + " - " + $(this).attr("title"));
     treeLinkCont.addClass("showMore");
     treeLinkCont.wrapInner($(document.createElement("span")).css({"width" : $(this).width() - 35, "height" : treeMenuLineHeight}));
     }
     });
     */
    //click na obrazek vedle linku vyvola stejnou akci jako samotny klik na link
    var _ico = $(elm).find("img");
    _ico.unbind("click");
    _ico.bind("click", function() {
        var _this_ico = $(this);
        //nebere to next("a"), proto ta obstrukce s parent
        //location.href = _this_ico.next("a").attr("href");
        location.href = _this_ico.parent().find("a").attr("href");
    }).css("cursor", " pointer");

    $(elm).find("div").unbind("click");
    $(elm).find("div").bind("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.cancelBubble = true;

        var _this = $(this);
        var _path = _this.attr("data-path");
        var _params = _this.attr("id").substring(5);

        if (_this.hasClass("expandable-hitarea")) {
            treeMenu(_path, elm, _params, 1);
        } else {
            treeMenu(_path, elm, _params, 0);
        }
    });
}

//vykreslovani tree menu + preloader
function treeMenu(_path, root, _params, act) {
    if (act) {//show new subtree
        //show preloader
        $("#cont-" + _params).after(
                $(document.createElement("span"))
                .attr("class", "preloader")
                .text(" ")
                );
        //get new html content
        //var htmlContent = action($(root).attr("data-action"), _path);
        action({
            data: prepareActionData($("#treeMenu").attr("data-action"), [_path]),
            error: function() {
                $(root).find(".preloader").remove();
            },
            success: function(res) {
                if (res) {
                    //add html content
                    $("#cont-" + _params).after(res);
                    //temp hide html content
                    $("#sub-" + _params).hide();
                    //begin hiding preloader
                    $("#li-" + _params + " span.preloader").slideUp("fast");
                    //show new html content / new subtree menu and call callback
                    $("#sub-" + _params).slideDown("fast", function() {
                        //init action for new html content/new subtree menu
                        initTreeMenu('#sub-' + _params);
                        //destruct preloader
                        $("#li-" + _params + " span.preloader").remove();
                        //check hitcher
                        checkHeight();
                    });
                    //switch expand and collaps classes
                    $("#tree-" + _params).removeClass("expandable-hitarea").addClass("collapsable-hitarea");
                    $("#li-" + _params).removeClass("expandable").addClass("collapsable");

                    if ($("#tree-" + _params).hasClass("last-expandable-hitarea")) {
                        $("#tree-" + _params).removeClass("last-expandable-hitarea").addClass("last-collapsable-hitarea");
                    }
                    else if ($("#tree-" + _params).hasClass("last-collapsable-hitarea")) {
                        $("#tree-" + _params).removeClass("last-collapsable-hitarea").addClass("last-expandable-hitarea");
                    }

                    if ($("#li-" + _params).hasClass("last-expandable")) {
                        $("#li-" + _params).removeClass("last-expandable").addClass("last-collapsable");
                    }
                    else if ($("#li-" + _params).hasClass("last-collapsable")) {
                        $("#li-" + _params).removeClass("last-collapsable").addClass("last-expandable");
                    }
                }
            },
            complete: function(jqXHR, textStatus) {
            }
        });
    } else { //destruct subtree
        $("#sub-" + _params).slideUp("fast", function() {
            $(this).remove();
            //check hitcher
            checkHeight();
        });
        $("#tree-" + _params).addClass("expandable-hitarea").removeClass("collapsable-hitarea");
        $("#li-" + _params).addClass("expandable").removeClass("collapsable");

        if ($("#tree-" + _params).hasClass("last-expandable-hitarea")) {
            $("#tree-" + _params).removeClass("last-expandable-hitarea").addClass("last-collapsable-hitarea");
        }
        else if ($("#tree-" + _params).hasClass("last-collapsable-hitarea")) {
            $("#tree-" + _params).removeClass("last-collapsable-hitarea").addClass("last-expandable-hitarea");
        }

        if ($("#li-" + _params).hasClass("last-expandable")) {
            $("#li-" + _params).removeClass("last-expandable").addClass("last-collapsable");
        }
        else if ($("#li-" + _params).hasClass("last-collapsable")) {
            $("#li-" + _params).removeClass("last-collapsable").addClass("last-expandable");
        }
    }
}

//open tree from iframe
function openTree(_path) {
    var htmlContent = action($("#treeMenu").attr("rel"), 'view');
    $(".treeViewContIn").html(htmlContent);
    initTreeMenu('#treeMenu');
}

//--move window shadow
function shadowWindow(_idWindow, _containment, center) {

    if (typeof containment == "undefined")
        containment = "";
    var center = (typeof center == "undefined") ? true : center;

    if (center) {
        $(_idWindow).css({
            "top": ($(window).height() - $("#header").height() - $("#footer").height() - $(".main_message").height() - $(_idWindow).height()) / 2,
            "left": ($(window).width() - $(".manager_panel").width() - $(_idWindow).width()) / 2
        });
    }

    $(_idWindow).draggable({
        containment: _containment,
        handle: _idWindow + "-header",
        start: function(event, ui) {
            $(_idWindow + "-shadow").css({"width": $(_idWindow).width(), "height": $(_idWindow).height(), "top": $(_idWindow).css("top"), "left": $(_idWindow).css("left")}).show();
        },
        drag: function(event, ui) {
            $(_idWindow + "-shadow").hide();
            //$(_idWindow + "-shadow").css({"width" : $(_idWindow).width(), "height" : $(_idWindow).height(), "top" : $(_idWindow).css("top"), "left" : $(_idWindow).css("left")}).show();
        },
        stop: function(event, ui) {
            $(_idWindow + "-shadow").show();
            $(_idWindow + "-shadow").css({"width": $(_idWindow).width(), "height": $(_idWindow).height(), "top": $(_idWindow).css("top"), "left": $(_idWindow).css("left")}).show();
        }
    });

    $(_idWindow + "-header").css({"cursor": "move"});
    $(_idWindow + "-shadow").css({"width": $(_idWindow).width(), "height": $(_idWindow).height(), "top": $(_idWindow).css("top"), "left": $(_idWindow).css("left")}).show();
}

function setFakeDialogPathWidth() {
    if ($("#fakePathHolder").is(":visible")) {
        var firstItemOnRightSide = $("#firstRDialogItem").offset();
        var fakePath = $("#fakePathHolder .fakePath").offset();
        var newFakePathWidth = firstItemOnRightSide.left - fakePath.left - 30;

        $("#fakePathHolder .fakePath").css({"width": newFakePathWidth + "px"});
    }
}

//datagrid inputs focus
function initTableInputs(_selector) {
    $(_selector + " input").add($(_selector + " select")).add($(_selector + " textarea"))
            .each(function() {
        if ($(this).attr("readonly") && !$(this).hasClass("custom_selectbox_fake")) {
            $(this).addClass("readonly-input").click(function() {
                $(this).blur();
            });
        }
    })
            .focus(function() {
        //if (!$(this).hasClass("file") && !$(this).hasClass("radio") && !$(this).hasClass("checkbox") && !$(this).hasClass("icon") && (!($.browser.msie) || ($.browser.msie && !$(this).is("select")))) {
        $(this).addClass("selected-input");
        //}
    })
            .blur(function() {
        $(this).removeClass("selected-input");
    });
}

/*function checkboxHints(){
 $('.controls .checkbox').each(function(i) {
 var $this = $(this);
 var help = $this.next();
 var div = "<div class='hintBox' id='hintBox-" + i + "'><div class='hintBoxHolder'><div class='hintBoxTop'></div><div class='hintBoxMiddle'><div class='hintBoxMiddleContent'>" + $(help).html() + "</div></div><div class='hintBoxBottom'></div></div></div>";
 
 $this
 .hover(function() {             
 help.wrap(div).show();                         
 },function(){
 help.parent().parent().parent().hide();
 });   
 });
 }*/

//hint (help) box generator
function hintBox() {
    $(".form-horizontal input").add('.icheckbox_minimal').add("select").add(".custom_selectbox_fake").add("textarea").add("div[id^='solmetraUploaderPlaceholder']").add(".withHint").each(function(i) {
        var $this = $(this);
        var help = $this.siblings(".help-block");
        //for inputs with trigger image (datepicker ...) - we have to put hint box after trigger image
        var triggerImage = $this.siblings(".ui-datepicker-trigger");
        if (jQuery.trim($(help).text()) != "") {
            var helpTimeout = new Array();
            $this
                    .bind("mouseover", function() {
                if (showHideHintsFlag) {
                    var _content = (triggerImage.length) ? triggerImage : $this;
                    if (!_content.siblings(".hintBox").length)
                        _content.after("<div class='hintBox' id='hintBox-" + i + "'><div class='hintBoxHolder'><div class='hintBoxTop'></div><div class='hintBoxMiddle'><div class='hintBoxMiddleContent'>" + $(help).html() + "</div></div><div class='hintBoxBottom'></div></div></div>");
                    $("#hintBox-" + i).hide();
                    helpTimeout[i] = setTimeout(function() {
                        $("#hintBox-" + i).hide().fadeIn("def");
                    }, 500);
                }
            })
                    .bind("mouseout", function() {
                if (showHideHintsFlag) {
                    clearTimeout(helpTimeout[i]);
                    $("#hintBox-" + i).fadeOut("fast", function() {
                        $(this).remove();
                    });
                }
            })
                    .bind("focus", function() {
                if (showHideHintsFlag) {
                    clearTimeout(helpTimeout[i]);
                    $("#hintBox-" + i).fadeOut("fast", function() {
                        $(this).remove();
                    });
                }
            })
                    .bind("click", function() {
                if (showHideHintsFlag) {
                    clearTimeout(helpTimeout[i]);
                    $("#hintBox-" + i).fadeOut("fast", function() {
                        $(this).remove();
                    });
                }
            });
        }
    });
}

//refresh ribbon
function refreshRibbon(_path) {
    var htmlContent = action($("#ribbonAction").attr("rel"), 'view');
    $(".ribbon-holder").html(htmlContent);
    ribbonInit();
}

function ribbonInit() {
//inicializace Ribbonu
    var ribbon_holder = $('.manager .ribbon-holder'), show_hide_ribbon_btn, show_hide_ribbon_title_array = [null, null], active_tab, active_tab_id;

    $('.tabContentRibbon .tab-content', ribbon_holder).hide();
    active_tab = ribbon_holder.find('a.active').closest('.tab-content');
    if (!active_tab.length) {
        active_tab = $("#ribbon-tab-tab1");
    }
    active_tab.show();
    ribbon_holder.find('#tabs .tabs li[data-rel="' + active_tab.attr("id") + '"]').addClass('selected');

    if (!ribbon_holder.length) {
        return;
    }
    if ($('a.showHideRibbon', ribbon_holder).length) {
        show_hide_ribbon_btn = $('a.showHideRibbon', ribbon_holder);
        show_hide_ribbon_title_array = [Messages.get('Vivo_UI_Ribbon_min_ribbon'), Messages.get('Vivo_UI_Ribbon_max_ribbon')];
    }

    rollRibbonInit($.cookie("showHideRibbon"), ribbon_holder, show_hide_ribbon_title_array);

    hoverElement('.tabs li', ribbon_holder);

    $('.tabs li.selected', ribbon_holder).on("dblclick", function() {
        var _selected = $(this);
        var sel;
        if (document.selection && document.selection.empty) {
            document.selection.empty();
        }
        else if (window.getSelection) {
            sel = window.getSelection();
            if (sel && sel.removeAllRanges) {
                sel.removeAllRanges();
            }
        }
        rollRibbon(_selected, true, ribbon_holder, show_hide_ribbon_title_array);
    });

    $('.tabs li a', ribbon_holder).click(function() {
        var _selected = $(this);
        rollRibbon(_selected, false, ribbon_holder, show_hide_ribbon_title_array);
    });

    /*
     $('a.showHideRibbon', ribbon_holder).click(function(e) { 
     var _thisIsShow = $(this);
     if (_thisIsShow.hasClass('active')) {
     var _selected = $('.tabs li.selected');
     rollRibbon(_selected, true, ribbon_holder, show_hide_ribbon_title_array);
     }
     else {
     var _selected = $('.tabs li.ref');
     rollRibbon(_selected, false, ribbon_holder, show_hide_ribbon_title_array);
     }
     e.preventDefault();
     });
     */

    $('a.disabled, .tabs li a', ribbon_holder).click(function(e) {
        e.preventDefault();
    });

    $(".tabs li", ribbon_holder).click(function(e) {
        var _this = $(this);
        ribbon_holder.find('#tabs .tabs li').removeClass('selected');
        _this.addClass('selected');
        ribbon_holder.find('.tab-content').hide();
        ribbon_holder.find('#' + _this.attr('data-rel')).show();
    });
    //ajaxove nacitani obsahu ribbonu
    /*
     $(".tabs li a", ribbon_holder).not(".tabs li.selected a").click(function(e) {
     e.preventDefault();
     var _link = $(this);
     var act_select = jQuery.url.setUrl(_link.attr("href")).param("act");
     
     var query_params_select = [jQuery.url.setUrl(_link.attr("href")).param("args[]")];
     var query_select = 'async=1&act=' + act_select;
     for (var i = 0; i < query_params_select.length; i++)
     query_select += '&args[]=' + encodeURIComponent(query_params_select[i]);
     
     var tabContent = "";
     var tab = "";
     var sending = false;
     if (!smc_sending) {
     $.ajax({
     url: window.location.pathname + "?" + query_select,
     type: "POST",
     dataType: "html",
     beforeSend: function(jqXHR, textStatus) {
     smc_sending = true;
     },
     error: function(jqXHR, textStatus, errorThrown) {
     },
     success: function(res, textStatus, jqXHR) {
     if(res && res !== "null") {
     tab = res;
     }
     },
     complete: function(jqXHR, textStatus) {
     if (tab) {
     smc_sending = changeRibbonContent();	
     } else {
     smc_sending = false;
     }
     }
     });
     }
     });	
     
     var changeRibbonContent = function() {
     var act_view = window.tabRibbonContainder.view;
     var query_params = [];
     var query = 'async=1&act=' + act_view;
     for (var i = 0; i < query_params.length; i++)
     query += '&args[]=' + encodeURIComponent(query_params[i]);
     $.ajax({
     url: window.location.pathname + "?" + query,
     type: "POST",
     dataType: "html",
     beforeSend: function(jqXHR, textStatus) {
     smc_sending = true;
     },
     error: function(jqXHR, textStatus, errorThrown) {
     },
     success: function(res, textStatus, jqXHR) {
     if(res && res !== "null") {
     tabContent = res;
     }
     },
     complete: function(jqXHR, textStatus) {
     ribbon_holder.html(tabContent);
     ribbonInit();					
     return false;
     }
     });
     }
     */

}

//inicializace ribbonu (zobrazen/skryt)
function rollRibbonInit(param, ribbon_holder, showHideRibbonTitleArray) {
    if (param == "hide") {
        ribbon_holder.css('height', '35px');
        $('.tabs li.selected', ribbon_holder).removeClass('selected').addClass('ref');
        $('a.showHideRibbon', ribbon_holder).removeClass('active').attr('title', showHideRibbonTitleArray[1]);
        checkHeight();
    }
    else {
        $('a.showHideRibbon', ribbon_holder).attr('title', showHideRibbonTitleArray[0]);
    }
    return;
}

//Zobrazeni/skryti ribbonu
function rollRibbon(_selected, doubleClick, ribbon_holder, showHideRibbonTitleArray) {
    if (doubleClick) {
        ribbon_holder.animate({
            height: '35px'
        },
        200,
                function() {
                    _selected.removeClass('selected').addClass('ref');
                    checkHeight();
                    $.cookie("showHideRibbon", "hide");
                    $('a.showHideRibbon', ribbon_holder).removeClass('active').attr('title', showHideRibbonTitleArray[1]);
                });
        return;
    }
    else {
        _selected.addClass('selected').removeClass('ref');
        $.cookie("showHideRibbon", "show");
        ribbon_holder.animate({
            height: '123px'
        },
        200,
                function() {
                    checkHeight();
                    $('a.showHideRibbon', ribbon_holder).addClass('active').attr('title', showHideRibbonTitleArray[0]);
                });
        return;
    }
}

//fce pro hover efekt na elementech
function hoverElement(selector, context) {
    if (context != null) {
        $(selector, context).hover(function() {
            $(this).addClass("hover");
        }, function() {
            $(this).removeClass("hover");
        });
        return;
    }
    else {
        $(selector).hover(function() {
            $(this).addClass("hover");
        }, function() {
            $(this).removeClass("hover");
        });
    }
}

//choose site ... menu
function siteMenu() {
    var site_chooser = $("#siteChooser");
    site_chooser.find(".site-name").text(site_chooser.find("li.active").text());
    site_chooser.click(function(e) {
        var $ul = $(this).find('ul');
        var $span = $ul.prev();

        if ($ul.is(":visible")) {
            $ul.removeShadow().hide();
        } else {
            $ul.slideDown(100, function() {
                $(this).css({"z-index": "1000"});
                if (!ie7 && !ie8 && !opera) {
                    $(this).removeShadow();
                    $(this).dropShadow({
                        left: 3,
                        top: 3,
                        blur: 2,
                        opacity: .7,
                        color: "#8ba9c8",
                        swap: false
                    });
                }
                //$(this).css({"z-index":1001});
            });
            e.preventDefault();
            e.stopPropagation();
            e.cancelBubble = true;
        }
    }).hover(function() {
        $(this).find("span").addClass("over");
    }, function() {
        $(this).find("span").removeClass("over");
    });
}