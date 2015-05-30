<?php
namespace T3chn0crat\LdapConnector;

use Exception;

class LdapUserObject
{
    /**
     * @param array $ldapCollection
     * @param array $fields
     * @throws Exception
     */
    public function __construct($ldapCollection, $fields)
    {
        $this->setElements($ldapCollection, $fields);
    }

    /**
     * Check to see if a user is in a group
     *
     * @param string $group
     * @return bool
     */
    public function isMemberOf($group)
    {
        if (is_array($this->memberof)) {
            foreach ($this->memberof as $in) {
                if (($pos = strpos($in, ',')) !== false) {
                    $in = substr($in, 0, $pos);
                }
                $in = str_ireplace('CN=', '', $in);
                if (strtolower($in) === strtolower($group)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Set all the elements to public properties on $this
     *
     * @param array $ldapCollection
     * @param array $fields
     */
    public function setElements(array $ldapCollection, $fields)
    {
        array_walk($fields, function ($field, $trash) use ($ldapCollection) {
            if (isset($ldapCollection[$field])) {
                $element = $ldapCollection[$field];
                if ($element['count'] == 1) {
                    $this->{$field} = $element[0];
                } else {
                    $array = [];
                    foreach ($element as $key => $value) {
                        if ($key === 'count') {
                            continue;
                        }
                        $array[] = $value;
                    }
                    $this->{$field} = $array;
                }
            } else {
                $this->{$field} = null;
            }
        });
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}