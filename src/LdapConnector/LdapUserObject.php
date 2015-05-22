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
     * Set all the elements to public properties on the object
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