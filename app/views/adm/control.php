<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/adm/control.css">
    <link rel="stylesheet" href="../../../public/css/general/main.css">
    <link rel="shortcut icon" href="../../../public/img/svgs/favi.png" type="image/x-icon">
    <title>SISGER</title>
</head>

<body>

    <?php
        require realpath( dirname( __FILE__ ) . '/../../../config/config.php' );
        require realpath( dirname( __FILE__ ) . '/../../models/Auth.php');
        require realpath( dirname( __FILE__ ) . '/../../dao/usuarioDao.php');
        require realpath( dirname( __FILE__ ) . '/../../scripts-php/control.php');
        require realpath( dirname( __FILE__ ) . '/../../dao/tarefasDao.php');
        require realpath( dirname( __FILE__ ) . '/../../dao/setoresDao.php');

        $auth = new Auth();
        $userInfo = $auth->checkToken(); // AUTENTICAÇÃO DE TOKEN DO USUARIO PARA CONFIRMAR O LOGIN

        //VERIFICANDO SE HÁ USUARIO LOGADO
        if($userInfo == false){
            header("Location: ../../services/logOutAction.php");
            exit;
        }
        
        //VERIFICANDO SE USUARIO É UM ADMINISTRADOR
        if($userInfo->getIsAdm() == 0){
            header("Location: ../worker/control_colabora.php");
            exit;
        }
        
        $uDao = new UsuarioDaoXml();// INICIANDO DAO DE USUARIOS
        $usersColabora = $uDao->findAll(0, $userInfo->getTokenEmpresa());//RECEBENDO FUNCIONÁRIOS DA EMPRESA

        $sDao = new SetoresDaoXml();// INICIANDO DAO DE SETORES
        $setores = $sDao->findAll($userInfo->getTokenEmpresa());// RECEBENDO TAREFAS DA EMPRESA

        //COLOCANDO SETORES EM ORDEM ALFABÉTICA
        function ordenarSetor($setorOne, $setorTwo){
            return strcasecmp($setorOne->getName(), $setorTwo->getName());
        }
        
        usort($setores, 'ordenarSetor');   

        $tDao = new TarefasDaoXml();// INICIANDO DAO DE TAREFAS
        $tarefasStatus =  $tDao->findAll($userInfo->getTokenEmpresa());

        //VERIFICANDO A DATA DAS TAREFAS, SE TIVER ALGUMA ATRASADA, MUDAMOS O STATUS
        function verificaStatus($tarefas){
            $dataAtual = new DateTime();
            $dataAtual->setTime(0, 0);
        
            foreach($tarefas as $tarefa){
                $dataTarefa = new DateTime($tarefa->getDataLimite());
                
                if($dataAtual > $dataTarefa){
                    if($tarefa->getStatus() != 1 && $tarefa->getStatus() != 4){
                        $tarefa->setStatus(5);
                        $tDao = new TarefasDaoXml();
                        $tDao->update($tarefa);
                    }
                }
            }
                      
        }

        verificaStatus($tarefasStatus);
   
        
        $tarefasGeral = $tDao->findAll($userInfo->getTokenEmpresa());// RECEBENDO TAREFAS DA EMPRESA;
        

        // FUNÇÃO QUE ORDENA AS TAREFAS NA TELA DE ACORDO COM O STATUS
        function ordenarStatus($statusOne, $statusTwo){
            return  $statusTwo->getStatus() - $statusOne->getStatus();
        }
        usort($tarefasGeral, 'ordenarStatus');   
        
        echo "<script>let tarefasGeral = [];</script>";

        foreach($tarefasGeral as $tarefa){
            $dataInicial = new DateTime($tarefa->getDataInicial());
            $dataInicialFormatada = $dataInicial->format('d/m/Y');

            $dataLimite = new DateTime($tarefa->getDataLimite());
            $dataLimiteFormatada = $dataLimite->format('d/m/Y');

            echo "<script>array = {
                    id: ".$tarefa->getId().",
                    nomeColabora: '".$uDao->findById($tarefa->getIdColabora())->getName()."',
                    tituloTarefa: '".$tarefa->getTituloTarefa()."',                    
                    status: ".$tarefa->getStatus().",
                    descricao: '".$tarefa->getDescricao()."',                    
                    dataInicial: '".$dataInicialFormatada."',
                    dataLimite: '".$dataLimiteFormatada."',
                    mensagem: '".$tarefa->getMensagem()."',

                }
             
                tarefasGeral.id".$tarefa->getId()." = array;
            </script>
            ";
        };

        
     
      
    ?>

    <header class="head">
        <div class="menu-button button-head" onclick="changeAside()">
            <img src="../../../public/img/icons/list.svg" alt="menu">
        </div>

        <div class="title">
            <h1>CENTRAL DE CONTROLE</h1>
        </div>
    </header>


    <!-- NAV BAR -->
    <!-- NO ASIDE VERIFICO O NIVEL DO USUARIO E DAI EXIBO AS TELAS QUE ELE PODE ACESSAR -->
    <aside>

        <div class="container-blue">

        </div>

        <ul class="list-menu">
            <li onclick="changeAside()">
                <div class="menu-button ">
                    <img src="../../../public/img/icons/list.svg" alt="">
                </div>
                <h3>Menu</h3>
            </li>

            <a href="../geral/conta.php">
                <li>
                    <div class="menu-button">
                        <img src="../../../public/img/icons/person.svg" alt="">
                    </div>

                    <h3>Conta</h3>
                </li>
            </a>

            <a href="participantes.php">
                <li>
                    <div class="menu-button">
                        <img style="height:30px;"  src="../../../public/img/icons/people.svg" alt="">
                    </div>

                    <h3>Participantes</h3>
                </li>
            </a>    
            
            <a href="setor.php">
                <li>
                    <div class="menu-button">
                        <img style="height:30px;"  src="../../../public/img/icons/setor.svg" alt="">
                    </div>

                    <h3>Setores</h3>
                </li>
            </a>  

            <?php if($userInfo->getMainAcc() == 0): ?>
                <a href="../worker/control_colabora.php">
                <li>
                    <div class="menu-button">
                        <img style="height:30px;"  src="../../../public/img/icons/tarefas.svg" alt="">
                    </div>

                    <h3>Minhas Tarefas</h3>
                </li>
            </a> 
            <?php endif; ?> 

            <a href="../../services/logoutAction.php">
                <li>
                    <div class="menu-button">
                        <img src="../../../public/img/icons/logout.svg" alt="">
                    </div>

                    <h3>Logout</h3>
                </li>
            </a>
        </ul>
    </aside>
    <!-- NAV BAR -->

    <main>
        <section class="sector">
            <?php foreach($setores as $setor):
               
               $tarefasSetor = $tDao->findBySetor($setor->getTokenSetor()) ? $tDao->findBySetor($setor->getTokenSetor()) : [];// RECEBENDO TAREFAS DA EMPRESA

               usort($tarefasSetor, 'ordenarStatus');   
                
            ?>
                <div class="sec">
                    <div class="sec-head">
                        <h2><?= $setor->getName() ?></h2>
                    </div>

                    <div class="content">

                        <?php foreach($tarefasSetor as $tarefa):?>
                            <div id="<?=$tarefa->getId()?>" class="task <?=alterarCorTarefa($tarefa->getStatus());?>" onclick="handleModalTask(this.id)">
                                <span>
                                    <?=$tarefa->getTituloTarefa();?>
                                </span>

                                <div class="container-img <?=alterarCorP($tarefa->getStatus()) ?>">
                                    <img src="<?=alterarImgTarefa($tarefa->getStatus()) ?>" alt="">
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                    </div>

                    <div class="add-act" onclick="handleModalNewTask(this.id)" id="<?=$setor->getTokenSetor()?>">
                        <img src="../../../public/img//icons/plus.svg" alt="">
                    </div>

                </div>
            <?php endforeach ?>
                        
        
            <?php if(!$setores): ?>
                <div class="container-inst">
                    <div class="title">
                        <h2>Instruções</h2>
                    </div>

                    <div class="cont">
                        <p>Ao entrar pela primeira vez, inicie criando os setores da sua empresa. Em seguida, adicione os participantes da empresa, ou seja, colaboradores e administradores.</p>


                        <p>Vá para a seção "Setores". Crie setores relevantes para a sua empresa (por exemplo: Vendas, Marketing, Desenvolvimento).</p>

                        <p>Acesse a seção "Participantes". Adicione colaboradores e administradores à sua equipe.</p>

                        <p>Logo após, volte para a Central de controle. Crie uma nova tarefa, especificando título, descrição e delegue a tarefa ao membro específico. Os moderadores também podem criar e delegar tarefas.</p>

                       
                    </div>
                </div>
            <?php endif; ?>
          

        </section>


    </main>

    <div class="dark">
        <div class="modal-new-task">
            <div class="head-task">
                <h3>Nova tarefa...</h3>
                <div onclick="handleModalNewTask()" class="back">                    
                    <img  src="../../../public/img/svgs/arrow_back.svg" alt="">
                </div>
            </div>

            <form action="../../services/newTaskAction.php" method="post">
                <input type="hidden" value='<?=$userInfo->getId()?>' name='idAdm'>                
                <input type="hidden" value='<?=$userInfo->getTokenEmpresa()?>' name='tokenEmpresa'>
                <input type="hidden" value='' name='tokenSetor' id="tokenSetor">

                <label>
                    <h4>Responsável</h4>
                    <select name="name" class="input-area input-model">
                       <?php foreach($usersColabora as $usuario): ?>
                            <option value='<?= $usuario->getId() ?>'><?=$usuario->getName()?></option>
                        <?php endforeach;?>
                    </select>
                </label>

                <label>
                    <h4>Titulo da tarefa</h4>
                    <input type="text" name="task_title" class="input-area input-model" maxlength="20">
                </label>

                <label>
                    <h4>Prazo da tarefa</h4>
                    <div class="input-container">
                        <input type="date" name="begin_date" class="input-area input-model">
                        <input type="date" name="last_date" class="input-area input-model">
                    </div>
                </label>

                <label>
                    <h4>Descrição da tarefa</h4>
                    <input type="text" name="task_description" class="input-area input-model">
                </label>

                <input type="submit" value="Adicionar" class="btn-modal">
            </form>
        </div>

        <div class="modal-task" >
            <div id="container-title" class="head-task ">
                <h3 id="task-title"></h3>
                <div onclick="handleModalTask(false)" class="back">                    
                    <img  src="../../../public/img/svgs/arrow_back.svg" alt="">
                </div>
            </div>

            <div class="modal-task-body">
                <div class="colab">
                    
                    <div class="container-title-del">
                        <h4 class="task-title">Responsável</h4>
                        <a onclick="delTask()" style="cursor: pointer;"><img src="../../../public/img/icons/delete.svg" alt=""></a>
                    </div>
                    <div id="task-name" class="container-info">
                        
                    </div>
                </div>

                <div class="date">
                    <h4 class="task-title">Prazo da tarefa</h4>
                    <div class="container-date">
                        <div id="task-dataInicial" class="container-info">
                          
                        </div>

                        <div id="task-dataFinal" class="container-info">
                           
                        </div>
                    </div>
                </div>

                <div class="status">
                    <div>
                        <h4 class="task-title" >Status</h4>
                        <div  id="task-status" class="container-info" style="color: #fff;"></div>
                    </div>

                </div>

                <div class="desc">
                    <h4 class="task-title">Descrição da tarefa</h4>
                    <div id="task-descricao" class="container-info"></div>
                </div>

                <div class="obs">
                    <h4 class="task-title">Observações</h4>
                    <div id="task-mensagem" class="container-info" style="height: 80px; align-items: start; overflow-y:scroll;"></div>
                </div>
            </div>
        </div>

        <div id="del-task" class="del-task">
            <div class="header">
                <h2>Tem certeza que deseja excluir?</h2>
                <img onclick="delTask()" class="close-modal" src="../../../public/img/svgs/arrow_back.svg" alt="">
            </div>

            <div class="modal-container">
                <form action="../../services/delTask.php" method="post">
                   <input type="hidden" name="idTask" id="idTask" value="0">     

                    <input type="password" name="senha" class="info" id="passDelTask" placeholder="Senha do usuário">
                    <?php 
                        //VERIFICANDO SE EXISTE SESSÃO DE AVISO ATIVA E IMPRIMINDO AVISO NA TELA CASO EXISTA
                        if(!empty($_SESSION['aviso']) && $_SESSION['aviso']){
                        echo "<span class='aviso'>".$_SESSION['aviso']."</span>";
                        $_SESSION['aviso'] = '';
                        }
                    ?>
                    <div style="display: flex;">
                        <input type="submit" class="button-enviar" value="Sim">
                        <div style="margin-left: 8px; display: flex; justify-content: center;" class="button-enviar" onclick="delTask()">Não</div>
                    </div>
                
                </form>
            </div>
        </div>

      
    </div>

    
    <script src="../../../public/js/adm/control.js"></script>
    <script src="../../../public/js/general/main.js"></script>
    

</body>

</html>