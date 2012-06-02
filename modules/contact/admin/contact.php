<?php
/*---------------------------------------------------------------+
| eXtreme-Fusion - Content Management System - version 5         |
+----------------------------------------------------------------+
| Copyright (c) 2005-2012 eXtreme-Fusion Crew                	 |
| http://extreme-fusion.org/                               		 |
+----------------------------------------------------------------+
| This product is licensed under the BSD License.				 |
| http://extreme-fusion.org/ef5/license/						 |
+---------------------------------------------------------------*/
try
{
	require_once '../../../config.php';
	require DIR_SITE.'bootstrap.php';
	require_once DIR_SYSTEM.'admincore.php';
	$_locale->moduleLoad('admin', 'contact');

	if ( ! $_user->hasPermission('module.contact.admin'))
	{
		throw new userException(__('Access denied'));
	}

	$_tpl = new AdminModuleIframe('contact');

	// Wyświetlenie komunikatów
	if ($_request->get(array('status', 'act'))->show())
	{
		// Wyświetli komunikat
		$_tpl->getMessage($_request->get('status')->show(), $_request->get('act')->show(), 
			array(
				'add' => array(
					__('Formularz kontaktowy został dodany.'), __('Błąd podczas dodawania formularza.')
				),
				'edit' => array(
					__('Formularz kontaktowy został zedytowany.'), __('Błąd podczas edycji formularza.')
				),
				'delete' => array(
					__('Formularz kontaktowy został usunięty.'), __('Błąd podczas usuwania formularza.')
				)
			)
		);
	}

	if ($_request->get('action')->show() === 'delete' && $_request->get('id')->isNum(TRUE))
	{
		$count = $_pdo->exec('DELETE FROM [contact] WHERE `id` = :id',
			array(
				array(':id', $_request->get('id')->isNum(), PDO::PARAM_INT)
			)
		);
	
		if ($count)
		{
			$_log->insertSuccess('delete', __('Formularz kontaktowy został usunięty.'));
			$_request->redirect(FILE_PATH, array('act' => 'delete', 'status' => 'ok'));
		}

		$_log->insertFail('delete', __('Błąd podczas usuwania formularza.'));
		$_request->redirect(FILE_PATH, array('act' => 'delete', 'status' => 'error'));
	}
	elseif ($_request->post('save')->show() && $_request->post('email')->isEmail() && $_request->post('title')->show())
	{
		$title = $_request->post('title')->filters('trim', 'strip');
		$email = $_request->post('email')->show();
		
		if ($_request->get('action')->show() === 'edit' && $_request->get('id')->isNum())
		{
			$count = $_pdo->exec('UPDATE [contact] SET `title` = :title, `email` = :email WHERE `id` = :id',
				array(
					array(':id', $_request->get('id')->show(), PDO::PARAM_INT),
					array(':title', $title, PDO::PARAM_STR),
					array(':email', $email, PDO::PARAM_STR)
				)
			);

			if ($count)
			{
				$_request->redirect(FILE_PATH, array('act' => 'edit', 'status' => 'ok'));
			}
		
			$_request->redirect(FILE_PATH, array('act' => 'edit', 'status' => 'ok'));
		}
		else
		{
			$count = $_pdo->exec('INSERT INTO [contact] (`title`, `email`) VALUES (:title, :email)',
				array(
					array(':title', $title, PDO::PARAM_STR),
					array(':email', $email, PDO::PARAM_STR)
				)
			);
				
			if ($count)
			{
				$_log->insertSuccess('add', __('Formularz kontaktowy został dodany.'));
				$_request->redirect(FILE_PATH, array('act' => 'add', 'status' => 'ok'));
			}

			$_log->insertFail('add', __('Błąd podczas dodawania formularza.'));
			$_request->redirect(FILE_PATH, array('act' => 'add', 'status' => 'error'));
		}
		
		$_request->redirect(FILE_PATH);
	}
	elseif ($_request->get('action')->show() === 'edit' && $_request->get('id')->isNum())
	{
		$data = $_pdo->getRow('SELECT `title`, `email` FROM [contact] WHERE `id` = :id',
			array(':id', $_request->get('id')->isNum(), PDO::PARAM_INT)
		);
		
		if($data)
		{
			$contact = array(
				'title' => $data['title'],
				'email' => $data['email']
			);
		}
		else
		{
			throw new userException(__('No ID :id for the table contact.', array(':id' => $_request->get('id')->isNum())));
		}
    }
    else
    {
        $contact = array();
    }

	$_tpl->assign('contact', $contact);
	
	$query = $_pdo->getData('SELECT `id`, `title`, `email` FROM [contact] ORDER BY `id` DESC');
	
	$i = 0; $contacts = array();
	foreach($query as $row)
	{
		$contacts[] = array(
			'row_color' => ($i % 2 == 0 ? 'tbl1' : 'tbl2'),
			'id' => $row['id'],
			'title' => $row['title'],
			'email' => $row['email']
		);
		$i++;
	}
	
	$_tpl->assign('data', $contacts);

    $_tpl->template('admin.tpl');

}
catch(optException $exception)
{
    optErrorHandler($exception);
}
catch(systemException $exception)
{
    systemErrorHandler($exception);
}
catch(userException $exception)
{
    userErrorHandler($exception);
}
catch(PDOException $exception)
{
    PDOErrorHandler($exception);
}