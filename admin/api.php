<?php
$config = include dirname(__FILE__) . '/../config/extended_config.php';

if ( empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']) )
{
    $HTTP_AUTHORIZATION = ! empty($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($HTTP_AUTHORIZATION, 6)));
}

if  (
        isset($_SERVER['PHP_AUTH_USER'])
        &&
        isset($_SERVER['PHP_AUTH_PW'])
        &&
        $_SERVER['PHP_AUTH_USER'] == 'webmaster'
        &&
        $_SERVER['PHP_AUTH_PW'] != 'default'
        &&        
        $_SERVER['PHP_AUTH_PW'] == $config['password']

    ){
}
elseif ($_SERVER['PHP_AUTH_PW'] == 'default')
{
    unAuthHeader('<span style="color:red;font-weight:bold">Unauthorized! Please change the default password, Once done restart your browser.</span>', false);
}
else
{
    unAuthHeader('Something Went Wrong.');    
}

function unAuthHeader($message, $headers = true)
{
    if ($headers)
    {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        header('HTTP/1.0 401 Unauthorized');
    }

    echo $message;
    exit;
}


include 'vendor/autoload.php';

define('LAZER_DATA_PATH', realpath(dirname(__FILE__)).'/db/');

use Lazer\Classes\Database as Lazer;

class Api {


	/**
	 * [$method description]
	 * @var array
	 */
	protected $methods = array(
			'manage',
			'delete',
			'get',
			'api_conf',
			'timezone_identifiers_list'
		);

	protected $table, $post;



	public function __construct()
	{

		$post = file_get_contents("php://input");

		if (strlen($post))
		{
			$this->post = json_decode($post);
		}
		else
		{
			$this->post = new StdClass;
		}


		$this->table = Lazer::table(
				isset($this->post->lazer_db)
				?
				$this->post->lazer_db 
				:
				'steps'
			);

		if (
				isset($this->post->method) 
				&&
				in_array($this->post->method, $this->methods)
			)
		{

			$this->{$this->post->method}();
		}
	}

	protected function manage()
	{

		if (@$this->post->step_type == 'main' && ! isset($this->post->id))
		{
			$result = $this->table->where('step_type', '=', 'main')->findAll()->count();

			if ($result > 0)
			{
				echo json_encode(array(
						'success' => false,
						'text' => 'Main step already exists.'
					));

				return;
			}
		}

		if ( isset($this->post->id) )
		{
			$table = $this->table->find($this->post->id);
		}
		else
		{
			$table = $this->table;
		}

		foreach ($this->table->schema() as $key => $value)
		{
			
			if ( isset($this->post->{$key}) )
			{
				$write = is_array($this->post->{$key}) || is_object($this->post->{$key}) ? json_encode($this->post->{$key}) : $this->post->{$key};

				$table->{$key} =  $write;
			}
		}

		$table->save();

		echo json_encode(array(
				'success' => true,
				'text' => '',
				'id' => $table->id
			));
	}

	protected function update() 
	{

		$update = $this->table->find($this->post->id);

		$update->step_type = @$this->post->step_type;
		$update->campaign_id = @$this->post->campaign_id;
		$update->product_id = @$this->post->product_id;
		$update->product_price = (string)@$this->post->product_price;
		$update->product_quantity = @$this->post->product_quantity;
		$update->shipping_id = @$this->post->shipping_id;

		$update->save();

		echo json_encode(array(
				'success' => true,
				'text' => ''
			));
	}

	protected function get()
	{

		if (isset($this->post->id))
		{
			$result = $this->table->where('id', '=', $this->post->id)->findAll();
		}
		else
		{
			$result = $this->table->findAll();
			$json = array();
		}

		foreach($result as $row)
		{
			$json[] = $row;
		}

		echo json_encode($json);
	}

	protected function delete()
	{
		if (! isset($this->post->id))
		{
			return false;
		}

		return $this->table->find($this->post->id)->delete();
	}

	protected function api_conf()
	{
		$result = $this->table->where('step_type', '=', 'main')->findAll()->asArray();
		echo json_encode(@$result[0]);
	}

	protected function timezone_identifiers_list()
	{
		echo json_encode(timezone_identifiers_list());
	}
}

new Api;