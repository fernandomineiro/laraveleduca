
        @section('sidebar')
            <aside class="left-side sidebar-offcanvas">
                <section class="sidebar">

                    <div class="user-panel">
                        <div class="pull-left info">
                            <p><?php // echo $_SESSION['Auth']['Usuario']['login']; ?></p>
                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>

                            <a href="{{ route('admin.logout') }}"><i class="fa fa-circle text-danger"></i> Logout</a>
                        </div>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/perfil/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Perfil</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.perfil') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.perfil.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/nacionalidade/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Nacionalidade</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.nacionalidade') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.nacionalidade.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/banco/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Banco</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.banco') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.banco.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/professor/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Professor</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.professor') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.professor.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/curso/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Cursos</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.curso') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.curso.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/quiz/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Questionário</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.quiz') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.quiz.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/trilha/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Trilha de Conhecimento</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.trilha') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.trilha.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/cursos_modulo/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Aulas dos Cursos</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.cursos_modulo') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.cursos_modulo.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/faculdade/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Projetos</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.faculdade') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.faculdade.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/configuracao/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Faculdade Configuração</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.configuracao') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.configuracao.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/cursos_tipo/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Tipos de Cursos</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.cursos_tipo') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.cursos_tipo.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/cursos_categoria/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Categoria de Cursos</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.cursos_categoria') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.cursos_categoria.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/propostas_status/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Propostas Status</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.propostas_status') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.propostas_status.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/usuario/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Usuários</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.usuario') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.usuario.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/proposta/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Propostas</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.proposta') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.proposta.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <!-- <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/proposta_modulo/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Proposta Modulos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.proposta_modulo') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.proposta_modulo.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                        -->

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/cupom/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Cupom </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.cupom') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.cupom.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/tipo_pagamento/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Tipos de Pagamento </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.tipo_pagamento') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.tipo_pagamento.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/eventos/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Proposta Agenda </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.eventos') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.eventos.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/eventos/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Eventos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.eventos') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.eventos.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/agendaeventos/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Agenda Eventos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.agendaeventos') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.agendaeventos.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/agendamentogravacao/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Agenda Gravação </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.agendamentogravacao') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.agendamentogravacao.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/parceiro/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Parceiros </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.parceiros') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.parceiros.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/tipoparceiro/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Tipos de parceiros</span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.tipoparceiro') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.tipoparceiro.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>


                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/projeto/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Projetos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.projeto') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.projeto.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/projetotipo/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Projeto Tipos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.projetotipo') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.projetotipo.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/produtora/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Produtora </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.produtora') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.produtora.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/pedido/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Lista de Pedidos </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.pedido') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/pedido_status/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Pedido Status </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.pedido_status') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($_SERVER['REDIRECT_URL']) && preg_match('/\/admin\/proposta_modulo/', $_SERVER['REDIRECT_URL'], $matches) ? 'active' : ''; ?>">
                            <a href="#"><i class="fa fa-angle-double-right"></i>  <span>Certificados </span><i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('admin.certificados') }}"><i class="fa fa-angle-double-right"></i> Listagem</a></li>
                                <li><a href="{{ route('admin.certificados.incluir') }}"><i class="fa fa-angle-double-right"></i> Incluir</a></li>
                            </ul>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
