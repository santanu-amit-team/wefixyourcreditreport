<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Application\Helper\Provider;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Extension\SplitTest\Helper;

class SplitTestController
{

    function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table = array(
            'name' => 'splittest',
            'attr' => array(
                'id' => 'integer',
                'config_id' => 'integer',
                'experiment_name' => 'string',
                'selectedSplitTestType' => 'string',
                'splitTypesData' => 'string',
                'created_at'=> 'string',
                'selectedPageType' => 'string',
                'last_modified' => 'string',
            ),
        );

        try
        {
            Validate::table($this->table['name'])->exists();
        }
        catch (LazerException $ex)
        {
            Database::create(
                    $this->table['name'], $this->table['attr']
            );
        }
    }

    public function all($campaignType = '', $conversionData = true)
    {
        try
        {
            $orderByField = Request::form()->get('orderByField');
            $orderBy = Request::form()->get('orderBy');

            if (empty($orderByField) || empty($orderBy))
            {
                $orderByField = 'id';
                $orderBy = 'DESC';
            }

            if (!empty($campaignType))
            {
                $query = Database::table($this->table['name'])
                        ->where('campaign_type', '=', $campaignType)
                        ->orderBy($orderByField, $orderBy);
            }
            else
            {
                $query = Database::table($this->table['name'])
                        ->orderBy($orderByField, $orderBy);
            }

            $totalRows = Database::table($this->table['name'])
                            ->findAll()->count();

            if (Request::form()->get('limit') == 'all')
            {
                $data = $query
                                ->findAll()->asArray();
            }
            else if (Request::form()->has('offset', 'limit'))
            {
                $data = $query
                                ->limit(Request::form()->get('limit'), Request::form()->get('offset'))
                                ->findAll()->asArray();
            }
            else
            {
                $data = $query
                                ->findAll()->asArray();
            }
            if ($conversionData && !empty($data))
            {
                foreach ($data as $key => $dataValue)
                {
                    $last_modified_formated = isset($dataValue['last_modified']) ? date('M j, Y', strtotime($dataValue['last_modified'])) : '';
                    $data[$key]['last_modified_formated'] = $last_modified_formated;
                    $data[$key]['view_data'] = Helper::getDataForList($dataValue['id'],$dataValue['selectedSplitTestType'], $dataValue['splitTypesData']);
                }
            }

            return array(
                'success' => true,
                'data' => $data,
                'totalData' => (int) $totalRows,
            );
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function add()
    {
        $data = array();

        try
        {
            $this->validateData();
            if (Request::form()->get('selectedSplitTestType') == "content-ab-testing")
            {
                $this->uploadEncodedFile();
            }

            $row = Database::table($this->table['name']);

            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                
                if ($key == 'created_at')
                {
                    $valueGet = date("Y-m-d H:i:s");
                }
                else
                {

                    $valueGet = $this->filterInput($key);
                }

                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
                }
                $data[$key] = $row->{$key} = $valueGet;
            }

            if ($this->isValidData($row))
            {
                $row->save();
                $this->reactivateExtension();
                return array(
                    'success' => true,
                    'data' => $data,
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function uploadEncodedFile()
    {
        Request::form()->all();
        $splitTypesData = json_decode(Request::form()->get('splitTypesData'), true);

        $filePath = dirname(dirname(dirname(__FILE__))) . DS . 'assets' . DS .
                'images' . DS . 'splittest' . DS;
        if (!file_exists($filePath))
        {
            mkdir($filePath, 0755, true);
        }
        if (!empty($splitTypesData))
        {
            foreach ($splitTypesData as $key => $value)
            {
                if (!empty($value['encodedFile']))
                {
                    $decoded_file = base64_decode($value['encodedFile']); // decode the file

                    if (!extension_loaded('fileinfo')) {
                        $mime_type = "image/jpeg";
                    }
                    else {
                         $mime_type = finfo_buffer(finfo_open(), $decoded_file, FILEINFO_MIME_TYPE); // extract mime type
                    }
                    
                    $extension = $this->mime2ext($mime_type); // extract extension from mime type
                    $fileName = uniqid() . '.' . $extension;
                    $file_dir = $filePath . $fileName;

                    file_put_contents($file_dir, $decoded_file); // save
                    $splitTypesData[$key]['fileName'] = empty($fileName) ? null : $fileName;
                    unset($splitTypesData[$key]['encodedFile']);
                }
            }

            Request::form()->set('splitTypesData', json_encode($splitTypesData));
            return true;
        }
    }

    public function unlinkImage($unlinkImages)
    {

        $filePath = dirname(dirname(dirname(__FILE__))) . DS . 'assets' . DS .
                'images' . DS . 'splittest' . DS;
        $unlinked = array();
        if (!empty($unlinkImages))
        {
            foreach (array_values($unlinkImages) as $key => $value)
            {
                try
                {
                    $unlinked[$value]['success'] = unlink($filePath . $value);
                }
                catch (Exception $ex)
                {
                    
                }
            }

            return $unlinked;
        }
    }

    private function filterInput($key)
    {
        switch ($this->table['attr'][$key])
        {
            case 'integer':
                return Request::form()->getInt($key, 0);
            case 'boolean':
                return (boolean) Request::form()->get($key, false);
            default:
                return Request::form()->get($key, '');
        }
    }

    private function filterInteger($key, $valueGet)
    {
        if (($key == 'shipping_price' || $key == 'product_price' || $key == 'rebill_product_price') && $valueGet != '')
        {
            return number_format($valueGet, 2, '.', '');
        }
        return $valueGet;
    }

    private function isValidData($data)
    {

        return true;
    }

    public function edit($id = '')
    {
        $data = array();
        try
        {   
            $this->validateData();
            if (Request::form()->get('selectedSplitTestType') == "content-ab-testing")
            {
                $this->uploadEncodedFile();
            }

            $row = Database::table($this->table['name'])->find($id);
            $currentPayload = json_decode(Request::form()->get('splitTypesData'), true);
            $exisTingPayload = json_decode($row->{'splitTypesData'}, true);
            $unLinkImg = array();
            foreach ($exisTingPayload as $key => $extValue)
            {
                $existFileInNewReq = false;
                foreach ($currentPayload as $key => $value)
                {
                    if ($extValue['fileName'] == $value['fileName'])
                    {
                        $existFileInNewReq = true;
                    }
                }
                if (empty($existFileInNewReq))
                {
                    array_push($unLinkImg, $extValue['fileName']);
                }
            }
            $this->unlinkImage($unLinkImg);
            $updateStatus = false;
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id' || $key === 'last_modified')
                {
                    continue;
                }

                $valueGet = $this->filterInput($key);

                if ($row->{$key} != $valueGet)
                {
                    $updateStatus = true;
                }
                $data[$key] = $row->{$key} = $valueGet;
            }
            if ($updateStatus)
            {
                $data['last_modified'] = $row->last_modified = date("Y-m-d H:i:s");
            }

            if ($this->isValidData($row))
            {
                $row->save();
                $this->reactivateExtension();
                return array(
                    'success' => true,
                    'data' => $data,
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function delete($id = '')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];
            foreach ($selectedIds as $key => $selectedId)
            {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();
                if ($res)
                {
                    $deletedIds[] = $selectedId;
                }
                else
                {
                    $notDeletedIds[] = $selectedId;
                }
            }

            $this->reactivateExtension();
            return array(
                'success' => true,
                'data' => array(),
                'deletedIds' => $deletedIds,
                'notDeletedIds' => $notDeletedIds
            );
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function mime2ext($mime)
    {
        $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp",
    "image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp",
    "image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp",
    "application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg",
    "image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],
    "wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],
    "ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg",
    "video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],
    "kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],
    "rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application",
    "application\/x-jar"],"zip":["application\/x-zip","application\/zip",
    "application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],
    "7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],
    "svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],
    "mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],
    "webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],
    "pdf":["application\/pdf","application\/octet-stream"],
    "pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],
    "ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office",
    "application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],
    "xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],
    "xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel",
    "application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],
    "xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo",
    "video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],
    "log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],
    "wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],
    "tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop",
    "image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],
    "mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar",
    "application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40",
    "application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],
    "cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary",
    "application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],
    "ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],
    "wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],
    "dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php",
    "application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],
    "swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],
    "mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],
    "rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],
    "jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],
    "eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],
    "p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],
    "p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
        $all_mimes = json_decode($all_mimes, true);
        foreach ($all_mimes as $key => $value)
        {
            if (array_search($mime, $value) !== false)
                return $key;
        }
        return false;
    }
    
    private function validateData() {
        $formData = Request::form()->all();
        $testType = $formData['selectedSplitTestType'];
        $splitData = json_decode($formData['splitTypesData'],true);
        $percenteage = 0.00;
        $uniqueLabels = array();
        $uniqueUrls = array();

        foreach ($splitData as $key => $value) {
            $percenteage = $percenteage+$value['percentage'];
            if(in_array($value['label'], $uniqueLabels))
                    throw new \Exception('Label should be unique');
            array_push($uniqueLabels, $value['label']);


            if(!strcmp($testType, 'url-ab-testing')) {
                if(in_array($value['url'], $uniqueUrls))
                    throw new \Exception("URL should be unique");
                array_push($uniqueUrls, $value['url']);
            }

            if(!strcmp($testType, 'template-ab-testing')) {

            }
        }
        if($percenteage > 100)
            throw new \Exception("Percentage should be set within 100");
        return;
    }

    private function reactivateExtension() {
        $checkExtensionInstalled = Provider::checkExtensions('SplitTest');

        if($checkExtensionInstalled) {
            $extensionCommonObject = new \Extension\SplitTest\Common();
            $extensionCommonObject->activate();
        }
    }
}
