<?php

use IDDRS\Proc\Processo;
use League\CLImate\CLImate;

require_once '../vendor/autoload.php';

$climate = new CLImate();

$climate->description("Mostra os dados do processo.");

$climate->arguments->add([
    'number' => [
        'prefix' => 'n',
        'longPrefix' => 'number',
        'description' => 'Número do processo no formato AAAA.M.D.#',
        'required' => true,
        'castTo' => 'string'
    ]
]);

try {
    $processos = new Processo(DATA_JSON);
} catch (Exception $ex) {
    $climate->error($ex->getTraceAsString());
    die();
}

try {
    $climate->arguments->parse();

    $numero = $climate->arguments->get('number');
    
} catch (Exception) {
    $climate->usage();
    exit();
}

try {
    $data = $processos->processo($numero);
} catch (Exception $ex) {
    $climate->error($ex->getMessage());
    $climate->error($ex->getTraceAsString());
    die();
}

$climate->green()->bold()->flank($numero);
$climate->bold()->out($data['subject']);
$climate->red()->out(join(', ', $data['tags']));
foreach ($data['local'] as $date => $local){
    $climate->whisper($date)->tab()->out($local);
}