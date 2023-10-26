<?php

require '../dao/usuarioDao.php'; 
require '../models/setores.php';
require '../dao/setoresDao.php'; 

$uDao = new UsuarioDaoXml();
$sDao = new SetoresDaoXml();



$setorNome = ucwords( strtolower( filter_input( INPUT_POST, 'setor' ) ) );
$tokenSetor = filter_input( INPUT_POST, 'tokenSetor' );
$senha = filter_input(INPUT_POST, 'senha');
$usuario = $uDao->findByToken($_SESSION['token']);



if($setorNome && $senha){
    if(password_verify($senha, $usuario->getPass())){
       
        $setor = $sDao->findByToken($tokenSetor);

        $s = new Setores();
        $s->setId($setor->getId());
        $s->setName($setorNome);
        $s->setTokenSetor($tokenSetor);
        $s->setTokenEmpresa($setor->getTokenEmpresa());

        $sDao->update($s);
    }else{
        $_SESSION['avisoAdd'] = 'Senha incorreta';
    }
}else{
    $_SESSION['avisoAdd'] = 'Preencha todos os campos';
}

header( 'Location: ../views/adm/setor.php' );
exit;