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
        $ano = date('Y');
        $mes = date('n');
        $dia = date('j');
        $seq = 0;

        $numeros = array_keys($this->data);
        
        foreach ($numeros as $item){
            if(str_starts_with($item, "$ano.$mes.$dia.")){
                $seq++;
            }
        }
        

        return "$ano.$mes.$dia.$seq";
    }
//    public function getProximoNumero(): string {
//        $proximo = date('Y.n.j.') . "0";
//
//        $numeros = array_keys($this->data);
//        sort($numeros);
//        $ultimo = array_pop($numeros);
//        $boom = explode('.', $ultimo);
//        $ano = $boom[0];
//        $mes = $boom[1];
//        $dia = $boom[2];
//        $sequencia = $boom[3];
//        if ($ano == date('Y') && $mes == date('n') && $dia == date('j')) {
//            $sequencia += 1;
//
//            $proximo = "$ano.$mes.$dia.$sequencia";
//        }
//
//        return $proximo;
//    }

    public function existeProcesso(string $numero): bool {
        return key_exists($numero, $this->data);
    }

    public function adicionaProcesso(string $numero, string $assunto, array $tags, string $local): void {
        if ($this->existeProcesso($numero)) {
            throw new Exception("Processo $numero já existe.");
        }

        $this->data[$numero] = [
            'subject' => $assunto,
            'tags' => $tags,
            'local' => [date('Y-m-d') => $local]
        ];

        $this->salvaJson();
    }

    public function salvaJson(): void {
        $json = json_encode($this->data);
        if ($json === false) {
            throw new Exception(json_last_error_msg());
        }

        if (file_put_contents($this->file, $json) === false) {
            throw new Exception("Falha ao salvar dados em {$this->file}");
        }
    }

    public function moveProcesso(string $numero, string $local, string $data) {
        if(!$this->existeProcesso($numero)){
            throw new Exception("Processo $numero não encontrado.");
        }
        $this->data[$numero]['local'][$data] = $local;
        $this->salvaJson();
    }

    public function processo(string $numero): array {
        if (!key_exists($numero, $this->data)) {
            throw new Exception("Processo $numero não encontrado.");
        }
        
        ksort($this->data[$numero]['local']);//ordena os locais por data crescente
        
        return $this->data[$numero];
    }

    public function buscaPorAssunto(string $regex): array {
        $result = [];
        foreach ($this->data as $numero => $item) {
            $pattern = "/$regex/i";
            $match = preg_match($pattern, $item['subject']);
            if ($match === false) {
                throw new Exception("Expressão $regex contém erros.");
            }
            if ($match === 1) {
                $result[$numero]['subject'] = $item['subject'];
                $result[$numero]['tags'] = $item['tags'];
                $result[$numero]['local'] = $item['local'];
            }
        }

        return $result;
    }

    public function buscaPorTags(string $regex): array {
        $result = [];
        foreach ($this->data as $numero => $item) {
            $pattern = "/$regex/i";
            foreach ($item['tags'] as $tag) {
                $match = preg_match($pattern, $tag);
                if ($match === false) {
                    throw new Exception("Expressão $regex contém erros.");
                }
                if ($match === 1) {
                    $result[$numero]['subject'] = $item['subject'];
                    $result[$numero]['tags'] = $item['tags'];
                    $result[$numero]['local'] = $item['local'];
                }
            }
        }

        return $result;
    }

    public function buscaPorLocalAtual(string $regex): array {
        $result = [];
        foreach ($this->data as $numero => $item) {
            $localAtual = $this->ondeEsta($numero);
            $pattern = "/$regex/i";
            $match = preg_match($pattern, current($localAtual));
            if ($match === false) {
                throw new Exception("Expressão $regex contém erros.");
            }
            if ($match === 1) {
                $result[$numero]['subject'] = $item['subject'];
                $result[$numero]['tags'] = $item['tags'];
                $result[$numero]['local'] = $localAtual;
            }
        }

        return $result;
    }

    public function ondeEsta(string $numero): array {
        if (!key_exists($numero, $this->data)) {
            throw new Exception("Processo $numero não encontrado.");
        }
        $locais = $this->data[$numero]['local'];
        krsort($locais, SORT_STRING);
        $localAtual[array_key_first($locais)] = $locais[array_key_first($locais)];
        return $localAtual;
    }
    
    public function listaProcessos(): array {
        ksort($this->data);//lista os processo por ordem crescente de número
        return $this->data;
    }
    
    public function listaTags(): array {
        $tags = [];
        foreach ($this->data as $number => $item){
            foreach ($item['tags'] as $tag){
                $tags[$tag][] = $number;
            }
        }
        
        ksort($tags);//lista as tags por ordem crescente
        return $tags;
    }
    
    public function listaLocais(): array {
        $locais = [];
        foreach ($this->data as $number => $item){
            $atual = $this->ondeEsta($number);
            $locais[current($atual)][] = $number;
        }
        
        ksort($locais);//ordena por ordem crescente os locais
        
        return $locais;
    }

}
