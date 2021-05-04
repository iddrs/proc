<?php

use IDDRS\Proc\Processo;
use League\CLImate\CLImate;

require_once 'vendor/autoload.php';

$climate = new CLImate();

$climate->description("Lista processos, locais e tags.");

$climate->arguments->add([
    'process' => [
        'prefix' => 'p',
        'longPrefix' => 'process',
        'description' => 'Lista os processos',
        'noValue' => true
    ],
    'local' => [
        'prefix' => 'l',
        'longPrefix' => 'local',
        'description' => 'Lista os locais',
        'noValue' => true
    ],
    'tags' => [
        'prefix' => 't',
        'longPrefix' => 'tags',
        'description' => 'Lista as tags',
        'noValue' => true
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

} catch (Exception) {
    $climate->usage();
    exit();
}

try {
    
    if($climate->arguments->defined('process')){
        $lista = $processos->listaProcessos();
        $campo = 'processo';
        $tabela = [];
        foreach ($lista as $numero => $item) {
            $local = $processos->ondeEsta($numero);
            $tabela[] = [
                'Número' => $numero,
                'Assunto' => $item['subject'],
                'Tags' => join(', ', $item['tags']),
                'Local Atual' => current($local),
                'Deste' => key($local)
            ];
        }
    }
    
    if($climate->arguments->defined('tags')){
        $lista = $processos->listaTags();
        $campo = 'tags';
        $tabela = [];
        foreach ($lista as $tag => $item) {
            $tabela[] = [
                'Tag' => $tag,
                'Processos' => sizeof($item)
            ];
        }
    }
    
    if($climate->arguments->defined('local')){
        $lista = $processos->listaLocais();
        $campo = 'local';
        $tabela = [];
        foreach ($lista as $local => $item) {
            $tabela[] = [
                'Local' => $local,
                'Processos' => sizeof($item)
            ];
        }
    }
    
} catch (Exception $ex) {
    $climate->error($ex->getTraceAsString());
    die();
}

if(sizeof($lista) > 0){
    $climate->table($tabela);
}else{
    $climate->backgroundLightYellow("Nenhum resultado encontrado para $campo.");
}