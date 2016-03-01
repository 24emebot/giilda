<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class SocieteModificationForm
 * @author mathurin
 */
class EtablissementModificationForm extends CompteForm {

    private $etablissement;
    private $liaisons_operateurs = null;

    public function __construct(Etablissement $etablissement, $options = array(), $CSRFSecret = null) {
        $this->etablissement = $etablissement;
        parent::__construct($etablissement->getMasterCompte(), array('etablissement' => true), $CSRFSecret);
    }

    public function configure() {
        parent::configure();
        $this->setWidget('nom', new bsWidgetFormInput());
        $this->setWidget('statut', new bsWidgetFormChoice(array('choices' => $this->getStatuts(), 'multiple' => false, 'expanded' => true)));
        $this->setWidget('region', new bsWidgetFormChoice(array('choices' => $this->getRegions())));
        $this->setWidget('no_accises', new bsWidgetFormInput());
        $this->setWidget('commentaire', new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setWidget('site_fiche', new bsWidgetFormInput());


        $this->widgetSchema->setLabel('nom', 'Nom du chai *');
        $this->widgetSchema->setLabel('statut', 'Statut *');
        $this->widgetSchema->setLabel('region', 'Région viticole *');
        $this->widgetSchema->setLabel('no_accises', "N° d'Accise");
        $this->widgetSchema->setLabel('commentaire', 'Commentaire');
        $this->widgetSchema->setLabel('site_fiche', 'Site Fiche Publique');



        $this->setValidator('nom', new sfValidatorString(array('required' => true)));
        $this->setValidator('statut', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getStatuts()))));
        $this->setValidator('region', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getRegions()))));
        $this->setValidator('site_fiche', new sfValidatorString(array('required' => false)));
        $this->setValidator('no_accises', new sfValidatorString(array('required' => false)));
        $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));

        
        if (!$this->etablissement->isCourtier()) {
            $this->setWidget('cvi', new bsWidgetFormInput());
            $this->widgetSchema->setLabel('cvi', 'CVI');           
            $this->setValidator('cvi', new sfValidatorString(array('required' => false)));           
        } else {
            $this->setWidget('carte_pro', new bsWidgetFormInput());
            $this->widgetSchema->setLabel('carte_pro', 'N° Carte professionnelle');
            $this->setValidator('carte_pro', new sfValidatorString(array('required' => false)));
        }
        
        if($this->etablissement->isNew()) {
            $this->widgetSchema['statut']->setAttribute('disabled', 'disabled');
        }

        $this->widgetSchema->setNameFormat('etablissement_modification[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        
    }

    public function getStatuts() {
        
        return EtablissementClient::getStatuts();
    }


    public function getAdresseSociete() {
        
        return array(1 => 'Oui', 0 => 'Non');
    }
    
    public function getRegions() {
        return EtablissementClient::getRegions();
    }

    public function getTypeDR() {
        return EtablissementClient::getTypeDR();
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->etablissement->setNom($values['nom']);        
        $this->etablissement->setStatut($values['statut']);        
        $this->etablissement->setRegion($values['region']);        
        $this->etablissement->setSiteFiche($values['site_fiche']);       
        $this->etablissement->setNoAccises($values['no_accises']);        
        $this->etablissement->setCommentaire($values['commentaire']);
        
         if (!$this->etablissement->isCourtier()) {
            $this->etablissement->setCvi($values['cvi']);        
        } else {
            $this->etablissement->setCartePro($values['carte_pro']);
        }
        $this->etablissement->save();
    }
    
    
//    protected function doSave($con = null) {
//        if (null === $con) {
//            $con = $this->getConnection();
//        }
//        $this->updateObject();
        /*
        $this->etablissement->remove('liaisons_operateurs');
        $this->etablissement->add('liaisons_operateurs');
        
        foreach ($this->getEmbeddedForms() as $key => $form) {

            foreach ($this->values[$key] as $liaison) {
                $this->etablissement->addLiaison($liaison['type_liaison'], EtablissementClient::getInstance()->find($liaison['id_etablissement']));
            }
        } */
//        if($this->values['recette_locale_choice']){
//            $this->etablissement->recette_locale->id_douane = $this->values['recette_locale_choice'];
//        }
       
//        $old_compte = $this->etablissement->compte;
//        $switch = false;
//         if($this->values['adresse_societe'] && !is_null($this->values['statut']) && !$this->etablissement->getSociete()->isManyEtbPrincipalActif()
//            && ($this->values['statut'] != ($socStatut = $this->etablissement->getSociete()->statut))){
//                throw new sfException("Il s'agit de l'établissement pricipal de la société, il ne peut être suspendu. Pour le suspendre, vous devez suspendre la société.");
//        }
//        if($this->values['adresse_societe'] && !$this->etablissement->isSameContactThanSociete()){           
//           $this->etablissement->compte = $this->etablissement->getSociete()->compte_societe;
//           $switch = true;
//        } elseif(!$this->values['adresse_societe'] && $this->etablissement->isSameContactThanSociete()) {
//           $this->etablissement->compte = null;
//           $switch = true;
//        }
//        var_dump($this->getObject()->cvi); exit;
//        $this->etablissement->save();
        
//        if($switch) {
//            $this->etablissement->switchOrigineAndSaveCompte($old_compte);
//            $this->etablissement->save();
//        }         
//    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if ($form instanceof LiaisonsItemForm) {
                if (isset($taintedValues[$key])) {
                    $form->bind($taintedValues[$key], $taintedFiles[$key]);
                    $this->updateEmbedForm($key, $form);
                }
            }
        }

        if(!array_key_exists('statut', $taintedValues)) {
            $taintedValues['statut'] = $this->getObject()->statut;
        }

        parent::bind($taintedValues, $taintedFiles);
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }

    public function getFormTemplate() {
        $etablissement = new Etablissement();
        $form_embed = new LiaisonItemForm($etablissement->liaisons_operateurs->add());
        $form = new EtablissementCollectionTemplateForm($this, 'liaisons_operateurs', $form_embed);
        return $form->getFormTemplate();
    }

    protected function unembedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
        $this->liaisons_operateurs->remove($key);
    }
/*
    protected function getRecettesLocales() {        
        $douanes = SocieteAllView::getInstance()->findByInterproAndStatut('INTERPRO-declaration', SocieteClient::STATUT_ACTIF, array(SocieteClient::SUB_TYPE_DOUANE));

        $douanesList = array();
        foreach ($douanes as $key => $douane) {
            $douaneObj = SocieteClient::getInstance()->find($douane->id);            
            $douanesList[$douane->id] = $douane->key[SocieteAllView::KEY_RAISON_SOCIALE].' '.$douaneObj->siege->commune.' ('.$douaneObj->siege->code_postal.')';
        }
        return $douanesList;    
    }
*/

}

?>
