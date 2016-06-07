#!/usr/bin/perl

use Encode;
$verbose = shift;

while(<STDIN>) {
	chomp;
	@field = split/;/ ;
	next if ($field[0] !~ /[0-9]/);
	print "Ecriture générale;" if ($verbose);
	print "#MPCT\n";
	print "numéro de compte (VINSI) :" if ($verbose);
	print $field[0]."\n";
	print "intitulé (VINSI) : " if ($verbose);
	print encode_utf8(substr(decode_utf8($field[1]), 0, 35))."\n";	
	if ($field[2] eq "CLIENT") {
	    print "type (client/fournisseur) (VINSI) : " if ($verbose);
	    print "0\n";
	    print "numéro de compte (VINSI) : " if ($verbose);
	    print "41100000\n";
	}else{
	    print "type (client/fournisseur) (VINSI) : " if ($verbose);
	    print "1\n";
	    print "numéro de compte (VINSI) : " if ($verbose);
	    print "40100000\n";
	}
	print "qualité : " if ($verbose);
	print "\n";
	print "abrégé (VINSI)" if ($verbose);
	print encode_utf8(substr(decode_utf8($field[3]), 0, 17))."\n";
	print "contact : " if ($verbose);
	print "\n";
	print "adresse (VINSI) : " if ($verbose);
	print encode_utf8(substr(decode_utf8($field[4]), 0, 35))."\n";
	print "complément adresse (VINSI) : " if ($verbose);
	print encode_utf8(substr(decode_utf8($field[5]), 0, 35))."\n";
	print "code postal (VINSI) : " if ($verbose);
	print $field[6]."\n";
	print "ville (VINSI) : " if ($verbose);
	print encode_utf8(substr(decode_utf8($field[7]), 0, 35))."\n";
	print "région : " if ($verbose);
	print "\n";
	print "pays (VINSI) : " if ($verbose);
	print encode_utf8(substr(decode_utf8($field[8]), 0, 35))."\n";
	print "raccourci : " if ($verbose);
	print "\n";
	print "numéro devise : " if ($verbose);
	print "0\n";
	print "code NAF (APE) (VINSI) : " if ($verbose);
	print substr($field[9], 0, 6)."\n";
	print "n° identifiant (VINSI) : " if ($verbose);
	print $field[10]."\n";
	print "n° siret (VINSI) : " if ($verbose);
	$field[11] =~ s/[^0-9]//g;
	print substr($field[11], 0, 14)."\n";
	print "valeurs stats 1: " if ($verbose);
	print "\n";
	print "valeurs stats 2: " if ($verbose);
	print "\n";
	print "valeurs stats 3: " if ($verbose);
	print "\n";
	print "valeurs stats 4: " if ($verbose);
	print "\n";
	print "valeurs stats 5: " if ($verbose);
	print "\n";
	print "valeurs stats 6: " if ($verbose);
	print "\n";
	print "valeurs stats 7: " if ($verbose);
	print "\n";
	print "valeurs stats 8: " if ($verbose);
	print "\n";
	print "valeurs stats 9: " if ($verbose);
	print "\n";
	print "valeurs stats 10: " if ($verbose);
	print "\n";
	print "commentaire (EXPORT SAGE) : " if ($verbose);
	print $field[21]."\n";
	print "encours : " if ($verbose);
	print "0.00\n";
	print "plafond assurance crédit : " if ($verbose);
	print "0.00\n";
	print "numéro compte tiers payeur : " if ($verbose);
	print $field[0]."\n";
	print "code risque : " if ($verbose);
	print "1\n";
	print "catégorie tarifaire : " if ($verbose);
	print "1\n";
	print "montant taux 1 : " if ($verbose);
	print "0.0000\n";
	print "montant taux 2 : " if ($verbose);
	print "0.0000\n";
	print "montant taux 3 : " if ($verbose);
	print "0.0000\n";
	print "montant taux 4 : " if ($verbose);
	print "0.0000\n";
	print "catégorie comptable : " if ($verbose);
	print "1\n";
	print "périodicité : " if ($verbose);
	print "1\n";
	print "nombre de factures : " if ($verbose);
	print "1\n";
	print "BL par facture : " if ($verbose);
	print "0\n";
	print "Langue : " if ($verbose);
	print "0\n";
	print "code Edi1 : " if ($verbose);
	print "\n";
	print "code Edi2 : " if ($verbose);
	print "\n";
	print "code Edi3 : " if ($verbose);
	print "\n";
	print "Expédition : " if ($verbose);
	print "1\n";
	print "Condition : " if ($verbose);
	print "1\n";
	print "saut de lignes : " if ($verbose);
	print "1\n";
	print "option lettrage (oui/non) : " if ($verbose);
	print "1\n";
	print "validation des dates d´échéance (oui/non) : " if ($verbose);
	print "0\n";
	print "mise en sommeil (VINSI) : " if ($verbose);
	if ($field[12] eq "ACTIF") {
	    print "0\n";
	}else{
	    print "1\n";
	}
	print "contrôle de l'encours : " if ($verbose);
	print "0\n";
	print "date de création (VINSI) : " if ($verbose);
	$field[13] =~ s/\d{2}(\d{2})-(\d{2})-(\d{2})/${3}${2}${1}/;
	$field[13] = '010112' if (!$field[13]);
	print $field[13]."\n";
	print "hors rappel/relevé : " if ($verbose);
	print "0\n";
	print "numéro analytique : " if ($verbose);
	print "1\n";
	print "numéro section analytique : " if ($verbose);
	print "\n";
	print "téléphone (VINSI) : " if ($verbose);
	print substr($field[14], 0, 21)."\n";
	print "télécopie (VINSI) : " if ($verbose);
	print substr($field[15], 0, 21)."\n";
	print "email (VINSI) : " if ($verbose);
	print substr($field[16], 0 ,69)."\n";
	print "site (VINSI) : " if ($verbose);
	print substr($field[17], 0, 69)."\n";
	print "numéro EASY : " if ($verbose);
	print "\n";
	print "placé sous surveillance : " if ($verbose);
	print "0\n";
	print "date de création société : " if ($verbose);
	print "\n";
	print "forme juridique : " if ($verbose);
	print "\n";
	print "effectif : " if ($verbose);
	print "\n";
	print "chiffre d'affaire : " if ($verbose);
	print "0.0000\n";
	print "résultat net : " if ($verbose);
	print "0.0000\n";
	print "incidents de paiement : " if ($verbose);
	print "0\n";
	print "date du dernier incident : " if ($verbose);
	print "\n";
	print "privilèges : " if ($verbose);
	print "0\n";
	print "régularité des paiements : " if ($verbose);
	print "0\n";
	print "dotation de la solvabilité : " if ($verbose);
	print "\n";
	print "date dernière mise à jour : " if ($verbose);
	print "\n";
	print "objet dernière maj : " if ($verbose);
	print "\n";
	print "date arrêté de bilan : " if ($verbose);
	print "\n";
	print "nb de mois du bilan : " if ($verbose);
	print "0\n";
	print "numéro plan IFRS : " if ($verbose);
	print "0\n";
	print "numéro section IFRS : " if ($verbose);
	print "\n";
	print "priorité livraison : " if ($verbose);
	print "0\n";
	print "livraison partielle : " if ($verbose);
	print "0\n";
	print "intitulé modèle de règlement (EXPORT SAGE): " if ($verbose);
	if ($field[27]) {
	    print $field[27]."\n";
	}else{
	    print "\n";
	}
	print "non soumis à pénalités de retard (EXPORT SAGE) : " if ($verbose);
	print $field[22]."\n";
	print "code banque élément banque : " if ($verbose);
	print "\n";
	print "code guichet élément baque : " if ($verbose);
	print "\n";
	print "compte élément banque : " if ($verbose);
	print "\n";
	print "numéro devise élément banque : " if ($verbose);
	print "0\n";
	print "numéro tiers centrale d'achat : " if ($verbose);
	print "\n";
	print "collaborateur nom (VINSI : ".$field[18].") : " if ($verbose);
	print "\n";
	print "collaborateur prénom : " if ($verbose);
	print "\n";
	print "date fermeture début : " if ($verbose);
	print "\n";
	print "date fermeture fin : " if ($verbose);
	print "\n";
	print "format facture : " if ($verbose);
	print "0\n";
	print "type NIF : " if ($verbose);
	print "0\n";
	print "intitulé représentant légal : " if ($verbose);
	print "\n";
	print "NIF représentant légal : " if ($verbose);
	print "\n";
	print "compte général rattaché : " if ($verbose);
	if ($field[2] eq "CLIENT") {
	    print "41100000\n";
	}else{
	    print "40100000\n";
	}
	if ($field[23] || $field[24] || $field[25] || $field[26]) {
	    print "Balise Règlement : " if ($verbose);
	    print "#MRLT\n";
	    print "Numéro règlement : " if ($verbose);
	    print $field[23]."\n";
	    print "Condition : " if ($verbose);
	    print $field[24]."\n";
	    print "Nombre de jours : " if ($verbose);
	    print $field[25]."\n";
	    print "Jours tombés 1/6 (EXPORT SAGE) : " if ($verbose);
	    print $field[26]."\n";
	    print "Jours tombés 2/6 : " if ($verbose);
	    print "0\n";
	    print "Jours tombés 3/6 : " if ($verbose);
	    print "0\n";
	    print "Jours tombés 4/6 : " if ($verbose);
	    print "0\n";
	    print "Jours tombés 5/6 : " if ($verbose);
	    print "0\n";
	    print "Jours tombés 6/6 : " if ($verbose);
	    print "0\n";
	    print "Type répartition : " if ($verbose);
	    print "1\n";
	    print "Valeur répartition : " if ($verbose);
	    print "0.00\n";
	}
}
