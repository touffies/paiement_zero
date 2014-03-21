PLUGIN DE PAIEMENT POUR THELIA 1.5
--------------------------------------

Ce plugin permet de valider une commande dans le cas ou le montant total du panier est de 0.

> **Auteur**
>
>   Christophe LAFFONT - Openstudio / [www.openstudio.fr][3]


INSTALLATION
---------

Pour installer ce plugin, il vous faut :

 1. Installer le plugin `paiement_zero` dans le dossier `/client/plugins/` de votre site.
 2. Activer ce plugin dans le menu `Configuration -> Activation des plugins`.


BOUCLE
---------

Ce plugin ne propose pas de nouvelles boucles, pour l'utiliser il faut juste utilisée
une boucle TEST pour vérifier.


Exemple d'utilisation :

```
<h2>::choixmodepaiement:: </h2>
<div class="choixDuReglement">
    <ul>
    <THELIA_paiement_zero type="PAIEMENT_ZERO">
        <THELIA_PAIEMENT type="PAIEMENT" exclusion="#EXCLUSION" id="#ID">
            <li><a href="#URLPAYER"><span class="modeDeReglement">#TITRE</span><span class="choisir"></span></a></li>
        </THELIA_PAIEMENT>
    </THELIA_paiement_zero>
    </ul>
</div>
```





----------

CHANGELOG
---------

- **1.0.1** (21/03/2014) - Issue #1 Problème lors de la vérification du total (TotalApresRemise < au frait de port)
- **1.0.0** (13/02/2014) - Première version du plugin


[1]: http://www.openstudio.fr
