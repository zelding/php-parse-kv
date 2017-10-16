Parse Kv files to array
=======================

Based on a reddit post I Can't find anymore


Install:
`composer require zedling/php-parse-kv`

Usage:
```
<?php

use Zedling\DotaKV\Parser;

$parser = new Parser();

$array = $parser->load(files_get_contents("link_to_file"));

print_r($array);

```

Changes:

 - 2.1.1: added parsing values as array if they are `;` separated
 - 2.1: added parsing values as array if they are space separated
 - 2.0: switched implememntation to use `token_get_all`
 
