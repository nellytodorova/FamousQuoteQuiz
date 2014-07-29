<?php
/**
 * Common class for different kind of validations, filters, etc.
 *
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class Cleaner
{
    /**
     * Strip tags for texts which are printed to the user.
     * Prevent from XSS attacks.
     * @param mixed $data
     * @return mixed
     */
    public static function stripTags($data)
    {
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$values) {
                strip_tags($values);
            }
        } else {
            $data = strip_tags($data);
        }

        return $data;
    }
}
?>