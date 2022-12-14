<?php

class SamModuleAdminController extends ModuleAdminController
{
    //constructor, no attributes
    public function __construct()
    {
        parent::__construct();
    }

    //returns the result of renderConfigurationForm function, just there to activate it
    public function renderView()
    {
        return $this->renderConfigurationForm();
    }

    //sets language for tab
    //below is all the html, creates title description and submit button that when activated moves you onto postprocess
    //function returns html structure of the tab
    public function renderConfigurationForm()
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();



        foreach ($langs as $key => $language)
        {
            $options[] = array(
                'id_option' => $language['id_lang'],
                'name' => $language['name']
            );
        }

        $orderstates[] = array(
            'id_option' => 0,
            'name' => 'All'
        );

        $inputs = array(
            array(
                'type' => 'text',   //menu tab title
                'value' => 'Hi',
                'label' => $this->l('Hello!!'),
                'name' => 'title',
            ),
            array(
                'type' => 'textarea',   //menu tab description
                'value' => 'Hi!',
                'label' => $this->l('Desc'),
                'name' => 'description',
            ),

            array(
                'type' => 'select',
                'label' => $this->l('Status'), //menu tab submit button
                'desc' => $this->l('Choose a language you wish to export'),
                'name' => 'status',
                'options' => array(
                    'query' => $orderstates,
                    'id' => 'id_option',
                    'name' => 'name'
                ),
            ),
        );


        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Export Options'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save & Export'),
                )
            ),
        );

        $helper = new HelperForm(); //helper related things, activates functions like getConfigFieldsValues to change text in title or desc if there is data in the database
        $helper->show_toolbar = false; //returns the structure of the tab

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitExport';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = $this->token;
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),   //calls function to get values from database if they are already saved
            'languages' => $this->context->controller->getLanguages(),
        );
        //generates the form when done


        return $helper->generateForm(array($fields_form));
    }

    //get values from database in case it already has values in it.
    public function getConfigFieldsValues()
    {
        //function to get the name and desc and put it into the html text boxes
        $file_object = array();
        $query = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT name, description FROM `ps_mymodule`');
        $file_object['title'] = $query[0]['name'];      //if there is a title or description previously filled in it will appear in prestashop
        $file_object['description'] = $query[0]['description'];

        //returns an array with the values of name and title from the database
        return $file_object;
    }

    public function postProcess()
    {
        //only activates once the form is submitted, saves all content in local database and replaces data if need be
        //doesnt return anything
        if (Tools::isSubmit('submitExport')) {
            $title = Tools::getValue('title') . "";
            $description = Tools::getValue('description');
            $ifQuery = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT name, description FROM `ps_mymodule`');
            //if the database has something in it the value will get replaced if a new one is put in, however if the database table is empty it will add a new one
            if (empty($title) && empty($description)) {
                return $this->error[] = 'Type something in';
                echo "0";
            }
            if($ifQuery[0]['name'] != "" && $ifQuery[0]['description'] != ""){
                $updateQuery = "UPDATE `ps_mymodule` SET name= '".$title."',description = '".$description."'";
                Db::getInstance()->Execute($updateQuery);
            }
            else{
                Db::getInstance()->Execute("INSERT INTO `ps_mymodule`(`name`, `description`) VALUES ('".$title."', '".$description."')");
            }

            parent::postProcess();
        }
    }

    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }
}