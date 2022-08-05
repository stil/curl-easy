<?php
    $constants = get_defined_constants(true);
    $camelCasedConstants = [];
    foreach ($constants['curl'] as $key => $value) {
        if (strpos($key, 'CURLOPT_') === 0) {
            $key = substr($key, 8);
            $camelCase = implode(
                '',
                array_map(
                    function (string $keyFrag) {
                        return strtoupper($keyFrag[0]) . strtolower(substr($keyFrag, 1));
                    },
                    preg_split('`_+`', $key)
                )
            );
            $camelCasedConstants[] = $camelCase;
        }
    }
    echo implode(
        "\n",
        array_map(
            function(string $constant): string {
                return "     * @method set$constant(mixed \$value)";
            },
            $camelCasedConstants
        )
    );
    echo "\n";