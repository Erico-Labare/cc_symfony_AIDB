<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Classe principale du noyau de l'application Symfony.
 *
 * Cette classe étend la classe de base Kernel de Symfony et utilise le trait MicroKernelTrait
 * pour une configuration simplifiée. Elle est le point d'entrée de l'application
 * et gère le chargement des bundles, la configuration et le cycle de vie de l'application.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
