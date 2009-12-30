/**********************************************************
Sleight
(c) 2001, Aaron Boodman
http://www.youngpup.net
**********************************************************/

if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent
	&& (navigator.appVersion.indexOf("msie 5") > -1 || navigator.appVersion.indexOf("msie 6") > -1) ) {
    document.writeln('<style type="text/css">img, input.image { visibility:hidden; } </style>');
    window.attachEvent("onload", fnLoadPngs);
}

function fnLoadPngs() {
    var rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
    var itsAllGood = (rslt != null && Number(rslt[1]) >= 5.5);

    for (var i = document.images.length - 1, img = null; (img = document.images[i]); i--) {
        if (itsAllGood && img.src.match(/(.*)\/com_virtuemart\/(.*)\.png$/i) != null) {
            fnFixPng(img);
            img.attachEvent("onpropertychange", fnPropertyChanged);
        }
        img.style.visibility = "visible";
    }

    var nl = document.getElementsByTagName("INPUT");
    for (var i = nl.length - 1, e = null; (e = nl[i]); i--) {
        if (e.className && e.className.match(/\bimage\b/i) != null) {
            if (e.src.match(/\.png$/i) != null) {
                fnFixPng(e);
                e.attachEvent("onpropertychange", fnPropertyChanged);
            }
            e.style.visibility = "visible";
        }
    }
}

function fnPropertyChanged() {
    if (window.event.propertyName == "src") {
        var el = window.event.srcElement;
        if (!el.src.match(/x\.gif$/i)) {
            el.filters.item(0).src = el.src;
            el.src = "x.gif";
        }
    }
}

function dbg(o) {
    var s = "";
    var i = 0;
    for (var p in o) {
        s += p + ": " + o[p] + "\n";
        if (++i % 10 == 0) {
            alert(s);
            s = "";
        }
    }
    alert(s);
}

function fnFixPng(img) {
    var src = img.src;
    img.style.width = img.width + "px";
    img.style.height = img.height + "px";
    img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')";
    img.src = "components/com_virtuemart/shop_image/blank.gif";
}