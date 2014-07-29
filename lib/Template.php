<?php
/**
 * Class used to operate with all template objects.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class Template
{
    /**
     * Holds all template variables.
     * @var array
     */
    protected $_vars;

    /**
     * Opens and returns the template file.
     * @param string $filename
     * @return fetched contents on success and false of fail
     */
    public function fetch($filename)
    {
        if (is_file(trim($filename))) {
            ob_start();
            extract($this->_vars, EXTR_OVERWRITE, 'wddx');
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }

        return false;
    }

    /**
     * Set a template variable. All variables are stored in $this->vars associative array.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
    }
}
?>