<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Symfony\Component\PropertyAccess\PropertyAccess;
use PHPMailer\PHPMailer\PHPMailer;
use Application\Session;

class AutoresponderController
{

    private $table;
    private static $dbConnection = null;
    private $dateRange = array();
    private $startDate;
    private $endDate;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table = array(
            'name' => 'autoresponders',
            'attr' => array(
                'id' => 'integer',
                'label' => 'string',
                'template' => 'string',
                'enable_smtp_profile' => 'boolean',
                'smtp_setup' => 'string',
                'trigger_event' => 'string',
                'event_id' => 'string',
                // 'enable_ssl' => 'boolean',
                'smtp_host' => 'string',
                'smtp_port' => 'string',
                'smtp_username' => 'string',
                'smtp_password' => 'string',
                'email_subject' => 'string',
                'sender_email' => 'string',
                'sender_name' => 'string',
                'smtp_sender_name' => "string",
                'smtp_senders_email' => "string",
                'notified_email' => 'string',
                'smtp_verify' => 'boolean',
                'smtp_mode' => 'string',
                'isActive' => 'boolean'
            ),
        );
    }

    public function all()
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

            $query = Database::table($this->table['name'])
                    ->orderBy($orderByField, $orderBy);


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

            foreach($data as $index => $row) {
                $data[$index]['trigger_event'] = $this->mapTriggerType($row['trigger_event']);
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
        try
        {

            $row = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
                }
                else
                {

                    $valueGet = $this->filterInteger($key, $this->filterInput($key));
                }

                $data[$key] = $row->{$key} = $valueGet;
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
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

    public function edit($id = '')
    {
        try
        {
            $row = Database::table($this->table['name'])->find($id);
            $data = array();
            $updateStatus = false;
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id' || $key === 'last_modified')
                {
                    continue;
                }
                $valueGet = $this->filterInteger($key, $this->filterInput($key));
                if ($row->{$key} != $valueGet)
                {
                    $updateStatus = true;
                }
                $data[$key] = $row->{$key} = $valueGet;
            }

            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
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

    public function get($id = '')
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            foreach ($this->table['attr'] as $key => $type)
            {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
            }
            return array(
                'success' => true,
                'data' => $data,
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

    public function delete($id = '')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];

            foreach ($selectedIds as $key => $selectedId) {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();
                if($res){
                    $deletedIds[] = $selectedId;
                }
                else{
                    $notDeletedIds[] = $selectedId;
                }
            }

            return array(
                'success' => true,
                'data' => array()
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

    private function mapTriggerType($id)
    {
        switch($id) {
            case 1:
                $return = "ORDER CONFIRMATION";
                break;
            case 2:
                $return = "CUSTOM";
                break;
            default:
                $return = "ORDER CONFIRMATION";
        }

        return $return;
    }
    
    public function fireAutoresponder($id, $toEmail, $toName = '', $type = '', $message = '', $tokens = array())
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();

            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            $templateProfile = $row[0];
            
            if(($type != null && $type != '') && $templateProfile['trigger_event'] != $type){
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' =>  'Wrong Template and Trigger combination.'
                );
            }
    
            if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' =>  'Recipient email address is not valid.'
                );
            }
            
            $settings = Config::settings();
            
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            if (
                !empty($_COOKIE['CB_DEBUG_MODE']) &&
                $_COOKIE['CB_DEBUG_MODE'] === 'ENABLE_DEBUGGER'
            )
            {
                $mail->SMTPDebug = 1;
            }

            $mail->isSMTP();
            $mail->SMTPAuth = true;

            $mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => false,
				)
            );
            
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $templateProfile['email_subject'];

//            if(!empty($tokens)) {
//                $tokens = array(
//                        'firstName' => Session::get(
//                            sprintf('customer.firstName')
//                        ),
//                        'lastName' => Session::get(
//                            sprintf('customer.lastName')
//                        ),
//                );
//            }

            $message = strlen($message) ? $message : $templateProfile['template'];

            $mail->MsgHTML(preg_replace_callback(
                "/\[\[([a-z0-9_]+)\]\]/i", 
                function ($data) use ($tokens) {
                    if (empty($tokens[strtolower($data[1])]))
                    {
                        return $data[0];
                    }
                    else
                    {
                        return $tokens[strtolower($data[1])];
                    }
                }, 
                $message
            ));

            $mail->AltBody = "\n\n";
    
            if( !strncmp($templateProfile['smtp_setup'], 'default', strlen('default')) ) {

                if($settings['smtp_verify']) {

                    $mail->Host       = trim($settings['smtp_host']); 
                    $mail->Username   = trim($settings['smtp_username']);  
                    $mail->Password   = trim($settings['smtp_password']); 
                    $mail->Port       = trim($settings['smtp_port']); ;  
                    $mail->SMTPSecure = trim($settings['smtp_mode']); 

                    $mail->setFrom(
                        $settings['from_email'], $settings['from_name']
                    );

                    $mail->addReplyTo(
                        $settings['from_email'], $settings['from_name']
                    );

                    $bccRecipient = preg_split('/\r\n|\r|\n/', $templateProfile['notified_email']);

                    foreach ($bccRecipient as $bcc) {
                        if (filter_var($bcc, FILTER_VALIDATE_EMAIL))
                            $mail->addBcc($bcc);
                    }

                }
                else {

                    return array(
                        'success' => false,
                        'data' => array(),
                        'error_message' =>  'Default SMTP is not verified, please verify first.'
                    );
                }
                 
            }
            else if ( !strncmp($templateProfile['smtp_setup'], 'custom', strlen('custom')) ) {
                //If 3rd Party SMTP is used, code here.
                if($templateProfile['smtp_verify']) {

                    $mail->Host       = trim($templateProfile['smtp_host']); 
                    $mail->Username   = trim($templateProfile['smtp_username']);  
                    $mail->Password   = trim($templateProfile['smtp_password']); 
                    $mail->Port       = trim($templateProfile['smtp_port']); ;  
                    $mail->SMTPSecure = trim($templateProfile['smtp_mode']);  

                    $mail->setFrom(
                        $templateProfile['smtp_senders_email'], $templateProfile['smtp_sender_name']
                    );

                    $mail->addReplyTo(
                        $templateProfile['smtp_senders_email'], $templateProfile['smtp_sender_name']
                    );

                    $bccRecipient = preg_split('/\r\n|\r|\n/', $templateProfile['notified_email']);

                    foreach ($bccRecipient as $bcc) {
                        if (filter_var($bcc, FILTER_VALIDATE_EMAIL))
                            $mail->addBcc($bcc);
                    }
                }
                else {

                    return array(
                        'success' => false,
                        'data' => array(),
                        'error_message' =>  'Default SMTP is not verified, please verify first.'
                    );
                }
            }
            else {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' =>  'SMTP details not found.'
                );
            }


            if($mail->smtpConnect() && $mail->send()) {
                
                return array(
                    'success' => true,
                    'data' => array(),
                    'success_message' =>  'Email Sent.'
                );
            }
            else {

                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' =>  'System fail to connect SMTP server, please check SMTP details.'
                );
            }

            return array(
                'success' => true,
                'data' => $data,
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

    public function status($id = '' , $status = '') 
    {
      
        $selectedIds = empty($id) ? Request::get('ids') : array($id);
        $status =  $status === '' ? Request::get('status') : $status === 'true'? true: false;

        try
        {
            foreach ($selectedIds as $key => $selectedId) {

                $res = Database::table($this->table['name'])->find($selectedId);
                $res->isActive =  $status;

                if($this->isValidData($res)){

                    $res->save();
                    $changeStatusId[] = $selectedId;
                }
                else{
                    $notchangeStatusId[] = $selectedId;
                }
            }

            $msg = $status ? 'activate' : 'deactivate';

            if (!empty($changeStatusId))
            {
                return array(
                    'success' => true,
                    'data' => $changeStatusId,
                    'success_message' => sprintf('Auto Responder has been %s successfully.', $msg)
                );
            }
            else {
                return array(
                    'success' => false,
                    'data' => null,
                    'error_message' => sprintf('System error, could not %s autoresponder%s.', $msg, count($selectedIds) > 2 ? 's' : '')
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => null,
                'error_message' => $ex->getMessage(),
            );
        }
    }
}
