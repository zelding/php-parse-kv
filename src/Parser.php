<?php
/**
 * php-parse-kv
 *
 * @author    lyozsi (kristof.dekany@apex-it-services.eu)
 * @copyright internal usage
 *
 * Date: 10/11/17 3:15 PM
 */

namespace Zedling\DotaKV;


class Parser
{
    protected $name;

    /**
     * Constructor
     *
     * @param string $name Root section name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Loads key values data from a string or file
     *
     * @param string $string String or file to load
     *
     * @return array
     */
    public function load($string)
    {
        // Use token_get_all() to easily ignore comments and whitespace
        $tokens = token_get_all("<?php\n".$string."\n?>");
        $data   = $this->_parse($tokens);
        // Strip root section
        $data = reset($data);

        return $data;
    }

    /**
     * Recursively parses key values data from tokens
     *
     * @param array $tokens Tokens received from token_get_all()
     *
     * @return array
     */
    private function _parse(&$tokens)
    {
        $data = array();
        $key  = null;

        // Use each() so the array cursor is also advanced
        // when the function is called recursively
        while (list(, $token) = each($tokens)) {
            // New section
            if ($token == '{') {
                // Recursively parse section
                $data[ $key ] = $this->_parse($tokens);
                $key          = null;
            }
            // End section
            elseif ($token == '}') {
                return $data;
            }
            // Key or value
            else {
                $value = $token[1];
                $type  = $token[0];

                if ( T_CONSTANT_ENCAPSED_STRING === $type ) {
                    // Strip surrounding quotes, then parse as a string
                    $value = substr($value, 1, -1);
                }

                if (T_CONSTANT_ENCAPSED_STRING === $type || T_STRING === $type) {
                    // If key is not set, store
                    if (is_null($key)) {
                        $key = $value;
                    }
                    // Otherwise, it's a key value pair
                    else {
                        // If value is already set, treat as an array
                        // to allow multiple values per key
                        if (isset($data[ $key ])) {
                            // If value is not an array, cast
                            if (!is_array($data[ $key ])) {
                                $data[ $key ] = (array) $data[ $key ];
                            }

                            // Add value to array
                            $data[ $key ][] = $value;
                        }
                        // Otherwise, store key value pair
                        else {
                            $data[ $key ] = $value;
                        }

                        $key = null;
                    }
                }
            }
        }

        return $data;
    }
}
