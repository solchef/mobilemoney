<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;

class RegisterUrl extends CommonClass
{

    protected $shortCode;
    protected $validationURL;
    protected $confirmationURL;
    protected $onTimeout = 'Completed';

    public function register($shortCode)
    {
        $this->shortCode = $shortCode;
        return $this;
    }
    public function onValidation($validationURL)
    {
        $this->validationURL = $validationURL;
        return $this;
    }

    public function onConfirmation($confirmationURL)
    {
        $this->confirmationURL = $confirmationURL;
        return $this;
    }

    public function onTimeout($onTimeout = 'Completed')
    {
        if ($onTimeout !== 'Completed' && $onTimeout !== 'Cancelled') {
            throw new \Exception('Invalid timeout argument. Use Completed or Cancelled');
        }
        $this->onTimeout = $onTimeout;
        return $this;
    }
    public function submit($shortCode = null, $confirmationURL = null, $validationURL = null, $onTimeout = null)
    {
        if ($onTimeout && $onTimeout !== 'Completed' && $onTimeout = 'Cancelled') {
            throw new \Exception('Invalid timeout argument. Use Completed or Cancelled');
        }
        $body = [
            'ShortCode' => $shortCode ?: $this->shortCode,
            'ResponseType' => $onTimeout ?: $this->onTimeout,
            'ConfirmationURL' => $confirmationURL ?: $this->confirmationURL,
            'ValidationURL' => $validationURL ?: $this->validationURL
        ];
        return $this->sendRequest($body, 'register');
    }

}