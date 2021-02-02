	
	@section('modals')
		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  		<div class="modal-dialog" role="document">
	    		<div class="modal-content">
	      			<div class="modal-header">
	        			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      			</div>
	      			<div class="modal-body">
	      				<div id="formulario_login">
		        			{{ Form::open(['route' => 'home.login.submit', 'id' => 'form_login']) }}
		          				<div class="group">
		            				{{ Form::input('text', 'email', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
		            				<span class="highlight"></span> <span class="bar"></span>
		            				<label>Login</label>
		          				</div>
		          				<div class="group">
		            				{{ Form::input('password', 'password', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
		            				<span class="highlight"></span> <span class="bar"></span>
		            				<label>Senha</label>
		          				</div>
		          				<a href="javascript:;" onclick="jQuery('#form_login').submit();" class="salvar-btn">entrar</a>
		          				<a href="javascript:;" onclick="jQuery('#formulario_login').hide(); jQuery('#formulario_senha').fadeIn();" class="salvar">Esqueci a senha</a>
		        			{{ Form::close() }}	      				
	      				</div>
	      				<div id="formulario_senha" style="display: none;">
		        			{{ Form::open(['route' => 'home.enviar_senha.submit', 'id' => 'form_senha']) }}
		          				<div class="group">
		            				{{ Form::input('text', 'email', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
		            				<span class="highlight"></span> <span class="bar"></span>
		            				<label>Login</label>
		          				</div>
		          				<a href="javascript:;" onclick="jQuery('#form_senha').submit();" class="salvar-btn">enviar senha</a> 
		        			{{ Form::close() }}
	      				</div>
	      			</div>
	      			<div class="modal-footer"> <a href="{{ route('home.cadastrar') }}" class="ainda-btn">AINDA NAO TENHO CADASTRO</a> </div>
	    		</div>
	  		</div>
		</div>
	
		<!-- Modal3 -->
		<div class="modal fade" id="enviar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  		<div class="modal-dialog" role="document">
	    		<div class="modal-content">
	      			<div class="modal-header">
	        			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      			</div>
	      			<div class="modal-body">
	        			{{ Form::open(['route' => 'home.salvar_depoimento.submit', 'id' => 'form_enviar_depoimento']) }}
	        			
	          				<div class="group">
	            				{{ Form::input('text', 'nome', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
	            				<span class="highlight"></span> <span class="bar"></span>
	            				<label>Nome</label>
	          				</div>
	          				<div class="group">
	            				{{ Form::input('text', 'email', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
	            				<span class="highlight"></span> <span class="bar"></span>
	            				<label>E-mail</label>
	          				</div>
	          				<div class="group">
	            				{{ Form::input('text', 'empresa', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
	            				<span class="highlight"></span> <span class="bar"></span>
	            				<label>Empresa</label>
	          				</div>
	          				<div class="group">
	            				{{ Form::input('text', 'cargo', null, ['autofocus', 'required' => true, 'maxlength' => '100']) }}
	            				<span class="highlight"></span> <span class="bar"></span>
	            				<label>Cargo</label>
	          				</div>
	          				<div class="group">
	            				{{ Form::textarea('texto', null, ['autofocus', 'required' => true, 'style' => 'width: 100%']) }}
	            				<span class="highlight"></span> <span class="bar"></span>
	            				<label>Sua história</label>
	          				</div>
	          				
	          				<a href="javascript:;" onclick="enviar_depoimento();" class="salvar-btn">enviar</a> 
	        			{{ Form::close() }}
	      			</div>      
	    		</div>
	  		</div>
		</div>
		
		@if(Auth::guard('web')->check())
			<div class="modal fade" id="trocarImagem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  		<div class="modal-dialog" role="document">
		    		<div class="modal-content">
		      			<div class="modal-header">
		        			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		      			</div>
		      			<div class="modal-body">
		        			{{ Form::model( null, ['method' => 'PATCH', 'route' => ['home.atualizar_imagem.submit'], 'files' => true] ) }}
		          				<div class="group">
									{{ Form::file('img_avatar', ['style' => 'font-size: 15px;']) }}
		          				</div>
		          				{{ Form::submit('atualizar', ['class' => 'btn btn-primary']) }}
		        			{{ Form::close() }}
		      			</div>
		    		</div>
		  		</div>
			</div>			
		@endif