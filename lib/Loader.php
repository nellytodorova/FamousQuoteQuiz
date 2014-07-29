<?php
/**
 * Set autoload of files stored into the lib folder.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class Loader
{
    /**
     * Registed function as autoload.
     * @return void
     */
    public static function RegisterAutoLoad()
    {
        spl_autoload_register(array('Loader', '_autoload'));
    }

    /**
     * Automatic include of a class file.
     * @param string $class
     * @throws Exception
     */
    protected static function _autoload($class)
    {
        if (!empty($class)) {
            $file = $GLOBALS['config']['root_lib'] . $class . '.php';
            if (is_file($file)) {
                include $file;
            } else {
                throw new Exception('File cannot be included: ' . $file);
            }
        }
    }
}
?>