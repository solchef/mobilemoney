<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Execute;
use Illuminate\Console\Command;
use Jawiwy\MobileMoney\src\Mpesa\Library\RegisterUrl;
class RegisterUrlCommand extends Command
{
    protected $signature = 'mpesa:register_url';
    protected $description = 'Register mpesa validation and confirmation URL';
    private $registerUrl;

    public function __construct(RegisterUrl $registerUrl)
    {
        parent::__construct();
        $this->registerUrl = $registerUrl;
    }
    public function handle()
    {
        $register = $this->registerUrl
            ->register($this->askShortcode())
            ->onConfirmation($this->askConfirmationUrl())
            ->onValidation($this->askValidationUrl())
            ->submit();
        dd($register);
    }

    private function askShortcode()
    {
        return $this->ask('What is your shortcode', \config('mobilemoney.c2b.short_code'));
    }

    private function askConfirmationUrl()
    {
        return $this->ask('Confirmation Url', \config('mobilemoney.c2b.confirmation_url'));
    }

    private function askValidationUrl()
    {
        return $this->ask('Validation Url', \config('mobilemoney.c2b.validation_url'));
    }
}