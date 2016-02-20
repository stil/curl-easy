<?php
namespace cURL;

class ConstantsTable
{
    /**
     * @var int[] Array of cURL constants required for intelligent setters
     */
    protected static $curlConstantsTable = array();

    /**
     * @return array
     */
    public static function loadCurlConstantsTable()
    {
        if (empty(self::$curlConstantsTable)) {
            $constants = get_defined_constants(true);
            foreach ($constants['curl'] as $key => $value) {
                if (strpos($key, 'CURLOPT_') === 0) {
                    $key = str_ireplace(array('CURLOPT', '_'), '', $key);
                    self::$curlConstantsTable[$key] = $value;
                }
            }
        }

        return self::$curlConstantsTable;
    }

    /**
     * @param string $const
     * @return int
     * @throws Exception
     */
    public static function findNumericValue($const)
    {
        $table = self::loadCurlConstantsTable();

        $const = strtoupper($const);
        if (!isset($table[$const])) {
            throw new Exception('Constant CURLOPT_'.$const.' does not exist.');
        }

        return $table[$const];
    }
}
