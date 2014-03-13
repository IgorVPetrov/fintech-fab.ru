<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.03.14
 * Time: 14:16
 */

namespace FintechFab\Widgets;


use Auth;
use Request;

class LinksInMenu
{
	public static function echoAuthMode()
	{
		if (Auth::check()) {
			return LinksInMenu::linkForUserProfile();
		}
		$link_registration = '<li ' . LinksInMenu::echoActiveClassIfRequestMatches("registration") . '>
								<a href="/registration">Регистрация</a>
							</li>';
		$link_login = '<li>
							<a href="" data-toggle="modal" data-target="#loginModal">Вход</a>
						</li>';

		return $link_registration . $link_login;
	}

	public static function linkForUserProfile()
	{
		$first_name = Auth::user()->first_name;
		$last_name = Auth::user()->last_name;
		$link_user = '<li><a href="profile">' . $first_name . ' ' . $last_name . '</a></li>';

		$link_logout = '<li><a href="/logout">Выход</a></li>';

		return $link_user . $link_logout;
	}

	public static function echoActiveClassIfRequestMatches($requestUri)
	{
		$current_file_name = basename(Request::server('REQUEST_URI'), ".php");

		if ($current_file_name == $requestUri) {
			return 'class="active"';
		}

		return '';
	}


} 