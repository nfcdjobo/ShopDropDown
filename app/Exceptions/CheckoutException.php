<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception métier levée quand une commande ne peut pas être finalisée :
 * panier vide, produit en rupture, etc. Le CheckoutController l'attrape
 * pour afficher un message utilisateur propre (redirect back with error).
 */
class CheckoutException extends RuntimeException
{
}
