<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function start_session()
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	set_sesskey();
}

function set_sesskey($regenerate = false)
{
	if ($regenerate || get_sesskey() === "") {
		$sesskey = bin2hex(random_bytes(25));
		$_SESSION["sesskey"] = $sesskey;
	}
}

function get_sesskey()
{
	if (isset($_SESSION["sesskey"])) {
		return $_SESSION["sesskey"];
	}

	return "";
}

function verify_sesskey($sesskey)
{
	if (isset($_SESSION["sesskey"])) {
		return $_SESSION["sesskey"] == $sesskey;
	}
	return false;
}

function is_logged_in()
{
	return isset($_SESSION['user_data']['ID_UPORABNIKA']);
}

function get_logged_in_id()
{
	if (is_logged_in())
		return $_SESSION['user_data']['ID_UPORABNIKA'];
	return "";
}

function get_logged_in_name()
{
	if (is_logged_in())
		return $_SESSION['user_data']['UPORABNISKO_IME'];
	return "";
}

function get_logged_in_email()
{
	if (is_logged_in())
		return $_SESSION['user_data']['EMAIL'];
	return "";
}

function deny_direct_access()
{
	if (!defined('IncludeAccess')) {
		die('Direkten dostop ni dovoljen!');
	}
}

?>