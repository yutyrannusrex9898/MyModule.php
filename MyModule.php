<?php
if (!defined('_PS_VERSION_')) {
    exit;
}


class MyModule extends Module
{
    //constructor
    public function __construct()
    {
        //attributes of my module's class
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.5'; //every update I add 0.0.1 to the version
        $this->author = 'sam';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        //name and description of the module, will appear in Module catalogue
        $this->displayName = $this->l('My module');
        $this->description = $this->l('doesnt do much');

        $this->confirmUninstall = $this->l('please PLEASE dont uninstall :(');
        //ya have to provide a name
        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    //function to install a tab on the left menu, I named the tab "Hello"
    public function installTab($MyModule, $menuTab){

        $tab_id = Tab::getIdFromClassName('MyModule');
        $languages = Language::getLanguages(false);
        if ($tab_id == false)
        {
            $tab = new Tab();
            $tab->class_name = 'SamModuleAdmin';
            $tab->position = 99;
            $tab->icon = 'photo_size_select_actual';
            $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT'); // for some odd reason this line is ABSOLUTELY mandatory and the code will NOT work without it and I have no idea why, main cause of most of my issues on Thursday
            $tab->module = 'mymodule';
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = "Hello";
            }
            $tab->add();
        }
    }

    //removes whatever tab was added during installation, a bit wonky though
    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('MyModule');
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return (
            parent::install()
            && $this->registerHook('leftColumn')
            && $this->registerHook('header')
            && $this->installTab('AdminCatalog', 'YourAdminClassName', 'a tab lol') && //installing admin class in addition to the tab
            Configuration::updateValue('MODULENAME', "ModuleName")
            && Configuration::updateValue('MYMODULE_NAME', 'my friend')

        );
    }

    public function uninstall()
    {
        return (
            parent::uninstall()
            && Configuration::deleteByName('MYMODULE_NAME'));

    }
    public function getContent()
    {
        $output = '';

        // this part is executed only when the form is submitted, also used to get names and content for config file
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $configValue = (string) Tools::getValue('MYMODULE_CONFIG');

            // check that the value is valid
            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue('MYMODULE_CONFIG', $configValue);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }
    public function displayForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Configuration value'), //unusual way of displaying html from php, it is copy pasted from the dev docs and I failed to tidy it up so its being left as is
                        'name' => 'MYMODULE_CONFIG', //also the purpose of this unusual section is to display the config settings
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['MYMODULE_CONFIG'] = Tools::getValue('MYMODULE_CONFIG', Configuration::get('MYMODULE_CONFIG'));

        return $helper->generateForm([$form]);
    }

}