<?php
require '../models/usuarios.php';
require '../dao/usuarioDao.php';
require_once '../../config/config.php';

//INICIALIZANDO DAO DE USUARIOS
$uDao = new UsuarioDaoXml();

$name = ucwords( strtolower( filter_input( INPUT_POST, 'nome' ) ) );// RECEBENDO NOME DO USUARIO
$email = ucwords( strtolower( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL )) );//RECEBENDO EMAIL
$cpf = ucwords( strtolower( filter_input( INPUT_POST, 'cpfCnpj' ) ) );//RECEBENDO CPF OU CNPJ DO USUARIO
$pass = filter_input( INPUT_POST, 'pass' );//RECEBENDO SENHA DO USUARIO
$confirmPass = filter_input(INPUT_POST, 'confirmPass');//RECEBENDO CONFIRMAÇÃO DE SENHA
$isAdm = filter_input( INPUT_POST, 'isAdm' );//CONFIRMANDO SE É ADM OU COLABORADOR
$mainAcc = filter_input(INPUT_POST, 'main');//CONFIRMANDO SE ESSA CONTA É A PRINCIPAL DA EMPRESA

if(!$mainAcc){
    $mainAcc = 0;
}

//FUNÇÃO QUE CRIA UM TOKEN ALEATÓRIO DE 50 CARACTERES
function token( $tamanho = 50 ) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
    $charactersLength = strlen( $characters );
    $randomString = '';
    for ( $i = 0; $i < $tamanho; $i++ ) {
        $randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
    }
    return $randomString;
}

//VERIFICANDO SE TODOS OS DADOS FORAM RECEBIDOS CORRETAMENTE
if ( $name && $email && $cpf && $pass && $confirmPass && ($isAdm == 1 || $isAdm == 0) && ($mainAcc == 0 || $mainAcc == 1)) {
    
    $emailInvalido = $uDao->findByEmail($email); 

    if($emailInvalido){
        $_SESSION['conteudo'] = [
            'nome' => $name,
            'email' => '',
            'cpfCnpj' => $cpf
        ];

        
        $_SESSION['aviso'] = 'Email já existe em nosso sistema';
        if($mainAcc == 1){
            header('Location: ../views/adm/singup.php');
            exit;
        }else{
            header('Location: ../views/adm/participantes.php');
            exit;
        }
    }
 

    //VERIFICANDO SE A SENHA E A CONFIRMAÇÃO DA SENHA SÃO IGUAIS
    if($pass != $confirmPass){
        $_SESSION['conteudo'] = [
            'nome' => $name,
            'email' => $email,
            'cpfCnpj' => $cpf
        ];
        if($mainAcc == 1){
            $_SESSION['aviso'] = 'Senhas diferentes';
            header('Location: ../views/adm/singup.php');
            exit;
        }else{
            $_SESSION['aviso'] = 'Senhas diferentes';
            header('Location: ../views/adm/participantes.php');
            exit;
        }
    }

    //VERIFICANDO TAMANHO MINIMO DA SENHA
    if(strlen($pass) < 8){

        $_SESSION['conteudo'] = [
            'nome' => $name,
            'email' => $email,
            'cpfCnpj' => $cpf
        ];

        if($mainAcc == 1){
            $_SESSION['aviso'] = 'Senha deve ter no minimo 8 caracteres';
            header('Location: ../views/adm/singup.php');
            exit;
        }else{
            $_SESSION['aviso'] = 'Senha deve ter no minimo 8 caracteres';
            header('Location: ../views/adm/participantes.php');
            exit;
        }
    }


    $token = '';
    $tokenNovaEmpresa = '';

    //LOOPING QUE IMPOSSIBILITA A CRIAÇÃO DE USUARIOS COM TOKEN IGUAL
    do {
        $token = token(); //CRIANDO TOKEN PARA O USUARIO
        $tokenNovaEmpresa = token(); // CRIANDO TOKEN PARA EMPRESA
        $verify = $uDao->findByToken( $token ) && $uDao->findByToken( $tokenNovaEmpresa ); //VERIFICANDO DE TOKENS 

    }
    while( $verify );

    // VERIFICANDO SE USUARIO A SER ADICIONADO NECESSITA DE UM TOKEN DE EMPRESA NOVO OU USARA O DA EMPRESA JÁ EXISTENTE
    if($mainAcc == 1){
        $tokenEmpresa = $tokenNovaEmpresa;  
    }else{        
        $tokenEmpresa = $uDao->findByToken($_SESSION['token'])->getTokenEmpresa();        
    }

    $hash = password_hash( $pass, PASSWORD_DEFAULT ); // CRIPTOGRAFANDO SENHA RECEBIDA

    $u = new Usuarios();//INSTANCIANDO MODELO DE USUARIOS
    $u->setName( $name );//SETANDO NOME DO USUARIO NO MODELO
    $u->setEmail( strtolower( $email ) );//SETANDO EMAIL DO USUARIO NO MODELO
    $u->setCpf( $cpf );//SETANDO CPF DO USUARIO NO MODELO
    $u->setPass( $hash );//SETANDO SENHA DO USUARIO NO MODELO
    $u->setIsAdm( $isAdm );//SETANDO CODIGO DE ADM OU COLABORADOR DO USUARIO NO MODELO
    $u->setToken( $token );//SETANDO TOKEN DO USUARIO NO MODELO
    $u->setTokenEmpresa($tokenEmpresa);//SETANDO TOKEN DA EMPRESA NO MODELO
    $u->setMainAcc($mainAcc);//Setando validação se essa é a conta principal ou não

    $uDao->add( $u ); //ENVIANDO USUARIO PARA O DAO ADICIONAR NO XML

}else {
    $_SESSION['conteudo'] = [
        'nome' => $name,
        'email' => $email,
        'cpfCnpj' => $cpf
    ];

    $_SESSION['aviso'] = 'Preencha todos os campos'; //SE EMAIL OU SENHA NÃO FOREM PREENCHIDOS CRIAR SESSAO COM AVISO 
    if($mainAcc == 1){
        header( 'Location: ../views/adm/singup.php' );
        exit;
    }else{
        header( 'Location: ../views/adm/participantes.php' );
        exit;
    }
}


if($mainAcc == 1){
    header( 'Location: ../views/geral/login.php' );
    exit; 
}else{        
    header( 'Location: ../views/adm/participantes.php' );
    exit;
}

