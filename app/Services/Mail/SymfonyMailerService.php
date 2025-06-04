<?php

namespace App\Services\Mail;

use App\Enums\UserRole;
use Illuminate\Support\Facades\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class SymfonyMailerService
{
    protected $mailer;
    public function __construct()
    {
        $transport = Transport::fromDsn(config('app.mailer_dsn'));
        $this->mailer = new Mailer($transport);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailVerification($user): void
    {
        $content = View::make('emails.email_template', [
            'username' => $user->first_name . ' ' . $user->last_name,
            'verification_code' => $user->verification_pin,
            'app_name' => ucwords(config('app.name')),
        ])->render();

        $email = (new Email())
            ->from("doctourdoudou@blank.ovh")
            ->to($user->email)
            ->subject('Verification de votre compte sur ' . ucwords(config('app.name')))
            ->html($content);
        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailResetPassword($user): void
    {

        if($user->role===UserRole::ADMIN->value){
            $content = View::make('emails.password_reset_admin', [
                'username' => $user->first_name . ' ' . $user->last_name,
                'reset_password_url'=> config('app.backoffice_url') . '/auth/reset-password?email='.$user->email.'&verification_code='. $user->verification_pin,
                'app_name' => ucwords(config('app.name')),
            ])->render();
        }else{
            $content = View::make('emails.Password_reset', [
                'username' => $user->first_name . ' ' . $user->last_name,
                'verification_code' => $user->verification_pin,
                'app_name' => ucwords(config('app.name')),
            ])->render();
        }

        $email = (new Email())
            ->from("doctourdoudou@blank.ovh")
            ->to($user->email)
            ->subject('Réinitialisation de votre mot de passe sur ' . ucwords(config('app.name')))
            ->html($content);
        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendWelcomeEmail($user):void
    {
        $content = View::make('emails.email_activated', [
            'username' => $user->first_name . ' ' . $user->last_name,
            'app_name' => ucwords(config('app.name')),
        ])->render();

        $email = (new Email())
            ->from("doctourdoudou@doctourdoudou@blank.ovh")
            ->to($user->email)
            ->subject('Bienvenue sur ' . ucwords(config('app.name')))
            ->html($content);
        $this->mailer->send($email);
    }
}
