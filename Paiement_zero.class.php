<?php
/*************************************************************************************/
/*                                                                                   */
/*      Module de Paiement pour Thelia	                                             */
/*                                                                                   */
/*      Copyright (c) Openstudio 		                                     		 */
/*      Développement : Christophe LAFFONT		                                     */
/*		email : claffont@openstudio.fr	        	                             	 */
/*      web : http://www.openstudio.fr					   							 */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 2 of the License, or            */
/*      (at your option) any later version.                                          */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program; if not, write to the Free Software                  */
/*      Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    */
/*                                                                                   */
/*************************************************************************************/

// On s'assure que la constante __DIR__ est définie pour les versions de PHP antérieur à 5.3
(@__DIR__ == '__DIR__') && define('__DIR__', realpath(dirname(__FILE__)));

// Classes de Thelia
include_once __DIR__ . "/../../../classes/PluginsPaiements.class.php";
include_once __DIR__ . "/../../../classes/Modules.class.php";
include_once __DIR__ . "/../../../classes/Tlog.class.php";


/**
 * Class Paiement_zero
 *
 * Cette classe permet de valider une commande d'un montant total de zero.
 */
class Paiement_zero extends PluginsPaiements {

    const MODULE = "paiement_zero";

    /**
     * Constructeur
     *
     * @param int/null $id Possibilité de passer un identifiant pour charger un objet Prepayment_livraison
     */
    function __construct()
    {
        parent::__construct(self::MODULE);
    }

    /**
     * Initialisation du plugin
     *
     * @return none
     */
	function init()
    {

        $this->ajout_desc("Finaliser ma commande", "paiement_zero", "Plugin permettant de valider une commande dans le cas ou le montant total du panier est de 0.", 1);

	}

    /**
     * Boucle permettant de vérifier le montant total du panier et
     * de filtrer les paiements.
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    function boucle($texte, $args)
    {

        // Récupération des arguments
        $id = lireTag($args, "id", "int");
        $exclusion = lireTag($args, "exclusion", "string_list");

        // Tableau temporaire
        $arrExclusion = array();
        if($exclusion != "")
            $arrExclusion = explode(",", $exclusion);

        if (session_id() === "") { @session_start(); }

        // On vérifie le total du panier
        $total = $_SESSION['navig']->panier->total();

        // Calcul des frais de port
        $port = port();
        if($port < 0)
            $port = 0;

        // Calcul des réductions
        $remise = $remise_client = $remise_promo = 0;
        if($_SESSION['navig']->client->pourcentage > 0) $remise_client = $total * $_SESSION['navig']->client->pourcentage / 100;
        $remise_promo += calc_remise($total);
        $remise = $remise_promo + $remise_client;

        // Total + Frais de Port  - Réduction
        $total = $total + $port - $remise;

        // Sécurité : on vérifie que le total est > au frais de port
        if($total < $port)
            $total = $port;

        // Si le total est de 0 et qu'on a au moins 1 article dans le panier
        if($total <= 0 && $_SESSION['navig']->panier->nbart > 0)
        {
            // On recherche l'id du module courant
            $mod = new Modules();
            if($mod->charger(self::MODULE))
                $id = $mod->id;

            // Substitutions
            $texte = str_replace("#ID", $id, $texte);
            $texte = str_replace("#EXCLUSION", "", $texte);

        } else {

            // On exlcut le plugin paiement_zero
            $arrExclusion[] = self::MODULE;

            // Substitutions
            $texte = str_replace("#ID", "", $texte);
            $texte = str_replace("#EXCLUSION", implode(",", $arrExclusion), $texte);
        }

        return $texte;
    }

    /**
     * Méthode appelée lors du paiement d'une commande
     *
     * @param $commande Objet de type commande
     *
     * @return none
     */
    function paiement($commande)
    {
        try {
            // Si la commande est dans le statut NON PAYE
            if($commande->statut !== 1)
            {
                throw new Exception('La commande a déjà été validée . COMMANDE : ' . $commande);
            }

            $commande->statut = 2;
            $commande->genfact();
            $commande->maj();

            ActionsModules::instance()->appel_module("confirmation", $commande);

        } catch (Exception $ex) {
            Tlog::error("Paiement_zero REQUETE: ", $_REQUEST , " - EXCEPTION : ", $ex->getMessage());
        }

        $fond_succes = defined('PAYMENT_ZERO_URL_SUCCES') ? PAYMENT_ZERO_URL_SUCCES : "merci";
        header("Location: " . urlfond($fond_succes));
        exit;
    }
}
?>