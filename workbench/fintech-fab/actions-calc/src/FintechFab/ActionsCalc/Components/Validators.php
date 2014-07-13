<?php
namespace FintechFab\ActionsCalc\Components;


use Validator;

class Validators
{

	public static function rulesForRegisterAcc()
	{
		$rules = array(
			'termId'          => 'required|integer',
			'username'        => 'required',
			'url'             => 'url',
			'queue'           => '',
			'password'        => 'required|min:4|alpha_dash',
			'confirmPassword' => 'required|same:password',
		);

		return $rules;
	}

	public static function rulesForChangeAccData()
	{
		$rules = array(
			'username'        => 'required',
			'url'             => 'url',
			'queue'           => '',
			'password'        => 'min:4|alpha_dash',
			'confirmPassword' => 'required_with:password|same:password',
		);

		return $rules;
	}

	public static function messagesForErrors()
	{
		$rules = array(
			'required'      => 'Поле должно быть заполнено.',
			'regex'         => 'Некорректный формат данных.',
			'url'           => 'Некорректный адрес',
			'min'           => 'Должен быть длиннее :min символов.',
			'alpha_dash'    => 'Только буквы, цифры, тире и подчёткивания.',
			'same'          => 'Пароли не одинаковы',
			'required_with' => 'Подтвердите пароль',
		);

		return $rules;
	}

	public static function getErrorFromRegData($data)
	{
		$data['username'] = e($data['username']);
		$validator = Validator::make($data, Validators::rulesForRegisterAcc(), Validators::messagesForErrors());
		$userMessages = $validator->messages();

		if ($validator->fails()) {
			$result['errors'] = array(
				'termId'          => $userMessages->first('termId'),
				'username'        => $userMessages->first('username'),
				'url'             => $userMessages->first('url'),
				'queue'           => $userMessages->first('queue'),
				'password'        => $userMessages->first('password'),
				'confirmPassword' => $userMessages->first('confirmPassword'),
			);

			return $result;
		}

		return null;
	}

	public static function getErrorFromChangeData($data)
	{
		$data['username'] = e($data['username']);
		$validator = Validator::make($data, Validators::rulesForChangeAccData(), Validators::messagesForErrors());
		$userMessages = $validator->messages();

		if ($validator->fails()) {
			$result['errors'] = array(
				'username'        => $userMessages->first('username'),
				'url'             => $userMessages->first('url'),
				'queue'           => $userMessages->first('queue'),
				'password'        => $userMessages->first('password'),
				'confirmPassword' => $userMessages->first('confirmPassword'),
			);

			return $result;
		}

		return null;
	}

}