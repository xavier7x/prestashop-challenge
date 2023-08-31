<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CtaProducto extends Module
{
    public function __construct()
    {
        $this->name = 'ctaproducto';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Xavier Moreno';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->secure_key = Tools::encrypt($this->name);
        parent::__construct();
        $this->displayName = $this->l('CTA producto');
        $this->description = $this->l('Campo personalizado para cada producto.');
        $this->confirmUninstall = $this->l('Are you sure?');
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];

        // Obtén el valor personalizado de la base de datos
        $customValue = Db::getInstance()->getValue('
            SELECT `custom_value` FROM `' . _DB_PREFIX_ . 'ctaproducto_custom_values`
            WHERE `id_product` = ' . (int) $idProduct
        );

        // Renderiza el formulario con el valor personalizado
        return $this->display(__FILE__, 'displayadminproductextra.tpl', array(
            'customValue' => $customValue
        ));
    }
    public function hookActionProductUpdate($params)
    {
        $idProduct = (int) $params['id_product'];
        $customValue = Tools::getValue('test_input');

        // Comprueba si ya existe un registro en la tabla intermedia
        $existingValue = Db::getInstance()->getValue('
            SELECT `id_ctaproducto_custom_value` FROM `' . _DB_PREFIX_ . 'ctaproducto_custom_values`
            WHERE `id_product` = ' . (int) $idProduct
        );
        
        if ($existingValue) {
            // Actualiza el valor en la tabla intermedia
            Db::getInstance()->update(
                'ctaproducto_custom_values',
                array('custom_value' => pSQL($customValue)),
                'id_product = ' . (int) $idProduct
            );
        } else {
            // Inserta un nuevo registro en la tabla intermedia
            Db::getInstance()->insert(
                'ctaproducto_custom_values',
                array(
                    'id_product' => (int) $idProduct,
                    'custom_value' => pSQL($customValue)
                )
            );
        }
    }
    public function hookDisplayProductAdditionalInfo($params)
{
    $idProduct = (int) $params['product']['id'];

    // Obtén el valor personalizado de la base de datos
    $customValue = Db::getInstance()->getValue('
        SELECT `custom_value` FROM `' . _DB_PREFIX_ . 'ctaproducto_custom_values`
        WHERE `id_product` = ' . (int) $idProduct
    );

    // Pasa el valor personalizado al gancho
    $params['customValue'] = $customValue;

    return $this->display(__FILE__, 'product-custom-value.tpl', $params);
}

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->createCustomValuesTable()) {
            return false;
        }

        return true;
    }
    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->unregisterHook('displayAdminProductsExtra')
            || !$this->dropCustomValuesTable()) {
            return false;
        }

        return true;
    }
    private function createCustomValuesTable()
    {
        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ctaproducto_custom_values` (
                `id_ctaproducto_custom_value` INT AUTO_INCREMENT PRIMARY KEY,
                `id_product` INT NOT NULL,
                `custom_value` VARCHAR(255) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');
    }

    private function dropCustomValuesTable()
    {
        return Db::getInstance()->execute('
            DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ctaproducto_custom_values`;
        ');
    }
}
