<?php

use IDDRS\Proc\Processo;
use League\CLImate\CLImate;

require_once 'vendor/autoload.php';

$climate = new CLImate();

$climate->description("Troca um processo de local.");

$climate->arguments->add([
    'number' => [
        'prefix' => 'n',
        'longPrefix' => 'number',
        'description' => 'Número do processo no formato AAAA.M.D.#',
        'required' => true,
        'castTo' => 'string'
    ],
    'local' => [
        'prefix' => 'l',
        'longPrefix' => 'local',
        'description' => 'Novo local do processo',
        'required' => true,
        'castTo' => 'string'
    ],
    'date' => [
        'prefix' => 'd',
        'longPrefix' => 'date',
        'description' => 'Data, no formato AAAA-MM-DD em que a movimentação ocorreu',
        'defaultValue' => null,
        'required' => false,
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
    $local = $climate->arguments->get('local');
    $data = $climate->arguments->get('date');
    
    if($data === ''){
        $data = date('Y-m-d');
    }
} catch (Exception) {
    $climate->usage();
    exit();
}

try {
    $processos->moveProcesso($numero, $local, $data);
} catch (Exception $ex) {
    $climate->error($ex->getMessage());
    $climate->error($ex->getTraceAsString());
    die();
}

$climate->info("Processo $numero movido para $local em $data");