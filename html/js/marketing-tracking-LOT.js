// Global variables
var digitalDataObj = null;
var digitalData = null;
var tokens = document.domain.split('.');//[www,name,com]
var domain = tokens[tokens.length - 2];

//Creates Brand ID
if (domain == 'lexingtonlaw') {
    var brand = 'LEX';
} else if (domain == 'creditrepair') {
    var brand = 'CR';
} else if (domain == 'lexontrack') {
    var brand = 'LOT';
}

// A date format
Date.prototype.ymd = function () {
    var yyyy = this.getFullYear().toString();
    var mmInt = this.getMonth() + 1;
    var mm = (mmInt < 10) ? '0' + mmInt.toString() : mmInt.toString();
    var ddInt = this.getDate();
    var dd = (ddInt < 10) ? '0' + ddInt.toString() : ddInt.toString();
    return mm + dd + yyyy;
};

//Sets, gets, and destroys cookies.
var Cookie =
    {
        set: function (name, value) {
            var expires;

            var days = 365;
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + ";path=/;domain=" + Environment.domain + "." + Environment.tld;
        },
        get: function (name) {
            var nameEQ = encodeURIComponent(name) + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
            return '';
        },
        destroy: function (name) {
            createCookie(name, "", -1);
        }
    };

//Gets query parameter values.
var Query =
    {
        get: function (name) {
            url = decodeURIComponent(window.location.href.toLowerCase().replace(/\+/g, " "));
            name = name.replace(/[\[\]]/g, "\\$&").toLowerCase();
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) {
                return '';
            }
            if (!results[2]) {
                return '';
            }
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }
    };

//Creates environment specific variables to be used elsewhere.
var Environment =
    {
        name: null,
        brand: null,
        pageType: 'Blog',
        domain: null,
        tld: null,
        protocol: null,
        ipAddressUrl: null,
        trackingUrl: null,
        pixelUrl: null,

        init: function () {
            this.setBrand();
            this.setName();
            this.setDomain();
            this.setTld();
            this.setProtocol();
            this.setUrls();
            this.setCookiesFromURLQuery();
        },
        setName: function () {
            this.name = 'production';
            if (window.location.host.match(/localhost/) || window.location.host.match(/vwsl\-aem.\-d/) || window.location.host.match(/\.dev/)) {
                this.name = 'development';
            } else if (window.location.host.match(/vwsl\-aem.\-t/) || window.location.host.match(/\.test/)) {
                this.name = 'testing';
            } else if (window.location.host.match(/vwsl\-aem.\-[b|s]/) || window.location.host.match(/\.stg/)) {
                this.name = 'staging';
            } else if (window.location.host.match(/vwsl\-aema\-a01\:4502/)) {
                this.name = 'aem-preview-mode';
            }
        },
        setDomain: function () {
            var tokens = document.domain.split('.');//[www,name,com]
            this.domain = tokens[tokens.length - 2];
        },
        setBrand: function () {
            this.brand = 'CRX';
            if (domain == 'lexingtonlaw') {
                this.brand = 'LEX';
            } else if (domain == 'creditrepair') {
                this.brand = 'CR';
            } else if (domain == 'lexontrack') {
                this.brand = 'LOT';
            }
        },
        setTld: function () {
            this.tld = 'com';
            if (this.name == 'development') {
                this.tld = 'test';
            } else if (this.name == 'testing') {
                this.tld = 'test';
            } else if (this.name == 'staging') {
                this.tld = 'stg';
            }
        },
        setProtocol: function () {
            this.protocol = 'http://';
            if (this.name == 'staging' || this.name == 'production') {
                this.protocol = 'https://';
            }
        },
        setUrls: function () {
            if (domain == 'lexontrack') {
                this.ipAddressUrl = this.protocol + 'app.' + 'lexingtonlaw' + '.' + this.tld + '/services/visitor/get_ip_address';
                this.trackingUrl = this.protocol + 'app.' + 'lexingtonlaw' + '.' + this.tld + '/services/v2/aem_tracking';
                this.pixelUrl = this.protocol + 'app.' + 'lexingtonlaw' + '.' + this.tld + '/services/aem_pixels';
            } else {
                this.ipAddressUrl = this.protocol + 'app.' + domain + '.' + this.tld + '/services/visitor/get_ip_address';
                this.trackingUrl = this.protocol + 'app.' + domain + '.' + this.tld + '/services/v2/aem_tracking';
                this.pixelUrl = this.protocol + 'app.' + domain + '.' + this.tld + '/services/aem_pixels';
            }
        },
        setCookiesFromURLQuery: function () {
            var pid = Query.get('pid');
            if (pid == '') {
                pid = Query.get('cj_pid');
            }
            if (pid != '') {
                Cookie.set('pid', pid);
            }
            var aid = Query.get('aid');
            if (aid != '') {
                Cookie.set('aid', aid);
            }
            var sid = Query.get('sid');
            if (sid != '') {
                Cookie.set('sid', sid);
            }
            var cjCookie = Cookie.get('cj_cookie')
            if (!cjCookie && pid && aid && sid) {
                Cookie.set('cj_cookie', JSON.stringify([aid, sid, pid]));
            }
        }
    };

var ServerInfo =
    {
        userAgent: null,
        referrer: null,
        ip: null,

        init: function () {
            this.userAgent = navigator.userAgent;
            this.referrer = document.referrer;
            $.ajax({
                url: Environment.ipAddressUrl,
                type: 'get',
                dataType: 'json',
                success: function (result) {
                    if (typeof result.visitor_ip != "undefined") {
                        ServerInfo.ip = result.visitor_ip;
                    } else {
                        console.log('ServerInfo IP Error...');
                    }
                    TrackingService.run(ServerInfo);
                },
                error: function () {
                    console.log('ServerInfo Error...');
                }
            });
        }
    };

var TrackingService =
    {
        tid: null,
        channel: null,
        number: null,
        run: function () {
            var visitor = Visitor.getInstance("931A1CFE532956FE0A490D45@AdobeOrg");
            var requestBody = {
                cookie_tid: Cookie.get('cookieTID'),
                referrer_url: ServerInfo.referrer,
                userAgent: ServerInfo.userAgent,
                ipAddress: ServerInfo.ip,
                current_url: window.location.href,
                entry_url: document.location.origin,
                device_type: Cookie.get('dtmDevice'),
                visitor_id: visitor.getMarketingCloudVisitorID()
            };
            $.ajax({
                url: Environment.trackingUrl,
                type: 'post',
                dataType: 'json',
                data: JSON.stringify(requestBody),
                success: function (result) {
                    if (Query.get('tid') != '') {
                        TrackingService.tid = Query.get('tid');
                    } else if (Cookie.get('tid') != '') {
                        TrackingService.tid = Cookie.get('cookieTID');
                    } else if (Cookie.get('cookieTID') != '') {
                        TrackingService.tid = Cookie.get('cookieTID');
                    } else {
                        TrackingService.tid = result.tid;
                    }
                    TrackingService.channel = result.campaign.manager;
                    TrackingService.number = result.phone_number;
                    TrackingService.unique_id = result.uid;
                    $("#phoneNumber").val(result.phone_number);
                    $("#tidBox").text(TrackingService.tid);
                    $("#tidBox1").text(TrackingService.tid);
                    $(".phoneNumber").text(result.phone_number).closest("a").attr("href", "tel:1-" + result.phone_number);
                    $(".pgxTid").text(TrackingService.tid).closest("a").attr("href", TrackingService.tid);
                    $('[data-service="get-phone-number"]').find('.phoneNumber').html(result.phone_number);

                    Cookie.set('orig_visit', JSON.stringify({url: window.location.href, ref: ServerInfo.referrer}));
                    Cookie.set('visitor_id', requestBody.visitor_id);
                    Cookie.set('cookieTID', TrackingService.tid);
                    Cookie.set('tid', TrackingService.tid);
                    Cookie.set('s_eVar8', result.phone_number);
                    Cookie.set('channel', result.campaign.manager);
                    Cookie.set('unique_id', result.uid);

                    var footerID = document.getElementById('footer_tid');
                    if (footerID !== null) {
                        footerID.textContent = '(' + TrackingService.tid + ')';
                    }

                    digitalDataObj.setParam('page.tid', TrackingService.tid);
                    digitalData = digitalDataObj.build();

                    PixelService.run();
                    //console.log(Cookie.get('cookieTID'));
                },
                error: function () {
                    console.log('TrackingService Error');
                }
            });
        }
    };
var PixelService =
    {
        replacements: null,

        run: function () {
            $.ajax({
                url: Environment.pixelUrl + '?tid=' + TrackingService.tid + '&url=' + encodeURIComponent(window.location.href) + '&page_type=' + Environment.pageType,
                type: 'get',
                dataType: 'json',
                success: function (result) {
                    if (result.pixels != undefined && Array.isArray(result.pixels) && result.pixels.length > 0) {
                        var pixelId = '';
                        if (result.pixels[0].pixel_id != undefined) {
                            pixelId = result.pixels[0].pixel_id;
                        }
                        PixelService.setReplacements(pixelId);
                        PixelService.findAndReplace(PixelService.replacements, result);
                        if (result.pixels != undefined) {
                            pixelInjectionCode = '';
                            for (i = 0; i < result.pixels.length; i++) {
                                pixelInjectionCode += result.pixels[i].html;
                            }
                            var pixelElement = $('<div />', {'style': 'position:absolute; height:0; text-indent:100%; white-space:nowrap; overflow:hidden;'});
                            $('body').append(pixelElement.append(pixelInjectionCode));
                        }
                    }
                },
                error: function () {
                    console.log('PixelService error...');
                }
            });
        },
        setReplacements: function (pixelId) {
            var cjCookie = Cookie.get('cj_cookie');
            if (cjCookie != '') {
                cjCookie = JSON.parse(decodeURIComponent(cjCookie));
            }
            var tidParts = TrackingService.tid.split('.');
            var sid = PixelService.getPixelReplacementValue('sid');
            if (sid == '' && typeof cjCookie == "object" && cjCookie[0] != undefined) {
                sid = cjCookie[0];
            }
            var aid = PixelService.getPixelReplacementValue('aid');
            if (aid == '' && typeof cjCookie == "object" && cjCookie[1] != undefined) {
                aid = cjCookie[0];
            }
            PixelService.replacements = [
                {search: '%PID%', replaceWith: PixelService.getPixelReplacementValue('pid')},
                {search: '%VID%', replaceWith: PixelService.getPixelReplacementValue('vid')},
                {search: '%TID%', replaceWith: TrackingService.tid},
                {search: '%SID%', replaceWith: sid},
                {search: '%AID%', replaceWith: aid},
                {search: '%CAMPAIGN%', replaceWith: tidParts[0] != undefined ? tidParts[0] : ''},
                {search: '%SUBCAMPAIGN%', replaceWith: tidParts[2] != undefined ? tidParts[2] : ''},
                {search: '%REFCODE%', replaceWith: PixelService.getPixelReplacementValue('LXrefcode')},
                {
                    search: '%UNIQUE_ID%',
                    replaceWith: PixelService.getPixelReplacementValue('LXrefcode').replace('-', '')
                },
                {search: '%RAND%', replaceWith: Math.floor((Math.random() * 100000) + 1)},
                {search: '%TIME%', replaceWith: Math.floor(Date.now() / 1000)},
                {search: '%MDY%', replaceWith: new Date().ymd()},
                {search: '%MMDDYY%', replaceWith: new Date().ymd()},
                {search: '%PIXEL_ID%', replaceWith: pixelId}
            ];
        },
        getPixelReplacementValue: function (name) {
            var queryValue = Query.get(name);
            var cookieValue = Cookie.get(name);
            return queryValue == '' ? cookieValue : queryValue;
        },
        findAndReplace: function (replacements, object) {
            for (var key in object) {
                if (typeof object[key] == 'object') {
                    this.findAndReplace(replacements, object[key]);
                } else {
                    for (i = 0; i < replacements.length; i++) {
                        if (replacements[i].replaceWith != null && replacements[i].replaceWith != '') {
                            object[key] = object[key].replace(new RegExp(replacements[i].search, 'g'), replacements[i].replaceWith);
                        }
                    }
                }
            }
        }
    };
var DigitalData = function () {
    this.pageType = 'Blog';
    this.pagePrefix = brand;
    this.pageName = null;
    this.urlPathPieces = null;
    this.params = {};
    this.build = function () {
        this.buildUrlPathPieces();
        this.buildPageName();
        this.setParam('page.category.pageType', this.pageType);
        this.setParam('page.category.primaryCategory', (this.urlPathPieces[0] === undefined) ? "n\/a" : this.urlPathPieces[0]);
        this.setParam('page.category.subCategory1', (this.urlPathPieces[1] === undefined) ? "n\/a" : this.urlPathPieces[1]);
        this.setParam('page.category.subCategory2', (this.urlPathPieces[2] === undefined) ? "n\/a" : this.urlPathPieces[2]);
        this.setParam('page.category.subCategory3', (this.urlPathPieces[3] === undefined) ? "n\/a" : this.urlPathPieces[3]);
        this.setParam('page.category.brand', this.pagePrefix);
        this.setParam('page.pageInfo.pageName', this.pageName);
        this.setParam('page.traffic.pageType', this.pageType);
        return this.params;
    };
    this.buildUrlPathPieces = function () {
        this.urlPathPieces = window.location.pathname.split('/');
        if (this.urlPathPieces[0] === '') {
            this.urlPathPieces.shift();
            this.urlPathPieces.filter(function (value) {
                return value;
            }); // Re-index the array
        }
    };
    this.buildPageName = function () {
        if (this.urlPathPieces[0] !== '') {
            this.pageName = this.pagePrefix + ":" + this.urlPathPieces.join(":");
        } else {
            this.pageName = this.pagePrefix + ":index";
        }
    };
    this.setParam = function (key, value) {
        var pointer = this.params;
        var keys = key.split('.');
        var keyLength = keys.length;
        for (i = 0; i < (keyLength - 1); i++) {
            if (!pointer[keys[i]]) {
                pointer[keys[i]] = {};
            }
            pointer = pointer[keys[i]];
        }
        pointer[keys[keyLength - 1]] = value;
    };
};

function allCallbacks(){
	Environment.init();
	digitalDataObj = new DigitalData();
	ServerInfo.init();
}

function adobeLaunchAvailable(callback) {
	if (typeof _satellite === 'undefined') {
		setTimeout(function () {
			adobeLaunchAvailable(callback);
		}, 100); // wait 100 ms
	} else {
		callback();
	}
}

$(function(){
	// Initialize the environment variables.
	adobeLaunchAvailable(allCallbacks)
});