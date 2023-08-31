<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class CsvProductImport extends Module
{
    public function __construct()
    {
        $this->name = "csvproductimport";
        $this->tab = "administration";
        $this->version = "1.0.0";
        $this->author = "Xavier Moreno";
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            "min" => "1.7.0.0",
            "max" => "1.7.99.99",
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l("CSV Product Import");
        $this->description = $this->l("Import products using CSV files.");
        $this->confirmUninstall = $this->l(
            "Are you sure you want to uninstall?"
        );
    }

    public function install()
    {
        if (
            !parent::install() ||
            !$this->registerHook("actionAdminControllerSetMedia") ||
            !$this->registerHook("displayAdminCatalog") ||
            !$this->registerHook("displayAdminProductsExtra")
        ) {
            return false;
        }
        return true;
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue("controller") === "AdminCsvProductImport") {
            $this->context->controller->addJS(
                $this->_path . "views/js/csvproductimport.js"
            );
        }
    }

    public function hookDisplayAdminCatalog($params)
    {
        $this->smarty->assign("module_dir", $this->_path);
        return $this->display(__FILE__, "views/templates/admin/menu.tpl");
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        // Display the option to import products using CSV
        $this->smarty->assign("module_dir", $this->_path);
        return $this->display(
            __FILE__,
            "views/templates/admin/import_option.tpl"
        );
    }

    public function getAllCategoriesWithIds()
    {
        $categories = Category::getCategories(
            Context::getContext()->language->id,
            false,
            false
        );

        $categoriesWithIds = [];
        foreach ($categories as $category) {
            $categoriesWithIds[$category["name"]] =
                (int) $category["id_category"];
        }

        return $categoriesWithIds;
    }

    // Override getContent() to handle CSV import form submission
    public function getContent()
    {
        // Check if the form was submitted
        if (Tools::isSubmit("submitCsvImport")) {
            if (
                isset($_FILES["csv_file"]) &&
                $_FILES["csv_file"]["error"] == UPLOAD_ERR_OK
            ) {
                $csvFilePath = $_FILES["csv_file"]["tmp_name"];

                $handle = fopen($csvFilePath, "r");
                if ($handle !== false) {
                    // Skip the header row
                    fgetcsv($handle);

                    while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                        $rowData = $data[0];
                        $rowColumns = explode(",", $rowData);

                        // Check if the product already exists by reference

                        $id_category = 2;
                        // Validate and sanitize each field
                        $name = isset($rowColumns[0])
                            ? pSQL(trim($rowColumns[0]))
                            : "";
                        $reference = isset($rowColumns[1])
                            ? pSQL(trim($rowColumns[1]))
                            : "";
                        $ean13 = isset($rowColumns[2])
                            ? pSQL(trim($rowColumns[2]))
                            : "";
                        $costPrice = isset($rowColumns[3])
                            ? (float) str_replace(",", ".", $rowColumns[3])
                            : 0.0;
                        $salePrice = isset($rowColumns[4])
                            ? (float) str_replace(",", ".", $rowColumns[4])
                            : 0.0;
                        $iva = isset($rowColumns[5]) ? (int) $rowColumns[5] : 0;
                        $quantity = isset($rowColumns[6])
                            ? (int) $rowColumns[6]
                            : 0;
                        $categories = isset($rowColumns[7])
                            ? pSQL(trim($rowColumns[7]))
                            : "";
                        $brand = isset($rowColumns[8])
                            ? pSQL(trim($rowColumns[8]))
                            : "";

                        $existingProduct = Product::getIdByReference(
                            $rowColumns[1]
                        );
                        // Perform additional validations as needed
                        if (
                            empty($name) ||
                            empty($reference) ||
                            $salePrice <= 0 ||
                            $quantity <= 0 ||
                            empty($brand)
                        ) {
                            // Log error for this product
                            $errorLogMessage = "Skipping product with invalid data - Name: $name, Reference: $reference";
                            PrestaShopLogger::addLog(
                                $errorLogMessage,
                                3,
                                null,
                                "ProductImport",
                                (int) $this->context->employee->id
                            );
                            continue;
                        }
                        $allCategories = $this->getAllCategoriesWithIds();

                        $category_parent = explode(";", $categories);
                        $category_parent = trim($category_parent[0]);

                        if (isset($allCategories[$category_parent])) {
                            $id_category = $allCategories[$category_parent];
                        } else {
                            $_GET["forceIDs"] = true;
                            $category = new Category();
                            $category->id = rand(0, 999999);
                            $category->name = [1 => $category_parent];
                            $category->active = 1;
                            $category->is_root_category = false;
                            $category->id_parent = 2;
                            $category->link_rewrite = [
                                1 => Tools::link_rewrite($category_parent),
                            ];
                            $category->add();
                            $id_category = $category->id;
                        }
                        // Get brand ID based on brand name
                        $manufacturer = Manufacturer::getIdByName($brand);
                        $id_brand = $manufacturer ? (int) $manufacturer : 0;
                        
                        if ($existingProduct) {
                            $existingProductObj = new Product($existingProduct);
                            $existingProductObj->name = isset($rowColumns[0])
                                ? pSQL(trim($rowColumns[0]))
                                : $existingProductObj->name;
                            $existingProductObj->reference = isset(
                                $rowColumns[1]
                            )
                                ? pSQL(trim($rowColumns[1]))
                                : $existingProductObj->reference;
                            $existingProductObj->ean13 = isset($rowColumns[2])
                                ? pSQL(trim($rowColumns[2]))
                                : $existingProductObj->ean13;
                            $existingProductObj->cost_price = isset(
                                $rowColumns[3]
                            )
                                ? (float) str_replace(",", ".", $rowColumns[3])
                                : $existingProductObj->cost_price;
                            $existingProductObj->price = isset($rowColumns[4])
                                ? (float) str_replace(",", ".", $rowColumns[4])
                                : $existingProductObj->price;
                            $existingProductObj->id_tax_rules_group = isset(
                                $rowColumns[5]
                            )
                                ? (int) $rowColumns[5]
                                : $existingProductObj->id_tax_rules_group;
                            $existingProductObj->quantity = isset(
                                $rowColumns[6]
                            )
                                ? (int) $rowColumns[6]
                                : $existingProductObj->quantity;
                            $existingProductObj->id_category_default = $id_category;
                            $existingProductObj->update();
                            $this->context->smarty->assign(
                                "imported_product_" .
                                    $existingProductObj->reference,
                                true
                            );
                        } else {
                            // Insert data into the database
                            $product = new Product();
                            $product->name = $name;
                            $product->reference = $reference;
                            $product->ean13 = $ean13;
                            $product->price = $salePrice;
                            $product->cost_price = $costPrice;
                            $product->quantity = $quantity;
                            $product->id_tax_rules_group = $iva;
                            $product->id_category_default = $id_category;
                            $product->id_manufacturer = $id_brand;

                            if ($product->add()) {
                                // Product added successfully
                                // Add the product to the specific category and brand
                                $product->addToCategories([$id_category]);
                                $product->id_category_default = $id_category;
                                $product->id_manufacturer = $id_brand;
                                $product->save();

                                // Log import success for this product
                                $successLogMessage = "Product imported successfully - Name: $name, Reference: $reference";
                                PrestaShopLogger::addLog(
                                    $successLogMessage,
                                    1,
                                    null,
                                    "ProductImport",
                                    (int) $this->context->employee->id
                                );
                                $this->context->smarty->assign(
                                    "imported_product_" . $product->reference,
                                    true
                                );
                            } else {
                                // Display error or log message for failure
                                $errorLogMessage = "Error importing product - Name: $name, Reference: $reference";
                                PrestaShopLogger::addLog(
                                    $errorLogMessage,
                                    3,
                                    null,
                                    "ProductImport",
                                    (int) $this->context->employee->id
                                );
                                $this->context->smarty->assign(
                                    "imported_product_" . $product->reference,
                                    false
                                );
                            }
                        }
                    }

                    fclose($handle);

                    // Display success message
                    $this->context->smarty->assign("import_success", true);
                } else {
                    // Display error message if unable to open file
                    $this->context->smarty->assign("import_error", true);
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts'));
            } else {
                // Display error message if no file or upload error
                $this->context->smarty->assign("import_error", true);
            }
        }

        // Display import form
        $this->context->smarty->assign("module_dir", $this->_path);
        return $this->display(
            __FILE__,
            "views/templates/admin/import_form.tpl"
        );
    }
}
