<?php

class SamModuleAdminController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }


    public function renderView()
    {
        return $this->renderConfigurationForm();
    }

    public function renderConfigurationForm()
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int)$this->context->shop->id;



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
                'type' => 'text',
                'value' => 'Hi',
                'label' => $this->l('Hello!!'),
                'name' => 'title',
            ),
            array(
                'type' => 'textarea',
                'value' => 'Hi!',
                'label' => $this->l('Desc'),
                'name' => 'description',
            ),

            array(
                'type' => 'select',
                'label' => $this->l('Status'),
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

        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitExport';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = $this->token;
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
        );


        return $helper->generateForm(array($fields_form));
    }
    public function getConfigFieldsValues()
    {
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)$this->context->shop->id;
        $file_object = array();
        $query = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT name, description FROM `ps_mymodule`');
        //var_dump($query);
        //die();
        $file_object['title'] = $query[0]['name'];
        $file_object['description'] = $query[0]['description'];

        return $file_object;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExport')) {
            $title = Tools::getValue('title') . "";
            $description = Tools::getValue('description');
            $status = Tools::getValue('status');
            $ifQuery = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT name, description FROM `ps_mymodule`');
//            var_dump($ifQuery);

            if (empty($title) && empty($description)) {
                return $this->error[] = 'Type something in';
            }
            if($ifQuery[0]['name'] != "" && $ifQuery[0]['description'] != ""){
                $updateQuery = "UPDATE `ps_mymodule` SET name= '".$title."',description = '".$description."'";
                Db::getInstance()->Execute($updateQuery);
                echo "1";
            }
            else{
                Db::getInstance()->Execute("INSERT INTO `ps_mymodule`(`name`, `description`) VALUES ('".$title."', '".$description."')");
                echo "2";
            }

            $where_status = '';

            if ($status != 0) {
                $f = fopen('php://output', 'w');
                foreach ($this->available_fields['orders'] as $field => $array) {
                    $titles[] = $array['label'];
                }
                $delimiter = ';';
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