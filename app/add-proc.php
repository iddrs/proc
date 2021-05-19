<?php

use IDDRS\Proc\Processo;
use League\CLImate\CLImate;

require_once '../vendor/autoload.php';

$climate = new CLImate();

$climate->description("Adiciona um novo processo.");

$climate->arguments->add([
    'number' => [
        'prefix' => 'n',
        'longPrefix' => 'number',
        'description' => 'Número do processo no formato AAAA.M.D.#',
        'defaultValue' => null,
        'required' => false,
        'castTo' => 'string'
    ],
    'subject' => [
        'prefix' => 's',
        'longPrefix' => 'subject',
        'description' => 'Assunto do processo.',
        'required' => true,
        'castTo' => 'string'
    ],
    'tags' => [
        'prefix' => 't',
        'longPrefix' => 'tags',
        'description' => 'Lista, separada por vírgulas, com as TAGS do processo.',
        'required' => true,
        'castTo' => 'string'
    ],
    'local' => [
        'prefix' => 'l',
        'longPrefix' => 'local',
        'description' => 'O local onde o processo se encontra.',
        'required' => false,
        'castTo' => 'string',
        'defaultValue' => 'em uso'
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

    try {
        $numero = $climate->arguments->get('number');

        if ($processos->existeProcesso($numero)) {
            $climate->error("Processo número $numero já existe.");
            die();
        }
        if ($numero === '') {
            $numero = $processos->getProximoNumero();
        }
    } catch (Exception $ex) {
        $climate->error($ex->getTraceAsString());
        die();
    }

    $assunto = $climate->arguments->get('subject');
    $tags = array_map('trim', explode(',', $climate->arguments->get('tags')));
    $local = $climate->arguments->get('local');
} catch (Exception) {
    $climate->usage();
    exit();
}

try {
    $processos->adicionaProcesso($numero, $assunto, $tags, $local);
} catch (Exception $ex) {
    $climate->error($ex->getMessage());
    $climate->error($ex->getTraceAsString());
    die();
}

$tags = join(', ', $tags);
$climate->out("Número: <green>{$numero}</green>");
$climate->out("Assunto: <bold>$assunto</bold>");
$climate->out("Tags: <blue>$tags</blue>");
$climate->out("Local: <blue>$local</blue>");
