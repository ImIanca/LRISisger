<?php
require '../models/Tarefas.php';
require_once '../dao/tarefasDao.php';
//INICIALIZANDO DAO DE TAREFAS
$tDao = new TarefasDaoXml();

$idAdm = filter_input(INPUT_POST, 'idAdm');//RECEBENDO ID DO ADM
$idColabora = filter_input(INPUT_POST, 'name');//RECEBENDO ID DO COLABORADOR
$tituloTarefa = ucwords( strtolower(filter_input(INPUT_POST, 'task_title')));//RECEBENDO TITULO DA TAREFA
$dataInicial = filter_input(INPUT_POST, 'begin_date');//RECEBENDO DATA INICIAL DA TAREFA
$dataLimite = filter_input(INPUT_POST, 'last_date');//RECEBENDO DATA LIMITE DA TAREFA
$descricao = filter_input(INPUT_POST, 'task_description');//RECEBENDO DESCRIÇÃO DA TAREFA
$tokenEmpresa = filter_input(INPUT_POST, 'tokenEmpresa');//RECEBENDO TOKEN DA EMPRESA
$status = 2;// SETANDO STATUS INICIAL PARA A TAREFA


// VERIFICANDO SE TODOS OS DADOS FORAM RECEBIDOS
if($idAdm && $idColabora && $tituloTarefa && $dataInicial && $dataLimite && $descricao && $status && $tokenEmpresa){
    $t = new Tarefas();//INSTACIANDO MODELO DE TAREFAS
    $t->setTituloTarefa($tituloTarefa);//SETANDO TITULO DA TAREFA
    $t->setStatus($status);//SETANDO STATUS DA TAREFA
    $t->setDescricao($descricao);//SETANDO DESCRIÇÃO DA TAREFA
    $t->setDataInicial($dataInicial);//SETANDO DATA INICIAL DA TAREFA
    $t->setDataLimite($dataLimite);//SETANDO DATA LIMITE DA TAREFA
    $t->setIdColabora($idColabora);// SETANDO ID DO COLABORADOR 
    $t->setIdAdm($idAdm);//SETANDO ID DO ADM DA TAREFA
    $t->setTokenEmpresa($tokenEmpresa);//SETANDO TOKEN DA EMPRESA

    $tDao->add($t);//ENVIANDO DADOS A SEREM ADICIONADOS NO XML PARA O DAO
}else{
    header('Location: ../views/adm/control.php');
    exit;
}


header('Location: ../views/adm/control.php');
exit;