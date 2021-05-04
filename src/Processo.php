<?php

namespace IDDRS\Proc;

use Exception;

/**
 * Métodos para manipular e consultar processos
 *
 * @author Everton
 */
class Processo {

    protected array $data;
    protected string $file;

    public function __construct(string $jsonFile) {
        if (!file_exists($jsonFile)) {
            throw new Exception("Arquivo $jsonFile não existe.");
        }

        $this->file = $jsonFile;
        
        $jsonData = file_get_contents($jsonFile);
        if ($jsonData === false) {
            throw new Exception("Falha ao ler o conteúdo de $jsonFile");
        }
        $data = json_decode($jsonData, true);
        if ($data === null) {
            throw new Exception(json_last_error_msg());
        }
        $this->data = $data;
    }

    /**
     * Retorna o próximo número dos processos.
     * 
     * @return string
     */
    public function getProximoNumero(): string {
        $proximo = date('Y.n.j.') . "0";

        $numeros = array_keys($this->data);
        sort($numeros);
        $ultimo = array_pop($numeros);
        $boom = explode('.', $ultimo);
        $ano = $boom[0];
        $mes = $boom[1];
        $dia = $boom[2];
        $sequencia = $boom[3];

        if (
                $ano == date('Y') && $mes == date('m') && $dia == date('d')
        ) {
            $sequencia += 1;

            $proximo = "$ano.$mes.$dia.$sequencia";
        }

        return $proximo;
    }
    
    public function existeProcesso(string $numero): bool {
        return key_exists($numero, $this->data);
    }
    
    public function adicionaProcesso(string $numero, string $assunto, array $tags): void {
        if($this->existeProcesso($numero)){
            throw new Exception("Processo $numero já existe.");
        }
        
        $this->data[$numero] = [
            'subject' => $assunto,
            'tags' => $tags,
            'local' => [date('Y-m-d') => 'em uso']
        ];
        
        $this->salvaJson();
    }
    
    public function salvaJson(): void {
        $json = json_encode($this->data);
        if($json === false){
            throw new Exception(json_last_error_msg());
        }
        
        if(file_put_contents($this->file, $json) === false){
            throw new Exception("Falha ao salvar dados em {$this->file}");
        }
    }
    
    public function moveProcesso(string $numero, string $local, string $data){
        $this->data[$numero]['local'][$data] = $local;
        $this->salvaJson();
    }
}
