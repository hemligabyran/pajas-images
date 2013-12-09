<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Images extends Admincontroller
{

	public function before()
	{
		parent::before();
		xml::to_XML(array('admin_page' => 'Images'), $this->xml_meta);
	}

	public function action_index()
	{
		xml::to_XML(Images::factory()->get(), array('images' => $this->xml_content), 'image', 'id');
	}

	public function action_image()
	{
		if (isset($_GET['id']))
		{
			$image = new Image($_GET['id']);
			$formdata = $image->get();
		}

		if ( ! empty($_POST))
		{
			if (isset($_POST['action']) && $_POST['action'] == 'rm')
			{
				if (isset($image)) $image->rm();
				$this->add_message('Image removed', FALSE, TRUE);
				$this->redirect('/admin/images');
			}
			else
			{
				if (isset($_POST['action'])) unset($_POST['action']);

				$post = new Validation($_POST);
				$post->filter('trim');

				$formdata     = $post->as_array();
				$allowed_exts = array('gif', 'jpeg', 'jpg', 'png');

				// Get file extension
				if ( ! empty($_FILES) && isset($_FILES['file']['name']))
					$extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				else
					$extension = FALSE;

				if ( ! isset($image))
				{
					if (empty($_FILES) || $_FILES['file']['error'] > 0) $this->add_error('No valid file uploaded');
					elseif ( ! in_array($extension, $allowed_exts))     $this->add_error('Only gif, jpg and png images are allowed');
					else
					{
						if (empty($formdata['filename'])) $formdata['filename'] = $_FILES['file']['name'];

						if (Image::factory_by_url($formdata['filename'])) $this->add_error('Filename is already taken');
						else
						{
							$image = Image::create($_FILES['file'], $formdata);
							$this->add_message('Image added', FALSE, TRUE);
							$this->redirect('/admin/images/image?id='.$image->id);
						}
					}
				}
				else
				{
					if ($image->filename != $formdata['filename'] && Image::factory_by_url($formdata['filename'])) $this->add_error('Filename is already taken');
					else
					{
						$image->set($formdata);

						if (isset($_FILES['file']) && $_FILES['file']['error'] == 0)
							$image->update_file($_FILES['file']);

						$this->add_message('Image updated');
					}
				}
			}
		}

		if (isset($formdata))
			$this->set_formdata($formdata);
	}

}