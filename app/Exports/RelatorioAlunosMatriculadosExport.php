<?php


namespace App\Exports;


use App\Helper\RelatorioAlunosMatriculadosHelper;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Helper\CertificadoHelper;

class RelatorioAlunosMatriculadosExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(array $parametros)
    {
        $this->parametros = $parametros;
    }

    public function headings(): array{

        if($this->parametros['ies'] == 6) {
            return [
                'Compra',
                'ID Aluno',
                'Aluno',
                'Email',
                'ID Curso',
                'Curso',
                'IES',
                'Emite Certificado',
                'Status Conclusão',
                'Conclusão %',
                'Último acesso'
            ];
        } else {
            return [
                'ID Pedido',
                'Compra',
                'ID Aluno',
                'Aluno',
                'Email',
                'ID Curso',
                'Curso',
                'Tipo Curso',
                'IES',
                'Emite Certificado',
                'Status Pagamento',
                'Status Conclusão',
                'Conclusão %',
                'Último acesso'
            ];
        }
    }

    public function collection()
    {
        $alunos_matriculados = new RelatorioAlunosMatriculadosHelper();

        if($this->parametros['ies'] == 6 ) {
            $total_query = $alunos_matriculados->lista_alunos_matriculadosITV($this->parametros)->count();;

            if ($total_query > 3000) {
                abort(403, 'Não é possível exportar esse número de registros ('. $total_query . '), o máximo permitdo é 3000 registros, altere o filtro e tente novamente');
            }
            
            $alunos = $alunos_matriculados->lista_alunos_matriculadosITV($this->parametros)->get();
        } else {

            $total_query = $alunos_matriculados->lista_alunos_matriculados($this->parametros)->count();;

            if ($total_query > 3000) {
                abort(403, 'Não é possível exportar esse número de registros ('. $total_query . '), o máximo permitdo é 3000 registros, altere o filtro e tente novamente');
            }
            
            $alunos = $alunos_matriculados->lista_alunos_matriculados($this->parametros)->get();

            $percentual_conclusao = new CertificadoHelper();
    
            foreach($alunos as $k=>$aluno ) {
                $percentual = round($percentual_conclusao->percentualOnline($aluno->fk_usuario, $aluno->curso_id), 2);
                $alunos[$k]->percentual_conclusao = $percentual;
            }
        }

        //$total_query = $query->count();

        //if ($total_query > 7000) {
        //    abort(403, 'Não é possível exportar esse número de registros ('. $total_query . '), altere o filtro e tente novamente');
        //}

        return $alunos;
    }

    public function map($aluno): array
    {
        if($this->parametros['ies'] == 6 ) {
            return [
                Carbon::parse($aluno->data_criacao)->format('d/m/Y'),
                $aluno->id,
                $aluno->nome . ' ' . $aluno->sobre_nome ,
                $aluno->email,
                isset($aluno->curso_id) ? $aluno->curso_id : '---',
                isset($aluno->curso_titulo) ? $aluno->curso_titulo : '---',
                'ITV',
                $aluno->fk_certificado != null ? 'SIM' : 'NÃO',
                $aluno->curso_concluido != null ? 'Concluído' : 'Em andamento',
                isset($aluno->percentual_conclusao) ? round($aluno->percentual_conclusao, 2) : 0,
                $aluno->ultimo_acesso,
            ];
        } else {
            return [
                $aluno->id,
                Carbon::parse($aluno->criacao)->format('d/m/Y'),
                $aluno->aluno_id,
                $aluno->aluno_nome . ' ' . $aluno->aluno_sobre_nome ,
                $aluno->aluno_email,
                $aluno->curso_id,
                $aluno->curso_titulo,
                $aluno->curso_tipo,
                $aluno->ies_fantasia,
                $aluno->fk_certificado ? 'SIM' : 'NÃO',
                $aluno->pedido_status_titulo,
                $aluno->curso_concluido != null ? 'Concluído' : 'Em andamento',
                $aluno->percentual_conclusao,
                $aluno->ultimo_acesso
            ];
        }
    }
}
