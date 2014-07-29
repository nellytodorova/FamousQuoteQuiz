<?php
/**
 * Centralized Session actions.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class Session
{
    public function __construct($name, $lifetime = 3600, $path = null, $domain = null, $secure = false)
    {
        if (mb_strlen($name) < 1){
            $name = '_sess_quiz';
        }

        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }

    public function &__get($name)
    {
        return $_SESSION[$name];
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function destroySession()
    {
        session_destroy();
    }
}
?>