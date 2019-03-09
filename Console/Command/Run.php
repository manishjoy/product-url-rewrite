<?php


namespace ManishJoy\ProductUrlRewrite\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{

    const PRODUCT_ID_ARGUMENT = "id";
    const NAME_OPTION = "option";

    protected $_productCollectionFactory;
    protected $_productVisibility;
    protected $_resources;
    protected $_scopeConfig;
    protected $_categoryFactory;
    protected $_categoryCollection;
    protected $_productFactory;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $resources,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    )
    {
        $this->_productFactory = $_productFactory;
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->_productVisibility = $productVisibility;
        $this->_resources = $resources;
        $this->_scopeConfig = $scopeConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollection = $categoryCollection;
        return parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $paramProductIds = $input->getArgument(self::PRODUCT_ID_ARGUMENT);
        
        $option = $input->getOption(self::NAME_OPTION);
        $paramProductIdArray = array_filter(explode(',', $paramProductIds));

        $output->writeln("Starting Product Rewrite Process.");
        $output->writeln("============================================================================================================");
        $resultIdArray = [];
        if(!empty($paramProductIdArray)) {
            $productCollection = $this->getProductCollection();
            $productCollection = $productCollection->addFieldToFilter('entity_id', array('in' => $paramProductIdArray));
            foreach ($productCollection as $_product) {
                $this->deleteExistingUrlRewrite($_product);
                $resultIdArray[] = $this->enterNewUrlRewrite($_product);
                $output->write('... ');
            }
        } else {
            $productCollection = $this->getProductCollection();
            foreach ($productCollection as $_product) {
                $this->deleteExistingUrlRewrite($_product);
                $resultIdArray[] = $this->enterNewUrlRewrite($_product);
                $output->write('... ');
            }
        }
        
        $output->writeln('');
        $output->writeln("============================================================================================================");
        $output->writeln('Congratulations, rewrite process is done for all of the following product Ids');
        $output->writeln(implode(', ', $resultIdArray));
        $output->writeln('');
        $output->writeln("============================================================================================================");
        $output->writeln('Run `bin/magento indexer:reindex`');
    }

    public function deleteExistingUrlRewrite($_product)
    {
        $connection= $this->_resources->getConnection();
        $urlRewriteTable = $this->_resources->getTableName('url_rewrite');
        $catalogUrlRewriteProductCategory = $this->_resources->getTableName('catalog_url_rewrite_product_category');
        $rows = $connection->fetchAll("SELECT `url_rewrite_id` FROM " . $urlRewriteTable . " WHERE `entity_type` = 'product' AND `entity_id` = "."'".$_product->getId()."'");
        if(count($rows) == 0){
            return;
        }
        foreach ($rows as $row) {
            $deleteQueryCatalogUrlRewriteProductCategory = "DELETE FROM ". $catalogUrlRewriteProductCategory ." WHERE `url_rewrite_id` = " . $row['url_rewrite_id'];
            $connection->query($deleteQueryCatalogUrlRewriteProductCategory);

            $deleteUrlRewriteTable = "DELETE FROM ". $urlRewriteTable ." WHERE `url_rewrite_id` = " . $row['url_rewrite_id'];
            $connection->query($deleteUrlRewriteTable);
        }

    }

    public function enterNewUrlRewrite($_product)
    {
        $connection= $this->_resources->getConnection();
        $urlRewriteTable = $this->_resources->getTableName('url_rewrite');
        $catalogUrlRewriteProductCategory = $this->_resources->getTableName('catalog_url_rewrite_product_category');

        $storeIds = $_product->getStoreIds();
        $categoryIds = $_product->getCategoryIds();
        foreach ($storeIds as $storeId) {
            $requestPathWithoutSuffix = $_product->getUrlKey();

            // check if request path exists
            $mathcingRows = $connection->fetchAll("SELECT `url_rewrite_id` FROM " . $urlRewriteTable . " WHERE `entity_type` = 'product' AND `request_path` = "."'".$requestPathWithoutSuffix.$this->getProductUrlSuffix()."'");
            while (count($mathcingRows) > 0) {
                $requestPathWithoutSuffix .= '-1';
                $mathcingRows = $connection->fetchAll("SELECT `url_rewrite_id` FROM " . $urlRewriteTable . " WHERE `entity_type` = 'product' AND `request_path` = "."'".$requestPathWithoutSuffix.$this->getProductUrlSuffix()."'");
            }
            $requestPath = $requestPathWithoutSuffix.$this->getProductUrlSuffix();

            $targetPath = 'catalog/product/view/id/'.$_product->getId();
            $insertUrlRewriteTable = "INSERT INTO ". $urlRewriteTable ." (`entity_type`, `entity_id`, `request_path`, `target_path`, `redirect_type`, `store_id`, `description`, `is_autogenerated`, `metadata`) VALUES ('product', ".$_product->getId().", '".$requestPath."', '".$targetPath."', 0, ".$storeId.", NULL, 1, NULL);";
            
            $connection->query($insertUrlRewriteTable);
            $urlRewriteId = $connection->lastInsertId();

            foreach ($categoryIds as $categoryId) {
                $category = $this->_categoryFactory->create()->load($categoryId);
                $categoryPath = $this->getCategoryPath($category, $storeId);
                if(empty($categoryPath)) {
                    continue;
                }
                $requestPathWithoutSuffix = $categoryPath.$_product->getUrlKey();

                // check if request path exists
                $mathcingRows = $connection->fetchAll("SELECT `url_rewrite_id` FROM " . $urlRewriteTable . " WHERE `entity_type` = 'product' AND `request_path` = "."'".$requestPathWithoutSuffix.$this->getProductUrlSuffix()."'");
                while (count($mathcingRows) > 0) {
                    $requestPathWithoutSuffix .= '-1';
                    $mathcingRows = $connection->fetchAll("SELECT `url_rewrite_id` FROM " . $urlRewriteTable . " WHERE `entity_type` = 'product' AND `request_path` = "."'".$requestPathWithoutSuffix.$this->getProductUrlSuffix()."'");
                }
                $requestPath = $requestPathWithoutSuffix.$this->getProductUrlSuffix();
                $requestPath = $categoryPath.$_product->getUrlKey().$this->getProductUrlSuffix();
                $targetPath = 'catalog/product/view/id/'.$_product->getId().'/category/'.$categoryId;
                $insertUrlRewriteTable = "INSERT INTO ". $urlRewriteTable ." (`entity_type`, `entity_id`, `request_path`, `target_path`, `redirect_type`, `store_id`, `description`, `is_autogenerated`, `metadata`) VALUES ('product', ".$_product->getId().", '".$requestPath."', '".$targetPath."', 0, ".$storeId.", NULL, 1, NULL);";
                
                $connection->query($insertUrlRewriteTable);
                $urlRewriteId = $connection->lastInsertId();

                $insertCatalogUrlRewriteProductCategory = "INSERT INTO ". $catalogUrlRewriteProductCategory ." (`url_rewrite_id`, `category_id`, `product_id`) VALUES (".$urlRewriteId.", ".$categoryId.", ".$_product->getId().");";
                
                $connection->query($insertCatalogUrlRewriteProductCategory);
            }
        }
        return $_product->getId();
    }

    public function getCategoryPath($category, $storeId)
    {
        $pathIds = explode('/', $category->getPath());
        $collection = $this->_categoryCollection->create()
            ->setStoreId($storeId)
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $pathIds));

        $urlPath = '';
        foreach($collection as $cat){
            if (!empty($cat->getUrlKey())) {
                $urlPath .= $cat->getUrlKey().'/';
            }
        }
        return $urlPath;
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     * @since 100.0.3
     */
    protected function getProductUrlSuffix($storeId = null)
    {
        return $this->_scopeConfig->getValue(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("mj:producturlrewrite:run");
        $this->setDescription("Run Product URL Rewrite");
        $this->setDefinition([
            new InputArgument(self::PRODUCT_ID_ARGUMENT, InputArgument::OPTIONAL, "Product Ids separated by commas"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }

    public function getProductCollection() {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // // set visibility filter
        $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());

        return $collection;
    }
}
