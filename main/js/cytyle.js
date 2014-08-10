/* Cytyle - iOS Interface Cascading Style Sheet
 * Copyright (C) 2007-2014  Jay Freeman (saurik)
*/

(function() {
    var uncytyle = function(e, d) {
        e.className = e.className.replace(new RegExp('(\\s|^)' + d + '(\\s|$)'), ' ');
    };

    var find = function(e) {
        for (var item = e.target; item != null && item.nodeName != 'A'; item = item.parentNode);
        if (item != null && item.href == '')
            return null;
        return item;
    };

    if ('ontouchstart' in document.documentElement) {
        document.addEventListener('DOMContentLoaded', function() {
            FastClick.attach(document.body);

            document.addEventListener('click', function(e) {
                var item = find(e);
                if (item == null)
                    return;

                if (typeof cydia != 'undefined')
                    if (item.href.substr(0, 32) == 'http://cydia.saurik.com/package/')
                        item.href = 'cydia://package/' + item.href.substr(32);

                item.className += ' cytyle-dn';
                uncytyle(item, 'cytyle-in');
            });
        }, false);

        var timeout = null;
        var clear = function() {
            if (timeout == null)
                return;
            clearTimeout(timeout);
            timeout = null;
        };

        document.addEventListener('touchstart', function(e) {
            var item = find(e);
            if (item == null)
                return;

            uncytyle(item, 'cytyle-up');
            timeout = setTimeout(function() {
                if (timeout != null)
                    item.className += ' cytyle-in';
            }, 50);
        });

        var stop = function(e) {
            var item = find(e);
            if (item == null)
                return;

            clear();
            uncytyle(item, 'cytyle-in');
        };

        document.addEventListener('touchmove', stop);
        document.addEventListener('touchend', stop);
    } else {
        document.addEventListener('mousedown', function(e) {
            var item = find(e);
            if (item == null)
                return;

            uncytyle(item, 'cytyle-up');
            item.className += ' cytyle-in';
        });

        var stop = function(e) {
            var item = find(e);
            if (item == null)
                return;

            uncytyle(item, 'cytyle-in');
        };

        document.addEventListener('mousemove', stop);
        document.addEventListener('mouseup', stop);
    }

    document.addEventListener("CydiaViewWillAppear", function() {
        var items = document.getElementsByClassName('cytyle-dn');
        for (var i = items.length, e = 0; i != e; --i) {
            var item = items.item(i - 1);
            uncytyle(item, 'cytyle-in');
            item.className += ' cytyle-up';
            uncytyle(item, 'cytyle-dn');
        }
    });
})();

if (navigator.userAgent.search(/Cydia/) == -1)
    document.write('<base target="_top"/>');
else {
    document.write('<style type="text/css"> body.pinstripe { background: none; } </style>');
    document.write('<base target="_blank"/>');
}

// XXX: this might just fail on Chrome everywhere, even Mac :(
// https://code.google.com/p/chromium/issues/detail?id=168646
if (navigator.userAgent.search(/Linux/) != -1)
    document.write('<style type="text/css"> p { text-rendering: optimizeSpeed !important; } </style>');

(function() {
    var update = function() {
        if (window.parent != window)
            parent.postMessage({cytyle: {name: "iframe-y", value: document.body.scrollHeight}}, "*");
    };

    window.addEventListener('message', function(event) {
        var message = event.data.cytyle;
        if (message == undefined)
            return;

        switch (message.name) {
            case "iframe-y":
                var height = message.value;
                var iframes = document.getElementsByTagName("iframe");
                if (iframes.length != 1)
                    return;
                var iframe = iframes.item(0);
                iframe.style.height = height + 'px';
                update();
            break;
        }
    }, false);

    window.addEventListener('load', update, false);
})();

(function() {
    var full = 16;

    var text = document.createElement("span");
    text.style.fontFamily = '"Helvetica", "Arial"';
    text.style.fontSize = full + "px";
    text.appendChild(document.createTextNode("My"));

    var block = document.createElement("div");
    block.style.display = "inline-block";
    block.style.height = "0px";
    block.style.width = "1px";
    block.style.verticalAlign = "baseline";

    var div = document.createElement("div");
    div.style.lineHeight = "normal";
    div.appendChild(text);
    div.appendChild(block);

    var body = document.documentElement;
    body.appendChild(div); try {
        var base = block.offsetTop - text.offsetTop;
        // XXX: on iOS 3 I am unable to do this?
        if (base == 0)
            base = 14;
    } finally {
        body.removeChild(div);
    }

    var down = (full - base) / full / 2;
    //alert(down + "em = (" + full + " - " + base + ") / " + full + " / 2");

    //var over = 4.0; // Modern
    //var over = 2.5; // Legacy
    //var over = 3.5; // Chrome
    //var over = 3.0; // Medium
    //var desc = full * 0.25;
    //var down = (desc - over) / full;

    document.write('<style type="text/css"> p, input[type="password"], input[type="text"], select { top: ' + down + 'em; } </style>');
})();
