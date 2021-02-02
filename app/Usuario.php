<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject {

    use Notifiable, EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';
    const ID_PERFIL = null;

    protected $primaryKey = 'id';
    protected $table = "usuarios";
    protected $hidden = ['password'];

    public $timestamps = true;
    public $aMembership = [];

    protected $fillable = [
        'nome',
        'email',
        'password',
        'fk_perfil',
        'fk_faculdade_id',
        'foto',
        'status',
        'aluno_kroton',
        'remember_token',
        'last_login',
        'id_google',
        'senha_texto',
    ];

    public $rules = [
        'nome' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'fk_perfil' => 'required',
        'fk_faculdade_id' => 'required'
    ];

    public $rulesUpdate = [
        'nome' => 'required',
        'fk_perfil' => 'required',
        'password' => 'sometimes|confirmed'
    ];

    public $messages = [
        'nome.required' => 'Nome é obrigatório',
        'fk_perfil.required' => 'Perfil é obrigatório',
        'fk_faculdade_id.required' => 'O ID da Faculdade é obrigatório',
        'email.required' => 'E-mail é obrigatório',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'email.email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'validation.unique' => 'E-mail deve ser único!',
        'validation.min.string' => 'Senha deve 8 caracteres ou mais.',
        //'email.unique' => 'A chave composta por E-mail X Faculdade deve ser única!',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
    ];

    public function _validate($data, $model = null){

        $oUser = $this;

        $rules = [
            'nome' => 'required',
            'fk_perfil' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
            'email' => [
                'required',
                'email',
                Rule::unique('usuarios', 'email')->where(function ($query) use ($oUser, $data) {
                    if (!empty($oUser->id)) {
                        $query->where('id', '!=', $oUser->id);
                    }
                    $query->where('email', '=', $data['email']);
                    $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                    $query->where('fk_perfil', '=', $data['fk_perfil']);
                    $query->where('status', '=','1');
                }),
            ],
        ];

        if(!is_null($model) && ($model instanceof Usuario || $model instanceof Aluno)){
            $rules['fk_faculdade_id'] = 'required';
        }

        return Validator::make($data, $rules, $this->messages);
    }

    static function validate ($data){

        return self::_validate($data);

    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Retorna a classe CursoTurmaInscricao
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inscricoes()
    {
        return $this->hasMany('App\CursoTurmaInscricao', 'fk_usuario');
    }

    /**
     * Retorna a classe CursoTurmaAgendaPresenca
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presencas()
    {
        return $this->hasMany('App\CursoTurmaAgendaPresenca', 'fk_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assinaturas()
    {
        return $this->hasMany('App\Assinatura', 'usuario_assinatura', 'fk_usuario', 'fk_assinatura');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function aluno()
    {
        return $this->hasOne('App\Aluno', 'fk_usuario_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function professor()
    {
        return $this->hasOne('App\Professor', 'fk_usuario_id');
    }

    public function faculdade() {
        return $this->belongsTo(Faculdade::class, 'fk_faculdade_id');
    }

    public function getId(){
        return $this->id;
    }

    public function getName()
    {
        return $this->nome;
    }

    public function getHasMentoriaAttribute()
    {
        return Usuario::select('usuarios.id', 'usuarios.nome', 'usuarios_assinaturas.status')
                        ->join('usuarios_assinaturas', 'usuarios_assinaturas.fk_usuario', 'usuarios.id')
                        ->join('assinatura', 'assinatura.id', 'usuarios_assinaturas.fk_assinatura')
                        ->where('usuarios.id', $this->id)
                        ->where('usuarios_assinaturas.status', 1)
                        ->where('assinatura.fk_tipo_assinatura', 4)
                        ->exists();
    }

    public function membership() {
        $aMembership = DB::select("select
                                    pedidos.id, pedidos.status, pedidos_status.titulo, pedidos.pid, pedidos.valor_bruto, pedidos.valor_desconto,
                                    assinatura.fk_tipo_assinatura as tipo_assinatura_id, assinatura.titulo as assinatura_titulo,
                                    tipo_assinatura.titulo as tipo_assinatura, assinatura.id as assinatura_id,
                                    assinatura.tipo_liberacao
                                from pedidos
                                    join pedidos_status ON pedidos_status.id = pedidos.status
                                    join pedidos_item ON pedidos_item.fk_pedido = pedidos.id
                                    join assinatura ON assinatura.id = pedidos_item.fk_assinatura
                                    join tipo_assinatura on tipo_assinatura.id = assinatura.fk_tipo_assinatura
                                    join usuarios_assinaturas on usuarios_assinaturas.fk_assinatura = assinatura.id
                                         AND usuarios_assinaturas.fk_pedido = pedidos.id
                                where pedidos.fk_usuario = ". $this->id . " AND usuarios_assinaturas.status = 1");

        return $this->aMembership = collect($aMembership);
    }

    public function getUsuario($usuarioId) {
        return $this->withTrashed()->find($usuarioId);
    }

    public function getAuthPassword() {
        return $this->password;
    }

    public function scopeActive($query) {
        return $query->where('status', '!=', 0);
    }
}
