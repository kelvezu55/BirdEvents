<?php

namespace App\Controladores;

use Nucleo\Controlador;
use Nucleo\EntidadeDAO;
use App\Modelos\Certificado;
use App\Modelos\CertificadoDigital;
use App\Modelos\Login;
use App\Modelos\VinculoCertificadoUsuario;
use App\Util\Util;

class CertificadoControle extends Controlador {

    private $certificado;
    private $dao;
    private $marcacoes;

    public function __construct() {
        $this->layout = "layout_base";
        $this->certificado = new Certificado();
        $this->dao = new EntidadeDAO($this->certificado);
    }

    public function processar($parametros) {
        //Colhe a ação do botão digitado
        $acao = Util::get_post_action(
                        'visualizar', 'editar', 'salvar', 'excluir', 'vincular', 'desvincular', 'vinculados', 'entregar', 'pesquisar_certificados', 'pesquisar_usuarios', 'pesquisar_por_usuario', 'gerar'
        );


        $usuario = new \App\Modelos\Usuario();
        $usuario->setNome('José');
        $usuario->setTipoAcesso('professor');

        $this->marcacoes = [
            'usuario@nome' => '',
            'usuario@tipoAcesso' => ''
        ];

        //Colhe a pagina que deve ser apresentada
        $pagina = '';
        if (isset($parametros[1])) {
            $pagina = $parametros[1];
        }
        switch ($pagina) {
            case 'visualizacao':
                //Prepara visualização da página
                $this->visao = 'form_certificado';
                $this->dados['pagina'] = "Visualização de Certificado";
                $this->dados['certificado'] = $this->certificado;
                $this->dados['marcacoes'] = $this->marcacoes;
                $this->dados['acao'] = 'editar';
                $this->dados['disabled'] = 'disabled';

                //Verifica se houve ação
                if ($acao == 'editar') {
                    $this->redirecionar('certificado/edicao');
                } else if ($acao == 'gerar') {
                    $this->gerarCertificado(
                            $this->certificado->getTexto(), $this->certificado->getImagem(), $usuario
                    );
                }

                break;

            case 'edicao':
                //Prepara visualização da página
                $this->visao = 'form_certificado';
                $this->dados['pagina'] = "Edição de Certificado";
                $this->dados['certificado'] = $this->certificado;
                $this->dados['marcacoes'] = $this->marcacoes;
                $this->dados['acao'] = 'salvar';
                $this->dados['disabled'] = '';

                //Verifica se houve ação
                if ($acao == 'salvar') {
                    if ($this->certificado instanceof Certificado) {
                        $certificado = new Certificado();

                        $certificado->setId($this->certificado->getId());
                        $certificado->setNome($_POST['nome']);
                        $certificado->setCaixaReferente($_POST['caixaReferente']);
                        $certificado->setAnoExercicio($_POST['anoExercicio']);

                        $this->dao->mudarEntidade('certificado');
                    } else {
                        $certificado = new CertificadoDigital();
                        $certificado->setId($this->certificado->getId());
                        $certificado->setNome($_POST['nome']);
                        $certificado->setAnoExercicio($_POST['anoExercicio']);
                        $certificado->setTexto($_POST['texto']);
                        if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
                            $imagem = fopen($_FILES['imagem']['tmp_name'], "r");
                            $tamanho = filesize($_FILES['imagem']['tmp_name']);
                            $imagemCodificada = base64_encode(fread($imagem, $tamanho));
                        } else {
                            $imagemCodificada = $this->certificado->getImagem();
                        }
                        $certificado->setImagem($imagemCodificada);
                        $this->dao->mudarEntidade('certificadodigital');
                    }


                    $this->dao->salvar($certificado);

                    $this->certificado = $certificado;
                    $this->dados['certificado'] = $this->certificado;

                    $this->retornos[] = "Certificado editado com sucesso!";
                } else if ($acao == 'gerar') {

                    $texto = $_POST['texto'];

                    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
                        $imagem = fopen($_FILES['imagem']['tmp_name'], "r");
                        $tamanho = filesize($_FILES['imagem']['tmp_name']);
                        $imagemCodificada = base64_encode(fread($imagem, $tamanho));
                    } else {
                        $imagemCodificada = $this->certificado->getImagem();
                    }

                    $this->gerarCertificado(
                            $texto, $imagemCodificada, $usuario
                    );
                }

                break;

            case 'cadastro':
                //Prepara visualização da página
                $this->visao = 'form_certificado';
                $this->dados['pagina'] = "Cadastro de Certificado";
                $this->dados['marcacoes'] = $this->marcacoes;
                $this->dados['acao'] = 'salvar';
                $this->dados['disabled'] = '';
                unset($this->dados['certificado']);

                //Verifica se houve ação
                if ($acao == 'salvar') {

                    $certificado;

                    if ($_POST['tipoCertificado'] == 'fisico') {
                        $certificado = new Certificado();
                        $certificado->setId(0);
                        $certificado->setNome($_POST['nome']);
                        $certificado->setCaixaReferente($_POST['caixaReferente']);
                        $certificado->setAnoExercicio($_POST['anoExercicio']);
                        $this->dao->mudarEntidade('certificado');
                    } else {
                        $certificado = new CertificadoDigital();
                        $certificado->setId(0);
                        $certificado->setNome($_POST['nome']);
                        $certificado->setAnoExercicio($_POST['anoExercicio']);
                        $certificado->setTexto($_POST['texto']);
                        if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
                            $imagem = fopen($_FILES['imagem']['tmp_name'], "r");
                            $tamanho = filesize($_FILES['imagem']['tmp_name']);
                        } else {
                            $imagem = fopen(__DIR__ . "img\img_padrao.png");
                            $tamanho = filesize(__DIR__ . "img\img_padrao.png");
                        }

                        $imagemCodificada = base64_encode(fread($imagem, $tamanho));
                        $certificado->setImagem($imagemCodificada);
                        $this->dao->mudarEntidade('certificadodigital');
                    }
                    $this->dao->salvar($certificado);
                    $this->certificado = $certificado;

                    $this->retornos[] = "Certificado cadastrado com sucesso!";
                } else if ($acao == 'gerar') {

                    $texto = $_POST['texto'];

                    if (isset($_FILES['imagem'])) {
                        $imagem = fopen($_FILES['imagem']['tmp_name'], "r");
                        $tamanho = filesize($_FILES['imagem']['tmp_name']);
                    } else {
                        $imagem = fopen(__DIR__ . "img\img_padrao.png");
                        $tamanho = filesize(__DIR__ . "img\img_padrao.png");
                    }

                    $imagemCodificada = base64_encode(fread($imagem, $tamanho));


                    $this->gerarCertificado(
                            $texto, $imagemCodificada, $usuario
                    );
                }
                break;

            case 'pesquisa_por_usuario':
                $this->visao = 'pesquisa_certificado_por_usuario';
                $this->dados['pagina'] = "Lista de Certificados por Usuario";
                //$this->pesquisar($acao);

                if (isset($_GET['url']) && isset(explode('/', $_GET['url'])[3])) {
                    $codigo = explode('/', $_GET['url'])[3];
                    $usuario = substr($codigo, 1, strpos($codigo, 'i') - 1);
                    $index = substr($codigo, strpos($codigo, 'i') + 1);
                    if ($this->dados['certificados'][$usuario][$index] instanceof CertificadoDigital) {
                        $certificado = $this->dados['certificados'][$usuario][$index];
                        $this->gerarCertificado(
                                $certificado->getTexto(), $certificado->getImagem(), (new EntidadeDAO(new \App\Modelos\Usuario()))->pesquisarPorId($usuario)
                        );
                    }
                }

                
                if ($acao == "entregar") {


                    $d = new EntidadeDAO(new VinculoCertificadoUsuario());

                    $vinculosDoUsuario = $d->pesquisarOnde('usuario', $_POST['idUsuario']);

                    $entregues = isset($_POST['entregues']) ? $_POST['entregues'] : [];

                    foreach ($vinculosDoUsuario as $vinculo) {

                        if (in_array($vinculo->getId(), $entregues)) {
                            $vinculo->setSituacao('Entregue');
                        } else {
                            $vinculo->setSituacao('Não Entregue');
                        }
                        $d->salvar($vinculo);
                    }

                    $usuario = $d->mudarEntidade('usuario')->pesquisarPorId($_POST['idUsuario']);
                    $_POST['nome'] = $usuario->getNome();
                }
                
                $d = new EntidadeDAO(new \App\Modelos\Usuario());
                if (isset($_POST['nome']) && $_POST['nome'] !== '') {
                    $usuarios = $d->pesquisarPorNome($_POST['nome']);

                    unset($this->dados['certificados']);
                    unset($this->dados['vinculos']);

                    if (count($usuarios) > 0) {

                        $this->dados['usuarios'] = $usuarios;

                        $idCertificados = [];


                        foreach ($usuarios as $usuario) {
                            $d->mudarEntidade('vinculocertificadousuario');
                            $vinculos = $d->pesquisarOnde('usuario', $usuario->getId());

                            if (count($vinculos) > 0) {
                                foreach ($vinculos as $vinculo) {
                                    $idCertificados[] = $vinculo->getCertificado();
                                }

                                $d->mudarEntidade('certificado');
                                $certificados = $d->pesquisarIN('id', $idCertificados);
                                unset($idCertificados);

                                $this->dados['certificados'][$usuario->getId()] = $certificados;
                                $this->dados['vinculos'][$usuario->getId()] = $vinculos;
                            }

                            $d->mudarEntidade('vinculocertificadodigitalusuario');
                            $vinculosDigitais = $d->pesquisarOnde('usuario', $usuario->getId());

                            if (count($vinculosDigitais) > 0) {
                                foreach ($vinculosDigitais as $vinculo) {
                                    $idCertificados[] = $vinculo->getCertificado();
                                }

                                $d->mudarEntidade('certificadodigital');
                                $certificados = $d->pesquisarIN('id', $idCertificados);
                                unset($idCertificados);

                                $this->dados['certificados'][$usuario->getId()] = isset($this->dados['certificados'][$usuario->getId()]) ? array_merge($certificados, $this->dados['certificados'][$usuario->getId()]) : $certificados;
                                $this->dados['vinculos'][$usuario->getId()] = isset($this->dados['vinculos'][$usuario->getId()]) ? array_merge($vinculosDigitais, $this->dados['vinculos'][$usuario->getId()]) : $vinculosDigitais;
                            }
                        }
                    } else {
                        unset($this->dados['usuarios']);
                    }
                } else {
                    unset($this->dados['usuarios']);
                }


                break;

            case 'vincular_certificados':
                $this->visao = 'vincular_certificado';
                $this->dados['pagina'] = "Vincular Certificados";

                $dao = new EntidadeDAO($this->certificado);

                if (!isset($_POST['nome_usuario']) && !isset($_POST['nome_certificado'])) {

                    $certificadosFisicosDisponiveis = $dao->mudarEntidade('certificado')->pesquisarLIVRE('order by id desc limit 50;', array()); #unset($this->dados['certificadosDisponiveis']);
                    $certificadosDigitaisDisponiveis = $dao->mudarEntidade('certificadodigital')->pesquisarLIVRE('order by id desc limit 50;', array());
                    $this->dados['certificadosDisponiveis'] = array_merge($certificadosFisicosDisponiveis, $certificadosDigitaisDisponiveis);
                    $this->dados['usuarios'] = $dao->mudarEntidade('usuario')->pesquisarLIVRE('order by id desc limit 50;', array());
                }
                //Verifica se houve ação
                switch ($acao) {
                    case 'pesquisar_certificados':
                        if (isset($_POST['nome_certificado']) && $_POST['nome_certificado'] !== '') {
                            $certificadosFisicosDisponiveis = $dao->mudarEntidade('certificado')->pesquisarPorNome($_POST['nome_certificado']);
                            $certificadosDigitaisDisponiveis = $dao->mudarEntidade('certificadodigital')->pesquisarPorNome($_POST['nome_certificado']);
                            $this->dados['certificadosDisponiveis'] = array_merge($certificadosFisicosDisponiveis, $certificadosDigitaisDisponiveis);
                        } else {
                            $certificadosFisicosDisponiveis = $dao->mudarEntidade('certificado')->pesquisarLIVRE('order by id desc limit 50;', array());
                            $certificadosDigitaisDisponiveis = $dao->mudarEntidade('certificadodigital')->pesquisarLIVRE('order by id desc limit 50;', array());
                            $this->dados['certificadosDisponiveis'] = array_merge($certificadosFisicosDisponiveis, $certificadosDigitaisDisponiveis);
                        }
                        break;

                    case 'pesquisar_usuarios':
                        if (isset($_POST['nome_usuario']) && $_POST['nome_usuario'] !== '') {
                            $this->dados['usuarios'] = $dao->mudarEntidade('usuario')->pesquisarPorNome($_POST['nome_usuario']);
                        } else {
                            $this->dados['usuarios'] = $dao->mudarEntidade('usuario')->pesquisarLIVRE('order by id desc limit 50;', array()); #unset($this->dados['usuarios']);
                        }
                        break;

                    case 'vincular':
                        $daoVinc = new EntidadeDAO(new VinculoCertificadoUsuario());
                        $daoVincDigital = new EntidadeDAO(new \App\Modelos\VinculoCertificadoDigitalUsuario);
                        $daoUsuario = new EntidadeDAO(new \App\Modelos\Usuario());
                        $daoAluno = new EntidadeDAO(new \App\Modelos\Aluno());


                        if (isset($_POST['indexCertificado']) && isset($_POST['idsUsuarios'])) {
                            $certificadoSelecionado = $this->dados['certificadosDisponiveis'][$_POST['indexCertificado']];

                            $usuarios = $daoUsuario->pesquisarIN('id', $_POST['idsUsuarios']);

                            if (count($usuarios) > 0) {

                                foreach ($usuarios as $usuario) {
                                    $vinculos;
                                    if ($certificadoSelecionado instanceof Certificado) {
                                        $vinculos = $daoVinc->pesquisarOnde('usuario', $usuario->getId());
                                    } else {
                                        $vinculos = $daoVincDigital->pesquisarOnde('usuario', $usuario->getId());
                                    }

                                    $devoSalvar = true;

                                    foreach ($vinculos as $v) {
                                        if ($v->getCertificado() == $certificadoSelecionado->getId()) {
                                            $devoSalvar = false;
                                            break;
                                        }
                                    }

                                    if ($devoSalvar) {

                                        if ($certificadoSelecionado instanceof Certificado) {
                                            $vinculo = new VinculoCertificadoUsuario();
                                            $vinculo->setId(0);
                                            $vinculo->setCertificado($certificadoSelecionado->getId());
                                            $vinculo->setUsuario($usuario->getId());
                                            $vinculo->setSituacao("Não Entregue");

                                            $daoVinc->salvar($vinculo);
                                        } else {
                                            $vinculo = new \App\Modelos\VinculoCertificadoDigitalUsuario();
                                            $vinculo->setId(0);
                                            $vinculo->setCertificado($certificadoSelecionado->getId());
                                            $vinculo->setUsuario($usuario->getId());

                                            $daoVincDigital->salvar($vinculo);
                                        }


                                        $this->retornos[] = 'Usuário ' . $usuario->getNome() . ' vinculado com sucesso!';
                                    } else {
                                        $this->retornos[] = 'O usuário ' . $usuario->getNome() . ' já estava vinculado.';
                                    }
                                }
                            } else {
                                $this->retornos[] = 'Não encontrado usuário correspondente';
                            }
                        } else if (isset($_FILES['arquivo']) && ($_FILES['arquivo']['name'] != '')) {

                            $certificadoSelecionado = $this->dados['certificadosDisponiveis'][$_POST['indexCertificado']];

                            $dom = new \DOMDocument();
                            $dom->load($_FILES['arquivo']['tmp_name']);
                            $linhas = $dom->getElementsByTagName('Row');
                            foreach ($linhas as $linha) {
                                $celulas = $linha->getElementsByTagName('Cell');
                                foreach ($celulas as $index => $celula) {

                                    if (isset($_POST['tipoVinculo'])) {
                                        if ($_POST['tipoVinculo'] == 'matricula') {
                                            $alunos = $daoAluno->pesquisarOnde('matricula', $celula->nodeValue);
                                            if (count($alunos) > 0) {

                                                $vinculos;
                                                if ($certificadoSelecionado instanceof Certificado) {
                                                    $vinculos = $daoVinc->pesquisarOnde('usuario', $alunos[0]->getUsuario()->getId());
                                                } else {
                                                    $vinculos = $daoVincDigital->pesquisarOnde('usuario', $alunos[0]->getUsuario()->getId());
                                                }
                                                $devoSalvar = true;

                                                foreach ($vinculos as $v) {
                                                    if ($v->getCertificado() == $certificadoSelecionado->getId()) {
                                                        $devoSalvar = false;
                                                        break;
                                                    }
                                                }

                                                if ($devoSalvar) {

                                                    if ($certificadoSelecionado instanceof Certificado) {
                                                        $vinculo = new VinculoCertificadoUsuario();
                                                        $vinculo->setId(0);
                                                        $vinculo->setCertificado($certificadoSelecionado->getId());
                                                        $vinculo->setUsuario($alunos[0]->getUsuario()->getId());
                                                        $vinculo->setSituacao("Não Entregue");

                                                        $daoVinc->salvar($vinculo);
                                                    } else {
                                                        $vinculo = new \App\Modelos\VinculoCertificadoDigitalUsuario();
                                                        $vinculo->setId(0);
                                                        $vinculo->setCertificado($certificadoSelecionado->getId());
                                                        $vinculo->setUsuario($alunos[0]->getUsuario()->getId());

                                                        $daoVincDigital->salvar($vinculo);
                                                    }
                                                } else {
                                                    $this->retornos[] = 'O aluno de Matrícula : ' . $celula->nodeValue . ' já estava vinculado.';
                                                }
                                            } else {
                                                $this->retornos[] = 'Não encontrado aluno correspondente para o Matrícula : ' . $celula->nodeValue;
                                            }
                                        } else if ($_POST['tipoVinculo'] == 'cpf') {
                                            $usuarios = $daoUsuario->pesquisarOnde('cpf', $celula->nodeValue);
                                            if (count($usuarios) > 0) {
                                                $vinculos;
                                                if ($certificadoSelecionado instanceof Certificado) {
                                                    $vinculos = $daoVinc->pesquisarOnde('usuario', $usuarios[0]->getId());
                                                } else {
                                                    $vinculos = $daoVincDigital->pesquisarOnde('usuario', $usuarios[0]->getId());
                                                }
                                                $devoSalvar = true;

                                                foreach ($vinculos as $v) {
                                                    if ($v->getCertificado() == $certificadoSelecionado->getId()) {
                                                        $devoSalvar = false;
                                                        break;
                                                    }
                                                }

                                                if ($devoSalvar) {
                                                    if ($certificadoSelecionado instanceof Certificado) {
                                                        $vinculo = new VinculoCertificadoUsuario();
                                                        $vinculo->setId(0);
                                                        $vinculo->setCertificado($certificadoSelecionado->getId());
                                                        $vinculo->setUsuario($usuarios[0]->getId());
                                                        $vinculo->setSituacao("Não Entregue");

                                                        $daoVinc->salvar($vinculo);
                                                    } else {
                                                        $vinculo = new \App\Modelos\VinculoCertificadoDigitalUsuario();
                                                        $vinculo->setId(0);
                                                        $vinculo->setCertificado($certificadoSelecionado->getId());
                                                        $vinculo->setUsuario($usuarios[0]->getId());

                                                        $daoVincDigital->salvar($vinculo);
                                                    }
                                                } else {
                                                    $this->retornos[] = 'O usuário de CPF : ' . $celula->nodeValue . ' já estava vinculado.';
                                                }
                                            } else {
                                                $this->retornos[] = 'Não encontrado usuário correspondente para o CPF : ' . $celula->nodeValue;
                                            }
                                        }
                                    }
                                }
                            }
                        }


                        break;
                }
                break;

            case 'vinculados':
                //Prepara visualização da página

                $this->visao = 'usuarios_vinculados';
                $this->dados['pagina'] = $this->certificado->getNome();
                $this->dados['certificado'] = $this->certificado;

                $daoVinc = new EntidadeDAO(new VinculoCertificadoUsuario());
                $daoUsuario = new EntidadeDAO(new \App\Modelos\Usuario());

                $vinculos = $daoVinc->pesquisarOnde('certificado', $this->certificado->getId());

                $usuariosVinculados = [];

                foreach ($vinculos as $vinculo) {
                    $usuariosVinculados[] = $daoUsuario->pesquisarOnde('id', $vinculo->getUsuario())[0];
                }

                if (count($usuariosVinculados) > 0) {
                    $this->dados['usuariosVinculados'] = $usuariosVinculados;
                }


                //Verifica se houve ação
                if ($acao == 'desvincular') {
                    if (isset($_POST['idsUsuarios'])) {
                        $vinculosParaExcluir = $daoVinc->pesquisarOnde('certificado', $this->certificado->getId());
                        foreach ($_POST['idsUsuarios'] as $id) {
                            foreach ($vinculosParaExcluir as $vinc) {
                                if ($vinc->getUsuario() == $id) {
                                    $daoVinc->excluir($vinc->getId());
                                }
                            }
                        }
                    }
                    unset($this->dados['usuariosVinculados']);
                    $this->redirecionar('certificado/vinculados');
                }

                break;

            default:
                $this->visao = 'pesquisa_certificado';
                $this->dados['pagina'] = "Lista de Certificados";
                $this->pesquisar($acao);

                break;
        }
    }

    private function pesquisar($acao) {

        switch ($acao) {
            case 'visualizar':
                if (isset($_POST['index']) && $_POST['index'] !== '') {
                    $index = array_shift($_POST['index']);

                    $certificadoSelecionado = $this->dados['resultado'][$index];

                    if ($certificadoSelecionado instanceof Certificado) {
                        $this->dao->mudarEntidade('certificado');
                    } else if ($certificadoSelecionado instanceof CertificadoDigital) {
                        $this->dao->mudarEntidade('certificadodigital');
                    }

                    $this->certificado = $this->dao->pesquisarPorId($certificadoSelecionado->getId());

                    $this->redirecionar('certificado/visualizacao');
                }
                break;

            case 'editar':
                if (isset($_POST['index']) && $_POST['index'] !== '') {
                    $index = array_shift($_POST['index']);

                    $certificadoSelecionado = $this->dados['resultado'][$index];

                    if ($certificadoSelecionado instanceof Certificado) {
                        $this->dao->mudarEntidade('certificado');
                    } else if ($certificadoSelecionado instanceof CertificadoDigital) {
                        $this->dao->mudarEntidade('certificadodigital');
                    }

                    $this->certificado = $this->dao->pesquisarPorId($certificadoSelecionado->getId());

                    $this->dados['acao'] = 'salvar';
                    $this->dados['disabled'] = '';

                    $this->redirecionar('certificado/edicao');
                }
                break;

            case 'excluir':
                foreach ($_POST['id'] as $id) {
                    $this->dao->excluir($id);
                    if (isset($_POST['nome']) && $_POST['nome'] !== '') {
                        $this->dados['resultado'] = $this->dao->pesquisarPorNome($_POST['nome']);
                    } else {
                        unset($this->dados['resultado']);
                    }
                }
                $this->retornos[] = "Certificado excluído com sucesso!";

                break;
           
            case 'vinculados':
                if (isset($_POST['index']) && $_POST['index'] !== '') {
                    $index = array_shift($_POST['index']);

                    $certificadoSelecionado = $this->dados['resultado'][$index];

                    if ($certificadoSelecionado instanceof Certificado) {
                        $this->dao->mudarEntidade('certificado');
                    } else if ($certificadoSelecionado instanceof CertificadoDigital) {
                        $this->dao->mudarEntidade('certificadodigital');
                    }

                    $this->certificado = $this->dao->pesquisarPorId($certificadoSelecionado->getId());

                    $this->redirecionar('certificado/vinculados');
                }
                break;

            default:

                if (isset($_GET['url']) && isset(explode('/', $_GET['url'])[3])) {
                    $index = explode('/', $_GET['url'])[3];
                    if ($this->dados['resultado'][$index] instanceof CertificadoDigital) {
                        $certificado = $this->dados['resultado'][$index];
                        $this->gerarCertificado($certificado->getTexto(), $certificado->getImagem(), Login::getUsuario());
                    }
                }

                if (Login::checaPermissao("Certificado.Visualização_Geral")) {
                    if (isset($_POST['nome']) && $_POST['nome'] !== '') {
                        $this->dao->mudarEntidade('certificado');
                        $certificadosComEsseNome = $this->dao->pesquisarPorNome($_POST['nome']);
                        $certificadosComEsseAno = $this->dao->pesquisarOnde('anoExercicio', $_POST['nome']);
                        $this->dao->mudarEntidade('certificadodigital');
                        $certificadosDigitaisComEsseNome = $this->dao->pesquisarPorNome($_POST['nome']);
                        $this->dados['resultado'] = array_merge($certificadosComEsseNome, $certificadosComEsseAno, $certificadosDigitaisComEsseNome);
                    } else if (!isset($_POST['nome'])) {

                        $this->dao->mudarEntidade('certificado');
                        $certificadosFisicos = $this->dao->pesquisarLIVRE('order by id desc limit 50;', array());
                        $this->dao->mudarEntidade('certificadodigital');
                        $certificadosDigitais = $this->dao->pesquisarLIVRE('order by id desc limit 50;', array());
                        $this->dados['resultado'] = array_merge($certificadosFisicos, $certificadosDigitais);
                    } else {
                        unset($this->dados['resultado']);
                    }
                } else {

                    $daoVinculo = new EntidadeDAO(new VinculoCertificadoUsuario());
                    $vinculosFisicos = $daoVinculo->pesquisarOnde('usuario', Login::getUsuario()->getId());

                    $daoVinculoDigital = new EntidadeDAO(new \App\Modelos\VinculoCertificadoDigitalUsuario());
                    $vinculosDigitais = $daoVinculoDigital->pesquisarOnde('usuario', Login::getUsuario()->getId());

                    $vinculos = array_merge($vinculosFisicos, $vinculosDigitais);

                    $idCertificadosFisicos = [];
                    $idCertificadosDigitais = [];

                    if (count($vinculos) > 0) {
                        foreach ($vinculos as $vinculo) {
                            if ($vinculo instanceof VinculoCertificadoUsuario) {
                                $idCertificadosFisicos[] = $vinculo->getCertificado();
                            } else {
                                $idCertificadosDigitais[] = $vinculo->getCertificado();
                            }
                        }

                        $daoVinculo->mudarEntidade('certificado');
                        $certificadosFisicos = $daoVinculo->pesquisarIN('id', $idCertificadosFisicos);
                        unset($idCertificadosFisicos);

                        $daoVinculoDigital->mudarEntidade('certificadodigital');
                        $certificadosDigitais = $daoVinculoDigital->pesquisarIN('id', $idCertificadosDigitais);
                        unset($idCertificadosDigitais);

                        $certificados = array_merge($certificadosFisicos, $certificadosDigitais);

                        $this->dados['vinculosDesseUsuario'] = $vinculos;
                        $this->dados['resultado'] = [];

                        if (isset($_POST['nome']) && $_POST['nome'] !== '') {
                            foreach ($certificados as $certificado) {
                                if ($certificado instanceof Certificado) {
                                    if (strpos($certificado->getNome(), $_POST['nome']) > -1 || $certificado->getAnoExercicio() == $_POST['nome']) {
                                        $this->dados['resultado'][] = $certificado;
                                    }
                                } else {
                                    if (strpos($certificado->getNome(), $_POST['nome']) > -1) {
                                        $this->dados['resultado'][] = $certificado;
                                    }
                                }
                            }
                        } else if (!isset($_POST['nome'])) {
                            $this->dados['resultado'] = $certificados;
                        } else {
                            unset($this->dados['resultado']);
                        }
                    }
                }
                #----------------------
                break;
        }
    }

    private function gerarCertificado($texto, $imagemCodificada, $usuario) {
        $marcacoes = [
            'usuario@nome' => $usuario->getNome(),
            'usuario@tipoAcesso' => $usuario->getTipoAcesso()
        ];

        $texto = $this->transformarTexto($texto, $marcacoes);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            
        <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Dosis|Lora|PT+Sans|Jua" rel="stylesheet">
            
        <style>
        * {
            margin:0px;
            padding:0px;
        }
        #caixa {
            position: absolute; left: 0px; right: 0px; top: 0px; 
            width: 1100px; 
            height: 778px;  
            margin: 1% 1%;
        }
        #caixa_texto{
            position: absolute; 
            //top: 50%;            
            padding-left: 50px; 
            padding-rigth: 150px;
            
        }
               
        
        </style>
        
    </head>
    <body>
        <div id="caixa">    
            
            <div id="caixa_texto">
                $texto                               
            </div>
           <img src="data:image/jpg;base64, $imagemCodificada" width="100%" height="100%">
        </div>
    </body>
</html>
HTML;


        require_once("vendor\dompdf\autoload.inc.php");

        $dom = new \Dompdf\Dompdf();

        $dom->load_html($html);

        $dom->setPaper('A4', 'landscape');

        $dom->render();

// Output the generated PDF to Browser
        $dom->stream(
                "certificado", // Nome do arquivo de saída
                array(
            "Attachment" => false // Para download, altere para true 
                )
        );
    }

    private function transformarTexto($texto, $marcacoes) {
        foreach ($marcacoes as $chave => $valor) {
            $texto = str_replace($chave, $valor, $texto);
        }
        return $texto;
    }

}
