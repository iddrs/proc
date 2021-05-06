<?php

use IDDRS\Proc\Processo;
use League\CLImate\CLImate;

require_once 'vendor/autoload.php';

$climate = new CLImate();

$climate->description("Encontra processos de acordo com critérios de pesquisa.");

$climate->arguments->add([
    'find' => [
        'prefix' => 'f',
        'longPrefix' => 'find',
        'description' => 'Uma expressão regular para realizar a busca.',
        'required' => true,
        'castTo' => 'string'
    ],
    'subject' => [
        'prefix' => 's',
        'longPrefix' => 'subject',
        'description' => 'Busca no campo de assunto',
        'required' => false,
        'castTo' => 'bool',
        'noValue' => true
    ],
    'tags' => [
        'prefix' => 't',
        'longPrefix' => 'tags',
        'description' => 'Busca no campo de tags',
        'required' => false,
        'castTo' => 'bool',
        'noValue' => true
    ],
    'local' => [
        'prefix' => 'l',
        'longPrefix' => 'local',
        'description' => 'Busca por local',
        'required' => false,
        'castTo' => 'bool',
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

    $regex = $climate->arguments->get('find');
    
} catch (Exception) {
    $climate->usage();
    exit();
}

try {
    if($climate->arguments->defined('subject')){
        $finded = $processos->buscaPorAssunto($regex);
        $campo = 'assunto';
        $tabela = [];
        foreach ($finded as $numero => $item) {
            $tabela[] = [
                'Número' => $numero,
                'Assunto' => $item['subject']
            ];
        }
    }
    
    if($climate->arguments->defined('tags')){
        $finded = $processos->buscaPorTags($regex);
        $campo = 'tags';
        $tabela = [];
        foreach ($finded as $numero => $item) {
            $tabela[] = [
                'Número' => $numero,
                'Assunto' => $item['subject'],
                'Tags' => join(', ', $item['tags'])
            ];
        }
    }
    if($climate->arguments->defined('local')){
        $finded = $processos->buscaPorLocalAtual($regex);
        $campo = 'local';
        $tabela = [];
        foreach ($finded as $numero => $item) {
            $local = $processos->ondeEsta($numero);
            $tabela[] = [
                'Número' => $numero,
                'Assunto' => $item['subject'],
                'Local Atual' => current($local),
                'Deste' => key($local)
            ];
        }
    }
} catch (Exception $ex) {
    $climate->error($ex->getMessage());
    $climate->error($ex->getTraceAsString());
    die();
}

if(sizeof($finded) > 0){
    $climate->table($tabela);
}else{
    $climate->backgroundLightYellow("Nenhum resultado encontrado para $regex em $campo.");
}