<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Image extends Model
{

	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			$db_name = $this->pdo->query('SELECT database()')->fetchColumn();

			$sql = 'SELECT count((1))
				FROM INFORMATION_SCHEMA.TABLES
				WHERE
					table_schema = '.$this->pdo->quote($db_name).'
					AND table_name = \'cms_images\'';

			if ( ! $this->pdo->query($sql)->fetchColumn())
			{
				// Table cms_images does not exist, create it dawg!
				$sql = 'CREATE TABLE IF NOT EXISTS `cms_images` (
						`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						`name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
						`filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						`inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
				$this->pdo->exec($sql);
			}
		}

		if ($id) $this->load($id);
	}

	/**
	 * Create new image in database
	 *
	 * @param arr - $file - Array as from a file form upload
	 * @param arr - $data - Array of data - name, filename or other things when the db supports it
	 *
	 * @return obj - instance of self
	 **/
	public static function create($file, $data = array())
	{
		$pdo = Pajas_Pdo::instance();

		if ( ! is_array($file) || ! isset($file['name'])) return FALSE;

		$path = Kohana::$config->load('user_content.dir').'/images';

		if (empty($data['filename'])) $data['filename'] = $file['name'];
		if (empty($data['name']))     $data['name']     = pathinfo($data['filename'], PATHINFO_FILENAME);

		// Two identical filenames are not allowed
		if (self::factory_by_url($data['filename'])) return FALSE;

		$full_path = $path.'/'.$data['filename'];

		// Make sure all subfolders exists
		exec('mkdir -p '.pathinfo($full_path, PATHINFO_DIRNAME));

		if (move_uploaded_file($file['tmp_name'], $full_path))
		{
			$sql = 'INSERT INTO cms_images (';

			foreach ($data as $field => $value)
				$sql .= Mysql::quote_identifier($field).',';
			$sql = rtrim($sql, ',').') VALUES(';

			foreach ($data as $field => $value)
				$sql .= $pdo->quote($value).',';
			$sql = rtrim($sql, ',').')';

			$pdo->exec($sql);

			return self::factory_by_url($data['filename']);
		}

		return FALSE;
	}

	public static function factory($id = FALSE)
	{
		return new self($id);
	}

	public static function factory_by_url($url)
	{
		$pdo = Pajas_Pdo::instance();
		$sql = 'SELECT id FROM cms_images WHERE filename = '.$pdo->quote($url);

		foreach ($pdo->query($sql) as $row)
			return new self($row['id']);

		return FALSE;
	}

	public function load($id)
	{
		foreach (Images::factory()->ids($id)->get() as $image)
		{
			$this->data = $image;
			return TRUE;
		}

		return FALSE;
	}

	public function rm()
	{
		if (is_array($this->data) && isset($this->data['id']))
		{
			$this->pdo->exec('DELETE FROM cms_images WHERE id = '.$this->pdo->quote($this->data['id']));
			$path = Kohana::$config->load('user_content.dir').'/images';

			unlink($path.'/'.$this->data['filename']);

			// Also clear cache
			foreach (glob(Kohana::$cache_dir.'/user_content/'.$this->data['filename'].'*') as $filename)
				unlink($filename);

			$this->data = NULL;

			return TRUE;
		}

		return FALSE;
	}

	public function set($data)
	{
		if (is_array($this->data) && isset($this->data['id']))
		{

			// Move file if needed
			if (isset($data['filename']) && $data['filename'] != $this->data['filename'])
			{
				// Cannot change filename to one that is already taken
				if (self::factory_by_url($data['filename'])) return FALSE;

				$path = Kohana::$config->load('user_content.dir').'/images';
				$full_path = $path.'/'.$data['filename'];

				// Make sure all subfolders exists
				exec('mkdir -p '.pathinfo($full_path, PATHINFO_DIRNAME));

				rename($path.'/'.$this->data['filename'], $full_path);
			}

			$sql = 'UPDATE cms_images SET ';

			foreach ($data as $field => $value)
				$sql .= Mysql::quote_identifier($field).' = '.$this->pdo->quote($value).',';
			$sql = rtrim($sql, ',');

			$sql .= ' WHERE id = '.$this->pdo->quote($this->data['id']);

			$this->pdo->exec($sql);

			$this->load($this->data['id']); // Reload data from database
		}

		return $this;
	}

	public function update_file($file)
	{
		if (is_array($this->data) && isset($this->data['id']))
		{
			$path = Kohana::$config->load('user_content.dir').'/images';
			$full_path = $path.'/'.$this->data['filename'];

			// Make sure all subfolders exists
			exec('mkdir -p '.pathinfo($full_path, PATHINFO_DIRNAME));

			move_uploaded_file($file['tmp_name'], $full_path);
		}

		return $this;
	}

}