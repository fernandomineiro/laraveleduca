<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class MailConfigServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $mail = DB::table('contas_email')
            ->where('fk_faculdade_id', 1)
            ->where('status', '!=', 0)
            ->first();

        $config = array(
            'driver' => 'smtp',
            'host' => isset($mail->smtp_server) && !empty($mail->smtp_server) ? $mail->smtp_server : 'mail.educaz.com.br',
            'port' => isset($mail->smtp_port) && !empty($mail->smtp_port) ? $mail->smtp_port : 465,
            'encryption' => isset($mail->smtp_ssl_tls) && !empty($mail->smtp_ssl_tls) ? $mail->smtp_ssl_tls : 'ssl',
            'username' => isset($mail->conta) && !empty($mail->conta) ? $mail->conta : 'donotreply@educaz.com.br',
            'password' => isset($mail->senha) && !empty($mail->senha) ? $mail->senha : 'don@j33dP2Ss$',
        );

        Config::set('mail', $config);
    }
}
