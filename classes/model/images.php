<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Images extends Model
{

	public function __construct($db_instance = FALSE)
	{
		if (Kohana::$environment == Kohana::DEVELOPMENT)
			Image::factory(); // This is needed to create the SQL table if it does not exist

		parent::__construct($db_instance); // Connect to the database
	}

	protected $ids;

	public function get()
	{
		$sql = 'SELECT * FROM cms_images WHERE 1';

		if ($this->ids) $sql .= ' AND id IN ('.implode(',', $this->ids).')';

		$sql .= ' ORDER BY name';

		return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function fields($fields)
	{
		if (is_array($fields))    $this->fields = $fields;
		elseif ($fields === TRUE) $this->fields = TRUE;
		else                      $this->fields = NULL;

		return $this;
	}

	public function get_by_fields($array)
	{
		if ($array === NULL) $this->get_by_fields = NULL;
		else                 $this->get_by_fields = $array;

		return $this;
	}

	public function ids($array)
	{
		if ($array === NULL) $this->ids = NULL;
		else
		{
			if ( ! is_array($array)) $array = array($array);

			if (empty($array)) $array = array(-1); // No matches should be found
			else
			{
				$array = array_map('intval', $array);

				$this->ids = $array;
			}
		}

		return $this;
	}

	public function limit($int)
	{
		if ($int === NULL) $this->limit = NULL;
		else               $this->limit = (int) $int;

		return $this;
	}

	public function offset($int)
	{
		if ($int === NULL) $this->offset = NULL;
		else               $this->offset = (int) $int;

		return $this;
	}

	public function order_by($order_by)
	{
		if ($order_by === NULL) $this->order_by = NULL;
		else                    $this->order_by = $order_by;

		return $this;
	}

	public function search($string)
	{
		if ($string === NULL) $this->search = NULL;
		else                  $this->search = (string) $string;

		return $this;
	}

	public function search_by_fields($array)
	{
		if ($array === NULL) $this->search_by_fields = NULL;
		else                 $this->search_by_fields = $array;

		return $this;
	}

}


class _____Model_Users extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static $driver;

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Users_'.ucfirst(Kohana::$config->load('user.driver'));
		return (self::$driver = new $driver_name);
	}

	public function get()
	{
		return self::driver()->get();
	}

}