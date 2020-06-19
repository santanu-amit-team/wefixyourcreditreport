<?php

namespace Admin\Library;

use Application\Config;
use Application\Request;

class RewriteEngine
{

    private static $files = array();

    public static function generate($is_manual = false, $returnStr = false)
    {
        
        self::$files['htaccess'] = BASE_DIR . DS . '.htaccess';
        self::$files['config']   = LAZER_DATA_PATH . 'settings.data.json';

        if (file_exists(self::$files['htaccess']) && !$is_manual) {
            return;
        }

        if (!$returnStr && !is_writable(self::$files['htaccess'])) {
            return;
        }

        try
        {
            if ($is_manual || !file_exists(self::$files['htaccess'])) {
                $htaccessContent = "##### start generated .htaccess code #####\n";
                $htaccessContent .= self::__addModules();
                $htaccessContent .= "<IfModule mod_rewrite.c>\nRewriteRule ^cms/([0-9]+)/[a-z0-9-]+/?$ admin/backend/cms.php?id=$1 [L]\n</IfModule>\n";
                $htaccessContent .= "##### end generated .htaccess code #####\n";
                $htaccessContent .= "\n" . self::__userDefinedCodes();
                
                if($returnStr)
                    return trim($htaccessContent);

                file_put_contents(self::$files['htaccess'], $htaccessContent);

                if ($is_manual) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private static function __userDefinedCodes()
    {

        if (!is_file(self::$files['htaccess'])) {
            return '';
        }

        $htaccess = file_get_contents(self::$files['htaccess']);
        $match    = preg_match('/##### end generated \.htaccess code #####(.+)/s', $htaccess, $matches);
        return isset($matches[1]) && !empty($matches[1]) ? trim($matches[1]) : '';
    }

    private static function __addModules()
    {
        $modules = '';

        $modules .= self::__defaultModules();

        if (Config::settings('force_https')) {
            $modules .= self::__forceSsl(true);
        }

        if (Config::settings('force_www')) {
            $modules .= self::__forceWww(true);
        }

        if (Config::settings('enable_browser_caching')) {
            $modules .= self::__browserCaching();
        }

        if (Config::settings('enable_gzip_compression')) {
            $modules .= self::__gzipCompression();
        }

        return $modules;
    }

    private static function __defaultModules()
    {
        $htaccessPart = "\n##### start generic codes #####\n";
        $htaccessPart .= 'Options +FollowSymlinks -Indexes
DirectoryIndex index.php index.html
AddDefaultCharset utf-8

<files .htaccess>
    order allow,deny
    deny from all
</files>

<FilesMatch ".(doc|pdf)$">
    <ifModule mod_headers.c>
        Header set X-Robots-Tag "noindex, nofollow, noarchive, nosnippet"
    </ifModule>    
</FilesMatch>

<FilesMatch "robots.txt">
    <ifModule mod_headers.c>
        Header set X-Robots-Tag "noindex"
    </ifModule>    
</FilesMatch>

<FilesMatch "sitemap.xml">
    <ifModule mod_headers.c>
        Header set X-Robots-Tag "noindex"
    </ifModule>    
</FilesMatch>

<IfModule mod_rewrite.c>
RewriteEngine On
</IfModule>
<ifModule mod_headers.c>
    Header set Connection keep-alive
</ifModule>';
        $htaccessPart .= "\n##### end generic codes #####\n";

        return $htaccessPart;
    }

    private static function __forceSsl($force = true)
    {
        $htaccessPart = "\n##### start force ssl/non-ssl #####\n";
        $htaccessPart .= "<IfModule mod_rewrite.c>\n";
        //$htaccessPart .= 'RewriteCond %{HTTP_HOST} !^.*\..*\. [NC]' . "\n";
        //$htaccessPart .= 'RewriteCond %{HTTP_HOST} ^www\. [NC]' . "\n";
        $htaccessPart .= 'RewriteCond %{HTTP_USER_AGENT} !cloudfront [NC]' . "\n";

        if ($force) {

            if (Request::headers()->get('HTTP_CF_RAY')) {
                $htaccessPart .= "RewriteCond %{HTTP:CF-Visitor} '\"scheme\":\"http\"'\n";
            } else {
                $htaccessPart .= Config::settings('force_https_based_on_env') ? "RewriteCond %{ENV:HTTPS} !=on [NC]\n" : "RewriteCond %{HTTPS} !=on [NC]\n";
            }

            $htaccessPart .= 'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
        } else {

            if (Request::headers()->get('HTTP_CF_RAY')) {
                $htaccessPart .= "RewriteCond %{HTTP:CF-Visitor} '\"scheme\":\"https\"'\n";
            } else {

                $htaccessPart .= Config::settings('force_https_based_on_env') ? "RewriteCond %{ENV:HTTPS} =on [NC]\n" : "RewriteCond %{HTTPS} =on [NC]\n";
            }

            $htaccessPart .= 'RewriteRule ^ http://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
        }

        $htaccessPart .= "\n</IfModule>";

        $htaccessPart .= "\n##### end force ssl/non-ssl #####\n";

        return $htaccessPart;
    }

    private static function __forceWww($force = true)
    {
        $htaccessPart = "\n##### start force www/non-www #####\n";
        $htaccessPart .= "<IfModule mod_rewrite.c>\n";

        if ($force) {
            $htaccessPart .= 'RewriteCond %{HTTP_USER_AGENT} !cloudfront [NC]' . "\n";
            $htaccessPart .= "RewriteCond %{HTTP_HOST} !^www\. [NC]\n";

            if (Config::settings('force_https')) {
                $htaccessPart .= 'RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
            } else {
                $htaccessPart .= 'RewriteRule ^ http://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
            }
        } else {
            $htaccessPart .= "RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]\n";

            if (Config::settings('force_https')) {
                $htaccessPart .= 'RewriteRule ^(.*)$ https://%1%{REQUEST_URI} [R=301,L]';
            } else {
                $htaccessPart .= 'RewriteRule ^(.*)$ http://%1%{REQUEST_URI} [R=301,L]';
            }
        }

        $htaccessPart .= "\n</IfModule>";
        $htaccessPart .= "\n##### end force www/non-www #####\n";

        return $htaccessPart;
    }

    private static function __browserCaching()
    {
        $htaccessPart = "\n##### start browser caching #####\n";
        $htaccessPart .= '<IfModule mod_mime.c>
    AddType text/css .css
    AddType application/x-javascript .js
    AddType text/x-component .htc
    AddType text/html .html .htm
    AddType text/richtext .rtf .rtx
    AddType image/svg+xml .svg .svgz
    AddType text/plain .txt
    AddType text/xsd .xsd
    AddType text/xsl .xsl
    AddType text/xml .xml
    AddType video/asf .asf .asx .wax .wmv .wmx
    AddType video/avi .avi
    AddType image/bmp .bmp
    AddType application/java .class
    AddType video/divx .divx
    AddType video/webm .webm
    AddType application/msword .doc .docx
    AddType application/vnd.ms-fontobject .eot
    AddType application/x-msdownload .exe
    AddType image/gif .gif
    AddType application/x-gzip .gz .gzip
    AddType image/x-icon .ico
    AddType image/jpeg .jpg .jpeg .jpe
    AddType application/vnd.ms-access .mdb
    AddType audio/midi .mid .midi
    AddType video/quicktime .mov .qt
    AddType audio/mpeg .mp3 .m4a
    AddType video/mp4 .mp4 .m4v
    AddType video/mpeg .mpeg .mpg .mpe
    AddType application/vnd.ms-project .mpp
    AddType application/x-font-otf .otf
    AddType application/vnd.oasis.opendocument.database .odb
    AddType application/vnd.oasis.opendocument.chart .odc
    AddType application/vnd.oasis.opendocument.formula .odf
    AddType application/vnd.oasis.opendocument.graphics .odg
    AddType application/vnd.oasis.opendocument.presentation .odp
    AddType application/vnd.oasis.opendocument.spreadsheet .ods
    AddType application/vnd.oasis.opendocument.text .odt
    AddType audio/ogg .ogg .ogv
    AddType application/pdf .pdf
    AddType image/png .png
    AddType application/vnd.ms-powerpoint .pot .pps .ppt .pptx
    AddType audio/x-realaudio .ra .ram
    AddType application/x-shockwave-flash .swf
    AddType application/x-tar .tar
    AddType image/tiff .tif .tiff
    AddType application/x-font-ttf .ttf .ttc
    AddType application/x-font-woff .woff
    AddType audio/wav .wav
    AddType audio/wma .wma
    AddType application/vnd.ms-write .wri
    AddType application/vnd.ms-excel .xla .xls .xlsx .xlt .xlw
    AddType application/zip .zip
    AddType application/json .json
</IfModule>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<FilesMatch "\.(php|xml|js|css|json)$">
    FileETag None
    BrowserMatch MSIE best-standards-support
    AddDefaultCharset utf-8
    AddLanguage en-US .css .js

    <IfModule mod_mime.c>
        AddCharset utf-8 .php .xml .css .js .json
    </IfModule>

    <IfModule mod_headers.c>
        Header unset ETag
        Header unset Pragma
        Header set X-UA-Compatible IE=8 env=best-standards-support
        Header append Vary: Accept-Encoding
    </IfModule>
</FilesMatch>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<FilesMatch "\.(ttf|otf|eot|woff)$">
    FileETag None
    AddDefaultCharset utf-8

    <IfModule mod_headers.c>
        Header unset ETag
        Header unset Pragma
        Header set Cache-Control "max-age=31536000, public, must-revalidate"
        Header append Access-Control-Allow-Origin "http://cloudfront.net"
    </IfModule>
</FilesMatch>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<FilesMatch "\.(jpg|jpeg|png|gif|swf)$">
    FileETag None

    <IfModule mod_headers.c>
        Header unset ETag
        Header unset Pragma
        Header set Cache-Control "max-age=31536000, public, must-revalidate"
    </IfModule>
</FilesMatch>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<FilesMatch "\.(js|css|jpg|png|jpeg|gif|xml|json|txt|pdf|mov|avi|otf|woff|ico|swf)$">
    <ifModule mod_headers.c>
        RequestHeader unset Cookie
        Header unset Cookie
        Header unset Set-Cookie
    </IfModule>    
</FilesMatch>';
        
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<FilesMatch "\.(html|htm|php)>
    FileETag None
    <ifModule mod_headers.c>
        Header unset ETag
        Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
        Header set Pragma "no-cache"
        Header set Expires "Wed, 11 Jan 1984 05:00:00 GMT"
    </IfModule>    
</FilesMatch>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<IfModule mod_expires.c>
    ExpiresActive On

    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"

    ExpiresByType audio/ogg "access plus 1 year"
    ExpiresByType video/ogg "access plus 1 year"
    ExpiresByType video/mp4 "access plus 1 year"
    ExpiresByType video/webm "access plus 1 year"

    ExpiresByType text/css "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType text/x-javascript "access plus 1 year"
    ExpiresByType application/x-javascript "access plus 1 year"

    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType application/rss+xml "access plus 1 year"
    ExpiresByType application/atom+xml "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 year"
    ExpiresByType application/x-shockwave-flash "access plus 1 year"
    ExpiresByType application/x-font-ttf "access plus 1 year"
    ExpiresByType application/x-font-woff "access plus 1 year"
    ExpiresByType application/vnd.ms-fontobject "access plus 1 year"

    ExpiresByType font/opentype "access plus 1 year"
</IfModule>';
        $htaccessPart .= "\n##### end browser caching #####\n";

        return $htaccessPart;
    }

    private static function __gzipCompression()
    {
        $htaccessPart = "\n##### start gzip/deflate compression #####\n";
        $htaccessPart .= '<IfModule mod_deflate.c>
    AddOutputFilter DEFLATE js css json
    <IfModule mod_setenvif.c>
        BrowserMatch ^Mozilla/4 gzip-only-text/html
        BrowserMatch ^Mozilla/4\.0[678] no-gzip
        BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
        BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
    </IfModule>
    <IfModule mod_headers.c>
        Header append Vary User-Agent env=!dont-vary
    </IfModule>
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE text/css application/x-javascript text/x-component text/html text/richtext image/svg+xml text/plain text/xsd text/xsl text/xml image/x-icon application/json
    </IfModule>
</IfModule>';
        $htaccessPart .= "\n\n";
        $htaccessPart .= '<IfModule mod_gzip.c>
    mod_gzip_on Yes
    mod_gzip_dechunk Yes
    mod_gzip_item_include file \.(html?|txt|css|js|php|pl|json)$
    mod_gzip_item_include handler ^cgi-script$
    mod_gzip_item_include mime ^text/.*
    mod_gzip_item_include mime ^application/x-javascript.*
    mod_gzip_item_include mime ^application/json
    mod_gzip_item_exclude mime ^image/.*
    mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</IfModule>';
        $htaccessPart .= "\n##### end gzip/deflate compression #####\n";

        return $htaccessPart;
    }

}
