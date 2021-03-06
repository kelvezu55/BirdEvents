<?php

namespace App\Util;

use \App\Modelos\Login;

class Util {

    private static $meses = [
        '00' => '',
        '01' => 'Janeiro',
        '02' => 'Fevereiro',
        '03' => 'Março',
        '04' => 'Abril',
        '05' => 'Maio',
        '06' => 'Junho',
        '07' => 'Julho',
        '08' => 'Agosto',
        '09' => 'Setembro',
        '10' => 'Outubro',
        '11' => 'Novembro',
        '12' => 'Dezembro'
    ];
    public static $permissoes = [
        'Aluno.Edição.0',
        'Aluno.Pesquisa.1',
        'Aluno.Visualização.0',
        'Certificado.Cadastro.1',
        'Certificado.Edição.0',
        'Certificado.Pesquisa.1',
        'Certificado.Visualização.0',
        'Certificado.Visualização_Geral.0',
        'Certificado.Vinculados.0',
        'Certificado.Pesquisa_Por_Usuário.1',
        'Certificado.Vincular_Certificados.1',
        'Certificado.Validação.1',
        'Configuração.Importação.1',
        'Curso.Cadastro.1',
        'Curso.Edição.0',
        'Curso.Pesquisa.1',
        'Curso.Visualização.0',
        'Iniciação.Cadastro.1',
        'Iniciação.Edição.0',
        'Iniciação.Pesquisa.1',
        'Iniciação.Visualização.0',
        'Professor.Edição.0',
        'Professor.Pesquisa.1',
        'Professor.Visualização.0',
        'Projeto.Cadastro.1',
        'Projeto.Edição.0',
        'Projeto.Pesquisa.1',
        'Projeto.Visualização.0',
        'Projeto.Visualização_Geral.0',
        'Projeto.Avaliar.0',
        'Projeto.Exclusão.0',
        'Setor.Cadastro.1',
        'Setor.Edição.0',
        'Setor.Pesquisa.1',
        'Setor.Visualização.0',
        'Usuário.Cadastro.1',
        'Usuário.Edição.0',
        'Usuário.Pesquisa.1',
        'Usuário.Visualização.0',
        'Usuário.Exclusão.0',
        'Visitante.Edição.0',
        'Visitante.Pesquisa.1',
        'Visitante.Visualização.0'
    ];
    public static $icones = [
        "Aluno" => "account_box",
        "Certificado" => "chrome_reader_mode",
        "Configuração" => "settings",
        "Curso" => "collections_bookmark",
        "Iniciação" => "school",
        "Professor" => "account_box",
        "Projeto" => "extension",
        "Setor" => "collections_bookmark",
        "Usuário" => "account_box",
        "Visitante" => "account_box",
        "Home" => "home"
    ];

    public static function get_post_action() {
        $params = \func_get_args();

        foreach ($params as $nome) {
            if (isset($_POST[$nome])) {
                return $nome;
            }
        }
    }

    public static function validaAcesso($controle) {
        $retorno = false;
        if (!Login::getUsuario() == NULL) {
            $usuario = Login::getUsuario();
            if ($usuario->getTipoAcesso() == $controle) {
                $retorno = true;
            }
        }

        return $retorno;
    }

    public static function formataDataAnoMesDia($data) {
        $dados = explode('-', $data);
        $dataFormatada = $dados[2] . '-' . self::mesPorNumero($dados[1]) . '-' . $dados[0];
        return $dataFormatada;
    }

    public static function formataDataDiaMesAno($data) {
        $dados = explode('-', $data);
        $dataFormatada = $dados[2] . ' de ' . self::mesPorExtenso($dados[1]) . ' de ' . $dados[0];
        return $dataFormatada;
    }

    private static function mesPorExtenso($mes) {
        return self::$meses[$mes];
    }

    private static function mesPorNumero($mes) {
        return array_search($mes, self::$meses);
    }

    public static function criarArrayPermissao(&$array, $string) {
        $arrayPermissao = explode('.', $string);
        $array[$arrayPermissao[0]][] = array(
            'funcao' => $arrayPermissao[1],
            'mostrar' => $arrayPermissao[2],
            'string' => $string
        );
    }

    public static function criarStringPermissao($permissoes) {
        $strings = [];
        foreach ($permissoes as $index => $permissao) {
            foreach ($permissao as $opcao) {
                $strings [] = $index . '.' . $opcao['funcao'] . '.' . $opcao['mostrar'];
            }
        }
        return $strings;
    }

    public static function usarPostParaCriarClasse($nomeClasse) {
        $nomeClasse = 'App\\Modelos\\' . $nomeClasse;

        $classe = new $nomeClasse();
        $dadosClasse = new \ReflectionClass($classe);

        foreach ($dadosClasse->getProperties() as $atributo) {
            $nomeAtributo = $atributo->name;
            if (isset($_POST[$nomeAtributo])) {
                $classe->set($nomeAtributo, $_POST[$nomeAtributo]);
            }
        }

        return $classe;
    }

    public static function tirarAcentos($string) {
        return utf8_encode(preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ç)/", "/(Ç)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U c C n N"), $string));
    }

    public static function validarCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
        // Valida tamanho
        if (strlen($cpf) != 11)
            return false;
        // Calcula e confere primeiro dígito verificador
        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
            $soma += $cpf{$i} * $j;
        $resto = $soma % 11;
        if ($cpf{9} != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Calcula e confere segundo dígito verificador
        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
            $soma += $cpf{$i} * $j;
        $resto = $soma % 11;
        return $cpf{10} == ($resto < 2 ? 0 : 11 - $resto);
    }

    public static function getBaseURL() {
        return '/birdevents/copex/'; #index.php?pg=';
        #return  '/copex/';
    }

}
