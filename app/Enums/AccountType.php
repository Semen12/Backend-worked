<?php

namespace App\Enums;

enum AccountType: string
{
    case INTERNET_RESOURCE = 'интернет-ресурс';
    case PROGRAM = 'программа';
    case APP = 'приложение';
    case EMAIL = 'электронная почта';
    case BANK = 'банк';
    case EDUCATION = 'образование';
    case WORK = 'работа';
    case CLOUD_STORAGE = 'облачное хранилище';
    case MESSENGERS = 'мессенджеры';
    case VPN = 'VPN';
    case PAYMENT_SYSTEMS = 'платежные системы';
    case OTHER = 'другое';
}
