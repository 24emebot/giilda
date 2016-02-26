<?php

class VracCsvFile extends CsvFile {

    const CSV_NUMERO_PAPIER = 0;
    const CSV_NUMERO_CONTRAT = 1;
    const CSV_DATE_SIGNATURE = 2;
    const CSV_DATE_SAISIE = 3;
    const CSV_TYPE_TRANSACTION = 4;
    const CSV_STATUT = 5;
    const CSV_VENDEUR_ID = 6;
    const CSV_VENDEUR_CVI = 7;
    const CSV_VENDEUR_VIN_LOGEMENT_AUTRE = 8;
    const CSV_INTERMEDIAIRE_ID = 9;
    const CSV_ACHETEUR_ID = 10;
    const CSV_COURTIER_ID = 11;
    const CSV_RESPONSABLE = 12;
    const CSV_PRODUIT_ID = 13;
    const CSV_PRODUIT_LIBELLE = 14;
    const CSV_MILLESIME = 15;
    const CSV_CEPAGE_ID = 16;
    const CSV_CEPAGE_LIBELLE = 17;
    const CSV_CATEGORIE_VIN = 18;
    const CSV_CATEGORIE_VIN_INFO = 19;
    const CSV_SURFACE = 20;
    const CSV_LOT = 21;
    const CSV_DEGRE = 22;
    const CSV_RECIPIENT_CONTENANCE = 23;
    const CSV_QUANTITE = 24;
    const CSV_QUANTITE_UNITE = 25;
    const CSV_VOLUME_PROPOSE = 26;
    const CSV_VOLUME_ENLEVE = 27;
    const CSV_PRIX_UNITAIRE = 28;
    const CSV_PRIX_UNITAIRE_HL = 29;
    const CSV_CLE_DELAI_PAIEMENT = 30;
    const CSV_DELAI_PAIEMENT = 31;
    const CSV_CLE_MODE_PAIEMENT = 32;
    const CSV_MODE_PAIEMENT = 33;
    const CSV_ACOMPTE_SIGNATURE = 34;
    const CSV_MOYEN_PAIEMENT = 35;
    const CSV_TAUX_COURTAGE = 36;
    const CSV_REPARTITION_COURTAGE = 37;
    const CSV_REPARTITION_CVO = 38;
    const CSV_RETIRAISON_DATE_DEBUT = 39;
    const CSV_RETIRAISON_DATE_FIN = 40;
    const CSV_CLAUSES = 41;
    const CSV_LABELS = 42;
    const CSV_COMMENTAIRES = 43;

    const LABEL_BIO = 'agriculture_biologique';

    public static $labels_array = array(self::LABEL_BIO => "Agriculture Biologique");

    public function import() {
        $this->errors = array();
        $csvs = $this->getCsv();

        $configuration = ConfigurationClient::getInstance()->getCurrent();

        foreach ($csvs as $line) {
            try {
                $v = new Vrac();

                $v->date_signature = $this->verifyAndFormatDateSignature($line);
                $v->date_campagne = $v->date_signature;
                $v->valide->date_saisie = $this->verifyAndFormatDateSaisie($line);

                $dateSaisie = new DateTime($v->valide->date_saisie);
                $v->numero_contrat = $this->verifyAndFormatNumeroContrat($line);
                $v->numero_archive = $this->verifyAndFormatNumeroArchive($line);
                
                $v->date_visa = $v->valide->date_saisie;
                
                $v->constructId();

                if ($vracDoublon = VracClient::getInstance()->find($v->_id, acCouchdbClient::HYDRATE_JSON)) {
                    throw new sfException(sprintf($this->red("Existe $v->_id archivage : $vracDoublon->numero_archive ")));
                }

                $vendeur = $this->verifyEtablissement($line[self::CSV_VENDEUR_ID]);
                $acheteur = $this->verifyEtablissement($line[self::CSV_ACHETEUR_ID]);

                $representant = null;
                if ($line[self::CSV_INTERMEDIAIRE_ID]) {
                    $representant = $this->verifyEtablissement($line[self::CSV_INTERMEDIAIRE_ID]);
                }

                $courtier = null;
                if ($line[self::CSV_COURTIER_ID]) {
                    $courtier = $this->verifyEtablissement($line[self::CSV_COURTIER_ID]);
                }

                $v->vendeur_identifiant = $vendeur->_id;

                if ($representant) {
                    $v->representant_identifiant = $representant->_id;
                } else {
                    $v->representant_identifiant = $vendeur->_id;
                }

                $v->acheteur_identifiant = $acheteur->_id;
                $v->mandataire_exist = false;

                if ($courtier) {
                    $v->mandataire_exist = true;
                    $v->mandataire_identifiant = $courtier->_id;
                }
                $v->setInformations();

                if($line[self::CSV_VENDEUR_CVI]) {
                    $v->vendeur->cvi = $line[self::CSV_VENDEUR_CVI];
                }

                $v->responsable = $this->verifyAndFormatResponsable($line);

                $produit = $configuration->identifyProductByLibelle($line[self::CSV_PRODUIT_LIBELLE]);

                if ($produit) {
                    $v->setProduit($produit->getHash());
                }

                if (!$v->produit) {
                    throw new sfException(sprintf("Le produit n'a pas été trouvé %s", $this->yellow($line[self::CSV_PRODUIT_LIBELLE])));
                }

                $v->cepage_libelle = $this->formatAndVerifyCepage($line);

                $this->verifyTypeTransaction($line);
                $v->type_transaction = $line[self::CSV_TYPE_TRANSACTION];
                $v->millesime = $this->verifyAndFormatMillesime($line);
                $v->categorie_vin = $this->verifyAndFormatCatgeorieVin($line);
                $v->domaine = $this->verifyAndFormatDomaine($line, $v);
                $v->degre = $this->verifyAndFormatDegre($line);

                $v->bouteilles_contenance_volume = $this->verifyAndFormatBouteillesContenanceVolume($line);
                $v->bouteilles_contenance_libelle = $this->verifyAndFormatBouteillesContenanceLibelle($line, $v);

                if($v->bouteilles_contenance_volume && $v->type_transaction != VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE) {
                    throw new sfException("Le contrat à une contenance de bouteille mais n'a pas été signalé en contrat bouteille");
                }

                if($v->type_transaction == VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE && !$v->bouteilles_contenance_volume) {
                    throw new sfException("Le contrat a été signalé en contrat bouteille, mais n'a pas de contenance");
                }

                $v->jus_quantite = $this->verifyAndFormatVolumePropose($line);
                $v->volume_enleve = $v->volume_propose;
                $v->prix_initial_unitaire = $this->formatAndVerifyPrixUnitaire($line);
                $v->prix_initial_unitaire_hl = $v->prix_initial_unitaire;
                $v->date_debut_retiraison = $this->formatAndVerifyDateRetiraisonDebut($line);
                $v->date_limite_retiraison = $this->formatAndVerifyDateRetiraisonFin($line);

                $v->vendeur_tva = 1;

                if ($line[self::CSV_LABELS]) {
                    $labels_contrat_array = explode(",", $line[self::CSV_LABELS]);
                    foreach ($labels_contrat_array as $label_key) {
                        $label_key = trim($label_key);
                        if (array_key_exists($label_key, self::$labels_array)) {

                            $v->getOrAdd('label')->add($label_key, self::$labels_array[$label_key]);
                        }
                    }
                }

                if ($v->date_debut_retiraison && $v->date_limite_retiraison && $v->date_debut_retiraison > $v->date_limite_retiraison) {
                    throw new sfException($this->red("La date de début de retiraison est supérieur à celle du début"));
                }

                $v->vendeur_tva = 0; 
                if(preg_match("/assujetti_tva/", $line[self::CSV_CLAUSES])) {
                    $v->vendeur_tva = 1; 
                }

                $v->tva = 0; 
                if(preg_match("/facturation_tva/", $line[self::CSV_CLAUSES])) {
                    $v->tva = 1; 
                }
                
                $v->delai_paiement = $line[self::CSV_CLE_DELAI_PAIEMENT];
                $v->delai_paiement_libelle = $line[self::CSV_DELAI_PAIEMENT];

                $v->moyen_paiement = $line[self::CSV_CLE_DELAI_PAIEMENT];
                $v->moyen_paiement_libelle = $line[self::CSV_MODE_PAIEMENT];

                $v->acompte = $this->formatAndVerifyAcompte($line);
                
                if(preg_match("/clause_reserve_propriete/", $line[self::CSV_CLAUSES])) {
                    $v->clause_reserve_propriete = true; 
                }

                if(preg_match("/autorisation_nom_vin/", $line[self::CSV_CLAUSES])) {
                    $v->autorisation_nom_vin = true; 
                }

                if(preg_match("/autorisation_nom_producteur/", $line[self::CSV_CLAUSES])) {
                    $v->autorisation_nom_producteur = true; 
                }

                $v->conditionnement_crd = $this->formatAndVerifyConditionnementCrd($line);

                $v->embouteillage = $this->formatAndVerifyEmbouteillage($line);

                $v->preparation_vin = $this->formatAndVerifyPreparationVin($line);
                
                $v->commentaire = str_replace('\n', "\n", $line[self::CSV_COMMENTAIRES]);
                
                $v->update();
                //$v->enleverVolume($v->volume_enleve);

                $v->valide->statut = $this->verifyAndFormatStatut($line);

                $v->save();
                echo sprintf("Le contrat %s a bien été importé\n", $this->green($v->_id));
            } catch (Exception $e) {
                echo sprintf("%s : #%s\n", $this->red($e->getMessage()), implode(";", $line));
                $this->error[] = $e->getMessage();
            }
        }
    }

    private function verifyAndFormatNumeroContrat($line) {
        if ($line[self::CSV_NUMERO_CONTRAT] && preg_match('/[0-9]+/', $line[self::CSV_NUMERO_CONTRAT]) && strlen(trim($line[self::CSV_NUMERO_CONTRAT])) == 13) {

            return sprintf("%13d", $line[self::CSV_NUMERO_CONTRAT]);
        }


        throw new Exception(sprintf("Le numéro de contrat est nul ou au mauvais format %s", $line[self::CSV_NUMERO_CONTRAT]));
    }

    private function verifyAndFormatNumeroArchive($line) {
        if ($line[self::CSV_NUMERO_PAPIER] && preg_match('/[0-9]+/', $line[self::CSV_NUMERO_PAPIER])) {

            return sprintf("%05d", $line[self::CSV_NUMERO_PAPIER]);
        }

        throw new Exception(sprintf("Le numéro d'archive est nul ou au mauvais format %s", $line[self::CSV_NUMERO_PAPIER]));
    }

    private function verifyAndFormatDateSignature($line) {
        $date = $line[self::CSV_DATE_SIGNATURE];

        if (!$date) {
            throw new Exception(sprintf("La date de signature est requise", $date));
        }

        return $this->formatAndVerifyDate($date);
    }

    private function verifyAndFormatDateSaisie($line) {
        $date = $line[self::CSV_DATE_SAISIE];

        if (!$date) {
            throw new Exception(sprintf("La date de saisie est requise", $date));
        }

        return $this->formatAndVerifyDate($date);
    }

    private function verifyAndFormatStatut($line) {
        $statut = $line[self::CSV_STATUT];
        $statuts = VracClient::getInstance()->getStatuts();

        if(!array_key_exists($line[self::CSV_STATUT], $statuts)) {
            throw new Exception(sprintf("Le statut %s n'existe pas", $statut));
        }

        return $statut;
    }

    private function verifyEtablissement($id) {
        if (strlen($id) < 6) {

            $id = sprintf("%06d01", $id);
        }

        $etablissement = EtablissementClient::getInstance()->find($id, acCouchdbClient::HYDRATE_JSON);

        if (!$etablissement) {
            throw new Exception(sprintf("L'établissement %s n'existe pas", $id));
        }

        return $etablissement;
    }

    private function verifyTypeTransaction($line) {
        
    }

    private function verifyAndFormatResponsable($line) {
        $responsable = $line[self::CSV_RESPONSABLE];

        if(!$responsable) {

            return null;
        }

        if(!in_array($responsable, array('vendeur', 'acheteur', 'mandataire'))) {

            throw new Exception(sprintf("Le type de responsable %s n'existe pas", $responsable));
        }

        return $responsable;
    }

    private function verifyAndFormatMillesime($line) {

        return $line[self::CSV_MILLESIME] ? (int) $line[self::CSV_MILLESIME] : null;
    }

    private function verifyAndFormatCatgeorieVin($line) {

        return ($line[self::CSV_CATEGORIE_VIN]) ? $line[self::CSV_CATEGORIE_VIN] : VracClient::CATEGORIE_VIN_GENERIQUE;
    }

    private function verifyAndFormatDomaine($line, $vrac) {
        $domaine = trim($line[self::CSV_CATEGORIE_VIN_INFO]);
        if($vrac->categorie_vin == VracClient::CATEGORIE_VIN_GENERIQUE && $domaine) {
            echo sprintf("%s : #%s\n", $this->yellow("Attention un domaine a été déclaré alors que le vin est générique"), implode(";", $line));
        }

        return ($domaine) ? $domaine : null;
    }

    private function verifyAndFormatDegre($line) {

        if (!trim($line[self::CSV_DEGRE])) {
            return null;
        }

        return $this->formatAndVerifyFloat($line[self::CSV_DEGRE]);
    }

    private function verifyAndFormatVolumePropose($line) {

        $number = $this->formatAndVerifyFloat($line[self::CSV_VOLUME_PROPOSE]);

        if (!$number) {

            throw new Exception(sprintf("Le volume proposé est requis"));
        }

        return $number;
    }

    private function formatAndVerifyPrixUnitaire($line) {
        $number = $this->formatAndVerifyFloat($line[self::CSV_PRIX_UNITAIRE_HL]);

        if (!$number) {

            throw new Exception(sprintf("Le prix unitaire est requis"));
        }

        return $number;
    }

    private function verifyAndFormatBouteillesContenanceVolume($line) {
        $contenance = $line[self::CSV_RECIPIENT_CONTENANCE];

        if(!$contenance) {

            return null;
        }

        $contenances = VracConfiguration::getInstance()->getContenances();

        if(!in_array($contenance, $contenances)) {
            
            throw new Exception(sprintf("La contenance %s n'existe pas en configuration", $contenance));
        }

        return $contenance;
    }

    private function verifyAndFormatBouteillesContenanceLibelle($line, $vrac) {

        if(!$vrac->bouteilles_contenance_volume) {

            return null;
        }

        $contenances = VracConfiguration::getInstance()->getContenances();

        if(!in_array($vrac->bouteilles_contenance_volume, $contenances)) {
            
            throw new Exception(sprintf("La contenance %s n'existe pas en configuration", $vrac->bouteilles_contenance_volume));
        }

        return array_search($vrac->bouteilles_contenance_volume, $contenances);
    }

    private function formatAndVerifyAcompte($line) {
        $number = $this->formatAndVerifyFloat($line[self::CSV_ACOMPTE_SIGNATURE]);

        return $number;
    }

    private function formatAndVerifyDateRetiraisonDebut($line) {
        $date = $line[self::CSV_RETIRAISON_DATE_DEBUT];

        if (!$date) {

            return null;
            //throw new Exception(sprintf("La date de début de retiraison est requise", $date));
        }

        return $this->formatAndVerifyDate($date);
    }

    private function formatAndVerifyDateRetiraisonFin($line) {
        $date = $line[self::CSV_RETIRAISON_DATE_FIN];

        if (!$date) {

            echo sprintf("%s : #%s\n", $this->yellow("La date de fin de retiraison est vide"), implode(";", $line));
        }

        return $this->formatAndVerifyDate($date);
    }

    private function formatAndVerifyConditionnementCrd($line) {
        $conditionnements = array_keys(VracConfiguration::getInstance()->getConditionnementsCRD());

        foreach($conditionnements as $conditionnement) {
            if(preg_match("/".$conditionnement."/", $line[self::CSV_CLAUSES])) {

                return $conditionnement;
            }
        }

        return null;
    }

    private function formatAndVerifyPreparationVin($line) {
        $acteurs = array_keys(VracConfiguration::getInstance()->getActeursPreparationVin());

        foreach($acteurs as $acteur) {
            if(preg_match("/"."PREPARATION_VIN_".$acteur."/", $line[self::CSV_CLAUSES])) {

                return $acteur;
            }
        }

        return null;
    }

    private function formatAndVerifyEmbouteillage($line) {
        $acteurs = array_keys(VracConfiguration::getInstance()->getActeursEmbouteillage());

        foreach($acteurs as $acteur) {
            if(preg_match("/"."EMBOUTEILLAGE_".$acteur."/", $line[self::CSV_CLAUSES])) {

                return $acteur;
            }
        }

        return null;
    }

    private function formatAndVerifyCepage($line) {

        return $line[self::CSV_CEPAGE_LIBELLE];
    }

    private function formatAndVerifyFloat($number) {
        $number = (float) str_replace(",", ".", $number);

        if (!is_float($number)) {

            throw new Exception(sprintf("Number %s is not a float", $number));
        }

        return (float) FloatHelper::getInstance()->format($number);
    }

    private function formatAndVerifyDate($date) {
        $date = preg_replace("|^([0-9]+)/([0-9]+)/([0-9]+)$|", '\3-\2-\1', $date);
        $date = preg_replace("|^([0-9]{4})([0-9]{2})([0-9]{2})$|", '\1-\2-\3', $date);
        $date = preg_replace("|^([0-9]{2})([0-9]{2})([0-9]{4})$|", '\3-\2-\1', $date);

        $date = new DateTime($date);

        return $date->format('Y-m-d');
    }

    public function getErrors() {
        return $this->errors;
    }

    public function green($string) {
        return "\033[32m" . $string . "\033[0m";
    }

    public function yellow($string) {
        return "\033[33m" . $string . "\033[0m";
    }

    public function red($string) {
        return "\033[31m" . $string . "\033[0m";
    }

}
