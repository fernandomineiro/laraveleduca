@section('sidebar')
    <aside class="left-side sidebar-offcanvas">
        <section class="sidebar">
            <div class="user-panel">
                <div class="pull-left image">
                    <?php
                    $nome = "Usuário";
                    if (isset($userData->nome))
                        $nome = trim($userData->nome);
                        $pathAvatar = url('/') . '/files/default.png';
                    if (isset($userData->foto))
                        if ($userData->foto != '') {
                            $pathAvatar = Storage::disk('s3')->url('files/usuario/' . Auth()->guard('admin')->user()->foto);
                        }
                    ?>
                    @if(isset($userData))
                        <a href="{{ url('admin/usuario/'.$userData->id.'/editar') }}">
                            <img src="<?=$pathAvatar?>" class="img-circle" width="45" alt="{{ $nome }}">
                        </a>
                    @endif
                </div>
                <div class="pull-left info">
                    <p><?=$nome;?></p>
                    <a class="btn btn-danger" style="color: #FFF;" href="{{ route('admin.logout') }}" onclick="return confirm('Deseja realmente sair?')">
                    	Logout
                    </a>
                </div>
            </div>

            <ul class="sidebar-menu">
                <!-- FIXO -->
                <li><a href="{{ route('admin.dashboard') }}"> Dashboard</a></li>
                <!-- FIXO -->
                <?php
                $menuName = "";
                if (isset($userMenu))
                    foreach ($userMenu as $menu) {

                        if ($menu->menu != 'ACL' || (($menu->menu == 'ACL') && ($userData->fk_perfil == 20))) {
                            if ($menuName != $menu->menu) {
                                if ($menuName != "")
                                    echo '</ul></li>';

                                echo '<li class="treeview">';
                                echo '<a href="javascript:void(0)"><i class="fa fa-angle-double-right"></i>  <span>' . $menu->menu . '</span><i class="fa fa-angle-left pull-right"></i></a>';
                                echo '<ul class="treeview-menu">';

                                $menuName = $menu->menu;
                            }

                            if($menu->modulo != "Quiz"){
                                echo '<li><a href="' . route('admin.' . $menu->rota) . '"><i class="fa fa-angle-double-right"></i> ' . (($menu->modulo == "Quiz")? "Questionário" : $menu->modulo) . '</a></li>';
                            }
                        }
                    }
                echo '</ul></li>';
                ?>
                <li class="treeview">
                    <a href="javascript:void(0)"><i class="fa fa-angle-double-right"></i>  <span>Relatórios e Gráficos</span><i class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Financeiro</a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="{{ route('admin.graficos.financeiros') }}">Gráfico</a>
                                <a class="dropdown-item" href="{{ route('admin.relatorio.financeiro') }}">Relatórios</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </section>
    </aside>

