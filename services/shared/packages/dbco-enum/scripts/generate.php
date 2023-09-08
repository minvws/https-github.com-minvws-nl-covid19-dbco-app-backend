<?php

declare(strict_types=1);

ini_set('display_errors', '1');


$packageRootPath = realpath(dirname(__FILE__) . '/../');

echo "Enum package root path: $packageRootPath\n";

run();

function run(): void
{
    global $packageRootPath;

    $schemaPaths = glob($packageRootPath . '/enums/*.json');
    $schemaIndexPath = $packageRootPath . '/enums/index.json';

    $phpOutputDirPath = $packageRootPath . '/output/php';
    $tsOutputDirPath = $packageRootPath . '/output/ts';

    if ($schemaPaths === false) {
        echo 'No JSON files found';
        return;
    }

    $index = array_search($schemaIndexPath, $schemaPaths);
    if ($index !== false) {
        unset($schemaPaths[$index]);
    }

    updateSchemaIndex($schemaIndexPath, $schemaPaths);

    deleteFolderContent($phpOutputDirPath);

    $tsSnippets = generateCodeAndOutputPhP($schemaPaths, $phpOutputDirPath);

    deleteFolderContent($tsOutputDirPath);
    outputTsFiles($tsOutputDirPath, $tsSnippets);
}

function deleteFolderContent($dir):void 
{

    global $packageRootPath;
    echo "Deleting folder contents: $dir\n";
    
    if (!str_starts_with($dir, $packageRootPath)) {
        throw new Exception('Can not delete folder content outside of this package.');
    }

    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            deleteFolderContent($file);
        else
            unlink($file);
    }
}

function recurseCopy(
    string $sourceDirectory,
    string $destinationDirectory,
    string $phpFilePrepend = ''
): void {
    echo "Copy folder contents: $sourceDirectory to $destinationDirectory\n";
    $directory = opendir($sourceDirectory);

    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }

    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fileSource = "$sourceDirectory/$file";
        $fileDestination = "$destinationDirectory/$file";

        if (is_dir($fileSource) === true) {
            recurseCopy($fileSource, $fileDestination, $phpFilePrepend);
        }
        else {
            copy($fileSource, $fileDestination);

            if(!empty($phpFilePrepend)) {
                $fileContents = file_get_contents($fileDestination);
                $phpTagPosition = strpos($fileContents, '<?php');
                if ($phpTagPosition !== false) {
                    $fileContents = substr_replace($fileContents, "<?php\r\n" . $phpFilePrepend, $phpTagPosition, strlen('<?php'));
                    file_put_contents($fileDestination, $fileContents);
                }
            }
        }
    }

    closedir($directory);
}

function safeName(string $value): string
{
    return lcfirst(
        str_replace(
            [" ", "-", "_", "\t", "\r", "\n", "\f", "\v"],
            '',
            ucwords($value, " -_\t\r\n\f\v")
        )
    );
}

/**
 * @throws Exception
 */
function safeNames(array $items, object $schema): void
{
    foreach ($items as $item) {
        if (empty($item->name)) {
            if (!is_string($item->value)) {
                throw new Exception('name-property should be present if value is not a string');
            }

            $item->name = safeName($item->value);
        }

        if (($schema->tree ?? false) && is_array($item->items ?? null)) {
            safeNames($item->items, $schema);
        }
    }
}

function addPhpTypeToSchemaProperties(object $schema): void
{
    foreach ($schema->properties ?? [] as $name => $data) {
        $phpType = $data->type;

        $refSchemaPath = __DIR__ . "../enums/{$data->type}.json";
        if (file_exists($refSchemaPath)) {
            $refSchema = loadSchema($refSchemaPath);
            $phpType = $refSchema->phpClass;
        }

        $data->phpType = $phpType;
    }
}

/**
 * @throws Exception
 */
function loadSchema(string $path): object
{
    $content = file_get_contents($path);

    if ($content === false) {
        throw new Exception(sprintf('Schema not found for: %s', $path));
    }

    $schema = json_decode($content);
    if ($schema === null) {
        die('Invalid schema file: ' . $path . '!');
    }

    safeNames($schema->items, $schema);
    addPhpTypeToSchemaProperties($schema);

    return $schema;
}

function getPhpStaticMethodsForItems(array $items, object $schema, int $level = 0): array
{
    $staticMethods = [];

    foreach ($items as $item) {
        // NOTE: the double {$item->name}() is intentional, PHPStorm needs the method name twice;
        //       first with only the name, second time with arguments (which we don't have)
        //       else the documentation for the method is parsed incorrectly
        $staticMethods[] = " * " . str_repeat(" ", 2 * $level) .  "@method static {$schema->phpClass} {$item->name}() {$item->name}() {$item->label}";

        if (($schema->tree ?? false) && is_array($item->items ?? null)) {
            $childStaticMethods = getPhpStaticMethodsForItems($item->items, $schema, $level + 1);
            $staticMethods = array_merge($staticMethods, $childStaticMethods);
        }
    }

    return $staticMethods;
}

function generatePhpClass(object $schema, string $filename): string
{
    $staticMethods = getPhpStaticMethodsForItems($schema->items, $schema);
    $properties = [];

    $scalarType = property_exists($schema, 'scalarType') ? $schema->scalarType : 'string';
    $properties[] = "\n * @property-read $scalarType \$value";

    foreach ($schema->properties ?? [] as $name => $data) {
        if (in_array($data->scope ?? "shared", ["php", "shared"])) {
            $description = $data->description ?? '';
            $properties[] = " * @property-read {$data->phpType} \${$name} {$description}";
        }
    }

    foreach ($schema->traitProperties ?? [] as $name => $data) {
        $description = $data->description ?? '';
        $properties[] = " * @property-read {$data->type} \${$name} {$description}";
    }

    if (isset($schema->tree) && isset($schema->phpClass)) {
        $properties[] = " * @property-read {$schema->phpClass}|null \$parent Parent.";
        $properties[] = " * @property-read {$schema->phpClass}[] \$children Children.";
    }

    $declarations = implode("\n", array_merge($staticMethods, $properties));

    $traits = '';
    foreach ($schema->traits ?? [] as $trait) {
        $traits .= "\n    use {$trait};\n";
    }

    $template = file_get_contents(__DIR__ . '/../templates/Enum.php.tpl');

    if ($template === false) {
        return '';
    }

    $schemaExport =
        trim(
            implode(
                "\n",
                array_map(
                    fn ($l) => str_repeat(' ', 8) . $l,
                    explode("\n", str_replace('NULL', 'null', var_export($schema, true)))
                )
            )
        );

    $result = str_replace(
        ['[class]', '[description]', '[declarations]', '[traits]', '[schema]', '[filename]'],
        [$schema->phpClass, $schema->description ?? '', $declarations, $traits, $schemaExport, $filename],
        $template
    );

    // remove whitespace at end of lines
    return implode("\n", array_map('rtrim', explode("\n", $result)));
}

function typeScriptEnumValue(object $schema, int $version, object $item): string
{
    return ucfirst($schema->tsConst) . 'V' . $version . '.VALUE_' . str_replace('-', '_', strval($item->value));
}

function generateTypeScriptItems(object $schema, array $properties, int $version): array
{
    $data = [];
    foreach ($schema->items as $item) {
        $minVersion = $item->minVersion ?? 1;
        $maxVersion = $item->maxVersion ?? $version;

        if ($minVersion > $version || $maxVersion < $version) {
            continue;
        }

        if (count($properties) > 0 || ($schema->tree ?? false)) {
            $entry = ['label' => $item->label, 'value' => typeScriptEnumValue($schema, $version, $item)];

            foreach ($properties as $propertyName) {
                $entry[$propertyName] = $item->$propertyName ?? null;
            }

            if (($schema->tree ?? false) && isset($item->items)) {
                $entry['items'] = generateTypeScriptItems($schema, $properties, $version);
            }

            $data[] = $entry;
        } else {
            $data['[' . typeScriptEnumValue($schema, $version, $item) . ']'] = $item->label;
        }
    }

    return $data;
}

function generateTypeScriptEnum(object $schema, int $version): string
{
    $data = [];
    foreach ($schema->items as $item) {
        $minVersion = $item->minVersion ?? 1;
        $maxVersion = $item->maxVersion ?? $version;

        if ($minVersion > $version || $maxVersion < $version) {
            continue;
        }

        $data[] = "  'VALUE_" . str_replace('-', '_', strval($item->value)) . "' = '" . $item->value . "',";
    }

    return ucfirst($schema->tsConst) . 'V' . $version . " {\n" . implode("\n", $data) . "\n}";
}

function generateTypeScriptData(object $schema, int $version): array
{
    $properties = [];
    foreach (get_object_vars($schema->properties ?? new stdClass()) as $name => $def) {
        if (!isset($def->scope) || $def->scope === 'shared' || $def->scope == 'ts') {
            $properties[] = $name;
        }
    }

    return generateTypeScriptItems($schema, $properties, $version);
}

function generateTypeScriptCode(object $schema, string $filename, int $version): string
{
    $data = generateTypeScriptData($schema, $version);
    $enum = generateTypeScriptEnum($schema, $version);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Since we generate typescript, and not json, lets REMOVE THE QUOTES from the generated enum values
    // ie. replace "[BCOPhaseV1.VALUE_1a]" with [BCOPhaseV1.VALUE_1a]
    $options = preg_replace('/"(\[?[^"]*?\.VALUE_[^"]*?\]?)"/', '$1', strval($json));
    $template = file_get_contents(__DIR__ . '/../templates/Enum.ts.tpl');

    if ($template === false) {
        return '';
    }

    return str_replace(
        ['[name]', '[description]', '[options]', '[filename]', '[enum]'],
        [$schema->tsConst . 'V' . $version, $schema->description ?? '', $options, $filename, $enum],
        $template
    );
}

function generateTypeScriptAllEnumsType(array $tsConsts): string
{
    $code = "/**\n * *** WARNING ***\n * This code is auto-generated. Any changes will be reverted by generating the schema!\n */\n\n";
    $allEnums = [];
    foreach ($tsConsts as $tsConst) {
        array_push($allEnums, ucfirst($tsConst));
    }

    foreach ($tsConsts as $tsConst) {
        $code .= "import { " . ucfirst($tsConst) ." } from './" . $tsConst . "';\n";
    }

    $code .= "\n";

    foreach ($tsConsts as $tsConst) {
        $code .= "export { " . ucfirst($tsConst) . ", " . $tsConst . "Options } from './" . $tsConst . "';\n";
    }

    $code .= "\nexport type AllEnums = " . implode(" | ", $allEnums) . ";";
    return $code;
}

function updateSchemaIndex($schemaIndexPath, $schemaPaths): void 
{
    //make a copy of schemapaths where only the last part of the filename *.json is kept
    $alteredSchemapaths = array_map(
        fn ($path) =>  basename($path),
        $schemaPaths
    );

    file_put_contents(
        $schemaIndexPath,
        json_encode(array_values($alteredSchemapaths), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}


function generateCodeAndOutputPhP($schemaPaths, $phpOutputDirPath): array 
{
    $tsSnippets = [];

    foreach ($schemaPaths as $schemaPath) {
        $schema = loadSchema($schemaPath);

        $filename = basename($schemaPath);

        if (isset($schema->phpClass)) {
            echo "Generate PHP class for $filename\n";

            $phpCode = generatePhpClass($schema, $filename);

            $classPath = $phpOutputDirPath . '/Models/' . $schema->phpClass . '.php';
            if (file_exists($classPath) && md5_file($classPath) === md5($phpCode)) {
                echo "Skipping, code has not changed!\n";
            } else {
                file_put_contents($classPath, $phpCode);
                echo "PHP class stored in $classPath\n";
            }
        }

        if (isset($schema->tsConst)) {
            echo "Generate TypeScript code for $filename\n";
            $maxVersion = isset($schema->currentVersion) ? $schema->currentVersion : 1;
            for ($version = 1; $version <= $maxVersion; $version++) {
                $tsSnippets[$schema->tsConst . 'V' . $version] = generateTypeScriptCode($schema, $filename, $version);
            }
        }

        echo "---\n";
    }

    return $tsSnippets;
}



function outputTsFiles($tsOutputDirPath, $tsSnippets): void
{
    foreach ($tsSnippets as $file => $tsCode) {
        $tsPath = $tsOutputDirPath . '/' . $file . '.ts';

        if (file_exists($tsPath) && md5_file($tsPath) === md5($tsCode)) {
            echo "Don't store TypeScript code, code has not changed!\n";
        } else {
            echo "Store TypeScript code in $tsPath\n";
            file_put_contents($tsPath, $tsCode);
        }
    }

    $tsConsts = array_keys($tsSnippets);
    asort($tsConsts);
    $tsBarrel = generateTypeScriptAllEnumsType($tsConsts);
    $allTsPath = $tsOutputDirPath . '/index.ts';
    if (file_exists($allTsPath) && md5_file($allTsPath) === md5($tsBarrel)) {
        echo "Don't store TypeScript code, code has not changed!\n";
    } else {
        echo "Store TypeScript code in $allTsPath\n";
        file_put_contents($allTsPath, $tsBarrel);
    }
}
